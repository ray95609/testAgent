<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ChatbotController;

Route::get('/', [ProductController::class, 'shopIndex'])->name('shop.index');

// 前台 Chatbot 對話 API
Route::post('/chat', [ChatbotController::class, 'chat'])->name('shop.chat');

Route::prefix('admin')->name('admin.')->group(function () {
    // 預設重導向到商品列表
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    });
    
    Route::resource('products', ProductController::class);
    
    // 後台 Chatbot 對話 API
    Route::post('/chat', [ChatbotController::class, 'adminChat'])->name('chat');
});
