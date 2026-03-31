@extends('admin.layout')

@section('content')
<div class="header">
    <h1>商品列表</h1>
    <a href="{{ route('admin.products.create') }}" class="btn">+ 新增商品</a>
</div>

<div class="card">
    @if($products->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>圖片</th>
                    <th>商品名稱</th>
                    <th>價格</th>
                    <th>建立時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-preview">
                        @else
                            <div class="img-preview" style="background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-size:12px; color:#718096;">無圖</div>
                        @endif
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn">編輯</a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('確定要刪除這個商品嗎？');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">刪除</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
            @if ($products->onFirstPage())
                <span class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed;">上一頁</span>
            @else
                <a href="{{ $products->previousPageUrl() }}" class="btn btn-secondary">上一頁</a>
            @endif

            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="btn btn-secondary">下一頁</a>
            @else
                <span class="btn btn-secondary" style="opacity: 0.5; cursor: not-allowed;">下一頁</span>
            @endif
        </div>
    @else
        <p style="text-align:center; color: var(--text-light);">目前還沒有商品喔，請點擊上方新增商品。</p>
    @endif
</div>
@endsection
