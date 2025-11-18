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
use App\Models\Kategoris;
use App\Models\DetailPenjualans;
use App\Models\StockHistory;
use App\Models\BatchProduk; // ✅ TAMBAH INI

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
        
        // ✅ Ambil produk yang punya stok (dari batch)
        $produks = Produks::whereHas('batches', function($q) {
            $q->where('stok', '>', 0);
        })->with(['satuan', 'kategori', 'batchesAktif'])->get();
        
        $kategoris = Kategoris::all();

        return view('penjualan.create', compact('pelanggans', 'produks', 'kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id'   => 'nullable',
            'produk_id.*'    => 'required',
            'jumlah_produk.*'=> 'required|integer|min:1',
            'metode'         => 'required|in:cash,qris',
            'jumlah_bayar'   => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Hitung total harga & cek stok
            $total = 0;
            foreach ($request->produk_id as $key => $produk_id) {
                $produk = Produks::findOrFail($produk_id);
                $jumlah = $request->jumlah_produk[$key];

                // ✅ Cek stok dari batch
                $stokTersedia = BatchProduk::where('produk_id', $produk_id)->sum('stok');
                
                if ($stokTersedia < $jumlah) {
                    DB::rollBack();
                    return back()->with('error', "Stok {$produk->nama_produk} tidak mencukupi! Tersedia: {$stokTersedia}");
                }

                $subtotal = $produk->harga_jual * $jumlah;
                $total += $subtotal;
            }

            // Hitung diskon untuk pelanggan terdaftar
            $nominalDiskon = 0;
            if ($request->pelanggan_id) {
                $jumlahKelipatan100rb = floor($total / 100000);
                $nominalDiskon = $jumlahKelipatan100rb * 5000;
                $nominalDiskon = min($nominalDiskon, $total);
            }

            $totalSetelahDiskon = $total - $nominalDiskon;

            // Validasi jumlah bayar untuk cash
            if ($request->metode == 'cash' && $request->jumlah_bayar < $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Jumlah bayar kurang dari total harga setelah diskon!');
            }

            // Untuk QRIS, jumlah bayar harus sama dengan total
            if ($request->metode == 'qris' && $request->jumlah_bayar != $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Untuk pembayaran QRIS, jumlah bayar harus sama dengan total!');
            }

            // Simpan penjualan
            $penjualan = Penjualans::create([
                'tanggal_penjualan' => now(),
                'total_harga'       => $total,
                'diskon'            => $nominalDiskon,
                'pelanggan_id'      => $request->pelanggan_id,
                'user_id'           => Auth::id(),
            ]);

            // ✅ FIFO: Process setiap item dengan mengambil batch terdekat kadaluwarsa
            foreach ($request->produk_id as $key => $produk_id) {
                $produk = Produks::findOrFail($produk_id);
                $jumlah = $request->jumlah_produk[$key];
                $subtotal = $produk->harga_jual * $jumlah;

                $stokSebelum = BatchProduk::where('produk_id', $produk_id)->sum('stok');

                // ✅ KURANGI STOK MENGGUNAKAN FIFO
                try {
                    $batchDipakai = BatchProduk::kurangiStokFIFO($produk_id, $jumlah);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->with('error', $e->getMessage());
                }

                // ✅ UPDATE STOK MASTER PRODUK
                $produk->updateStokFromBatch();

                $stokSesudah = BatchProduk::where('produk_id', $produk_id)->sum('stok');

                // ✅ SIMPAN DETAIL PENJUALAN (BISA MULTIPLE BATCH UNTUK 1 ITEM)
                foreach ($batchDipakai as $batchInfo) {
                    // Hitung subtotal proporsional per batch
                    $subtotalBatch = ($batchInfo['jumlah'] / $jumlah) * $subtotal;

                    DetailPenjualans::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'produk_id' => $produk_id,
                        'batch_id' => $batchInfo['batch_id'],
                        'jumlah_produk' => $batchInfo['jumlah'],
                        'subtotal' => $subtotalBatch,
                        'barcode_batch' => $batchInfo['barcode_batch'],
                        'kadaluwarsa_batch' => $batchInfo['kadaluwarsa'],
                    ]);

                    // Keterangan berdasarkan pelanggan
                    $pelanggan = $request->pelanggan_id 
                        ? Pelanggans::find($request->pelanggan_id) 
                        : null;
                    
                    $keterangan = $pelanggan 
                        ? "Penjualan kepada {$pelanggan->nama_pelanggan} - Batch: {$batchInfo['barcode_batch']}" 
                        : "Penjualan (Umum) - Batch: {$batchInfo['barcode_batch']}";

                    // ✅ CATAT STOCK HISTORY PER BATCH
                    StockHistory::create([
                        'produk_id' => $produk_id,
                        'tipe' => 'keluar',
                        'jumlah' => $batchInfo['jumlah'],
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'keterangan' => $keterangan,
                        'referensi_tipe' => 'penjualan',
                        'referensi_id' => $penjualan->penjualan_id,
                        'user_id' => Auth::id(),
                    ]);

                    $stokSebelum = $stokSesudah; // Update untuk batch berikutnya
                }
            }

            // Simpan pembayaran
            Pembayarans::create([
                'penjualan_id'      => $penjualan->penjualan_id,
                'metode'            => $request->metode,
                'jumlah'            => $request->jumlah_bayar,
                'kembalian'         => $request->metode == 'cash' ? ($request->jumlah_bayar - $totalSetelahDiskon) : 0,
                'tanggal_pembayaran'=> now(),
                'qris_reference'    => $request->metode == 'qris' ? 'QRIS-' . time() . '-' . $penjualan->penjualan_id : null,
            ]);

            DB::commit();

            // Redirect dengan data transaksi
            return redirect()->route('penjualan.create')->with([
                'success' => 'Transaksi berhasil disimpan dengan sistem FIFO!',
                'penjualan_id' => $penjualan->penjualan_id,
                'metode_pembayaran' => $request->metode,
                'total_bayar' => $totalSetelahDiskon
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Method untuk cetak struk
    public function cetakStruk($id)
    {
        $penjualan = Penjualans::with([
            'pelanggan',
            'user',
            'detailPenjualans.produk',
            'detailPenjualans.batch',
            'pembayaran'
        ])->findOrFail($id);

        return view('laporan.struk', compact('penjualan'));
    }

    // Method untuk generate QRIS
    public function generateQRIS($amount)
    {
        $STATIC_QRIS = "00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214844519767362640303UMI51440014ID.CO.QRIS.WWW0215ID20243345184510303UMI5204541153033605802ID5920YANTO SHOP OK18846346005DEPOK61051641162070703A0163046879";

        function charCodeAt($str, $index) {
            return ord($str[$index]);
        }

        function ConvertCRC16($str) {
            $crc = 0xFFFF;
            for ($c = 0; $c < strlen($str); $c++) {
                $crc ^= charCodeAt($str, $c) << 8;
                for ($i = 0; $i < 8; $i++) {
                    $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
                }
            }
            $hex = strtoupper(dechex($crc & 0xFFFF));
            return strlen($hex) === 3 ? '0' . $hex : str_pad($hex, 4, '0', STR_PAD_LEFT);
        }

        $qris = substr($STATIC_QRIS, 0, -4);
        $step1 = str_replace("010211", "010212", $qris);
        $step2 = explode("5802ID", $step1);
        $uang = "54" . str_pad(strlen($amount), 2, '0', STR_PAD_LEFT) . $amount;
        $uang .= "5802ID";
        $fix = trim($step2[0]) . $uang . trim($step2[1]);
        $finalQR = $fix . ConvertCRC16($fix);

        return $finalQR;
    }

    public function show(string $id)
    {
        $penjualan = Penjualans::with([
            'pelanggan',
            'user',
            'detailPenjualans.produk',
            'detailPenjualans.batch',
            'pembayaran'
        ])->findOrFail($id);

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
        // ✅ HAPUS DENGAN KEMBALIKAN STOK KE BATCH
        $penjualan = Penjualans::findOrFail($id);

        DB::beginTransaction();

        try {
            // Kembalikan stok ke batch
            foreach ($penjualan->detailPenjualans as $detail) {
                if ($detail->batch_id) {
                    $batch = BatchProduk::find($detail->batch_id);
                    if ($batch) {
                        $stokSebelum = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');
                        
                        $batch->stok += $detail->jumlah_produk;
                        $batch->save();

                        $stokSesudah = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');

                        // Update stok master
                        $batch->produk->updateStokFromBatch();

                        // CATAT STOCK HISTORY
                        StockHistory::create([
                            'produk_id' => $batch->produk_id,
                            'tipe' => 'masuk',
                            'jumlah' => $detail->jumlah_produk,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => 'Koreksi: Hapus penjualan #' . $penjualan->penjualan_id,
                            'referensi_tipe' => 'penjualan',
                            'referensi_id' => $penjualan->penjualan_id,
                            'user_id' => Auth::id(),
                        ]);
                    }
                }
            }

            $penjualan->delete();

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus dan stok dikembalikan');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}