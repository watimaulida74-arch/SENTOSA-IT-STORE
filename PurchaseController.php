<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    /**
     * Tampilkan daftar transaksi pembelian dengan filter & pagination
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');
        $supplierId = $request->query('supplier_id');

        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($startDate) {
            $query->whereDate('purchased_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchased_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->orderBy('purchased_at', 'desc')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'startDate', 'endDate', 'status', 'supplierId'));
    }

    /**
     * Form pembuatan transaksi pembelian (Stock-in)
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Simpan transaksi pembelian secara atomik menggunakan DB::transaction
     */
    public function store(PurchaseRequest $request)
    {
        $purchase = DB::transaction(function () use ($request) {
            // 1. Buat Header Transaksi Pembelian
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'purchase_number' => $this->generatePurchaseNumber(),
                'total' => 0, // Akan dihitung dan diperbarui oleh server
                'status' => 'completed',
                'notes' => $request->notes,
                'purchased_at' => $request->purchased_at,
            ]);

            $total = 0;

            // 2. Loop setiap item barang yang dibeli
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1 || $unitCost < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Kuantitas atau harga beli tidak valid pada salah satu produk.',
                    ]);
                }

                $subtotal = $unitCost * $qty;

                // Simpan detail item pembelian
                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Business Rule: Tambah stok produk secara otomatis saat pembelian berhasil
                $product->increment('stock', $qty);

                // Update harga beli modal terakhir jika perlu
                $product->update(['purchase_price' => $unitCost]);

                $total += $subtotal;
            }

            // 3. Update total faktur pembelian hasil kalkulasi server
            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase->id)
            ->with('success', "Transaksi pembelian {$purchase->purchase_number} berhasil dicatat dan stok telah bertambah.");
    }

    /**
     * Tampilkan detail faktur pembelian
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Helper membuat nomor faktur unik: PO-YYYYMMDD-XXXX
     */
    private function generatePurchaseNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $latest = Purchase::where('purchase_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->purchase_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
