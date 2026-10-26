<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Tampilkan ringkasan laporan penjualan dan pergerakan stok
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        // 1. Total Penjualan Sukses dalam periode
        $sales = Sale::where('status', 'completed')
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate)
            ->get();

        $totalSalesRevenue = $sales->sum('total');
        $totalSalesCount = $sales->count();

        // 2. Total Pembelian dalam periode
        $purchases = Purchase::where('status', 'completed')
            ->whereDate('purchased_at', '>=', $startDate)
            ->whereDate('purchased_at', '<=', $endDate)
            ->get();

        $totalPurchaseExpense = $purchases->sum('total');
        $totalPurchasesCount = $purchases->count();

        // 3. Estimasi Laba Kotor Sederhana (Pendapatan Penjualan - Pengeluaran Pembelian)
        $grossProfit = $totalSalesRevenue - $totalPurchaseExpense;

        // 4. Produk Terlaris (Top 5 Best Sellers)
        $topProducts = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                  ->whereDate('sold_at', '>=', $startDate)
                  ->whereDate('sold_at', '<=', $endDate);
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalSalesRevenue',
            'totalSalesCount',
            'totalPurchaseExpense',
            'totalPurchasesCount',
            'grossProfit',
            'topProducts'
        ));
    }
}
