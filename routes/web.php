<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/auth/google', function () {
    return Socialite::driver('google')
        ->scopes(['https://www.googleapis.com/auth/calendar'])
        ->redirect();
});

Route::get('/api/auth/google/callback', function () {
    $user = Socialite::driver('google')->stateless()->user();

    Session::put('google_token', $user->token);
    Session::put('google_refresh_token', $user->refreshToken);

    return redirect('/google/calendar');
});

Route::get('/google/calendar', function () {
    $token = Session::get('google_token');

    $client = new \Google_Client();
    $client->setAccessToken($token);

    $calendar = new \Google_Service_Calendar($client);
    $events = $calendar->events->listEvents('primary');

    echo "<h2>Eventos:</h2>";
    foreach ($events->getItems() as $event) {
        echo $event->getSummary() . "<br>";
    }
});

Route::get('/google/calendar/create', function () {
    return '
        <form method="POST" action="/google/calendar/store">
            ' . csrf_field() . '
            <label>Título: <input type="text" name="summary"></label><br>
            <label>Descripción: <input type="text" name="description"></label><br>
            <label>Fecha de inicio: <input type="datetime-local" name="start"></label><br>
            <label>Fecha de fin: <input type="datetime-local" name="end"></label><br>
            <button type="submit">Crear evento</button>
        </form>
    ';
});


Route::post('/google/calendar/store', function (Request $request) {
    $token = Session::get('google_token');

    if (!$token) {
        return redirect('/api/auth/google');
    }

    $client = new \Google_Client();
    $client->setAccessToken($token);

    $calendarService = new \Google_Service_Calendar($client);

    $event = new \Google_Service_Calendar_Event([
        'summary'     => $request->summary,
        'description' => $request->description,
        'start' => [
            'dateTime' => date('c', strtotime($request->start)),
            'timeZone' => 'America/Mexico_City',
        ],
        'end' => [
            'dateTime' => date('c', strtotime($request->end)),
            'timeZone' => 'America/Mexico_City',
        ],
    ]);

    $calendarService->events->insert('primary', $event);

    return redirect('/google/calendar')->with('success', 'Evento creado con éxito.');
});

