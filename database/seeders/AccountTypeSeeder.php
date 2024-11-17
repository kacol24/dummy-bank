<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountType::create([
            'is_default'    => true,
            'name'          => 'Regular',
            'interest_rate' => 2,
        ]);

        AccountType::create([
            'name'          => 'Savings',
            'interest_rate' => 4,
        ]);
    }
}
