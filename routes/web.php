<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store');
Route::get('/catalog', [StoreController::class, 'catalog'])->name('catalog');
Route::get('/catalog/{category:slug}', [StoreController::class, 'catalog'])->name('category.show');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('product.show');
Route::post('/checkout/{product}', [StoreController::class, 'checkout'])->middleware('throttle:10,1')->name('checkout');
Route::get('/orders/{token}', [StoreController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('order.show');
Route::get('/orders/{token}/download', [StoreController::class, 'download'])->where('token', '[A-Za-z0-9]{64}')->name('order.download');

Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/login', [CustomerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/forgot-password', [CustomerAuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [CustomerAuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [CustomerAuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [CustomerAuthController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    Route::get('/email/verify', [CustomerAuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [CustomerAuthController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [CustomerAuthController::class, 'resend'])->middleware('throttle:1,1')->name('verification.send');
    Route::middleware('verified')->group(function () {
        Route::get('/account', [AccountController::class, 'index'])->name('account');
        Route::get('/account/orders/{order}', [AccountController::class, 'show'])->name('account.order');
        Route::get('/account/orders/{order}/download', [AccountController::class, 'download'])->name('account.download');
    });
});
