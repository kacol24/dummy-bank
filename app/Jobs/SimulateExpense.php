<?php

namespace App\Jobs;

use App\Actions\Account\MakeWithdraw;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Lottery;

class SimulateExpense implements ShouldQueue
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
            Lottery::odds(1, 10)
                   ->winner(function () use ($account) {
                       // withdraw
                       $amount = mt_rand(1, 10) * 10000;
                       (new MakeWithdraw($account))->handle($amount, 'Random expense');
                   })
                   ->choose();
        }
    }
}
