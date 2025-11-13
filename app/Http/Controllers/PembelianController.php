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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembelians = Pembelians::with(['supplier', 'user'])->latest()->get();
        return view('pembelian.index', compact('pembelians'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Suppliers::all();
        $produks = Produks::with(['kategori', 'satuan'])->get();

        return view('pembelian.create', compact('suppliers', 'produks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'tanggal' => 'required|date',
        'produk_id.*' => 'required|exists:tbl_produks,produk_id',
        'jumlah.*' => 'required|integer|min:1',
        'harga_beli.*' => 'required|numeric|min:0',
        'supplier_id.*' => 'nullable|exists:tbl_suppliers,supplier_id', // Ubah array
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
            // Pastikan harga_beli adalah numeric
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

        // Loop produk
        foreach ($request->produk_id as $i => $produkId) {
            $jumlah = $request->jumlah[$i];
            $hargaBeli = (float) str_replace(['.', ','], ['', '.'], $request->harga_beli[$i]);
            $subtotal = $jumlah * $hargaBeli;

            $produk = Produks::findOrFail($produkId);
            $produk->stok += $jumlah;
            $produk->save();

            DetailPembelians::create([
                'pembelian_id' => $pembelian->pembelian_id,
                'produk_id' => $produkId,
                'jumlah' => $jumlah,
                'harga_beli' => $hargaBeli,
                'subtotal' => $subtotal,
            ]);
        }

        DB::commit();
        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pembelian = Pembelians::with(['supplier', 'user', 'details.produk.kategori', 'details.produk.satuan'])->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pembelian = Pembelians::with(['details.produk'])->findOrFail($id);
        $suppliers = Suppliers::all();
        $produks = Produks::with(['kategori', 'satuan'])->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'produks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pembelian = Pembelians::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'nullable|exists:tbl_suppliers,supplier_id', // Ubah menjadi nullable
            'produk_id.*' => 'required|exists:tbl_produks,produk_id',
            'jumlah.*' => 'required|integer|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Kembalikan stok dari detail pembelian lama
            foreach ($pembelian->details as $detail) {
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $produk->stok -= $detail->jumlah;
                    $produk->save();
                }
            }

            // Hapus detail lama
            $pembelian->details()->delete();

            // Hitung total baru
            $total = 0;
            foreach ($request->produk_id as $key => $produkId) {
                $total += $request->jumlah[$key] * $request->harga_beli[$key];
            }

            // Update pembelian
            $pembelian->update([
                'tanggal' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_harga' => $total,
            ]);

            // Simpan detail baru dan update stok
            foreach ($request->produk_id as $key => $produkId) {
                $jumlah = $request->jumlah[$key];
                $hargaBeli = $request->harga_beli[$key];
                $subtotal = $jumlah * $hargaBeli;

                // Update stok produk
                $produk = Produks::findOrFail($produkId);
                $produk->stok += $jumlah;
                $produk->save();

                // Simpan detail pembelian baru
                DetailPembelians::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id' => $produkId,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pembelian = Pembelians::findOrFail($id);

        DB::beginTransaction();

        try {
            // Kembalikan stok dari detail pembelian
            foreach ($pembelian->details as $detail) {
                $produk = Produks::find($detail->produk_id);
                if ($produk) {
                    $produk->stok -= $detail->jumlah;
                    $produk->save();
                }
            }

            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus dan stok produk telah dikembalikan');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
