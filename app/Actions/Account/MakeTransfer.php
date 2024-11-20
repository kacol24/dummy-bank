<?php

namespace App\Actions\Account;

use App\Models\Account;
use Bavix\Wallet\External\Dto\Extra;
use Bavix\Wallet\External\Dto\Option;

final class MakeTransfer
{
    public function __construct(
        protected Account $sourceAccount,
        protected Account $destinationAccount
    ) {
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
        $meta = [];
        if (! is_null($message)) {
            $meta['message'] = $message;
        }
        $this->sourceAccount->transfer(
            $this->destinationAccount,
            $amount,
            new Extra(
                new Option([
                    'from'    => $this->sourceAccount->balanceInt,
                    'to'      => $this->destinationAccount->balanceInt + $amount,
                    'message' => "Received transfer from account {$this->sourceAccount->name}",
                ]),
                new Option([
                    'from'    => $this->sourceAccount->balanceInt,
                    'to'      => $this->destinationAccount->balanceInt_ - $amount,
                    'message' => "Transferred funds to account {$this->destinationAccount->name}",
                ]),
                extra: array_merge($additionalMeta, $meta)
            ),
        );
    }
}
