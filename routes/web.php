<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route(
            match (auth()->user()->role) {
                UserRole::Admin => 'admin.dashboard',
                UserRole::Kasir => 'pos.index',
                UserRole::Dapur => 'kitchen.index',
                default => 'login',
            }
        );
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('menu-categories', MenuCategoryController::class);
        Route::resource('menu-items', MenuItemController::class);
        Route::resource('tables', TableController::class);
    });

    Route::middleware('role:admin,kasir')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/orders', [PosController::class, 'store'])->name('orders.store');
        Route::put('/orders/{order}', [PosController::class, 'update'])->name('orders.update');
        Route::get('/orders/active', [PosController::class, 'getActiveOrders'])->name('orders.active');
        Route::post('/orders/{order}/complete', [PosController::class, 'completeOrder'])->name('orders.complete');
        Route::post('/orders/{order}/cancel', [PosController::class, 'cancelOrder'])->name('orders.cancel');
    });

    Route::middleware('role:admin,dapur')->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/orders', [KitchenController::class, 'getOrders'])->name('orders');
        Route::patch('/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('orders.status');
    });

    Route::middleware('role:admin,kasir')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/popular-menu', [ReportController::class, 'popularMenu'])->name('popular-menu');
        Route::get('/tables', [ReportController::class, 'tables'])->name('tables');
    });
});
