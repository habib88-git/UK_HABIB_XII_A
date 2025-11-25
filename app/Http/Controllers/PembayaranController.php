<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayarans;
use App\Models\Penjualans;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($penjualan_id)
    {
        $penjualan = Penjualans::findOrFail($penjualan_id);
        return view('pembayaran.create', compact('penjualan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $penjualan_id)
    {
        $request->validate([
            'metode' => 'required|in:cash,transfer,ewallet',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $penjualan = Penjualans::findOrFail($penjualan_id);

        Pembayarans::create([
            'penjualan_id' => $penjualan_id,
            'metode' => $request->metode,
            'jumlah' => $request->jumlah,
            'tanggal_pembayaran' => now(),
        ]);

        // update status penjualan
        $penjualan->update(['status' => 'success']);

        return redirect()->route('penjualan.show', $penjualan_id)
        ->with('success', 'Pembayaran berhasil!');
    }

    public function struk($pembayaran_id)
    {
        $pembayaran = Pembayarans::with('penjualan.detailPenjualan.produk')->findOrFail($pembayaran_id);
        return view('pembayaran.struk', compact('pembayaran'));
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
