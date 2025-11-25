<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelians;
use App\Models\DetailPembelians;
use App\Models\Produks;
use App\Models\Suppliers;
use App\Models\StockHistory;
use App\Models\BatchProduk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * PembelianController
 * Controller untuk mengelola transaksi pembelian produk dari supplier
 * Fitur: CRUD pembelian, manajemen batch produk, tracking stok, cetak PDF
 * Sistem: FEFO (First Expired First Out) dengan batch tracking
 */
class PembelianController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua transaksi pembelian
     * Flow:
     * 1. Query semua pembelian dengan eager loading (supplier, user)
     * 2. Urutkan dari yang terbaru (latest)
     * 3. Kirim data ke view
     *
     * Return: View daftar pembelian
     * Note: Eager loading untuk optimasi query (hindari N+1 problem)
     */
    public function index()
    {
        // Ambil semua pembelian dengan relasi supplier & user, urutkan terbaru
        $pembelians = Pembelians::with(['supplier', 'user'])->latest()->get();

        return view('pembelian.index', compact('pembelians'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form input pembelian baru
     * Flow:
     * 1. Ambil semua data supplier untuk dropdown
     * 2. Ambil produk UNIK (1 produk master per nama)
     *    - Menghindari duplikasi produk yang sama dengan batch berbeda
     *    - Group by nama_produk untuk ambil 1 template saja
     * 3. Kirim data ke view form pembelian
     *
     * Return: View form pembelian dengan data supplier & produk
     * Note: GROUP BY untuk ambil produk master saja (tanpa duplikasi batch)
     */
    public function create()
    {
        // Ambil semua supplier untuk dropdown
        $suppliers = Suppliers::all();

        // ✅ Ambil produk unik (1 produk master per nama)
        // GROUP BY nama_produk untuk hindari duplikasi
        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->selectRaw('MAX(produk_id) as produk_id') // Ambil ID tertinggi sebagai template
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.create', compact('suppliers', 'produks'));
    }

    /**
     * store()
     * Fungsi: Menyimpan transaksi pembelian baru beserta batch produk
     * Flow:
     * 1. Parse harga_beli dari format Rupiah (1.000.000) ke float
     * 2. Normalisasi supplier_id (handle jika array dari form)
     * 3. Validasi semua input:
     *    - tanggal, supplier, produk, jumlah, harga_beli, kadaluwarsa
     * 4. Hitung total pembelian
     * 5. Simpan data pembelian master
     * 6. Process setiap item pembelian (buat batch, update stok, catat history)
     * 7. Commit transaction jika sukses
     *
     * Parameter: Request $request (data form pembelian)
     * Return: Redirect ke pembelian.index dengan flash message
     * Note: Menggunakan DB transaction untuk data integrity
     */
    public function store(Request $request)
{
    // PARSE HARGA BELI SEBELUM VALIDASI (Fix format Rupiah)
    // Convert "1.000.000" atau "1.000.000,50" menjadi float
    $hargaBeliParsed = [];
    foreach ($request->harga_beli as $harga) {
        $hargaBeliParsed[] = (float) str_replace(['.', ','], ['', '.'], $harga);
    }
    $request->merge(['harga_beli' => $hargaBeliParsed]);

    // NORMALISASI supplier_id (supaya tidak array)
    // Handle jika supplier_id dari form berupa array
    $supplierId = null;
    if ($request->has('supplier_id')) {
        $s = $request->input('supplier_id');
        if (is_array($s)) {
            // Ambil nilai pertama yang tidak null/kosong
            foreach ($s as $val) {
                if ($val !== null && $val !== '') {
                    $supplierId = $val;
                    break;
                }
            }
        } else {
            $supplierId = $s;
        }
    }
    $request->merge(['supplier_id' => $supplierId]);

    // VALIDASI INPUT
    $request->validate([
        'tanggal' => 'required|date',
        'supplier_id' => 'nullable|exists:tbl_suppliers,supplier_id',
        'produk_id.*' => 'required|exists:tbl_produks,produk_id',
        'jumlah.*' => 'required|integer|min:1',
        'harga_beli.*' => 'required|numeric|min:0',
        'kadaluwarsa.*' => 'required|date',
    ]);

    // Mulai database transaction
    DB::beginTransaction();

    try {
        // Hitung total pembelian (sum semua subtotal)
        $total = 0;
        foreach ($request->jumlah as $i => $qty) {
            $total += $qty * $request->harga_beli[$i];
        }

        // Create pembelian master (header pembelian)
        $pembelian = Pembelians::create([
            'tanggal' => $request->tanggal,
            'supplier_id' => $supplierId ? (int) $supplierId : null,
            'user_id' => Auth::id() ?? 1, // ID user yang login
            'total_harga' => $total,
        ]);

        // PROSES ITEM (simpan detail pembelian, batch, stok, history)
        $this->processItemsPembelian($request, $pembelian);

        // Commit jika semua sukses
        DB::commit();
        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan!');

    } catch (\Exception $e) {
        // Rollback jika ada error
        DB::rollback();
        return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
    }
}

    /**
     * show()
     * Fungsi: Menampilkan detail transaksi pembelian tertentu
     * Flow:
     * 1. Cari pembelian berdasarkan ID dengan eager loading lengkap:
     *    - supplier, user (yang melakukan pembelian)
     *    - details (item pembelian)
     *    - produk per detail (dengan kategori, satuan)
     *    - batch per detail
     * 2. Kirim data ke view detail
     *
     * Parameter: string $id (ID pembelian)
     * Return: View detail pembelian
     * Note: Eager loading nested untuk load semua relasi sekaligus
     */
    public function show(string $id)
    {
        // Cari pembelian dengan eager loading relasi lengkap
        $pembelian = Pembelians::with(['supplier', 'user', 'details.produk.kategori', 'details.produk.satuan', 'details.batch'])->findOrFail($id);

        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * edit()
     * Fungsi: Menampilkan form edit pembelian
     * Flow:
     * 1. Cari pembelian berdasarkan ID dengan detail & batch
     * 2. Ambil semua supplier untuk dropdown
     * 3. Ambil produk unik (sama seperti create)
     * 4. Kirim semua data ke view edit
     *
     * Parameter: string $id (ID pembelian yang akan diedit)
     * Return: View form edit dengan data pembelian, supplier, produk
     * Note: Data pembelian lama akan tampil di form untuk diedit
     */
    public function edit(string $id)
    {
        // Cari pembelian dengan detail & batch
        $pembelian = Pembelians::with(['details.produk', 'details.batch'])->findOrFail($id);

        // Ambil semua supplier
        $suppliers = Suppliers::all();

        // Ambil produk unik (sama seperti create)
        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->selectRaw('MAX(produk_id) as produk_id')
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'produks'));
    }

    /**
     * update()
     * Fungsi: Update data pembelian yang sudah ada
     * Flow:
     * 1. Parse harga_beli dari format Rupiah
     * 2. Normalisasi supplier_id
     * 3. Validasi input (sama seperti store)
     * 4. HAPUS batch lama (kembalikan stok, catat history)
     * 5. Hitung total baru
     * 6. Update data pembelian master
     * 7. BUAT batch baru sesuai data edit
     * 8. Commit transaction
     *
     * Parameter:
     * - Request $request (data form edit)
     * - string $id (ID pembelian yang diupdate)
     * Return: Redirect ke pembelian.index dengan flash message
     * Note: Hapus batch lama dulu, baru buat batch baru (prevent duplikasi)
     */
    public function update(Request $request, string $id)
{
    // Cari pembelian yang akan diupdate
    $pembelian = Pembelians::findOrFail($id);

    // PARSE HARGA BELI SEBELUM VALIDASI (sama seperti store)
    $hargaBeliParsed = [];
    foreach ($request->harga_beli as $harga) {
        $hargaBeliParsed[] = (float) str_replace(['.', ','], ['', '.'], $harga);
    }
    $request->merge(['harga_beli' => $hargaBeliParsed]);

    // NORMALISASI supplier_id (sama seperti store)
    $supplierId = null;
    if ($request->has('supplier_id')) {
        $s = $request->input('supplier_id');
        if (is_array($s)) {
            foreach ($s as $val) {
                if ($val !== null && $val !== '') {
                    $supplierId = $val;
                    break;
                }
            }
        } else {
            $supplierId = $s;
        }
    }
    $request->merge(['supplier_id' => $supplierId]);

    // VALIDASI (sama seperti store)
    $request->validate([
        'tanggal' => 'required|date',
        'supplier_id' => 'nullable|exists:tbl_suppliers,supplier_id',
        'produk_id.*' => 'required|exists:tbl_produks,produk_id',
        'jumlah.*' => 'required|integer|min:1',
        'harga_beli.*' => 'required|numeric|min:0',
        'kadaluwarsa.*' => 'required|date',
    ]);

    DB::beginTransaction();

    try {
        // HAPUS BATCH LAMA (kembalikan stok, catat riwayat)
        $this->deletePembelianBatches($pembelian);

        // Hitung total baru
        $total = 0;
        foreach ($request->produk_id as $key => $produkId) {
            $total += $request->jumlah[$key] * $request->harga_beli[$key];
        }

        // Update pembelian master
        $pembelian->update([
            'tanggal' => $request->tanggal,
            'supplier_id' => $supplierId ? (int) $supplierId : null,
            'total_harga' => $total,
        ]);

        // BUAT BATCH BARU dengan data yang sudah diedit
        $this->processItemsPembelian($request, $pembelian);

        DB::commit();
        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    /**
     * destroy()
     * Fungsi: Hapus transaksi pembelian
     * Flow:
     * 1. Cari pembelian berdasarkan ID
     * 2. Hapus semua batch terkait (kembalikan stok, catat history)
     * 3. Hapus record pembelian master
     * 4. Commit transaction
     *
     * Parameter: string $id (ID pembelian yang akan dihapus)
     * Return: Redirect ke pembelian.index dengan flash message
     * Warning: Hati-hati jika ada penjualan yang sudah menggunakan batch ini
     * Note: Batch akan dihapus dan stok dikembalikan via helper method
     */
    public function destroy(string $id)
    {
        // Cari pembelian yang akan dihapus
        $pembelian = Pembelians::findOrFail($id);

        DB::beginTransaction();

        try {
            // ✅ HAPUS SEMUA BATCH & KEMBALIKAN STOK
            // Helper method akan handle:
            // - Hapus batch
            // - Update stok produk master
            // - Catat stock history
            $this->deletePembelianBatches($pembelian);

            // Hapus record pembelian master
            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * printPdf()
     * Fungsi: Generate dan download laporan pembelian dalam format PDF
     * Flow:
     * 1. Cari pembelian dengan eager loading lengkap (semua relasi)
     * 2. Load view laporan_pembelian dengan data pembelian
     * 3. Set ukuran kertas A4 landscape
     * 4. Generate nama file dengan ID pembelian & timestamp
     * 5. Stream PDF ke browser
     *
     * Parameter: $id (ID pembelian yang akan dicetak)
     * Return: PDF stream untuk download
     * Note: Landscape untuk tampil data yang banyak kolom
     */
    public function printPdf($id)
    {
        // Cari pembelian dengan eager loading relasi lengkap
        $pembelian = Pembelians::with([
            'supplier',
            'user',
            'details.produk.kategori',
            'details.produk.satuan',
            'details.produk.supplier',
            'details.batch'
        ])->findOrFail($id);

        // Generate PDF dari view
        $pdf = Pdf::loadView('laporan.laporan_pembelian', compact('pembelian'));
        $pdf->setPaper('a4', 'landscape'); // A4 landscape untuk data banyak

        // Generate nama file dengan format: Laporan_Pembelian_000001_20250101_123456.pdf
        $filename = 'Laporan_Pembelian_' . str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.pdf';

        // Stream PDF ke browser
        return $pdf->stream($filename);
    }

    // ========================================
    // ✅ HELPER METHODS (Private Functions)
    // ========================================

    /**
     * processItemsPembelian()
     * Fungsi: Process setiap item pembelian (untuk store & update)
     * Flow (untuk setiap produk yang dibeli):
     * 1. Cari/buat produk master (avoid duplikasi dengan cache)
     * 2. Hitung stok sebelum
     * 3. Buat batch baru dengan:
     *    - Barcode sama dengan produk
     *    - Stok, kadaluwarsa, harga_beli
     * 4. Update stok produk master (sum dari semua batch)
     * 5. Simpan detail pembelian (link ke batch)
     * 6. Catat stock history (masuk)
     *
     * Parameter:
     * - Request $request (data form)
     * - Pembelians $pembelian (record pembelian master)
     * Note: Method ini dipanggil dari store() dan update()
     *       Cache produk master untuk hindari duplikasi query
     */
    private function processItemsPembelian(Request $request, Pembelians $pembelian)
    {
        // ✅ CACHE produk master yang sudah di-create (fix duplikasi)
        $produkMasterCache = [];

        // Loop setiap produk yang dibeli
        foreach ($request->produk_id as $i => $produkId) {
            $jumlah = $request->jumlah[$i];
            $hargaBeli = $request->harga_beli[$i]; // ✅ Sudah float dari parse sebelumnya
            $subtotal = $jumlah * $hargaBeli;
            $kadaluwarsa = $request->kadaluwarsa[$i];

            // Ambil produk template (dari form dropdown)
            $produkTemplate = Produks::findOrFail($produkId);
            $barcode = $produkTemplate->barcode;

            // ✅ CEK CACHE DULU (fix duplikasi produk master)
            $namaProduk = $produkTemplate->nama_produk;

            if (isset($produkMasterCache[$namaProduk])) {
                // Produk master sudah ada di cache, pakai yang itu
                $produkMaster = $produkMasterCache[$namaProduk];
            } else {
                // ✅ CARI ATAU BUAT PRODUK MASTER
                // firstOrCreate: cari dulu by nama_produk, kalau tidak ada buat baru
                $produkMaster = Produks::firstOrCreate(
                    ['nama_produk' => $namaProduk], // Kondisi pencarian
                    [ // Data jika buat baru
                        'barcode' => $produkTemplate->barcode,
                        'photo' => $produkTemplate->photo,
                        'harga_jual' => $produkTemplate->harga_jual,
                        'harga_beli' => $hargaBeli,
                        'stok' => 0, // Stok awal 0, nanti di-update dari batch
                        'kadaluwarsa' => $kadaluwarsa,
                        'kategori_id' => $produkTemplate->kategori_id,
                        'satuan_id' => $produkTemplate->satuan_id,
                        'supplier_id' => $produkTemplate->supplier_id ?? null,
                    ]
                );

                // Simpan ke cache untuk item berikutnya
                $produkMasterCache[$namaProduk] = $produkMaster;
            }

            // Hitung stok sebelum (sum dari semua batch produk ini)
            $stokSebelum = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');

            // ✅ BUAT BATCH BARU (barcode tetap sama dengan barcode produk)
            $batch = BatchProduk::create([
                'produk_id' => $produkMaster->produk_id,
                'barcode_batch' => $barcode, // ✅ Barcode sama dengan produk
                'stok' => $jumlah,
                'kadaluwarsa' => $kadaluwarsa,
                'harga_beli' => $hargaBeli,
                'pembelian_id' => $pembelian->pembelian_id,
            ]);

            // Update stok master produk (sum dari semua batch)
            $stokSesudah = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');
            $produkMaster->stok = $stokSesudah;
            $produkMaster->save();

            // Simpan detail pembelian (link pembelian -> produk -> batch)
            DetailPembelians::create([
                'pembelian_id' => $pembelian->pembelian_id,
                'produk_id' => $produkMaster->produk_id,
                'batch_id' => $batch->batch_id,
                'jumlah' => $jumlah,
                'harga_beli' => $hargaBeli,
                'subtotal' => $subtotal,
                'kadaluwarsa' => $kadaluwarsa,
                'barcode_batch' => $barcode,
            ]);

            // Catat riwayat stok (tipe: masuk)
            StockHistory::create([
                'produk_id' => $produkMaster->produk_id,
                'tipe' => 'masuk', // Stok masuk dari pembelian
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => "Pembelian batch {$barcode} - ED: {$kadaluwarsa}",
                'referensi_tipe' => 'pembelian',
                'referensi_id' => $pembelian->pembelian_id,
                'user_id' => Auth::id() ?? 1,
            ]);
        }
    }

    /**
     * deletePembelianBatches()
     * Fungsi: Hapus semua batch dari pembelian & kembalikan stok
     * Flow (untuk setiap detail pembelian):
     * 1. Cari batch terkait
     * 2. Hitung stok sebelum
     * 3. Hapus batch
     * 4. Update stok produk master (sum dari batch yang tersisa)
     * 5. Catat stock history (keluar/koreksi)
     * 6. Hapus semua detail pembelian
     *
     * Parameter: Pembelians $pembelian (record pembelian yang akan dihapus batchnya)
     * Note: Method ini dipanggil dari update() dan destroy()
     *       Stok dikembalikan dengan menghapus batch (bukan menambah)
     */
    private function deletePembelianBatches(Pembelians $pembelian)
    {
        // Loop setiap detail pembelian
        foreach ($pembelian->details as $detail) {
            if ($detail->batch_id) {
                // Cari batch terkait
                $batch = BatchProduk::find($detail->batch_id);
                if ($batch) {
                    $produk = $batch->produk;

                    // Hitung stok sebelum
                    $stokSebelum = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                    // Hapus batch (stok otomatis berkurang karena batch hilang)
                    $batch->delete();

                    // Update stok produk master (sum dari batch yang tersisa)
                    $stokSesudah = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');
                    $produk->stok = $stokSesudah;
                    $produk->save();

                    // Catat riwayat (tipe: keluar, karena stok berkurang)
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar', // Keluar karena batch dihapus
                        'jumlah' => $detail->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'keterangan' => "Koreksi: Hapus/Edit pembelian #{$pembelian->pembelian_id}",
                        'referensi_tipe' => 'pembelian',
                        'referensi_id' => $pembelian->pembelian_id,
                        'user_id' => Auth::id() ?? 1,
                    ]);
                }
            }
        }

        // Hapus semua detail pembelian (record di tabel detail_pembelians)
        $pembelian->details()->delete();
    }
}
