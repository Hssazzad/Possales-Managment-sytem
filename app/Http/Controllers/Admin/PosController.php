<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\HeldSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $products = Product::active()->inStock()->get();
        $categories = Product::active()->distinct()->pluck('category')->filter();

        return view('admin.pos.index', compact('products', 'categories'));
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $query = Product::active()->inStock();

        if ($search) {
            $query->search($search);
        }

        if ($category && $category !== 'all') {
            $query->byCategory($category);
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'category' => $product->category,
                'image_url' => $product->image_url,
                'is_low_stock' => $product->isLowStock(),
            ];
        });

        return response()->json($products);
    }

    public function processSale(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,mobile,bank',
            'total_amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::find($item['id']);
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $itemTotal = $item['quantity'] * $item['price'];
                $itemTax = $itemTotal * ($product->tax_rate / 100);

                $subtotal += $itemTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            // Create sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'paid_amount' => $totalAmount,
                'change_amount' => 0,
                'customer_id' => $data['customer_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'notes' => $data['notes'],
                'user_id' => auth()->id(),
            ]);

            // Create sale items and update stock
            foreach ($data['items'] as $item) {
                $product = Product::find($item['id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                    'tax_rate' => $product->tax_rate,
                    'tax_amount' => ($item['quantity'] * $item['price']) * ($product->tax_rate / 100),
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function holdSale(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        try {
            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::find($item['id']);
                $itemTotal = $item['quantity'] * $item['price'];
                $itemTax = $itemTotal * ($product->tax_rate / 100);

                $subtotal += $itemTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            // Create held sale
            $heldSale = HeldSale::create([
                'hold_token' => Str::random(8),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'cart_data' => $data['items'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sale held successfully',
                'hold_id' => $heldSale->hold_token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getHeldSales()
    {
        $heldSales = HeldSale::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($heldSale) {
                return [
                    'id' => $heldSale->id,
                    'hold_token' => $heldSale->hold_token,
                    'customer_name' => $heldSale->customer_name,
                    'total_amount' => $heldSale->formatted_total,
                    'created_at' => $heldSale->created_at->format('M d, Y h:i A'),
                    'items_count' => count($heldSale->cart_data),
                ];
            });

        return response()->json($heldSales);
    }

    public function resumeSale($holdId)
    {
        $heldSale = HeldSale::where('hold_token', $holdId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$heldSale) {
            return response()->json([
                'success' => false,
                'message' => 'Held sale not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'items' => $heldSale->cart_data,
            'customer_name' => $heldSale->customer_name,
            'customer_phone' => $heldSale->customer_phone,
        ]);
    }

    public function getTodayStats()
    {
        $today = now()->startOfDay();

        $sales = Sale::today();

        $stats = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $sales->where('payment_method', 'card')->sum('total_amount'),
            'mobile_sales' => $sales->where('payment_method', 'mobile')->sum('total_amount'),
            'bank_sales' => $sales->where('payment_method', 'bank')->sum('total_amount'),
            'items_sold' => SaleItem::whereIn('sale_id', $sales->pluck('id'))->sum('quantity'),
        ];

        return response()->json($stats);
    }

    public function openCashDrawer()
    {
        // Logic to open cash drawer (hardware integration)
        // This would typically involve sending a command to a cash drawer device

        return response()->json([
            'success' => true,
            'message' => 'Cash drawer opened',
        ]);
    }

    public function generateReceipt($saleId)
    {
        $sale = Sale::with(['saleItems.product', 'user'])->find($saleId);

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        // Generate receipt data
        $receiptData = [
            'sale' => $sale,
            'items' => $sale->saleItems,
            'company' => [
                'name' => config('app.name', 'PosSales'),
                'address' => '123 Main St, City, State',
                'phone' => '+1 234 567 8900',
                'email' => 'info@possales.com',
            ],
        ];

        return response()->json([
            'success' => true,
            'receipt_data' => $receiptData,
        ]);
    }

    public function searchSales()
    {
        $sales = Sale::select('id', 'invoice_number', 'customer_name', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer_name,
                    'total_amount' => $sale->formatted_total,
                    'date' => $sale->created_at->format('M d, Y'),
                    'display_text' => $sale->invoice_number . ' - ' . ($sale->customer_name ?: 'Walk-in') . ' (' . $sale->formatted_total . ')'
                ];
            });

        return response()->json($sales);
    }
}
