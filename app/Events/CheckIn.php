<?php

namespace App\Events;

use App\Actions\Account\MakeDeposit;
use App\Models\User;
use App\States\DailyCheckInState;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class CheckIn extends Event
{
    #[StateId(DailyCheckInState::class)]
    public int $user_id;

    public function __construct(
        $user_id
    ) {
        $this->user_id = $user_id;
    }

    public function validate(DailyCheckInState $state)
    {
        $this->assert(
            is_null($state->last_checkin_at) || ! $state->last_checkin_at->isToday(),
            'Task completed. Come back again tomorrow.'
        );
    }

    public function apply(DailyCheckInState $state)
    {
        if ($state->checkin_count > 7) {
            $state->checkin_count = 0;
        }

        if (! is_null($state->last_checkin_at) && $state->last_checkin_at->isYesterday()) {
            $state->checkin_count++;
        } else {
            $state->checkin_count = 1;
        }

        $state->last_checkin_at = now();
    }

    /**
     * @throws \Bavix\Wallet\Internal\Exceptions\ExceptionInterface
     */
    public function handle(DailyCheckInState $state)
    {
        $depositAmount = [
            0,
            100000,
            200000,
            300000,
            500000,
            800000,
            1300000,
            2100000,
        ];

        $user = User::find($this->user_id);
        $account = $user->accounts->where('is_primary', true)->first();
        (new MakeDeposit($account))->handle($depositAmount[$state->checkin_count], 'Check-in reward');
    }
}
