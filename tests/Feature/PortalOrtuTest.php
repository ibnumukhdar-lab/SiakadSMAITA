<?php

namespace Tests\Feature;

use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_rapor_orang_tua_bisa_dibuka_setelah_masuk(): void
    {
        $this->buatSiswa();

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '24052009']);

        $this->get('/ortu/rapor')
            ->assertOk()
            ->assertSee('Rapor Perkembangan Karakter')
            ->assertSee('Ahmad Contoh');
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
}
