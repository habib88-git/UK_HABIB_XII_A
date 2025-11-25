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

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelians::with(['supplier', 'user'])->latest()->get();
        return view('pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $suppliers = Suppliers::all();

        // ✅ Ambil produk unik (1 produk master per nama)
        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->selectRaw('MAX(produk_id) as produk_id')
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.create', compact('suppliers', 'produks'));
    }

    public function store(Request $request)
{
    // PARSE HARGA BELI SEBELUM VALIDASI (Fix format Rupiah)
    $hargaBeliParsed = [];
    foreach ($request->harga_beli as $harga) {
        $hargaBeliParsed[] = (float) str_replace(['.', ','], ['', '.'], $harga);
    }
    $request->merge(['harga_beli' => $hargaBeliParsed]);

    // NORMALISASI supplier_id (supaya tidak array)
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

    // VALIDASI
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
        // Hitung total pembelian
        $total = 0;
        foreach ($request->jumlah as $i => $qty) {
            $total += $qty * $request->harga_beli[$i];
        }

        // Create pembelian master
        $pembelian = Pembelians::create([
            'tanggal' => $request->tanggal,
            'supplier_id' => $supplierId ? (int) $supplierId : null,
            'user_id' => Auth::id() ?? 1,
            'total_harga' => $total,
        ]);

        // PROSES ITEM
        $this->processItemsPembelian($request, $pembelian);

        DB::commit();
        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan!');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
    }
}

    public function show(string $id)
    {
        $pembelian = Pembelians::with(['supplier', 'user', 'details.produk.kategori', 'details.produk.satuan', 'details.batch'])->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function edit(string $id)
    {
        $pembelian = Pembelians::with(['details.produk', 'details.batch'])->findOrFail($id);
        $suppliers = Suppliers::all();

        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->selectRaw('MAX(produk_id) as produk_id')
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo', 'barcode')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'produks'));
    }

    public function update(Request $request, string $id)
{
    $pembelian = Pembelians::findOrFail($id);

    // PARSE HARGA BELI SEBELUM VALIDASI
    $hargaBeliParsed = [];
    foreach ($request->harga_beli as $harga) {
        $hargaBeliParsed[] = (float) str_replace(['.', ','], ['', '.'], $harga);
    }
    $request->merge(['harga_beli' => $hargaBeliParsed]);

    // NORMALISASI supplier_id
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

    // VALIDASI
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
        // HAPUS BATCH LAMA
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

        // BUAT BATCH BARU
        $this->processItemsPembelian($request, $pembelian);

        DB::commit();
        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}


    public function destroy(string $id)
    {
        $pembelian = Pembelians::findOrFail($id);

        DB::beginTransaction();

        try {
            // ✅ HAPUS SEMUA BATCH & KEMBALIKAN STOK
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

    public function printPdf($id)
    {
        $pembelian = Pembelians::with([
            'supplier',
            'user',
            'details.produk.kategori',
            'details.produk.satuan',
            'details.produk.supplier',
            'details.batch'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('laporan.laporan_pembelian', compact('pembelian'));
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Laporan_Pembelian_' . str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    // ========================================
    // ✅ HELPER METHODS
    // ========================================

    /**
     * Process pembelian items (untuk store & update)
     */
    private function processItemsPembelian(Request $request, Pembelians $pembelian)
    {
        // ✅ CACHE produk master yang sudah di-create (fix duplikasi)
        $produkMasterCache = [];

        foreach ($request->produk_id as $i => $produkId) {
            $jumlah = $request->jumlah[$i];
            $hargaBeli = $request->harga_beli[$i]; // ✅ Sudah float dari parse sebelumnya
            $subtotal = $jumlah * $hargaBeli;
            $kadaluwarsa = $request->kadaluwarsa[$i];

            // Ambil produk template
            $produkTemplate = Produks::findOrFail($produkId);
            $barcode = $produkTemplate->barcode;

            // ✅ CEK CACHE DULU (fix duplikasi produk master)
            $namaProduk = $produkTemplate->nama_produk;

            if (isset($produkMasterCache[$namaProduk])) {
                $produkMaster = $produkMasterCache[$namaProduk];
            } else {
                // ✅ CARI ATAU BUAT PRODUK MASTER
                $produkMaster = Produks::firstOrCreate(
                    ['nama_produk' => $namaProduk],
                    [
                        'barcode' => $produkTemplate->barcode,
                        'photo' => $produkTemplate->photo,
                        'harga_jual' => $produkTemplate->harga_jual,
                        'harga_beli' => $hargaBeli,
                        'stok' => 0,
                        'kadaluwarsa' => $kadaluwarsa,
                        'kategori_id' => $produkTemplate->kategori_id,
                        'satuan_id' => $produkTemplate->satuan_id,
                        'supplier_id' => $produkTemplate->supplier_id ?? null,
                    ]
                );

                // Simpan ke cache
                $produkMasterCache[$namaProduk] = $produkMaster;
            }

            // Hitung stok sebelum
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

            // Update stok master
            $stokSesudah = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');
            $produkMaster->stok = $stokSesudah;
            $produkMaster->save();

            // Simpan detail pembelian
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

            // Catat riwayat stok
            StockHistory::create([
                'produk_id' => $produkMaster->produk_id,
                'tipe' => 'masuk',
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
     * Delete all batches dari pembelian & kembalikan stok
     */
    private function deletePembelianBatches(Pembelians $pembelian)
    {
        foreach ($pembelian->details as $detail) {
            if ($detail->batch_id) {
                $batch = BatchProduk::find($detail->batch_id);
                if ($batch) {
                    $produk = $batch->produk;
                    $stokSebelum = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                    // Hapus batch
                    $batch->delete();

                    // Update stok produk master
                    $stokSesudah = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');
                    $produk->stok = $stokSesudah;
                    $produk->save();

                    // Catat riwayat
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar',
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

        // Hapus semua detail pembelian
        $pembelian->details()->delete();
    }
}
