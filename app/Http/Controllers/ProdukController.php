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

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produks::with(['kategori', 'satuan', 'supplier', 'batches'])->get();
        return view('produk.index', compact('produks'));
    }

    public function create()
    {
        return view('produk.create', [
            'kategoris' => Kategoris::all(),
            'satuans'   => Satuans::all(),
            'suppliers' => Suppliers::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode'     => 'required|string|max:50',
            'nama_produk' => 'required|string|max:100',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            $data = $request->except(['photo', 'stok', 'kadaluwarsa']); // ❌ Jangan ambil stok & kadaluwarsa

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
            }

            // ✅ SET STOK = 0 DULU (nanti dihitung dari batch)
            $data['stok'] = 0;
            $data['kadaluwarsa'] = $request->kadaluwarsa; // Simpan sebagai referensi template

            $produk = Produks::create($data);

            // ✅ BIKIN BATCH OTOMATIS kalau input stok > 0
            if ($request->stok > 0) {
                $batch = BatchProduk::create([
                    'produk_id' => $produk->produk_id,
                    'barcode_batch' => $request->barcode, // Pakai barcode master sebagai barcode batch pertama
                    'stok' => $request->stok,
                    'kadaluwarsa' => $request->kadaluwarsa,
                    'harga_beli' => $request->harga_beli,
                    'pembelian_id' => null, // Stok awal (bukan dari pembelian)
                ]);

                // ✅ UPDATE STOK MASTER dari batch
                $produk->updateStokFromBatch();

                // ✅ CATAT STOCK HISTORY
                StockHistory::create([
                    'produk_id' => $produk->produk_id,
                    'tipe' => 'masuk',
                    'jumlah' => $request->stok,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $request->stok,
                    'keterangan' => 'Stok awal produk baru - Batch: ' . $batch->barcode_batch,
                    'referensi_tipe' => 'manual',
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

    public function edit(string $id)
    {
        $produk = Produks::with('batches')->findOrFail($id);
        return view('produk.edit', [
            'produk'     => $produk,
            'kategoris'  => Kategoris::all(),
            'satuans'    => Satuans::all(),
            'suppliers'  => Suppliers::all(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $produk = Produks::findOrFail($id);

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
            $data = $request->except(['photo', 'stok', 'kadaluwarsa']);

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
            }

            $produk->update($data);

            // ✅ UPDATE STOK MASTER dari batch (biar selalu sinkron)
            $produk->updateStokFromBatch();

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui. Stok dihitung otomatis dari batch.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $produk = Produks::with('batches')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // ✅ CATAT HISTORY & HAPUS SEMUA BATCH
            foreach ($produk->batches as $batch) {
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
    public function cetakBarcode($id)
    {
        $produk = Produks::findOrFail($id);
        $pdf = Pdf::loadView('produk.barcode', compact('produk'))->setPaper('a7', 'portrait');
        return $pdf->stream('barcode-' . $produk->nama_produk . '.pdf');
    }

    public function cetakSemuaBarcode()
    {
        $produks = Produks::all();

        if ($produks->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada produk untuk dicetak.');
        }

        $pdf = Pdf::loadView('produk.barcode-semua', compact('produks'))->setPaper('a4', 'portrait');
        return $pdf->stream('semua-barcode-produk.pdf');
    }
}