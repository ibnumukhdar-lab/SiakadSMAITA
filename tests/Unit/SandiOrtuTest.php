<?php

namespace Tests\Unit;

use App\Support\SandiOrtu;
use PHPUnit\Framework\TestCase;

/**
 * Sandi portal orang tua = tanggal lahir anak (ddmmyyyy).
 * Kolom ttl berisi teks bebas dari data induk, jadi pembacaannya harus tahan
 * terhadap spasi ganda, koma, huruf kecil, dan tanggal satu digit.
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

    public function test_tahan_terhadap_singkatan_bulan(): void
    {
        $this->assertSame('24052009', SandiOrtu::dariTtl('Banyuwangi 24 Mei 2009'));
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
    }

    public function test_bisa_dipakai_sebagai_pemeriksa_kesiapan(): void
    {
        $this->assertTrue(SandiOrtu::bisa('Banyuwangi 24 Mei 2009'));
        $this->assertFalse(SandiOrtu::bisa(''));
    }
}
