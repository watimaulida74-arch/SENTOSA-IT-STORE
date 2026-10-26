<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk dengan search, filter kategori, status stok rendah, dan pagination
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $lowStockOnly = $request->boolean('low_stock');

        $query = Product::with('category');

        // Filter pencarian berdasarkan nama produk atau SKU
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kategori
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter hanya produk yang stoknya menipis
        if ($lowStockOnly) {
            $query->lowStock();
        }

        $products = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'lowStockOnly'));
    }

    /**
     * Form tambah produk baru (Admin / Staff)
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Simpan produk baru ke database
     */
    public function store(ProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Produk baru dengan SKU [' . $request->sku . '] berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail produk dan riwayat pergerakannya
     */
    public function show(Product $product)
    {
        $product->load(['category', 'saleItems.sale', 'purchaseItems.purchase']);
        return view('products.show', compact('product'));
    }

    /**
     * Form edit produk (Admin)
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Perbarui data produk
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', "Data produk '{$product->name}' berhasil diperbarui.");
    }

    /**
     * Hapus produk dengan validasi integritas transaksi
     */
    public function destroy(Product $product)
    {
        // Business Rule: Jangan hapus produk jika sudah memiliki riwayat transaksi (purchase atau sale)
        $hasSales = $product->saleItems()->exists();
        $hasPurchases = $product->purchaseItems()->exists();

        if ($hasSales || $hasPurchases) {
            return redirect()->route('products.index')
                ->with('error', "Produk '{$product->name}' tidak boleh dihapus karena sudah memiliki riwayat transaksi penjualan/pembelian.");
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus dari sistem.');
    }
}
