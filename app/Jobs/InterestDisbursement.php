<?php

namespace App\Jobs;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class InterestDisbursement implements ShouldQueue
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
        $accounts = Account::get();

        foreach ($accounts as $account) {
            $this->disburse($account);
        }
    }

    protected function disburse($account)
    {
        $balance = $account->balance;
        $interestRate = 10 / 100;
        $daysInPeriod = 1;
        $daysInYear = Carbon::now()->daysInYear();
        // 3,50 % (Bunga Tabungan per tahun) x 1 / 365 (periode harian) x Rp100.000.000
        $amount = floor($interestRate * $daysInPeriod / $daysInYear * $balance);

        try {
            (new MakeDeposit())->handle($account, $amount, 'Savings interest');
        } catch (\Exception $e) {
            logger($e->getMessage(), $e->getTrace());
        }
    }
}
