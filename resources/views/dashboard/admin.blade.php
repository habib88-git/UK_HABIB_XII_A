@extends('layout.master')

@section('title', 'Dashboard')

@section('content')
    <!-- Header dengan background gradient -->
    <div class="d-flex align-items-center justify-content-between mb-4 p-4 rounded-3"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div>
            <h1 class="h3 mb-1 text-white">Dashboard Admin</h1>
            <p class="mb-0 text-white-50">Ringkasan statistik dan performa toko tahun {{ $tahun }}</p>
        </div>
        <div class="text-white">
            <i class="fas fa-chart-line fa-2x opacity-75"></i>
        </div>
    </div>

    {{-- 🔔 Notifikasi --}}
    <div class="row mb-4">
        @if ($stokMinimum->count() > 0)
            <div class="col-md-6">
                <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                    <div>
                        <strong>Stok Menipis!</strong><br>
                        Ada {{ $stokMinimum->count() }} produk dengan stok ≤50
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Row Statistik Utama -->
    <div class="row mb-4">
        <!-- Card User -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total User</h6>
                            <h2 class="mb-0">{{ $totalUser }}</h2>
                            <small class="text-white-50">Aktif</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kategori -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Kategori</h6>
                            <h2 class="mb-0">{{ $totalKategori }}</h2>
                            <small class="text-white-50">Produk</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Produk -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Produk</h6>
                            <h2 class="mb-0">{{ $totalProduk }}</h2>
                            <small class="text-white-50">Item</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-box fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Pelanggan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Pelanggan</h6>
                            <h2 class="mb-0">{{ $totalPelanggan }}</h2>
                            <small class="text-white-50">Registered</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-friends fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Statistik Keuangan -->
    <div class="row mb-4">
        <!-- Card Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-dark text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Revenue</h6>
                            <h4 class="mb-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                            <small class="text-white-50">Pendapatan {{ $tahun }}</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Profit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Profit</h6>
                            <h4 class="mb-1">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h4>
                            <small class="text-white-50">Keuntungan bersih</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Penjualan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Total Transaksi</h6>
                            <h2 class="mb-0">{{ number_format($totalPenjualan) }}</h2>
                            <small class="text-white-50">Rata: {{ number_format($rataPenjualan, 0) }}/bulan</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Margin -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white shadow-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-content">
                            <h6 class="card-title text-white-50 mb-2">Margin Profit</h6>
                            <h2 class="mb-0">{{ $marginProfit }}%</h2>
                            <small class="text-white-50">Rata transaksi: Rp {{ number_format($rataTransaksi, 0, ',', '.') }}</small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Grafik Utama -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i> Grafik Penjualan, Revenue & Profit Tahun {{ $tahun }}
                    </h6>
                    <form method="GET" action="{{ route('admin.index') }}">
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <select name="tahun" class="form-select form-select-sm border-end-0"
                                onchange="this.form.submit()">
                                @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <span class="input-group-text bg-white border-start-0">
                                <i class="fas fa-calendar-alt text-primary"></i>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="card-body p-4">
                    <canvas id="chartPenjualan" style="height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Middle Content -->
    <div class="row mb-4">
        <!-- Produk Terlaris -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-pie me-2"></i> Produk Terlaris
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-3" style="height: 250px;">
                        <canvas id="produkTerlarisChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategori Terlaris -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-gradient-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-layer-group me-2"></i> Kategori Terlaris
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-3" style="height: 250px;">
                        <canvas id="kategoriChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tachometer-alt me-2"></i> Ringkasan Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="quick-stats">
                        <div class="stat-item d-flex justify-content-between align-items-center mb-3 p-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line text-primary me-3"></i>
                                <span>Rata Penjualan/Bulan</span>
                            </div>
                            <strong>{{ number_format($rataPenjualan, 0) }}</strong>
                        </div>
                        
                        <div class="stat-item d-flex justify-content-between align-items-center mb-3 p-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-coins text-success me-3"></i>
                                <span>Rata Profit/Bulan</span>
                            </div>
                            <strong>Rp {{ number_format($rataProfit, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div class="stat-item d-flex justify-content-between align-items-center mb-3 p-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-danger me-3"></i>
                                <span>Stok Rendah</span>
                            </div>
                            <strong class="text-danger">{{ $stokMinimum->count() }}</strong>
                        </div>
                        
                        <div class="stat-item d-flex justify-content-between align-items-center p-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-warning me-3"></i>
                                <span>Produk Tidak Laku</span>
                            </div>
                            <strong class="text-warning">{{ $produkSlowMoving->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Bottom Content - Updated to match "Produk Baru Masuk" style -->
    <div class="row">
        <!-- Stok Minimum -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> Stok Minimum (±50)
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if ($stokMinimum->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">Semua stok produk mencukupi</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width:70%">Produk</th>
                                        <th class="text-center" style="width:30%">Sisa Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stokMinimum as $produk)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-truncate" style="max-width: 200px;" 
                                                     title="{{ $produk->nama_produk }}">
                                                    {{ $produk->nama_produk }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $produk->stok <= 10 ? 'bg-danger' : ($produk->stok <= 30 ? 'bg-warning' : 'bg-info') }}">
                                                    {{ $produk->stok }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Produk Profit Tertinggi -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-trophy me-2"></i> Produk Profit Tertinggi
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:10%">No</th>
                                    <th style="width:60%">Produk</th>
                                    <th class="text-end pe-3" style="width:30%">Total Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produkProfitTertinggi as $index => $produk)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="text-muted">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 180px;"
                                                 title="{{ $produk->nama_produk }}">
                                                {{ $produk->nama_produk }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <strong class="text-success">Rp {{ number_format($produk->total_profit, 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pelanggan Setia -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-star me-2"></i> Pelanggan Setia (Top 5)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:40%">Pelanggan</th>
                                    <th class="text-center" style="width:30%">Transaksi</th>
                                    <th class="text-end pe-3" style="width:30%">Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pelangganSetia as $pelanggan)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-truncate" style="max-width: 150px;"
                                                 title="{{ $pelanggan->nama_pelanggan }}">
                                                {{ $pelanggan->nama_pelanggan }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $pelanggan->total_transaksi }}x</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <strong class="text-success">Rp {{ number_format($pelanggan->total_belanja, 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <p class="mb-0">Belum ada data pelanggan</p>
                                        </td>
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

@section('styles')
<style>
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
    }
    
    .stat-card .card-body {
        padding: 1.5rem;
    }
    
    .stat-card .stat-content h2,
    .stat-card .stat-content h4 {
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .stat-card .stat-icon {
        opacity: 0.8;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%) !important;
    }
    
    .bg-gradient-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%) !important;
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%) !important;
    }
    
    .quick-stats .stat-item {
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .quick-stats .stat-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .table-responsive {
        border-radius: 0 0 10px 10px;
    }
    
    .card {
        border-radius: 10px;
        border: 1px solid #e3e6f0;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        background: white;
    }
    
    /* Table styling to match the reference image */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #6e707e;
        background-color: #f8f9fc;
        padding: 0.75rem;
    }
    
    .table td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Grafik Penjualan, Revenue & Profit
        const ctx = document.getElementById('chartPenjualan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                        label: 'Total Transaksi',
                        data: @json($data),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (Rp)',
                        data: @json($dataRevenue),
                        backgroundColor: 'rgba(153, 102, 255, 0.7)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Profit (Rp)',
                        data: @json($dataProfit),
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label + ': ';
                                return context.dataset.label === 'Total Transaksi' ?
                                    label + context.parsed.y + ' transaksi' :
                                    label + 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi'
                        },
                        ticks: {
                            color: '#6c757d'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#6c757d',
                            callback: v => v >= 1e6 ? 'Rp ' + (v / 1e6).toFixed(1) + 'Jt' :
                                v >= 1e3 ? 'Rp ' + (v / 1e3).toFixed(0) + 'Rb' :
                                'Rp ' + v.toLocaleString('id-ID')
                        },
                        title: {
                            display: true,
                            text: 'Revenue & Profit (Rp)'
                        }
                    }
                }
            }
        });

        // Grafik Produk Terlaris
        const ctxPie = document.getElementById('produkTerlarisChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: @json($produkLabels),
                datasets: [{
                    data: @json($produkData),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = Math.round((context.parsed / total) * 100);
                                return `${context.label}: ${context.parsed} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Grafik Kategori Terlaris
        const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
        new Chart(ctxKategori, {
            type: 'pie',
            data: {
                labels: @json($kategoriLabels),
                datasets: [{
                    data: @json($kategoriData),
                    backgroundColor: [
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = Math.round((context.parsed / total) * 100);
                                return `${context.label}: ${context.parsed} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection