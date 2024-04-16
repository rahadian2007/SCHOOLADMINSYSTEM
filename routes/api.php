<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/v1.0')->group(function () {
    Route::post('/access-token/b2b', [\App\Http\Controllers\Auth\AccessTokenController::class, 'issueToken']);
    Route::post('/validate-token', [\App\Http\Controllers\Auth\AccessTokenController::class, 'validateToken']);
    Route::post('/transfer-va/inquiry', [\App\Http\Controllers\SnapVaInboundController::class, 'transferVaInquiry']);
    Route::post('/transfer-va/payment', [\App\Http\Controllers\SnapVaInboundController::class, 'transferVaPayment']);
});

Route::prefix('/v2.0')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AppAccessController::class, 'login']);
    Route::get('/profile', [\App\Http\Controllers\API\AccountController::class, 'profile']);
    Route::get('/product', [\App\Http\Controllers\API\ProductController::class, 'index']);
    Route::get('/category', [\App\Http\Controllers\API\ProductController::class, 'categories']);
    Route::get('/order', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::post('/order', [\App\Http\Controllers\API\OrderController::class, 'store']);
});