<?php

use App\Http\Controllers\oauth\AuthenticationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

// Rutas públicas (sin middleware)
Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login']);

// Rutas públicas para ver productos y categorías (index y show)
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:api')->group(function () {

    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());

    // Rutas protegidas para CRUD excepto index y show
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    // Aquí podrías incluir otros archivos de rutas protegidas si tienes
    // Por ejemplo:
    // $protectedRoutesPath = __DIR__ . '/api/protected';
    // $files = File::allFiles($protectedRoutesPath);
    // foreach ($files as $file) {
    //     require $file->getPathname();
    // }
});
