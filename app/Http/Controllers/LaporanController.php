<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualans;
use App\Models\Pelanggans;
use App\Models\Users;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * LaporanController
 * Controller untuk mengelola laporan penjualan
 * Fitur: Filter laporan, cetak PDF, dan cetak struk thermal
 */
class LaporanController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan halaman laporan penjualan dengan filter
     * Flow:
     * 1. Query data penjualan dengan relasi (pelanggan, pembayaran, user/kasir)
     * 2. Apply filter berdasarkan parameter:
     *    - start_date: Filter tanggal awal
     *    - end_date: Filter tanggal akhir
     *    - pelanggan_id: Filter berdasarkan pelanggan tertentu
     *    - kasir_id: Filter berdasarkan kasir/user tertentu
     * 3. Urutkan data dari yang terbaru
     * 4. Ambil data pelanggan & kasir untuk dropdown filter
     * 5. Kirim semua data ke view
     *
     * Parameter: Request $request (berisi parameter filter dari form)
     * Return: View laporan.index dengan data penjualan, pelanggan, dan kasir
     */
    public function index(Request $request)
    {
        // Query penjualan dengan eager loading relasi untuk optimasi
        $query = Penjualans::with(['pelanggan', 'pembayaran', 'user']);

        // Filter tanggal awal - cari penjualan >= tanggal yang dipilih
        if ($request->start_date) {
            $query->whereDate('tanggal_penjualan', '>=', $request->start_date);
        }

        // Filter tanggal akhir - cari penjualan <= tanggal yang dipilih
        if ($request->end_date) {
            $query->whereDate('tanggal_penjualan', '<=', $request->end_date);
        }

        // Filter berdasarkan pelanggan tertentu
        if ($request->pelanggan_id) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        // 🔹 Filter berdasarkan kasir/user yang melakukan transaksi
        if ($request->kasir_id) {
            $query->where('user_id', $request->kasir_id);
        }

        // Eksekusi query dan urutkan dari tanggal terbaru
        $penjualans = $query->orderBy('tanggal_penjualan', 'desc')->get();

        // Ambil semua pelanggan untuk dropdown filter (urutkan by nama)
        $pelanggans = Pelanggans::orderBy('nama_pelanggan')->get();

        // 🔹 Ambil semua user dengan role kasir untuk dropdown filter
        $kasirs = Users::where('role', 'kasir')->orderBy('name')->get();

        // Kirim data ke view laporan
        return view('laporan.index', compact('penjualans', 'pelanggans', 'kasirs'));
    }

    /**
     * cetakPdf()
     * Fungsi: Generate dan download laporan penjualan dalam format PDF
     * Flow:
     * 1. Query penjualan dengan relasi lengkap (detail produk, pelanggan, kasir, pembayaran)
     * 2. Apply filter yang sama seperti index():
     *    - Range tanggal (start_date & end_date)
     *    - Pelanggan tertentu
     *    - Kasir tertentu
     * 3. Generate PDF menggunakan DomPDF library
     * 4. Set ukuran kertas A4 portrait
     * 5. Stream PDF ke browser (bisa langsung dilihat atau didownload)
     *
     * Parameter: Request $request (berisi parameter filter)
     * Return: PDF stream untuk didownload
     */
    public function cetakPdf(Request $request)
    {
        // Query penjualan dengan eager loading relasi lengkap + detail produk
        $query = Penjualans::with(['pelanggan', 'user', 'pembayaran', 'detailPenjualans.produk'])
            ->latest(); // latest() = order by created_at DESC

        // Filter berdasarkan range tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            // Jika ada kedua tanggal, gunakan whereBetween untuk range
            $query->whereBetween('tanggal_penjualan', [$request->start_date, $request->end_date]);
        } elseif ($request->has('start_date')) {
            // Jika hanya start_date, cari >= tanggal tersebut
            $query->whereDate('tanggal_penjualan', '>=', $request->start_date);
        } elseif ($request->has('end_date')) {
            // Jika hanya end_date, cari <= tanggal tersebut
            $query->whereDate('tanggal_penjualan', '<=', $request->end_date);
        }

        // 🔹 Filter pelanggan - sama seperti di index
        if ($request->pelanggan_id) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        // 🔹 Filter kasir - sama seperti di index
        if ($request->kasir_id) {
            $query->where('user_id', $request->kasir_id);
        }

        // Eksekusi query
        $penjualans = $query->get();

        // Generate PDF dari view laporan.pdf dengan data penjualan
        $pdf = Pdf::loadView('laporan.pdf', compact('penjualans'))
            ->setPaper('a4', 'portrait'); // Ukuran A4 orientasi portrait

        // Stream PDF ke browser dengan nama file
        return $pdf->stream('laporan-penjualan.pdf');
    }

    /**
     * struk()
     * Fungsi: Cetak struk thermal untuk 1 transaksi penjualan tertentu
     * Flow:
     * 1. Cari data penjualan berdasarkan ID dengan semua relasi
     * 2. Generate PDF dengan ukuran kertas thermal (80mm lebar)
     * 3. Stream PDF struk ke browser
     *
     * Parameter: $id (ID penjualan yang akan dicetak struknya)
     * Return: PDF stream struk thermal
     * Use case: Cetak struk kasir setelah transaksi selesai
     */
    public function struk($id)
    {
        // Cari penjualan berdasarkan ID dengan eager loading relasi lengkap
        $penjualan = Penjualans::with(['pelanggan', 'user', 'detailPenjualans.produk', 'pembayaran'])
            ->findOrFail($id); // Throw 404 jika tidak ditemukan

        // Generate PDF dari view laporan.struk
        $pdf = Pdf::loadView('laporan.struk', compact('penjualan'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');
            // Custom paper size: 80mm width (226.77 points) x 600 points height
            // 1mm = 2.83465 points, jadi 80mm = 226.77 points

        // Stream PDF struk dengan nama file dinamis berdasarkan ID penjualan
        return $pdf->stream('struk-penjualan-' . $penjualan->penjualan_id . '.pdf');
    }
}
