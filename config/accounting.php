<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chart of Accounts (GL Accounts)
    |--------------------------------------------------------------------------
    | Mapped to the accounts created in the system.
    */
    'cash_account'         => '20101101', // Cash In Hand (Asset)
    'sales_account'        => '30201101', // Sales (Income)
    'sales_return_account' => '30301101', // Sales Return (Income / Contra-Income)
    'cogs_account'         => '40201101', // Cost of Good Sold (Expenditure)
    'receivable_account'   => '20301101', // Accounts Receivable / Customer Due (Asset)
    'discount_account'     => '40301101', // Sales Discount (Expenditure)
    'bank_account'         => '20201101', // Bank / MFS Accounts (Asset)

    /*
    |--------------------------------------------------------------------------
    | Debit vs Credit Nature
    |--------------------------------------------------------------------------
    | 2: Asset (Debit-natured: Dr - Cr)
    | 4: Expenditure (Debit-natured: Dr - Cr)
    | 5: Expenditure / Other Debit
    | 1: Liability (Credit-natured: Cr - Dr)
    | 3: Income (Credit-natured: Cr - Dr)
    */
    'debit_natured_types' => [2, 4, 5],

    'gl_types' => [
        1 => 'Liability',
        2 => 'Asset',
        3 => 'Income',
        4 => 'Expenditure',
        5 => 'Equity',
    ],

    /*
    |--------------------------------------------------------------------------
    | Automation
    |--------------------------------------------------------------------------
    | Set to true so POS sales and returns are automatically authorized
    | and posted directly to the accounting ledger (no manual review needed).
    */
    'auto_authorize_pos' => true,
];
