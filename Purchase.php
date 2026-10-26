<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'purchase_number',
        'total',
        'status',
        'notes',
        'purchased_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    /**
     * Relasi: Purchase belongsTo Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi: Purchase belongsTo User (Staf/Admin pembuat)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Purchase hasMany PurchaseItems
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
