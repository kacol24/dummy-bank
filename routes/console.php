<?php

use App\Jobs\InterestDisbursement;
use App\Jobs\SimulateIncome;
use Illuminate\Support\Facades\Artisan;

Artisan::command('bank:interest-disburse', function () {
    dispatch(new InterestDisbursement());
})->describe('Disburse interest');

Artisan::command('bank:simulate-income', function () {
    dispatch(new SimulateIncome());
})->describe('Trigger income simulation');
