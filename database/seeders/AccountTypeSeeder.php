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
            'is_locked'     => false,
            'name'          => 'Regular',
            'interest_rate' => 4,
            'period'        => 1,
            'period_unit'   => 'days',
        ]);

        AccountType::create([
            'is_locked'     => false,
            'name'          => 'Flexi Saver',
            'interest_rate' => 6,
            'period'        => 1,
            'period_unit'   => 'days',
        ]);

        AccountType::create([
            'is_locked'     => true,
            'name'          => 'Mini Saver',
            'interest_rate' => 10,
            'period'        => 7,
            'period_unit'   => 'days',
        ]);

        AccountType::create([
            'is_locked'     => true,
            'name'          => 'Hero Saver',
            'interest_rate' => 14,
            'period'        => 1,
            'period_unit'   => 'months',
        ]);

        AccountType::create([
            'is_locked'     => true,
            'name'          => 'Super Saver',
            'interest_rate' => 20,
            'period'        => 3,
            'period_unit'   => 'months',
        ]);
    }
}
