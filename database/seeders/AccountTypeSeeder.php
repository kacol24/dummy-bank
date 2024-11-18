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
            'period'        => 1,
            'period_unit'   => 'days',
        ]);

        AccountType::create([
            'name'          => 'Mini Saver',
            'interest_rate' => 4,
            'period'        => 7,
            'period_unit'   => 'days',
        ]);
    }
}
