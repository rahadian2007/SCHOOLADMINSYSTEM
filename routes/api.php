<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DaftarVendorController;


// untuk uji coba CRUD
use App\Http\Controllers\ID_WargaController;
//

Route::apiResource('daftar-vendor', DaftarVendorController::class);


//untuk uji coba CRUD

Route::post('/ID-warga', [ID_WargaController::class, 'store']);
Route::get('/ID-warga', [ID_WargaController::class, 'index']);
Route::get('/ID-warga/{id}', [ID_WargaController::class, 'show']);
Route::put('/ID-warga/{id}', [ID_WargaController::class, 'update']);
Route::delete('/ID-warga/{id}', [ID_WargaController::class, 'destroy']);



//


// Contoh route lain dengan prefix (sesuaikan jika diperlukan)
Route::prefix('v1.0')->group(function () {
    Route::post('/access-token/b2b', [\App\Http\Controllers\Auth\AccessTokenController::class, 'methodName']);
    Route::post('/validate-token', [\App\Http\Controllers\Auth\AccessTokenController::class, 'methodName']);
    Route::post('/transfer-va/inquiry', [\App\Http\Controllers\SnapVaInboundController::class, 'methodName']);
    Route::post('/transfer-va/payment', [\App\Http\Controllers\SnapVaInboundController::class, 'methodName']);
});

Route::prefix('v2.0')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AppAccessController::class, 'methodName']);
    Route::get('/profile', [\App\Http\Controllers\API\AccountController::class, 'methodName']);
    Route::get('/product', [\App\Http\Controllers\API\ProductController::class, 'methodName']);
    Route::get('/category', [\App\Http\Controllers\API\ProductController::class, 'methodName']);
});
