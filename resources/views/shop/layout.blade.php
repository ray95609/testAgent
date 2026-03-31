<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>清新選物店</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700;800&display=swap');

        :root {
            /* Pokepia Kawaii Palette */
            --brand-color: #ffb6c1; /* 草莓牛奶粉 */
            --brand-dark: #f08cb3;  /* 稍微深一點的粉紅 */
            --brand-light: #ffe4e1; /* 迷霧玫瑰 (Misty Rose) */
            --accent-mint: #a8e6cf; /* 粉彩薄荷 (Pastel Mint) */
            --accent-lavender: #d4a5a5; /* 粉柔玫瑰 / 薰衣草系陰影色 */
            --text-main: #6b5b5b;   /* 深褐色取代生硬黑色，更柔和 */
            --text-muted: #9e8e8e;
            --bg-color: #fffafb;    /* 帶點極淡粉的白色背景 */
            --card-bg: #ffffff;
            
            --spring-bounce: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        body {
            margin: 0;
            font-family: 'M PLUS Rounded 1c', 'Quicksand', 'Helvetica Neue', 'PingFang TC', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(var(--brand-light) 10%, transparent 10%);
            background-size: 20px 20px; /* 微妙的波卡圓點點綴 */
            color: var(--text-main);
            overflow-x: hidden;
            font-weight: 700;
        }

        /* Navbar - Floating Pill */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 2rem;
            margin: 1.5rem 5%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            position: sticky;
            top: 20px;
            z-index: 100;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(255, 182, 193, 0.3);
            border: 3px solid white; /* 貼紙白邊感 */
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--brand-dark);
            text-decoration: none;
            letter-spacing: 2px;
            text-shadow: 2px 2px 0px white;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            padding: 0.8rem;
            border-radius: 50%;
            background: white;
            color: var(--brand-dark);
            border: 2px solid var(--brand-light);
            transition: all 0.3s var(--spring-bounce);
            box-shadow: 0 4px 10px rgba(255, 182, 193, 0.2);
        }

        .cart-icon:hover {
            transform: translateY(-3px) scale(1.05);
            background: var(--brand-light);
            border-color: var(--brand-dark);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-mint);
            color: #4a6c62;
            font-size: 0.8rem;
            font-weight: 800;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .main-content {
            padding: 1rem 5% 4rem;
            min-height: calc(100vh - 120px);
        }

        /* Cart Drawer (超圓潤果凍感) */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(107, 91, 91, 0.3);
            backdrop-filter: blur(5px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .cart-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .cart-drawer {
            position: fixed;
            top: 15px;
            right: -420px;
            width: calc(100% - 30px);
            max-width: 400px;
            height: calc(100vh - 30px);
            background: var(--card-bg);
            z-index: 1000;
            border-radius: 30px;
            box-shadow: -10px 0 40px rgba(255, 182, 193, 0.3);
            border: 4px solid white;
            transition: right 0.6s var(--spring-bounce);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .cart-drawer.active {
            right: 15px;
        }

        .cart-header {
            padding: 1.5rem 2rem;
            background: var(--brand-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--brand-dark);
            font-weight: 800;
        }

        .close-cart {
            background: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--brand-dark);
            box-shadow: 0 2px 8px rgba(255, 182, 193, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            transition: transform 0.3s var(--spring-bounce);
        }

        .close-cart:hover {
            transform: scale(1.1) rotate(90deg);
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }
        
        /* 捲軸美化 */
        .cart-items::-webkit-scrollbar {
            width: 8px;
        }
        .cart-items::-webkit-scrollbar-thumb {
            background: var(--brand-light);
            border-radius: 10px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            background: var(--bg-color);
            padding: 0.8rem;
            border-radius: 20px;
            border: 2px solid white;
            box-shadow: 0 4px 12px rgba(255, 182, 193, 0.15);
        }

        .cart-item img {
            width: 65px;
            height: 65px;
            border-radius: 14px;
            object-fit: cover;
            background: #fff;
            padding: 2px;
            border: 2px solid var(--brand-light);
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-title {
            margin: 0 0 0.3rem 0;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .cart-item-price {
            color: var(--brand-dark);
            font-weight: 700;
            margin: 0;
            font-size: 1rem;
        }

        .cart-item-remove {
            color: white;
            background: #ffb6c1;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.75rem;
            padding: 4px 10px;
            font-weight: bold;
            transition: all 0.2s;
        }
        .cart-item-remove:hover {
            background: #ff9ebd;
            transform: scale(1.05);
        }

        .cart-footer {
            padding: 1.5rem 2rem;
            background: white;
            border-top: 3px dashed var(--brand-light);
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--brand-dark);
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--brand-color), var(--brand-dark));
            color: white;
            border: none;
            padding: 1.2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.3s var(--spring-bounce), box-shadow 0.3s;
            box-shadow: 0 6px 20px rgba(255, 182, 193, 0.6);
            letter-spacing: 1px;
        }

        .checkout-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(255, 182, 193, 0.8);
        }

        .empty-cart-msg {
            text-align: center;
            color: var(--text-muted);
            margin-top: 3rem;
            font-size: 1.1rem;
        }
        
        /* Toast 通知 - 粉彩氣泡 */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: linear-gradient(135deg, var(--brand-light), white);
            color: var(--brand-dark);
            font-weight: 800;
            padding: 15px 30px;
            border-radius: 50px;
            border: 3px solid white;
            box-shadow: 0 8px 25px rgba(255, 182, 193, 0.4);
            opacity: 0;
            transition: all 0.5s var(--spring-bounce);
            z-index: 2000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast::before { content: '🎀 '; font-size: 1.2rem; }
        
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Chat UI - 魔法對話助手 */
        .chatbot-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--accent-mint), #8ee0c2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(168, 230, 207, 0.6);
            border: 4px solid white;
            cursor: pointer;
            z-index: 998;
            transition: transform 0.4s var(--spring-bounce);
        }

        .chatbot-fab:hover {
            transform: translateY(-5px) scale(1.1) rotate(10deg);
        }

        .chat-window {
            position: fixed;
            bottom: 110px;
            right: 30px;
            width: 360px;
            height: 550px;
            max-height: calc(100vh - 140px);
            background: var(--bg-color);
            border-radius: 30px;
            border: 4px solid white;
            box-shadow: 0 15px 40px rgba(255, 182, 193, 0.35);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1001;
            transform-origin: bottom right;
            transform: scale(0);
            opacity: 0;
            pointer-events: none;
            transition: all 0.5s var(--spring-bounce);
        }

        .chat-window.active {
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--brand-color), var(--brand-dark));
            color: white;
            padding: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .chat-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.1);
        }

        .close-chat {
            background: rgba(255,255,255,0.2);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: white;
            font-size: 1.3rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .close-chat:hover { background: rgba(255,255,255,0.4); transform: scale(1.1); }

        .chat-messages {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        
        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--brand-light); border-radius: 10px; }

        .message {
            max-width: 75%;
            padding: 0.8rem 1.2rem;
            border-radius: 20px;
            line-height: 1.5;
            font-size: 0.95rem;
            word-break: break-all;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            position: relative;
        }

        .message.bot {
            background: white;
            color: var(--text-main);
            border: 2px solid var(--brand-light);
            border-bottom-left-radius: 6px;
            align-self: flex-start;
        }

        .message.user {
            background: var(--brand-dark);
            color: white;
            border-bottom-right-radius: 6px;
            align-self: flex-end;
            box-shadow: 0 4px 12px rgba(240, 140, 179, 0.4);
        }

        .chat-input-area {
            padding: 1.2rem;
            background: white;
            border-top: 2px dashed var(--brand-light);
            display: flex;
            gap: 10px;
        }

        .chat-input-area input {
            flex-grow: 1;
            background: var(--bg-color);
            border: 2px solid var(--brand-light);
            border-radius: 50px;
            padding: 0.8rem 1.2rem;
            outline: none;
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-main);
            transition: all 0.3s;
        }

        .chat-input-area input:focus {
            border-color: var(--brand-dark);
            box-shadow: 0 0 0 3px rgba(255, 182, 193, 0.2);
            background: white;
        }

        .chat-send-btn {
            background: var(--brand-dark);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s var(--spring-bounce);
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(240, 140, 179, 0.4);
        }

        .chat-send-btn:hover {
            transform: scale(1.1) translateY(-2px);
        }

        .typing-indicator {
            display: none;
            padding: 0.8rem 1.2rem;
            background: white;
            border: 2px solid var(--brand-light);
            border-radius: 20px;
            border-bottom-left-radius: 6px;
            align-self: flex-start;
            gap: 6px;
        }

        .typing-indicator.active {
            display: flex;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: var(--brand-dark);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
            opacity: 0.6;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('shop.index') }}" class="logo">🎀 FRESH.</a>
        <div class="cart-icon" onclick="toggleCart()">
            <!-- SVG Cart Icon -->
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span class="cart-count" id="cart-count">0</span>
        </div>
    </nav>

    <main class="main-content">
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
                ✨ 購物小幫手 🎀
            </h3>
            <button class="close-chat" onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot">您好喵！我是 FRESH 購物小幫手 ✨ 隨時告訴我想找什麼魔法商品吧～🎀</div>
            <div class="typing-indicator" id="typing-indicator">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="問我任何問題..." onkeypress="handleChatKeyPress(event)">
            <button class="chat-send-btn" onclick="sendChatMessage()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>

    <!-- Cart Overlay & Drawer -->
    <div class="cart-overlay" id="cart-overlay" onclick="toggleCart()"></div>
    <div class="cart-drawer" id="cart-drawer">
        <div class="cart-header">
            <h2>你的購物車</h2>
            <button class="close-cart" onclick="toggleCart()">&times;</button>
        </div>
        <div class="cart-items" id="cart-items-container">
            <!-- 購物車內項目會透過 JS 渲染到這裡 -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>總計</span>
                <span id="cart-total-price">$0.00</span>
            </div>
            <button class="checkout-btn" onclick="checkout()">前往結帳</button>
        </div>
    </div>
    
    <div class="toast" id="toast">已加入購物車！</div>

    <!-- 購物車 Vanilla JS 邏輯 -->
    <script>
        let cart = JSON.parse(localStorage.getItem('fresh_cart')) || [];

        function saveCart() {
            localStorage.setItem('fresh_cart', JSON.stringify(cart));
            renderCart();
        }

        function addToCart(product) {
            // 尋找是否已存在
            const existing = cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({ ...product, quantity: 1 });
            }
            saveCart();
            showToast();
            document.querySelector('.cart-icon').style.transform = "scale(1.2)";
            setTimeout(() => {
                document.querySelector('.cart-icon').style.transform = "scale(1)";
            }, 200);
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            saveCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const countBtn = document.getElementById('cart-count');
            const totalPriceEl = document.getElementById('cart-total-price');
            
            // 計算總數與金額
            let totalCount = 0;
            let totalPrice = 0;
            
            container.innerHTML = '';

            if (cart.length === 0) {
                container.innerHTML = '<p class="empty-cart-msg">購物車目前是空的喔，去逛逛吧！</p>';
            } else {
                cart.forEach(item => {
                    totalCount += item.quantity;
                    totalPrice += item.price * item.quantity;

                    const itemEl = document.createElement('div');
                    itemEl.className = 'cart-item';
                    itemEl.innerHTML = `
                        <img src="${item.image_url || 'data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='}" alt="${item.name}">
                        <div class="cart-item-info">
                            <h3 class="cart-item-title">${item.name}</h3>
                            <p class="cart-item-price">$${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                            <button class="cart-item-remove" onclick="removeFromCart(${item.id})">移除</button>
                        </div>
                    `;
                    container.appendChild(itemEl);
                });
            }

            countBtn.innerText = totalCount;
            totalPriceEl.innerText = '$' + totalPrice.toFixed(2);
        }

        function toggleCart() {
            document.getElementById('cart-drawer').classList.toggle('active');
            document.getElementById('cart-overlay').classList.toggle('active');
        }

        function checkout() {
            if(cart.length === 0) {
                alert('購物車沒有商品無法結帳喔！');
                return;
            }
            alert('這是一個展示 Demo 網站，無法實際結帳。感謝您的測試！');
            cart = [];
            saveCart();
            toggleCart();
        }
        
        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }

        // 初始化
        renderCart();

        // == Chatbot JS 邏輯 ==
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
                const response = await fetch('/chat', {
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

                // 判斷是否觸發了購物車操作
                if (data.action === 'ADD_TO_CART' && data.product) {
                    addToCart(data.product);
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
