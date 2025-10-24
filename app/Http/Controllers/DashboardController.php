<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Users;
use App\Models\Kategoris;
use App\Models\Produks;
use App\Models\Pelanggans;
use App\Models\Penjualans;
use App\Models\DetailPenjualans;

class DashboardController extends Controller
{
    public function admindashboard(Request $request)
{
    $tahun = $request->input('tahun', date('Y'));
    $today = date('Y-m-d');

    // ==================== STATISTIK DASAR ====================
    $totalUser = Users::count();
    $totalKategori = Kategoris::count();
    $totalProduk = Produks::count();
    $totalPelanggan = Pelanggans::count();
    $totalPenjualan = Penjualans::whereYear('created_at', $tahun)->count();

    // ==================== KEUANGAN ====================
    $totalRevenue = Penjualans::whereYear('created_at', $tahun)->sum('total_harga');
    
    $totalProfit = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
        ->whereYear('tbl_penjualans.created_at', $tahun)
        ->selectRaw('SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as profit')
        ->value('profit') ?? 0;

    $marginProfit = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;
    $rataTransaksi = $totalPenjualan > 0 ? round($totalRevenue / $totalPenjualan, 0) : 0;
    $rataPenjualan = round($totalPenjualan / 12, 2);
    $rataProfit = round($totalProfit / 12, 2);

    // ==================== DATA GRAFIK PER BULAN ====================
    $penjualanPerBulan = Penjualans::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
        ->whereYear('created_at', $tahun)
        ->groupBy('bulan')
        ->pluck('total', 'bulan')
        ->toArray();

    $profitPerBulan = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
        ->selectRaw('MONTH(tbl_penjualans.created_at) as bulan, SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as profit')
        ->whereYear('tbl_penjualans.created_at', $tahun)
        ->groupBy('bulan')
        ->pluck('profit', 'bulan')
        ->toArray();

    $revenuePerBulan = Penjualans::selectRaw('MONTH(created_at) as bulan, SUM(total_harga) as revenue')
        ->whereYear('created_at', $tahun)
        ->groupBy('bulan')
        ->pluck('revenue', 'bulan')
        ->toArray();

    // Format data untuk grafik
    $labels = [];
    $data = [];
    $dataProfit = [];
    $dataRevenue = [];
    
    for ($i = 1; $i <= 12; $i++) {
        $labels[] = date("F", mktime(0, 0, 0, $i, 1));
        $data[] = $penjualanPerBulan[$i] ?? 0;
        $dataProfit[] = $profitPerBulan[$i] ?? 0;
        $dataRevenue[] = $revenuePerBulan[$i] ?? 0;
    }

    // ==================== PRODUK ====================
    $stokMinimum = Produks::where('stok', '<=', 50)->get();
    
    $produkTerlaris = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->selectRaw('tbl_produks.nama_produk, SUM(tbl_detail_penjualans.jumlah_produk) as total_terjual')
        ->groupBy('tbl_produks.nama_produk')
        ->orderByDesc('total_terjual')
        ->limit(5)
        ->get();

    $produkLabels = $produkTerlaris->pluck('nama_produk');
    $produkData = $produkTerlaris->pluck('total_terjual');

    $produkProfitTertinggi = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->selectRaw('tbl_produks.nama_produk, SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as total_profit')
        ->groupBy('tbl_produks.nama_produk')
        ->orderByDesc('total_profit')
        ->limit(5)
        ->get();

    $produkSlowMoving = Produks::whereNotIn('produk_id', function($query) {
            $query->select('produk_id')
                ->from('tbl_detail_penjualans')
                ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
                ->where('tbl_penjualans.created_at', '>=', Carbon::now()->subDays(30));
        })
        ->where('stok', '>', 0)
        ->limit(10)
        ->get();

    $nilaiInventory = Produks::selectRaw('SUM(stok * harga_jual) as total')->value('total') ?? 0;

    // ==================== KATEGORI ====================
    $kategoriTerlaris = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->join('tbl_kategoris', 'tbl_produks.kategori_id', '=', 'tbl_kategoris.kategori_id')
        ->selectRaw('tbl_kategoris.nama_kategori, SUM(tbl_detail_penjualans.jumlah_produk) as total_terjual')
        ->groupBy('tbl_kategoris.nama_kategori')
        ->orderByDesc('total_terjual')
        ->limit(5)
        ->get();

    $kategoriLabels = $kategoriTerlaris->pluck('nama_kategori');
    $kategoriData = $kategoriTerlaris->pluck('total_terjual');

    // ==================== PELANGGAN ====================
    $pelangganSetia = Penjualans::join('tbl_pelanggans', 'tbl_penjualans.pelanggan_id', '=', 'tbl_pelanggans.pelanggan_id')
        ->selectRaw('tbl_pelanggans.nama_pelanggan, COUNT(*) as total_transaksi, SUM(tbl_penjualans.total_harga) as total_belanja')
        ->groupBy('tbl_pelanggans.nama_pelanggan')
        ->orderByDesc('total_transaksi')
        ->limit(5)
        ->get();

    // ==================== TRANSAKSI ====================
    $transaksiTerbaru = Penjualans::with('pelanggan', 'user')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // ==================== PERFORMA KASIR TAHUNAN ====================
    $performaKasir = Penjualans::join('users', 'tbl_penjualans.user_id', '=', 'users.id')
        ->whereYear('tbl_penjualans.created_at', $tahun)
        ->selectRaw('users.name, COUNT(*) as total_transaksi, SUM(tbl_penjualans.total_harga) as total_pendapatan')
        ->groupBy('users.name')
        ->orderByDesc('total_transaksi')
        ->get();

    $kasirLabels = $performaKasir->pluck('name');
    $kasirData = $performaKasir->pluck('total_transaksi');

    // ==================== AKTIVITAS KASIR HARI INI ====================
    $aktivitasKasirHariIni = Penjualans::join('users', 'tbl_penjualans.user_id', '=', 'users.id')
        ->whereDate('tbl_penjualans.created_at', $today)
        ->selectRaw('
            users.id,
            users.name,
            users.email,
            COUNT(*) as total_transaksi_hari_ini,
            SUM(tbl_penjualans.total_harga) as total_pendapatan_hari_ini,
            MIN(tbl_penjualans.created_at) as transaksi_pertama,
            MAX(tbl_penjualans.created_at) as transaksi_terakhir,
            AVG(tbl_penjualans.total_harga) as rata_rata_transaksi
        ')
        ->groupBy('users.id', 'users.name', 'users.email')
        ->orderByDesc('total_transaksi_hari_ini')
        ->get();

    // Total keseluruhan hari ini
    $totalTransaksiHariIni = Penjualans::whereDate('created_at', $today)->count();
    $totalPendapatanHariIni = Penjualans::whereDate('created_at', $today)->sum('total_harga');

    // ==================== PERTUMBUHAN BULANAN ====================
    $bulanIni = Penjualans::whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->sum('total_harga');

    $bulanLalu = Penjualans::whereMonth('created_at', date('m', strtotime('-1 month')))
        ->whereYear('created_at', date('Y', strtotime('-1 month')))
        ->sum('total_harga');

    $pertumbuhanBulanan = $bulanLalu > 0 ? round((($bulanIni - $bulanLalu) / $bulanLalu) * 100, 2) : 0;

    // ==================== RETURN VIEW ====================
    return view('dashboard.admin', compact(
        'totalUser',
        'totalKategori',
        'totalProduk',
        'totalPelanggan',
        'totalPenjualan',
        'totalProfit',
        'totalRevenue',
        'marginProfit',
        'rataTransaksi',
        'rataPenjualan',
        'rataProfit',
        'labels',
        'data',
        'dataProfit',
        'dataRevenue',
        'tahun',
        'stokMinimum',
        'produkLabels',
        'produkData',
        'produkProfitTertinggi',
        'kategoriLabels',
        'kategoriData',
        'pelangganSetia',
        'transaksiTerbaru',
        'performaKasir',
        'kasirLabels',
        'kasirData',
        'nilaiInventory',
        'produkSlowMoving',
        'bulanIni',
        'bulanLalu',
        'pertumbuhanBulanan',
        'aktivitasKasirHariIni',
        'totalTransaksiHariIni',
        'totalPendapatanHariIni',
        'today'
    ));
}
}
