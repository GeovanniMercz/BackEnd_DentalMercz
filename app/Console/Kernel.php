<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Los comandos Artisan registrados por la aplicación.
     *
     * @var array<int, class-string|string>
     */
    protected $commands = [
        // Aquí registra tu comando personalizado
        \App\Console\Commands\SendWhatsAppReminders::class,
    ];

    /**
     * Define el programador de tareas de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecuta el comando reminders:whatsapp cada 5 minutos
        $schedule->command('reminders:whatsapp')->everyFiveMinutes();
    }

    /**
     * Registra los comandos para la aplicación.
     */
    protected function commands(): void
    {
        // Carga automáticamente los comandos que estén en el directorio Commands
        $this->load(__DIR__ . '/Commands');

        // Aquí puedes incluir comandos adicionales
        require base_path('routes/console.php');
    }
}
