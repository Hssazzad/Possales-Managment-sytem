<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Accounting\AcctTblBalsheetAccount;
use App\Services\Accounting\GlType;

class TransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Table names
    |--------------------------------------------------------------------------
    */
    private const TBL_ACCOUNT      = 'acct_tbl_balsheet_account';
    private const TBL_BALANCE      = 'acct_tbl_balance';
    private const TBL_TRAN_TMP     = 'acct_tbl_transaction_tmp';
    private const TBL_UNAUTH_TRAN  = 'acct_tbl_unauth_transaction';
    private const TBL_TRANSACTION  = 'acct_tbl_transaction';
    private const TBL_OPENNING_BALANCE = 'acct_tbl_openning_balance';

    /**
     * Seed values for the non-Balance columns of acct_tbl_balance.
     */
    private const BALANCE_ROW_DEFAULTS = [
        'VerifiedBalance' => 0,
        'Difference'      => 0,
        'EventNo'         => 0,
    ];

    /**
     * Cached column listing for acct_tbl_balance.
     */
    private ?array $balanceColumns = null;

    /**
     * Fixed Cash account used by the Cash Payment / Cash Receive screens.
     */
    private function cashAccountNo(): string
    {
        return (string) config('accounting.cash_account', '205101101');
    }

    /**
     * Validation rules for a posting date.
     */
    private function postingDateRules(): array
    {
        $min = (string) config('accounting.posting.min_date', '2000-01-01');
        $max = now()->addDays((int) config('accounting.posting.future_days', 0))->format('Y-m-d');

        return ['required', 'date', "after_or_equal:{$min}", "before_or_equal:{$max}"];
    }

    /**
     * Validation rules for an account number on a voucher.
     */
    private function accountRules(): array
    {
        return ['required', 'string', 'exists:' . self::TBL_ACCOUNT . ',AccountNo'];
    }

    /**
     * Account numbers considered "bank" accounts (GL group 202 or bank in title).
     * Used to keep bank accounts out of the opposite-side dropdowns on the
     * Bank Receive / Bank Payment screens — bank-to-bank moves belong on
     * the Transfer Posting screen instead.
     */
    private function bankAccountNumbers(): array
    {
        $numbers = AcctTblBalsheetAccount::where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->pluck('AccountNo')
            ->map(fn ($no) => (string) $no)
            ->all();

        // Mirror the fallback used by the bank-side dropdowns: with no real
        // bank account configured, group-2xx ledgers (other than cash) act as
        // the bank side, so they must also be excluded from the other side.
        if ($numbers === []) {
            $numbers = AcctTblBalsheetAccount::where('gl_group', 'like', '2%')
                ->where('AccountNo', '!=', $this->cashAccountNo())
                ->pluck('AccountNo')
                ->map(fn ($no) => (string) $no)
                ->all();
        }

        return $numbers;
    }

    private function postingValidationMessages(): array
    {
        return [
            'Amount.gt'                   => 'Amount must be greater than zero.',
            'debitsign.in'                => 'Invalid debit sign. Please reload the form and submit again.',
            'creditsign.in'               => 'Invalid credit sign. Please reload the form and submit again.',
            'PostingDate.after_or_equal'  => 'Posting date is earlier than the accounting system allows.',
            'PostingDate.before_or_equal' => 'Posting date cannot be in the future.',
            'DebitAccount.exists'         => 'The selected debit account does not exist.',
            'DebitAccount.different'      => 'Debit and credit account cannot be the same.',
            'CreditAccount.exists'        => 'The selected credit account does not exist.',
            'CreditAccount.not_in'        => 'A bank account cannot be selected on this side. For bank-to-bank transfers use Transfer Posting.',
            'DebitAccount.not_in'         => 'A bank account cannot be selected on this side. For bank-to-bank transfers use Transfer Posting.',
        ];
    }

    // =====================================================
    // CASH PAYMENT — form load
    // =====================================================
    public function cashPayment(Request $request)
    {
        $drAccounts = AcctTblBalsheetAccount::where('AccountNo', '!=', $this->cashAccountNo())
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);

        

        $refNumber = session('refNumber');

        $cashBalance = DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $this->cashAccountNo())
            ->value('Balance');

        return view('transaction.cashpayment', [
            'drAccount'      => $drAccounts,
            'refNumber'      => $refNumber,
            'cashBalance'    => $cashBalance ?? 0,
            'cashAccountNo'  => $this->cashAccountNo(),
        ]);
    }

    // =====================================================
    // CASH PAYMENT — save
    // =====================================================
    public function cashPaymentAction(Request $request)
    {
        $validated = $request->validate([
            'debitsign'     => ['required', 'in:-1'],
            'creditsign'    => ['required', 'in:1'],
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PostingDate'   => $this->postingDateRules(),
            'DebitAccount'  => array_merge($this->accountRules(), ['not_in:' . $this->cashAccountNo()]),
           
            'Serial'        => ['required'],
        ], $this->postingValidationMessages());

        return DB::transaction(function () use ($request, $validated) {
            $refNumber = $this->nextReferenceNumber();

            $creditAccount = $this->cashAccountNo();;
            $debitAccount  = $validated['DebitAccount'];
            $amount        = $validated['Amount'];
            $postingDate   = date('Y-m-d', strtotime($validated['PostingDate']));
            $inputerId     = (string) (Auth::id() ?? '');

            $newDebitBal  = $this->provisionalBalance($debitAccount, (float) $amount, 0.0);
            $newCreditBal = $this->provisionalBalance($creditAccount, 0.0, (float) $amount);

            $debitData = [
                'AccountNo'    => $debitAccount,
                'TrType'       => 'Debit',
                'DrAmount'     => $amount,
                'CrAmount'     => 0,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newDebitBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'CP',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $creditData = [
                'AccountNo'    => $creditAccount,
                'TrType'       => 'Credit',
                'DrAmount'     => 0,
                'CrAmount'     => $amount,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newCreditBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'CP',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $unauthData = [
                'DrAccount'    => (string) $debitAccount,
                'CrAccount'    => (string) $creditAccount,
                'Particular'   => $request->input('Particular'),
                'amount'       => $amount,
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
                'Serial'       => $request->input('Serial'),
                'MoNumber'     => $request->input('Serial'),
                'ScreenType'   => 'CP',
            ];

            DB::table(self::TBL_TRAN_TMP)->insert($debitData);
            DB::table(self::TBL_TRAN_TMP)->insert($creditData);
            DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthData);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.cashpayment')
                ->with('success', 'Saved Successfully')
                ->with('refNumber', $refNumber);
        });
    }

    // =====================================================
    // CASH RECEIVE — form load
    // =====================================================
    public function cashReceive(Request $request)
    {
        $purpose = [
            'PATIENT_PAY' => 'Patient Payment',
            'CONSULT_FEE' => 'Consultation Fee',
            'ADMISSION_FEE' => 'Admission Fee',
            'BED_CHARGE' => 'Bed Charge',
            'OPERATION_FEE' => 'Operation Fee',
            'PHARMACY_SALE' => 'Pharmacy Sale',
            'LABORATORY_FEE' => 'Laboratory Fee',
            'OTHER_INCOME' => 'Other Income',
        ];

        $crAccounts = AcctTblBalsheetAccount::where('AccountNo', '!=', $this->cashAccountNo())
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);

        $refNumber = session('refNumber');

        $cashBalance = DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $this->cashAccountNo())
            ->value('Balance');

        return view('transaction.cashreceive', [
            'crAccount'   => $crAccounts,
            'refNumber'   => $refNumber,
            'cashBalance' => $cashBalance ?? 0,
            'purpose'     => $purpose,
        ]);
    }

    // =====================================================
    // CASH RECEIVE — save
    // =====================================================
    public function cashReceiveAction(Request $request)
    {
        $validated = $request->validate([
            'debitsign'     => ['required', 'in:-1'],
            'creditsign'    => ['required', 'in:1'],
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PostingDate'   => $this->postingDateRules(),
            'CreditAccount' => array_merge($this->accountRules(), ['not_in:' . $this->cashAccountNo()]),
        ], $this->postingValidationMessages());

        return DB::transaction(function () use ($request, $validated) {
            $refNumber = $this->nextReferenceNumber();

            $debitAccount  = $this->cashAccountNo();
            $creditAccount = $validated['CreditAccount'];
            $amount        = $validated['Amount'];
            $postingDate   = date('Y-m-d', strtotime($validated['PostingDate']));
            $inputerId     = (string) (Auth::id() ?? '');

            $newDebitBal  = $this->provisionalBalance($debitAccount, (float) $amount, 0.0);
            $newCreditBal = $this->provisionalBalance($creditAccount, 0.0, (float) $amount);

            $debitData = [
                'AccountNo'    => $debitAccount,
                'TrType'       => 'Debit',
                'DrAmount'     => $amount,
                'CrAmount'     => 0,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newDebitBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'CR',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $creditData = [
                'AccountNo'    => $creditAccount,
                'TrType'       => 'Credit',
                'DrAmount'     => 0,
                'CrAmount'     => $amount,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newCreditBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'CR',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $unauthData = [
                'DrAccount'    => (string) $debitAccount,
                'CrAccount'    => (string) $creditAccount,
                'Particular'   => $request->input('Particular'),
                'amount'       => $amount,
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
                'Serial'       => $request->input('Serial'),
                'MoNumber'     => $request->input('Serial'),
                'ScreenType'   => 'CR',
            ];

            DB::table(self::TBL_TRAN_TMP)->insert($debitData);
            DB::table(self::TBL_TRAN_TMP)->insert($creditData);
            DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthData);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.cashreceive')
                ->with('success', 'Saved Successfully')
                ->with('refNumber', $refNumber);
        });
    }

    /** Cash receive report: posted and pending receipts in one list. */
    public function supplementaryCashReceive(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $receipts = $this->supplementaryCashReceiveQuery($fromDate, $toDate)
            ->paginate(30)
            ->withQueryString();

        return view('transaction.supplementary_cashreceive', compact('receipts', 'fromDate', 'toDate'));
    }

    public function supplementaryCashReceivePdf(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $records = $this->supplementaryCashReceiveQuery($fromDate, $toDate)->get();

        return Pdf::loadView('transaction.supplementary_transaction_pdf', [
            'title' => 'Cash Receive Report',
            'reportSubtitle' => 'All posted and pending cash receipts for the selected period',
            'numberLabel' => 'Receipt No.',
            'accountLabel' => 'Receive From',
            'accent' => '#047857',
            'accentSoft' => '#ecfdf5',
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'generatedBy' => Auth::user()->name ?? '',
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('cash-receive-report.pdf');
    }

    private function supplementaryCashReceiveQuery(?string $fromDate, ?string $toDate)
    {
        $cashAccount = $this->cashAccountNo();

        $posted = DB::table(self::TBL_TRANSACTION . ' as t')
            ->leftJoin(self::TBL_ACCOUNT . ' as a', 'a.AccountNo', '=', 't.AccountNo')
            ->where('t.AccountNo', $cashAccount)
            ->where('t.DrAmount', '>', 0)
            ->whereIn('t.ScreenType', ['CR', 'MCR'])
            ->selectRaw("t.PostingDate as posting_date, t.RefNumber as ref_number, COALESCE(t.Serial, t.MoNumber, '') as receipt_number, t.Particular as particular, t.DrAmount as amount, 'Posted' as status, (SELECT title FROM " . self::TBL_ACCOUNT . " WHERE AccountNo = (SELECT AccountNo FROM " . self::TBL_TRANSACTION . " WHERE RefNumber = t.RefNumber AND AccountNo <> ? LIMIT 1) LIMIT 1) as account_title", [$cashAccount]);

        $pending = DB::table(self::TBL_UNAUTH_TRAN . ' as u')
            ->leftJoin(self::TBL_ACCOUNT . ' as a', 'a.AccountNo', '=', 'u.CrAccount')
            ->whereIn('u.ScreenType', ['CR', 'MCR'])
            ->selectRaw("u.PostingDate as posting_date, u.RefNumber as ref_number, COALESCE(u.Serial, u.MoNumber, '') as receipt_number, u.Particular as particular, u.amount as amount, 'Pending' as status, a.title as account_title");

        foreach ([$posted, $pending] as $query) {
            if ($fromDate) $query->where('PostingDate', '>=', $fromDate);
            if ($toDate) $query->where('PostingDate', '<=', $toDate);
        }

        return $posted->unionAll($pending)
            ->orderByDesc('posting_date')
            ->orderByDesc('ref_number');
    }

    /** Cash payment report: posted and pending payments in one list. */
    public function supplementaryCashPayment(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $payments = $this->supplementaryCashPaymentQuery($fromDate, $toDate)
            ->paginate(30)
            ->withQueryString();

        return view('transaction.supplementary_cashpayment', compact('payments', 'fromDate', 'toDate'));
    }

    public function supplementaryCashPaymentPdf(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $records = $this->supplementaryCashPaymentQuery($fromDate, $toDate)->get();

        return Pdf::loadView('transaction.supplementary_transaction_pdf', [
            'title' => 'Cash Payment Report',
            'reportSubtitle' => 'All posted and pending cash payments for the selected period',
            'numberLabel' => 'Payment No.',
            'accountLabel' => 'Paid To',
            'accent' => '#b91c1c',
            'accentSoft' => '#fef2f2',
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'generatedBy' => Auth::user()->name ?? '',
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('cash-payment-report.pdf');
    }

    private function supplementaryCashPaymentQuery(?string $fromDate, ?string $toDate)
    {
        $cashAccount = $this->cashAccountNo();

        $posted = DB::table(self::TBL_TRANSACTION . ' as t')
            ->where('t.AccountNo', $cashAccount)
            ->where('t.CrAmount', '>', 0)
            ->where('t.ScreenType', 'CP')
            ->selectRaw("t.PostingDate as posting_date, t.RefNumber as ref_number, COALESCE(t.Serial, t.MoNumber, '') as receipt_number, t.Particular as particular, t.CrAmount as amount, 'Posted' as status, (SELECT title FROM " . self::TBL_ACCOUNT . " WHERE AccountNo = (SELECT AccountNo FROM " . self::TBL_TRANSACTION . " WHERE RefNumber = t.RefNumber AND AccountNo <> ? LIMIT 1) LIMIT 1) as account_title", [$cashAccount]);

        $pending = DB::table(self::TBL_UNAUTH_TRAN . ' as u')
            ->leftJoin(self::TBL_ACCOUNT . ' as a', 'a.AccountNo', '=', 'u.DrAccount')
            ->where('u.ScreenType', 'CP')
            ->selectRaw("u.PostingDate as posting_date, u.RefNumber as ref_number, COALESCE(u.Serial, u.MoNumber, '') as receipt_number, u.Particular as particular, u.amount as amount, 'Pending' as status, a.title as account_title");

        foreach ([$posted, $pending] as $query) {
            if ($fromDate) $query->where('PostingDate', '>=', $fromDate);
            if ($toDate) $query->where('PostingDate', '<=', $toDate);
        }

        return $posted->unionAll($pending)
            ->orderByDesc('posting_date')
            ->orderByDesc('ref_number');
    }

    /** Trial balance from posted transactions up to the selected date. */
    public function supplementaryTrialBalance(Request $request)
    {
        $reportDate = $request->input('report_date', now()->format('Y-m-d'));

        $accounts = DB::table(self::TBL_ACCOUNT . ' as a')
            ->leftJoin(self::TBL_TRANSACTION . ' as t', function ($join) use ($reportDate) {
                $join->on('t.AccountNo', '=', 'a.AccountNo')->where('t.PostingDate', '<=', $reportDate);
            })
            ->select('a.AccountNo', 'a.title')
            ->selectRaw('COALESCE(SUM(t.DrAmount), 0) as debit')
            ->selectRaw('COALESCE(SUM(t.CrAmount), 0) as credit')
            ->groupBy('a.AccountNo', 'a.title')
            ->havingRaw('COALESCE(SUM(t.DrAmount), 0) <> 0 OR COALESCE(SUM(t.CrAmount), 0) <> 0')
            ->orderBy('a.AccountNo')
            ->get();

        $totalDebit = (float) $accounts->sum('debit');
        $totalCredit = (float) $accounts->sum('credit');

        return view('transaction.supplementary_trialbalance', compact('accounts', 'reportDate', 'totalDebit', 'totalCredit'));
    }

    public function supplementaryTrialBalancePdf(Request $request)
    {
        $data = $this->supplementaryTrialBalance($request)->getData();

        return Pdf::loadView('transaction.supplementary_trialbalance_pdf', $data + [
            'generatedBy' => Auth::user()->name ?? '',
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'landscape')
            ->download('trial-balance-report.pdf');
    }

    // =====================================================
    // MULTIPLE CASH RECEIVE
    // =====================================================
    public function multipleCashReceive()
    {
        return view('transaction.multiplecashreceive', [
            'accounts' => AcctTblBalsheetAccount::where('AccountNo', '!=', $this->cashAccountNo())
                ->orderBy('title')->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']),
            'cashBalance' => DB::table(self::TBL_BALANCE)->where('AccountNo', $this->cashAccountNo())->value('Balance') ?? 0,
            'refNumber' => session('refNumber'),
            'lastReceipt' => session('lastReceipt'),
        ]);
    }

    public function multipleCashReceiveAction(Request $request)
    {
        $validated = $request->validate([
            'PostingDate' => $this->postingDateRules(),
            'Serial' => ['nullable', 'string', 'max:100'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.CreditAccount' => array_merge($this->accountRules(), ['not_in:' . $this->cashAccountNo()]),
            'rows.*.Amount' => ['required', 'numeric', 'gt:0'],
            'rows.*.Particular' => ['nullable', 'string', 'max:255'],
        ], $this->postingValidationMessages());

        // One entry screen, one shared receipt: every line of this batch must
        // carry the same Serial so the authorize list / review can group the
        // batch as a single payment with an itemwise breakup.
        $givenSerial = trim((string) ($validated['Serial'] ?? ''));

        $result = DB::transaction(function () use ($validated, $givenSerial) {
            $refs = [];
            $total = 0.0;
            foreach ($validated['rows'] as $row) {
                $refs[] = $this->queueCashReceive(
                    $row['CreditAccount'], (float) $row['Amount'], $validated['PostingDate'],
                    $row['Particular'] ?? '', $givenSerial
                );
                $total += (float) $row['Amount'];
            }

            // User left receipt number blank: adopt the first RefNumber as the
            // shared receipt number so the batch still groups as one payment
            // without forcing extra typing on entry.
            $receiptNo = $givenSerial !== '' ? $givenSerial : (string) $refs[0];
            if ($givenSerial === '') {
                DB::table(self::TBL_UNAUTH_TRAN)
                    ->whereIn('RefNumber', $refs)
                    ->update(['Serial' => $receiptNo, 'MoNumber' => $receiptNo]);
                DB::table(self::TBL_TRAN_TMP)
                    ->whereIn('RefNumber', $refs)
                    ->update(['MoNumber' => $receiptNo]);
            }

            return [
                'refs' => $refs,
                'total' => round($total, 2),
                'receiptNo' => $receiptNo,
            ];
        });

        $receipt = [
            'receipt_no' => $result['receiptNo'],
            'refs' => array_map('strval', $result['refs']),
            'count' => count($result['refs']),
            'total' => $result['total'],
        ];

        session(['refNumber' => end($result['refs']), 'lastReceipt' => $receipt]);

        return redirect()->route('transaction.multiplecashreceive')
            ->with('success', 'Receipt #' . $receipt['receipt_no'] . ': ' . $receipt['count']
                . ' item(s), total Tk. ' . number_format($receipt['total'], 2) . ' saved successfully.')
            ->with('refNumber', end($result['refs']))
            ->with('lastReceipt', $receipt);
    }

    private function queueCashReceive(string $creditAccount, float $amount, string $postingDate, string $particular, string $serial): int
    {
        $refNumber = $this->nextReferenceNumber();
        $cashAccount = $this->cashAccountNo();
        $date = date('Y-m-d', strtotime($postingDate));
        $inputerId = (string) (Auth::id() ?? '');

        DB::table(self::TBL_TRAN_TMP)->insert([
            ['AccountNo' => $cashAccount, 'TrType' => 'Debit', 'DrAmount' => $amount, 'CrAmount' => 0, 'Particular' => $particular, 'Balance' => $this->provisionalBalance($cashAccount, $amount, 0), 'PostingDate' => $date, 'RefNumber' => (string) $refNumber, 'ScreenType' => 'MCR', 'FactoryCode' => '', 'EmployeeID' => '', 'MoNumber' => $serial, 'InputerID' => $inputerId, 'AuthorizerID' => ''],
            ['AccountNo' => $creditAccount, 'TrType' => 'Credit', 'DrAmount' => 0, 'CrAmount' => $amount, 'Particular' => $particular, 'Balance' => $this->provisionalBalance($creditAccount, 0, $amount), 'PostingDate' => $date, 'RefNumber' => (string) $refNumber, 'ScreenType' => 'MCR', 'FactoryCode' => '', 'EmployeeID' => '', 'MoNumber' => $serial, 'InputerID' => $inputerId, 'AuthorizerID' => ''],
        ]);

        DB::table(self::TBL_UNAUTH_TRAN)->insert([
            'DrAccount' => $cashAccount, 'CrAccount' => $creditAccount, 'Particular' => $particular,
            'amount' => $amount, 'FactoryCode' => '', 'EmployeeID' => '', 'PostingDate' => $date,
            'RefNumber' => (string) $refNumber, 'InputerID' => $inputerId, 'AuthorizerID' => '',
            'Serial' => $serial, 'MoNumber' => $serial, 'ScreenType' => 'MCR',
        ]);

        return $refNumber;
    }

    // =====================================================
    // MULTIPLE CASH PAYMENT
    // =====================================================
    public function multipleCashPayment()
    {
        return view('transaction.multiplecashpayment', [
            'accounts' => AcctTblBalsheetAccount::where('AccountNo', '!=', $this->cashAccountNo())
                ->orderBy('title')->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']),
            'cashBalance' => DB::table(self::TBL_BALANCE)->where('AccountNo', $this->cashAccountNo())->value('Balance') ?? 0,
            'refNumber' => session('refNumber'),
            'lastPayment' => session('lastPayment'),
        ]);
    }

    public function multipleCashPaymentAction(Request $request)
    {
        $validated = $request->validate([
            'PostingDate' => $this->postingDateRules(),
            'Serial' => ['nullable', 'string', 'max:100'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.DebitAccount' => array_merge($this->accountRules(), ['not_in:' . $this->cashAccountNo()]),
            'rows.*.Amount' => ['required', 'numeric', 'gt:0'],
            'rows.*.Particular' => ['nullable', 'string', 'max:255'],
        ], $this->postingValidationMessages());

        // One entry screen, one shared payment number: every line of this batch
        // must carry the same Serial so the authorize list / review can group
        // the batch as a single payment with an itemwise breakup.
        $givenSerial = trim((string) ($validated['Serial'] ?? ''));

        $result = DB::transaction(function () use ($validated, $givenSerial) {
            $refs = [];
            $total = 0.0;
            foreach ($validated['rows'] as $row) {
                $refs[] = $this->queueCashPayment(
                    $row['DebitAccount'], (float) $row['Amount'], $validated['PostingDate'],
                    $row['Particular'] ?? '', $givenSerial
                );
                $total += (float) $row['Amount'];
            }

            // User left payment number blank: adopt the first RefNumber as the
            // shared payment number so the batch still groups as one payment
            // without forcing extra typing on entry.
            $receiptNo = $givenSerial !== '' ? $givenSerial : (string) $refs[0];
            if ($givenSerial === '') {
                DB::table(self::TBL_UNAUTH_TRAN)
                    ->whereIn('RefNumber', $refs)
                    ->update(['Serial' => $receiptNo, 'MoNumber' => $receiptNo]);
                DB::table(self::TBL_TRAN_TMP)
                    ->whereIn('RefNumber', $refs)
                    ->update(['MoNumber' => $receiptNo]);
            }

            return [
                'refs' => $refs,
                'total' => round($total, 2),
                'receiptNo' => $receiptNo,
            ];
        });

        $payment = [
            'receipt_no' => $result['receiptNo'],
            'refs' => array_map('strval', $result['refs']),
            'count' => count($result['refs']),
            'total' => $result['total'],
        ];

        session(['refNumber' => end($result['refs']), 'lastPayment' => $payment]);

        return redirect()->route('transaction.multiplecashpayment')
            ->with('success', 'Payment #' . $payment['receipt_no'] . ': ' . $payment['count']
                . ' item(s), total Tk. ' . number_format($payment['total'], 2) . ' saved successfully.')
            ->with('refNumber', end($result['refs']))
            ->with('lastPayment', $payment);
    }

    private function queueCashPayment(string $debitAccount, float $amount, string $postingDate, string $particular, string $serial): int
    {
        $refNumber = $this->nextReferenceNumber();
        $cashAccount = $this->cashAccountNo();
        $date = date('Y-m-d', strtotime($postingDate));
        $inputerId = (string) (Auth::id() ?? '');

        DB::table(self::TBL_TRAN_TMP)->insert([
            ['AccountNo' => $debitAccount, 'TrType' => 'Debit', 'DrAmount' => $amount, 'CrAmount' => 0, 'Particular' => $particular, 'Balance' => $this->provisionalBalance($debitAccount, $amount, 0), 'PostingDate' => $date, 'RefNumber' => (string) $refNumber, 'ScreenType' => 'MCP', 'FactoryCode' => '', 'EmployeeID' => '', 'MoNumber' => $serial, 'InputerID' => $inputerId, 'AuthorizerID' => ''],
            ['AccountNo' => $cashAccount, 'TrType' => 'Credit', 'DrAmount' => 0, 'CrAmount' => $amount, 'Particular' => $particular, 'Balance' => $this->provisionalBalance($cashAccount, 0, $amount), 'PostingDate' => $date, 'RefNumber' => (string) $refNumber, 'ScreenType' => 'MCP', 'FactoryCode' => '', 'EmployeeID' => '', 'MoNumber' => $serial, 'InputerID' => $inputerId, 'AuthorizerID' => ''],
        ]);

        DB::table(self::TBL_UNAUTH_TRAN)->insert([
            'DrAccount' => $debitAccount, 'CrAccount' => $cashAccount, 'Particular' => $particular,
            'amount' => $amount, 'FactoryCode' => '', 'EmployeeID' => '', 'PostingDate' => $date,
            'RefNumber' => (string) $refNumber, 'InputerID' => $inputerId, 'AuthorizerID' => '',
            'Serial' => $serial, 'MoNumber' => $serial, 'ScreenType' => 'MCP',
        ]);

        return $refNumber;
    }

    // =====================================================
    // BANK RECEIVE — form load
    // =====================================================
    public function bankReceive(Request $request)
    {
        $bankAccounts = AcctTblBalsheetAccount::where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);

        if ($bankAccounts->isEmpty()) {
            $bankAccounts = AcctTblBalsheetAccount::where('gl_group', 'like', '2%')
                ->where('AccountNo', '!=', $this->cashAccountNo())
                ->orderBy('title')
                ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);
        }

        // The receiving side is already a bank account, so the "Receive From"
        // dropdown must not offer bank accounts — bank-to-bank belongs on
        // the Transfer Posting screen.
        $crAccounts = AcctTblBalsheetAccount::orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group'])
            ->reject(fn ($account) => in_array((string) $account->AccountNo, $this->bankAccountNumbers(), true))
            ->values();

        $refNumber = session('refNumber');

        return view('transaction.bankreceive', [
            'bankAccounts' => $bankAccounts,
            'crAccounts'   => $crAccounts,
            'refNumber'    => $refNumber,
        ]);
    }

    // =====================================================
    // BANK RECEIVE — save
    // =====================================================
    public function bankReceiveAction(Request $request)
    {
        $validated = $request->validate([
            'debitsign'     => ['required', 'in:-1'],
            'creditsign'    => ['required', 'in:1'],
            'DebitAccount'  => $this->accountRules(),
            'CreditAccount' => array_merge($this->accountRules(), ['different:DebitAccount', 'not_in:' . implode(',', $this->bankAccountNumbers())]),
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PostingDate'   => $this->postingDateRules(),
            'Serial'        => ['nullable', 'string', 'max:100'],
            'BankName'      => ['nullable', 'string', 'max:150'],
            'ChequeDate'    => ['nullable', 'string', 'max:50'],
            'Particular'    => ['nullable', 'string', 'max:255'],
        ], $this->postingValidationMessages());

        return DB::transaction(function () use ($request, $validated) {
            $refNumber = $this->nextReferenceNumber();

            $debitAccount  = $validated['DebitAccount'];
            $creditAccount = $validated['CreditAccount'];
            $amount        = $validated['Amount'];
            $postingDate   = date('Y-m-d', strtotime($validated['PostingDate']));
            $inputerId     = (string) (Auth::id() ?? '');

            $particular = trim((string) ($validated['Particular'] ?? ''));
            $extras = [];
            if (!empty($validated['Serial'])) {
                $extras[] = 'Chq/Ref: ' . $validated['Serial'];
            }
            if (!empty($validated['BankName'])) {
                $extras[] = 'Bank: ' . $validated['BankName'];
            }
            if (!empty($validated['ChequeDate'])) {
                $extras[] = 'Date: ' . $validated['ChequeDate'];
            }
            if (!empty($extras)) {
                $particular = $particular !== '' ? $particular . ' (' . implode(', ', $extras) . ')' : implode(', ', $extras);
            }

            $newDebitBal  = $this->provisionalBalance($debitAccount, (float) $amount, 0.0);
            $newCreditBal = $this->provisionalBalance($creditAccount, 0.0, (float) $amount);

            $debitData = [
                'AccountNo'    => $debitAccount,
                'TrType'       => 'Debit',
                'DrAmount'     => $amount,
                'CrAmount'     => 0,
                'Particular'   => $particular,
                'Balance'      => $newDebitBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'BR',
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $creditData = [
                'AccountNo'    => $creditAccount,
                'TrType'       => 'Credit',
                'DrAmount'     => 0,
                'CrAmount'     => $amount,
                'Particular'   => $particular,
                'Balance'      => $newCreditBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'BR',
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $unauthData = [
                'DrAccount'    => (string) $debitAccount,
                'CrAccount'    => (string) $creditAccount,
                'Particular'   => $particular,
                'amount'       => $amount,
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
                'Serial'       => (string) ($validated['Serial'] ?? ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'ScreenType'   => 'BR',
            ];

            DB::table(self::TBL_TRAN_TMP)->insert($debitData);
            DB::table(self::TBL_TRAN_TMP)->insert($creditData);
            DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthData);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.bankreceive')
                ->with('success', 'Bank Receive transaction saved successfully.')
                ->with('refNumber', $refNumber);
        });
    }

    // =====================================================
    // BANK PAYMENT — form load
    // =====================================================
    public function bankPayment(Request $request)
    {
        $bankAccounts = AcctTblBalsheetAccount::where('gl_group', 202)
            ->orWhere('title', 'like', '%bank%')
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);

        if ($bankAccounts->isEmpty()) {
            $bankAccounts = AcctTblBalsheetAccount::where('gl_group', 'like', '2%')
                ->where('AccountNo', '!=', $this->cashAccountNo())
                ->orderBy('title')
                ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);
        }

        // The paying side is already a bank account, so the "Payment To"
        // dropdown must not offer bank accounts — bank-to-bank belongs on
        // the Transfer Posting screen.
        $drAccounts = AcctTblBalsheetAccount::orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group'])
            ->reject(fn ($account) => in_array((string) $account->AccountNo, $this->bankAccountNumbers(), true))
            ->values();

        $refNumber = session('refNumber');

        return view('transaction.bankpayment', [
            'bankAccounts' => $bankAccounts,
            'drAccounts'   => $drAccounts,
            'refNumber'    => $refNumber,
        ]);
    }

    // =====================================================
    // BANK PAYMENT — save
    // =====================================================
    public function bankPaymentAction(Request $request)
    {
        $validated = $request->validate([
            'debitsign'     => ['required', 'in:-1'],
            'creditsign'    => ['required', 'in:1'],
            'CreditAccount' => $this->accountRules(),
            'DebitAccount'  => array_merge($this->accountRules(), ['different:CreditAccount', 'not_in:' . implode(',', $this->bankAccountNumbers())]),
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PostingDate'   => $this->postingDateRules(),
            'Serial'        => ['nullable', 'string', 'max:100'],
            'BankName'      => ['nullable', 'string', 'max:150'],
            'ChequeDate'    => ['nullable', 'string', 'max:50'],
            'Particular'    => ['nullable', 'string', 'max:255'],
        ], $this->postingValidationMessages());

        return DB::transaction(function () use ($request, $validated) {
            $refNumber = $this->nextReferenceNumber();

            $debitAccount  = $validated['DebitAccount'];
            $creditAccount = $validated['CreditAccount'];
            $amount        = $validated['Amount'];
            $postingDate   = date('Y-m-d', strtotime($validated['PostingDate']));
            $inputerId     = (string) (Auth::id() ?? '');

            $particular = trim((string) ($validated['Particular'] ?? ''));
            $extras = [];
            if (!empty($validated['Serial'])) {
                $extras[] = 'Chq/Ref: ' . $validated['Serial'];
            }
            if (!empty($validated['BankName'])) {
                $extras[] = 'Bank: ' . $validated['BankName'];
            }
            if (!empty($validated['ChequeDate'])) {
                $extras[] = 'Date: ' . $validated['ChequeDate'];
            }
            if (!empty($extras)) {
                $particular = $particular !== '' ? $particular . ' (' . implode(', ', $extras) . ')' : implode(', ', $extras);
            }

            $newDebitBal  = $this->provisionalBalance($debitAccount, (float) $amount, 0.0);
            $newCreditBal = $this->provisionalBalance($creditAccount, 0.0, (float) $amount);

            $debitData = [
                'AccountNo'    => $debitAccount,
                'TrType'       => 'Debit',
                'DrAmount'     => $amount,
                'CrAmount'     => 0,
                'Particular'   => $particular,
                'Balance'      => $newDebitBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'BP',
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $creditData = [
                'AccountNo'    => $creditAccount,
                'TrType'       => 'Credit',
                'DrAmount'     => 0,
                'CrAmount'     => $amount,
                'Particular'   => $particular,
                'Balance'      => $newCreditBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'BP',
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $unauthData = [
                'DrAccount'    => (string) $debitAccount,
                'CrAccount'    => (string) $creditAccount,
                'Particular'   => $particular,
                'amount'       => $amount,
                'FactoryCode'  => (string) $request->input('FactoryCode', ''),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
                'Serial'       => (string) ($validated['Serial'] ?? ''),
                'MoNumber'     => (string) ($validated['Serial'] ?? ''),
                'ScreenType'   => 'BP',
            ];

            DB::table(self::TBL_TRAN_TMP)->insert($debitData);
            DB::table(self::TBL_TRAN_TMP)->insert($creditData);
            DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthData);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.bankpayment')
                ->with('success', 'Bank Payment transaction saved successfully.')
                ->with('refNumber', $refNumber);
        });
    }

    // =====================================================
    // TRANSFER POSTING - form load
    // =====================================================
    public function transferPosting(Request $request)
    {
        $accounts = AcctTblBalsheetAccount::where('gl_group', '!=', '201001101')
            ->orderBy('title')
            ->get(['AccountNo', 'title', 'gl_group', 'gl_sub_group']);

        $refNumber = session('refNumber');

        return view('transaction.transferposting', [
            'AccountNo'  => $accounts,
            'refNumber'  => $refNumber,
        ]);
    }

    // =====================================================
    // TRANSFER POSTING - save
    // =====================================================
    public function transferPostingAction(Request $request)
    {
        $validated = $request->validate([
            'debitsign'     => ['required', 'in:-1'],
            'creditsign'    => ['required', 'in:1'],
            'Amount'        => ['required', 'numeric', 'gt:0'],
            'PostingDate'   => $this->postingDateRules(),
            'DebitAccount'  => array_merge($this->accountRules(), ['different:CreditAccount']),
            'CreditAccount' => $this->accountRules(),
        ], $this->postingValidationMessages());

        return DB::transaction(function () use ($request, $validated) {
            $refNumber = $this->nextReferenceNumber();

            $debitAccount  = $validated['DebitAccount'];
            $creditAccount = $validated['CreditAccount'];
            $amount        = $validated['Amount'];
            $postingDate   = date('Y-m-d', strtotime($validated['PostingDate']));
            $inputerId     = (string) (Auth::id() ?? '');

            $newDebitBal  = $this->provisionalBalance($debitAccount, (float) $amount, 0.0);
            $newCreditBal = $this->provisionalBalance($creditAccount, 0.0, (float) $amount);

            $debitData = [
                'AccountNo'    => $debitAccount,
                'TrType'       => 'Debit',
                'DrAmount'     => $amount,
                'CrAmount'     => 0,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newDebitBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'TP',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $creditData = [
                'AccountNo'    => $creditAccount,
                'TrType'       => 'Credit',
                'DrAmount'     => 0,
                'CrAmount'     => $amount,
                'Particular'   => $request->input('Particular'),
                'Balance'      => $newCreditBal,
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'ScreenType'   => 'TP',
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'MoNumber'     => $request->input('Serial'),
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
            ];

            $unauthData = [
                'DrAccount'    => (string) $debitAccount,
                'CrAccount'    => (string) $creditAccount,
                'Particular'   => $request->input('Particular'),
                'amount'       => $amount,
                'FactoryCode'  => $request->input('FactoryCode'),
                'EmployeeID'   => (string) $request->input('EmployeeID', ''),
                'PostingDate'  => $postingDate,
                'RefNumber'    => (string) $refNumber,
                'InputerID'    => $inputerId,
                'AuthorizerID' => '',
                'Serial'       => $request->input('Serial'),
                'MoNumber'     => $request->input('Serial'),
                'ScreenType'   => 'TP',
            ];

            DB::table(self::TBL_TRAN_TMP)->insert($debitData);
            DB::table(self::TBL_TRAN_TMP)->insert($creditData);
            DB::table(self::TBL_UNAUTH_TRAN)->insert($unauthData);

            session(['refNumber' => $refNumber]);

            return redirect()
                ->route('transaction.transferposting')
                ->with('success', 'Saved Successfully')
                ->with('refNumber', $refNumber);
        });
    }

    // =====================================================
    // SHOW LEDGER — form load
    // =====================================================
    public function showAllAccountBalance(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $reportDate = $request->input('report_date', now()->format('d-m-Y'));
        $type = (string) $request->input('type', 'all');
        $showData = $request->boolean('view_data');

        return view('transaction.allaccountbalance', [
            'accounts' => $showData ? $this->allAccountBalanceRows($search, $type, $reportDate) : collect(),
            'search' => $search,
            'reportDate' => $reportDate,
            'type' => $type,
            'showData' => $showData,
        ]);
    }

    public function allAccountBalancePDF(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type = (string) $request->input('type', 'all');
        $reportDate = $request->input('report_date', now()->format('d-m-Y'));
        $onlyData = $request->boolean('only_data');
        $typeLabel = $type === 'all' ? 'All' : GlType::label((int) $type);

        $pdf = Pdf::loadView('transaction.allaccountbalancepdf', [
            'accounts' => $this->allAccountBalanceRows($search, $type, $reportDate),
            'search' => $search,
            'type' => $type,
            'typeLabel' => $typeLabel,
            'reportDate' => $reportDate,
            'onlyData' => $onlyData,
        ])->setPaper('a4', 'landscape');

        return base64_encode($pdf->output());
    }

    public function allAccountBalanceData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $reportDate = $request->input('report_date', now()->format('d-m-Y'));
        $type = (string) $request->input('type', 'all');

        return view('transaction.partials.allaccountbalance_data', [
            'accounts' => $this->allAccountBalanceRows($search, $type, $reportDate),
            'reportDate' => $reportDate,
        ]);
    }

    private function allAccountBalanceRows(string $search = '', string $type = 'all', string $reportDate = '')
    {
        $asOfDate = date('Y-m-d', strtotime($reportDate ?: now()->format('d-m-Y')));
        $openingBalanceFallback = DB::getSchemaBuilder()->hasTable(self::TBL_OPENNING_BALANCE)
            ? "(SELECT o.Balance FROM " . self::TBL_OPENNING_BALANCE . " o WHERE o.AccountNo = a.AccountNo AND o.PostingDate <= ? ORDER BY o.PostingDate DESC LIMIT 1)"
            : "b.Balance";
        $balanceExpression = "COALESCE((SELECT t.Balance FROM " . self::TBL_TRANSACTION . " t WHERE t.AccountNo = a.AccountNo AND t.PostingDate <= ? ORDER BY t.PostingDate DESC, t.ID DESC LIMIT 1), {$openingBalanceFallback}, b.Balance, 0)";

        $query = DB::table(self::TBL_ACCOUNT . ' as a')
            ->leftJoin(self::TBL_BALANCE . ' as b', 'b.AccountNo', '=', 'a.AccountNo')
            ->leftJoin('acct_tbl_balsheet_group as g', 'g.gl_group', '=', 'a.gl_group')
            ->leftJoin('acct_tbl_balsheet_sub_group as s', 's.gl_sub_group', '=', 'a.gl_sub_group')
            ->select('a.AccountNo', 'a.title', 'a.gl_type', 'a.gl_group', 'a.gl_sub_group', 'g.gl_group_name', 's.gl_sub_group_name')
            ->selectRaw("{$balanceExpression} as balance", $openingBalanceFallback === 'b.Balance' ? [$asOfDate] : [$asOfDate, $asOfDate]);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($filter) use ($like) {
                $filter->where('a.AccountNo', 'like', $like)
                    ->orWhere('a.title', 'like', $like)
                    ->orWhere('g.gl_group_name', 'like', $like)
                    ->orWhere('s.gl_sub_group_name', 'like', $like);
            });
        }

        if ($type !== 'all' && is_numeric($type)) {
            $query->where('a.gl_type', (int) $type);
        }

        return $query->orderBy('a.AccountNo')->get();
    }

    public function showLedger(Request $request)
    {
        $accountNo = AcctTblBalsheetAccount::orderBy('title')
            ->get(['AccountNo', 'title']);

        return view('transaction.showledger', [
            'AccountNo' => $accountNo,
            'selectedAccount' => $request->input('AccountNo'),
        ]);
    }

    // =====================================================
    // fetch_account_bal — AJAX balance lookup
    // =====================================================
    public function fetchAccountBal(Request $request)
    {
        $accountNo = $request->input('AccountNo');

        $balance = DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $accountNo)
            ->value('Balance');

        return response()->json([
            'balance' => $balance ?? 0
        ]);
    }

    // =====================================================
    // SHOW LEDGER STATEMENT — Main Query Logic
    // =====================================================
    public function showLedgerStatement(Request $request)
    {
        try {
            $accountNo = $request->input('AccountNo');
            $fromDateInput = $request->input('FromDate');
            $toDateInput = $request->input('ToDate');

            if (!$accountNo) {
                return redirect()->back()
                    ->with('error', 'Please select an account.');
            }

            $accountTitle = AcctTblBalsheetAccount::where('AccountNo', $accountNo)
                ->value('title') ?? 'N/A';

            $data = [
                'AccountNo'    => $accountNo,
                'AccountTitle' => $accountTitle,
                'FromDate'     => $fromDateInput ?? '',
                'ToDate'       => $toDateInput ?? '',
                'OpeningBal'   => 0,
                'rsResult'     => collect(),
                'totalDebit'   => 0,
                'totalCredit'  => 0,
            ];

            $fromDate = null;
            $toDate = null;

            if (!empty($fromDateInput)) {
                try {
                    $fromDate = date('Y-m-d', strtotime($fromDateInput));
                } catch (\Exception $e) {
                    \Log::error('FromDate parsing error', ['input' => $fromDateInput]);
                }
            }

            if (!empty($toDateInput)) {
                try {
                    $toDate = date('Y-m-d', strtotime($toDateInput));
                } catch (\Exception $e) {
                    \Log::error('ToDate parsing error', ['input' => $toDateInput]);
                }
            }

            if ($fromDate && $toDate) {
                $data['OpeningBal'] = $this->getOpeningBalance($accountNo, $fromDate);
                $rsResult = DB::table(self::TBL_TRANSACTION)
                    ->where('AccountNo', $accountNo)
                    ->whereBetween('PostingDate', [$fromDate, $toDate])
                    ->orderBy('PostingDate', 'asc')
                    ->orderBy('ID', 'asc')
                    ->get();
            } elseif ($fromDate) {
                $data['OpeningBal'] = $this->getOpeningBalance($accountNo, $fromDate);
                $rsResult = DB::table(self::TBL_TRANSACTION)
                    ->where('AccountNo', $accountNo)
                    ->where('PostingDate', '>=', $fromDate)
                    ->orderBy('PostingDate', 'asc')
                    ->orderBy('ID', 'asc')
                    ->get();
            } else {
                $data['OpeningBal'] = 0;
                $rsResult = DB::table(self::TBL_TRANSACTION)
                    ->where('AccountNo', $accountNo)
                    ->orderBy('PostingDate', 'asc')
                    ->orderBy('ID', 'asc')
                    ->get();
            }

            if ($rsResult && count($rsResult) > 0) {
                $data['totalDebit'] = $rsResult->sum('DrAmount');
                $data['totalCredit'] = $rsResult->sum('CrAmount');
            }

            $data['rsResult'] = $rsResult;

            return view('transaction.showledgerstatement', $data);

        } catch (\Exception $e) {
            \Log::error('Ledger Statement Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // =====================================================
    // SHOW LEDGER STATEMENT PDF
    // =====================================================
    public function showLedgerStatementPDF(Request $request)
    {
        $accountNo     = $request->input('AccountNo');
        $fromDateInput = $request->input('FromDate');
        $toDateInput   = $request->input('ToDate');

        $data['AccountNo']    = $accountNo;
        $data['FromDate']     = $request->input('FromDate');
        $data['ToDate']       = $request->input('ToDate');
        $data['AccountTitle'] = AcctTblBalsheetAccount::where('AccountNo', $accountNo)
            ->value('title') ?? 'N/A';

        $fromDate = null;
        $toDate = null;

        if (!empty($fromDateInput)) {
            $fromDate = date('Y-m-d', strtotime($fromDateInput));
        }
        if (!empty($toDateInput)) {
            $toDate = date('Y-m-d', strtotime($toDateInput));
        }

        if ($fromDate && $toDate) {
            $data['OpeningBal'] = $this->getOpeningBalance($accountNo, $fromDate);
            $data['rsResult'] = DB::table(self::TBL_TRANSACTION)
                ->where('AccountNo', $accountNo)
                ->whereBetween('PostingDate', [$fromDate, $toDate])
                ->orderBy('PostingDate', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        } elseif ($fromDate) {
            $data['OpeningBal'] = $this->getOpeningBalance($accountNo, $fromDate);
            $data['rsResult'] = DB::table(self::TBL_TRANSACTION)
                ->where('AccountNo', $accountNo)
                ->where('PostingDate', '>=', $fromDate)
                ->orderBy('PostingDate', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        } else {
            $data['OpeningBal'] = 0;
            $data['rsResult'] = DB::table(self::TBL_TRANSACTION)
                ->where('AccountNo', $accountNo)
                ->orderBy('PostingDate', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        $pdf = Pdf::loadView('transaction.showledgerstatementpdf', $data)
            ->setPaper('a4', 'portrait');

        return base64_encode($pdf->output());
    }

    // =====================================================
    // Helper: Get opening balance before a given date
    // =====================================================
    private function getOpeningBalance($accountNo, $fromDate)
    {
        try {
            return $this->balanceBefore(
                (string) $accountNo,
                (int) $this->getGlType($accountNo),
                $fromDate
            );
        } catch (\Exception $e) {
            \Log::error('Opening Balance Error', [
                'account' => $accountNo,
                'date' => $fromDate,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * The account's balance immediately before $date.
     */
    private function balanceBefore(string $accountNo, int $glType, string $date): float
    {
        $movement = (float) (DB::table(self::TBL_TRANSACTION)
            ->where('AccountNo', $accountNo)
            ->where('PostingDate', '<', $date)
            ->selectRaw(GlType::sumExpression($glType) . ' as bal')
            ->value('bal') ?? 0);

        return round($this->recordedOpeningBalance($accountNo, $date) + $movement, 2);
    }

    /**
     * Opening balance held only in acct_tbl_openning_balance.
     */
    private function recordedOpeningBalance(string $accountNo, string $date): float
    {
        if (! DB::getSchemaBuilder()->hasTable(self::TBL_OPENNING_BALANCE)) {
            return 0.0;
        }

        $postedAsVoucher = DB::table(self::TBL_TRANSACTION)
            ->where('AccountNo', $accountNo)
            ->where('ScreenType', config('accounting.opening_balance_screen_type', 'OB'))
            ->exists();

        if ($postedAsVoucher) {
            return 0.0;
        }

        return (float) (DB::table(self::TBL_OPENNING_BALANCE)
            ->where('AccountNo', $accountNo)
            ->where('PostingDate', '<', $date)
            ->orderByDesc('PostingDate')
            ->value('Balance') ?? 0);
    }

    // =====================================================
    // Helper: Get gl_type for an account
    // =====================================================
    private function getGlType($accountNo)
    {
        return AcctTblBalsheetAccount::where('AccountNo', $accountNo)
            ->value('gl_type');
    }

    /**
     * Helper: Provisional balance shown on the unauthorized voucher.
     *
     * DISPLAY ONLY. This value is written to acct_tbl_transaction_tmp.Balance so
     * the review screen can show the operator what the account would look like,
     * and authorizeTransaction() recomputes it from scratch before anything
     * reaches acct_tbl_transaction.
     */
    private function provisionalBalance($accountNo, float $drAmount, float $crAmount): float
    {
        $glType = (int) $this->getGlType($accountNo);

        $currentBalance = (float) (DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $accountNo)
            ->value('Balance') ?? 0);

        return round($currentBalance + GlType::signedAmount($glType, $drAmount, $crAmount), 2);
    }

    // =====================================================
    // UNAUTHORIZE LIST
    // =====================================================
    public function unauthorizeList(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $screenType = $request->input('screen_type');
        $search   = $request->input('search');

        $query = DB::table(self::TBL_UNAUTH_TRAN . ' as u')
            ->leftJoin(self::TBL_ACCOUNT . ' as dr', 'u.DrAccount', '=', 'dr.AccountNo')
            ->leftJoin(self::TBL_ACCOUNT . ' as cr', 'u.CrAccount', '=', 'cr.AccountNo')
            ->select(
                'u.*',
                'dr.title as dr_title',
                'cr.title as cr_title'
            )
            ->orderBy('u.PostingDate', 'desc')
            ->orderBy('u.RefNumber', 'desc');

        if (!empty($fromDate)) {
            $query->where('u.PostingDate', '>=', date('Y-m-d', strtotime($fromDate)));
        }
        if (!empty($toDate)) {
            $query->where('u.PostingDate', '<=', date('Y-m-d', strtotime($toDate)));
        }
        if (!empty($screenType)) {
            $query->where('u.ScreenType', $screenType);
        }
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('u.RefNumber', 'like', "%{$search}%")
                  ->orWhere('u.Particular', 'like', "%{$search}%")
                  ->orWhere('u.DrAccount', 'like', "%{$search}%")
                  ->orWhere('u.CrAccount', 'like', "%{$search}%")
                  ->orWhere('dr.title', 'like', "%{$search}%")
                  ->orWhere('cr.title', 'like', "%{$search}%")
                  ->orWhere('u.Serial', 'like', "%{$search}%");
            });
        }

        $totalAmount = (clone $query)->sum('u.amount');
        $transactions = $query->paginate(20)->withQueryString();

        return view('transaction.unauthorizelist', compact(
            'transactions',
            'totalAmount',
            'fromDate',
            'toDate',
            'screenType',
            'search'
        ));
    }

    // =====================================================
    // AUTHORIZE REVIEW
    // =====================================================
    public function authorizeReview(Request $request)
    {
        $refNumbers = $this->parseRefNumbers($request);

        if (empty($refNumbers)) {
            return redirect()
                ->route('transaction.unauthorizelist')
                ->with('error', 'No transaction selected for review.');
        }

        // Expand MCR siblings sharing the same Receipt (Serial/MoNumber)
        // so a multiple-receive entered together is reviewed together.
        $refNumbers = $this->expandMcrReceiptSiblings($refNumbers);

        $transactions = [];

        foreach ($refNumbers as $refNumber) {
            $summary = DB::table(self::TBL_UNAUTH_TRAN . ' as u')
                ->leftJoin(self::TBL_ACCOUNT . ' as dr', 'u.DrAccount', '=', 'dr.AccountNo')
                ->leftJoin(self::TBL_ACCOUNT . ' as cr', 'u.CrAccount', '=', 'cr.AccountNo')
                ->where('u.RefNumber', $refNumber)
                ->select(
                    'u.*',
                    'dr.title as dr_title',
                    'cr.title as cr_title'
                )
                ->first();

            if (!$summary) {
                continue;
            }

            $tmpRows = DB::table(self::TBL_TRAN_TMP . ' as t')
                ->leftJoin(self::TBL_ACCOUNT . ' as a', 't.AccountNo', '=', 'a.AccountNo')
                ->where('t.RefNumber', $refNumber)
                ->select('t.*', 'a.title as account_title')
                ->orderByRaw("CASE WHEN t.TrType = 'Debit' THEN 0 ELSE 1 END")
                ->get();

            $debitRow = $tmpRows->firstWhere('TrType', 'Debit');
            $creditRow = $tmpRows->firstWhere('TrType', 'Credit');

            $transactions[] = [
                'summary' => $summary,
                'debit'   => $debitRow,
                'credit'  => $creditRow,
            ];
        }

        if (empty($transactions)) {
            return redirect()
                ->route('transaction.unauthorizelist')
                ->with('error', 'Selected transaction(s) not found or already authorized.');
        }

        // Group MCR items by receipt (Serial) for itemwise breakup display.
        $receiptGroups = $this->groupMcrReceipts($transactions);

        return view('transaction.authorizereview', [
            'transactions' => $transactions,
            'refNumbers'   => array_column(array_column($transactions, 'summary'), 'RefNumber'),
            'receiptGroups' => $receiptGroups,
        ]);
    }

    /**
     * Expand a ref list with every MCR sibling sharing the same receipt
     * (Serial/MoNumber), so one receipt is always reviewed AND authorized
     * as a single payment — never partially.
     */
    private function expandMcrReceiptSiblings(array $refNumbers): array
    {
        $expanded = array_values(array_unique(array_map('trim', $refNumbers)));
        $expanded = array_values(array_filter($expanded, fn($r) => $r !== ''));
        $seen = array_flip($expanded);
        foreach ($expanded as $refNumber) {
            $head = DB::table(self::TBL_UNAUTH_TRAN)
                ->where('RefNumber', $refNumber)
                ->first(['ScreenType', 'Serial', 'MoNumber']);
            if (!$head || ($head->ScreenType ?? '') !== 'MCR') {
                continue;
            }
            $serial = trim((string) ($head->Serial ?? '')) !== '' ? trim((string) $head->Serial) : trim((string) ($head->MoNumber ?? ''));
            if ($serial === '') {
                continue;
            }
            $siblings = DB::table(self::TBL_UNAUTH_TRAN)
                ->where('ScreenType', 'MCR')
                ->where(function ($q) use ($serial) {
                    $q->where('Serial', $serial)->orWhere('MoNumber', $serial);
                })
                ->pluck('RefNumber');
            foreach ($siblings as $sib) {
                $sib = trim((string) $sib);
                if ($sib !== '' && !isset($seen[$sib])) {
                    $seen[$sib] = true;
                    $expanded[] = $sib;
                }
            }
        }

        return $expanded;
    }

    /**
     * Group MCR transactions sharing the same Serial into one receipt group
     * so the review screen can show an itemwise breakup like the input screen.
     * Non-MCR or MCR without serial stay as single-item groups.
     */
    private function groupMcrReceipts(array $transactions): array
    {
        $groups = [];
        $mcrIndexBySerial = [];

        foreach ($transactions as $item) {
            $summary = $item['summary'];
            $screenType = (string) ($summary->ScreenType ?? '');
            $serial = trim((string) ($summary->Serial ?? '')) !== ''
                ? trim((string) $summary->Serial)
                : trim((string) ($summary->MoNumber ?? ''));

            if ($screenType === 'MCR' && $serial !== '') {
                if (!isset($mcrIndexBySerial[$serial])) {
                    $mcrIndexBySerial[$serial] = count($groups);
                    $groups[] = [
                        'isMultiple' => true,
                        'screenType' => 'MCR',
                        'serial' => $serial,
                        'date' => $summary->PostingDate ?? null,
                        'inputer' => $summary->InputerID ?? null,
                        'items' => [],
                        'total' => 0.0,
                    ];
                }
                $gi = $mcrIndexBySerial[$serial];
                $groups[$gi]['items'][] = $item;
                $groups[$gi]['total'] += (float) ($summary->amount ?? 0);
                // Keep earliest date / first inputer if mixed (should not happen).
                if (empty($groups[$gi]['date']) && !empty($summary->PostingDate)) {
                    $groups[$gi]['date'] = $summary->PostingDate;
                }
                continue;
            }

            $groups[] = [
                'isMultiple' => false,
                'screenType' => $screenType,
                'serial' => $serial,
                'date' => $summary->PostingDate ?? null,
                'inputer' => $summary->InputerID ?? null,
                'items' => [$item],
                'total' => (float) ($summary->amount ?? 0),
            ];
        }

        // Mark MCR groups with only 1 item as single (still show breakup table).
        foreach ($groups as &$g) {
            if (($g['screenType'] ?? '') === 'MCR') {
                $g['isMultiple'] = count($g['items']) > 1;
                // Sort items by RefNumber for stable display like input order.
                usort($g['items'], fn($a, $b) => strcmp((string) $a['summary']->RefNumber, (string) $b['summary']->RefNumber));
            }
        }

        return $groups;
    }

    // =====================================================
    // AUTHORIZE TRANSACTION
    // =====================================================
    public function authorizeTransaction(Request $request)
    {
        $refNumbers = $this->parseRefNumbers($request);
        // Same expansion as the review screen: authorizing any line of a
        // multiple-receive receipt authorizes the whole receipt as one payment.
        $refNumbers = $this->expandMcrReceiptSiblings($refNumbers);

        if (empty($refNumbers)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No transactions selected for authorization.'], 422);
            }
            return redirect()
                ->route('transaction.unauthorizelist')
                ->with('error', 'No transactions selected for authorization.');
        }

        $authorizerId = (string) (Auth::id() ?? (Auth::user()->name ?? 'admin'));

        try {
            DB::transaction(function () use ($refNumbers, $authorizerId) {
                $this->authorizeRefsInternal($refNumbers, $authorizerId);
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => count($refNumbers) . ' transaction(s) authorized successfully.',
                ]);
            }

            return redirect()
                ->route('transaction.unauthorizelist')
                ->with('success', count($refNumbers) . ' transaction(s) authorized successfully.');
        } catch (\Exception $e) {
            \Log::error('Authorization Error', ['error' => $e->getMessage()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authorize: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->route('transaction.unauthorizelist')
                ->with('error', 'Failed to authorize: ' . $e->getMessage());
        }
    }

    /**
     * Move tmp/unauth rows to the authorized ledger with balance recalculation.
     *
     * Called inside an outer DB transaction — authorizeTransaction() wraps it,
     * and the manual Cash Receive / Payment / Transfer screens call it directly
     * when config('accounting.auto_authorize_manual') is true so manual
     * income/expense goes straight to the ledger with no authorize step.
     */
    private function authorizeRefsInternal(array $refNumbers, string $authorizerId): void
    {
        $allTmpRows = DB::table(self::TBL_TRAN_TMP)
            ->whereIn('RefNumber', $refNumbers)
            ->orderBy('PostingDate', 'asc')
            ->orderBy('ID', 'asc')
            ->get();

        if ($allTmpRows->isEmpty()) {
            return;
        }

        $accountNos = $allTmpRows->pluck('AccountNo')->unique()->values()->all();
        $glTypes = $this->getGlTypesForAccounts($accountNos);

        $unknown = array_diff(
            array_map('strval', $accountNos),
            array_map('strval', array_keys($glTypes))
        );

        if (! empty($unknown)) {
            throw new \RuntimeException(
                'Cannot authorize: account(s) ' . implode(', ', $unknown)
                . ' have no entry in the chart of accounts, so their gl_type is unknown.'
            );
        }

        $sortedAccounts = $accountNos;
        sort($sortedAccounts, SORT_STRING);
        foreach ($sortedAccounts as $acctNo) {
            DB::table(self::TBL_BALANCE)
                ->where('AccountNo', $acctNo)
                ->lockForUpdate()
                ->first();
        }

        $rowsByAccount = $allTmpRows->groupBy('AccountNo');

        foreach ($rowsByAccount as $acctNo => $rows) {
            $glType = (int) ($glTypes[$acctNo] ?? 0);
            $earliestDate = (string) $rows->min('PostingDate');

            foreach ($rows as $tran) {
                DB::table(self::TBL_TRANSACTION)->insert([
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

            $finalBalance = $this->recalculateAccountBalances(
                (string) $acctNo,
                $glType,
                $earliestDate
            );

            $this->writeAccountBalance((string) $acctNo, $finalBalance);
        }

        DB::table(self::TBL_TRAN_TMP)
            ->whereIn('RefNumber', $refNumbers)
            ->delete();

        DB::table(self::TBL_UNAUTH_TRAN)
            ->whereIn('RefNumber', $refNumbers)
            ->delete();
    }

    /**
     * Rewrite the running Balance of every row of one account from $fromDate on.
     */
    private function recalculateAccountBalances(string $accountNo, int $glType, string $fromDate): float
    {
        $running = $this->balanceBefore($accountNo, $glType, $fromDate);

        $rows = DB::table(self::TBL_TRANSACTION)
            ->where('AccountNo', $accountNo)
            ->where('PostingDate', '>=', $fromDate)
            ->orderBy('PostingDate', 'asc')
            ->orderBy('ID', 'asc')
            ->select('ID', 'DrAmount', 'CrAmount', 'Balance')
            ->get();

        foreach ($rows as $row) {
            $running = round(
                $running + GlType::signedAmount($glType, (float) $row->DrAmount, (float) $row->CrAmount),
                2
            );

            if (abs($running - (float) $row->Balance) >= 0.005) {
                DB::table(self::TBL_TRANSACTION)
                    ->where('ID', $row->ID)
                    ->update(['Balance' => $running]);
            }
        }

        return $running;
    }

    /**
     * Write an account's closing balance to acct_tbl_balance.
     */
    private function writeAccountBalance(string $accountNo, float $balance): void
    {
        $exists = DB::table(self::TBL_BALANCE)
            ->where('AccountNo', $accountNo)
            ->exists();

        if ($exists) {
            DB::table(self::TBL_BALANCE)
                ->where('AccountNo', $accountNo)
                ->update(['Balance' => $balance]);

            return;
        }

        $row = ['AccountNo' => $accountNo, 'Balance' => $balance];

        foreach (self::BALANCE_ROW_DEFAULTS as $column => $default) {
            if (in_array($column, $this->balanceTableColumns(), true)) {
                $row[$column] = $default;
            }
        }

        DB::table(self::TBL_BALANCE)->insert($row);
    }

    /**
     * Column listing for acct_tbl_balance, resolved once per request.
     */
    private function balanceTableColumns(): array
    {
        if ($this->balanceColumns === null) {
            $this->balanceColumns = DB::getSchemaBuilder()->getColumnListing(self::TBL_BALANCE);
        }

        return $this->balanceColumns;
    }

    // =====================================================
    // Helper: Batch-fetch gl_type for multiple accounts.
    // =====================================================
    private function getGlTypesForAccounts(array $accountNos): array
    {
        if (empty($accountNos)) {
            return [];
        }

        return DB::table(self::TBL_ACCOUNT)
            ->whereIn('AccountNo', $accountNos)
            ->pluck('gl_type', 'AccountNo')
            ->map(fn($v) => (int) $v)
            ->toArray();
    }

    private function parseRefNumbers(Request $request): array
    {
        $refNumbers = $request->input('ref_numbers');

        if (is_string($refNumbers)) {
            $refNumbers = array_filter(array_map('trim', explode(',', $refNumbers)));
        }

        if (empty($refNumbers) && $request->filled('ref_number')) {
            $refNumbers = [trim((string) $request->input('ref_number'))];
        }

        if (!is_array($refNumbers)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $refNumbers)));
    }

    /**
     * Next numeric reference number for regular vouchers.
     *
     * RefNumber is now VARCHAR(20) to hold invoice references like 'INV-000123'.
     * This method only considers purely numeric references so that alphanumeric
     * invoice refs do not interfere with the auto-increment sequence.
     */
    private function nextReferenceNumber(): int
    {
        $permanentMax = (int) (DB::table(self::TBL_TRANSACTION)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->orderByRaw('CAST(RefNumber AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('RefNumber') ?? 0);

        $temporaryMax = (int) (DB::table(self::TBL_TRAN_TMP)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->selectRaw('MAX(CAST(RefNumber AS UNSIGNED)) as max_ref')
            ->value('max_ref') ?? 0);

        $unauthorizedMax = (int) (DB::table(self::TBL_UNAUTH_TRAN)
            ->whereRaw('RefNumber REGEXP ?', ['^[0-9]+$'])
            ->selectRaw('MAX(CAST(RefNumber AS UNSIGNED)) as max_ref')
            ->value('max_ref') ?? 0);

        return max($permanentMax, $temporaryMax, $unauthorizedMax) + 1;
    }

    // =====================================================
    // DELETE UNAUTH TRANSACTION
    // =====================================================
    public function deleteUnauthTransaction(Request $request)
    {
        $refNumber = $request->input('ref_number');

        if (empty($refNumber)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid Reference Number.'], 422);
            }
            return redirect()->back()->with('error', 'Invalid Reference Number.');
        }

        try {
            DB::transaction(function () use ($refNumber) {
                DB::table(self::TBL_TRAN_TMP)->where('RefNumber', $refNumber)->delete();
                DB::table(self::TBL_UNAUTH_TRAN)->where('RefNumber', $refNumber)->delete();
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaction #{$refNumber} deleted successfully.",
                ]);
            }

            return redirect()->back()->with('success', "Transaction #{$refNumber} deleted successfully.");
        } catch (\Exception $e) {
            \Log::error('Delete Unauth Error', ['error' => $e->getMessage()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
?>
