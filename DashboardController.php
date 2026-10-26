<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard dengan ringkasan metrik database
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Metrik Agregat Master Data
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();

        // 2. Metrik Transaksi Penjualan
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        $totalSalesCount = Sale::where('status', 'completed')->count();
        $todaySalesRevenue = Sale::where('status', 'completed')
            ->where('sold_at', '>=', $today)
            ->sum('total');
        $monthSalesRevenue = Sale::where('status', 'completed')
            ->where('sold_at', '>=', $startOfMonth)
            ->sum('total');

        // 3. Metrik Pembelian (Stock-in)
        $totalPurchasesCount = Purchase::where('status', 'completed')->count();
        $monthPurchasesTotal = Purchase::where('status', 'completed')
            ->where('purchased_at', '>=', $startOfMonth)
            ->sum('total');

        // 4. Alert Stok Rendah (stock <= minimum_stock)
        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();
        $lowStockCount = Product::lowStock()->count();

        // 5. Transaksi Terbaru
        $recentSales = Sale::with(['user', 'items.product'])
            ->orderBy('sold_at', 'desc')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'user', 'items.product'])
            ->orderBy('purchased_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'totalCategories',
            'totalUsers',
            'totalSalesCount',
            'todaySalesRevenue',
            'monthSalesRevenue',
            'totalPurchasesCount',
            'monthPurchasesTotal',
            'lowStockProducts',
            'lowStockCount',
            'recentSales',
            'recentPurchases'
        ));
    }
}
