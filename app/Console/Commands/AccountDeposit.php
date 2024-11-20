<?php

namespace App\Console\Commands;

use App\Actions\Account\MakeDeposit;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use function Laravel\Prompts\search;

class AccountDeposit extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:account-deposit {account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account');
        $amount = $this->ask('Amount?');

        if (! $amount) {
            $this->error('Amount is required.');
        }

        $message = $this->ask('Include custom message?', 'Deposit from teller');

        $account = Account::findOrFail($accountId);
        (new MakeDeposit($account))->handle($amount, $message);

        $this->info('The command was successful!');
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'account' => fn() => search(
                label: 'Search for account number:',
                options: fn($value) => strlen($value) > 0
                    ? Account::query()
                             ->where(function ($query) use ($value) {
                                 return $query->where('account_number', 'like', "%{$value}%")
                                              ->orWhere('name', 'like', "%{$value}%");
                             })
                             ->get()
                             ->pluck('dropdown_display', 'id')
                             ->toArray()
                    : []
            ),
        ];
    }
}
