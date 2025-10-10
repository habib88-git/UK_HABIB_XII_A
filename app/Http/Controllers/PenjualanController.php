<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Penjualans;
use App\Models\Pelanggans;
use App\Models\Users;
use App\Models\Produks;
use App\Models\Pembayarans;
use App\Models\DetailPenjualans;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualans = Penjualans::with(['pelanggan','user','pembayaran','detailPenjualans'])
        ->latest()
        ->get();

        return view('penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $pelanggans = Pelanggans::all();
        $produks = Produks::all();
        return view('penjualan.create', compact('pelanggans','produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
        'pelanggan_id'   => 'nullable',
        'produk_id.*'    => 'required',
        'jumlah_produk.*'=> 'required|integer|min:1',
        'metode'         => 'required',
        'jumlah_bayar'   => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // Hitung total harga & cek stok
        $total = 0;
        foreach ($request->produk_id as $key => $produk_id) {
            $produk = Produks::findOrFail($produk_id);
            $jumlah = $request->jumlah_produk[$key];

            if ($produk->stok < $jumlah) {
                DB::rollBack();
                return back()->with('error', "Stok {$produk->nama_produk} tidak mencukupi!");
            }

            $subtotal = $produk->harga_jual * $jumlah;
            $total += $subtotal;
        }

        // =============================
        // 💡 Diskon Rp 5.000 per 100rb
        // =============================
        $jumlahKelipatan100rb = floor($total / 100000);
        $nominalDiskon = $jumlahKelipatan100rb * 5000;

        // Batas diskon = total
        $nominalDiskon = min($nominalDiskon, $total);

        $totalSetelahDiskon = $total - $nominalDiskon;

        // Validasi jumlah bayar
        if ($request->jumlah_bayar < $totalSetelahDiskon) {
            DB::rollBack();
            return back()->with('error', 'Jumlah bayar kurang dari total harga setelah diskon!');
        }

        // Simpan penjualan
        $penjualan = Penjualans::create([
            'tanggal_penjualan' => now(),
            'total_harga'       => $total,
            'diskon'            => $nominalDiskon,
            'pelanggan_id'      => $request->pelanggan_id,
            'user_id'           => Auth::id(),
        ]);

        // Simpan detail & kurangi stok
        foreach ($request->produk_id as $key => $produk_id) {
            $produk = Produks::findOrFail($produk_id);
            $jumlah = $request->jumlah_produk[$key];
            $subtotal = $produk->harga_jual * $jumlah;

            DetailPenjualans::create([
                'penjualan_id'  => $penjualan->penjualan_id,
                'produk_id'     => $produk_id,
                'jumlah_produk' => $jumlah,
                'subtotal'      => $subtotal,
            ]);

            $produk->stok -= $jumlah;
            $produk->save();
        }

        // Simpan pembayaran
        Pembayarans::create([
            'penjualan_id'      => $penjualan->penjualan_id,
            'metode'            => $request->metode,
            'jumlah'            => $request->jumlah_bayar,
            'kembalian'         => $request->jumlah_bayar - $totalSetelahDiskon,
            'tanggal_pembayaran'=> now(),
        ]);

        DB::commit();
        return redirect()->route('penjualan.index')->with('success','Transaksi berhasil disimpan');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    }

    public function show(string $id)
    {
        $penjualan = Penjualans::with(['pelanggan','user','detailPenjualans.produk','pembayaran'])
        ->findOrFail($id);

        return view('penjualan.show', compact('penjualan'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
