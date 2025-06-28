<?php

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

//Authentication to google calendar
Route::get('/api/auth/google', function () {
    return Socialite::driver('google')
        ->scopes(['https://www.googleapis.com/auth/calendar'])
        ->with(['access_type' => 'offline', 'prompt' => 'consent'])
        ->redirect();
});


Route::get('/api/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user(); // SIN stateless()

    $user = Auth::user();

    if (!$user) {
        abort(403, 'Usuario no autenticado');
    }

    // Guardar el access y refresh token
    $user->google_token = json_encode([
        'access_token' => $googleUser->token,
        'expires_in' => $googleUser->expiresIn,
        'created' => time(),
        'scope' => 'https://www.googleapis.com/auth/calendar',
        'token_type' => 'Bearer',
    ]);

    $user->google_refresh_token = $googleUser->refreshToken; // ✅ Este es el que necesitas
    $user->google_token_expires_at = now()->addSeconds($googleUser->expiresIn);
    $user->save();

    Session::put('google_token', $googleUser->token);

    return redirect('/google/calendar')->with('success', 'Google Calendar vinculado correctamente.');
});

// Route to downland a specific voucher
Route::middleware('auth:api')->get('/api/appointments/comprobante/{appointmentId}', function ($appointmentId, Request $request) {
    $user = $request->user();

    // Buscar cita en base de datos
    $appointment = \App\Models\Appointment::where('id', $appointmentId)
        ->where('user_id', $user->id)
        ->first();

    if (!$appointment) {
        return response()->json(['error' => 'Cita no encontrada o no autorizada'], 404);
    }

    // Crear contenido del comprobante con info de la cita
    $comprobante = "***** COMPROBANTE DE CITA *****\n";
    $comprobante .= "Título: {$appointment->summary}\n";
    $comprobante .= "Descripción: {$appointment->description}\n";
    $comprobante .= "Inicio: {$appointment->start_time}\n";
    $comprobante .= "Fin: {$appointment->end_time}\n";
    $comprobante .= "Fecha de emisión: " . now() . "\n";
    $comprobante .= "*******************************\n";

    $filename = "comprobante_cita_{$appointmentId}.txt";

    return response($comprobante, 200)
        ->header('Content-Type', 'text/plain')
        ->header('Content-Disposition', "attachment; filename=\"$filename\"");
});

//Get the appointments in google Calendar
Route::get('/doctor/calendar', function () {
    // Trae primer doctor activo (mejor pon lógica para el doctor correcto)
    $doctor = User::where('is_doctor', true)->first();

    if (!$doctor || !$doctor->google_token) {
        return response()->json(['error' => 'El doctor no tiene token de Google Calendar'], 422);
    }

    $token = json_decode($doctor->google_token, true);

    $client = new Google_Client();
    $client->setAccessToken($token);

    // Refresca token si está expirado
    if ($client->isAccessTokenExpired()) {
        if ($doctor->google_refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($doctor->google_refresh_token);
            $doctor->google_token = json_encode($client->getAccessToken());
            $doctor->google_token_expires_at = now()->addSeconds($client->getAccessToken()['expires_in']);
            $doctor->save();
        } else {
            return response()->json(['error' => 'Token expirado y no hay refresh token'], 401);
        }
    }

    $service = new Google_Service_Calendar($client);
    $events = $service->events->listEvents('primary', [
        'maxResults' => 50,
        'singleEvents' => true,
        'orderBy' => 'startTime',
        'timeMin' => now()->toRfc3339String(),
    ]);

    $eventList = [];

    foreach ($events->getItems() as $event) {
        $eventList[] = [
            'id' => $event->getId(),
            'summary' => $event->getSummary(),
            'description' => $event->getDescription(),
            'start' => Carbon::parse($event->getStart()->getDateTime() ?? $event->getStart()->getDate())
                ->setTimezone('America/Mexico_City')
                ->toIso8601String(),
            'end' => Carbon::parse($event->getEnd()->getDateTime() ?? $event->getEnd()->getDate())
                ->setTimezone('America/Mexico_City')
                ->toIso8601String(),
        ];
    }

    return response()->json($eventList);
});
