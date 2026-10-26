@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Kelola Pengguna Sistem & Hak Akses (Role)')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama atau email pengguna..." value="{{ $search }}">
            </div>

            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Semua Role Akses --</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin / Owner</option>
                    <option value="staf" {{ $role === 'staf' ? 'selected' : '' }}>Staf Gudang & Kasir</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User / Pelanggan</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                @if($search || $role)
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-people me-1 text-primary"></i> Daftar Pengguna Terdaftar</span>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
            <i class="bi bi-person-plus"></i> Tambah Pengguna Baru
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Lengkap</th>
                    <th>Alamat Email</th>
                    <th>Role / Hak Akses</th>
                    <th>Waktu Dibuat</th>
                    <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                <tr>
                    <td>{{ $users->firstItem() + $index }}</td>
                    <td class="fw-semibold">
                        {{ $u->name }}
                        @if($u->id === auth()->id())
                            <span class="badge bg-secondary-subtle text-secondary ms-1">Anda</span>
                        @endif
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if($u->role === 'admin')
                            <span class="badge badge-role-admin">ADMIN / OWNER</span>
                        @elseif($u->role === 'staf')
                            <span class="badge badge-role-staf">STAF OPERASIONAL</span>
                        @else
                            <span class="badge badge-role-user">USER / PELANGGAN</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $u->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>

                        @if($u->id !== auth()->id())
                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada pengguna yang cocok dengan filter pencarian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
