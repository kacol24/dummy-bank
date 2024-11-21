<?php

namespace App\States;

use Illuminate\Support\Carbon;
use Thunk\Verbs\State;

class ClientState extends State
{
    public Carbon|null $last_deposit_at = null;

    public int $deposit_amount = 0;
}
