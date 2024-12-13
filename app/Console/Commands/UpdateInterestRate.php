<?php

namespace App\Console\Commands;

use App\Models\AccountType;
use App\Models\TimeDeposit;
use Illuminate\Console\Command;
use function Laravel\Prompts\search;

class UpdateInterestRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:update-interest-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update interest rate of specific account type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountTypes = AccountType::get();

        $accountTypeId = search(
            label: "Select account type",
            options: fn($search) => $accountTypes->pluck('dropdownDisplay', 'id')->toArray(),
            placeholder: 'Search...',
            scroll: 15,
        );
        $selectedAccountType = $accountTypes->firstWhere('id', $accountTypeId);

        $newRate = $this->ask(
            'What the new interest rate?',
            $selectedAccountType->interest_rate
        );

        if ($newRate == $selectedAccountType->interest_rate) {
            $this->line('New rate is the same with old rate, do nothing');

            return;
        }

        \DB::beginTransaction();
        $selectedAccountType->interest_rate = $newRate;
        $selectedAccountType->save();
        TimeDeposit::query()
                   ->whereHas('account.accountType', function ($query) use ($accountTypeId) {
                       return $query->where('account_type_id', $accountTypeId);
                   })
                   ->update([
                       'interest_rate' => $newRate,
                   ]);
        \DB::commit();

        $this->info('Success update interest rate!');
    }
}
