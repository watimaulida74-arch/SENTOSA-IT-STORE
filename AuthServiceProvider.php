<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 1. Gate Manajemen Pengguna (Hanya Admin)
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // 2. Gate Pengelolaan Master Data Penuh (Hanya Admin)
        Gate::define('manage-master-data', function (User $user) {
            return $user->isAdmin();
        });

        // 3. Gate Operasional Transaksi Pembelian Stock-in (Admin & Staf)
        Gate::define('manage-purchases', function (User $user) {
            return $user->isAdmin() || $user->isStaff();
        });

        // 4. Gate Pembuatan Transaksi Penjualan (Admin, Staf, & User)
        Gate::define('manage-sales', function (User $user) {
            return in_array($user->role, ['admin', 'staf', 'user']);
        });

        // 5. Gate Pembatalan Transaksi / Void (Hanya Admin)
        Gate::define('cancel-sales', function (User $user) {
            return $user->isAdmin();
        });
    }
}
