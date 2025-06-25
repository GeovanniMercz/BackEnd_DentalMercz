<?php


use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('appointments')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])
        ->name('appointments.index')
        ->middleware('can:appointment_index');

    Route::post('/', [AppointmentController::class, 'store'])
        ->name('appointments.store')
        ->middleware('auth:api');
});
