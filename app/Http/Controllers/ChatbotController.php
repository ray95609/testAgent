<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $messages = $request->input('messages', []);

        // Load all available products context
        $products = Product::all(['id', 'name', 'price', 'description']);
        $productContext = "商店目前販售的商品如下 (ID, 名稱, 價格)：\n" . json_encode($products, JSON_UNESCAPED_UNICODE) . "\n若使用者表達想購買其中某樣商品，請主動呼叫 add_to_cart 函式加進購物車。";

        // Prepare the initial system message with instructions
        $systemMessage = [
            'role' => 'system',
            'content' => "你是一個在 FRESH 品牌選物店的線上貼心客服小幫手。請用親切、簡短的繁體中文與顧客對話。\n\n" . $productContext
        ];

        // Translate history to Gemini format
        $geminiMessages = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $geminiMessages[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']]
            ];
        }

        // 讀取 Gemini 金鑰（如果使用者命名為 OPENAI_API_KEY 也會一併相容撈取）
        $apiKey = env('GEMINI_API_KEY', env('OPENAI_API_KEY'));
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => '抱歉，系統尚未設定 Gemini 金鑰，所以無法對話喔！請洽管理員。',
            ], 200); 
        }

        try {
            $modelId = 'gemini-3-flash-preview';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
            $payload = [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => "你是一個在 FRESH 品牌選物店的線上貼心客服小幫手。請用親切、簡短的繁體中文與顧客對話。\n\n" . $productContext]
                    ]
                ],
                'contents' => $geminiMessages,
                'tools' => [
                    [
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
                    ]
                ]
            ];

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('Gemini Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => '抱歉，我的大腦暫時連線異常，請稍後再試！(錯誤: ' . $response->status() . ')'
                ], 200);
            }

            $responseData = $response->json();
            $candidates = $responseData['candidates'][0] ?? null;

            if (!$candidates || !isset($candidates['content']['parts'])) {
                 return response()->json([
                    'success' => true,
                    'action' => 'TEXT',
                    'reply' => [
                        'role' => 'assistant',
                        'content' => '我有點混亂，可以再詳細說一次嗎？'
                    ]
                ]);
            }

            // 解讀 Gemini 回傳的 parts
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
                    // 如果有自帶文字而且不是只拋 FunctionCall，保留 Gemini 的回答
                    if (!$hasFunctionCall) {
                        $textResponse = $part['text'];
                    }
                }
            }

            $jsonReturn = [
                'success' => true,
                'action' => $action,
                'reply' => [
                    // 前端需要的 role name 是 assistant (套用現有的邏輯)
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

        $products = Product::all(['id', 'name', 'price', 'stock_quantity']);
        $productContext = "這裡是系統後台。目前的商品資料庫如下 (ID, 名稱, 價格, 庫存)：\n" . json_encode($products, JSON_UNESCAPED_UNICODE) . "\n若使用者要新增、編輯、或跳轉至編輯頁面，請主動呼叫對應的函式（create_product, update_product, redirect_to_edit_page, redirect_to_create_page）。你是一個專業的後台商品管理系統助理。";

        $geminiMessages = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $geminiMessages[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']]
            ];
        }

        $apiKey = env('GEMINI_API_KEY', env('OPENAI_API_KEY'));
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => '未設定 Gemini 金鑰。']);
        }

        try {
            $modelId = 'gemini-3-flash-preview';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $productContext]]
                ],
                'contents' => $geminiMessages,
                'tools' => [
                    [
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
                            ],
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
                    ]
                ]
            ];

            $response = Http::timeout(30)->withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);

            if ($response->failed()) {
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
                        $action = 'RELOAD'; // 可以請前端重新整理列表
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
