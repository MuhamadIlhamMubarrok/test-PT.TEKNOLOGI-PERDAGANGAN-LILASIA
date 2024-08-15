<?php

use App\Http\Controllers\API\authController;
use App\Http\Controllers\API\productController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/registerUser', [authController::class, 'registerUser']);
Route::post('/logout', [AuthController::class, 'logoutUser'])->middleware('auth:sanctum');
Route::post('/loginUser', [authController::class, 'loginUser']);
Route::get('/login', function(){
    return response()->json([
        "status" => false,
        "message" => "Access not Approve"
    ]);
})->name("login");

Route::patch('/products/{id}', [ProductController::class, 'update'])->middleware(['auth:sanctum', CheckForAnyAbility::class . ":admin"]);
Route::get('/products', [ProductController::class, 'index'])
    ->middleware([
        'auth:sanctum', 
        CheckForAnyAbility::class . ':admin,user'
    ]);
Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum', CheckForAnyAbility::class . ':admin');
Route::get('/products/{id}', [ProductController::class, 'show'])->middleware(['auth:sanctum', CheckForAnyAbility::class . ":admin"]);
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->middleware(['auth:sanctum', CheckForAnyAbility::class . ":admin"]);

