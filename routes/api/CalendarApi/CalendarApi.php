<?php


use App\Models\Appointment;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


//Ruta para agendar una cita por medio del store
Route::middleware('auth:api')->post('/appointment/store', function (Request $request) {
    $user = $request->user(); // ✅ Esto sí funciona

    $doctor = User::where('is_doctor', true)->first();

    if (!$doctor || !$doctor->google_token) {
        return response()->json(['error' => 'No se encontró un doctor con Google Calendar vinculado.'], 422);
    }

    $appointment = Appointment::create([
        'user_id'    => $request->user()->id,
        'summary'    => $request->summary,
        'description'=> $request->description,
        'start_time' => $request->start,
        'end_time'   => $request->end,
    ]);

    $client = new Google_Client();
    $client->setAccessToken($doctor->google_token);

    if ($client->isAccessTokenExpired() && $doctor->google_refresh_token) {
        $client->fetchAccessTokenWithRefreshToken($doctor->google_refresh_token);
        $doctor->google_token = json_encode($client->getAccessToken());
        $doctor->google_token_expires_at = now()->addSeconds($client->getAccessToken()['expires_in']);
        $doctor->save();
    }

    $calendarService = new Google_Service_Calendar($client);
    $event = new Google_Service_Calendar_Event([
        'summary'     => $appointment->summary,
        'description' => $appointment->description,
        'start' => [
            'dateTime' => Carbon::parse($appointment->start_time)->toRfc3339String(),
            'timeZone' => 'America/Mexico_City',
        ],
        'end' => [
            'dateTime' => Carbon::parse($appointment->end_time)->toRfc3339String(),
            'timeZone' => 'America/Mexico_City',
        ],
    ]);

    $calendarService->events->insert('primary', $event);

    return response()->json([
        'message' => 'Cita agendada correctamente',
        'appointment' => $appointment,
    ], 201);
});

