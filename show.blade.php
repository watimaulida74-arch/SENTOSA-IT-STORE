@extends('layouts.app')

@section('title', 'Faktur Penjualan ' . $sale->sale_number)
@section('page_title', 'Struk & Rincian Faktur Penjualan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="fw-bold mb-0 text-success">STRUK PEMBELIAN KONSUMEN</h5>
                    <small class="text-muted">No. Invoice: <strong>{{ $sale->sale_number }}</strong></small>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer"></i> Cetak Struk
                    </button>

                    <!-- Tombol Pembatalan Transaksi Khusus Admin (Slide 03 & 04) -->
                    @if(auth()->user()->isAdmin() && $sale->status === 'completed')
                    <form action="{{ route('sales.cancel', $sale->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Membatalkan faktur penjualan ini akan mengembalikan stok seluruh barang ke inventaris. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle"></i> Batalkan Faktur & Pulihkan Stok
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Status Pembatalan jika dibatalkan -->
                @if($sale->status === 'cancelled')
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                    <div>
                        <strong>TRANSAKSI INI TELAH DIBATALKAN (VOID)</strong><br>
                        Seluruh stok produk dalam transaksi ini telah dikembalikan secara konsisten ke sistem inventaris.
                    </div>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted text-uppercase fw-bold small">Diterbitkan Untuk Pelanggan:</h6>
                        <h5 class="fw-bold text-dark mb-1">{{ $sale->customer_name ?: 'Pelanggan Umum' }}</h5>
                        <p class="mb-0 text-secondary small">
                            Status Akun: 
                            @if($sale->user->isUser())
                                <span class="badge bg-success-subtle text-success">Member / Pelanggan Terdaftar</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Transaksi Kasir Kas</span>
                            @endif
                        </p>
                    </div>

                    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                        <h6 class="text-muted text-uppercase fw-bold small">Data Invoice:</h6>
                        <table class="table table-borderless table-sm mb-0 ms-auto" style="width: auto;">
                            <tr>
                                <td class="text-muted text-start">Waktu Transaksi:</td>
                                <td class="text-end fw-semibold">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted text-start">Kasir / Operator:</td>
                                <td class="text-end fw-semibold">{{ $sale->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted text-start">Status:</td>
                                <td class="text-end">
                                    <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($sale->notes)
                <div class="alert alert-light border small mb-4">
                    <strong>Catatan Transaksi:</strong> {{ $sale->notes }}
                </div>
                @endif

                <div class="table-responsive mb-4">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Nama Produk</th>
                                <th>SKU</th>
                                <th class="text-center" style="width: 100px;">Qty</th>
                                <th class="text-end" style="width: 160px;">Harga Satuan</th>
                                <th class="text-end" style="width: 180px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $item->product->name }}</td>
                                <td><code>{{ $item->product->sku }}</code></td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold fs-6">TOTAL PEMBAYARAN:</td>
                                <td class="text-end fw-bold fs-5 text-success">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row pt-4 text-center small text-muted border-top">
                    <div class="col-6">
                        <p class="mb-4">Tanda Terima Pelanggan,</p>
                        <br><br>
                        <p class="fw-bold mb-0">({{ $sale->customer_name ?: 'Pelanggan' }})</p>
                    </div>
                    <div class="col-6">
                        <p class="mb-4">Kasir yang Bertugas,</p>
                        <br><br>
                        <p class="fw-bold mb-0">({{ $sale->user->name }})</p>
                    </div>
                </div>

                <div class="text-center mt-4 small text-secondary">
                    <em>Terima kasih telah berbelanja. Simpan struk ini sebagai bukti pembayaran yang sah.</em>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
