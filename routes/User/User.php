<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('user', [UserController::class, 'index'])
    ->name('user.index')
    ->middleware('permission:user_index|user_show');

Route::post('user', [UserController::class, 'store'])
    ->name('user.store')
    ->middleware('can:user_store');

Route::get('user/{user}', [UserController::class, 'show'])
    ->name('user.show')
    ->middleware('permission:user_show|user_update');

Route::put('user/{user}', [UserController::class, 'update'])
    ->name('user.update')
    ->middleware('can:user_update');

Route::delete('user/{user}', [UserController::class, 'destroy'])
    ->name('user.destroy')
    ->middleware('can:user_destroy');
