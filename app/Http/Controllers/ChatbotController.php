<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ChatbotController extends Controller
{
    /**
     * 第一階段：意圖判斷小精靈
     * 只吃最後幾句話（或全部對話），不吃龐大的商品 Context。
     * 強制回傳 JSON，解析出 intent 字串。
     */
    private function determineIntent($messages, $apiKey, $availableIntents)
    {
        $modelId = 'gemini-2.5-flash-lite';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";

        // 取出最後一次使用者的發言即可，大幅節省 Token
        $lastMessage = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastMessage = $msg['content'] ?? '';
                break;
            }
        }
        if (!$lastMessage) {
             // 如果沒有 user 訊息，預設 general
             return 'general';
        }

        // ==========================================
        // 關鍵字快篩 (繞過 API 直接判定)，大幅提升速度
        // ==========================================
        $lowerMsg = mb_strtolower($lastMessage);
        
        // 前台攔截
        if (isset($availableIntents['shopping'])) {
            if (str_contains($lowerMsg, '買') || str_contains($lowerMsg, '購物車') || str_contains($lowerMsg, '多少錢')) {
                return 'shopping';
            }
        }
        
        // 後台攔截
        if (isset($availableIntents['manage_product'])) {
            if (str_contains($lowerMsg, '新增') || str_contains($lowerMsg, '編輯') || str_contains($lowerMsg, '更新') || str_contains($lowerMsg, '修改')) {
                return 'manage_product';
            }
            if (str_contains($lowerMsg, '進入') || str_contains($lowerMsg, '跳轉') || str_contains($lowerMsg, '頁面')) {
                return 'navigate_page';
            }
        }

        $intentDescriptions = [];
        foreach ($availableIntents as $intent => $desc) {
            $intentDescriptions[] = "- {$intent}: {$desc}";
        }
        $intentListStr = implode("\n", $intentDescriptions);

        $systemInstruction = "你是一個意圖分析機器人。請根據使用者的敘述，判斷其意圖。回傳格式必須為嚴格的 JSON：{\"intent\": \"意圖名稱\"}。意圖名稱只能是以下之一：\n{$intentListStr}\n\n如果不屬於任何一種，請回傳 'general'。";

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $lastMessage]]
                ]
            ],
            // force JSON formatting setting in Gemini API
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        try {
            $response = Http::timeout(10)->withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);
            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // 清理可能包含的 Markdown JSON codeblock (` ` `json ... ` ` `)
                $text = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $text);
                $text = str_replace('```', '', $text);
                $decoded = json_decode(trim($text), true);
                
                $intentResult = $decoded['intent'] ?? 'general';
                Log::info('Intent Router result: ' . $intentResult . ' for message: ' . $lastMessage);
                return $intentResult;
            }
        } catch (\Exception $e) {
            Log::error('Intent Router Error: ' . $e->getMessage());
        }
        
        return 'general';
    }

    public function chat(Request $request)
    {
        $messages = $request->input('messages', []);

        $apiKey = env('GEMINI_API_KEY', env('OPENAI_API_KEY'));
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => '抱歉，系統尚未設定 Gemini 金鑰，所以無法對話喔！請洽管理員。',
            ], 200); 
        }

        // ==========================================
        // Stage 1: Intent Routing
        // ==========================================
        $intent = $this->determineIntent($messages, $apiKey, [
            'shopping' => '想要購買商品、加入購物車、詢問特定商品',
            'general' => '一般問候、聊天、不確定意圖'
        ]);

        // ==========================================
        // Stage 2: 準備對應的 Context 與 Tools
        // ==========================================
        $systemText = "你是一個在 FRESH 品牌選物店的線上貼心客服小幫手。請用親切、簡短的繁體中文與顧客對話。\n\n";
        $tools = [];

        if ($intent === 'shopping') {
            $products = Product::all(['id', 'name', 'price', 'description']);
            $systemText .= "商店目前販售的商品如下 (ID, 名稱, 價格)：\n" . json_encode($products, JSON_UNESCAPED_UNICODE) . "\n【重要指示】你有權限可以直接將商品加入購物車！當使用者表達想購買其中某樣商品，請「絕對不要」請他們自己去操作，你必須主動幫他們呼叫 add_to_cart 函式並加進購物車。";
            
            $tools[] = [
                'functionDeclarations' => [
                    [
                        'name' => 'add_to_cart',
                        'description' => 'Add a specific product to the shopping cart based on user intent.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'product_id' => [
                                    'type' => 'INTEGER',
                                    'description' => 'The ID of the product to add to cart.'
                                ]
                            ],
                            'required' => ['product_id']
                        ]
                    ]
                ]
            ];
        } else {
             // General intent: no heavy context, no tools, just basic chat.
             $systemText .= "商店販售各種質感生活選物。目前的意圖已判定為一般內容，請進行一般問候交流即可，無須且也無法呼叫任何功能。若是使用者詢問商品，請溫柔地提醒他們可以直接說出『我想購買OOO』。";
        }

        // Translate history to Gemini format
        $geminiMessages = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $geminiMessages[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']]
            ];
        }

        // ==========================================
        // Stage 3: 主要呼叫 (Function Execution)
        // ==========================================
        try {
            // 為了提升穩定性與回答速度，主對話模組改用更流暢的 gemini-2.5-flash
            $modelId = 'gemini-2.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
            
            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemText]]
                ],
                'contents' => $geminiMessages,
            ];
            
            // 只有判定為 shopping 才帶入工具
            if (!empty($tools)) {
                $payload['tools'] = $tools;
            }

            // 遇到掛站時早點跳出，並縮短 Timeout 至 15 秒
            $response = Http::timeout(15)->withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => '抱歉，我的大腦暫時連線異常，請稍後再試！'
                ], 200);
            }

            $responseData = $response->json();
            $candidates = $responseData['candidates'][0] ?? null;

            if (!$candidates || !isset($candidates['content']['parts'])) {
                 return response()->json([
                    'success' => true,
                    'action' => 'TEXT',
                    'reply' => ['role' => 'assistant', 'content' => '我有點混亂，可以再詳細說一次嗎？']
                ]);
            }

            // 解析回應
            $textResponse = '好的，有什麼能為您服務的嗎？';
            $action = 'TEXT';
            $productToReturn = null;
            $hasFunctionCall = false;

            foreach ($candidates['content']['parts'] as $part) {
                if (isset($part['functionCall'])) {
                    if ($part['functionCall']['name'] === 'add_to_cart') {
                        $hasFunctionCall = true;
                        $productId = $part['functionCall']['args']['product_id'] ?? null;
                        if ($productId) {
                            $product = Product::find($productId);
                            if ($product) {
                                $action = 'ADD_TO_CART';
                                $productToReturn = $product;
                                $textResponse = "好的！我已經幫您將「{$product->name}」加入購物車了！如果還想看別的商品，隨時告訴我喔。";
                            } else {
                                $textResponse = "抱歉，我找不到您想加的此商品！";
                            }
                        }
                    }
                } elseif (isset($part['text'])) {
                    if (!$hasFunctionCall) {
                        $textResponse = $part['text'];
                    }
                }
            }

            $jsonReturn = [
                'success' => true,
                'action' => $action,
                'reply' => [
                    'role' => 'assistant',
                    'content' => $textResponse
                ]
            ];
            
            if ($productToReturn) {
                $jsonReturn['product'] = $productToReturn;
            }

            return response()->json($jsonReturn);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '發生網路錯誤：' . $e->getMessage()
            ], 200);
        }
    }

    public function adminChat(Request $request)
    {
        $messages = $request->input('messages', []);

        $apiKey = env('GEMINI_API_KEY', env('OPENAI_API_KEY'));
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => '未設定 Gemini 金鑰。']);
        }

        // ==========================================
        // Stage 1: Intent Routing
        // ==========================================
        $intent = $this->determineIntent($messages, $apiKey, [
            'manage_product' => '新增商品、更新商品、修改商品資料與價格',
            'navigate_page' => '跳轉頁面、進入編輯頁面、進入新增頁面',
            'general' => '一般問候、閒聊、不確定意圖'
        ]);

        // ==========================================
        // Stage 2: 準備對應的 Context 與 Tools
        // ==========================================
        $systemText = "這裡是系統後台。你是一個專業的後台商品管理系統助理。\n\n";
        $tools = [];

        if (in_array($intent, ['manage_product', 'navigate_page'])) {
            // 只有需要商品相關操作時才讀取 DB 全資料
            $products = Product::all(['id', 'name', 'price', 'stock_quantity']);
            $systemText .= "目前的商品資料庫如下 (ID, 名稱, 價格, 庫存)：\n" . json_encode($products, JSON_UNESCAPED_UNICODE) . "\n";

            if ($intent === 'manage_product') {
                $systemText .= "【重要指示】你擁有後台操作權限！若使用者要新增或編輯商品相關資訊，請主動幫他們呼叫對應的函式（create_product, update_product），千萬不要請他們自己操作。";
                $tools[] = [
                    'functionDeclarations' => [
                        [
                            'name' => 'create_product',
                            'description' => '新增一件商品到資料庫',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'name' => ['type' => 'STRING'],
                                    'price' => ['type' => 'NUMBER'],
                                    'description' => ['type' => 'STRING'],
                                    'stock_quantity' => ['type' => 'INTEGER']
                                ],
                                'required' => ['name', 'price']
                            ]
                        ],
                        [
                            'name' => 'update_product',
                            'description' => '更新現有商品的資料',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'product_id' => ['type' => 'INTEGER'],
                                    'name' => ['type' => 'STRING'],
                                    'price' => ['type' => 'NUMBER'],
                                    'description' => ['type' => 'STRING'],
                                    'stock_quantity' => ['type' => 'INTEGER']
                                ],
                                'required' => ['product_id']
                            ]
                        ]
                    ]
                ];
            } elseif ($intent === 'navigate_page') {
                $systemText .= "【重要指示】若使用者只想跳轉至編輯頁面，或跳轉至新增商品頁面，請主動呼叫對應的函式（redirect_to_edit_page, redirect_to_create_page），千萬不要請他們自己點擊。";
                $tools[] = [
                    'functionDeclarations' => [
                        [
                            'name' => 'redirect_to_edit_page',
                            'description' => '跳轉至某個特定商品的編輯頁面（當使用者只說要"進入編輯頁面"時使用）',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'product_id' => ['type' => 'INTEGER', 'description' => '要編輯的商品 ID']
                                ],
                                'required' => ['product_id']
                            ]
                        ],
                        [
                            'name' => 'redirect_to_create_page',
                            'description' => '跳轉至新增商品頁面，不帶參數即可',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'intent' => ['type' => 'STRING']
                                ]
                            ]
                        ]
                    ]
                ];
            }
        } else {
             // General intent
             $systemText .= "目前的對話屬於一般問候或不確定狀態，請直接進行文字回覆，不需要進行對商品的操作。";
        }

        $geminiMessages = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $geminiMessages[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']]
            ];
        }

        // ==========================================
        // Stage 3: 主要呼叫 (Function Execution)
        // ==========================================
        try {
            // 後台主對話模組改用 gemini-2.5-flash
            $modelId = 'gemini-2.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
            
            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemText]]
                ],
                'contents' => $geminiMessages,
            ];
            if (!empty($tools)) {
                $payload['tools'] = $tools;
            }

            // 縮短 Timeout 至 15 秒
            $response = Http::timeout(15)->withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini Error: ' . $response->body());
                return response()->json(['success' => false, 'message' => 'API 錯誤']);
            }

            $responseData = $response->json();
            $candidates = $responseData['candidates'][0] ?? null;

            if (!$candidates || !isset($candidates['content']['parts'])) {
                 return response()->json(['success' => true, 'action' => 'TEXT', 'reply' => ['role' => 'assistant', 'content' => '解析失敗']]);
            }

            $textResponse = '好的，請確認操作。';
            $action = 'TEXT';
            $actionData = [];
            $hasFunctionCall = false;

            foreach ($candidates['content']['parts'] as $part) {
                if (isset($part['functionCall'])) {
                    $hasFunctionCall = true;
                    $funcName = $part['functionCall']['name'];
                    $args = $part['functionCall']['args'] ?? [];

                    if ($funcName === 'create_product') {
                        $product = Product::create([
                            'name' => $args['name'],
                            'price' => $args['price'],
                            'description' => $args['description'] ?? '',
                            'stock_quantity' => $args['stock_quantity'] ?? 0,
                        ]);
                        $textResponse = "已為您新增商品：「{$product->name}」！";
                        $action = 'RELOAD';
                    } elseif ($funcName === 'update_product') {
                        $product = Product::find($args['product_id']);
                        if ($product) {
                            $updateData = [];
                            if (isset($args['name'])) $updateData['name'] = $args['name'];
                            if (isset($args['price'])) $updateData['price'] = $args['price'];
                            if (isset($args['description'])) $updateData['description'] = $args['description'];
                            if (isset($args['stock_quantity'])) $updateData['stock_quantity'] = $args['stock_quantity'];
                            $product->update($updateData);
                            $textResponse = "已為您更新商品：「{$product->name}」！";
                            $action = 'RELOAD';
                        } else {
                            $textResponse = "找不到該商品 ID：{$args['product_id']}";
                        }
                    } elseif ($funcName === 'redirect_to_edit_page') {
                        $action = 'REDIRECT';
                        $actionData['url'] = route('admin.products.edit', $args['product_id']);
                        $textResponse = "好的，正在為您跳轉到編輯頁面...";
                    } elseif ($funcName === 'redirect_to_create_page') {
                        $action = 'REDIRECT';
                        $actionData['url'] = route('admin.products.create');
                        $textResponse = "好的，正在為您跳轉到新增頁面...";
                    }
                } elseif (isset($part['text'])) {
                    if (!$hasFunctionCall) {
                        $textResponse = $part['text'];
                    }
                }
            }

            $jsonReturn = [
                'success' => true,
                'action' => $action,
                'reply' => ['role' => 'assistant', 'content' => $textResponse]
            ];
            if (!empty($actionData)) {
                $jsonReturn['data'] = $actionData;
            }

            return response()->json($jsonReturn);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '錯誤：' . $e->getMessage()]);
        }
    }
}
