<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\TelegramBotController;
use App\Http\Controllers\Api\WasteController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/purchases', PurchaseController::class);
    Route::apiResource('/sales', SaleController::class);
    Route::apiResource('/expenses', ExpenseController::class);
    Route::apiResource('/shifts', ShiftController::class);
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close']);
    Route::post('/waste', [WasteController::class, 'store']);

    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
});