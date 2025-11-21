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
use App\Models\BatchProduk;
use Carbon\Carbon;

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
        
        // Ambil produk yang punya stok dengan info batch terdekat
        $produks = Produks::whereHas('batches', function($q) {
            $q->where('stok', '>', 0);
        })->with(['satuan', 'kategori', 'batchesAktif'])->get();
        
        // Tambahkan info diskon expiry untuk setiap produk
        $produks->map(function($produk) {
            $batchTerdekat = $produk->getBatchTerdekat();
            if ($batchTerdekat) {
                $produk->diskon_expiry = $this->hitungDiskonExpiry($batchTerdekat->kadaluwarsa);
                $produk->batch_terdekat = $batchTerdekat;
            } else {
                $produk->diskon_expiry = null;
                $produk->batch_terdekat = null;
            }
            return $produk;
        });
        
        $kategoris = Kategoris::all();

        return view('penjualan.create', compact('pelanggans', 'produks', 'kategoris'));
    }

    /**
     * Hitung diskon berdasarkan tanggal kadaluwarsa
     * 
     * Aturan:
     * - H-7 atau kurang: Buy 1 Get 1 (diskon 50% karena dapat 2 item)
     *   * Jika stok ganjil, item terakhir: diskon 10% + gratis 1
     * - Minggu ke-2 (H-8 s.d. H-14): Diskon 5% + potongan Rp 3.000
     * - Minggu ke-3 (H-15 s.d. H-21): Diskon 5% + potongan Rp 2.000
     * - Minggu ke-4 (H-22 s.d. H-28): Diskon 5% + potongan Rp 1.000
     * - Lebih dari 28 hari: Tidak ada diskon
     */
    private function hitungDiskonExpiry($tanggalKadaluwarsa)
    {
        if (!$tanggalKadaluwarsa) {
            return [
                'persentase' => 0,
                'potongan_nominal' => 0,
                'is_bogo' => false,
                'hari_sisa' => null,
                'minggu' => null
            ];
        }

        $now = Carbon::now();
        $expiry = Carbon::parse($tanggalKadaluwarsa);
        $hariSisa = $now->diffInDays($expiry, false);

        // Jika sudah kadaluwarsa, tidak ada diskon
        if ($hariSisa < 0) {
            return [
                'persentase' => 0,
                'potongan_nominal' => 0,
                'is_bogo' => false,
                'hari_sisa' => $hariSisa,
                'minggu' => null,
                'status' => 'expired'
            ];
        }

        // H-7 atau kurang: Buy 1 Get 1
        if ($hariSisa <= 7) {
            return [
                'persentase' => 50, // Karena dapat 2 item dengan harga 1
                'potongan_nominal' => 0,
                'is_bogo' => true,
                'hari_sisa' => $hariSisa,
                'minggu' => 1,
                'bonus_qty' => 1,
                'status' => 'bogo',
                // ✅ UNTUK STOK GANJIL (ITEM TERAKHIR)
                'diskon_stok_ganjil' => 10 // Diskon 10% untuk item terakhir
            ];
        }

        // Minggu ke-2 (H-8 s.d. H-14)
        if ($hariSisa >= 8 && $hariSisa <= 14) {
            return [
                'persentase' => 5,
                'potongan_nominal' => 3000,
                'is_bogo' => false,
                'hari_sisa' => $hariSisa,
                'minggu' => 2,
                'status' => 'minggu_2'
            ];
        }

        // Minggu ke-3 (H-15 s.d. H-21)
        if ($hariSisa >= 15 && $hariSisa <= 21) {
            return [
                'persentase' => 5,
                'potongan_nominal' => 2000,
                'is_bogo' => false,
                'hari_sisa' => $hariSisa,
                'minggu' => 3,
                'status' => 'minggu_3'
            ];
        }

        // Minggu ke-4 (H-22 s.d. H-28)
        if ($hariSisa >= 22 && $hariSisa <= 28) {
            return [
                'persentase' => 5,
                'potongan_nominal' => 1000,
                'is_bogo' => false,
                'hari_sisa' => $hariSisa,
                'minggu' => 4,
                'status' => 'minggu_4'
            ];
        }

        // Lebih dari 28 hari: tidak ada diskon
        return [
            'persentase' => 0,
            'potongan_nominal' => 0,
            'is_bogo' => false,
            'hari_sisa' => $hariSisa,
            'minggu' => null,
            'status' => 'normal'
        ];
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
            $total = 0;
            $totalDiskonExpiry = 0;
            $itemDetails = []; // Simpan detail untuk processing

            // Loop 1: Validasi stok dan hitung total
            foreach ($request->produk_id as $key => $produk_id) {
                $produk = Produks::findOrFail($produk_id);
                $jumlah = $request->jumlah_produk[$key];

                // Ambil batch terdekat untuk cek diskon expiry
                $batchTerdekat = BatchProduk::where('produk_id', $produk_id)
                    ->where('stok', '>', 0)
                    ->orderBy('kadaluwarsa', 'asc')
                    ->first();

                if (!$batchTerdekat) {
                    DB::rollBack();
                    return back()->with('error', "Produk {$produk->nama_produk} tidak memiliki batch aktif!");
                }

                $diskonExpiry = $this->hitungDiskonExpiry($batchTerdekat->kadaluwarsa);
                
                // ✅ JIKA BOGO, STOK YANG DIBUTUHKAN = JUMLAH BELI x 2
                $jumlahDibutuhkan = $diskonExpiry['is_bogo'] ? $jumlah * 2 : $jumlah;
                
                // Cek stok dengan jumlah yang dibutuhkan (untuk BOGO = jumlah x 2)
                $stokTersedia = BatchProduk::where('produk_id', $produk_id)->sum('stok');
                
                if ($stokTersedia < $jumlahDibutuhkan) {
                    $pesanError = "Stok {$produk->nama_produk} tidak mencukupi! Tersedia: {$stokTersedia}";
                    if ($diskonExpiry['is_bogo']) {
                        $pesanError .= " (BOGO aktif: beli {$jumlah} butuh stok {$jumlahDibutuhkan})";
                    }
                    DB::rollBack();
                    return back()->with('error', $pesanError);
                }
                
                // Hitung harga setelah diskon expiry
                $hargaAsli = $produk->harga_jual;
                $diskonPersenExpiry = ($hargaAsli * $diskonExpiry['persentase'] / 100);
                $hargaSetelahDiskon = $hargaAsli - $diskonPersenExpiry - $diskonExpiry['potongan_nominal'];
                
                // Pastikan harga tidak negatif
                $hargaSetelahDiskon = max($hargaSetelahDiskon, 0);
                
                $subtotal = $hargaSetelahDiskon * $jumlah; // Harga sudah diskon, qty masih original
                $total += $subtotal;

                // Total diskon expiry untuk item ini
                $diskonItemExpiry = ($hargaAsli * $jumlah) - $subtotal;
                $totalDiskonExpiry += $diskonItemExpiry;

                // Simpan detail untuk diproses nanti
                $itemDetails[] = [
                    'produk' => $produk,
                    'jumlah' => $jumlah, // Jumlah yang dibeli (di form)
                    'jumlah_dibutuhkan' => $jumlahDibutuhkan, // Stok yang dikurangi (untuk BOGO = x2)
                    'harga_asli' => $hargaAsli,
                    'harga_setelah_diskon' => $hargaSetelahDiskon,
                    'subtotal' => $subtotal,
                    'diskon_expiry' => $diskonExpiry,
                    'diskon_item_expiry' => $diskonItemExpiry,
                    'batch_terdekat' => $batchTerdekat
                ];
            }

            // Hitung diskon member (hanya jika pelanggan terdaftar)
            $diskonMember = 0;
            $alasanDiskonMember = [];

            if ($request->pelanggan_id) {
                $totalJumlahProduk = array_sum($request->jumlah_produk);
                
                // Diskon member 5% jika beli >= 10 item
                if ($totalJumlahProduk >= 10) {
                    $diskonMember += ($total * 0.05);
                    $alasanDiskonMember[] = "Pembelian {$totalJumlahProduk} item (≥10)";
                }
                
                // Diskon member 5% jika belanja >= Rp 100.000
                if ($total >= 100000) {
                    $diskonMember += ($total * 0.05);
                    $alasanDiskonMember[] = "Belanja ≥ Rp 100.000";
                }
                
                // Cap maksimal diskon member tidak melebihi total
                $diskonMember = min($diskonMember, $total);
            }

            // Total diskon = diskon expiry + diskon member
            $totalDiskon = $totalDiskonExpiry + $diskonMember;
            $totalSetelahDiskon = $total - $diskonMember; // Diskon expiry sudah masuk di $total

            // Validasi jumlah bayar
            if ($request->metode == 'cash' && $request->jumlah_bayar < $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Jumlah bayar kurang dari total harga setelah diskon!');
            }

            if ($request->metode == 'qris' && $request->jumlah_bayar != $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Untuk pembayaran QRIS, jumlah bayar harus sama dengan total!');
            }

            // Simpan penjualan
            $penjualan = Penjualans::create([
                'tanggal_penjualan' => now(),
                'total_harga'       => $total,
                'diskon'            => $totalDiskon, // Total semua diskon
                'pelanggan_id'      => $request->pelanggan_id,
                'user_id'           => Auth::id(),
            ]);

            // Loop 2: Process setiap item
            foreach ($itemDetails as $item) {
                $produk = $item['produk'];
                $jumlah = $item['jumlah']; // Jumlah yang dibeli (input customer)
                $jumlahDibutuhkan = $item['jumlah_dibutuhkan']; // Stok yang dikurangi (BOGO = x2)
                $subtotal = $item['subtotal'];
                $diskonExpiry = $item['diskon_expiry'];

                $stokSebelum = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                // ✅ KURANGI STOK SESUAI JUMLAH YANG DIBUTUHKAN (UNTUK BOGO = JUMLAH x 2)
                try {
                    $batchDipakai = BatchProduk::kurangiStokFEFO($produk->produk_id, $jumlahDibutuhkan);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->with('error', $e->getMessage());
                }

                $produk->updateStokFromBatch();
                $stokSesudah = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                // Simpan detail penjualan per batch
                foreach ($batchDipakai as $batchInfo) {
                    $subtotalBatch = ($batchInfo['jumlah'] / $jumlahDibutuhkan) * $subtotal;

                    // Informasi diskon untuk keterangan
                    $infoDiskon = '';
                    if ($diskonExpiry['is_bogo']) {
                        $totalItemDidapat = $batchInfo['jumlah']; // Sudah dikalikan 2 di kurangiStokFEFO
                        $infoDiskon = " [BOGO - Beli " . ($batchInfo['jumlah'] / 2) . " dapat {$batchInfo['jumlah']} item]";
                    } elseif ($diskonExpiry['persentase'] > 0 || $diskonExpiry['potongan_nominal'] > 0) {
                        $infoDiskon = " [Diskon Expiry: {$diskonExpiry['persentase']}% + Rp " . number_format($diskonExpiry['potongan_nominal']) . "]";
                    }

                    DetailPenjualans::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'produk_id' => $produk->produk_id,
                        'batch_id' => $batchInfo['batch_id'],
                        'jumlah_produk' => $batchInfo['jumlah'], // ✅ Simpan jumlah SEBENARNYA yang keluar dari stok
                        'subtotal' => $subtotalBatch,
                        'barcode_batch' => $batchInfo['barcode_batch'],
                        'kadaluwarsa_batch' => $batchInfo['kadaluwarsa'],
                    ]);

                    $pelanggan = $request->pelanggan_id 
                        ? Pelanggans::find($request->pelanggan_id) 
                        : null;
                    
                    $keterangan = $pelanggan 
                        ? "Penjualan kepada {$pelanggan->nama_pelanggan} - Batch: {$batchInfo['barcode_batch']} (FEFO){$infoDiskon}" 
                        : "Penjualan (Umum) - Batch: {$batchInfo['barcode_batch']} (FEFO){$infoDiskon}";

                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar',
                        'jumlah' => $batchInfo['jumlah'], // ✅ Catat jumlah SEBENARNYA
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'keterangan' => $keterangan,
                        'referensi_tipe' => 'penjualan',
                        'referensi_id' => $penjualan->penjualan_id,
                        'user_id' => Auth::id(),
                    ]);

                    $stokSebelum = $stokSesudah;
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

            $pesanDiskon = [];
            if ($totalDiskonExpiry > 0) {
                $pesanDiskon[] = "Diskon produk mendekati kadaluwarsa: Rp " . number_format($totalDiskonExpiry);
            }
            if ($diskonMember > 0) {
                $pesanDiskon[] = "Diskon member: Rp " . number_format($diskonMember) . " (" . implode(" + ", $alasanDiskonMember) . ")";
            }
            
            $pesanSukses = 'Transaksi berhasil disimpan dengan sistem FEFO!';
            if (!empty($pesanDiskon)) {
                $pesanSukses .= ' ' . implode(', ', $pesanDiskon);
            }

            return redirect()->route('penjualan.create')->with([
                'success' => $pesanSukses,
                'penjualan_id' => $penjualan->penjualan_id,
                'metode_pembayaran' => $request->metode,
                'total_bayar' => $totalSetelahDiskon
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

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
        $penjualan = Penjualans::findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($penjualan->detailPenjualans as $detail) {
                if ($detail->batch_id) {
                    $batch = BatchProduk::find($detail->batch_id);
                    if ($batch) {
                        $stokSebelum = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');
                        
                        $batch->stok += $detail->jumlah_produk;
                        $batch->save();

                        $stokSesudah = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');
                        $batch->produk->updateStokFromBatch();

                        StockHistory::create([
                            'produk_id' => $batch->produk_id,
                            'tipe' => 'masuk',
                            'jumlah' => $detail->jumlah_produk,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => 'Koreksi: Hapus penjualan #' . $penjualan->penjualan_id . ' (FEFO)',
                            'referensi_tipe' => 'penjualan',
                            'referensi_id' => $penjualan->penjualan_id,
                            'user_id' => Auth::id(),
                        ]);
                    }
                }
            }

            $penjualan->delete();

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus dan stok dikembalikan (sistem FEFO)');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}