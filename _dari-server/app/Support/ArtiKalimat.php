<?php

namespace App\Support;

/**
 * Terjemahan bahasa Indonesia untuk kalimat bank soal BEE Smart.
 *
 * Bank kosakata (`bee_vocabs`) menyimpan kalimat Inggris (`sentence_en`) dan kalimat Arab
 * (`jumlah_ar`) beserta arti KATA-nya (`kosakata_id`), tetapi TIDAK menyimpan terjemahan
 * kalimatnya. Terjemahan kalimat disimpan di `resources/data/arti-kalimat.json`
 * (kunci = kalimat asli apa adanya, huruf demi huruf) supaya sisi siswa bisa menampilkan
 * arti kalimat — dipakai oleh mode "Lengkapi Kalimat" (pg_rumpang).
 *
 * Berkas JSON tersebut sengaja dibuat mudah disunting: guru bisa membetulkan terjemahan
 * langsung di sana tanpa menyentuh kode, dan halaman ujian otomatis memakai versi terbaru.
 */
class ArtiKalimat
{
    /** @var array<string,string>|null */
    private static ?array $peta = null;

    /** Semua pasangan kalimat → terjemahan Indonesia. */
    public static function semua(): array
    {
        if (self::$peta === null) {
            $berkas = resource_path('data/arti-kalimat.json');
            $isi = is_file($berkas) ? json_decode((string) file_get_contents($berkas), true) : [];
            self::$peta = is_array($isi) ? $isi : [];
        }

        return self::$peta;
    }

    /** Terjemahan satu kalimat; null bila belum tersedia (halaman tetap jalan tanpa arti). */
    public static function cari(?string $kalimat): ?string
    {
        $kunci = self::bersih((string) $kalimat);
        if ($kunci === '') {
            return null;
        }

        return self::semua()[$kunci] ?? null;
    }

    /**
     * Susun kembali kalimat utuh dari butir soal berlubang.
     *
     * Butir pg_rumpang/drag_rumpang menyimpan `tanya` (kalimat dengan satu bagian diganti `____`) dan
     * `lubang` (nomor bagian, 1 = kata pertama). Kata yang dihilangkan TIDAK ikut disimpan di butir
     * (sengaja: butir dikirim ke peramban), jadi jawabannya diambil dari kolom `kunci` percobaan.
     * Mesin membangun `tanya` sebagai implode(' ', potongan kalimat), sehingga menaruh kembali
     * jawaban pada posisi lubang menghasilkan kalimat aslinya kembali.
     *
     * @param  string|null  $jawaban  kata yang dihilangkan (dari `$percobaan->kunci[$nomor]`)
     */
    public static function kalimatButir(array $butir, ?string $jawaban = null): ?string
    {
        $tanya = (string) ($butir['tanya'] ?? '');
        $jawaban = self::bersih((string) ($jawaban ?? ($butir['jawaban'] ?? '')));
        if ($tanya === '' || $jawaban === '') {
            return null;
        }

        $bagian = preg_split('~\s+~u', self::bersih($tanya), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lubang = (int) ($butir['lubang'] ?? 0);

        if ($lubang >= 1 && isset($bagian[$lubang - 1]) && str_contains($bagian[$lubang - 1], '_')) {
            $bagian[$lubang - 1] = $jawaban;

            return implode(' ', $bagian);
        }

        // Cadangan: cari penanda lubang mana pun.
        foreach ($bagian as $i => $potongan) {
            if (str_contains($potongan, '_')) {
                $bagian[$i] = $jawaban;

                return implode(' ', $bagian);
            }
        }

        return null;
    }

    /**
     * Pembersih yang SAMA dengan mesin soal (App\Support\SoalBee::bersih): buang karakter tak
     * terlihat lalu rapikan spasi. Kunci JSON memakai bentuk ini supaya pasti ketemu saat
     * kalimat disusun ulang dari butir soal.
     */
    private static function bersih(string $teks): string
    {
        $teks = preg_replace('~[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}\x{FEFF}]~u', '', $teks);

        return trim((string) preg_replace('~\s+~u', ' ', (string) $teks));
    }
}
