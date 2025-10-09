<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetailPenjualans;

class ProfitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         // Ambil tanggal dari request atau fallback default bulan ini
    $start = $request->input('start', now()->startOfMonth()->toDateString());
    $end   = $request->input('end', now()->endOfMonth()->toDateString());

    $profits = DetailPenjualans::with('produk','penjualan')
        ->whereHas('penjualan', function($q) use ($start, $end) {
            $q->whereBetween('tanggal_penjualan', [
                $start . " 00:00:00",
                $end . " 23:59:59"
            ]);
        })
        ->get()
        ->map(function($item) {
            $item->profit = ($item->produk->harga_jual - $item->produk->harga_beli) * $item->jumlah_produk;
            return $item;
        });

    // Hitung total profit
    $totalProfit = $profits->sum('profit');

    // kirim semua data ke view
    return view('profit.index', compact('profits', 'start', 'end', 'totalProfit'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
