<?php

namespace App\Support;

/**
 * SANDI ORANG TUA DARI TANGGAL LAHIR (mulai 30 Sep 2026)
 * =====================================================================
 * Keputusan pemilik sekolah: kata sandi portal orang tua = tanggal lahir anak
 * dengan format ddmmyyyy (mis. 24 Mei 2009 -> 24052009).
 *
 * Kolom `siswas.ttl` berisi teks bebas, misalnya:
 *   "Banyuwangi 24 Mei 2009"
 *   "bekasi 06 agustus 2008"
 *   "Yogyakarta  27 Mei 2009"       (spasi ganda)
 *   "Pematang Siantar, 8 Maret 2011" (koma & tanggal satu digit)
 * Jadi pembacaannya harus toleran: cari angka tanggal, nama bulan Indonesia,
 * lalu empat angka tahun — di mana pun posisinya di dalam teks.
 *
 * Data produksi 30 Sep 2026: 128 dari 130 siswa bisa dibaca; 2 siswa belum ada
 * tanggal lahirnya (Alif Nizham Muzakki, Nur Alya Aqila Azahra) sehingga belum
 * bisa memakai portal ini sampai data induknya dilengkapi Tata Usaha.
 */
class SandiOrtu
{
    /** Nama bulan Indonesia -> angka. */
    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    /**
     * Kata sandi (ddmmyyyy) dari teks tanggal lahir. Null bila tak bisa dibaca.
     */
    public static function dariTtl(?string $ttl): ?string
    {
        $teks = trim((string) $ttl);

        if ($teks === '') {
            return null;
        }

        // Tanggal, nama bulan, tahun — dipisah spasi/koma/titik apa saja.
        if (! preg_match('/(\d{1,2})\s*[^\dA-Za-z]?\s*([A-Za-z]+)\s+(\d{4})/u', $teks, $m)) {
            return null;
        }

        $hari = (int) $m[1];
        $bulan = self::bulan($m[2]);
        $tahun = (int) $m[3];

        if ($bulan === 0 || ! checkdate($bulan, $hari, $tahun)) {
            return null;
        }

        return sprintf('%02d%02d%04d', $hari, $bulan, $tahun);
    }

    /** Apakah tanggal lahir siswa ini bisa dijadikan sandi? */
    public static function bisa(?string $ttl): bool
    {
        return self::dariTtl($ttl) !== null;
    }

    /** Nama bulan angka (1-12), 0 bila tidak dikenali (termasuk singkatan "Jan"). */
    private static function bulan(string $nama): int
    {
        $nama = strtolower(trim($nama));

        if (isset(self::BULAN[$nama])) {
            return self::BULAN[$nama];
        }

        // Toleransi singkatan: "jan", "feb", "agu", "des", dst.
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
}
