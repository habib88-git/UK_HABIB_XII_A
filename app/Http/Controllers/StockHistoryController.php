<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockHistory;
use App\Models\Produks;
use App\Models\Kategoris;

class StockHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StockHistory::with(['produk.kategori', 'produk.satuan', 'user']);

        // Filter berdasarkan produk
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        // Filter berdasarkan tipe (masuk/keluar)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori_id')) {
            $query->whereHas('produk', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        $histories = $query->latest()->paginate(50);

        // Data untuk filter
        $produks = Produks::select('produk_id', 'nama_produk', 'barcode')
            ->distinct('nama_produk')
            ->orderBy('nama_produk')
            ->get();
        
        $kategoris = Kategoris::all();

        return view('stock-history.index', compact('histories', 'produks', 'kategoris'));
    }

    // Lihat history per produk
    public function show($produkId)
    {
        $produk = Produks::with(['kategori', 'satuan', 'supplier'])->findOrFail($produkId);
        
        $histories = StockHistory::where('produk_id', $produkId)
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('stock-history.show', compact('produk', 'histories'));
    }

    // Download PDF History Stock
    public function downloadPdf(Request $request)
    {
        $query = StockHistory::with(['produk.kategori', 'produk.satuan', 'user']);

        // Filter berdasarkan produk
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        // Filter berdasarkan tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori_id')) {
            $query->whereHas('produk', function($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        $histories = $query->latest()->get();

        // Info filter untuk PDF
        $filterInfo = [
            'produk' => $request->produk_id ? Produks::find($request->produk_id)->nama_produk : 'Semua Produk',
            'tipe' => $request->tipe ? ucfirst($request->tipe) : 'Semua Tipe',
            'kategori' => $request->kategori_id ? Kategoris::find($request->kategori_id)->nama_kategori : 'Semua Kategori',
            'tanggal_dari' => $request->tanggal_dari ?? '-',
            'tanggal_sampai' => $request->tanggal_sampai ?? '-',
        ];

        $pdf = \PDF::loadView('stock-history.pdf', compact('histories', 'filterInfo'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('history-stock-' . date('Y-m-d-His') . '.pdf');
    }
}