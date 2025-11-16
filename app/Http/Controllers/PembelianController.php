<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelians;
use App\Models\DetailPembelians;
use App\Models\Produks;
use App\Models\Suppliers;
use App\Models\Users;
use App\Models\Kategoris;
use App\Models\Satuans;
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
        $produks = Produks::with(['kategori', 'satuan', 'supplier'])->get();

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
            // Ambil supplier_id pertama yang valid
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

            // Simpan pembelian
            $pembelian = Pembelians::create([
                'tanggal' => $request->tanggal,
                'supplier_id' => $supplierId,
                'user_id' => Auth::id() ?? 1,
                'total_harga' => $total,
            ]);

            // Loop produk - BUAT PRODUK BARU (BUKAN UPDATE STOK!)
            foreach ($request->produk_id as $i => $produkId) {
                $jumlah = $request->jumlah[$i];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$i];
                $kadaluwarsa = $request->kadaluwarsa[$i];

                // Ambil data produk template
                $produkTemplate = Produks::findOrFail($produkId);

                // BUAT PRODUK BARU dengan barcode & kadaluwarsa berbeda
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

                // Simpan detail pembelian dengan ID produk baru
                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $newProduk->produk_id,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'kadaluwarsa' => $kadaluwarsa,
                    'barcode_batch' => $barcode,
                ]);
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan! Produk baru ditambahkan.');

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
        $produks = Produks::with(['kategori', 'satuan', 'supplier'])->get();

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
            // Kembalikan stok dari produk yang di detail lama
            foreach ($pembelian->details as $detail) {
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $produk->stok -= $detail->jumlah;

                    // Jika stok jadi 0, hapus produk
                    if ($produk->stok <= 0) {
                        $produk->delete();
                    } else {
                        $produk->save();
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

            // Simpan detail baru - SELALU BUAT PRODUK BARU
            foreach ($request->produk_id as $key => $produkId) {
                $jumlah = $request->jumlah[$key];
                $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$key]);
                $subtotal = $jumlah * $hargaBeli;
                $barcode = $request->barcode[$key];
                $kadaluwarsa = $request->kadaluwarsa[$key];

                // Ambil data produk template
                $produkTemplate = Produks::findOrFail($produkId);

                // BUAT PRODUK BARU (tidak peduli barcode sama atau tidak)
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

                // Simpan detail pembelian baru
                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $newProduk->produk_id,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'kadaluwarsa' => $kadaluwarsa,
                    'barcode_batch' => $barcode,
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
            // Kembalikan stok dan hapus produk jika stok 0
            foreach ($pembelian->details as $detail) {
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $produk->stok -= $detail->jumlah;

                    // Jika stok jadi 0 atau kurang, hapus produk
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
