<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ExpiredProductReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->whereNotNull('expire_date');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('expire_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expire_date', '<=', $request->end_date);
        }

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by expiration status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'expired':
                    $query->whereDate('expire_date', '<', now());
                    break;
                case 'expiring_soon':
                    $query->whereDate('expire_date', '>', now())
                        ->whereDate('expire_date', '<=', now()->addDays(30));
                    break;
                case 'not_expired':
                    $query->whereDate('expire_date', '>', now()->addDays(30));
                    break;
            }
        }

        $products = $query->orderBy('expire_date', 'asc')->get();

        // Categorize products by expiration status
        $expiredProducts = $products->filter(function ($product) {
            return $product->expire_date && $product->expire_date->isPast();
        });

        $expiringSoonProducts = $products->filter(function ($product) {
            return $product->expire_date &&
                   $product->expire_date->isFuture() &&
                   $product->expire_date->diffInDays(now()) <= 30;
        });

        $notExpiredProducts = $products->filter(function ($product) {
            return $product->expire_date &&
                   $product->expire_date->diffInDays(now()) > 30;
        });

        // Calculate totals
        $totalExpiredValue = $expiredProducts->sum(function ($product) {
            return $product->stock_quantity * $product->cost;
        });

        $totalExpiringSoonValue = $expiringSoonProducts->sum(function ($product) {
            return $product->stock_quantity * $product->cost;
        });

        $totalNotExpiredValue = $notExpiredProducts->sum(function ($product) {
            return $product->stock_quantity * $product->cost;
        });

        // Group by category for summary
        $categoryBreakdown = $expiredProducts->groupBy('category')
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

        return view('admin.expired-product-reports.index', compact(
            'products',
            'expiredProducts',
            'expiringSoonProducts',
            'notExpiredProducts',
            'totalExpiredValue',
            'totalExpiringSoonValue',
            'totalNotExpiredValue',
            'categoryBreakdown',
            'categories'
        ));
    }
}
