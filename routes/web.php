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

    $user->google_token = $googleUser->token;
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

    // Convertir fechas con zona horaria explícita
    $start = Carbon::parse($request->start, 'America/Mexico_City')->toRfc3339String();
    $end = Carbon::parse($request->end, 'America/Mexico_City')->toRfc3339String();

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

