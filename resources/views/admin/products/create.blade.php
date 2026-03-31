@extends('admin.layout')

@section('content')
<div class="header">
    <h1>🌟 新增魔法商品</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">返回寶庫</a>
</div>

<div class="card">
    <form action="{{ route('admin.products.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">商品名稱 *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            @error('name')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="price">價格 *</label>
            <input type="number" name="price" id="price" step="0.01" value="{{ old('price') }}" required>
            @error('price')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="image_url">圖片網址 (URL)</label>
            <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}">
            @error('image_url')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="description">商品描述</label>
            <textarea name="description" id="description">{{ old('description') }}</textarea>
            @error('description')<span style="color: red; font-size: 0.8rem;">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn">✨ 施放魔法儲存</button>
    </form>
</div>
@endsection
