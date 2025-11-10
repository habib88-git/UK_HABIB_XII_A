<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Produks;
use App\Models\Kategoris;
use App\Models\Satuans;
use App\Models\Suppliers;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produks::with(['kategori', 'satuan', 'supplier'])->get();
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
            'barcode'     => 'nullable|string|max:50|unique:tbl_produks,barcode',
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

        $data = $request->except('photo');

        $data['barcode'] = Produks::generateUniqueBarcode();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
        }

        Produks::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $produk = Produks::findOrFail($id);
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
            'barcode'     => 'nullable|string|max:50|unique:tbl_produks,barcode,' . $id . ',produk_id',
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

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $produk = Produks::findOrFail($id);
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
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
