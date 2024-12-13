<?php

namespace App\Events;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use App\States\ClientState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class ClientMakeDeposit extends Event
{
    #[StateId(ClientState::class)]
    public int $account_id;

    public int $amount;

    public function __construct(
        $account_id,
        $amount
    ) {
        $this->account_id = $account_id;
        $this->amount = $amount;
    }

    public function validate(ClientState $state)
    {
        // first deposit, ever
        if (is_null($state->last_deposit_at)) {
            return true;
        }

        // first deposit of the day
        if (! $state->last_deposit_at->isToday()) {
            return true;
        }

        // should check for amount
        $this->assert(
            assertion: $this->checkAvailableAmount($state->deposit_amount),
            message: 'Max 1,000,000/day. Remaining today '.number_format(abs($state->deposit_amount - 1000000))
        );
    }

    protected function checkAvailableAmount($currentAmount, $limit = 1000000)
    {
        return $this->amount + $currentAmount <= $limit;
    }

    public function apply(ClientState $state)
    {
        $state->deposit_amount += $this->amount;
        if (is_null($state->last_deposit_at) || ! $state->last_deposit_at->isToday()) {
            $state->deposit_amount = $this->amount;
        }

        $state->last_deposit_at = now();
    }

    public function handle()
    {
        $account = Account::find($this->account_id);
        (new MakeDeposit($account))->handle($this->amount, 'Client deposit');
    }
}
