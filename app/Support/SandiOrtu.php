<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * SANDI ORANG TUA DARI TANGGAL LAHIR (mulai 30 Sep 2026, disempurnakan 30 Sep 2026)
 * =====================================================================
 * Keputusan pemilik sekolah: kata sandi portal orang tua = tanggal lahir anak
 * dengan format ddmmyyyy (mis. 24 Mei 2009 -> 24052009).
 *
 * Sejak 30 Sep 2026 tanggal lahir disimpan di kolom sendiri
 * (`siswas.tanggal_lahir`, tipe DATE, diisi lewat pemilih kalender) dan
 * `siswas.tempat_lahir`. Fungsi di sini melayani DUA keadaan:
 *   1. data baru  -> baca `tanggal_lahir` (pasti benar, tanpa tebak-tebakan);
 *   2. data lama  -> bedah teks `ttl` (kolom warisan) sebagai cadangan.
 *
 * Bentuk teks `ttl` yang nyata di produksi (30 Sep 2026, 130 siswa):
 *   "Pematang Siantar, 8 Maret 2011"          (106 siswa) nama bulan Indonesia
 *   "Kotawaringin Timur 2009-11-23 00:00:00"  ( 22 siswa) hasil impor ISO
 *   ""                                        (  2 siswa) belum ada isian
 * Karena itu pembacaan teks harus tahan: spasi ganda, koma, huruf kecil,
 * tanggal satu digit, bulan singkat, dan bentuk ISO/numerik.
 *
 * Alat bantu: `php artisan siswa:pilah-ttl --uji` (lihat rencana pengisian),
 * `php artisan siswa:pilah-ttl` (isi tempat_lahir & tanggal_lahir dari ttl).
 */
class SandiOrtu
{
    /** Nama bulan Indonesia -> angka. */
    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    // =================================================================
    //  BAGIAN 1 — kata sandi portal
    // =================================================================

    /**
     * Kata sandi (ddmmyyyy) untuk seorang siswa.
     * Diutamakan kolom `tanggal_lahir`; bila belum ada, teks `ttl` dibedah.
     */
    public static function untukSiswa($siswa): ?string
    {
        if (! $siswa) {
            return null;
        }

        $tanggal = $siswa->tanggal_lahir ?? null;

        if ($tanggal) {
            return Carbon::parse($tanggal)->format('dmY');
        }

        return self::dariTtl($siswa->ttl ?? null);
    }

    /**
     * Kata sandi (ddmmyyyy) dari teks. Null bila tak bisa dibaca.
     * Dipertahankan sebagai jalan cadangan untuk baris lama.
     */
    public static function dariTtl(?string $ttl): ?string
    {
        $tanggal = self::tanggalLahir($ttl);

        return $tanggal === null ? null : Carbon::parse($tanggal)->format('dmY');
    }

    /** Apakah sandi siswa ini siap dipakai? */
    public static function bisaSiswa($siswa): bool
    {
        return self::untukSiswa($siswa) !== null;
    }

    /** Apakah teks tanggal lahir ini bisa dibaca? */
    public static function bisa(?string $ttl): bool
    {
        return self::dariTtl($ttl) !== null;
    }

    // =================================================================
    //  BAGIAN 2 — pembedah teks tempat + tanggal lahir
    // =================================================================

    /**
     * Tanggal lahir (Y-m-d) hasil bedah teks. Null bila tak bisa dibaca.
     * Menerima tiga bentuk: nama bulan Indonesia, ISO (2009-11-23), numerik
     * (23/11/2009, 23-11-2009, 23.11.2009). Tanggal ISO dianggap TAHUN-BULAN-HARI
     * karena begitulah isi data impor; bentuk numerik dianggap HARI-BULAN-TAHUN.
     */
    public static function tanggalLahir(?string $teks): ?string
    {
        $teks = trim((string) $teks);

        if ($teks === '') {
            return null;
        }

        // (a) 2009-11-23 (iso, boleh diikuti jam 00:00:00 dari impor)
        if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $teks, $m)) {
            return self::sah((int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // (b) 23/11/2009, 23-11-2009, 23.11.2009
        if (preg_match('#(\d{1,2})\s*[/.\-]\s*(\d{1,2})\s*[/.\-]\s*(\d{4})#', $teks, $m)) {
            return self::sah((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // (c) 24 Mei 2009 / 24 Mei 2009 / "8 Maret 2011"
        if (preg_match('/(\d{1,2})\s*[^\dA-Za-z]?\s*([A-Za-z]+)\s+(\d{4})/u', $teks, $m)) {
            $bulan = self::bulan($m[2]);

            if ($bulan === 0) {
                return null;
            }

            return self::sah((int) $m[1], $bulan, (int) $m[3]);
        }

        return null;
    }

    /**
     * Nama tempat lahir dari teks (bagian sebelum angka/tahun).
     * "Pematang Siantar, 8 Maret 2011" -> "Pematang Siantar"
     * "Kotawaringin Timur 2009-11-23 00:00:00" -> "Kotawaringin Timur"
     */
    public static function tempatLahir(?string $teks): ?string
    {
        $teks = trim((string) $teks);

        if ($teks === '') {
            return null;
        }

        // Buang bagian tanggal: mulai dari angka/tahun pertama.
        $potong = preg_split('/\d/', $teks, 2);
        $tempat = trim((string) ($potong[0] ?? ''), " \t\n\r,-.");

        return $tempat === '' ? null : $tempat;
    }

    /** Rangkaian ulang "Tempat, 24 Mei 2009" untuk kolom lama `ttl`. */
    public static function rangkai(?string $tempat, ?string $tanggal): ?string
    {
        if (! $tanggal) {
            return $tempat ?: null;
        }

        $tanggal = Carbon::parse($tanggal);
        $teks = $tempat ? $tempat . ', ' : '';

        return $teks . $tanggal->format('j') . ' ' . self::namaBulan((int) $tanggal->format('n')) . ' ' . $tanggal->format('Y');
    }

    // =================================================================
    //  ALAT INTERNAL
    // =================================================================

    /** Nama bulan angka (1-12), 0 bila tidak dikenali (termasuk singkatan "Jan"). */
    private static function bulan(string $nama): int
    {
        $nama = strtolower(trim($nama));

        if (isset(self::BULAN[$nama])) {
            return self::BULAN[$nama];
        }

        if (mb_strlen($nama) >= 3) {
            $awalan = mb_substr($nama, 0, 3);

            foreach (self::BULAN as $bulanNama => $angka) {
                if (mb_substr($bulanNama, 0, 3) === $awalan) {
                    return $angka;
                }
            }
        }

        return 0;
    }

    private static function namaBulan(int $angka): string
    {
        $nama = array_keys(self::BULAN);

        return ucfirst($nama[$angka - 1] ?? '-');
    }

    /** Tanggal sah -> "Y-m-d", sebaliknya null. Tahun wajar 1980-2026. */
    private static function sah(int $hari, int $bulan, int $tahun): ?string
    {
        if (! checkdate($bulan, $hari, $tahun)) {
            return null;
        }

        if ($tahun < 1980 || $tahun > 2026) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
    }
}
