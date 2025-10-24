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

    {{-- 🔔 Notifikasi stok menipis --}}
    @if ($stokMinimum->count() > 0)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Ada {{ $stokMinimum->count() }} produk dengan stok menipis!
        </div>
    @endif

    {{-- 🔔 Notifikasi produk slow moving --}}
    @if ($produkSlowMoving->count() > 0)
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Ada {{ $produkSlowMoving->count() }} produk yang tidak terjual dalam 30 hari terakhir
        </div>
    @endif

    <!-- Row Statistik Utama -->
    <div class="row">
        <!-- Card User -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 card-hover">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total User</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUser }}</div>
                    </div>
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Card Kategori -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 card-hover">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Kategori</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalKategori }}</div>
                    </div>
                    <i class="fas fa-list fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Card Produk -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 card-hover">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Produk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProduk }}</div>
                    </div>
                    <i class="fas fa-box fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Card Pelanggan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 card-hover">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Pelanggan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPelanggan }}</div>
                    </div>
                    <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Statistik Keuangan -->
    <div class="row">
        <!-- Card Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Total Revenue</div>
                        <i class="fas fa-money-bill-wave fa-lg text-gray-300"></i>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">Pendapatan kotor tahun {{ $tahun }}</small>
                </div>
            </div>
        </div>

        <!-- Card Profit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase">Total Profit</div>
                        <i class="fas fa-chart-line fa-lg text-gray-300"></i>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp {{ number_format($totalProfit, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">Keuntungan bersih</small>
                </div>
            </div>
        </div>

        <!-- Card Penjualan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase">Total Transaksi</div>
                        <i class="fas fa-shopping-cart fa-lg text-gray-300"></i>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPenjualan) }}</div>
                    <small class="text-muted">Rata-rata: {{ number_format($rataPenjualan, 0) }}/bulan</small>
                </div>
            </div>
        </div>

        <!-- Card Margin -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase">Margin Profit</div>
                        <i class="fas fa-percentage fa-lg text-gray-300"></i>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $marginProfit }}%</div>
                    <small class="text-muted">Rata-rata nilai transaksi: Rp {{ number_format($rataTransaksi, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Pertumbuhan & Inventory -->
    <div class="row">
        <!-- Card Pertumbuhan Bulanan -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-area me-2"></i> Pertumbuhan Bulanan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small">Bulan Ini</span>
                        <h4 class="mb-0">Rp {{ number_format($bulanIni, 0, ',', '.') }}</h4>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small">Bulan Lalu</span>
                        <h5 class="mb-0 text-muted">Rp {{ number_format($bulanLalu, 0, ',', '.') }}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($pertumbuhanBulanan > 0)
                            <i class="fas fa-arrow-up text-success me-2"></i>
                            <span class="text-success font-weight-bold">+{{ $pertumbuhanBulanan }}%</span>
                        @elseif($pertumbuhanBulanan < 0)
                            <i class="fas fa-arrow-down text-danger me-2"></i>
                            <span class="text-danger font-weight-bold">{{ $pertumbuhanBulanan }}%</span>
                        @else
                            <i class="fas fa-minus text-secondary me-2"></i>
                            <span class="text-secondary font-weight-bold">0%</span>
                        @endif
                        <span class="ms-2 text-muted small">dari bulan sebelumnya</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Nilai Inventory -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-warehouse me-2"></i> Nilai Inventory
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                        <h3 class="mb-0">Rp {{ number_format($nilaiInventory, 0, ',', '.') }}</h3>
                        <p class="text-muted mb-0 mt-2">Total nilai stok saat ini</p>
                        <small class="text-muted">{{ $totalProduk }} jenis produk</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Quick Stats -->
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i> Ringkasan Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Rata-rata Penjualan/Bulan:</span>
                        <strong>{{ number_format($rataPenjualan, 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Rata-rata Profit/Bulan:</span>
                        <strong>Rp {{ number_format($rataProfit, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Produk Stok Rendah:</span>
                        <strong class="text-danger">{{ $stokMinimum->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Produk Tidak Laku:</span>
                        <strong class="text-warning">{{ $produkSlowMoving->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🆕 SECTION BARU: Aktivitas Kasir Hari Ini -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-dark text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-clock me-2"></i> Aktivitas Kasir Hari Ini ({{ \Carbon\Carbon::parse($today)->format('d M Y') }})
                        </h6>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-receipt me-1"></i> {{ $totalTransaksiHariIni }} Transaksi
                            </span>
                            <span class="badge bg-light text-success">
                                <i class="fas fa-money-bill-wave me-1"></i> Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($aktivitasKasirHariIni->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-moon fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada transaksi hari ini</h5>
                            <p class="text-muted mb-0">Aktivitas kasir akan muncul setelah ada transaksi</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 25%">Kasir</th>
                                        <th class="text-center" style="width: 12%">Transaksi</th>
                                        <th class="text-end" style="width: 18%">Total Pendapatan</th>
                                        <th class="text-end" style="width: 15%">Rata-rata</th>
                                        <th class="text-center" style="width: 15%">Pertama</th>
                                        <th class="text-center" style="width: 15%">Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($aktivitasKasirHariIni as $index => $kasir)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 35px; height: 35px; font-weight: bold;">
                                                        {{ strtoupper(substr($kasir->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong class="d-block">{{ $kasir->name }}</strong>
                                                        <small class="text-muted">{{ $kasir->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">
                                                    {{ $kasir->total_transaksi_hari_ini }}x
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    Rp {{ number_format($kasir->total_pendapatan_hari_ini, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-end text-muted">
                                                Rp {{ number_format($kasir->rata_rata_transaksi, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($kasir->transaksi_pertama)->format('H:i') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($kasir->transaksi_terakhir)->format('H:i') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td class="ps-3">
                                            <i class="fas fa-calculator me-2 text-primary"></i> TOTAL KESELURUHAN
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark fs-6">{{ $totalTransaksiHariIni }}x</span>
                                        </td>
                                        <td class="text-end text-success">
                                            Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Row Grafik Utama -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow border-0">
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
                <div class="card-body p-3">
                    <canvas id="chartPenjualan" style="height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Grafik Produk & Kategori -->
    <div class="row">
        <!-- Produk Terlaris -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-primary text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-chart-pie fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Produk Terlaris</h6>
                </div>
                <div class="card-body text-center p-4">
                    <canvas id="produkTerlarisChart" style="height:250px; max-height:250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Kategori Terlaris -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-success text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-layer-group fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Kategori Terlaris</h6>
                </div>
                <div class="card-body text-center p-4">
                    <canvas id="kategoriChart" style="height:250px; max-height:250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Stok Minimum & Produk Profit Tertinggi -->
    <div class="row">
        <!-- Stok Minimum -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-danger text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-exclamation-triangle fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Stok Minimum (≤50)</h6>
                </div>
                <div class="card-body p-0">
                    @if ($stokMinimum->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">Semua stok produk mencukupi</p>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-3" style="width:70%">Produk</th>
                                        <th class="text-center" style="width:30%">Sisa Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stokMinimum as $produk)
                                        <tr>
                                            <td class="ps-3 fw-semibold text-truncate" title="{{ $produk->nama_produk }}">
                                                {{ $produk->nama_produk }}
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

        <!-- Produk dengan Profit Tertinggi -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-success text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-trophy fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Produk Profit Tertinggi</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3" style="width:10%">#</th>
                                    <th style="width:60%">Produk</th>
                                    <th class="text-end pe-3" style="width:30%">Total Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produkProfitTertinggi as $index => $produk)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">{{ $produk->nama_produk }}</td>
                                        <td class="text-end pe-3 text-success fw-bold">
                                            Rp {{ number_format($produk->total_profit, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Pelanggan Setia & Transaksi Terbaru -->
    <div class="row">
        <!-- Pelanggan Setia -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-warning text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-star fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Pelanggan Setia (Top 5)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Pelanggan</th>
                                    <th class="text-center">Transaksi</th>
                                    <th class="text-end pe-3">Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pelangganSetia as $pelanggan)
                                    <tr>
                                        <td class="ps-3">
                                            <i class="fas fa-user-circle me-2 text-warning"></i>
                                            <strong>{{ $pelanggan->nama_pelanggan }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $pelanggan->total_transaksi }}x</span>
                                        </td>
                                        <td class="text-end pe-3 text-success fw-bold">
                                            Rp {{ number_format($pelanggan->total_belanja, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-info text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-receipt fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Transaksi Terbaru</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th class="text-end pe-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaksiTerbaru as $transaksi)
                                    <tr>
                                        <td class="ps-3">
                                            <small>{{ $transaksi->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{ $transaksi->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                        <td class="text-end pe-3 fw-bold">
                                            Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Performa Kasir & Produk Slow Moving -->
    <div class="row">
        <!-- Performa Kasir -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-primary text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-user-tie fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Performa Kasir Tahun {{ $tahun }}</h6>
                </div>
                <div class="card-body text-center p-4">
                    <canvas id="kasirChart" style="height:300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Produk Slow Moving -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-secondary text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-hourglass-half fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Produk Tidak Laku (30 Hari)</h6>
                </div>
                <div class="card-body p-0">
                    @if ($produkSlowMoving->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">Semua produk terjual dengan baik</p>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-3" style="width:70%">Produk</th>
                                        <th class="text-center" style="width:30%">Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produkSlowMoving as $produk)
                                        <tr>
                                            <td class="ps-3 text-truncate" title="{{ $produk->nama_produk }}">
                                                {{ $produk->nama_produk }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $produk->stok }}</span>
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
    </div>
@endsection

@section('styles')
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%) !important;
        }

        .bg-gradient-dark {
            background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%) !important;
        }

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-dark {
            border-left: 4px solid #858796 !important;
        }

        .table-responsive {
            overflow-y: auto;
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
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

        // Grafik Performa Kasir
        const ctxKasir = document.getElementById('kasirChart').getContext('2d');
        new Chart(ctxKasir, {
            type: 'bar',
            data: {
                labels: @json($kasirLabels),
                datasets: [{
                    label: 'Total Transaksi',
                    data: @json($kasirData),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Transaksi: ' + context.parsed.x;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
@endsection