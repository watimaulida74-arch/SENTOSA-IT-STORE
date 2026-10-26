<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo accounts & realistic data.
     */
    public function run(): void
    {
        // 1. Akun Demo Berdasarkan 3 Role
        $admin = User::firstOrCreate(
            ['email' => 'admin@uts.com'],
            [
                'name' => 'Budi Santoso (Admin/Owner)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $staf = User::firstOrCreate(
            ['email' => 'staf@uts.com'],
            [
                'name' => 'Siti Rahma (Staf Gudang & Kasir)',
                'password' => Hash::make('password123'),
                'role' => 'staf',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@uts.com'],
            [
                'name' => 'Ahmad Fauzi (Pelanggan/User)',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        // 2. Kategori Produk
        $categoriesData = [
            ['name' => 'Komputer & Laptop', 'description' => 'Perangkat komputer desktop, notebook, dan ultrabook'],
            ['name' => 'Aksesoris & Peripheral', 'description' => 'Mouse, keyboard, headset, dan perangkat input lainnya'],
            ['name' => 'Penyimpanan & Komponen', 'description' => 'SSD, RAM, Harddisk, dan suku cadang komputer'],
            ['name' => 'Perangkat Jaringan', 'description' => 'Router, switch hub, kabel LAN, dan access point'],
            ['name' => 'Peralatan Kantor (ATK)', 'description' => 'Kertas, tinta printer, dan perlengkapan administrasi'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(['name' => $c['name']], $c);
        }

        // 3. Pemasok (Suppliers)
        $suppliersData = [
            [
                'name' => 'PT Sumber Makmur Elektronik',
                'phone' => '021-5551234',
                'email' => 'sales@sumbermakmur.co.id',
                'address' => 'Jl. Mangga Dua Raya No. 45, Jakarta Pusat'
            ],
            [
                'name' => 'CV Sentosa Abadi Tech',
                'phone' => '022-7778901',
                'email' => 'contact@sentosatech.id',
                'address' => 'Jl. Braga No. 88, Bandung'
            ],
            [
                'name' => 'Mitra Niaga Digital Distributor',
                'phone' => '031-8889912',
                'email' => 'distro@mitraniaga.com',
                'address' => 'Jl. Pemuda No. 120, Surabaya'
            ],
            [
                'name' => 'Toko Grosir IT Mandiri',
                'phone' => '0274-512345',
                'email' => 'info@itmandiri.com',
                'address' => 'Jl. Gejayan No. 12, Yogyakarta'
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $s) {
            $suppliers[] = Supplier::firstOrCreate(['name' => $s['name']], $s);
        }

        // 4. Produk Dummy Realistis (dengan beberapa produk berstatus stok rendah)
        $productsData = [
            [
                'category_id' => $categories['Komputer & Laptop']->id,
                'sku' => 'LAP-TP-T14',
                'name' => 'Laptop Lenovo ThinkPad T14 Gen 3 (Core i7, 16GB)',
                'purchase_price' => 14500000,
                'selling_price' => 17200000,
                'stock' => 8,
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $categories['Komputer & Laptop']->id,
                'sku' => 'MON-LG-24IPS',
                'name' => 'Monitor LG 24 Inch Full HD IPS 75Hz (24MP400)',
                'purchase_price' => 1250000,
                'selling_price' => 1550000,
                'stock' => 14,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $categories['Aksesoris & Peripheral']->id,
                'sku' => 'MOU-LOG-M331',
                'name' => 'Logitech Silent Wireless Mouse M331',
                'purchase_price' => 175000,
                'selling_price' => 245000,
                'stock' => 2, // LOW STOCK (minimum: 5)
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $categories['Aksesoris & Peripheral']->id,
                'sku' => 'KBD-KEY-K2V2',
                'name' => 'Keychron K2 V2 Wireless Mechanical Keyboard RGB',
                'purchase_price' => 1150000,
                'selling_price' => 1450000,
                'stock' => 1, // LOW STOCK (minimum: 4)
                'minimum_stock' => 4,
            ],
            [
                'category_id' => $categories['Penyimpanan & Komponen']->id,
                'sku' => 'SSD-SAM-980-1T',
                'name' => 'Samsung NVMe M.2 980 1TB PCIe 3.0',
                'purchase_price' => 1100000,
                'selling_price' => 1350000,
                'stock' => 0, // OUT OF STOCK
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $categories['Penyimpanan & Komponen']->id,
                'sku' => 'RAM-COR-16GB',
                'name' => 'Corsair Vengeance LPX DDR4 16GB (2x8GB) 3200MHz',
                'purchase_price' => 580000,
                'selling_price' => 750000,
                'stock' => 9,
                'minimum_stock' => 4,
            ],
            [
                'category_id' => $categories['Perangkat Jaringan']->id,
                'sku' => 'NET-TPL-AX73',
                'name' => 'TP-Link Archer AX73 AX5400 Dual-Band Gigabit Wi-Fi 6 Router',
                'purchase_price' => 1300000,
                'selling_price' => 1650000,
                'stock' => 6,
                'minimum_stock' => 2,
            ],
            [
                'category_id' => $categories['Aksesoris & Peripheral']->id,
                'sku' => 'CAM-LOG-C920',
                'name' => 'Webcam Logitech C920 Pro HD 1080p',
                'purchase_price' => 950000,
                'selling_price' => 1250000,
                'stock' => 7,
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $categories['Peralatan Kantor (ATK)']->id,
                'sku' => 'ATK-PAP-A480',
                'name' => 'Kertas HVS PaperOne A4 80 Gram (1 Rim/500 Lembar)',
                'purchase_price' => 48000,
                'selling_price' => 60000,
                'stock' => 45,
                'minimum_stock' => 15,
            ],
            [
                'category_id' => $categories['Aksesoris & Peripheral']->id,
                'sku' => 'KAB-UG-HDMI2',
                'name' => 'Ugreen Kabel HDMI 2.1 8K@60Hz 4K@120Hz 2 Meter',
                'purchase_price' => 120000,
                'selling_price' => 170000,
                'stock' => 28,
                'minimum_stock' => 10,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $p) {
            $createdProducts[] = Product::firstOrCreate(['sku' => $p['sku']], $p);
        }

        // 5. Transaksi Pembelian Awal (Purchase / Stock-in)
        $purchase = Purchase::firstOrCreate(
            ['purchase_number' => 'PO-' . date('Ymd') . '-0001'],
            [
                'supplier_id' => $suppliers[0]->id,
                'user_id' => $staf->id,
                'total' => 30400000,
                'status' => 'completed',
                'notes' => 'Pengadaan stok awal kuartal',
                'purchased_at' => now()->subDays(2),
            ]
        );

        if ($purchase->items()->count() === 0) {
            $purchase->items()->createMany([
                [
                    'product_id' => $createdProducts[0]->id,
                    'quantity' => 2,
                    'unit_cost' => 14500000,
                    'subtotal' => 29000000,
                ],
                [
                    'product_id' => $createdProducts[3]->id,
                    'quantity' => 1,
                    'unit_cost' => 1150000,
                    'subtotal' => 1150000,
                ],
                [
                    'product_id' => $createdProducts[1]->id,
                    'quantity' => 1,
                    'unit_cost' => 250000,
                    'subtotal' => 250000,
                ],
            ]);
        }

        // 6. Transaksi Penjualan Awal (Sales / Stock-out)
        $sale = Sale::firstOrCreate(
            ['sale_number' => 'INV-' . date('Ymd') . '-0001'],
            [
                'user_id' => $staf->id,
                'customer_name' => 'PT Maju Bersama',
                'total' => 18750000,
                'status' => 'completed',
                'notes' => 'Pesanan perlengkapan workstation programmer',
                'sold_at' => now()->subDay(),
            ]
        );

        if ($sale->items()->count() === 0) {
            $sale->items()->createMany([
                [
                    'product_id' => $createdProducts[0]->id,
                    'quantity' => 1,
                    'unit_price' => 17200000,
                    'subtotal' => 17200000,
                ],
                [
                    'product_id' => $createdProducts[1]->id,
                    'quantity' => 1,
                    'unit_price' => 1550000,
                    'subtotal' => 1550000,
                ],
            ]);
        }

        $sale2 = Sale::firstOrCreate(
            ['sale_number' => 'INV-' . date('Ymd') . '-0002'],
            [
                'user_id' => $user->id,
                'customer_name' => 'Ahmad Fauzi (Pelanggan)',
                'total' => 415000,
                'status' => 'completed',
                'notes' => 'Order daring aksesoris',
                'sold_at' => now(),
            ]
        );

        if ($sale2->items()->count() === 0) {
            $sale2->items()->createMany([
                [
                    'product_id' => $createdProducts[2]->id,
                    'quantity' => 1,
                    'unit_price' => 245000,
                    'subtotal' => 245000,
                ],
                [
                    'product_id' => $createdProducts[9]->id,
                    'quantity' => 1,
                    'unit_price' => 170000,
                    'subtotal' => 170000,
                ],
            ]);
        }
    }
}
