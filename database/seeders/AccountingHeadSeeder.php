<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. GL Types
        $glTypes = [
            ['code' => 1, 'name' => 'Liability'],
            ['code' => 2, 'name' => 'Asset'],
            ['code' => 3, 'name' => 'Income'],
            ['code' => 4, 'name' => 'Expenditure'],
            ['code' => 5, 'name' => 'Equity'],
        ];

        foreach ($glTypes as $gt) {
            DB::table('acct_gltype_info')->updateOrInsert(
                ['code' => $gt['code']],
                ['name' => $gt['name']]
            );
        }

        // 2. GL Groups
        $groups = [
            ['gl_type' => 2, 'gl_group' => 201, 'gl_group_name' => 'Cash In Hand', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 4, 'gl_group' => 402, 'gl_group_name' => 'Cost of Good Sold (COGS)', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 3, 'gl_group' => 302, 'gl_group_name' => 'Sales', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 3, 'gl_group' => 303, 'gl_group_name' => 'Sales Return', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 1, 'gl_group' => 101, 'gl_group_name' => 'Mr XYZ', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 2, 'gl_group' => 202, 'gl_group_name' => 'Bank / MFS Accounts', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 2, 'gl_group' => 203, 'gl_group_name' => 'Accounts Receivable', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 4, 'gl_group' => 403, 'gl_group_name' => 'Sales Discount', 'tradingaccount' => 0, 'Balance' => 0],
        ];

        foreach ($groups as $g) {
            DB::table('acct_tbl_balsheet_group')->updateOrInsert(
                ['gl_group' => $g['gl_group']],
                $g
            );
        }

        // 3. GL Sub Groups
        $subgroups = [
            ['gl_type' => 2, 'gl_group' => 201, 'gl_sub_group' => 20101, 'gl_sub_group_name' => 'Cash In Hand', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 4, 'gl_group' => 402, 'gl_sub_group' => 40201, 'gl_sub_group_name' => 'Cost of Good Sold (COGS)', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 3, 'gl_group' => 302, 'gl_sub_group' => 30201, 'gl_sub_group_name' => 'Sales', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 3, 'gl_group' => 303, 'gl_sub_group' => 30301, 'gl_sub_group_name' => 'Sales Return', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 1, 'gl_group' => 101, 'gl_sub_group' => 10101, 'gl_sub_group_name' => 'Mr XYZ', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 2, 'gl_group' => 202, 'gl_sub_group' => 20201, 'gl_sub_group_name' => 'Bank / MFS (Card, bKash, Nagad)', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 2, 'gl_group' => 203, 'gl_sub_group' => 20301, 'gl_sub_group_name' => 'Customer Accounts Receivable', 'tradingaccount' => 0, 'Balance' => 0],
            ['gl_type' => 4, 'gl_group' => 403, 'gl_sub_group' => 40301, 'gl_sub_group_name' => 'Sales Discount', 'tradingaccount' => 0, 'Balance' => 0],
        ];

        foreach ($subgroups as $sg) {
            DB::table('acct_tbl_balsheet_sub_group')->updateOrInsert(
                ['gl_sub_group' => $sg['gl_sub_group']],
                $sg
            );
        }

        // 4. GL Accounts
        $accounts = [
            [
                'AccountNo' => '20101101',
                'title' => 'Cash In Hand',
                'gl_type' => 2,
                'gl_group' => 201,
                'gl_sub_group' => 20101,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '40201101',
                'title' => 'Cost of Good Sold (COGS)',
                'gl_type' => 4,
                'gl_group' => 402,
                'gl_sub_group' => 40201,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '30201101',
                'title' => 'Sales',
                'gl_type' => 3,
                'gl_group' => 302,
                'gl_sub_group' => 30201,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '30301101',
                'title' => 'Sales Return',
                'gl_type' => 3,
                'gl_group' => 303,
                'gl_sub_group' => 30301,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '10101101',
                'title' => 'Mr XYZ',
                'gl_type' => 1,
                'gl_group' => 101,
                'gl_sub_group' => 10101,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '20201101',
                'title' => 'Bank / MFS (Card, bKash, Nagad)',
                'gl_type' => 2,
                'gl_group' => 202,
                'gl_sub_group' => 20201,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '20301101',
                'title' => 'Accounts Receivable (Customer Due)',
                'gl_type' => 2,
                'gl_group' => 203,
                'gl_sub_group' => 20301,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
            [
                'AccountNo' => '40301101',
                'title' => 'Sales Discount',
                'gl_type' => 4,
                'gl_group' => 403,
                'gl_sub_group' => 40301,
                'AccountType' => 0,
                'Balance' => 0.00,
            ],
        ];

        foreach ($accounts as $acc) {
            DB::table('acct_tbl_balsheet_account')->updateOrInsert(
                ['AccountNo' => $acc['AccountNo']],
                $acc
            );

            // 5. Ensure balance row exists
            DB::table('acct_tbl_balance')->updateOrInsert(
                ['AccountNo' => $acc['AccountNo']],
                [
                    'Balance'         => 0.00,
                    'VerifiedBalance' => 0.00,
                    'Difference'      => 0.00,
                    'EventNo'         => 0,
                ]
            );
        }
    }
}
