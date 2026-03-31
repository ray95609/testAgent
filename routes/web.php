<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'shopIndex'])->name('shop.index');

Route::prefix('admin')->name('admin.')->group(function () {
    // 預設重導向到商品列表
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    });
    
    Route::resource('products', ProductController::class);
});
