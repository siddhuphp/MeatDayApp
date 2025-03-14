<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

# Public routes
Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('admin/register', [AdminAuthController::class, 'registerAdmin']); // One-time use
Route::post('admin/login', [AdminAuthController::class, 'adminLogin']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('admin/category', [AdminController::class, 'addCategory']);
    Route::post('admin/subcategory', [AdminController::class, 'addSubcategory']);
    Route::post('admin/product', [AdminController::class, 'addProduct']);
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

