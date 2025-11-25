<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Produks;
use App\Models\Kategoris;
use App\Models\Satuans;
use App\Models\Suppliers;
use App\Models\StockHistory;
use App\Models\BatchProduk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ProdukController
 * Controller untuk mengelola data produk master
 * Fitur: CRUD produk, manajemen batch otomatis, cetak barcode
 * Sistem: Stok produk master dihitung otomatis dari sum batch (FEFO)
 */
class ProdukController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua produk beserta informasinya
     * Flow:
     * 1. Query semua produk dengan eager loading relasi:
     *    - kategori, satuan, supplier (data master)
     *    - batches (untuk tracking stok per batch)
     * 2. Kirim data ke view
     *
     * Return: View daftar produk
     * Note: Eager loading untuk optimasi query dan menampilkan info lengkap
     */
    public function index()
    {
        // Ambil semua produk dengan relasi kategori, satuan, supplier, dan batch
        $produks = Produks::with(['kategori', 'satuan', 'supplier', 'batches'])->get();

        return view('produk.index', compact('produks'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form tambah produk baru
     * Flow:
     * 1. Ambil data master untuk dropdown:
     *    - kategoris (kategori produk)
     *    - satuans (satuan produk: pcs, box, kg, dll)
     *    - suppliers (supplier produk)
     * 2. Kirim data ke view form
     *
     * Return: View form create dengan data dropdown
     * Note: User akan input barcode, nama, harga, stok awal, kadaluwarsa
     */
    public function create()
    {
        return view('produk.create', [
            'kategoris' => Kategoris::all(),  // Data kategori untuk dropdown
            'satuans'   => Satuans::all(),    // Data satuan untuk dropdown
            'suppliers' => Suppliers::all(),  // Data supplier untuk dropdown
        ]);
    }

    /**
     * store()
     * Fungsi: Menyimpan produk baru beserta batch pertama (stok awal)
     * Flow:
     * 1. Validasi input:
     *    - barcode, nama_produk, photo (opsional)
     *    - harga_beli, harga_jual
     *    - stok awal, kadaluwarsa
     *    - kategori_id, satuan_id, supplier_id
     * 2. Set stok master = 0 (akan dihitung dari batch)
     * 3. Upload photo jika ada
     * 4. Simpan produk master
     * 5. Jika stok > 0, buat batch pertama otomatis:
     *    - Barcode batch = barcode master
     *    - Stok dari input user
     *    - Kadaluwarsa dari input
     * 6. Update stok master dari batch
     * 7. Catat stock history (stok awal)
     * 8. Commit transaction
     *
     * Parameter: Request $request (data form produk)
     * Return: Redirect ke produk.index dengan flash message
     * Note: Batch pertama otomatis dibuat sebagai "stok awal"
     */
    public function store(Request $request)
    {
        // Validasi input form produk
        $request->validate([
            'barcode'     => 'required|string|max:50',
            'nama_produk' => 'required|string|max:100',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'harga_beli'  => 'required|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|integer|min:0',
            'kadaluwarsa' => 'required|date',
            'kategori_id' => 'required|exists:tbl_kategoris,kategori_id',
            'satuan_id'   => 'required|exists:tbl_satuans,satuan_id',
            'supplier_id' => 'required|exists:tbl_suppliers,supplier_id',
        ]);

        DB::beginTransaction();
        try {
            // Ambil data kecuali photo, stok, kadaluwarsa (dihandle terpisah)
            $data = $request->except(['photo', 'stok', 'kadaluwarsa']); // ❌ Jangan ambil stok & kadaluwarsa

            // Upload photo jika ada file
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
            }

            // ✅ SET STOK = 0 DULU (nanti dihitung dari batch)
            // Stok master selalu 0 saat create, nanti di-update dari sum batch
            $data['stok'] = 0;
            $data['kadaluwarsa'] = $request->kadaluwarsa; // Simpan sebagai referensi template

            // Simpan produk master
            $produk = Produks::create($data);

            // ✅ BIKIN BATCH OTOMATIS kalau input stok > 0
            if ($request->stok > 0) {
                // Buat batch pertama (stok awal)
                $batch = BatchProduk::create([
                    'produk_id' => $produk->produk_id,
                    'barcode_batch' => $request->barcode, // Pakai barcode master sebagai barcode batch pertama
                    'stok' => $request->stok,
                    'kadaluwarsa' => $request->kadaluwarsa,
                    'harga_beli' => $request->harga_beli,
                    'pembelian_id' => null, // Stok awal (bukan dari pembelian)
                ]);

                // ✅ UPDATE STOK MASTER dari batch
                // Method model: sum semua batch.stok untuk produk ini
                $produk->updateStokFromBatch();

                // ✅ CATAT STOCK HISTORY (tipe: masuk, stok awal)
                StockHistory::create([
                    'produk_id' => $produk->produk_id,
                    'tipe' => 'masuk',
                    'jumlah' => $request->stok,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $request->stok,
                    'keterangan' => 'Stok awal produk baru - Batch: ' . $batch->barcode_batch,
                    'referensi_tipe' => 'manual', // Bukan dari pembelian/penjualan
                    'referensi_id' => $batch->batch_id,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * edit()
     * Fungsi: Menampilkan form edit produk
     * Flow:
     * 1. Cari produk berdasarkan ID dengan batches
     * 2. Ambil data master (kategori, satuan, supplier) untuk dropdown
     * 3. Kirim data ke view edit
     *
     * Parameter: string $id (ID produk yang akan diedit)
     * Return: View form edit dengan data produk dan dropdown
     * Note: Stok tidak bisa diedit langsung, harus lewat batch management
     */
    public function edit(string $id)
    {
        // Cari produk dengan batches
        $produk = Produks::with('batches')->findOrFail($id);

        return view('produk.edit', [
            'produk'     => $produk,
            'kategoris'  => Kategoris::all(),
            'satuans'    => Satuans::all(),
            'suppliers'  => Suppliers::all(),
        ]);
    }

    /**
     * update()
     * Fungsi: Update data produk master (TANPA ubah stok)
     * Flow:
     * 1. Cari produk berdasarkan ID
     * 2. Validasi input (sama seperti store, tapi tanpa stok & kadaluwarsa)
     * 3. Update data produk (kecuali stok & kadaluwarsa)
     * 4. Upload photo baru jika ada
     * 5. Update stok master dari batch (sinkronisasi)
     * 6. Commit transaction
     *
     * Parameter:
     * - Request $request (data form edit)
     * - string $id (ID produk yang diupdate)
     * Return: Redirect ke produk.index dengan flash message
     * Note: Stok dan kadaluwarsa TIDAK bisa diedit di sini
     *       Stok dikelola lewat batch (pembelian/penjualan)
     *       Update hanya untuk data master (nama, harga, kategori, dll)
     */
    public function update(Request $request, string $id)
    {
        // Cari produk yang akan diupdate
        $produk = Produks::findOrFail($id);

        // Validasi input (tanpa stok & kadaluwarsa)
        $request->validate([
            'barcode'     => 'required|string|max:50',
            'nama_produk' => 'required|string|max:100',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'harga_beli'  => 'required|numeric',
            'harga_jual'  => 'required|numeric',
            'kategori_id' => 'required|exists:tbl_kategoris,kategori_id',
            'satuan_id'   => 'required|exists:tbl_satuans,satuan_id',
            'supplier_id' => 'required|exists:tbl_suppliers,supplier_id',
        ]);

        DB::beginTransaction();
        try {
            // ❌ JANGAN ambil 'stok' dan 'kadaluwarsa' dari request (dikelola lewat batch)
            // Ambil semua data kecuali photo, stok, kadaluwarsa
            $data = $request->except(['photo', 'stok', 'kadaluwarsa']);

            // Upload photo baru jika ada
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
            }

            // Update data produk master
            $produk->update($data);

            // ✅ UPDATE STOK MASTER dari batch (biar selalu sinkron)
            // Ini untuk memastikan stok master sesuai dengan sum batch
            $produk->updateStokFromBatch();

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui. Stok dihitung otomatis dari batch.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * destroy()
     * Fungsi: Hapus produk beserta semua batchnya
     * Flow:
     * 1. Cari produk dengan batches
     * 2. Loop setiap batch:
     *    - Catat stock history (keluar) jika batch masih punya stok
     *    - Hapus batch
     * 3. Hapus produk master
     * 4. Commit transaction
     *
     * Parameter: string $id (ID produk yang akan dihapus)
     * Return: Redirect ke produk.index dengan flash message
     * Warning: Hati-hati jika produk masih ada di transaksi penjualan
     * Note: Semua batch akan dihapus dan dicatat di stock history
     */
    public function destroy(string $id)
    {
        // Cari produk dengan batches
        $produk = Produks::with('batches')->findOrFail($id);

        DB::beginTransaction();
        try {
            // ✅ CATAT HISTORY & HAPUS SEMUA BATCH
            foreach ($produk->batches as $batch) {
                // Jika batch masih punya stok, catat history
                if ($batch->stok > 0) {
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar',
                        'jumlah' => $batch->stok,
                        'stok_sebelum' => $batch->stok,
                        'stok_sesudah' => 0,
                        'keterangan' => 'Produk dihapus: ' . $produk->nama_produk . ' - Batch: ' . $batch->barcode_batch,
                        'referensi_tipe' => 'hapus_produk',
                        'referensi_id' => $produk->produk_id,
                        'user_id' => Auth::id() ?? 1,
                    ]);
                }

                // Hapus batch
                $batch->delete();
            }

            // Hapus produk master
            $produk->delete();

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk dan semua batch berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // === CETAK BARCODE ===

    /**
     * cetakBarcode()
     * Fungsi: Cetak barcode untuk 1 produk tertentu
     * Flow:
     * 1. Cari produk berdasarkan ID
     * 2. Generate PDF barcode dari view
     * 3. Set ukuran kertas A7 portrait (ukuran label barcode)
     * 4. Stream PDF ke browser
     *
     * Parameter: $id (ID produk yang akan dicetak barcodenya)
     * Return: PDF stream barcode 1 produk
     * Use case: Cetak label barcode untuk ditempel di produk
     * Note: Ukuran A7 (74x105mm) cocok untuk label barcode
     */
    public function cetakBarcode($id)
    {
        // Cari produk berdasarkan ID
        $produk = Produks::findOrFail($id);

        // Generate PDF barcode dengan ukuran A7 portrait
        $pdf = Pdf::loadView('produk.barcode', compact('produk'))->setPaper('a7', 'portrait');

        // Stream PDF dengan nama file dinamis
        return $pdf->stream('barcode-' . $produk->nama_produk . '.pdf');
    }

    /**
     * cetakSemuaBarcode()
     * Fungsi: Cetak barcode untuk SEMUA produk sekaligus
     * Flow:
     * 1. Ambil semua produk
     * 2. Cek apakah ada produk, jika tidak ada return error
     * 3. Generate PDF barcode dari view (multiple barcode)
     * 4. Set ukuran kertas A4 portrait (muat banyak barcode)
     * 5. Stream PDF ke browser
     *
     * Return: PDF stream barcode semua produk atau redirect error
     * Use case: Cetak barcode massal untuk semua produk
     * Note: Ukuran A4 untuk muat banyak barcode dalam 1 kertas
     */
    public function cetakSemuaBarcode()
    {
        // Ambil semua produk
        $produks = Produks::all();

        // Validasi: cek apakah ada produk
        if ($produks->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada produk untuk dicetak.');
        }

        // Generate PDF barcode semua produk dengan ukuran A4
        $pdf = Pdf::loadView('produk.barcode-semua', compact('produks'))->setPaper('a4', 'portrait');

        // Stream PDF
        return $pdf->stream('semua-barcode-produk.pdf');
    }
}
