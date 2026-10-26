<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Http\Requests\SaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Tampilkan riwayat transaksi penjualan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Sale::with(['user', 'items.product']);

        // Jika user adalah role 'user' biasa, batasi hanya melihat transaksinya sendiri
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        }

        if ($startDate) {
            $query->whereDate('sold_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('sold_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('sold_at', 'desc')->paginate(10)->withQueryString();

        return view('sales.index', compact('sales', 'startDate', 'endDate', 'status', 'search'));
    }

    /**
     * Form pembuatan transaksi penjualan baru
     */
    public function create()
    {
        // Hanya ambil produk yang memiliki stok > 0
        $products = Product::where('stock', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('sales.create', compact('products'));
    }

    /**
     * Simpan transaksi penjualan secara atomik dengan DB::transaction & lockForUpdate
     */
    public function store(SaleRequest $request)
    {
        $sale = DB::transaction(function () use ($request) {
            // 1. Simpan Header Transaksi Penjualan
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'sale_number' => $this->generateSaleNumber(),
                'customer_name' => $request->customer_name ?: 'Pelanggan Umum',
                'total' => 0, // Dihitung di server
                'status' => 'completed',
                'notes' => $request->notes,
                'sold_at' => $request->sold_at,
            ]);

            $total = 0;

            // 2. Loop setiap item yang dipesan
            foreach ($request->items as $item) {
                // KUNCI PENTING (Slide 16): Gunakan lockForUpdate untuk mencegah overselling / race condition
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                // Validasi integritas kuantitas
                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas produk '{$product->name}' harus minimal 1.",
                    ]);
                }

                // Business Rule: Cek apakah stok mencukupi
                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok produk '{$product->name}' tidak mencukupi! Tersedia: {$product->stock}, Diminta: {$qty}.",
                    ]);
                }

                // KUNCI PENTING: Ambil harga jual dari DATABASE, BUKAN dari input browser!
                $unitPrice = $product->selling_price;
                $subtotal = $unitPrice * $qty;

                // Simpan baris detail penjualan
                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk
                $product->decrement('stock', $qty);

                $total += $subtotal;
            }

            // 3. Update total akhir transaksi yang valid dari server
            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale->id)
            ->with('success', "Transaksi penjualan {$sale->sale_number} berhasil diproses. Stok barang telah otomatis berkurang.");
    }

    /**
     * Tampilkan detail struk/faktur penjualan
     */
    public function show(Sale $sale)
    {
        $user = Auth::user();

        // Otorisasi: role 'user' hanya boleh melihat fakturnya sendiri
        if ($user->isUser() && $sale->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat transaksi milik pengguna lain.');
        }

        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Pembatalan Transaksi Penjualan (Khusus Admin)
     * Mengembalikan stok secara konsisten (Reverse Stock Movement)
     */
    public function cancel(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Transaksi ini sudah dalam status dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($sale) {
            // Kembalikan stok untuk setiap item barang
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            // Tandai status penjualan menjadi dibatalkan
            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale->id)
            ->with('success', "Transaksi {$sale->sale_number} berhasil dibatalkan dan seluruh stok produk telah dikembalikan.");
    }

    /**
     * Helper membuat nomor faktur penjualan unik: INV-YYYYMMDD-XXXX
     */
    private function generateSaleNumber(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $latest = Sale::where('sale_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->sale_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
