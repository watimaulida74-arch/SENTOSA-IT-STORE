<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory & Sales System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 1rem;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: #2563eb;
            color: #ffffff;
            padding: 2rem;
            text-align: center;
        }
        .demo-box {
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.85rem;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="bi bi-shop fs-1"></i>
        <h4 class="fw-bold mt-2 mb-1">SENTOSA IT STORE</h4>
        <div class="small opacity-75">Sistem Inventaris &amp; Penjualan Retail</div>
    </div>

    <div class="p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@uts.com" autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
                </div>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label small" for="remember">Ingat Saya di Perangkat Ini</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <!-- Quick Demo Credentials -->
        <div class="demo-box">
            <div class="fw-bold mb-1 text-secondary text-uppercase" style="font-size: 0.75rem;">
                <i class="bi bi-key-fill text-warning"></i> Akun Demo Cepat (Klik untuk isi otomatis):
            </div>
            <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-outline-danger text-start py-1" onclick="fillCreds('admin@uts.com', 'password123')">
                    <strong>Admin/Owner:</strong> admin@uts.com
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary text-start py-1" onclick="fillCreds('staf@uts.com', 'password123')">
                    <strong>Staf/Kasir:</strong> staf@uts.com
                </button>
                <button type="button" class="btn btn-sm btn-outline-success text-start py-1" onclick="fillCreds('user@uts.com', 'password123')">
                    <strong>User/Customer:</strong> user@uts.com
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillCreds(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
