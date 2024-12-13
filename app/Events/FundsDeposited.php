<?php

namespace App\Events;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use Thunk\Verbs\Event;

class FundsDeposited extends Event
{
    public function __construct(
        public int $accountId,
        public int $amount,
        public string $message = 'Received funds'
    ) {
    }

    public function handle()
    {
        $account = Account::find($this->accountId);
        (new MakeDeposit($account))->handle($this->amount, $this->message);
    }
}
