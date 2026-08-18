<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\TableController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('profile', [ProfileController::class, 'show']);

    Route::get('menu', [MenuController::class, 'index']);
    Route::get('menu/{menuItem}', [MenuController::class, 'show']);

    Route::get('tables', [TableController::class, 'index']);
    Route::post('tables', [TableController::class, 'store']);
    Route::get('tables/{table}', [TableController::class, 'show']);
    Route::put('tables/{table}', [TableController::class, 'update']);
    Route::delete('tables/{table}', [TableController::class, 'destroy']);

    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{reservation}', [ReservationController::class, 'show']);

    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

    Route::post('payments/callback', [PaymentController::class, 'callback']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);

    Route::post('sync', [SyncController::class, 'sync']);
});
