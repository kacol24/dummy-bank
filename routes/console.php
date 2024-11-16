<?php

use App\Jobs\InterestDisbursement;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('bank:interest-disburse', function (){
    dispatch(new InterestDisbursement());
});
