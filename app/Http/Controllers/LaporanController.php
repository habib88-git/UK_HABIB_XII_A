<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualans;
use Barryvdh\DomPDF\Facade\Pdf; // pastikan barryvdh/laravel-dompdf sudah terinstall

class LaporanController extends Controller
{
    /**
     * Tampilkan daftar laporan penjualan.
     */
    public function index(Request $request)
    {
        $query = Penjualans::with(['pelanggan', 'user', 'pembayaran', 'detailPenjualans.produk'])
            ->latest();

        // filter tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal_penjualan', [$request->start_date, $request->end_date]);
        }

        $penjualans = $query->get();

        return view('laporan.index', compact('penjualans'));
    }

    /**
     * Cetak laporan ke PDF
     */
    public function cetakPdf(Request $request)
    {
        $query = Penjualans::with(['pelanggan', 'user', 'pembayaran', 'detailPenjualans.produk'])
            ->latest();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal_penjualan', [$request->start_date, $request->end_date]);
        }

        $penjualans = $query->get();

        $pdf = Pdf::loadView('laporan.pdf', compact('penjualans'));

        return $pdf->stream('laporan-penjualan.pdf');
    }


    public function struk($id)
    {
        $penjualan = Penjualans::with(['pelanggan', 'user', 'detailPenjualans.produk', 'pembayaran'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('laporan.struk', compact('penjualan'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');
        // ukuran kertas 80mm (cocok untuk struk printer thermal)

        return $pdf->stream('struk-penjualan-' . $penjualan->penjualan_id . '.pdf');
    }
}
