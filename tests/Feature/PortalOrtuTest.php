<?php

namespace Tests\Feature;

use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * PORTAL ORANG TUA (/ortu) — masuk dengan NISN + tanggal lahir anak (ddmmyyyy).
 * Yang dijaga di sini: anak yang benar bisa masuk, sandi salah tidak bisa,
 * halaman tidak bisa dibuka tanpa masuk, dan data anak lain tidak pernah bocor.
 */
class PortalOrtuTest extends TestCase
{
    use RefreshDatabase;

    private function buatSiswa(array $ubah = []): Siswa
    {
        return Siswa::create(array_merge([
            'nama_lengkap' => 'Ahmad Contoh',
            'nisn'         => '0093905443',
            'ttl'          => 'Banyuwangi 24 Mei 2009',
            'kelas'        => 'XII',
            'jk'           => 'Laki-laki',
            'status'       => 'Aktif',
        ], $ubah));
    }

    public function test_halaman_masuk_bisa_dibuka(): void
    {
        $this->get('/ortu')
            ->assertOk()
            ->assertSee('Portal Orang Tua')
            ->assertSee('NISN');
    }

    public function test_masuk_dengan_tanggal_lahir_anak_membuka_dasbor(): void
    {
        $siswa = $this->buatSiswa();

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009'])
            ->assertRedirect(route('ortu.dasbor'));

        $this->assertSame($siswa->id, session('ortu_siswa_id'));

        $this->get('/ortu/dasbor')
            ->assertOk()
            ->assertSee('Ahmad Contoh')
            ->assertSee('Perkembangan ananda');
    }

    public function test_rapor_orang_tua_memanggil_rapor_resmi(): void
    {
        $this->buatSiswa();

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);

        // Halaman pilihan rapor (memanggil rapor resmi yang sudah ada).
        $this->get('/ortu/rapor')
            ->assertOk()
            ->assertSee('Rapor ananda')
            ->assertSee('Rapor Student Root')
            ->assertSee('Rapot Adab')
            ->assertSee('Rapor Diniyah');

        // Rapor Student Root resmi (dari SrRaporController) untuk anak sendiri.
        // Catatan: pada sqlite (basis data uji) halaman ini tidak bisa dirender karena
        // rekap tren bulanan memakai DATE_FORMAT (khusus MySQL). Rendering sungguhan
        // diuji terhadap MySQL lokal & produksi.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->get('/ortu/rapor/student-root')
                ->assertOk()
                ->assertSee('Rapor Student Root')
                ->assertSee('Ahmad Contoh');
        }

        // Rapot Adab & Keasramaan resmi (dari PenilaianRaporController).
        // Peran "Super Admin" harus ada karena halaman itu menyaring penilai admin.
        \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');

        $this->get('/ortu/rapor/adab')
            ->assertOk()
            ->assertSee('Adab');
    }

    public function test_wali_murid_hanya_boleh_melihat_tidak_bisa_mencetak(): void
    {
        $this->buatSiswa();
        \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);

        // Tidak ada tombol cetak/tampilkan periode yang khas petugas di halaman wali murid.
        $this->get('/ortu/rapor/adab')
            ->assertOk()
            ->assertDontSee('window.print()')
            ->assertDontSee('Cetak / Simpan PDF')
            ->assertSee('hanya untuk dilihat', false);
    }

    public function test_wali_murid_tidak_bisa_membuka_rapor_anak_lain(): void
    {
        $this->buatSiswa();
        $this->buatSiswa(['nisn' => '0091111111', 'nama_lengkap' => 'Zulfa Anak Lain', 'ttl' => 'Banjarmasin 5 Jan 2010']);

        \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);

        // Parameter apa pun tidak bisa memindahkan halaman ke anak lain:
        // id anak diambil dari sesi NISN, bukan dari URL.
        $this->get('/ortu/rapor/adab?siswa=2&kamar=1')
            ->assertOk()
            ->assertSee('Ahmad Contoh')
            ->assertDontSee('Zulfa Anak Lain');

        $this->get('/ortu/rapor?siswa=2')
            ->assertOk()
            ->assertSee('Ahmad Contoh')
            ->assertDontSee('Zulfa Anak Lain');
    }

    public function test_halaman_rapor_tidak_bisa_dibuka_tanpa_masuk(): void
    {
        $this->get('/ortu/rapor')->assertRedirect(route('ortu.masuk'));
        $this->get('/ortu/rapor/student-root')->assertRedirect(route('ortu.masuk'));
        $this->get('/ortu/rapor/adab')->assertRedirect(route('ortu.masuk'));
    }

    public function test_sandi_salah_tidak_membuka_dasbor(): void
    {
        $this->buatSiswa();

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '01011990'])
            ->assertSessionHasErrors('sandi');

        $this->assertNull(session('ortu_siswa_id'));

        $this->get('/ortu/dasbor')->assertRedirect(route('ortu.masuk'));
    }

    public function test_nisn_tidak_dikenal_memberi_pesan_yang_sama_dengan_sandi_salah(): void
    {
        $this->buatSiswa();

        $jawaban = $this->post('/ortu/masuk', ['nisn' => '0000000001', 'sandi' => '24052009']);
        $pesan = $jawaban->getSession()->get('errors')->first('sandi');

        $this->assertStringContainsString('NISN atau kata sandi tidak cocok', $pesan);
    }

    public function test_siswa_tanpa_tanggal_lahir_diberi_pesan_khusus(): void
    {
        $this->buatSiswa(['nisn' => '0099999999', 'nama_lengkap' => 'Tanpa Tanggal', 'ttl' => '']);

        $jawaban = $this->post('/ortu/masuk', ['nisn' => '0099999999', 'sandi' => '24052009']);

        $jawaban->assertSessionHasErrors('sandi');
        $this->assertStringContainsString('Data tanggal lahir', $jawaban->getSession()->get('errors')->first('sandi'));
        $this->assertNull(session('ortu_siswa_id'));
    }

    public function test_dasbor_tidak_bisa_dibuka_tanpa_masuk(): void
    {
        $this->get('/ortu/dasbor')->assertRedirect(route('ortu.masuk'));
        $this->get('/ortu/rapor')->assertRedirect(route('ortu.masuk'));
    }

    public function test_orang_tua_hanya_melihat_anaknya_sendiri(): void
    {
        $this->buatSiswa();
        $this->buatSiswa(['nisn' => '0091111111', 'nama_lengkap' => 'Zulfa Anak Lain', 'ttl' => 'Banjarmasin 5 Jan 2010']);

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);

        $this->get('/ortu/dasbor')
            ->assertOk()
            ->assertSee('Ahmad Contoh')
            ->assertDontSee('Zulfa Anak Lain');
    }

    public function test_keluar_menghapus_sesi(): void
    {
        $this->buatSiswa();

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);
        $this->post('/ortu/keluar')->assertRedirect(route('ortu.masuk'));

        $this->assertNull(session('ortu_siswa_id'));
        $this->get('/ortu/dasbor')->assertRedirect(route('ortu.masuk'));
    }

    public function test_terlalu_banyak_percobaan_diblokir(): void
    {
        $this->buatSiswa(['nisn' => '0092222222', 'ttl' => 'Banyuwangi 24 Mei 2009']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/ortu/masuk', ['nisn' => '0092222222', 'sandi' => '11111111']);
        }

        // Percobaan ke-6 — walaupun sandinya benar — harus ditolak sementara.
        $this->post('/ortu/masuk', ['nisn' => '0092222222', 'sandi' => '24052009'])
            ->assertSessionHasErrors('sandi');

        $this->assertNull(session('ortu_siswa_id'));
    }

    public function test_sandi_berupa_angka_bertitik_atau_spasi_tetap_diterima(): void
    {
        $this->buatSiswa();

        // Orang tua sering mengetik "24-05-2009" atau "24 05 2009".
        $this->post('/ortu/masuk', ['nisn' => '0093 905 443', 'sandi' => '24-05-2009'])
            ->assertRedirect(route('ortu.dasbor'));

        $this->assertNotNull(session('ortu_siswa_id'));
    }

    public function test_kolom_tanggal_lahir_baru_dipakai_untuk_sandi(): void
    {
        // Petugas mengisi lewat pemilih kalender; teks lama `ttl` boleh keliru/tidak rapi.
        $this->buatSiswa([
            'nisn' => '0095555555',
            'nama_lengkap' => 'Kalender Baru',
            'ttl' => 'Sampit (belum rapi)',
            'tanggal_lahir' => '2011-03-08',
        ]);

        $this->post('/ortu/masuk', ['nisn' => '0095555555', 'sandi' => '08032011'])
            ->assertRedirect(route('ortu.dasbor'));

        $this->assertNotNull(session('ortu_siswa_id'));
    }
}
