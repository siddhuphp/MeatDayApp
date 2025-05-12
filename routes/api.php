<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RewardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('admin/register', [AdminAuthController::class, 'registerAdmin']); // One-time use
Route::post('admin/login', [AdminAuthController::class, 'adminLogin']);

Route::get('categories', [CategoryController::class, 'getCategories']);
Route::get('subcategories', [SubcategoryController::class, 'getAllSubcategories']);
Route::get('subcategories/{category_id}', [SubcategoryController::class, 'getSubcategories']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // 🛒 Customer Purchase & Billing
    Route::post('create-bill', [TransactionController::class, 'createBill']);
    Route::get('transactions/{customer_id}', [TransactionController::class, 'getCustomerTransactions']);

    // 🎁 Reward Points System
    Route::get('reward-points/{customer_id}', [RewardController::class, 'getPoints']);
    Route::post('redeem-points', [RewardController::class, 'redeemPoints']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Require Authentication & Admin Access)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum', 'admin')->group(function () {

    // 🛍️ Manage Categories
    Route::post('category', [CategoryController::class, 'addCategory']);

    // 🏷️ Manage Subcategories
    Route::post('subcategory', [SubcategoryController::class, 'addSubcategory']);

    // 📦 Manage Products
    Route::post('admin/product', [ProductController::class, 'addProduct']);
    Route::get('admin/products/{subcategory_id}', [ProductController::class, 'getProducts']);

    // 📋 View All Transactions
    Route::get('admin/transactions', [TransactionController::class, 'getAllTransactions']);
});


// Clear application cache:
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return 'Application cache has been cleared';
});

//Clear route cache:
Route::get('/route-cache', function () {
    Artisan::call('route:cache');
    return 'Routes cache has been cleared';
});

//Clear config cache:
Route::get('/config-cache', function () {
    Artisan::call('config:cache');
    return 'Config cache has been cleared';
});

// Clear view cache:
Route::get('/view-clear', function () {
    Artisan::call('view:clear');
    return 'View cache has been cleared';
});
