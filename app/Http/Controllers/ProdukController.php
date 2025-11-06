<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Produks;
use App\Models\Kategoris;
use App\Models\Satuans;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produks = Produks::with(['kategori', 'satuan'])->get();
        $kategoris = Kategoris::all();
        $satuans = Satuans::all();

        return view('produk.index', compact('produks', 'kategoris', 'satuans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategoris::all();
        $satuans = Satuans::all();

        return view('produk.create', compact('kategoris', 'satuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
        }

        Produks::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produk = Produks::findOrFail($id);
        $kategoris = Kategoris::all();
        $satuans = Satuans::all();

        return view('produk.edit', compact('produk', 'kategoris', 'satuans'));
    }

    /**
     * Update the specified resource in storage.
     */
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
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('uploads/produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produk = Produks::findOrFail($id);
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Scan barcode untuk penjualan (AJAX)
     */
    public function scanBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        $produk = Produks::where('barcode', $barcode)
                        ->with(['kategori', 'satuan'])
                        ->first();

        if ($produk) {
            return response()->json([
                'success' => true,
                'data' => $produk
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ], 404);
    }


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

        $pdf = Pdf::loadView('produk.barcode-semua', compact('produks'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('semua-barcode-produk.pdf');
    }
}
