@extends('shop.layout')

@section('content')
<style>
    .hero {
        text-align: center;
        margin-bottom: 4rem;
        padding: 3rem 0;
        background: linear-gradient(135deg, var(--brand-light) 0%, #ffffff 100%);
        border-radius: 20px;
    }

    .hero h1 {
        font-size: 2.5rem;
        color: var(--brand-dark);
        margin: 0 0 1rem 0;
    }

    .hero p {
        color: var(--text-muted);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }

    .product-card {
        background: var(--card-bg);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(129, 212, 250, 0.15);
    }

    .product-img-wrapper {
        width: 100%;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        position: relative;
        background: var(--brand-light);
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
        transform: scale(1.05);
    }

    .product-info {
        padding: 1.5rem;
    }

    .product-title {
        font-size: 1.25rem;
        margin: 0 0 0.5rem 0;
        color: var(--text-main);
    }

    .product-price {
        font-size: 1.1rem;
        font-weight: bold;
        color: var(--brand-dark);
        margin: 0 0 1rem 0;
    }
    
    .product-desc {
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .add-to-cart-btn {
        width: 100%;
        background: transparent;
        color: var(--brand-dark);
        border: 2px solid var(--brand-dark);
        padding: 0.75rem;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    .add-to-cart-btn:hover {
        background: var(--brand-dark);
        color: white;
    }
    
</style>

<div class="hero">
    <h1>清新、簡單、美好。</h1>
    <p>在這裡，我們為您挑選了最純粹、無負擔的優質生活選物。點擊喜歡的商品加入購物車吧！</p>
</div>

@if($products->count() > 0)
    <div class="products-grid">
        @foreach($products as $product)
        <div class="product-card">
            <div class="product-img-wrapper">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                @else
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1.5rem;">FRESH</div>
                @endif
            </div>
            <div class="product-info">
                <h2 class="product-title">{{ $product->name }}</h2>
                <p class="product-price">${{ number_format($product->price, 2) }}</p>
                <p class="product-desc">{{ $product->description ?? '體驗極簡之美，點滴好物構築清新生活。' }}</p>
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
    <div style="text-align: center; padding: 5rem 0; color: var(--text-muted);">
        <p>目前還沒有任何商品喔，請去後台新增！</p>
        <a href="{{ route('admin.products.index') }}" style="color: var(--brand-dark); text-decoration: none; border-bottom: 1px solid currentColor;">前往後台</a>
    </div>
@endif

@endsection
