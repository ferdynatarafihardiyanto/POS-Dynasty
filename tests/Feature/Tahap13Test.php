<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\CafeTable;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\BahanBaku;
use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\RiwayatStok;

class Tahap13Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'KategoriSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProdukSeeder']);
        $this->artisan('db:seed', ['--class' => 'CafeTableSeeder']);
    }

    private function getAdminToken()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        if (!$user) {
            $user = User::factory()->create(['email' => 'admin@cafe.test', 'role' => 'admin']);
        }
        return $user->createToken('test')->plainTextToken;
    }

    private function adminHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAdminToken(),
            'Accept' => 'application/json',
        ];
    }

    private function getMeja()
    {
        $meja = CafeTable::first();
        if (!$meja) {
            $meja = CafeTable::create(['table_number' => '1', 'name' => 'Meja 1', 'status' => 'active', 'qr_token' => 'test-token']);
        }
        $meja->update(['status' => 'active', 'qr_token' => 'test-token']);
        return $meja;
    }

    private function getProdukTanpaResep()
    {
        $produk = Produk::first();
        if (!$produk) {
            $produk = Produk::create(['nama' => 'Test', 'harga' => 10000, 'kategori_id' => 1, 'aktif' => true, 'stok' => 100]);
        }
        $produk->update(['aktif' => true, 'stok' => 100]);
        return $produk;
    }

    private function getProdukDenganResep()
    {
        $kopi = BahanBaku::firstOrCreate(['nama' => 'Kopi Test'], ['harga_beli' => 100, 'satuan' => 'gram', 'stok' => 1000, 'stok_minimum' => 10, 'aktif' => true]);
        $susu = BahanBaku::firstOrCreate(['nama' => 'Susu Test'], ['harga_beli' => 20, 'satuan' => 'ml', 'stok' => 2000, 'stok_minimum' => 10, 'aktif' => true]);
        
        $produk = Produk::firstOrCreate(['nama' => 'Kopi Susu Test'], ['harga' => 15000, 'kategori_id' => 1, 'aktif' => true, 'stok' => 0]);
        $produk->update(['aktif' => true, 'stok' => 0]); // Pastikan 0

        $resep = Resep::firstOrCreate(['produk_id' => $produk->id]);
        if ($resep->detail()->count() == 0) {
            ResepDetail::create(['resep_id' => $resep->id, 'bahan_baku_id' => $kopi->id, 'jumlah' => 20]);
            ResepDetail::create(['resep_id' => $resep->id, 'bahan_baku_id' => $susu->id, 'jumlah' => 100]);
        }

        $kopi->update(['stok' => 1000, 'aktif' => true]);
        $susu->update(['stok' => 2000, 'aktif' => true]);
        
        return [$produk, $kopi, $susu];
    }

    // --- GROUP A: CUSTOMER ---

    public function test_01_qr_meja_berhasil()
    {
        $meja = $this->getMeja();
        $response = $this->getJson("/api/meja/{$meja->qr_token}");
        $response->assertStatus(200);
    }

    public function test_02_menu_customer_berhasil()
    {
        $meja = $this->getMeja();
        $response = $this->getJson("/api/menu/meja/{$meja->qr_token}");
        $response->assertStatus(200);
    }

    public function test_03_customer_checkout_berhasil()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('pesanan', ['meja_id' => $meja->id]);
    }

    public function test_04_customer_tidak_dapat_memanipulasi_harga()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 1, 'harga' => 1] // Manipulate
            ]
        ]);
        $response->assertStatus(201);
        $pesananId = $response->json('data.nomor_pesanan');
        $pesanan = Pesanan::where('nomor_pesanan', $pesananId)->first();
        
        // Backend harus pakai harga asli
        $this->assertEquals($produk->harga, $pesanan->total_harga);
    }

    public function test_05_produk_nonaktif_ditolak()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();
        $produk->update(['aktif' => false]);

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $response->assertStatus(422);
    }

    public function test_06_qr_token_palsu_ditolak()
    {
        $produk = $this->getProdukTanpaResep();
        $response = $this->postJson("/api/pesanan", [
            'qr_token' => 'fake-token-123',
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $response->assertStatus(404);
    }

    public function test_07_meja_nonaktif_ditolak()
    {
        $meja = $this->getMeja();
        $meja->update(['status' => 'inactive']);
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $response->assertStatus(422);
    }

    // --- GROUP B: PRODUK TANPA RESEP ---

    public function test_08_produk_tanpa_resep_mengurangi_stok_produk()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();
        $stokAwal = $produk->stok;

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $payRes = $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $payRes->assertStatus(200);

        $produk->refresh();
        $this->assertEquals($stokAwal - 2, $produk->stok);
    }

    public function test_09_penjualan_produk_mencatat_riwayat_stok()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();
        $produk = $this->getProdukTanpaResep();
        $this->assertDatabaseHas('riwayat_stok', [
            'produk_id' => $produk->id,
            'bahan_baku_id' => null,
            'jenis' => 'keluar'
        ]);
    }

    // --- GROUP C: PRODUK DENGAN RESEP ---

    public function test_10_produk_dengan_resep_mengurangi_bahan_baku()
    {
        $meja = $this->getMeja();
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();

        $stokKopiAwal = $kopi->stok; // 1000
        $stokSusuAwal = $susu->stok; // 2000

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $payRes = $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $payRes->assertStatus(200);

        $kopi->refresh();
        $susu->refresh();

        $this->assertEquals($stokKopiAwal - (20 * 2), $kopi->stok);
        $this->assertEquals($stokSusuAwal - (100 * 2), $susu->stok);
    }

    public function test_11_produk_dengan_resep_tidak_mengurangi_produk_stok()
    {
        $meja = $this->getMeja();
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        
        $stokProdukAwal = $produk->stok;

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $produk->refresh();
        $this->assertEquals($stokProdukAwal, $produk->stok); // Harus tetap 0
    }

    public function test_12_hpp_tersimpan_sebagai_snapshot()
    {
        $meja = $this->getMeja();
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $detail = DetailPesanan::where('pesanan_id', $pesanan->id)->first();
        // HPP = (100 * 20) + (20 * 100) = 2000 + 2000 = 4000
        $this->assertEquals(4000, $detail->hpp);
    }

    public function test_13_hpp_transaksi_lama_tidak_berubah()
    {
        $this->test_12_hpp_tersimpan_sebagai_snapshot();
        $detailLama = DetailPesanan::latest('id')->first();
        
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        $kopi->update(['harga_beli' => 500]); // Ubah harga

        $detailLama->refresh();
        $this->assertEquals(4000, $detailLama->hpp); // Tetap 4000
    }

    public function test_14_harga_transaksi_lama_tidak_berubah()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();
        $detailLama = DetailPesanan::latest('id')->first();
        $hargaLama = $detailLama->harga;

        $produk = $this->getProdukTanpaResep();
        $produk->update(['harga' => 99999]);

        $detailLama->refresh();
        $this->assertEquals($hargaLama, $detailLama->harga);
    }

    // --- GROUP D: STOK ---

    public function test_15_stok_tidak_cukup_ditolak()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();
        $produk->update(['stok' => 1]);

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        
        $response->assertStatus(422);
        
        $produk->refresh();
        $this->assertEquals(1, $produk->stok);
    }

    public function test_16_bahan_baku_tidak_cukup_ditolak()
    {
        $meja = $this->getMeja();
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        $kopi->update(['stok' => 5]); // Butuh 20

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);

        $response->assertStatus(422);

        $kopi->refresh();
        $this->assertEquals(5, $kopi->stok);
    }

    public function test_17_transaksi_stock_payment_atomic()
    {
        // Karena test 16 gagal di tahap validasi pesanan, maka kita simulasikan kegagalan payment di level POS
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();
        
        // Buat pesanan dengan stok cukup
        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();
        
        // Buat stok habis SEBELUM pembayaran (simulasi concurrent)
        $produk->update(['stok' => 0]);
        
        // Coba bayar
        $payRes = $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());
        
        $payRes->assertStatus(422);
        $this->assertDatabaseMissing('pembayaran', ['pesanan_id' => $pesanan->id]);
    }

    public function test_18_double_payment_ditolak()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        // Payment 1
        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders())->assertStatus(200);

        // Payment 2
        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders())->assertStatus(422);
    }

    // --- GROUP E: PAYMENT ---

    public function test_19_pembayaran_cash_berhasil()
    {
        // Already tested in 08, 10
        $this->assertTrue(true);
    }

    public function test_20_pembayaran_qris_berhasil()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders())->assertStatus(200);

        $this->assertDatabaseHas('pembayaran', ['pesanan_id' => $pesanan->id, 'metode_pembayaran' => 'qris']);
    }

    public function test_21_pembayaran_transfer_berhasil()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'transfer',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders())->assertStatus(200);

        $this->assertDatabaseHas('pembayaran', ['pesanan_id' => $pesanan->id, 'metode_pembayaran' => 'transfer']);
    }

    public function test_22_metode_pembayaran_invalid_ditolak()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'bitcoin',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders())->assertStatus(422);
    }

    // --- GROUP F: INVENTORY ---

    public function test_23_stok_masuk_bahan_baku()
    {
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        $stokAwal = $kopi->stok;

        $response = $this->postJson("/api/admin/stok/masuk", [
            'bahan_baku_id' => $kopi->id,
            'jumlah' => 50,
            'keterangan' => 'Masuk'
        ], $this->adminHeaders());

        $response->assertStatus(200);
        $kopi->refresh();
        $this->assertEquals($stokAwal + 50, $kopi->stok);
    }

    public function test_24_stok_keluar_bahan_baku()
    {
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        $stokAwal = $kopi->stok;

        $response = $this->postJson("/api/admin/stok/keluar", [
            'bahan_baku_id' => $kopi->id,
            'jumlah' => 20,
            'keterangan' => 'Keluar'
        ], $this->adminHeaders());

        $response->assertStatus(200);
        $kopi->refresh();
        $this->assertEquals($stokAwal - 20, $kopi->stok);
    }

    public function test_25_stok_negatif_ditolak()
    {
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        $stokAwal = $kopi->stok;

        $response = $this->postJson("/api/admin/stok/keluar", [
            'bahan_baku_id' => $kopi->id,
            'jumlah' => $stokAwal + 10, // Melebihi stok
            'keterangan' => 'Keluar'
        ], $this->adminHeaders());

        $response->assertStatus(422);
        $kopi->refresh();
        $this->assertEquals($stokAwal, $kopi->stok);
    }

    public function test_26_stock_opname()
    {
        [$produk, $kopi, $susu] = $this->getProdukDenganResep();
        
        $response = $this->postJson("/api/admin/stock-opname", [
            'keterangan' => 'Test',
            'detail' => [
                ['bahan_baku_id' => $kopi->id, 'stok_fisik' => 50]
            ]
        ], $this->adminHeaders());
        
        $response->assertStatus(201);
        $opnameId = $response->json('data.id');

        $resSelesai = $this->postJson("/api/admin/stock-opname/{$opnameId}/selesai", [], $this->adminHeaders());
        $resSelesai->assertStatus(200);

        $kopi->refresh();
        $this->assertEquals(50, $kopi->stok);
    }

    // --- GROUP G: HISTORY ---

    public function test_27_history_transaksi()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();
        
        $response = $this->getJson("/api/admin/pos/pesanan", $this->adminHeaders());
        $response->assertStatus(200);
        
        // Asumsi admin POS melihat pesanan pending, kita cek detail pesanan
        $pesanan = Pesanan::where('status', 'dibayar')->first();
        $res = $this->getJson("/api/admin/pos/pesanan/{$pesanan->id}", $this->adminHeaders());
        $res->assertStatus(200);
    }

    // --- GROUP H: REPORTS ---

    public function test_28_laporan_harian()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();

        $tanggal = date('Y-m-d');
        $response = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.tanggal', $tanggal);
        $this->assertGreaterThan(0, $response->json('data.jumlah_transaksi'));
    }

    public function test_29_laporan_bulanan()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();

        $bulan = date('Y-m');
        $response = $this->getJson("/api/admin/laporan/bulanan?bulan={$bulan}", $this->adminHeaders());
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.bulan', $bulan);
        $this->assertGreaterThan(0, $response->json('data.jumlah_transaksi'));
    }

    public function test_30_trend_penjualan()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk();

        $bulan = date('Y-m');
        $response = $this->getJson("/api/admin/laporan/tren?bulan={$bulan}", $this->adminHeaders());
        
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_31_transaksi_gagal_tidak_masuk_laporan()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        // Pending
        $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        
        $tanggal = date('Y-m-d');
        $response = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        
        // Transaksi pending tidak ikut dihitung, jadi jumlah_transaksi = 0
        $this->assertEquals(0, $response->json('data.jumlah_transaksi'));
    }

    public function test_32_laporan_tanpa_transaksi()
    {
        $tanggal = '2099-01-01'; // Future date
        $response = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.jumlah_transaksi'));
        $this->assertEquals(0, $response->json('data.total_omzet'));
    }

    public function test_33_double_counting_ditangani()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();
        $produk2 = Produk::create(['kategori_id' => 1, 'nama' => 'Test 2', 'harga' => 5000, 'stok' => 10, 'aktif' => true]);

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 1],
                ['produk_id' => $produk2->id, 'jumlah' => 2]
            ]
        ]);
        $pesanan = Pesanan::where('nomor_pesanan', $response->json('data.nomor_pesanan'))->first();

        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $tanggal = date('Y-m-d');
        $laporan = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        
        // Harus tetap 1 transaksi, walaupun detailnya banyak
        $this->assertEquals(1, $laporan->json('data.jumlah_transaksi'));
        $this->assertEquals($pesanan->total_harga, $laporan->json('data.total_omzet'));
    }

    public function test_34_payment_method_dalam_laporan()
    {
        $this->test_08_produk_tanpa_resep_mengurangi_stok_produk(); // Cash
        $this->test_20_pembayaran_qris_berhasil(); // Qris
        $this->test_21_pembayaran_transfer_berhasil(); // Transfer
        
        $tanggal = date('Y-m-d');
        $laporan = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        
        $this->assertGreaterThan(0, $laporan->json('data.pembayaran.cash'));
        $this->assertGreaterThan(0, $laporan->json('data.pembayaran.qris'));
        $this->assertGreaterThan(0, $laporan->json('data.pembayaran.transfer'));
    }

    // --- GROUP I: AUTHORIZATION ---

    public function test_35_customer_tidak_dapat_mengakses_admin()
    {
        $tanggal = date('Y-m-d');
        $response = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}"); // Tanpa header auth
        $response->assertStatus(401);
    }

    public function test_36_admin_dapat_akses_laporan()
    {
        $tanggal = date('Y-m-d');
        $response = $this->getJson("/api/admin/laporan/harian?tanggal={$tanggal}", $this->adminHeaders());
        $response->assertStatus(200);
    }

    public function test_37_customer_tidak_boleh_manipulasi_hpp()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 1, 'hpp' => 1] // Manipulate
            ]
        ]);
        $pesananId = $response->json('data.nomor_pesanan');
        $pesanan = Pesanan::where('nomor_pesanan', $pesananId)->first();
        
        $this->postJson("/api/admin/pos/pembayaran", [
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $pesanan->total_harga
        ], $this->adminHeaders());

        $detail = DetailPesanan::where('pesanan_id', $pesanan->id)->first();
        // HPP diproses backend
        $this->assertEquals(0, $detail->hpp); // Tanpa resep default 0
    }

    public function test_38_customer_tidak_boleh_manipulasi_total()
    {
        $meja = $this->getMeja();
        $produk = $this->getProdukTanpaResep();

        $response = $this->postJson("/api/pesanan", [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]],
            'total_harga' => 1 // Manipulate
        ]);
        $pesananId = $response->json('data.nomor_pesanan');
        $pesanan = Pesanan::where('nomor_pesanan', $pesananId)->first();
        
        $this->assertEquals($produk->harga, $pesanan->total_harga);
    }
}
