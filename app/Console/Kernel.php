<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{ 
    /**
     * Define the  application's command schedule.
     * 
     * @param    \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Revisa estructuras sin sincronizar y envía correo si hay alguna con +24h
        $schedule->command('sync:check-status')->dailyAt('03:00');

        // Limpia renovaciones/compras de membresía duplicadas del día en curso
        $schedule->command('transactions:clean-duplicates')->everyFiveMinutes();

        // Detecta e inserta clientes nuevos desde la API cada 5 minutos
        $schedule->command('sync:nuevos-clientes')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Detecta duplicados exactos en client_membership y alerta por correo
        $schedule->command('membership:check-duplicates')->dailyAt('01:00');

        // Limpia (soft-delete) los duplicados detectados, una vez por noche
        $schedule->command('membership:clean-duplicates')->dailyAt('01:05');

        // Sincronización completa: actualiza datos y membresías de todos los clientes (2:30pm y 11:00pm)
        $schedule->command('sync:actualizar-clientes')
            ->cron('30 14,23 * * *')
            ->withoutOverlapping();

        // Registra en individual_invoices las autofacturas del portal cliente (facturacion_aqua)
        $schedule->command('facturas:sync-individuales --origen=cliente')->hourly();
    } 
  
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    } 
}
