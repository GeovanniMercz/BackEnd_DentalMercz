<?php

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

//Authentication to google calendar
Route::get('/api/auth/google', function () {
    return Socialite::driver('google')
        ->scopes(['https://www.googleapis.com/auth/calendar'])
        ->redirect();
});


Route::get('/api/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();

    // 🔧 SIMULA LOGIN TEMPORAL PARA PRUEBA
    Auth::loginUsingId(1); // Solo si aún no tienes login real

    $user = Auth::user();

    if (!$user) {
        abort(403, 'Usuario no autenticado');
    }

    $user->google_token = json_encode([
        'access_token' => $googleUser->token,
        'expires_in' => $googleUser->expiresIn,
        'created' => time(),
        'scope' => 'https://www.googleapis.com/auth/calendar',
        'token_type' => 'Bearer',
    ]);
    $user->google_refresh_token = $googleUser->refreshToken;
    $user->google_token_expires_at = now()->addSeconds($googleUser->expiresIn);
    $user->save();

    Session::put('google_token', $googleUser->token);

    return redirect('/google/calendar')->with('success', 'Google Calendar vinculado correctamente.');

});



//Visualiza eventos de calendario
Route::get('/google/calendar', function () {
    $token = Session::get('google_token');

    $client = new \Google_Client();
    $client->setAccessToken($token);

    $calendar = new \Google_Service_Calendar($client);
    $events = $calendar->events->listEvents('primary');

    echo "<h2>Eventos:</h2>";
    foreach ($events->getItems() as $event) {
        $summary = $event->getSummary();
        $eventId = $event->getId();

        echo "$summary ";
        echo "<a href='/google/calendar/comprobante/{$eventId}'>[Descargar comprobante]</a><br>";
    }
});

//Creation of voucher of appointment
Route::get('/google/calendar/comprobante/{eventId}', function ($eventId) {
    $token = Session::get('google_token');

    if (!$token) {
        return redirect('/api/auth/google');
    }

    $client = new \Google_Client();
    $client->setAccessToken($token);

    $calendar = new \Google_Service_Calendar($client);
    $event = $calendar->events->get('primary', $eventId);

    $summary     = $event->getSummary();
    $description = $event->getDescription();
    $start       = $event->getStart()->getDateTime();
    $end         = $event->getEnd()->getDateTime();

    $comprobante = "***** COMPROBANTE DE EVENTO *****\n";
    $comprobante .= "Título: $summary\n";
    $comprobante .= "Descripción: $description\n";
    $comprobante .= "Inicio: $start\n";
    $comprobante .= "Fin: $end\n";
    $comprobante .= "Fecha de emisión: " . now() . "\n";
    $comprobante .= "*******************************\n";

    $filename = str_replace(' ', '_', $summary) . "_comprobante.txt";

    return Response::make($comprobante, 200, [
        'Content-Type' => 'text/plain',
        'Content-Disposition' => "attachment; filename=\"$filename\""
    ]);
});

//Form to create a new Appointment
Route::get('/google/calendar/create', function () {
    if (!Auth::check()) {
        return redirect('/login'); // o donde manejes login
    }

    return '
        <form method="POST" action="/appointment/store">
            ' . csrf_field() . '
            <label>Título:</label><input name="summary"><br>
            <label>Descripción:</label><input name="description"><br>
            <label>Inicio:</label><input type="datetime-local" name="start"><br>
            <label>Fin:</label><input type="datetime-local" name="end"><br>
            <button type="submit">Agendar cita</button>
        </form>
    ';
});


//Creation of event in calendar

Route::post('/google/calendar/store', function (Request $request) {
    $token = Session::get('google_token');

    if (!$token) {
        return redirect('/api/auth/google');
    }

    $client = new \Google_Client();
    $client->setAccessToken($token);

    $calendarService = new \Google_Service_Calendar($client);

    // ✅ Asegurarse que la hora no se convierta a UTC (NO usar toRfc3339String aquí)
    $start = Carbon::parse($request->start)
        ->setTimezone('America/Mexico_City')
        ->format('Y-m-d\TH:i:s');

    $end = Carbon::parse($request->end)
        ->setTimezone('America/Mexico_City')
        ->format('Y-m-d\TH:i:s');

    $event = new \Google_Service_Calendar_Event([
        'summary'     => $request->summary,
        'description' => $request->description,
        'start' => [
            'dateTime' => $start,
            'timeZone' => 'America/Mexico_City',
        ],
        'end' => [
            'dateTime' => $end,
            'timeZone' => 'America/Mexico_City',
        ],
    ]);

    $calendarService->events->insert('primary', $event);

    return redirect('/google/calendar')->with('success', 'Evento creado con éxito.');
});

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
