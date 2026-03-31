@extends('admin.layout')

@section('content')
<div class="header">
    <h1>編輯商品</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">返回列表</a>
</div>

<div class="card">
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">商品名稱 *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
            @error('name')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="price">價格 *</label>
            <input type="number" name="price" id="price" step="0.01" value="{{ old('price', $product->price) }}" required>
            @error('price')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="image_url">圖片網址 (URL)</label>
            <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url', $product->image_url) }}">
            @error('image_url')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
            
            @if($product->image_url)
                <div style="margin-top:10px;">
                    <img src="{{ $product->image_url }}" alt="目前圖片預覽" style="max-width: 150px; border-radius:4px;">
                </div>
            @endif
        </div>

        <div class="form-group">
            <label for="description">商品描述</label>
            <textarea name="description" id="description">{{ old('description', $product->description) }}</textarea>
            @error('description')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn">更新儲存</button>
    </form>
</div>
@endsection
