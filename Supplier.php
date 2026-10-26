<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    /**
     * Relasi: Supplier hasMany Purchases
     * Satu supplier memiliki riwayat banyak faktur pembelian
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
