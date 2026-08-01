<?php

use App\Console\Commands\CreateSpecialistCommand;
use App\Console\Commands\FinishExpiredPlansCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        CreateSpecialistCommand::class,
        FinishExpiredPlansCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('plans:finish-expired')->dailyAt('00:05')->timezone('America/Lima')->withoutOverlapping();
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
