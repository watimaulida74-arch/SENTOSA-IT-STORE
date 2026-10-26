<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Pemasok (supplier) wajib dipilih.',
            'supplier_id.exists' => 'Pemasok yang dipilih tidak ditemukan dalam sistem.',
            'purchased_at.required' => 'Tanggal pembelian wajib ditentukan.',
            'items.required' => 'Transaksi pembelian harus menyertakan minimal 1 barang.',
            'items.min' => 'Transaksi pembelian harus menyertakan minimal 1 barang.',
            'items.*.product_id.required' => 'Produk pada baris item wajib dipilih.',
            'items.*.quantity.min' => 'Jumlah barang pembelian minimal bernilai 1.',
            'items.*.unit_cost.min' => 'Harga modal/beli barang tidak boleh negatif.',
        ];
    }
}
