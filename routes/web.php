<?php

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
