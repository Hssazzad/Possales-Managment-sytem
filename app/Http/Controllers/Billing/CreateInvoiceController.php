<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\TmpBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateInvoiceController extends Controller
{
    /**
     * Show the invoice creation form
     */
    public function index()
    {
        return view('invoice.create');
    }

    /**
     * Get main categories (admission types)
     */
    public function getMain()
    {
        try {
            $categories = MainCategory::select('code', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch main categories',
            ], 500);
        }
    }

    /**
     * Get sub categories (investigations) by main category
     */
    public function getSub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_code' => 'required|string|exists:main_categories,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subCategories = SubCategory::where('main_code', $request->main_code)
                ->select('code', 'name', 'amount')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub categories',
            ], 500);
        }
    }

    /**
     * Add items to temporary bill
     */
    public function addTmp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_code' => 'required|string|exists:main_categories,code',
            'patient_code' => 'required|string|exists:customers,code',
            'sub_codes' => 'required|array|min:1',
            'sub_codes.*' => 'string|exists:sub_categories,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $patient = Customer::where('code', $request->patient_code)->first();
            if (! $patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found',
                ], 404);
            }

            $addedCount = 0;
            $duplicateCount = 0;

            foreach ($request->sub_codes as $subCode) {
                // Validate sub_code belongs to main_code
                $subCategory = SubCategory::where('code', $subCode)
                    ->where('main_code', $request->main_code)
                    ->first();

                if (! $subCategory) {
                    continue;
                }

                // Check for duplicate entries (for this patient, temp status)
                $exists = TmpBill::where('patient_code', $request->patient_code)
                    ->where('sub_code', $subCode)
                    ->where('status', 0)
                    ->exists();

                if ($exists) {
                    $duplicateCount++;

                    continue;
                }

                // Add to temporary table
                TmpBill::create([
                    'main_code' => $request->main_code,
                    'sub_code' => $subCode,
                    'name' => $subCategory->name,
                    'amount' => $subCategory->amount,
                    'patient_code' => $request->patient_code,
                    'bill_no' => null,
                    'status' => 0,
                ]);

                $addedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Added $addedCount item(s). ".($duplicateCount > 0 ? "$duplicateCount duplicate(s) skipped." : ''),
                'added' => $addedCount,
                'duplicates' => $duplicateCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to add items: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get temporary bill items for a patient
     */
    public function getTmp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_code' => 'required|string|exists:customers,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $items = TmpBill::where('patient_code', $request->patient_code)
                ->where('status', 0)
                ->select('id', 'name', 'amount')
                ->get();

            $total = $items->sum('amount');

            return response()->json([
                'success' => true,
                'data' => $items,
                'total' => $total,
                'count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch temporary items',
            ], 500);
        }
    }

    /**
     * Remove item from temporary bill
     */
    public function removeTmp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:tbl_bill_tmp,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $tmpBill = TmpBill::findOrFail($request->id);

            // Ensure it's a temp bill (not finalized)
            if ($tmpBill->status !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove finalized bill items',
                ], 400);
            }

            $tmpBill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
            ], 500);
        }
    }

    /**
     * Create final invoice
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_code' => 'required|string|exists:customers,code',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get patient
            $patient = Customer::where('code', $request->patient_code)->firstOrFail();

            // Get temporary items
            $tmpItems = TmpBill::where('patient_code', $request->patient_code)
                ->where('status', 0)
                ->get();

            if ($tmpItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items to invoice',
                ], 400);
            }

            // Calculate totals
            $total = $tmpItems->sum('amount');
            $paidAmount = (float) $request->paid_amount;
            $due = $total - $paidAmount;

            // Generate bill number (can be customized: BIL-YYYYMMDD-XXXXXX)
            $billNo = 'BIL-'.date('YmdHis').'-'.rand(1000, 9999);

            // Create invoice record
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'patient_code' => $patient->code,
                'total' => $total,
                'paid' => $paidAmount,
                'due' => $due,
                'bill_no' => $billNo,
                'created_at' => now(),
            ]);

            // Update temporary items to final status
            TmpBill::where('patient_code', $request->patient_code)
                ->where('status', 0)
                ->update([
                    'bill_no' => $billNo,
                    'status' => 1,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully',
                'invoice_id' => $invoice->id,
                'bill_no' => $billNo,
                'total' => $total,
                'paid' => $paidAmount,
                'due' => $due,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print invoice
     */
    public function printInvoice($billNo)
    {
        try {
            $invoice = Invoice::where('bill_no', $billNo)
                ->with('patient', 'billItems')
                ->firstOrFail();

            return view('invoice.print', compact('invoice'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }
    }

    /**
     * Search patients via AJAX
     */
    public function searchPatients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = $request->q;

            $patients = Customer::where('code', 'like', "%$query%")
                ->orWhere('name', 'like', "%$query%")
                ->select('id', 'code', 'name', 'email', 'phone')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $patients,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search patients',
            ], 500);
        }
    }

    /**
     * Get patient details
     */
    public function getPatientDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_code' => 'required|string|exists:customers,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $patient = Customer::where('code', $request->patient_code)
                ->select('id', 'code', 'name', 'email', 'phone')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $patient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }
    }
}
