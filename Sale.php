<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_number',
        'customer_name',
        'total',
        'status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    /**
     * Relasi: Sale belongsTo User (Kasir/Staf atau User yang bertransaksi)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Sale hasMany SaleItems (Detail barang terjual)
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
