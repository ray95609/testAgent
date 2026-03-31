<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>清新選物店</title>
    <style>
        :root {
            --brand-color: #81D4FA; /* 預設為淺藍/薄荷系 */
            --brand-dark: #4fc3f7;
            --brand-light: #e1f5fe;
            --text-main: #37474F;
            --text-muted: #90A4AE;
            --bg-color: #F8FDFF;
            --card-bg: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Helvetica Neue', 'PingFang TC', '微軟正黑體', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(129, 212, 250, 0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--brand-dark);
            text-decoration: none;
            letter-spacing: 2px;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .cart-icon:hover {
            background: var(--brand-light);
        }

        .cart-count {
            position: absolute;
            top: 0;
            right: 0;
            background: #ff7043;
            color: white;
            font-size: 0.75rem;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transform: translate(20%, -20%);
        }

        .main-content {
            padding: 3rem 5%;
            min-height: calc(100vh - 80px);
        }

        /* Cart Drawer */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .cart-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: -400px;
            width: 100%;
            max-width: 400px;
            height: 100%;
            background: var(--card-bg);
            z-index: 1000;
            box-shadow: -5px 0 20px rgba(0,0,0,0.05);
            transition: right 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            display: flex;
            flex-direction: column;
        }

        .cart-drawer.active {
            right: 0;
        }

        .cart-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--brand-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--brand-dark);
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .close-cart:hover {
            color: var(--text-main);
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: var(--brand-light);
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-title {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
        }

        .cart-item-price {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.9rem;
        }

        .cart-item-remove {
            color: #ff7043;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 0;
        }

        .cart-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--brand-light);
            background: #fafafa;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        .checkout-btn {
            width: 100%;
            background: var(--brand-dark);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(79, 195, 247, 0.4);
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 195, 247, 0.6);
        }

        .empty-cart-msg {
            text-align: center;
            color: var(--text-muted);
            margin-top: 2rem;
        }
        
        /* Toast 通知 */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--brand-dark);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 2000;
        }
        
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Server-side error fixes & Chat UI */
        .chatbot-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--brand-dark);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(79, 195, 247, 0.4);
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
            background: var(--card-bg);
            border-radius: 20px;
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
        }

        .chat-window.active {
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .chat-header {
            background: var(--brand-dark);
            color: white;
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
            color: white;
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
            background: #fafafa;
        }

        .message {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            line-height: 1.4;
            font-size: 0.95rem;
            word-break: break-all;
        }

        .message.bot {
            background: white;
            border: 1px solid var(--brand-light);
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }

        .message.user {
            background: var(--brand-dark);
            color: white;
            border-bottom-right-radius: 4px;
            align-self: flex-end;
        }

        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid var(--brand-light);
            display: flex;
            gap: 10px;
            background: white;
        }

        .chat-input-area input {
            flex-grow: 1;
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            outline: none;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .chat-input-area input:focus {
            border-color: var(--brand-dark);
        }

        .chat-send-btn {
            background: var(--brand-dark);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .chat-send-btn:hover {
            transform: scale(1.1);
        }

        .typing-indicator {
            display: none;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid var(--brand-light);
            border-radius: 18px;
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

    <nav class="navbar">
        <a href="{{ route('shop.index') }}" class="logo">FRESH.</a>
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line>
                </svg>
                購物小幫手
            </h3>
            <button class="close-chat" onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot">您好！我是 FRESH 的購物小幫手，隨時可以告訴我您想找什麼商品，或是直接請我幫您加入購物車喔！</div>
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
