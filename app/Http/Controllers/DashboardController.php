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
        // Ambil tahun dari request atau default tahun sekarang
        $tahun = $request->input('tahun', date('Y'));

        // Hitung total data
        $totalUser      = Users::count();
        $totalKategori  = Kategoris::count();
        $totalProduk    = Produks::count();
        $totalPelanggan = Pelanggans::count();

        // 🔹 Total Penjualan berdasarkan tahun terpilih
        $totalPenjualan = Penjualans::whereYear('created_at', $tahun)->count();

        // 🔹 Total Profit berdasarkan tahun terpilih
        $totalProfit = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
            ->whereYear('tbl_penjualans.created_at', $tahun)
            ->select(DB::raw('SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as profit'))
            ->value('profit') ?? 0;

        // 🔹 Total Revenue (Pendapatan)
        $totalRevenue = Penjualans::whereYear('created_at', $tahun)->sum('total_harga');

        // 🔹 Margin Profit (%)
        $marginProfit = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

        // 🔹 Rata-rata nilai transaksi
        $rataTransaksi = $totalPenjualan > 0 ? round($totalRevenue / $totalPenjualan, 0) : 0;

        // 🔹 Data penjualan per bulan (berdasarkan tahun)
        $penjualan = Penjualans::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // 🔹 Data profit per bulan (berdasarkan tahun)
        $profitPerBulan = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
            ->select(
                DB::raw('MONTH(tbl_penjualans.created_at) as bulan'),
                DB::raw('SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as profit')
            )
            ->whereYear('tbl_penjualans.created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('profit', 'bulan')
            ->toArray();

        // 🔹 Data revenue per bulan
        $revenuePerBulan = Penjualans::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total_harga) as revenue')
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('revenue', 'bulan')
            ->toArray();

        // 🔹 Siapkan data grafik
        $labels = [];
        $data   = [];
        $dataProfit = [];
        $dataRevenue = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date("F", mktime(0, 0, 0, $i, 1));
            $data[]   = $penjualan[$i] ?? 0;
            $dataProfit[] = $profitPerBulan[$i] ?? 0;
            $dataRevenue[] = $revenuePerBulan[$i] ?? 0;
        }

        // 🔹 Ambil produk dengan stok minimum (<= 50)
        $stokMinimum = Produks::where('stok', '<=', 50)->get();

        // 🔹 Produk terlaris (Top 5)
        $produkTerlaris = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->select('tbl_produks.nama_produk', DB::raw('SUM(tbl_detail_penjualans.jumlah_produk) as total_terjual'))
            ->groupBy('tbl_produks.nama_produk')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $produkLabels = $produkTerlaris->pluck('nama_produk');
        $produkData   = $produkTerlaris->pluck('total_terjual');

        // 🔹 Produk dengan profit tertinggi (Top 5)
        $produkProfitTertinggi = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->select(
                'tbl_produks.nama_produk',
                DB::raw('SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as total_profit')
            )
            ->groupBy('tbl_produks.nama_produk')
            ->orderByDesc('total_profit')
            ->limit(5)
            ->get();

        // 🔹 Kategori terlaris
        $kategoriTerlaris = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->join('tbl_kategoris', 'tbl_produks.kategori_id', '=', 'tbl_kategoris.kategori_id')
            ->select(
                'tbl_kategoris.nama_kategori',
                DB::raw('SUM(tbl_detail_penjualans.jumlah_produk) as total_terjual')
            )
            ->groupBy('tbl_kategoris.nama_kategori')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $kategoriLabels = $kategoriTerlaris->pluck('nama_kategori');
        $kategoriData = $kategoriTerlaris->pluck('total_terjual');

        // 🔹 Pelanggan setia (Top 5 berdasarkan jumlah transaksi)
        $pelangganSetia = Penjualans::join('tbl_pelanggans', 'tbl_penjualans.pelanggan_id', '=', 'tbl_pelanggans.pelanggan_id')
            ->select(
                'tbl_pelanggans.nama_pelanggan',
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(tbl_penjualans.total_harga) as total_belanja')
            )
            ->groupBy('tbl_pelanggans.nama_pelanggan')
            ->orderByDesc('total_transaksi')
            ->limit(5)
            ->get();

        // 🔹 Transaksi terbaru (5 terakhir)
        $transaksiTerbaru = Penjualans::with('pelanggan', 'user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 🔹 Performa Kasir (berdasarkan tahun)
        $performaKasir = Penjualans::join('users', 'tbl_penjualans.user_id', '=', 'users.id')
            ->whereYear('tbl_penjualans.created_at', $tahun)
            ->select(
                'users.name',
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(tbl_penjualans.total_harga) as total_pendapatan')
            )
            ->groupBy('users.name')
            ->orderByDesc('total_transaksi')
            ->get();

        $kasirLabels = $performaKasir->pluck('name');
        $kasirData = $performaKasir->pluck('total_transaksi');

        // 🔹 Nilai total inventory
        $nilaiInventory = Produks::select(DB::raw('SUM(stok * harga_jual) as total'))->value('total') ?? 0;

        // 🔹 Produk slow moving (tidak terjual dalam 30 hari terakhir)
        $produkSlowMoving = Produks::whereNotIn('produk_id', function($query) {
                $query->select('produk_id')
                    ->from('tbl_detail_penjualans')
                    ->join('tbl_penjualans', 'tbl_detail_penjualans.penjualan_id', '=', 'tbl_penjualans.penjualan_id')
                    ->where('tbl_penjualans.created_at', '>=', Carbon::now()->subDays(30));
            })
            ->where('stok', '>', 0)
            ->limit(10)
            ->get();

        // 🔹 Perbandingan bulan ini vs bulan lalu
        $bulanIni = Penjualans::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('total_harga');

        $bulanLalu = Penjualans::whereMonth('created_at', date('m', strtotime('-1 month')))
            ->whereYear('created_at', date('Y', strtotime('-1 month')))
            ->sum('total_harga');

        $pertumbuhanBulanan = $bulanLalu > 0 ? round((($bulanIni - $bulanLalu) / $bulanLalu) * 100, 2) : 0;

        // 🔹 Tambahan info rata-rata penjualan & profit
        $rataPenjualan = $totalPenjualan > 0 ? round($totalPenjualan / 12, 2) : 0;
        $rataProfit    = $totalProfit > 0 ? round($totalProfit / 12, 2) : 0;

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
            'rataPenjualan',
            'rataProfit'
        ));
    }
}
