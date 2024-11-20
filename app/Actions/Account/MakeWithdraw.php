<?php

namespace App\Actions\Account;

use App\Models\Account;

/**
 *
 */
final class MakeWithdraw
{
    /**
     * @param  \App\Models\Account  $account
     */
    public function __construct(protected Account $account)
    {
    }

    /**
     * @param  int  $amount
     * @param  string|null  $message
     * @param  array  $additionalMeta
     * @return void
     * @throws \Bavix\Wallet\Internal\Exceptions\ExceptionInterface
     */
    public function handle(int $amount, string $message = null, array $additionalMeta = [])
    {
        $meta = [
            'from' => $this->account->balanceInt,
            'to'   => $this->account->balanceInt - $amount,
        ];
        if (! is_null($message)) {
            $meta['message'] = $message;
        }
        $this->account->withdraw($amount, array_merge($additionalMeta, $meta));
    }
}
