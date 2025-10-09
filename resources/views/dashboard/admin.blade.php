@extends('layout.master')

@section('title', 'Dashboard')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Row Statistik -->
    <div class="row">
        <!-- Card User -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
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
            <div class="card border-left-success shadow h-100">
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
            <div class="card border-left-info shadow h-100">
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
            <div class="card border-left-warning shadow h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Pelanggan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPelanggan }}</div>
                    </div>
                    <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Card Penjualan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Penjualan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPenjualan }}</div>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Card Profit -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Profit</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Grafik + Stok Minimum -->
    <div class="row">
        <!-- Grafik Penjualan -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Penjualan & Profit Tahun {{ $tahun }}</h6>
                    <form method="GET" action="{{ route('admin.index') }}">
                        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <canvas id="chartPenjualan" style="height: 280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Stok Minimum + Produk Terlaris -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-danger text-white d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <h6 class="m-0 font-weight-bold">Stok Minimum</h6>
                </div>
                <div class="card-body p-0">
                    @if ($stokMinimum->isEmpty())
                        <p class="text-muted text-center py-3 mb-0">
                            Tidak ada produk dengan stok minimum
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:70%">Produk</th>
                                        <th class="text-center" style="width:30%">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stokMinimum as $produk)
                                        <tr>
                                            <td class="fw-semibold">{{ $produk->nama_produk }}</td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill bg-danger px-3 py-2">
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

            <!-- Produk Terlaris (Pie kecil) -->
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-chart-pie me-2"></i>
                    <h6 class="m-0 font-weight-bold">Produk Terlaris</h6>
                </div>
                <div class="card-body text-center">
                    <canvas id="produkTerlarisChart" style="height:200px; max-height:200px;"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Grafik Penjualan & Profit
        const ctx = document.getElementById('chartPenjualan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [
                    {
                        label: 'Total Penjualan',
                        data: @json($data),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Profit (Rp)',
                        data: @json($dataProfit),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.dataset.label === 'Total Penjualan') {
                                    label += context.parsed.y + ' transaksi';
                                } else {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Jumlah Transaksi' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Profit (Rp)' },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Grafik Produk Terlaris (Pie kecil)
        const ctxPie = document.getElementById('produkTerlarisChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: @json($produkLabels),
                datasets: [{
                    data: @json($produkData),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
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
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' terjual';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
