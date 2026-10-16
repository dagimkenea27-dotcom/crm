<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Manager Account (Admin)
        User::updateOrCreate(
            ['email' => 'admin@gojoshop.com'],
            [
                'name'            => 'Manager (Admin)',
                'password'        => Hash::make('password'),
                'role'            => 'manager',
                'coupon_code'     => null,
                'fixed_pay_birr'  => 0,
                'commission_birr' => 0,
            ]
        );

        // 2. Initial Contractors
        User::updateOrCreate(
            ['email' => 'hanna@gojoshop.com'],
            [
                'name'            => 'Hanna Kebede',
                'password'        => Hash::make('password'),
                'role'            => 'contractor',
                'coupon_code'     => 'HANNA_100',
                'fixed_pay_birr'  => 3000.00,
                'commission_birr' => 150.00,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dawit@gojoshop.com'],
            [
                'name'            => 'Dawit Tadesse',
                'password'        => Hash::make('password'),
                'role'            => 'contractor',
                'coupon_code'     => 'DAWIT_100',
                'fixed_pay_birr'  => 2800.00,
                'commission_birr' => 150.00,
            ]
        );

        User::updateOrCreate(
            ['email' => 'selam@gojoshop.com'],
            [
                'name'            => 'Selam Alemayehu',
                'password'        => Hash::make('password'),
                'role'            => 'contractor',
                'coupon_code'     => 'SELAM_100',
                'fixed_pay_birr'  => 2800.00,
                'commission_birr' => 150.00,
            ]
        );
    }
}
