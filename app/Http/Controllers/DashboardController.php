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
        $totalPenjualan = Penjualans::count();

        // Hitung total profit
        $totalProfit = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->select(DB::raw('SUM(((tbl_detail_penjualans.subtotal / tbl_detail_penjualans.jumlah_produk) - tbl_produks.harga_beli) * tbl_detail_penjualans.jumlah_produk) as profit'))
            ->value('profit') ?? 0;

        // Ambil data penjualan per bulan sesuai tahun
        $penjualan = Penjualans::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('total','bulan')
            ->toArray();

        // Ambil data profit per bulan sesuai tahun
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

        // Siapkan data grafik
        $labels = [];
        $data   = [];
        $dataProfit = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date("F", mktime(0,0,0,$i,1));
            $data[]   = $penjualan[$i] ?? 0;
            $dataProfit[] = $profitPerBulan[$i] ?? 0;
        }

        // Ambil produk dengan stok minimum (<= 25)
        $stokMinimum = Produks::where('stok', '<=', 500)->get();

        // 🔹 Produk terlaris (Top 5)
        $produkTerlaris = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
            ->select('tbl_produks.nama_produk', DB::raw('SUM(tbl_detail_penjualans.jumlah_produk) as total_terjual'))
            ->groupBy('tbl_produks.nama_produk')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $produkLabels = $produkTerlaris->pluck('nama_produk');
        $produkData   = $produkTerlaris->pluck('total_terjual');

        return view('dashboard.admin', compact(
            'totalUser',
            'totalKategori',
            'totalProduk',
            'totalPelanggan',
            'totalPenjualan',
            'totalProfit',
            'labels',
            'data',
            'dataProfit',
            'tahun',
            'stokMinimum',
            'produkLabels',
            'produkData'
        ));
    }

    public function kasirdashboard()
{
    $tanggalHariIni = Carbon::today();

    $totalTransaksi = Penjualans::whereDate('tanggal_penjualan', $tanggalHariIni)->count();
    $totalPendapatan = Penjualans::whereDate('tanggal_penjualan', $tanggalHariIni)->sum('total_harga');

    $produkTerjual = DetailPenjualans::join('tbl_produks', 'tbl_detail_penjualans.produk_id', '=', 'tbl_produks.produk_id')
        ->select('tbl_produks.nama_produk', DB::raw('SUM(tbl_detail_penjualans.jumlah_produk) as total'))
        ->whereDate('tbl_detail_penjualans.created_at', $tanggalHariIni)
        ->groupBy('tbl_produks.nama_produk')
        ->orderByDesc('total')
        ->limit(5)
        ->get();

    $transaksiTerbaru = Penjualans::with('pelanggan')
        ->orderByDesc('tanggal_penjualan')
        ->limit(5)
        ->get();

    $stokMenipis = Produks::where('stok', '<', 10)
        ->orderBy('stok', 'asc')
        ->limit(5)
        ->get();

    $produkLabels = $produkTerjual->pluck('nama_produk');
    $produkData   = $produkTerjual->pluck('total');

    return view('dashboard.kasir', compact(
        'totalTransaksi',
        'totalPendapatan',
        'produkLabels',
        'produkData',
        'tanggalHariIni',
        'transaksiTerbaru',
        'stokMenipis'
    ));
}

}
