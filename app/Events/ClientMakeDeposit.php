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
        if (is_null($state->last_deposit_at)) {
            return true;
        }

        if ($state->last_deposit_at->lt(today())) {
            return true;
        }

        if ($state->last_deposit_at->isToday()) {
            $this->assert(
                assertion: $this->checkAvailableAmount($state),
                message: 'Max 1,000,000/day. Remaining today '.number_format(abs($state->deposit_amount - 1000000))
            );
        }
    }

    protected function checkAvailableAmount(ClientState $state)
    {
        return $this->amount + $state->deposit_amount <= 1000000;
    }

    protected function checkLastDepositDate(ClientState $state)
    {
        if (is_null($state->last_deposit_at)) {
            return true;
        }

        return today()->gt($state->last_deposit_at);
    }

    public function apply(ClientState $state)
    {
        $state->last_deposit_at = now();
        $state->deposit_amount += $this->amount;
    }

    public function handle()
    {
        $account = Account::find($this->account_id);
        (new MakeDeposit($account))->handle($this->amount, 'Client deposit');
    }
}
