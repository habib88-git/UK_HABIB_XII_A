@extends('layout.master')
@section('title', 'Detail Penjualan')
@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">🧾 Detail Penjualan</h4>
            </div>
            <div class="card-body">
                {{-- Info Umum --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong>
                            {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y') }}</p>
                        <p><strong>Pelanggan:</strong> {{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Kasir:</strong> {{ $penjualan->user->name ?? '-' }}</p>
                        <p><strong>Total Harga:</strong> <span class="text-success">Rp
                                {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span></p>
                    </div>
                </div>

                {{-- Produk Dibeli --}}
                <h5 class="text-secondary mb-3">📦 Produk Dibeli</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Produk</th>
                                <th width="100px">Jumlah</th>
                                <th width="200px">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penjualan->detailPenjualans as $detail)
                                <tr>
                                    <td>{{ $detail->produk->nama_produk }}</td>
                                    <td class="text-center">{{ $detail->jumlah_produk }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>Rp
                                        {{ number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                            @if ($penjualan->diskon > 0)
                                <tr>
                                    <td colspan="2" class="text-end">
                                        <strong>Diskon
                                            @if ($penjualan->diskon > 0)
                                                ({{ number_format(($penjualan->diskon / $penjualan->total_harga) * 100, 1) }}%):
                                            @else
                                                :
                                            @endif
                                        </strong>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong>- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif
                            <tr class="table-success">
                                <td colspan="2" class="text-end"><strong>Total Setelah Diskon:</strong></td>
                                <td class="text-end">
                                    <strong>Rp
                                        {{ number_format($penjualan->total_harga - $penjualan->diskon, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Ringkasan Pembayaran --}}
                <h5 class="text-secondary mt-4 mb-3">💳 Pembayaran</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Metode:</strong>
                            <p class="mb-0">{{ ucfirst($penjualan->pembayaran->metode ?? '-') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Jumlah Bayar:</strong>
                            <p class="mb-0 text-success">Rp
                                {{ number_format($penjualan->pembayaran->jumlah ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Kembalian:</strong>
                            <p class="mb-0 text-danger">Rp
                                {{ number_format($penjualan->pembayaran->kembalian ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Penjualan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
