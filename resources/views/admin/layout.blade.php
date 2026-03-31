<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理後台</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700;800&display=swap');

        :root {
            /* Pokepia Kawaii Admin Palette */
            --primary: #f08cb3;      /* 粉紅 (主色) */
            --primary-dark: #d67a9e; /* 深粉紅 */
            --secondary: #ffe4e1;    /* 迷霧玫瑰 / 邊框與次要色 */
            --accent: #a8e6cf;       /* 粉彩薄荷 / 按鈕色 */
            --accent-hover: #8ce0c0;
            --danger: #ffb6c1;
            --danger-hover: #f08cb3;
            --text-dark: #6b5b5b;    /* 深褐色 */
            --text-light: #d4a5a5;   /* 淺褐色/粉灰 */
            --white: #ffffff;
            --bg: #fffafb;           /* 淡粉白背景 */
            
            --spring-bounce: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        body {
            margin: 0;
            font-family: 'M PLUS Rounded 1c', 'Quicksand', 'Helvetica Neue', Arial, sans-serif;
            font-weight: 700;
            background-color: var(--bg);
            background-image: radial-gradient(var(--secondary) 10%, transparent 10%);
            background-size: 20px 20px;
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
            color: var(--white);
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem;
            box-shadow: 4px 0 20px rgba(240, 140, 179, 0.3);
            border-top-right-radius: 40px;
            border-bottom-right-radius: 40px;
            border-right: 4px solid white;
            z-index: 10;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 1.6rem;
            font-weight: 800;
            text-align: center;
            border-bottom: 2px dashed rgba(255,255,255,0.4);
            padding-bottom: 1.5rem;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.1);
        }

        .sidebar a {
            color: var(--white);
            text-decoration: none;
            padding: 0.8rem 1.2rem;
            margin: 0.5rem 10px;
            border-radius: 50px;
            transition: all 0.3s var(--spring-bounce);
            font-weight: 800;
            opacity: 0.85;
            display: block;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: white;
            color: var(--primary);
            opacity: 1;
            transform: scale(1.05) translateX(5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .main-content {
            flex-grow: 1;
            padding: 3rem;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: var(--primary);
            font-size: 2.2rem;
            font-weight: 800;
            text-shadow: 2px 2px 0px white;
            margin: 0;
        }

        .btn {
            display: inline-block;
            background: var(--accent);
            color: #4a6c62;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 50px;
            border: 3px solid white;
            cursor: pointer;
            transition: all 0.3s var(--spring-bounce);
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(168, 230, 207, 0.4);
            text-align: center;
            font-family: inherit;
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 15px rgba(168, 230, 207, 0.6);
        }

        .btn-danger { 
            background: var(--danger); 
            color: white; 
            box-shadow: 0 4px 10px rgba(255, 182, 193, 0.4); 
            border-color: white;
        }
        .btn-danger:hover { 
            background: var(--danger-hover); 
            box-shadow: 0 6px 15px rgba(240, 140, 179, 0.6); 
        }

        .btn-secondary { 
            background: white; 
            color: var(--primary); 
            border-color: var(--primary);
            box-shadow: 0 4px 10px rgba(240, 140, 179, 0.2);
        }
        .btn-secondary:hover { 
            background: var(--secondary); 
        }

        .alert {
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            border-radius: 20px;
            font-weight: 800;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .alert-success { background: #d4f0e5; color: #22543d; }
        .alert-danger { background: #ffe4e1; color: #822727; }

        .card {
            background: var(--white);
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(240, 140, 179, 0.15);
            padding: 2.5rem;
            border: 4px solid white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 800;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid var(--secondary);
            border-radius: 20px;
            font-size: 1rem;
            box-sizing: border-box;
            background: var(--bg);
            transition: all 0.3s;
            font-family: inherit;
            color: var(--text-dark);
            font-weight: 700;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(240, 140, 179, 0.2);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            border-radius: 20px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        table th, table td {
            text-align: left;
            padding: 1.2rem 1rem;
        }
        table th {
            color: var(--primary);
            font-weight: 800;
            padding-bottom: 0.5rem;
            text-shadow: 1px 1px 0px white;
        }
        table tbody tr {
            background: var(--bg);
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            transition: transform 0.2s;
        }
        table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(240, 140, 179, 0.1);
        }
        table td:first-child { border-top-left-radius: 20px; border-bottom-left-radius: 20px; border-left: 2px solid white; }
        table td:last-child { border-top-right-radius: 20px; border-bottom-right-radius: 20px; border-right: 2px solid white; }
        table td { border-top: 2px solid white; border-bottom: 2px solid white; }

        .img-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 15px;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .action-btns {
            display: flex;
            gap: 0.8rem;
        }
    </style>
    <!-- Chatbot CSS -->
    <style>
        .chatbot-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(45, 55, 72, 0.4);
            cursor: pointer;
            z-index: 998;
            transition: transform 0.3s;
        }

        .chatbot-fab:hover {
            transform: scale(1.1);
        }

        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            max-height: calc(100vh - 120px);
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1001;
            transform-origin: bottom right;
            transform: scale(0);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--secondary);
        }

        .chat-window.active {
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .chat-header {
            background: var(--primary);
            color: var(--white);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .close-chat {
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 1rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: var(--bg);
        }

        .message {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            line-height: 1.4;
            font-size: 0.95rem;
            word-break: break-all;
        }

        .message.bot {
            background: var(--white);
            border: 1px solid var(--secondary);
            border-bottom-left-radius: 4px;
            align-self: flex-start;
            color: var(--text-dark);
        }

        .message.user {
            background: var(--accent);
            color: var(--white);
            border-bottom-right-radius: 4px;
            align-self: flex-end;
        }

        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid var(--secondary);
            display: flex;
            gap: 10px;
            background: var(--white);
        }

        .chat-input-area input {
            flex-grow: 1;
            border: 1px solid var(--secondary);
            border-radius: 4px;
            padding: 0.5rem 1rem;
            outline: none;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .chat-input-area input:focus {
            border-color: var(--primary);
        }

        .chat-send-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 4px;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

        .chat-send-btn:hover {
            opacity: 0.8;
        }

        .typing-indicator {
            display: none;
            padding: 0.5rem 1rem;
            background: var(--white);
            border: 1px solid var(--secondary);
            border-radius: 12px;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
            gap: 4px;
        }

        .typing-indicator.active {
            display: flex;
        }

        .dot {
            width: 6px;
            height: 6px;
            background: #ccc;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🎀 甜心後台</h2>
        <!-- 修正側邊欄為當前路由如果包含 admin.products 則 active -->
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">商品管理</a>
        <a href="{{ route('shop.index') }}" target="_blank">查看前台</a>
    </aside>

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                請檢查輸入的內容。
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Chatbot UI -->
    <div class="chatbot-fab" onclick="toggleChat()">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
    </div>

    <div class="chat-window" id="chat-window">
        <div class="chat-header">
            <h3>
                ✨ 魔法系統助理 🎀
            </h3>
            <button class="close-chat" onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot">管理員好喵！我是魔法小助理 ✨ 我可以幫您新增商品，或直接跳轉到編輯頁面。請問有什麼需要幫助的嗎？🎀</div>
            <div class="typing-indicator" id="typing-indicator">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="輸入指令或問題..." onkeypress="handleChatKeyPress(event)">
            <button class="chat-send-btn" onclick="sendChatMessage()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        let chatHistory = [];

        function toggleChat() {
            document.getElementById('chat-window').classList.toggle('active');
        }

        function handleChatKeyPress(event) {
            if (event.key === 'Enter') {
                sendChatMessage();
            }
        }

        async function sendChatMessage() {
            const inputEl = document.getElementById('chat-input');
            const text = inputEl.value.trim();
            if (!text) return;

            // 顯示使用者訊息
            appendChatMessage(text, 'user');
            inputEl.value = '';

            // 存入紀錄
            chatHistory.push({ role: 'user', content: text });

            // 顯示載入動畫
            const typing = document.getElementById('typing-indicator');
            typing.classList.add('active');
            
            // 捲動到底部
            scrollToChatBottom();

            try {
                const response = await fetch("{{ route('admin.chat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ messages: chatHistory })
                });

                const data = await response.json();
                typing.classList.remove('active');

                if (data.reply) {
                    appendChatMessage(data.reply.content, 'bot');
                    chatHistory.push(data.reply);
                } else if (data.message) {
                    appendChatMessage(data.message, 'bot');
                }

                if (data.action === 'REDIRECT' && data.data && data.data.url) {
                    setTimeout(() => {
                        window.location.href = data.data.url;
                    }, 1000);
                } else if (data.action === 'RELOAD') {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }

            } catch (error) {
                console.error('Chat error:', error);
                typing.classList.remove('active');
                appendChatMessage('抱歉，發生了一點網路錯誤，請稍後再試。', 'bot');
            }
        }

        function appendChatMessage(text, sender) {
            const messagesContainer = document.getElementById('chat-messages');
            const typingIndicator = document.getElementById('typing-indicator');
            
            const msgEl = document.createElement('div');
            msgEl.className = 'message ' + sender;
            msgEl.innerText = text;
            
            messagesContainer.insertBefore(msgEl, typingIndicator);
            scrollToChatBottom();
        }

        function scrollToChatBottom() {
            const container = document.getElementById('chat-messages');
            container.scrollTop = container.scrollHeight;
        }
    </script>
</body>
</html>
