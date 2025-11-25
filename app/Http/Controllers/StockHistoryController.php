<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockHistory;
use App\Models\Produks;
use App\Models\Kategoris;
use Barryvdh\DomPDF\Facade\Pdf; // ✅ Import library DomPDF untuk generate PDF

/**
 * StockHistoryController
 * Controller untuk mengelola riwayat pergerakan stok produk
 * Fitur: Tracking semua perubahan stok (masuk/keluar), filter, dan export PDF
 * Tujuan: Audit trail dan monitoring stok untuk analisis bisnis
 */
class StockHistoryController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar riwayat stok dengan filter dan pagination
     * Flow:
     * 1. Query stock history dengan eager loading (produk, kategori, satuan, user)
     * 2. Apply filter berdasarkan parameter:
     *    - produk_id: Filter berdasarkan produk tertentu
     *    - tipe: Filter berdasarkan tipe (masuk/keluar)
     *    - kategori_id: Filter berdasarkan kategori produk
     *    - tanggal_dari: Filter tanggal awal
     *    - tanggal_sampai: Filter tanggal akhir
     * 3. Urutkan dari yang terbaru (latest)
     * 4. Pagination 50 record per halaman
     * 5. Ambil data produk dan kategori untuk dropdown filter
     * 6. Kirim data ke view
     *
     * Parameter: Request $request (berisi parameter filter dari form)
     * Return: View dengan daftar riwayat stok dan data filter
     * Use case: Admin monitoring pergerakan stok harian/bulanan
     * Note: Pagination untuk performa saat data banyak
     */
    public function index(Request $request)
    {
        // Query stock history dengan eager loading relasi
        $query = StockHistory::with(['produk.kategori', 'produk.satuan', 'user']);

        // Filter berdasarkan produk tertentu
        // Use case: Lihat riwayat stok untuk "Indomie Goreng" saja
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        // Filter berdasarkan tipe (masuk/keluar)
        // Use case: Lihat hanya stok yang masuk (pembelian) atau keluar (penjualan)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan kategori produk
        // Use case: Lihat riwayat stok untuk kategori "Makanan" saja
        // whereHas: filter berdasarkan relasi (cek produk.kategori_id)
        if ($request->filled('kategori_id')) {
            $query->whereHas('produk', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan tanggal awal
        // Use case: Lihat riwayat stok mulai dari 1 Januari 2025
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        // Filter berdasarkan tanggal akhir
        // Use case: Lihat riwayat stok sampai 31 Desember 2025
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // Eksekusi query, urutkan terbaru, pagination 50 per halaman
        $histories = $query->latest()->paginate(50);

        // Data untuk filter dropdown
        // Ambil produk unik (distinct nama_produk) untuk dropdown filter
        $produks = Produks::select('produk_id', 'nama_produk', 'barcode')
            ->distinct('nama_produk')
            ->orderBy('nama_produk')
            ->get();

        // Ambil semua kategori untuk dropdown filter
        $kategoris = Kategoris::all();

        // Kirim data ke view stock history
        return view('stock-history.index', compact('histories', 'produks', 'kategoris'));
    }

    /**
     * show()
     * Fungsi: Menampilkan detail riwayat stok untuk 1 produk tertentu
     * Flow:
     * 1. Cari produk berdasarkan ID dengan eager loading lengkap:
     *    - kategori, satuan, supplier (info produk)
     *    - batches (batch tracking)
     * 2. Query riwayat stok untuk produk ini saja
     * 3. Eager load user (yang melakukan perubahan stok)
     * 4. Urutkan dari yang terbaru, pagination 30 per halaman
     * 5. Kirim data produk dan riwayatnya ke view
     *
     * Parameter: $produkId (ID produk yang akan dilihat riwayatnya)
     * Return: View detail riwayat stok 1 produk
     * Use case: Lihat semua pergerakan stok untuk produk "Indomie Goreng"
     * Note: Pagination 30 untuk detail 1 produk (lebih kecil dari index)
     */
    public function show($produkId)
    {
        // Cari produk dengan eager loading relasi lengkap
        $produk = Produks::with(['kategori', 'satuan', 'supplier', 'batches'])->findOrFail($produkId);

        // Query riwayat stok untuk produk ini saja
        $histories = StockHistory::where('produk_id', $produkId)
            ->with('user') // Eager load user yang melakukan perubahan
            ->latest() // Urutkan dari yang terbaru
            ->paginate(30); // Pagination 30 per halaman

        // Kirim data produk dan riwayatnya ke view detail
        return view('stock-history.show', compact('produk', 'histories'));
    }

    /**
     * downloadPdf()
     * Fungsi: Generate dan download laporan riwayat stok dalam format PDF
     * Flow:
     * 1. Query stock history dengan eager loading
     * 2. Apply filter yang SAMA dengan index():
     *    - produk_id, tipe, kategori_id
     *    - tanggal_dari, tanggal_sampai
     * 3. Get ALL data (tanpa pagination) untuk PDF
     * 4. Buat array filterInfo untuk info filter di PDF:
     *    - Tampilkan nama produk, tipe, kategori yang difilter
     *    - Tampilkan range tanggal
     * 5. Generate PDF dari view dengan data histories & filterInfo
     * 6. Set ukuran kertas A4 landscape (untuk kolom banyak)
     * 7. Stream PDF ke browser dengan nama file dinamis (timestamp)
     *
     * Parameter: Request $request (berisi parameter filter dari form)
     * Return: PDF stream untuk download
     * Use case: Export laporan stok bulanan untuk analisis/arsip
     * Note: Landscape untuk muat kolom banyak (produk, tipe, jumlah, stok, user, dll)
     */
    public function downloadPdf(Request $request)
    {
        // Query stock history dengan eager loading (sama seperti index)
        $query = StockHistory::with(['produk.kategori', 'produk.satuan', 'user']);

        // Filter berdasarkan produk (sama seperti index)
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        // Filter berdasarkan tipe (sama seperti index)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan kategori (sama seperti index)
        if ($request->filled('kategori_id')) {
            $query->whereHas('produk', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan tanggal awal (sama seperti index)
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        // Filter berdasarkan tanggal akhir (sama seperti index)
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // Eksekusi query, ambil SEMUA data (tanpa pagination)
        $histories = $query->latest()->get();

        // Info filter untuk ditampilkan di PDF header
        // Memberikan context tentang filter apa yang digunakan
        $filterInfo = [
            // Tampilkan nama produk jika difilter, atau "Semua Produk"
            'produk' => $request->produk_id ? Produks::find($request->produk_id)->nama_produk : 'Semua Produk',
            // Tampilkan tipe jika difilter (Masuk/Keluar), atau "Semua Tipe"
            'tipe' => $request->tipe ? ucfirst($request->tipe) : 'Semua Tipe',
            // Tampilkan nama kategori jika difilter, atau "Semua Kategori"
            'kategori' => $request->kategori_id ? Kategoris::find($request->kategori_id)->nama_kategori : 'Semua Kategori',
            // Tampilkan range tanggal atau "-" jika tidak difilter
            'tanggal_dari' => $request->tanggal_dari ?? '-',
            'tanggal_sampai' => $request->tanggal_sampai ?? '-',
        ];

        // ✅ Generate PDF dari view stock-history.pdf
        // Konsisten dengan ProdukController (pakai setPaper)
        $pdf = Pdf::loadView('stock-history.pdf', compact('histories', 'filterInfo'))
            ->setPaper('a4', 'landscape'); // A4 landscape untuk kolom banyak

        // Stream PDF dengan nama file dinamis: history-stock-2025-01-15-143022.pdf
        return $pdf->stream('history-stock-' . date('Y-m-d-His') . '.pdf');
    }
}
