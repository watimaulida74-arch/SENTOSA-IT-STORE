<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Inventory & Sales Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
        }
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #1e293b;
            color: #cbd5e1;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .sidebar .brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background: #0f172a;
            border-left: 4px solid #3b82f6;
        }
        .sidebar .nav-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem 1.5rem 0.5rem;
            font-weight: 700;
        }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-area {
            padding: 1.75rem;
            flex-grow: 1;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        .badge-role-admin { background-color: #ef4444; color: white; }
        .badge-role-staf { background-color: #3b82f6; color: white; }
        .badge-role-user { background-color: #10b981; color: white; }
        @media (max-width: 991.98px) {
            .sidebar { position: static; width: 100%; min-height: auto; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <i class="bi bi-shop text-primary fs-4"></i>
            <div>
                <div>SENTOSA IT STORE</div>
                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: normal;">Pusat Komputer &amp; Retail IT</div>
            </div>
        </div>

        <nav class="nav flex-column mt-3">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <!-- MENU MASTER DATA (Admin & Staf) -->
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <div class="nav-header">Master Data</div>
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-boxes"></i> Data Produk
            </a>
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                <i class="bi bi-tags"></i> Data Kategori
            </a>
            <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                <i class="bi bi-truck"></i> Data Pemasok
            </a>
            @else
            <!-- USER/PELANGGAN MENU -->
            <div class="nav-header">Katalog Belanja</div>
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-grid"></i> Jelajah Produk
            </a>
            @endif

            <!-- MENU TRANSAKSI -->
            <div class="nav-header">Transaksi Bisnis</div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                <i class="bi bi-bag-plus"></i> Pembelian (Stock-in)
            </a>
            @endif
            <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                <i class="bi bi-cart-check"></i> Penjualan (Stock-out)
            </a>

            <!-- MENU LAPORAN & ADMIN -->
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <div class="nav-header">Analisis & Laporan</div>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-graph-up-arrow"></i> Laporan Keuangan
            </a>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="nav-header">Pengaturan Sistem</div>
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-people"></i> Manajemen Pengguna
            </a>
            @endif
        </nav>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-wrapper">
        <!-- TOP NAVBAR -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold text-secondary">
                    @yield('page_title', 'Halaman')
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold fs-7">{{ auth()->user()->name }}</div>
                    <span class="badge badge-role-{{ auth()->user()->role }}">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Keluar dari sistem">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="content-area">
            <!-- Flash Notification -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-shield-exclamation"></i> Terdapat kesalahan input formulir:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
