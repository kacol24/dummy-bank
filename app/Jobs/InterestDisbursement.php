<?php

namespace App\Jobs;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use App\Models\TimeDeposit;
use Carbon\Unit;
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
        $timeDeposit = $account->timeDeposit;
        if ($balance <= 0) {
            return;
        }

        if ($timeDeposit->isMonthly() && today()->get(Unit::Day) != 28) {
            return;
        }

        $interestRate = $timeDeposit->interest_rate / 100;
        $daysInPeriod = $this->getDaysInPeriod($timeDeposit);
        $daysInYear = Carbon::now()->daysInYear();
        // 3,50 % (Bunga Tabungan per tahun) x 1 / 365 (periode harian) x Rp100.000.000
        $amount = floor($interestRate * $daysInPeriod / $daysInYear * $balance);

        try {
            (new MakeDeposit())->handle(
                $account,
                $amount,
                'Savings interest',
                array_merge(
                    $timeDeposit->toArray(),
                    [
                        'balance'     => $balance,
                        'calculation' => "$interestRate * $daysInPeriod / $daysInYear * $balance",
                    ],
                )
            );
        } catch (\Exception $e) {
            logger($e->getMessage(), $e->getTrace());
        }
    }

    protected function getDaysInPeriod(TimeDeposit $timeDeposit)
    {
        if (! is_null($timeDeposit->ends_at)) {
            return $timeDeposit->created_at->diffInDays($timeDeposit->ends_at);
        }

        // monthly or daily
        return today()->sub("$timeDeposit->period $timeDeposit->period_unit")->diffInDays(today());
    }
}
