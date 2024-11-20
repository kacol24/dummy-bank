<?php

use App\Console\Commands\AccountDeposit;
use App\Jobs\InterestDisbursement;
use App\Jobs\SimulateExpense;
use App\Jobs\SimulateIncome;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
                  ->withRouting(
                      web: __DIR__.'/../routes/web.php',
                      commands: __DIR__.'/../routes/console.php',
                      health: '/up',
                  )
                  ->withMiddleware(function (Middleware $middleware) {
                      //
                  })
                  ->withSchedule(function (Schedule $schedule) {
                      $schedule->job(new InterestDisbursement())
                               ->dailyAt('05:00');
                      $schedule->job(new SimulateIncome())
                               ->dailyAt('10:00');
                      $schedule->job(new SimulateExpense())
                               ->hourly()
                               ->between('08:00', '22:00');
                  })
                  ->withCommands([
                      AccountDeposit::class,
                  ])
                  ->withExceptions(function (Exceptions $exceptions) {
                      //
                  })->create();
