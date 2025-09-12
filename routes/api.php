<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\DB;

Route::get('/db-test', function () {
    $start = microtime(true);
    try {
        DB::connection()->getPdo();
        $status = 'ok';
    } catch (\Throwable $e) {
        $status = 'fail';
    }

    return [
        'status' => $status,
        'duration' => round(microtime(true) - $start, 4) . 's',
    ];
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});


Route::middleware('auth:api')->get('/dashboard', [DashboardController::class, 'index']);
Route::middleware('auth:api')->apiResource('transactions', TransactionController::class);


Route::middleware('auth:api')->get('/report', [ReportController::class, 'monthly']);
Route::middleware('auth:api')->get('/report/export', [ReportController::class, 'export']);

Route::middleware('auth:api')->apiResource('categories', CategoryController::class);;
