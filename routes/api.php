<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CheckoutController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

# Public routes
Route::post('register', [AuthController::class, "register"]);
Route::post('login', [AuthController::class, "login"]);
Route::post('googleLogin', [AuthController::class, "googleLogin"]);
Route::get('verify', [UsersController::class, "verify"])->name('verify');
Route::post('reset-password', [UsersController::class, 'resetPassword']);
Route::post('set-password/{code}', [UsersController::class, 'setNewPassword']);

Route::get('categories', [CategoryController::class, 'getCategories']);
Route::get('products/category/{category_id}', [ProductController::class, 'getProductsByCategory']);

Route::get('products/{id}', [ProductController::class, 'viewProduct']);
Route::get('products', [ProductController::class, 'listProducts']);



/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // 🛒 Cart Management
    Route::post('cart/add', [CartController::class, 'addToCart']);
    Route::put('cart/update/{cartItemId}', [CartController::class, 'updateCartItem']);
    Route::delete('cart/remove/{cartItemId}', [CartController::class, 'removeFromCart']);
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::delete('cart/clear', [CartController::class, 'clearCart']);
    Route::get('cart/item/{cartItemId}', [CartController::class, 'getCartItem']);

    // 🛒 Customer Purchase & Billing
    Route::post('create-bill', [TransactionController::class, 'createBill']);
    Route::get('transactions/{customer_id}', [TransactionController::class, 'getCustomerTransactions']);

    // 🎁 Reward Points System
    Route::get('reward-points/{customer_id}', [RewardController::class, 'getPoints']);
    Route::post('redeem-points', [RewardController::class, 'redeemPoints']);

    // 🛍️ Checkout & Payment
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    Route::post('payment/verify', [CheckoutController::class, 'verifyPayUTransaction']);
    Route::get('transactions/{transactionId}', [CheckoutController::class, 'getTransaction']);
    Route::get('transactions', [CheckoutController::class, 'getTransactionHistory']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Require Authentication & Admin Access)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum', 'admin')->group(function () {

    // 🛍️ Manage Categories
    Route::post('category', [CategoryController::class, 'addCategory']);
    Route::put('categories/{id}/status', [CategoryController::class, 'updateCategoryStatus']);

    // 👥 Admin Management
    Route::post('register-admin', [AuthController::class, 'registerAdmin']);

    // 📋 View All Transactions
    Route::get('transactions', [TransactionController::class, 'getAllTransactions']);
});

/*
|--------------------------------------------------------------------------
| Product Management Routes (Require Authentication & Product Management Access)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // 📦 Manage Products (Admin and Content Creator)
    Route::post('product', [ProductController::class, 'addProduct']);
    Route::put('products/{id}', [ProductController::class, 'updateProduct']);
    Route::put('products/{id}/update2', [ProductController::class, 'updateProduct2']);
    
    // Delete products (Admin only)
    Route::delete('products/{id}', [ProductController::class, 'deleteProduct']);
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
