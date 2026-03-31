@extends('shop.layout')

@section('content')
<style>
    .hero {
        text-align: center;
        margin-bottom: 4rem;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, var(--brand-light) 0%, white 100%);
        border-radius: 40px;
        border: 4px solid white;
        box-shadow: 0 10px 30px rgba(255, 182, 193, 0.2);
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '✨';
        position: absolute;
        top: 20px;
        left: 30px;
        font-size: 2rem;
        animation: float 3s ease-in-out infinite;
    }
    .hero::after {
        content: '🌟';
        position: absolute;
        bottom: 20px;
        right: 40px;
        font-size: 1.5rem;
        animation: float 4s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .hero h1 {
        font-size: 2.8rem;
        color: var(--brand-dark);
        margin: 0 0 1rem 0;
        font-weight: 800;
        text-shadow: 2px 2px 0px white;
    }

    .hero p {
        color: var(--text-main);
        font-size: 1.15rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 700;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2.5rem;
    }

    .product-card {
        background: var(--card-bg);
        border-radius: 30px;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 10px 25px rgba(255, 182, 193, 0.3);
        transition: all 0.4s var(--spring-bounce);
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 15px 35px rgba(255, 182, 193, 0.5);
    }

    .product-img-wrapper {
        width: 100%;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        position: relative;
        background: var(--brand-light);
        border-bottom: 4px solid white;
    }

    .product-img-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-img-wrapper img {
        transform: scale(1.08);
    }

    .product-info {
        padding: 1.8rem;
    }

    .product-title {
        font-size: 1.3rem;
        margin: 0 0 0.5rem 0;
        color: var(--brand-dark);
        font-weight: 800;
    }

    .product-price {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 1rem 0;
    }
    
    .product-desc {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-weight: 700;
    }

    .add-to-cart-btn {
        width: 100%;
        background: linear-gradient(135deg, white, var(--brand-light));
        color: var(--brand-dark);
        border: 3px solid var(--brand-color);
        padding: 0.8rem;
        border-radius: 50px;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s var(--spring-bounce);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 10px rgba(255, 182, 193, 0.2);
    }

    .add-to-cart-btn:hover {
        background: linear-gradient(135deg, var(--brand-color), var(--brand-dark));
        color: white;
        border-color: var(--brand-dark);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 182, 193, 0.4);
    }

    
</style>

<div class="hero">
    <h1>✨ 歡迎來到蘿莉塔魔法商店 🎀</h1>
    <p>在這裡，我們為您準備了最可愛、最夢幻的選物！點擊帶走喜歡的寶貝吧 🌟</p>
</div>

@if($products->count() > 0)
    <div class="products-grid">
        @foreach($products as $product)
        <div class="product-card">
            <div class="product-img-wrapper">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                @else
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1.5rem; text-shadow: 1px 1px 0px rgba(0,0,0,0.1);">🎀 FRESH</div>
                @endif
            </div>
            <div class="product-info">
                <h2 class="product-title">{{ $product->name }}</h2>
                <p class="product-price">${{ number_format($product->price, 2) }}</p>
                <p class="product-desc">{{ $product->description ?? '體驗軟萌可愛的魔力，讓它陪伴你的每一天～✨' }}</p>
                <!-- 加入購物車按鈕，以 JSON 格式帶參數給 addToCart 函式 -->
                <button class="add-to-cart-btn" onclick='addToCart({!! json_encode($product) !!})'>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    加入購物車
                </button>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 5rem 0; color: var(--text-muted); font-weight: 700;">
        <p>嗚嗚...目前架上還沒有魔法道具喔，請等待店長補貨！ 🥺</p>
        <a href="{{ route('admin.products.index') }}" style="color: var(--brand-dark); text-decoration: none; border-bottom: 2px dashed currentColor; font-weight: 800;">使用店長鑰匙前往後台 🔑</a>
    </div>
@endif

@endsection
