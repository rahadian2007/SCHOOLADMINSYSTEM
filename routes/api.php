<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::prefix('/v1.0')->group(function () {
    Route::post('/access-token/b2b', [\App\Http\Controllers\Auth\AccessTokenController::class, 'issueToken']);
    Route::post('/validate-token', [\App\Http\Controllers\Auth\AccessTokenController::class, 'validateToken']);
    Route::post('/transfer-va/inquiry', [\App\Http\Controllers\SnapVaInboundController::class, 'transferVaInquiry']);
    Route::post('/transfer-va/payment', [\App\Http\Controllers\SnapVaInboundController::class, 'transferVaPayment']);
});