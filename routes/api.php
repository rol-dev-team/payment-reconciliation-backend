<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaymentChannelController;
use App\Http\Controllers\API\WalletController;

// PUBLIC routes
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED routes (require token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // User CRUD
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // Example protected route
    Route::get('/profile', function () {
        return auth()->user();
    });

    // Payment Channel CRUD
    Route::prefix('payment-channels')->group(function () {
        Route::get('/', [PaymentChannelController::class, 'index']);
        Route::post('/', [PaymentChannelController::class, 'store']);
        Route::get('{paymentChannel}', [PaymentChannelController::class, 'show']);
        Route::put('{paymentChannel}', [PaymentChannelController::class, 'update']);
        Route::delete('{paymentChannel}', [PaymentChannelController::class, 'destroy']);
    });

    // Wallet CRUD
    Route::prefix('wallets')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::post('/', [WalletController::class, 'store']);
        Route::get('{wallet}', [WalletController::class, 'show']);
        Route::put('{wallet}', [WalletController::class, 'update']);
        Route::delete('{wallet}', [WalletController::class, 'destroy']);
    });
});

    // // without Middleware api list
    // // User CRUD
    // Route::prefix('users')->group(function () {
    //     Route::get('/', [UserController::class, 'index']);
    //     Route::post('/', [UserController::class, 'store']);
    //     Route::get('{id}', [UserController::class, 'show']);
    //     Route::put('{id}', [UserController::class, 'update']);
    //     Route::delete('{id}', [UserController::class, 'destroy']);
    // });

    // // Payment Channel CRUD
    // Route::prefix('payment-channels')->group(function () {
    //     Route::get('/', [PaymentChannelController::class, 'index']);
    //     Route::post('/', [PaymentChannelController::class, 'store']);
    //     Route::get('{paymentChannel}', [PaymentChannelController::class, 'show']);
    //     Route::put('{paymentChannel}', [PaymentChannelController::class, 'update']);
    //     Route::delete('{paymentChannel}', [PaymentChannelController::class, 'destroy']);
    // });

    // // Wallet CRUD
    // Route::prefix('wallets')->group(function () {
    //     Route::get('/', [WalletController::class, 'index']);
    //     Route::post('/', [WalletController::class, 'store']);
    //     Route::get('{wallet}', [WalletController::class, 'show']);
    //     Route::put('{wallet}', [WalletController::class, 'update']);
    //     Route::delete('{wallet}', [WalletController::class, 'destroy']);
    // });
