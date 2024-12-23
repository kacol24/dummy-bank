<?php

namespace App\States;

use Carbon\Carbon;
use Thunk\Verbs\State;

class DailyCheckInState extends State
{
    public Carbon|null $last_checkin_at = null;

    public int $checkin_count = 0;
}
