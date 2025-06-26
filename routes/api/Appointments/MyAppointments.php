<?php


use App\Http\Controllers\MyAppointmentController;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:api')->get('/appointments/my', [MyAppointmentController::class, 'myAppointments']);
