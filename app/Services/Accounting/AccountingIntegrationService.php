<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\ReturnItem;
use Exception;

class AccountingIntegrationService
{
    /**
     * GL account numbers used by POS and Billing.
     */
    public function cashAccount(): string
    {
        return (string) config('accounting.cash_account', '20101101');
    }

    public function salesAccount(): string
    {
        return (string) config('accounting.sales_account', '30201101');
    }

    public function salesReturnAccount(): string
    {
        return (string) config('accounting.sales_return_account', '30301101');
    }

    public function cogsAccount(): string
    {
        return (string) config('accounting.cogs_account', '40201101');
    }

    public function receivableAccount(): string
    {
        return (string) config('accounting.receivable_account', '20301101');
    }

    public function discountAccount(): string
    {
        return (string) config('accounting.discount_account', '40301101');
    }

    public function bankAccount(): string
    {
        return (string) config('accounting.bank_account', '20201101');
    }

    /**
     * Fallback definitions for auto-creating accounts if not already in DB.
     */
    private const GL_DEFINITIONS = [
        '20101101' => [
            'title' => 'Cash In Hand',
            'gl_type' => 2,
            'gl_group' => 201,
            'gl_group_name' => 'Cash In Hand',
            'gl_sub_group' => 20101,
            'gl_sub_group_name' => 'Cash In Hand',
        ],
        '30201101' => [
            'title' => 'Sales',
            'gl_type' => 3,
            'gl_group' => 302,
            'gl_group_name' => 'Sales',
            'gl_sub_group' => 30201,
            'gl_sub_group_name' => 'Sales',
        ],
        '30301101' => [
            'title' => 'Sales Return',
            'gl_type' => 3,
            'gl_group' => 303,
            'gl_group_name' => 'Sales Return',
            'gl_sub_group' => 30301,
            'gl_sub_group_name' => 'Sales Return',
        ],
        '40201101' => [
            'title' => 'Cost of Good Sold (COGS)',
            'gl_type' => 4,
            'gl_group' => 402,
            'gl_group_name' => 'Cost of Good Sold (COGS)',
            'gl_sub_group' => 40201,
            'gl_sub_group_name' => 'Cost of Good Sold (COGS)',
        ],
        '20301101' => [
            'title' => 'Accounts Receivable (Customer Due)',
            'gl_type' => 2,
            'gl_group' => 203,
            'gl_group_name' => 'Accounts Receivable',
            'gl_sub_group' => 20301,
            'gl_sub_group_name' => 'Customer Accounts Receivable',
        ],
        '40301101' => [
            'title' => 'Sales Discount',
            'gl_type' => 4,
            'gl_group' => 403,
            'gl_group_name' => 'Sales Discount',
            'gl_sub_group' => 40301,
            'gl_sub_group_name' => 'Sales Discount',
        ],
        '20201101' => [
            'title' => 'Bank / MFS (Card, bKash, Nagad)',
            'gl_type' => 2,
            'gl_group' => 202,
            'gl_group_name' => 'Bank / MFS Accounts',
            'gl_sub_group' => 20201,
            'gl_sub_group_name' => 'Bank / MFS (Card, bKash, Nagad)',
        ],
        // Legacy clinical accounts mapping
        '205101101' => ['title' => 'Cash', 'gl_type' => 2, 'gl_group' => 205, 'gl_group_name' => 'Cash', 'gl_sub_group' => 205101, 'gl_sub_group_name' => 'Cash Account'],
        '20701101'  => ['title' => 'Patient Accounts Receivable', 'gl_type' => 2, 'gl_group' => 207, 'gl_group_name' => 'Receivable', 'gl_sub_group' => 20701, 'gl_sub_group_name' => 'Patient Accounts Receivable'],
        '40601101'  => ['title' => 'Investigation Income', 'gl_type' => 4, 'gl_group' => 406, 'gl_group_name' => 'Laboratory Income', 'gl_sub_group' => 40601, 'gl_sub_group_name' => 'Lab Services'],
        '51501101'  => ['title' => 'Investigation Discount', 'gl_type' => 5, 'gl_group' => 515, 'gl_group_name' => 'Discount Group', 'gl_sub_group' => 51501, 'gl_sub_group_name' => 'Investigation Discount'],
    ];

    /**
     * Post a POS Sale into General Ledger.
     */
    public function postPosSale(Sale $sale): void
    {
        $refNumber = 'SL-' . $sale->id;
        if (strlen($refNumber) > 10) {
            $refNumber = substr('SL-' . $sale->id, 0, 10);
        }

        if ($this->checkDuplicate($refNumber)) {
            return;
        }

        $paid = round((float) ($sale->paid_amount ?? 0), 2);
        $due = round((float) ($sale->due_amount ?? 0), 2);
        $discount = round((float) ($sale->discount_amount ?? 0), 2);

        $grossSales = round($paid + $due + $discount, 2);
        if ($grossSales <= 0) {
            return;
        }

        $paymentMethod = strtolower((string) ($sale->payment_method ?? 'cash'));
        $cashOrBank = in_array($paymentMethod, ['card', 'bank', 'bkash', 'bcash', 'nagad', 'rocket'], true)
            ? $this->bankAccount()
            : $this->cashAccount();

        $debits = [];
        $credits = [];

        if ($paid > 0) {
            $debits[] = ['account' => $cashOrBank, 'amount' => $paid];
        }
        if ($due > 0) {
            $debits[] = ['account' => $this->receivableAccount(), 'amount' => $due];
        }
        if ($discount > 0) {
            $debits[] = ['account' => $this->discountAccount(), 'amount' => $discount];
        }

        $credits[] = ['account' => $this->salesAccount(), 'amount' => $grossSales];

        $paymentDate = $sale->sale_date ? $sale->sale_date->format('Y-m-d') : now()->format('Y-m-d');
        $serial = (string) ($sale->invoice_number ?: ('#' . $sale->id));
        $particular = "POS Sale Invoice " . $serial;

        $this->createVoucher($debits, $credits, $refNumber, $paymentDate, $particular, 'POS', $serial);

        if (config('accounting.auto_authorize_pos', false)) {
            $this->authorizeVoucher($refNumber);
        }
    }

    /**
     * Post a Sales Return into General Ledger.
     */
    public function postSalesReturn(ReturnItem $returnItem, ?Sale $sale = null): void
    {
        $refNumber = 'RET-' . $returnItem->id;
        if (strlen($refNumber) > 10) {
            $refNumber = substr('RET-' . $returnItem->id, 0, 10);
        }

        if ($this->checkDuplicate($refNumber)) {
            return;
        }

        $refundAmount = round((float) ($returnItem->refund_amount ?? 0), 2);
        if ($refundAmount <= 0) {
            return;
        }

        $refundMethod = strtolower((string) ($returnItem->refund_method ?? 'cash'));
        $returnDate = $returnItem->return_date ? date('Y-m-d', strtotime((string)$returnItem->return_date)) : now()->format('Y-m-d');

        $debits = [
            ['account' => $this->salesReturnAccount(), 'amount' => $refundAmount],
        ];

        $credits = [];
        if ($refundMethod === 'credit') {
            $credits[] = ['account' => $this->receivableAccount(), 'amount' => $refundAmount];
        } else {
            $credits[] = ['account' => $this->cashAccount(), 'amount' => $refundAmount];
        }

        $invLabel = $sale ? $sale->invoice_number : ('Sale #' . $returnItem->sale_id);
        $serial = 'RET-' . $returnItem->id;
        $particular = "Sales Return #" . $returnItem->id . " for " . $invLabel;

        $this->createVoucher($debits, $credits, $refNumber, $returnDate, $particular, 'RET', $serial);

        if (config('accounting.auto_authorize_pos', false)) {
            $this->authorizeVoucher($refNumber);
        }
    }

    /**
     * Post Customer Due Payment into General Ledger.
     */
    public function postCustomerDuePayment(Sale $sale, float $payingAmount, string $method = 'cash', ?string $paymentDate = null): void
    {
        $payingAmount = round($payingAmount, 2);
        if ($payingAmount <= 0) {
            return;
        }

        $refNumber = substr('COL-' . $sale->id . '-' . time(), 0, 10);
        if ($this->checkDuplicate($refNumber)) {
            return;
        }

        $cashOrBank = in_array(strtolower($method), ['card', 'bank', 'bkash', 'bcash', 'nagad', 'rocket'], true)
            ? $this->bankAccount()
            : $this->cashAccount();

        $debits = [
            ['account' => $cashOrBank, 'amount' => $payingAmount],
        ];
        $credits = [
            ['account' => $this->receivableAccount(), 'amount' => $payingAmount],
        ];

        $date = $paymentDate ?: now()->format('Y-m-d');
        $serial = (string) ($sale->invoice_number ?: ('#' . $sale->id));
        $particular = "Due Collection for " . ($sale->invoice_number ?: ('Sale #' . $sale->id));

        $this->createVoucher($debits, $credits, $refNumber, $date, $particular, 'DUE', $serial);

        if (config('accounting.auto_authorize_pos', false)) {
            $this->authorizeVoucher($refNumber);
        }
    }

    /**
     * Create voucher rows in temporary / unauth tables.
     */
    private function createVoucher(array $debits, array $credits, string $refNumber, string $postingDate, string $particular, string $screenType, string $serial = ''): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($debits as $dr) {
            $totalDebit += round($dr['amount'], 2);
            $this->ensureGlAccount($dr['account']);
        }
        foreach ($credits as $cr) {
            $totalCredit += round($cr['amount'], 2);
            $this->ensureGlAccount($cr['account']);
        }

        if ($totalDebit == 0 && $totalCredit == 0) {
            throw new Exception("Accounting Error: Zero amounts for Ref: $refNumber");
        }
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new Exception("Accounting Error: Debit ($totalDebit) and Credit ($totalCredit) do not balance for Ref: $refNumber");
        }

        $inputerId = (string) (Auth::id() ?? '1');
        $now = now();

        foreach ($debits as $dr) {
            if (round($dr['amount'], 2) > 0) {
                DB::table('acct_tbl_transaction_tmp')->insert([
                    'AccountNo'    => $dr['account'],
                    'TrType'       => 'Debit',
                    'DrAmount'     => round($dr['amount'], 2),
                    'CrAmount'     => 0,
                    'Particular'   => $particular,
                    'Balance'      => $this->provisionalBalance($dr['account'], round($dr['amount'], 2), 'Debit'),
                    'PostingDate'  => $postingDate,
                    'RefNumber'    => $refNumber,
                    'ScreenType'   => $screenType,
                    'FactoryCode'  => null,
                    'EmployeeID'   => '',
                    'MoNumber'     => $serial,
                    'Serial'       => $serial,
                    'InputerID'    => $inputerId,
                    'AuthorizerID' => '',
                    'Timestamp'    => $now,
                ]);
            }
        }

        foreach ($credits as $cr) {
            if (round($cr['amount'], 2) > 0) {
                DB::table('acct_tbl_transaction_tmp')->insert([
                    'AccountNo'    => $cr['account'],
                    'TrType'       => 'Credit',
                    'DrAmount'     => 0,
                    'CrAmount'     => round($cr['amount'], 2),
                    'Particular'   => $particular,
                    'Balance'      => $this->provisionalBalance($cr['account'], round($cr['amount'], 2), 'Credit'),
                    'PostingDate'  => $postingDate,
                    'RefNumber'    => $refNumber,
                    'ScreenType'   => $screenType,
                    'FactoryCode'  => null,
                    'EmployeeID'   => '',
                    'MoNumber'     => $serial,
                    'Serial'       => $serial,
                    'InputerID'    => $inputerId,
                    'AuthorizerID' => '',
                    'Timestamp'    => $now,
                ]);
            }
        }

        DB::table('acct_tbl_unauth_transaction')->insert([
            'DrAccount'    => $debits[0]['account'] ?? '',
            'CrAccount'    => $credits[0]['account'] ?? '',
            'Particular'   => $particular,
            'amount'       => round($totalDebit, 2),
            'FactoryCode'  => null,
            'EmployeeID'   => '',
            'PostingDate'  => $postingDate,
            'RefNumber'    => $refNumber,
            'Serial'       => $serial,
            'MoNumber'     => $serial,
            'InputerID'    => $inputerId,
            'AuthorizerID' => '',
            'ScreenType'   => $screenType,
            'AutoPosting'  => $now,
        ]);
    }

    /**
     * Authorize a voucher and commit it to final transaction & balance ledger.
     */
    public function authorizeVoucher(string $refNumber): void
    {
        $tmpRows = DB::table('acct_tbl_transaction_tmp')
            ->where('RefNumber', $refNumber)
            ->get();

        if ($tmpRows->isEmpty()) {
            return;
        }

        $authorizerId = (string) (Auth::id() ?? '1');

        foreach ($tmpRows as $tran) {
            DB::table('acct_tbl_transaction')->insert([
                'AccountNo'    => $tran->AccountNo,
                'TrType'       => $tran->TrType,
                'Particular'   => $tran->Particular,
                'DrAmount'     => $tran->DrAmount,
                'CrAmount'     => $tran->CrAmount,
                'Balance'      => 0,
                'PostingDate'  => $tran->PostingDate,
                'RefNumber'    => (string) $tran->RefNumber,
                'MatGroupNo'   => $tran->MatGroupNo ?? 0,
                'InputerID'    => (string) ($tran->InputerID ?? ''),
                'AuthorizerID' => $authorizerId,
                'EmployeeID'   => (string) ($tran->EmployeeID ?? ''),
                'FactoryCode'  => (string) ($tran->FactoryCode ?? ''),
                'SupplierCode' => (string) ($tran->SupplierCode ?? ''),
                'Serial'       => (string) ($tran->MoNumber ?? $tran->Serial ?? ''),
                'ScreenType'   => (string) ($tran->ScreenType ?? ''),
            ]);
        }

        $accountNos = $tmpRows->pluck('AccountNo')->unique()->values()->all();

        foreach ($accountNos as $acctNo) {
            $glType = (int) (DB::table('acct_tbl_balsheet_account')->where('AccountNo', $acctNo)->value('gl_type') ?? 2);
            $this->recomputeAndSaveBalance((string) $acctNo, $glType);
        }

        DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->delete();
        DB::table('acct_tbl_unauth_transaction')->where('RefNumber', $refNumber)->delete();
    }

    /**
     * Recalculates balance for an account and persists to acct_tbl_balance and acct_tbl_balsheet_account.
     */
    private function recomputeAndSaveBalance(string $accountNo, int $glType): void
    {
        $rows = DB::table('acct_tbl_transaction')
            ->where('AccountNo', $accountNo)
            ->orderBy('PostingDate', 'asc')
            ->orderBy('ID', 'asc')
            ->get(['ID', 'DrAmount', 'CrAmount']);

        $running = 0.0;
        foreach ($rows as $r) {
            $running = round($running + GlType::signedAmount($glType, (float) $r->DrAmount, (float) $r->CrAmount), 2);
            DB::table('acct_tbl_transaction')->where('ID', $r->ID)->update(['Balance' => $running]);
        }

        // Update acct_tbl_balance
        DB::table('acct_tbl_balance')->updateOrInsert(
            ['AccountNo' => $accountNo],
            [
                'Balance'         => $running,
                'VerifiedBalance' => 0,
                'Difference'      => 0,
                'EventNo'         => 0,
            ]
        );

        // Update acct_tbl_balsheet_account
        DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->update([
            'Balance' => $running,
        ]);
    }

    private function checkDuplicate(string $refNumber): bool
    {
        return DB::table('acct_tbl_transaction_tmp')->where('RefNumber', $refNumber)->exists()
            || DB::table('acct_tbl_transaction')->where('RefNumber', $refNumber)->exists();
    }

    private function provisionalBalance(string $accountNo, float $amount, string $trType): float
    {
        $glType = (int) (DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->value('gl_type') ?? 0);
        $isDebit = $trType === 'Debit';
        $delta = GlType::signedAmount($glType, $isDebit ? $amount : 0.0, $isDebit ? 0.0 : $amount);
        $currentBalance = (float) (DB::table('acct_tbl_balance')->where('AccountNo', $accountNo)->value('Balance') ?? 0);

        return round($currentBalance + $delta, 2);
    }

    private function ensureGlAccount(string $accountNo): void
    {
        if (DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->exists()) {
            return;
        }

        $def = self::GL_DEFINITIONS[$accountNo] ?? null;
        if (!$def) {
            throw new Exception("Accounting Error: GL Account {$accountNo} does not exist and has no auto-create definition.");
        }

        DB::transaction(function () use ($accountNo, $def) {
            if (!DB::table('acct_tbl_balsheet_group')->where('gl_group', $def['gl_group'])->exists()) {
                DB::table('acct_tbl_balsheet_group')->insert([
                    'gl_type'        => $def['gl_type'],
                    'gl_group'       => $def['gl_group'],
                    'gl_group_name'  => $def['gl_group_name'],
                    'tradingaccount' => 0,
                    'Balance'        => 0,
                ]);
            }

            if (!DB::table('acct_tbl_balsheet_sub_group')->where('gl_sub_group', $def['gl_sub_group'])->exists()) {
                DB::table('acct_tbl_balsheet_sub_group')->insert([
                    'gl_type'           => $def['gl_type'],
                    'gl_group'          => $def['gl_group'],
                    'gl_sub_group'      => $def['gl_sub_group'],
                    'gl_sub_group_name' => $def['gl_sub_group_name'],
                    'tradingaccount'    => 0,
                    'Balance'           => 0,
                ]);
            }

            if (!DB::table('acct_tbl_balsheet_account')->where('AccountNo', $accountNo)->exists()) {
                DB::table('acct_tbl_balsheet_account')->insert([
                    'AccountNo'   => $accountNo,
                    'title'       => $def['title'],
                    'gl_type'     => $def['gl_type'],
                    'gl_group'    => $def['gl_group'],
                    'gl_sub_group'=> $def['gl_sub_group'],
                    'Balance'     => 0,
                    'AccountType' => 0,
                    'Position'    => 0,
                ]);
            }

            DB::table('acct_tbl_balance')->updateOrInsert(
                ['AccountNo' => $accountNo],
                [
                    'Balance'         => 0,
                    'VerifiedBalance' => 0,
                    'Difference'      => 0,
                    'EventNo'         => 0,
                ]
            );
        });

        \Log::info('Accounting: GL account auto-created', ['AccountNo' => $accountNo, 'title' => $def['title']]);
    }
}