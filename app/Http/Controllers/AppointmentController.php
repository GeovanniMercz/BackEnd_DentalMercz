<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        // Validar datos (puedes usar FormRequest para más orden)

        // Buscar al dentista a quien asignar la cita
        $dentist = User::find($request->dentist_id);

        if (!$dentist) {
            return response()->json(['error' => 'Dentista no encontrado'], 404);
        }

        // Crear la cita en base de datos
        $appointment = Appointment::create([
            'user_id' => $dentist->id,
            'summary' => $request->summary,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Crear evento en Google Calendar usando token del dentista
        $googleClient = $this->getGoogleClientForUser($dentist);
        $this->createEventInGoogleCalendar($appointment, $googleClient);

        return response()->json(['message' => 'Cita creada correctamente', 'appointment' => $appointment]);
    }

    protected function getGoogleClientForUser(User $user)
    {
        $client = new \Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->addScope(\Google_Service_Calendar::CALENDAR);

        $accessToken = json_decode($user->google_token, true);

        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $newToken = $client->getAccessToken();
            $user->update(['google_token' => json_encode($newToken)]);
        }

        return $client;
    }

    protected function createEventInGoogleCalendar(Appointment $appointment, \Google_Client $client)
    {
        $service = new \Google_Service_Calendar($client);

        $event = new \Google_Service_Calendar_Event([
            'summary' => $appointment->summary,
            'description' => $appointment->description,
            'start' => ['dateTime' => $appointment->start_time->toRfc3339String()],
            'end' => ['dateTime' => $appointment->end_time->toRfc3339String()],
        ]);

        $calendarId = 'primary';
        $createdEvent = $service->events->insert($calendarId, $event);

        $appointment->update(['google_event_id' => $createdEvent->id]);
    }
}
