@extends('layout.master')

@section('title', 'Dashboard Kasir')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-primary mb-0">
                <i class="fas fa-cash-register me-2"></i> Dashboard Kasir
            </h3>
            <small class="text-muted">
                Kasir Aktif: <strong>{{ Auth::user()->name }}</strong>
            </small>
        </div>
        <div class="text-end">
            <span class="text-muted d-block">Tanggal: {{ $tanggalHariIni->translatedFormat('l, d F Y') }}</span>
            <span id="jamSekarang" class="text-muted small"></span>
        </div>
    </div>

    {{-- Ringkasan Hari Ini --}}
    <div class="row">
        <!-- Total Transaksi -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-primary text-uppercase mb-2">Transaksi Hari Ini</h6>
                        <h3 class="fw-bold mb-0">{{ $totalTransaksi }}</h3>
                    </div>
                    <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-success text-uppercase mb-2">Pendapatan Hari Ini</h6>
                        <h3 class="fw-bold mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card border-left-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-warning text-uppercase mb-2">Aksi Cepat</h6>
                    <a href="{{ route('penjualan.create') }}" class="btn btn-warning w-100 fw-bold">
                        <i class="fas fa-plus me-2"></i> Tambah Penjualan
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bagian Grafik & Stok --}}
    <div class="row">
        <!-- Produk Terjual -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-chart-pie me-2"></i> Produk Terjual Hari Ini
                    </h6>
                </div>
                <div class="card-body text-center">
                    <canvas id="produkTerjualChart" style="max-height:180px; max-width:100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Stok Hampir Habis -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> Stok Hampir Habis
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Produk</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stokMenipis as $produk)
                                <tr>
                                    <td>{{ $produk->nama_produk }}</td>
                                    <td><span class="badge bg-danger">{{ $produk->stok }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Semua stok masih aman 🎉</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaksi Terbaru --}}
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-receipt me-2"></i> Transaksi Terbaru
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksiTerbaru as $index => $transaksi)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $transaksi->pelanggan->nama_pelanggan ?? '-' }}</td>
                                    <td>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_penjualan)->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada transaksi hari ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Grafik Produk Terjual
    const ctx = document.getElementById('produkTerjualChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($produkLabels),
            datasets: [{
                data: @json($produkData),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${context.parsed} pcs`
                    }
                }
            },
            layout: { padding: 10 }
        }
    });

    // Jam real-time
    function updateJam() {
        const now = new Date();
        const jam = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('jamSekarang').textContent = jam;
    }
    setInterval(updateJam, 1000);
    updateJam();
</script>
@endsection
