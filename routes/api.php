<?php

use App\Http\Controllers\oauth\AuthenticationController;
use App\Models\Appointment;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;



$routesPath = __DIR__ . '/api';

$files = File::allFiles($routesPath);

Route::middleware(['auth:api'])->group(function () use ($files) {
    foreach ($files as $file) {
        require $file->getPathname();
    }
});

Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthenticationController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());
});

