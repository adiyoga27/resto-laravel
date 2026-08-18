<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\StockLogController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\QzSignController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/qz/sign', QzSignController::class)->name('qz.sign');

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
        Route::get('/export', [DashboardController::class, 'export'])->name('dashboard.export');
        Route::resource('users', UserController::class);
        Route::resource('menu-categories', MenuCategoryController::class);
        Route::resource('menu-items', MenuItemController::class);
        Route::resource('tables', TableController::class);
        Route::resource('ingredients', IngredientController::class);
        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
        Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
        Route::get('/stock-logs', [StockLogController::class, 'index'])->name('stock-logs.index');
        Route::get('/stock-logs/create', [StockLogController::class, 'create'])->name('stock-logs.create');
        Route::post('/stock-logs', [StockLogController::class, 'store'])->name('stock-logs.store');
        Route::get('/stock-logs/production', [StockLogController::class, 'createProduction'])->name('stock-logs.create-production');
        Route::post('/stock-logs/production', [StockLogController::class, 'storeProduction'])->name('stock-logs.store-production');
        Route::get('/stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
        Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
        Route::post('/stock-opnames', [StockOpnameController::class, 'store'])->name('stock-opnames.store');
        Route::get('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opnames.show');
        Route::post('/stock-opnames/{stockOpname}/post', [StockOpnameController::class, 'post'])->name('stock-opnames.post');
        Route::delete('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy'])->name('stock-opnames.destroy');
    });

    Route::middleware('role:admin,kasir')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/orders', [PosController::class, 'store'])->name('orders.store');
        Route::put('/orders/{order}', [PosController::class, 'update'])->name('orders.update');
        Route::get('/orders/active', [PosController::class, 'getActiveOrders'])->name('orders.active');
        Route::get('/orders/{order}/receipt', [ReceiptController::class, 'show'])->name('orders.receipt');
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
        Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
        Route::get('/popular-menu', [ReportController::class, 'popularMenu'])->name('popular-menu');
        Route::get('/popular-menu/export', [ReportController::class, 'exportPopularMenu'])->name('popular-menu.export');
        Route::get('/tables', [ReportController::class, 'tables'])->name('tables');
        Route::get('/tables/export', [ReportController::class, 'exportTables'])->name('tables.export');
        Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow');
        Route::get('/cash-flow/create', [CashFlowController::class, 'create'])->name('cash-flow.create');
        Route::post('/cash-flow', [CashFlowController::class, 'store'])->name('cash-flow.store');
        Route::get('/cash-flow/posting/transaksi', [CashFlowController::class, 'posting'])->name('cash-flow.posting');
        Route::post('/cash-flow/posting/transaksi', [CashFlowController::class, 'postTransaction'])->name('cash-flow.post-transaction');
        Route::get('/cash-flow/export', [CashFlowController::class, 'export'])->name('cash-flow.export');
        Route::get('/cash-flow/{cashFlow}/edit', [CashFlowController::class, 'edit'])->name('cash-flow.edit');
        Route::put('/cash-flow/{cashFlow}', [CashFlowController::class, 'update'])->name('cash-flow.update');
        Route::delete('/cash-flow/{cashFlow}', [CashFlowController::class, 'destroy'])->name('cash-flow.destroy');
        Route::post('/cash-flow/{cashFlow}/post', [CashFlowController::class, 'post'])->name('cash-flow.post');
        Route::post('/cash-flow/{cashFlow}/unpost', [CashFlowController::class, 'unpost'])->name('cash-flow.unpost');
    });

    Route::middleware('role:admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales/{order}', [ReportController::class, 'show'])->name('sales.show');
        Route::put('/sales/{order}', [ReportController::class, 'updateOrder'])->name('sales.update');
        Route::delete('/sales/{order}', [ReportController::class, 'destroy'])->name('sales.destroy');
    });
});
