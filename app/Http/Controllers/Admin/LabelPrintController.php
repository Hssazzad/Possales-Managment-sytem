<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class LabelPrintController extends Controller
{
    /**
     * Show the label printing interface
     */
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(10);
        
        return view('admin.labels.print', compact('products'));
    }

    /**
     * Search products for label printing
     */
    public function search(Request $request)
    {
        $query = Product::with(['category', 'brand'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(10);

        return view('admin.labels.print', compact('products'));
    }

    /**
     * Generate and print labels
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.packing_date' => 'nullable|date',
            'business_name' => 'nullable|string|max:50',
            'business_name_size' => 'nullable|integer|min:8|max:20',
            'product_name_size' => 'nullable|integer|min:8|max:20',
            'price_size' => 'nullable|integer|min:8|max:20',
            'code_size' => 'nullable|integer|min:8|max:20',
            'date_size' => 'nullable|integer|min:8|max:20',
            'show_price' => 'boolean',
            'inc_tax' => 'boolean',
            'barcode_type' => 'required|string|in:C128,C39,EAN13',
            'paper_size' => 'required|string',
        ]);

        $productIds = collect($validated['products'])->pluck('id')->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $labels = [];
        foreach ($validated['products'] as $item) {
            $product = $products[$item['id']] ?? null;
            if (!$product) continue;

            for ($i = 0; $i < $item['qty']; $i++) {
                $labels[] = [
                    'product' => $product,
                    'packing_date' => $item['packing_date'] ?? null,
                ];
            }
        }

        return view('admin.labels.preview', [
            'labels' => $labels,
            'settings' => [
                'business_name' => $validated['business_name'] ?? 'NBS TRADERS',
                'business_name_size' => $validated['business_name_size'] ?? 15,
                'product_name_size' => $validated['product_name_size'] ?? 15,
                'price_size' => $validated['price_size'] ?? 14,
                'code_size' => $validated['code_size'] ?? 14,
                'date_size' => $validated['date_size'] ?? 12,
                'show_price' => $validated['show_price'] ?? true,
                'inc_tax' => $validated['inc_tax'] ?? false,
                'barcode_type' => $validated['barcode_type'] ?? 'C128',
                'paper_size' => $validated['paper_size'] ?? 'Labels Roll-Label Size 2"x1", 50mmx25mm, Gap:3.1mm',
            ],
        ]);
    }
}
