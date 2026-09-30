<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Support\SandiOrtu;
use Illuminate\Console\Command;

/**
 * MEMILAH "TEMPAT/TANGGAL LAHIR" LAMA MENJADI DUA KOLOM (30 Sep 2026)
 * =====================================================================
 * Mengisi `tempat_lahir` + `tanggal_lahir` dari teks lama `siswas.ttl`
 * supaya kata sandi portal orang tua tidak perlu menebak lagi.
 *
 *   php artisan siswa:pilah-ttl --uji   -> hanya menampilkan rencana (tidak menyimpan)
 *   php artisan siswa:pilah-ttl         -> mengisi kolom yang MASIH KOSONG
 *
 * Sifatnya:
 *   - tidak pernah menimpa isian yang sudah ada (aman diulang);
 *   - TIDAK mengubah/menghapus kolom lama `ttl` (cetak lembar induk & ekspor tetap utuh);
 *   - siswa yang tanggalnya tak terbaca dilaporkan, tidak dipaksa diisi.
 */
class PilahTtlSiswa extends Command
{
    protected $signature = 'siswa:pilah-ttl {--uji : hanya memperlihatkan rencana pengisian}';

    protected $description = 'Pilah kolom ttl menjadi tempat_lahir + tanggal_lahir (untuk sandi portal orang tua)';

    public function handle(): int
    {
        $uji = (bool) $this->option('uji');

        $diisi = 0;
        $sudah = 0;
        $gagal = [];
        $contoh = [];

        Siswa::query()->orderBy('id')->chunk(200, function ($siswa) use ($uji, &$diisi, &$sudah, &$gagal, &$contoh) {
            foreach ($siswa as $s) {
                $kosong = (bool) (! $s->tempat_lahir || ! $s->tanggal_lahir);

                if (! $kosong) {
                    $sudah++;

                    continue;
                }

                $tempat = $s->tempat_lahir ?: SandiOrtu::tempatLahir($s->ttl);
                $tanggal = $s->tanggal_lahir ?: SandiOrtu::tanggalLahir($s->ttl);

                if (! $tanggal) {
                    $gagal[] = "{$s->id} | {$s->nisn} | {$s->nama_lengkap} | " . ($s->ttl ?: '(kosong)');

                    continue;
                }

                if (! $uji) {
                    $s->tempat_lahir = $tempat;
                    $s->tanggal_lahir = $tanggal;
                    $s->save();
                }

                $diisi++;
                if (count($contoh) < 5) {
                    $contoh[] = "{$s->nama_lengkap}: '" . ($s->ttl ?: '-') . "' -> tempat='" . ($tempat ?: '-') . "', tanggal={$tanggal}, sandi=" . SandiOrtu::untukSiswa($s);
                }
            }
        });

        $this->info(($uji ? '[UJI] ' : '') . "Siap diisi: {$diisi} siswa | sudah lengkap: {$sudah} | tidak terbaca: " . count($gagal));

        foreach ($contoh as $c) {
            $this->line('   ' . $c);
        }

        foreach ($gagal as $g) {
            $this->warn('   TIDAK TERBACA: ' . $g);
        }

        if ($uji) {
            $this->comment('Belum ada yang disimpan (mode uji). Jalankan tanpa --uji untuk mengisi.');
        }

        return self::SUCCESS;
    }
}
