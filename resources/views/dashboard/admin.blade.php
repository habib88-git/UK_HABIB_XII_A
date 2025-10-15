@extends('layout.master')

@section('title', 'Dashboard')

@section('content')
    <!-- Header dengan background gradient -->
    <div class="d-flex align-items-center justify-content-between mb-4 p-4 rounded-3"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div>
            <h1 class="h3 mb-1 text-white">Dashboard</h1>
            <p class="mb-0 text-white-50">Ringkasan statistik dan performa toko</p>
        </div>
        <div class="text-white">
            <i class="fas fa-chart-line fa-2x opacity-75"></i>
        </div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                            {{ number_format($totalProfit, 0, ',', '.') }}</div>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Grafik -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i> Grafik Penjualan & Profit Tahun {{ $tahun }}
                    </h6>
                    <form method="GET" action="{{ route('admin.index') }}">
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <select name="tahun" class="form-select form-select-sm border-end-0"
                                onchange="this.form.submit()">
                                @for ($i = date('Y'); $i >= date('Y') - 50; $i--)
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
                    <canvas id="chartPenjualan" style="height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Stok Minimum & Produk Terlaris -->
    <div class="row">
        <!-- Stok Minimum -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-gradient-danger text-white d-flex align-items-center py-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 30px; height: 30px; background-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-exclamation-triangle fa-sm"></i>
                    </div>
                    <h6 class="m-0 font-weight-bold">Stok Minimum</h6>
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
                                        <th class="text-center" style="width:30%">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stokMinimum as $produk)
                                        <tr>
                                            <td class="ps-3 fw-semibold text-truncate" title="{{ $produk->nama_produk }}">
                                                {{ $produk->nama_produk }}
                                            </td>
                                            <td class="text-center">
                                                {{ $produk->stok }}
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
                    <canvas id="produkTerlarisChart" style="height:220px; max-height:220px;"></canvas>
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
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
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
    </style>
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
                datasets: [{
                        label: 'Total Penjualan',
                        data: @json($data),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y'
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
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label + ': ';
                                return context.dataset.label === 'Total Penjualan' ?
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
                            callback: v => v >= 1e6 ? 'Rp ' + (v / 1e6).toFixed(1) + 'Jt' : v >= 1e3 ? 'Rp ' + (v /
                                1e3).toFixed(0) + 'Rb' : 'Rp ' + v.toLocaleString('id-ID')
                        },
                        title: {
                            display: true,
                            text: 'Profit (Rp)'
                        }
                    }
                }
            }
        });

        // Grafik Produk Terlaris
        const ctxPie = document.getElementById('produkTerlarisChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: @json($produkLabels),
                datasets: [{
                    data: @json($produkData),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)'
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
                                size: 10
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
