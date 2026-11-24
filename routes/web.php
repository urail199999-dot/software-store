<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store');
Route::post('/checkout/{product}', [StoreController::class, 'checkout'])->middleware('throttle:10,1')->name('checkout');
Route::get('/orders/{token}', [StoreController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('order.show');
Route::get('/orders/{token}/download', [StoreController::class, 'download'])->where('token', '[A-Za-z0-9]{64}')->name('order.download');
