<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Helper Methods untuk Role Checking (Admin, Staf, User)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staf';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Relasi: User memiliki banyak transaksi Penjualan (Sale)
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relasi: User memiliki banyak transaksi Pembelian (Purchase)
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
