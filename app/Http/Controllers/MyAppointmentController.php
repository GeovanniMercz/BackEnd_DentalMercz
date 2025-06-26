<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class MyAppointmentController extends Controller
{

    public function myAppointments(Request $request)
    {
        $user = $request->user(); // Obtiene el usuario autenticado

        $appointments = Appointment::where('user_id', $user->id)->get();

        return response()->json($appointments);
    }


}
