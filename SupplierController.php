<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $suppliers = Supplier::withCount('purchases')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    /**
     * Form tambah supplier
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Simpan supplier baru
     */
    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok baru berhasil disimpan.');
    }

    /**
     * Form edit supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Perbarui data supplier
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Data pemasok berhasil diperbarui.');
    }

    /**
     * Hapus data supplier dengan proteksi transaksi
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', "Pemasok '{$supplier->name}' tidak dapat dihapus karena memiliki riwayat faktur pembelian.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }
}
