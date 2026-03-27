<?php

use App\Http\Controllers\Api\Auth\CasherAuthController;
use App\Http\Controllers\Api\Invoice\SyncController;
use App\Http\Controllers\Api\PullData\PullDataController;
use App\Http\Controllers\Web\Admin\CasherController;
use App\Http\Controllers\Web\Admin\Invoice\InvoiceController;
use App\Http\Controllers\Web\auth\LoginController;
use App\Http\Controllers\Web\auth\LogoutController;
use App\Http\Controllers\Web\auth\ResetpassController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\Notification\NotificationController;
use App\Http\Controllers\Web\Admin\Product\CompatibilityController;
use App\Http\Controllers\Web\Admin\Product\ProductController;
use App\Http\Controllers\Web\Admin\Product\InventoryController;
use App\Http\Controllers\Web\Admin\Product\SearchController;
use App\Http\Controllers\Web\Admin\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\CustomerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/cashier/login', [CasherAuthController::class, 'login'])->name('casher.login');
Route::post('/forget-password', [ResetpassController::class, 'forgetPassword']);
Route::post('/reset-password', [ResetpassController::class, 'resetForgottenPassword']);

/*
|--------------------------------------------------------------------------
| Cashier Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'CheckCashier'])->group(function () {

    // Auth
    Route::post('/cashier/logout', [CasherAuthController::class, 'logout'])->name('casher.logout');


    // Pull Data (first login or manual sync)
    Route::get('pull-data', [PullDataController::class, 'pull']);

    // Customers
    Route::post('/customers', [CustomerController::class, 'store']);

    // Sync
    Route::prefix('sync')->group(function () {
        Route::post('sale', [SyncController::class, 'receiveSale']);              // إرسال فاتورة أو دفعة
        Route::post('check', [SyncController::class, 'checkAvailability']);       // التحقق من الكمية
        Route::post('adjust-request', [SyncController::class, 'requestAdjust']); // طلب تعديل مخزون
    });
});

/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'CheckAdmin'])->group(function () {

    // Auth & Passwords
    Route::put('/change-password', [ResetpassController::class, 'changePassword'])->name('change-password');
    Route::post('/logout', [LogoutController::class, 'logout']);

    // Categories
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);
    Route::get('categories/{category}/related', [CategoryController::class, 'related']);
    Route::apiResource('categories', CategoryController::class);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Cashiers Management
    Route::get('/cashiers', [CasherController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/create', [CasherController::class, 'store'])->name('cashier.store');
    Route::put('/cashier/update/{user}', [CasherController::class, 'update'])->name('cashier.update');
    Route::post('/cashier/disable/{user}', [CasherController::class, 'disable'])->name('cashier.delete');
    Route::post('/cashier/enable/{user}', [CasherController::class, 'enable'])->name('cashier.enable');

    // Products [CRUD]
    Route::get('categories/{category}/products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // Inventory & Reports [Products]
    Route::prefix('inventory')->group(function () {
        Route::post('products/{product}/restock', [InventoryController::class, 'restock']);
        Route::post('products/{product}/adjust', [InventoryController::class, 'adjust']);
        Route::get('reports/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('reports/profit-analysis', [InventoryController::class, 'profitAnalysis']);
        Route::get('reports/adjustments', [InventoryController::class, 'reportsAdjust']);
        Route::get('reports/valuation', [InventoryController::class, 'inventoryValuation']);


        // Stock Adjustment Requests (from Cashiers)
        Route::get('adjustment-requests', [InventoryController::class, 'pendingRequests']);
        Route::post('adjustment-requests/{adjustmentRequest}/review', [InventoryController::class, 'reviewRequest']);
    });

    // Invoices (Admin View)
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
    });

    // Notifications Routes (Admin Panel)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);

        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });

    // Customers
    Route::get('customers',           [CustomerController::class, 'index']);
    Route::get('customers/{customer}', [CustomerController::class, 'show']);
    Route::put('customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

    /**
     * AI Generation Routes
     */
    Route::prefix('products')->group(function () {
        Route::post('{product}/generate-models', [CompatibilityController::class, 'generateSingleProductModels']);
        Route::post('category/{category}/generate-models', [CompatibilityController::class, 'generateCategoryModels']);
        Route::post('{product}/compatibilities', [CompatibilityController::class, 'store']);
        Route::get('{product}/compatibilities', [CompatibilityController::class, 'index']);
        Route::delete('{product}/compatibilities/{carModel}', [CompatibilityController::class, 'destroy']);
    });

    // Search Routes
    Route::prefix('admin/search')->group(function () {
        Route::get('/', [SearchController::class, 'search'])->name('admin.search');
    });
});
