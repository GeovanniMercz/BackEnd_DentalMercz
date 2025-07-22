<?php

use App\Http\Controllers\oauth\AuthenticationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    $routesPath = __DIR__ . '/api';

    $files = File::allFiles($routesPath);
    Route::middleware(['auth:api'])->group(function () use ($files) {
        foreach ($files as $file) {
            require $file->getPathname();
        }
    });
});
