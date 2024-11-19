<?php

namespace App\Actions\Account;

use App\Models\Account;

/**
 *
 */
final class MakeDeposit
{
    /**
     * @param  \App\Models\Account  $account
     * @param  int  $amount
     * @param  string|null  $message
     * @param  array  $additionalMeta
     * @return void
     * @throws \Bavix\Wallet\Internal\Exceptions\ExceptionInterface
     */
    public function handle(Account $account, int $amount, string $message = null, array $additionalMeta = [])
    {
        $meta = [
            'from' => $account->balanceInt,
            'to'   => $account->balanceInt + $amount,
        ];
        if (! is_null($message)) {
            $meta['message'] = $message;
        }
        $account->deposit($amount, array_merge($additionalMeta, $meta));
    }
}
