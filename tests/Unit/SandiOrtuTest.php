<?php

namespace Tests\Unit;

use App\Models\Siswa;
use App\Support\SandiOrtu;
use Tests\TestCase;

/**
 * Sandi portal orang tua = tanggal lahir anak (ddmmyyyy).
 * Dua sumber: kolom baru `tanggal_lahir` (pemilih kalender) dan kolom lama
 * `ttl` (teks bebas) sebagai cadangan. Ketiga bentuk teks nyata dari produksi
 * harus terbaca: nama bulan Indonesia, ISO dari impor, dan numerik.
 */
class SandiOrtuTest extends TestCase
{
    public function test_membaca_format_produksi_yang_beragam(): void
    {
        $contoh = [
            'Banyuwangi 24 Mei 2009'                 => '24052009',
            'Kotawaringin Timur 14 Januari 2009'     => '14012009',
            'bekasi 06 agustus 2008'                 => '06082008',
            'Yogyakarta  27 Mei 2009'                => '27052009',
            'Pematang Siantar, 8 Maret 2011'         => '08032011',
            'Kotawaringin Timur 12 Agustus 2009'     => '12082009',
            'Jakarta 1 Desember 2010'                => '01122010',
            'Sukamara 30 September 2011'             => '30092011',
        ];

        foreach ($contoh as $ttl => $sandi) {
            $this->assertSame($sandi, SandiOrtu::dariTtl($ttl), "gagal membaca: {$ttl}");
        }
    }

    public function test_membaca_bentuk_iso_hasil_impor(): void
    {
        // 22 siswa produksi berbentuk begini (impor) dan sebelumnya TIDAK bisa masuk.
        $this->assertSame('23112009', SandiOrtu::dariTtl('Bandung 2009-11-23 00:00:00'));
        $this->assertSame('14112010', SandiOrtu::dariTtl('Telaga Pulang 2010-11-14 00:00:00'));
        $this->assertSame('21092010', SandiOrtu::dariTtl('Banyuwangi 2010-09-21 00:00:00'));
    }

    public function test_membaca_bentuk_numerik(): void
    {
        $this->assertSame('24052009', SandiOrtu::dariTtl('Banyuwangi 24/05/2009'));
        $this->assertSame('24052009', SandiOrtu::dariTtl('Banyuwangi 24-05-2009'));
        $this->assertSame('24052009', SandiOrtu::dariTtl('Banyuwangi 24.05.2009'));
        $this->assertSame('01022010', SandiOrtu::dariTtl('Sampit 1/2/2010'));
    }

    public function test_tahan_terhadap_singkatan_bulan(): void
    {
        $this->assertSame('05012010', SandiOrtu::dariTtl('Banjarmasin 5 Jan 2010'));
        $this->assertSame('05122010', SandiOrtu::dariTtl('Banjarmasin 5 Des 2010'));
    }

    public function test_mengembalikan_null_bila_tanggal_lahir_kosong_atau_tidak_masuk_akal(): void
    {
        $this->assertNull(SandiOrtu::dariTtl(null));
        $this->assertNull(SandiOrtu::dariTtl(''));
        $this->assertNull(SandiOrtu::dariTtl('   '));
        $this->assertNull(SandiOrtu::dariTtl('Kotawaringin Timur'));
        $this->assertNull(SandiOrtu::dariTtl('Banyuwangi 31 Februari 2009'), '31 Februari bukan tanggal sah');
        $this->assertNull(SandiOrtu::dariTtl('Banyuwangi 24 Blnaneh 2009'));
        $this->assertNull(SandiOrtu::dariTtl('Banyuwangi 24 Mei'), 'tanpa tahun');
        $this->assertNull(SandiOrtu::dariTtl('Banyuwangi 24 Mei 1832'), 'tahun tidak wajar');
    }

    public function test_membedah_tempat_lahir(): void
    {
        $this->assertSame('Pematang Siantar', SandiOrtu::tempatLahir('Pematang Siantar, 8 Maret 2011'));
        $this->assertSame('Kotawaringin Timur', SandiOrtu::tempatLahir('Kotawaringin Timur 2009-11-23 00:00:00'));
        $this->assertSame('Nganjuk', SandiOrtu::tempatLahir('Nganjuk  2009-11-08 00:00:00'));
        $this->assertNull(SandiOrtu::tempatLahir(''));
        $this->assertNull(SandiOrtu::tempatLahir('2009-11-23 00:00:00'));
    }

    public function test_kolom_tanggal_lahir_diutamakan_atas_teks_lama(): void
    {
        $siswa = new Siswa(['tanggal_lahir' => '2009-05-24', 'ttl' => 'Bandung 2009-11-23 00:00:00']);

        // Tanggal di kolom baru yang menang, bukan teks lama yang keliru.
        $this->assertSame('24052009', SandiOrtu::untukSiswa($siswa));
    }

    public function test_tanpa_kolom_baru_jatuh_ke_teks_lama(): void
    {
        $siswa = new Siswa(['ttl' => 'Pematang Siantar, 8 Maret 2011']);

        $this->assertSame('08032011', SandiOrtu::untukSiswa($siswa));
        $this->assertTrue(SandiOrtu::bisaSiswa($siswa));
    }

    public function test_rangkai_ulang_teks_untuk_kolom_lama(): void
    {
        $this->assertSame('Sampit, 24 Mei 2009', SandiOrtu::rangkai('Sampit', '2009-05-24'));
        $this->assertSame('Sampit, 8 Maret 2011', SandiOrtu::rangkai('Sampit', '2011-03-08'));
        $this->assertSame('Sampit', SandiOrtu::rangkai('Sampit', null));
        $this->assertNull(SandiOrtu::rangkai(null, null));
    }
}
