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

/**
 * Controller untuk mengelola transaksi penjualan
 * Fitur utama:
 * - Penjualan dengan sistem FEFO (First Expired First Out)
 * - Diskon otomatis berdasarkan kadaluwarsa produk (termasuk BOGO)
 * - Diskon member berdasarkan jumlah item dan total belanja
 * - Pembayaran Cash dan QRIS
 * - Tracking stok dengan history
 * - Cetak struk dan generate QRIS dinamis
 */
class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar semua penjualan
     * Mengambil data penjualan dengan relasi pelanggan, user, pembayaran, dan detail
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil semua penjualan dengan eager loading untuk optimasi query
        // latest() mengurutkan berdasarkan created_at descending (terbaru dulu)
        $penjualans = Penjualans::with(['pelanggan','user','pembayaran','detailPenjualans'])
        ->latest()
        ->get();

        // Return ke view dengan data penjualan
        return view('penjualan.index', compact('penjualans'));
    }

    /**
     * Menampilkan form untuk membuat penjualan baru
     * Mempersiapkan data pelanggan, produk dengan stok, dan kategori
     * Menghitung diskon expiry untuk setiap produk
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ambil semua data pelanggan untuk dropdown
        $pelanggans = Pelanggans::all();

        // Ambil produk yang punya stok dengan info batch terdekat
        // whereHas untuk filter produk yang memiliki batch dengan stok > 0
        $produks = Produks::whereHas('batches', function($q) {
            $q->where('stok', '>', 0);
        })->with(['satuan', 'kategori', 'batchesAktif'])->get();

        // Tambahkan info diskon expiry untuk setiap produk
        // Loop melalui setiap produk untuk menghitung diskon berdasarkan batch terdekat
        $produks->map(function($produk) {
            // Ambil batch dengan tanggal kadaluwarsa paling dekat
            $batchTerdekat = $produk->getBatchTerdekat();
            if ($batchTerdekat) {
                // Hitung diskon berdasarkan sisa hari kadaluwarsa
                $produk->diskon_expiry = $this->hitungDiskonExpiry($batchTerdekat->kadaluwarsa);
                $produk->batch_terdekat = $batchTerdekat;
            } else {
                // Jika tidak ada batch, set null
                $produk->diskon_expiry = null;
                $produk->batch_terdekat = null;
            }
            return $produk;
        });

        // Ambil semua kategori untuk filter di frontend
        $kategoris = Kategoris::all();

        // Return ke view dengan data yang sudah diproses
        return view('penjualan.create', compact('pelanggans', 'produks', 'kategoris'));
    }

    /**
     * Hitung diskon berdasarkan tanggal kadaluwarsa
     *
     * Aturan diskon bertingkat:
     * - H-7 atau kurang: Buy 1 Get 1 (diskon 50% karena dapat 2 item)
     *   * Jika stok ganjil, item terakhir: diskon 10% + gratis 1
     * - Minggu ke-2 (H-8 s.d. H-14): Diskon 5% + potongan Rp 3.000
     * - Minggu ke-3 (H-15 s.d. H-21): Diskon 5% + potongan Rp 2.000
     * - Minggu ke-4 (H-22 s.d. H-28): Diskon 5% + potongan Rp 1.000
     * - Lebih dari 28 hari: Tidak ada diskon
     *
     * @param string|null $tanggalKadaluwarsa - Tanggal kadaluwarsa produk
     * @return array - Array berisi info diskon (persentase, nominal, status BOGO, dll)
     */
    private function hitungDiskonExpiry($tanggalKadaluwarsa)
    {
        // Jika tidak ada tanggal kadaluwarsa, return diskon 0
        if (!$tanggalKadaluwarsa) {
            return [
                'persentase' => 0,
                'potongan_nominal' => 0,
                'is_bogo' => false,
                'hari_sisa' => null,
                'minggu' => null
            ];
        }

        // Hitung selisih hari antara sekarang dan tanggal kadaluwarsa
        $now = Carbon::now();
        $expiry = Carbon::parse($tanggalKadaluwarsa);
        $hariSisa = $now->diffInDays($expiry, false); // false = bisa negatif jika sudah lewat

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
        // Customer beli 1 dapat 2, jadi diskon efektif 50%
        if ($hariSisa <= 7) {
            return [
                'persentase' => 50, // Karena dapat 2 item dengan harga 1
                'potongan_nominal' => 0,
                'is_bogo' => true, // Flag untuk identifikasi promo BOGO
                'hari_sisa' => $hariSisa,
                'minggu' => 1,
                'bonus_qty' => 1, // Bonus 1 item gratis
                'status' => 'bogo',
                // ✅ UNTUK STOK GANJIL (ITEM TERAKHIR)
                // Jika stok ganjil, item terakhir dapat diskon 10% tambahan
                'diskon_stok_ganjil' => 10 // Diskon 10% untuk item terakhir
            ];
        }

        // Minggu ke-2 (H-8 s.d. H-14)
        // Diskon kombinasi: 5% + potongan Rp 3.000
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
        // Diskon kombinasi: 5% + potongan Rp 2.000
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
        // Diskon kombinasi: 5% + potongan Rp 1.000
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
        // Produk masih fresh, jual dengan harga normal
        return [
            'persentase' => 0,
            'potongan_nominal' => 0,
            'is_bogo' => false,
            'hari_sisa' => $hariSisa,
            'minggu' => null,
            'status' => 'normal'
        ];
    }

    /**
     * Menyimpan transaksi penjualan baru
     *
     * Proses:
     * 1. Validasi input
     * 2. Loop 1: Validasi stok dan hitung total (termasuk diskon expiry)
     * 3. Hitung diskon member (jika ada)
     * 4. Validasi pembayaran
     * 5. Simpan data penjualan
     * 6. Loop 2: Kurangi stok dengan sistem FEFO dan simpan detail
     * 7. Simpan pembayaran
     * 8. Commit transaksi
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'pelanggan_id'   => 'nullable', // Opsional, untuk customer umum tidak perlu
            'produk_id.*'    => 'required', // Array produk yang dibeli
            'jumlah_produk.*'=> 'required|integer|min:1', // Jumlah per produk minimal 1
            'metode'         => 'required|in:cash,qris', // Metode pembayaran hanya cash atau qris
            'jumlah_bayar'   => 'required|numeric|min:0', // Jumlah uang yang dibayarkan
        ]);

        // Mulai database transaction untuk memastikan data konsisten
        DB::beginTransaction();
        try {
            // Inisialisasi variabel untuk perhitungan
            $total = 0; // Total harga sebelum diskon member
            $totalDiskonExpiry = 0; // Total diskon dari produk mendekati kadaluwarsa
            $itemDetails = []; // Simpan detail untuk processing

            // Loop 1: Validasi stok dan hitung total
            // Loop pertama untuk validasi sebelum melakukan perubahan ke database
            foreach ($request->produk_id as $key => $produk_id) {
                // Ambil data produk
                $produk = Produks::findOrFail($produk_id);
                $jumlah = $request->jumlah_produk[$key];

                // Ambil batch terdekat untuk cek diskon expiry
                // FEFO: ambil batch yang paling dekat kadaluwarsa dan masih ada stok
                $batchTerdekat = BatchProduk::where('produk_id', $produk_id)
                    ->where('stok', '>', 0)
                    ->orderBy('kadaluwarsa', 'asc') // Urutkan dari yang paling dekat kadaluwarsa
                    ->first();

                // Jika tidak ada batch aktif, rollback dan error
                if (!$batchTerdekat) {
                    DB::rollBack();
                    return back()->with('error', "Produk {$produk->nama_produk} tidak memiliki batch aktif!");
                }

                // Hitung diskon berdasarkan tanggal kadaluwarsa
                $diskonExpiry = $this->hitungDiskonExpiry($batchTerdekat->kadaluwarsa);

                // ✅ JIKA BOGO, STOK YANG DIBUTUHKAN = JUMLAH BELI x 2
                // Karena customer beli 1 dapat 2, maka stok yang keluar adalah 2x lipat
                $jumlahDibutuhkan = $diskonExpiry['is_bogo'] ? $jumlah * 2 : $jumlah;

                // Cek stok dengan jumlah yang dibutuhkan (untuk BOGO = jumlah x 2)
                $stokTersedia = BatchProduk::where('produk_id', $produk_id)->sum('stok');

                // Validasi: apakah stok mencukupi?
                if ($stokTersedia < $jumlahDibutuhkan) {
                    $pesanError = "Stok {$produk->nama_produk} tidak mencukupi! Tersedia: {$stokTersedia}";
                    // Jika BOGO aktif, berikan info tambahan
                    if ($diskonExpiry['is_bogo']) {
                        $pesanError .= " (BOGO aktif: beli {$jumlah} butuh stok {$jumlahDibutuhkan})";
                    }
                    DB::rollBack();
                    return back()->with('error', $pesanError);
                }

                // Hitung harga setelah diskon expiry
                $hargaAsli = $produk->harga_jual;
                // Hitung diskon persentase
                $diskonPersenExpiry = ($hargaAsli * $diskonExpiry['persentase'] / 100);
                // Hitung harga setelah dikurangi diskon persentase dan nominal
                $hargaSetelahDiskon = $hargaAsli - $diskonPersenExpiry - $diskonExpiry['potongan_nominal'];

                // Pastikan harga tidak negatif (safety check)
                $hargaSetelahDiskon = max($hargaSetelahDiskon, 0);

                // Subtotal = harga sudah diskon × jumlah yang dibeli (bukan yang keluar dari stok)
                $subtotal = $hargaSetelahDiskon * $jumlah; // Harga sudah diskon, qty masih original
                $total += $subtotal;

                // Total diskon expiry untuk item ini
                // Selisih antara harga asli dan harga setelah diskon
                $diskonItemExpiry = ($hargaAsli * $jumlah) - $subtotal;
                $totalDiskonExpiry += $diskonItemExpiry;

                // Simpan detail untuk diproses nanti (di Loop 2)
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
            $alasanDiskonMember = []; // Untuk info ke user

            if ($request->pelanggan_id) {
                // Hitung total jumlah item yang dibeli
                $totalJumlahProduk = array_sum($request->jumlah_produk);

                // Diskon member 5% jika beli >= 10 item
                if ($totalJumlahProduk >= 10) {
                    $diskonMember += ($total * 0.05); // Tambah diskon 5%
                    $alasanDiskonMember[] = "Pembelian {$totalJumlahProduk} item (≥10)";
                }

                // Diskon member 5% jika belanja >= Rp 100.000
                if ($total >= 100000) {
                    $diskonMember += ($total * 0.05); // Tambah diskon 5% lagi
                    $alasanDiskonMember[] = "Belanja ≥ Rp 100.000";
                }

                // Cap maksimal diskon member tidak melebihi total
                // Safety: diskon tidak boleh lebih besar dari total belanja
                $diskonMember = min($diskonMember, $total);
            }

            // Total diskon = diskon expiry + diskon member
            $totalDiskon = $totalDiskonExpiry + $diskonMember;
            // Total yang harus dibayar setelah semua diskon
            $totalSetelahDiskon = $total - $diskonMember; // Diskon expiry sudah masuk di $total

            // Validasi jumlah bayar
            // Untuk cash: jumlah bayar harus >= total
            if ($request->metode == 'cash' && $request->jumlah_bayar < $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Jumlah bayar kurang dari total harga setelah diskon!');
            }

            // Untuk QRIS: jumlah bayar harus sama persis dengan total
            if ($request->metode == 'qris' && $request->jumlah_bayar != $totalSetelahDiskon) {
                DB::rollBack();
                return back()->with('error', 'Untuk pembayaran QRIS, jumlah bayar harus sama dengan total!');
            }

            // Simpan penjualan ke database
            $penjualan = Penjualans::create([
                'tanggal_penjualan' => now(), // Timestamp sekarang
                'total_harga'       => $total, // Total sebelum diskon member
                'diskon'            => $totalDiskon, // Total semua diskon (expiry + member)
                'pelanggan_id'      => $request->pelanggan_id, // Null jika customer umum
                'user_id'           => Auth::id(), // User yang melakukan transaksi
            ]);

            // Loop 2: Process setiap item
            // Setelah penjualan tersimpan, proses pengurangan stok dan detail
            foreach ($itemDetails as $item) {
                $produk = $item['produk'];
                $jumlah = $item['jumlah']; // Jumlah yang dibeli (input customer)
                $jumlahDibutuhkan = $item['jumlah_dibutuhkan']; // Stok yang dikurangi (BOGO = x2)
                $subtotal = $item['subtotal'];
                $diskonExpiry = $item['diskon_expiry'];

                // Catat stok sebelum dikurangi (untuk history)
                $stokSebelum = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                // ✅ KURANGI STOK SESUAI JUMLAH YANG DIBUTUHKAN (UNTUK BOGO = JUMLAH x 2)
                // Fungsi kurangiStokFEFO akan mengambil dari batch dengan kadaluwarsa terdekat
                try {
                    $batchDipakai = BatchProduk::kurangiStokFEFO($produk->produk_id, $jumlahDibutuhkan);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->with('error', $e->getMessage());
                }

                // Update stok produk utama dari total batch
                $produk->updateStokFromBatch();
                // Catat stok setelah dikurangi
                $stokSesudah = BatchProduk::where('produk_id', $produk->produk_id)->sum('stok');

                // Simpan detail penjualan per batch
                // Satu produk bisa diambil dari beberapa batch jika batch pertama tidak cukup
                foreach ($batchDipakai as $batchInfo) {
                    // Hitung subtotal proporsional untuk batch ini
                    $subtotalBatch = ($batchInfo['jumlah'] / $jumlahDibutuhkan) * $subtotal;

                    // Informasi diskon untuk keterangan
                    $infoDiskon = '';
                    if ($diskonExpiry['is_bogo']) {
                        $totalItemDidapat = $batchInfo['jumlah']; // Sudah dikalikan 2 di kurangiStokFEFO
                        $infoDiskon = " [BOGO - Beli " . ($batchInfo['jumlah'] / 2) . " dapat {$batchInfo['jumlah']} item]";
                    } elseif ($diskonExpiry['persentase'] > 0 || $diskonExpiry['potongan_nominal'] > 0) {
                        $infoDiskon = " [Diskon Expiry: {$diskonExpiry['persentase']}% + Rp " . number_format($diskonExpiry['potongan_nominal']) . "]";
                    }

                    // Simpan ke tabel detail_penjualans
                    DetailPenjualans::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'produk_id' => $produk->produk_id,
                        'batch_id' => $batchInfo['batch_id'],
                        'jumlah_produk' => $batchInfo['jumlah'], // ✅ Simpan jumlah SEBENARNYA yang keluar dari stok
                        'subtotal' => $subtotalBatch,
                        'barcode_batch' => $batchInfo['barcode_batch'],
                        'kadaluwarsa_batch' => $batchInfo['kadaluwarsa'],
                    ]);

                    // Ambil info pelanggan untuk keterangan history
                    $pelanggan = $request->pelanggan_id
                        ? Pelanggans::find($request->pelanggan_id)
                        : null;

                    // Buat keterangan yang informatif
                    $keterangan = $pelanggan
                        ? "Penjualan kepada {$pelanggan->nama_pelanggan} - Batch: {$batchInfo['barcode_batch']} (FEFO){$infoDiskon}"
                        : "Penjualan (Umum) - Batch: {$batchInfo['barcode_batch']} (FEFO){$infoDiskon}";

                    // Catat ke stock history untuk tracking
                    StockHistory::create([
                        'produk_id' => $produk->produk_id,
                        'tipe' => 'keluar', // Stok keluar karena terjual
                        'jumlah' => $batchInfo['jumlah'], // ✅ Catat jumlah SEBENARNYA
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'keterangan' => $keterangan,
                        'referensi_tipe' => 'penjualan',
                        'referensi_id' => $penjualan->penjualan_id,
                        'user_id' => Auth::id(),
                    ]);

                    // Update stok sebelum untuk iterasi berikutnya (jika ada)
                    $stokSebelum = $stokSesudah;
                }
            }

            // Simpan pembayaran
            Pembayarans::create([
                'penjualan_id'      => $penjualan->penjualan_id,
                'metode'            => $request->metode,
                'jumlah'            => $request->jumlah_bayar,
                // Kembalian hanya untuk cash, untuk QRIS = 0
                'kembalian'         => $request->metode == 'cash' ? ($request->jumlah_bayar - $totalSetelahDiskon) : 0,
                'tanggal_pembayaran'=> now(),
                // Generate referensi unik untuk QRIS
                'qris_reference'    => $request->metode == 'qris' ? 'QRIS-' . time() . '-' . $penjualan->penjualan_id : null,
            ]);

            // Commit semua perubahan ke database
            DB::commit();

            // Buat pesan sukses dengan info diskon
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

            // Redirect dengan session data untuk auto print
            return redirect()->route('penjualan.create')->with([
                'success' => $pesanSukses,
                'penjualan_id' => $penjualan->penjualan_id,
                'metode_pembayaran' => $request->metode,
                'total_bayar' => $totalSetelahDiskon,
                'auto_print' => true  // 🔥 TAMBAHIN INI UNTUK AUTO PRINT
            ]);

        } catch (\Exception $e) {
            // Jika terjadi error, rollback semua perubahan
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cetak struk penjualan
     *
     * @param int $id - ID penjualan
     * @return \Illuminate\View\View
     */
    public function cetakStruk($id)
    {
        // Ambil data penjualan lengkap dengan semua relasinya
        $penjualan = Penjualans::with([
            'pelanggan',
            'user',
            'detailPenjualans.produk',
            'detailPenjualans.batch',
            'pembayaran'
        ])->findOrFail($id);

        // Return view struk (biasanya dengan layout minimal untuk print)
        return view('laporan.struk', compact('penjualan'));
    }

    /**
     * Generate QRIS dinamis dengan nominal transaksi
     * Menggunakan algoritma CRC16 untuk validasi QRIS
     *
     * @param float $amount - Nominal transaksi
     * @return string - QRIS string yang sudah di-encode
     */
    public function generateQRIS($amount)
    {
        // QRIS static dari merchant (didapat dari registrasi QRIS)
        $STATIC_QRIS = "00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214844519767362640303UMI51440014ID.CO.QRIS.WWW0215ID20243345184510303UMI5204541153033605802ID5920YANTO SHOP OK18846346005DEPOK61051641162070703A0163046879";

        /**
         * Helper function untuk mendapatkan character code pada index tertentu
         * Equivalen dengan charCodeAt di JavaScript
         */
        function charCodeAt($str, $index) {
            return ord($str[$index]);
        }

        /**
         * Konversi string ke CRC16 checksum
         * Algoritma untuk validasi integritas data QRIS
         *
         * @param string $str - String yang akan di-hash
         * @return string - 4 digit hexadecimal checksum
         */
        function ConvertCRC16($str) {
            $crc = 0xFFFF; // Initial value
            // Loop setiap karakter
            for ($c = 0; $c < strlen($str); $c++) {
                $crc ^= charCodeAt($str, $c) << 8;
                // Loop 8 bit
                for ($i = 0; $i < 8; $i++) {
                    $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
                }
            }
            // Konversi ke hex dan uppercase
            $hex = strtoupper(dechex($crc & 0xFFFF));
            // Pad dengan 0 jika kurang dari 4 digit
            return strlen($hex) === 3 ? '0' . $hex : str_pad($hex, 4, '0', STR_PAD_LEFT);
        }

        // Hapus CRC lama (4 karakter terakhir)
        $qris = substr($STATIC_QRIS, 0, -4);
        // Ubah dari static (010211) ke dynamic (010212)
        $step1 = str_replace("010211", "010212", $qris);
        // Split berdasarkan marker country code
        $step2 = explode("5802ID", $step1);
        // Format nominal: 54 + panjang amount (2 digit) + amount
        $uang = "54" . str_pad(strlen($amount), 2, '0', STR_PAD_LEFT) . $amount;
        $uang .= "5802ID";
        // Gabungkan kembali
        $fix = trim($step2[0]) . $uang . trim($step2[1]);
        // Generate CRC baru dan append
        $finalQR = $fix . ConvertCRC16($fix);

        // Return QRIS string siap di-encode ke QR code
        return $finalQR;
    }

    /**
     * Menampilkan detail penjualan
     *
     * @param string $id - ID penjualan
     * @return \Illuminate\View\View
     */
    public function show(string $id)
    {
        // Ambil data penjualan dengan semua relasi
        $penjualan = Penjualans::with([
            'pelanggan',
            'user',
            'detailPenjualans.produk',
            'detailPenjualans.batch',
            'pembayaran'
        ])->findOrFail($id);

        // Return view detail
        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Menampilkan form edit penjualan
     * (Belum diimplementasi)
     *
     * @param string $id - ID penjualan yang akan diedit
     * @return void
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update data penjualan
     * (Belum diimplementasi)
     *
     * @param Request $request - Data update dari form
     * @param string $id - ID penjualan yang akan diupdate
     * @return void
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Hapus penjualan dan kembalikan stok
     *
     * Proses:
     * 1. Ambil data penjualan beserta detail
     * 2. Loop semua detail untuk kembalikan stok ke batch asalnya
     * 3. Update stok produk utama
     * 4. Catat ke stock history sebagai koreksi
     * 5. Hapus penjualan (cascade delete detail dan pembayaran)
     *
     * PENTING: Fungsi ini mengembalikan stok ke batch yang SAMA dengan saat penjualan
     * untuk menjaga akurasi tracking FEFO
     *
     * @param string $id - ID penjualan yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        // Ambil data penjualan yang akan dihapus
        // findOrFail akan throw 404 jika tidak ditemukan
        $penjualan = Penjualans::findOrFail($id);

        // Mulai transaction untuk memastikan konsistensi data
        DB::beginTransaction();

        try {
            // Loop semua detail penjualan untuk kembalikan stok
            // detailPenjualans adalah relasi hasMany dari model Penjualans
            foreach ($penjualan->detailPenjualans as $detail) {
                // Jika ada batch_id (penjualan menggunakan sistem batch)
                // batch_id = null jika penjualan sebelum implementasi batch
                if ($detail->batch_id) {
                    // Ambil batch yang digunakan saat penjualan
                    $batch = BatchProduk::find($detail->batch_id);

                    // Jika batch masih ada (belum dihapus)
                    if ($batch) {
                        // Catat stok sebelum dikembalikan (untuk stock history)
                        $stokSebelum = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');

                        // Kembalikan stok ke batch yang sama
                        // Stok batch ditambah dengan jumlah yang terjual
                        $batch->stok += $detail->jumlah_produk;
                        $batch->save();

                        // Catat stok setelah dikembalikan
                        $stokSesudah = BatchProduk::where('produk_id', $batch->produk_id)->sum('stok');

                        // Update stok produk utama (aggregate dari semua batch)
                        $batch->produk->updateStokFromBatch();

                        // Catat ke stock history sebagai koreksi
                        // Ini penting untuk audit trail dan tracking
                        StockHistory::create([
                            'produk_id' => $batch->produk_id,
                            'tipe' => 'masuk', // Stok masuk kembali karena penjualan dibatalkan
                            'jumlah' => $detail->jumlah_produk,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => 'Koreksi: Hapus penjualan #' . $penjualan->penjualan_id . ' (FEFO)',
                            'referensi_tipe' => 'penjualan', // Referensi ke tipe transaksi
                            'referensi_id' => $penjualan->penjualan_id, // ID penjualan yang dihapus
                            'user_id' => Auth::id(), // User yang melakukan penghapusan
                        ]);
                    }
                }
            }

            // Hapus penjualan
            // Jika ada foreign key cascade, detail dan pembayaran akan ikut terhapus
            // Jika tidak, perlu hapus manual detail dan pembayaran terlebih dahulu
            $penjualan->delete();

            // Commit transaction jika semua proses berhasil
            DB::commit();

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus dan stok dikembalikan (sistem FEFO)');

        } catch (\Exception $e) {
            // Rollback semua perubahan jika ada error
            DB::rollback();

            // Redirect kembali dengan pesan error
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
