<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReminders extends Command
{
    protected $signature = 'reminders:whatsapp';
    protected $description = 'Enviar recordatorios por WhatsApp dos horas antes de la cita';

    public function handle()
    {
        $now = Carbon::now();
        $start = $now->copy()->subMinutes(10);
        $end = $now->copy()->addHours(24);

        $appointments = Appointment::whereBetween('start_time', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('whatsapp_sent')->orWhere('whatsapp_sent', false);
            })
            ->with('user')
            ->get();

        Log::info("Citas encontradas para enviar WhatsApp: " . $appointments->count());

        if ($appointments->isEmpty()) {
            $this->info("No hay citas para recordar ahora.");
            return 0;
        }

        foreach ($appointments as $appointment) {
            $user = $appointment->user;
            if (!$user || !$user->phonenumber) {
                $this->warn("La cita {$appointment->id} no tiene usuario o teléfono.");
                Log::warning("Cita {$appointment->id} sin usuario o teléfono");
                continue;
            }

            $phone = $user->phonenumber;
            $name = $user->name ?? 'Paciente';
            $dateTime = Carbon::parse($appointment->start_time)->format('d/m/Y H:i');

            $message = "Hola $name, recuerda que tienes una cita programada el $dateTime. ¡Nos vemos pronto!";

            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->post('http://localhost:3000/send-message', [
                    'json' => [
                        'phone' => $phone,
                        'message' => $message
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $this->info("Mensaje enviado a $name ($phone)");
                    Log::info("Mensaje enviado a $name ($phone)");

                    // MARCAR como enviado para evitar enviar de nuevo
                    $appointment->whatsapp_sent = true;
                    $appointment->save();

                } else {
                    $this->error("Error enviando mensaje a $phone");
                    Log::error("Error enviando mensaje a $phone. Código HTTP: " . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                $this->error("Excepción enviando mensaje a $phone: " . $e->getMessage());
                Log::error("Excepción enviando mensaje a $phone: " . $e->getMessage());
            }
        }

        return 0;
    }
}
