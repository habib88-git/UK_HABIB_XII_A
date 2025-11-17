<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelians;
use App\Models\DetailPembelians;
use App\Models\Produks;
use App\Models\Suppliers;
use App\Models\StockHistory; // ✅ TAMBAH INI
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
            $supplierId = null;
            foreach ($request->supplier_id as $sid) {
                if ($sid) {
                    $supplierId = $sid;
                    break;
                }
            }

            $total = 0;
            foreach ($request->jumlah as $i => $qty) {
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
                $total += $qty * $hargaBeli;
            }

            $pembelian = Pembelians::create([
                'tanggal' => $request->tanggal,
                'supplier_id' => $supplierId,
                'user_id' => Auth::id() ?? 1,
                'total_harga' => $total,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $jumlah = $request->jumlah[$i];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$i];
                $kadaluwarsa = $request->kadaluwarsa[$i];

                $produkTemplate = Produks::findOrFail($produkId);

                $newProduk = Produks::create([
                    'barcode' => $barcode,
                    'nama_produk' => $produkTemplate->nama_produk,
                    'photo' => $produkTemplate->photo,
                    'harga_jual' => $produkTemplate->harga_jual,
                    'harga_beli' => $hargaBeli,
                    'stok' => $jumlah,
                    'kadaluwarsa' => $kadaluwarsa,
                    'kategori_id' => $produkTemplate->kategori_id,
                    'satuan_id' => $produkTemplate->satuan_id,
                    'supplier_id' => $produkTemplate->supplier_id ?? null,
                ]);

                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $newProduk->produk_id,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'kadaluwarsa' => $kadaluwarsa,
                    'barcode_batch' => $barcode,
                ]);

                // ✅ CATAT STOCK HISTORY - STOK MASUK
                StockHistory::create([
                    'produk_id' => $newProduk->produk_id,
                    'tipe' => 'masuk',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => 0, // Produk baru, stok awal 0
                    'stok_sesudah' => $jumlah,
                    'keterangan' => 'Pembelian dari ' . ($produkTemplate->supplier->nama_supplier ?? 'Supplier'),
                    'referensi_tipe' => 'pembelian',
                    'referensi_id' => $pembelian->pembelian_id,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan! Produk baru ditambahkan dengan barcode: ' . implode(', ', $request->barcode));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(string $id)
    {
        $pembelian = Pembelians::with(['supplier', 'user', 'details.produk.kategori', 'details.produk.satuan'])->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function edit(string $id)
    {
        $pembelian = Pembelians::with(['details.produk'])->findOrFail($id);
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
            // Kembalikan stok dan catat history
            foreach ($pembelian->details as $detail) {
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $stokSebelum = $produk->stok;
                    $produk->stok -= $detail->jumlah;

                    // ✅ CATAT STOCK HISTORY - KOREKSI STOK (KELUAR)
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar',
                        'jumlah' => $detail->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $produk->stok,
                        'keterangan' => 'Koreksi: Edit pembelian #' . $pembelian->pembelian_id,
                        'referensi_tipe' => 'pembelian',
                        'referensi_id' => $pembelian->pembelian_id,
                        'user_id' => Auth::id() ?? 1,
                    ]);

                    if ($produk->stok <= 0) {
                        $produk->delete();
                    } else {
                        $produk->save();
                    }
                }
            }

            $pembelian->details()->delete();

            $total = 0;
            foreach ($request->produk_id as $key => $produkId) {
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$key]);
                $total += $request->jumlah[$key] * $hargaBeli;
            }

            $pembelian->update([
                'tanggal' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_harga' => $total,
            ]);

            foreach ($request->produk_id as $key => $produkId) {
                $jumlah = $request->jumlah[$key];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$key]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$key];
                $kadaluwarsa = $request->kadaluwarsa[$key];

                $produkTemplate = Produks::findOrFail($produkId);

                $newProduk = Produks::create([
                    'barcode' => $barcode,
                    'nama_produk' => $produkTemplate->nama_produk,
                    'photo' => $produkTemplate->photo,
                    'harga_jual' => $produkTemplate->harga_jual,
                    'harga_beli' => $hargaBeli,
                    'stok' => $jumlah,
                    'kadaluwarsa' => $kadaluwarsa,
                    'kategori_id' => $produkTemplate->kategori_id,
                    'satuan_id' => $produkTemplate->satuan_id,
                    'supplier_id' => $produkTemplate->supplier_id ?? null,
                ]);

                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $newProduk->produk_id,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'kadaluwarsa' => $kadaluwarsa,
                    'barcode_batch' => $barcode,
                ]);

                // ✅ CATAT STOCK HISTORY - STOK MASUK (EDIT)
                StockHistory::create([
                    'produk_id' => $newProduk->produk_id,
                    'tipe' => 'masuk',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $jumlah,
                    'keterangan' => 'Edit pembelian #' . $pembelian->pembelian_id,
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
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $stokSebelum = $produk->stok;
                    $produk->stok -= $detail->jumlah;

                    // ✅ CATAT STOCK HISTORY - KOREKSI (KELUAR)
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar',
                        'jumlah' => $detail->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $produk->stok,
                        'keterangan' => 'Hapus pembelian #' . $pembelian->pembelian_id,
                        'referensi_tipe' => 'pembelian',
                        'referensi_id' => $pembelian->pembelian_id,
                        'user_id' => Auth::id() ?? 1,
                    ]);

                    if ($produk->stok <= 0) {
                        $produk->delete();
                    } else {
                        $produk->save();
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