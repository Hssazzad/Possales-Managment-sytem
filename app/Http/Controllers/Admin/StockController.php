<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::where('stock_quantity', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.stock.index', compact('products'));
    }

    public function lowStock()
    {
        $products = Product::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return view('admin.stock.low-stock-clean', compact('products'));
    }
}
