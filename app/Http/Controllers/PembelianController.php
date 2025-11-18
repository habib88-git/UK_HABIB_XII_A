<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelians;
use App\Models\DetailPembelians;
use App\Models\Produks;
use App\Models\Suppliers;
use App\Models\StockHistory;
use App\Models\BatchProduk; // ✅ TAMBAH INI
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
        
        // Ambil produk unik (tidak duplikat)
        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo')
            ->selectRaw('MAX(produk_id) as produk_id')
            ->selectRaw('MAX(barcode) as barcode')
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.create', compact('suppliers', 'produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'produk_id.*' => 'required|exists:tbl_produks,produk_id',
            'jumlah.*' => 'required|integer|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
            'supplier_id.*' => 'nullable|exists:tbl_suppliers,supplier_id',
            'barcode.*' => 'required|string|max:100',
            'kadaluwarsa.*' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Ambil supplier_id
            $supplierId = null;
            foreach ($request->supplier_id as $sid) {
                if ($sid) {
                    $supplierId = $sid;
                    break;
                }
            }

            // Hitung total
            $total = 0;
            foreach ($request->jumlah as $i => $qty) {
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
                $total += $qty * $hargaBeli;
            }

            // Buat pembelian
            $pembelian = Pembelians::create([
                'tanggal' => $request->tanggal,
                'supplier_id' => $supplierId,
                'user_id' => Auth::id() ?? 1,
                'total_harga' => $total,
            ]);

            // Process setiap item
            foreach ($request->produk_id as $i => $produkId) {
                $jumlah = $request->jumlah[$i];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$i];
                $kadaluwarsa = $request->kadaluwarsa[$i];

                $produkTemplate = Produks::findOrFail($produkId);

                // ✅ CARI ATAU BUAT PRODUK MASTER (HANYA 1 RECORD PER NAMA PRODUK)
                $produkMaster = Produks::where('nama_produk', $produkTemplate->nama_produk)
                    ->first();

                if (!$produkMaster) {
                    // Buat produk master baru (tanpa barcode spesifik)
                    $produkMaster = Produks::create([
                        'barcode' => 'MASTER-' . strtoupper(substr($produkTemplate->nama_produk, 0, 5)) . '-' . time(),
                        'nama_produk' => $produkTemplate->nama_produk,
                        'photo' => $produkTemplate->photo,
                        'harga_jual' => $produkTemplate->harga_jual,
                        'harga_beli' => $hargaBeli,
                        'stok' => 0, // Stok akan dihitung dari batch
                        'kadaluwarsa' => $kadaluwarsa,
                        'kategori_id' => $produkTemplate->kategori_id,
                        'satuan_id' => $produkTemplate->satuan_id,
                        'supplier_id' => $produkTemplate->supplier_id ?? null,
                    ]);
                }

                // ✅ HITUNG STOK TOTAL SEBELUM (DARI SEMUA BATCH)
                $stokSebelum = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');

                // ✅ BUAT BATCH BARU
                $batch = BatchProduk::create([
                    'produk_id' => $produkMaster->produk_id,
                    'barcode_batch' => $barcode,
                    'stok' => $jumlah,
                    'kadaluwarsa' => $kadaluwarsa,
                    'harga_beli' => $hargaBeli,
                    'pembelian_id' => $pembelian->pembelian_id,
                ]);

                // ✅ UPDATE STOK TOTAL DI PRODUK MASTER
                $stokSesudah = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');
                $produkMaster->stok = $stokSesudah;
                $produkMaster->save();

                // ✅ SIMPAN DETAIL PEMBELIAN (DENGAN REFERENSI KE BATCH)
                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $produkMaster->produk_id,
                    'batch_id' => $batch->batch_id, // ✅ REFERENSI KE BATCH
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'kadaluwarsa' => $kadaluwarsa,
                    'barcode_batch' => $barcode,
                ]);

                // ✅ CATAT STOCK HISTORY
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

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil! Batch produk dicatat dengan barcode: ' . implode(', ', $request->barcode));

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
        
        $produks = Produks::select('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo')
            ->selectRaw('MAX(produk_id) as produk_id')
            ->selectRaw('MAX(barcode) as barcode')
            ->with(['kategori', 'satuan', 'supplier'])
            ->groupBy('nama_produk', 'kategori_id', 'satuan_id', 'supplier_id', 'harga_beli', 'harga_jual', 'photo')
            ->orderBy('nama_produk', 'asc')
            ->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'produks'));
    }

    public function update(Request $request, string $id)
    {
        $pembelian = Pembelians::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'nullable|exists:tbl_suppliers,supplier_id',
            'produk_id.*' => 'required|exists:tbl_produks,produk_id',
            'jumlah.*' => 'required|integer|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
            'barcode.*' => 'required|string|max:100',
            'kadaluwarsa.*' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // ✅ KEMBALIKAN STOK DARI BATCH LAMA & HAPUS BATCH
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

                        // Catat history
                        StockHistory::create([
                            'produk_id' => $produk->produk_id,
                            'tipe' => 'keluar',
                            'jumlah' => $detail->jumlah,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => "Koreksi: Edit pembelian #{$pembelian->pembelian_id}",
                            'referensi_tipe' => 'pembelian',
                            'referensi_id' => $pembelian->pembelian_id,
                            'user_id' => Auth::id() ?? 1,
                        ]);
                    }
                }
            }

            // Hapus detail lama
            $pembelian->details()->delete();

            // Hitung total baru
            $total = 0;
            foreach ($request->produk_id as $key => $produkId) {
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$key]);
                $total += $request->jumlah[$key] * $hargaBeli;
            }

            // Update pembelian
            $pembelian->update([
                'tanggal' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_harga' => $total,
            ]);

            // ✅ BUAT BATCH BARU
            foreach ($request->produk_id as $key => $produkId) {
                $jumlah = $request->jumlah[$key];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$key]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$key];
                $kadaluwarsa = $request->kadaluwarsa[$key];

                $produkTemplate = Produks::findOrFail($produkId);

                $produkMaster = Produks::where('nama_produk', $produkTemplate->nama_produk)->first();

                if (!$produkMaster) {
                    $produkMaster = Produks::create([
                        'barcode' => 'MASTER-' . strtoupper(substr($produkTemplate->nama_produk, 0, 5)) . '-' . time(),
                        'nama_produk' => $produkTemplate->nama_produk,
                        'photo' => $produkTemplate->photo,
                        'harga_jual' => $produkTemplate->harga_jual,
                        'harga_beli' => $hargaBeli,
                        'stok' => 0,
                        'kadaluwarsa' => $kadaluwarsa,
                        'kategori_id' => $produkTemplate->kategori_id,
                        'satuan_id' => $produkTemplate->satuan_id,
                        'supplier_id' => $produkTemplate->supplier_id ?? null,
                    ]);
                }

                $stokSebelum = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');

                $batch = BatchProduk::create([
                    'produk_id' => $produkMaster->produk_id,
                    'barcode_batch' => $barcode,
                    'stok' => $jumlah,
                    'kadaluwarsa' => $kadaluwarsa,
                    'harga_beli' => $hargaBeli,
                    'pembelian_id' => $pembelian->pembelian_id,
                ]);

                $stokSesudah = BatchProduk::where('produk_id', $produkMaster->produk_id)->sum('stok');
                $produkMaster->stok = $stokSesudah;
                $produkMaster->save();

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

                StockHistory::create([
                    'produk_id' => $produkMaster->produk_id,
                    'tipe' => 'masuk',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan' => "Edit pembelian batch {$barcode}",
                    'referensi_tipe' => 'pembelian',
                    'referensi_id' => $pembelian->pembelian_id,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

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
            foreach ($pembelian->details as $detail) {
                if ($detail->batch_id) {
                    $batch = BatchProduk::find($detail->batch_id);
                    if ($batch) {
                        $produk = $batch->produk;
                        $stokSebelum = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                        $batch->delete();

                        $stokSesudah = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');
                        $produk->stok = $stokSesudah;
                        $produk->save();

                        StockHistory::create([
                            'produk_id' => $produk->produk_id,
                            'tipe' => 'keluar',
                            'jumlah' => $detail->jumlah,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => "Hapus pembelian #{$pembelian->pembelian_id}",
                            'referensi_tipe' => 'pembelian',
                            'referensi_id' => $pembelian->pembelian_id,
                            'user_id' => Auth::id() ?? 1,
                        ]);
                    }
                }
            }

            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}