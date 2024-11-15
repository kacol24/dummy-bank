<?php

namespace App\Actions\Account;

use App\Models\Account;

final class MakeDeposit
{
    public function handle(Account $account, $amount, $message = null)
    {
        $meta = [
            'from' => $account->balanceInt,
            'to'   => $account->balanceInt + $amount,
        ];
        if (! is_null($message)) {
            $meta['message'] = $message;
        }
        $account->deposit($amount, $meta);
    }
}
