<?php

use App\Http\Controllers\oauth\AuthenticationController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());
});
