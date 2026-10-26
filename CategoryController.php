<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $categories = Category::withCount('products')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    /**
     * Tampilkan form tambah kategori (Khusus Admin)
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Simpan kategori baru (Khusus Admin)
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit kategori (Khusus Admin)
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Perbarui data kategori (Khusus Admin)
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori dengan proteksi integritas data
     */
    public function destroy(Category $category)
    {
        // Proteksi: cegah penghapusan jika kategori masih memiliki produk
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih menaungi {$category->products()->count()} produk.");
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
