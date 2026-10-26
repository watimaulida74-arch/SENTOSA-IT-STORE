<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
    ];

    /**
     * Relasi: Product belongsTo Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: Product hasMany SaleItems
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relasi: Product hasMany PurchaseItems
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Scope untuk mengambil produk dengan stok rendah (stock <= minimum_stock)
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }

    /**
     * Helper cek apakah stok produk di bawah atau sama dengan batas minimum
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }
}
