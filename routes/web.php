<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store');
Route::get('/catalog', [StoreController::class, 'catalog'])->name('catalog');
Route::get('/catalog/{category:slug}', [StoreController::class, 'catalog'])->name('category.show');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('product.show');
Route::post('/checkout/{product}', [StoreController::class, 'checkout'])->middleware('throttle:10,1')->name('checkout');
Route::get('/orders/{token}', [StoreController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('order.show');
Route::get('/orders/{token}/download', [StoreController::class, 'download'])->where('token', '[A-Za-z0-9]{64}')->name('order.download');
