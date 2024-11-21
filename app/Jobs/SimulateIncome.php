<?php

namespace App\Jobs;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Lottery;

class SimulateIncome implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $accounts = Account::where('is_primary', true)->get();
        foreach ($accounts as $account) {
            Lottery::odds(1, 2)
                   ->winner(function () use ($account) {
                       // deposit
                       $amount = mt_rand(10, 20) * 50000;
                       (new MakeDeposit($account))->handle($amount, 'Received funds');
                   })
                   ->choose();
        }
    }
}
