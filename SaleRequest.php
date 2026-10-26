<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:100'],
            'sold_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'sold_at.required' => 'Tanggal penjualan wajib ditentukan.',
            'items.required' => 'Transaksi penjualan harus menyertakan minimal 1 item produk.',
            'items.min' => 'Transaksi penjualan harus menyertakan minimal 1 item produk.',
            'items.*.product_id.required' => 'Produk pada baris item wajib dipilih.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak valid di database.',
            'items.*.quantity.min' => 'Kuantitas penjualan minimal 1 unit.',
        ];
    }
}
