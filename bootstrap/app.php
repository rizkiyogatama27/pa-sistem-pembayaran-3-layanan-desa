<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
            'midtrans/callback',
            'logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('tagihan:generate-bulanan')->monthlyOn(1, '00:01');
        $schedule->command('tagihan:apply-denda')->dailyAt('00:05');
        $schedule->command('tagihan:send-whatsapp-reminder')->dailyAt('08:00');
        $schedule->command('tagihan:send-whatsapp-reminder --days=0')->dailyAt('08:10');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
