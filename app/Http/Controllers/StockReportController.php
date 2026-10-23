<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class StockReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->orderBy('name');

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity <= min_stock_level AND stock_quantity > 0');
                    break;
            }
        }

        $products = $query->get();

        // Calculate stock statistics
        $totalProducts = $products->count();
        $totalStockValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->cost;
        });
        $totalStockQuantity = $products->sum('stock_quantity');
        $outOfStockCount = $products->where('stock_quantity', 0)->count();
        $lowStockCount = $products->filter(function ($product) {
            return $product->stock_quantity > 0 && $product->stock_quantity <= $product->min_stock_level;
        })->count();

        // Group by category for summary
        $categoryBreakdown = $products->groupBy('category')
            ->map(function ($products, $categoryId) {
                $category = $products->first()->category;

                return [
                    'category_name' => $category ? $category->name : 'Uncategorized',
                    'total_products' => $products->count(),
                    'total_quantity' => $products->sum('stock_quantity'),
                    'total_value' => $products->sum(function ($product) {
                        return $product->stock_quantity * $product->cost;
                    }),
                ];
            })->sortByDesc('total_value');

        // Get all categories for filter
        $categories = Category::active()->get();

        return view('admin.stock-reports.index', compact(
            'products',
            'totalProducts',
            'totalStockValue',
            'totalStockQuantity',
            'outOfStockCount',
            'lowStockCount',
            'categoryBreakdown',
            'categories'
        ));
    }
}
