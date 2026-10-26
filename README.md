# Integrated Laravel Project: Inventory & Sales Management System
**Ujian Tengah Semester (UTS) — Pemrograman Web 2**

Sistem Manajemen Inventaris dan Penjualan Terintegrasi berbasis **Laravel MVC**, **MySQL**, **Eloquent ORM**, autentikasi sesi, otorisasi berbasis 3 Role (**Admin**, **Staf**, **User**), validasi server-side, serta konsistensi transaksi stok menggunakan `DB::transaction()` dan pessimistic locking (`lockForUpdate`).

---

## 1. Project Overview & Business Scenario

Sebuah usaha distribusi kecil mengelola produk, pemasok, pembelian stok (stock-in), dan transaksi penjualan (stock-out) melalui beberapa staf. Sistem ini dibangun untuk mengatasi permasalahan pencatatan manual:
- **Konsistensi Stok**: Stok terpotong atau bertambah secara otomatis hanya jika transaksi sukses secara utuh.
- **Pencegahan Overselling**: Penjualan yang melebihi sisa stok fisik di gudang akan ditolak seketika dengan pesan kesalahan jelas.
- **Pessimistic Concurrency**: Menggunakan `lockForUpdate()` saat proses transaksi penjualan bersamaan.
- **Server-Side Trust**: Nilai subtotal, harga barang, dan grand total dihitung 100% oleh server berdasarkan data master di database, bukan dipercaya mentah dari browser.
- **Multi-Role Access Control**: Pemisahan hak akses server-side untuk **Admin/Owner**, **Staf Operasional/Gudang/Kasir**, dan **User/Pelanggan**.
- **Audit & Pembatalan Transaksi**: Fitur pembatalan (void) transaksi penjualan khusus Admin yang memulihkan stok kembali ke gudang secara otomatis (*reverse stock movement*).

---

## 2. Hak Akses 3 Role (Role-Based Access Control)

Sistem mengimplementasikan otorisasi di tingkat server via `RoleMiddleware` dan Laravel Gate:

| Fitur / Modul | Admin / Owner | Staf (Gudang & Kasir) | User / Pelanggan | Penjelasan Teknis |
|---|:---:|:---:|:---:|---|
| **Login / Logout** | Ya | Ya | Ya | Password di-hash menggunakan `bcrypt()`. Sesi diregenerasi untuk cegah *session fixation*. |
| **Dashboard Metrik** | Ya | Ya | Ya | Admin & Staf melihat metrik omzet, laba kotor, dan peringatan stok. User melihat sapaan & status katalog. |
| **Katalog Produk** | Full CRUD | Lihat, Tambah, Edit | Hanya Lihat | Admin & Staf dapat memperbarui katalog; User hanya melihat stok dan harga jual. |
| **Proteksi Hapus Produk** | Ya (Tervalidasi) | Ditolak | Ditolak | Produk yang pernah dipakai dalam transaksi dilarang di-hard delete (integritas relasi terjaga). |
| **Master Kategori** | Full CRUD | Hanya Lihat | Ditolak | Dilindungi middleware `role:admin`. Kategori berelasi produk dilarang dihapus. |
| **Master Pemasok** | Full CRUD | Hanya Lihat | Ditolak | Mengelola kontak distributor. Pemasok yang memiliki riwayat PO dilarang dihapus. |
| **Pembelian (Stock-in)** | Ya | Ya | Ditolak | Multi-item. Stok produk otomatis bertambah (`increment('stock', $qty)`). |
| **Penjualan (Stock-out)** | Ya | Ya | Ya | Multi-item. Stok otomatis berkurang (`decrement('stock', $qty)`). Ditransaksikan atomik. |
| **Lihat Riwayat Penjualan** | Semua Transaksi | Semua Transaksi | Transaksi Sendiri | Query difilter `$query->where('user_id', Auth::id())` jika pengguna bertindak sebagai User. |
| **Batalkan Transaksi (Void)** | Ya | Ditolak | Ditolak | Mengembalikan stok produk yang terjual secara konsisten dalam `DB::transaction`. |
| **Laporan Keuangan** | Ya | Ya | Ditolak | Filter rentang tanggal, agregasi pendapatan, biaya HPP, laba kotor, dan 5 produk terlaris. |
| **Manajemen Pengguna** | Full CRUD | Ditolak | Ditolak | Modul Admin untuk menambah user dan menetapkan role (`admin`, `staf`, `user`). |

---

## 3. Entity Relationship Diagram (ERD)

```text
+------------------+         +---------------------+         +--------------------+
|    categories    |         |      suppliers      |         |       users        |
+------------------+         +---------------------+         +--------------------+
| id (PK)          |         | id (PK)             |         | id (PK)            |
| name (Unique)    |         | name                |         | name               |
| description      |         | phone               |         | email (Unique)     |
| timestamps       |         | email               |         | password (Hashed)  |
+--------+---------+         | address             |         | role (enum)        |
         |                   | timestamps          |         | timestamps         |
         | 1                 +----------+----------+         +----+----------+----+
         |                              |                         |          |
         | hasMany                      | 1                       | 1        | 1
         |                              | hasMany                 |          |
         | N                            |                         |          |
+--------v---------+                    | N                       |          |
|     products     |         +----------v----------+              |          |
+------------------+         |      purchases      |              |          |
| id (PK)          |         +---------------------+              |          |
| category_id (FK) |         | id (PK)             |              |          |
| sku (Unique)     |         | supplier_id (FK)    |              |          |
| name             |         | user_id (FK) <------+--------------+          |
| purchase_price   |         | purchase_number(UQ) |                         |
| selling_price    |         | total               |                         |
| stock            |         | status (enum)       |                         |
| minimum_stock    |         | purchased_at        |                         |
| timestamps       |         +----------+----------+                         |
+---+----------+---+                    |                                    |
    |          |                        | 1                                  |
    | 1        | 1                      | hasMany                            |
    |          |                        |                                    |
    | N        | N                      | N                                  |
+---v----+   +-v-----------------+  +---v-----------------+   +--------------v-----+
| sale_  |   |   purchase_items  |  |   purchase_items    |   |       sales        |
| items  |   +-------------------+  +---------------------+   +--------------------+
+--------+   | id (PK)           |                            | id (PK)            |
             | purchase_id (FK)  |                            | user_id (FK) <-----+
             | product_id (FK)   |                            | sale_number (UQ)   |
             | quantity          |                            | customer_name      |
             | unit_cost         |                            | total              |
             | subtotal          |                            | status (enum)      |
             +-------------------+                            | sold_at            |
                                                              +----------+---------+
                                                                         | 1
                                                                         | hasMany
                                                                         | N
                                                              +----------v---------+
                                                              |     sale_items     |
                                                              +--------------------+
                                                              | id (PK)            |
                                                              | sale_id (FK)       |
                                                              | product_id (FK)    |
                                                              | quantity           |
                                                              | unit_price         |
                                                              | subtotal           |
                                                              +--------------------+
```

### Penjelasan Relasi Eloquent:
- **`Category hasMany Product`** & **`Product belongsTo Category`**: Satu kategori mengelompokkan banyak produk.
- **`Supplier hasMany Purchase`** & **`Purchase belongsTo Supplier`**: Faktur pengadaan merujuk ke distributor tertentu.
- **`Purchase hasMany PurchaseItem`** & **`PurchaseItem belongsTo Purchase`**: Header faktur pengadaan membawahi banyak item barang masuk.
- **`Product hasMany PurchaseItem`** & **`PurchaseItem belongsTo Product`**: Setiap baris item pengadaan menambah stok produk tertentu.
- **`User hasMany Sale`** & **`Sale belongsTo User`**: Penjualan mencatat kasir/pengguna yang bertransaksi.
- **`Sale hasMany SaleItem`** & **`SaleItem belongsTo Sale`**: Header faktur penjualan membawahi rincian produk yang dibeli konsumen.
- **`Product hasMany SaleItem`** & **`SaleItem belongsTo Product`**: Mengurangi stok produk yang bersangkutan.

---

## 4. Logika Bisnis & Konsistensi Transaksi (Critical Code)

### A. Transaksi Penjualan Atomik (Stock-out)
```php
DB::transaction(function () use ($request) {
    // 1. Simpan Header Penjualan
    $sale = Sale::create([
        'user_id' => auth()->id(),
        'sale_number' => $this->generateSaleNumber(),
        'customer_name' => $request->customer_name ?: 'Pelanggan Umum',
        'total' => 0,
        'status' => 'completed',
        'sold_at' => $request->sold_at,
    ]);

    $total = 0;

    // 2. Loop setiap item barang
    foreach ($request->items as $item) {
        // PENTING: Kunci baris dengan lockForUpdate() cegah overselling & race condition
        $product = Product::lockForUpdate()->findOrFail($item['product_id']);
        $qty = (int) $item['quantity'];

        // Cek validitas kuantitas dan kecukupan stok
        if ($qty < 1 || $product->stock < $qty) {
            throw ValidationException::withMessages([
                'items' => "Stok produk '{$product->name}' tidak mencukupi! Tersedia: {$product->stock}, Diminta: {$qty}.",
            ]);
        }

        // PENTING: Ambil harga jual dari DATABASE, BUKAN dari input browser!
        $unitPrice = $product->selling_price;
        $subtotal = $unitPrice * $qty;

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        // Kurangi stok barang
        $product->decrement('stock', $qty);
        $total += $subtotal;
    }

    // 3. Update total faktur yang dihitung server
    $sale->update(['total' => $total]);
});
```

### B. Transaksi Pembelian Atomik (Stock-in)
```php
DB::transaction(function () use ($request) {
    $purchase = Purchase::create([
        'supplier_id' => $request->supplier_id,
        'user_id' => auth()->id(),
        'purchase_number' => $this->generatePurchaseNumber(),
        'total' => 0,
        'status' => 'completed',
        'purchased_at' => $request->purchased_at,
    ]);

    $total = 0;
    foreach ($request->items as $item) {
        $product = Product::lockForUpdate()->findOrFail($item['product_id']);
        $qty = (int) $item['quantity'];
        $unitCost = (float) $item['unit_cost'];
        $subtotal = $unitCost * $qty;

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'subtotal' => $subtotal,
        ]);

        // Tambah stok barang secara otomatis
        $product->increment('stock', $qty);
        $total += $subtotal;
    }

    $purchase->update(['total' => $total]);
});
```

---

## 5. Akun Demo untuk Pengujian (Testing Credentials)

Aplikasi telah dilengkapi seeder akun demo siap pakai:

| Role Akses | Alamat Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Admin / Owner** | `admin@uts.com` | `password123` | Akses penuh, kelola user & role, CRUD master data, void transaksi |
| **Staf Operasional** | `staf@uts.com` | `password123` | Buat pembelian (stock-in), buat penjualan, lihat master data |
| **User / Pelanggan** | `user@uts.com` | `password123` | Lihat katalog stok & harga, buat order penjualan, riwayat pribadi |

---

## 6. Petunjuk Instalasi & Menjalankan Project (Reproducible Setup)

### Kebutuhan Sistem:
- PHP >= 8.0 (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`)
- MySQL Server (XAMPP / Laragon / MariaDB)
- Composer

### Langkah-langkah:
```bash
# 1. Pindah ke direktori project
cd uts_integrated_laravel_sales

# 2. Salin environment configuration
cp .env.example .env

# 3. Sesuaikan kredensial database di .env jika perlu:
# DB_DATABASE=uts_inventory_sales
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Jalankan install dependency
composer install

# 5. Generate application key
php artisan key:generate

# 6. Jalankan migrasi dan seeder otomatis
php artisan migrate:fresh --seed

# 7. Jalankan server lokal Laravel
php artisan serve
```

Aplikasi dapat langsung diakses di browser melalui URL: `http://localhost:8000`.

---

## 7. Pertanyaan & Jawaban Code Ownership Interview

Pertanyaan khas penguji/dosen dan jawaban teknis:

1. **Mengapa Sale memiliki relasi hasMany ke SaleItem?**
   - *Jawaban:* Karena satu transaksi penjualan (header faktur) dapat memuat lebih dari satu barang (*multi-item purchase*). Dengan memisahkan tabel header (`sales`) dan tabel detail (`sale_items`), data tidak melanggar First Normal Form (1NF) dan kuantitas serta harga masing-masing produk tercatat terperinci.

2. **Di mana sistem memastikan stok tidak menjadi negatif?**
   - *Jawaban:* Pada `SaleController::store()`, di dalam blok `DB::transaction()` sebelum kuantitas dikurangi. Sistem mengecek kondisi `$product->stock < $qty`. Jika kondisi ini terpenuhi, dilempar exception `ValidationException` sehingga proses dibatalkan dan respon pesan error dikirim ke pengguna.

3. **Mengapa total penjualan dihitung di server?**
   - *Jawaban:* Nilai pada browser (HTML form / JavaScript) dapat dimanipulasi dengan mudah melalui inspect element atau HTTP request tool (Postman). Menghitung total di server menjamin harga barang bersumber dari database yang sah dan mencegah kerugian akibat manipulasi harga dari sisi klien.

4. **Apa risiko jika pengurangan stok dilakukan di luar `DB::transaction()`?**
   - *Jawaban:* Terjadi ketidakkonsistenan data (*partial save*). Jika penyimpanan data header sukses tetapi saat loop item ke-3 database error (misal koneksi terputus atau disk penuh), maka faktur sudah terbuat tetapi item tidak lengkap dan sebagian stok sudah terlanjur terpotong tanpa bisa dipulihkan kembali (*dirty data*).

5. **Bagaimana mencegah Staf atau User mengakses fungsi Admin melalui URL langsung?**
   - *Jawaban:* Menggunakan `RoleMiddleware` yang didaftarkan pada route group di `routes/web.php`. Middleware ini memeriksa `$request->user()->role` di tingkat server. Jika role tidak sesuai, sistem langsung memutus request dan melempar HTTP 403 Forbidden sebelum Controller sempat dieksekusi.

6. **Apa yang terjadi jika dua kasir menjual stok produk yang sama hampir bersamaan?**
   - *Jawaban:* Jika tanpa proteksi, dapat terjadi *race condition* dan *overselling* (misal sisa stok 1, dibeli kasir A dan B bersamaan, stok menjadi -1). Sistem ini menggunakan `Product::lockForUpdate()`, yaitu *pessimistic locking* pada level baris database MySQL. Transaksi pertama akan mengunci baris tersebut sampai selesai commit, sehingga transaksi kedua harus menunggu dan akan ditolak karena stok sudah 0 saat giliran transaksinya diproses.
