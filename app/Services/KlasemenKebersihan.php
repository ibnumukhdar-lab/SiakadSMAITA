<?php

namespace App\Services;

use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * SUMBER TUNGGAL angka klasemen kebersihan kamar per bulan.
 *
 * Dipakai bersama oleh: dashboard asrama (AsramaDashboardController),
 * slide TV (`student-root.display-setting.tv`), dan halaman Peringkat Bulanan
 * (AsramaPeringkatController) — supaya ketiganya TIDAK PERNAH lagi menampilkan
 * angka yang saling bertentangan.
 *
 * DASAR JUARA (keputusan pemilik, 14 Okt 2026): TOTAL SKOR CAPAIAN, yaitu
 * jumlah seluruh skor sidak harian dalam bulan itu — BUKAN rata-rata. Karena
 * tiap sesi memberi maksimal 25 (5 kriteria x 5), angka bulanan bisa ratusan
 * (mis. 18 kali sidak x 25 = 450).
 * Catatan yang harus diingat saat menjelaskan ke sekolah: bila semua kamar
 * disidak sama banyak, urutan total skor = urutan rata-rata. Yang membuat
 * urutan berbeda hanyalah jumlah kesempatan sidak tiap kamar.
 *
 * Kolom pendukung: rata-rata per sidak (x/25 + persen), jumlah menang
 * "kamar terbersih" dan "kamar terkotor" — sama dengan poin +1/−1 yang
 * disuntikkan ke Student Root saat finalisasi.
 *
 * Urutan: total skor DESC → rata-rata DESC → menang terbersih DESC → nama kamar.
 */
class KlasemenKebersihan
{
    /** Skor maksimal satu lembar sidak: 5 kriteria x skor tertinggi 5. */
    public const SKOR_MAKS = 25;

    /** Batas kamar boleh disebut "terkotor": di bawah persen ini dari skor maksimal. */
    public const BATAS_TERKOTOR_PERSEN = 70;

    /** Ambang persen untuk mewarnai angka rata-rata (hijau penuh). */
    public const AMBANG_HIJAU_PERSEN = 90;

    /** Batas absolut "terkotor" (25 x 70% = 17,5). */
    public static function batasTerkotor(): float
    {
        return self::SKOR_MAKS * self::BATAS_TERKOTOR_PERSEN / 100;
    }

    /**
     * Seluruh kamar satu bulan, sudah urut (juara di depan).
     *
     * @return Collection<int, object>
     */
    public static function bulan(?int $bulan = null, ?int $tahun = null): Collection
    {
        $bulan = $bulan ?: (int) now()->month;
        $tahun = $tahun ?: (int) now()->year;

        $inspeksi = AsramaPenilaian::where('status', 'final')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get(['id', 'kategori', 'kamar_terbersih_id', 'kamar_terkotor_id']);

        $detail = AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksi->pluck('id'))
            ->get(['kamar_id', 'total_skor']);

        $kamars = AsramaKamar::where('status', 'aktif')->orderBy('nama_kamar')->get();
        if ($kamars->isEmpty()) {
            $kamars = AsramaKamar::orderBy('nama_kamar')->get();
        }

        $users  = DB::table('users')->pluck('name', 'id');
        $menang = $inspeksi->whereNotNull('kamar_terbersih_id')->countBy('kamar_terbersih_id');
        $kalah  = $inspeksi->whereNotNull('kamar_terkotor_id')->countBy('kamar_terkotor_id');

        $baris = [];

        foreach ($kamars as $kamar) {
            $skor = $detail->where('kamar_id', $kamar->id)
                ->pluck('total_skor')
                ->filter(fn ($s) => $s !== null && $s !== '');

            $sidak = $skor->count();
            $total = (int) $skor->sum();
            $rata  = $sidak > 0 ? round((float) $skor->avg(), 2) : null;
            $m     = (int) ($menang[$kamar->id] ?? 0);
            $t     = (int) ($kalah[$kamar->id] ?? 0);

            $baris[] = (object) [
                'kamar_id'     => (int) $kamar->id,
                'nama_kamar'   => $kamar->nama_kamar ?? 'Kamar ' . $kamar->id,
                'kategori'     => self::kategoriBaku($kamar->kategori ?? ''),
                'nama_musyrif' => $kamar->musyrif_id ? ($users[$kamar->musyrif_id] ?? 'Belum Diatur') : 'Belum Diatur',
                'jumlah_sidak' => $sidak,          // alias lama (view) = sidak
                'sidak'        => $sidak,
                'menang'       => $m,
                'kalah'        => $t,
                'poin'         => $m - $t,
                'total'        => $total,          // DASAR JUARA
                'rata'         => $rata,
                'rata_rata_skor' => (float) ($rata ?? 0), // alias lama (view)
                'persen'       => $rata === null ? null : round($rata / self::SKOR_MAKS * 100, 1),
            ];
        }

        usort($baris, function ($a, $b) {
            if ($a->total !== $b->total) {
                return $b->total <=> $a->total;
            }
            if ($a->rata !== $b->rata) {
                return ($b->rata ?? 0) <=> ($a->rata ?? 0);
            }
            if ($a->menang !== $b->menang) {
                return $b->menang <=> $a->menang;
            }

            return strcmp((string) $a->nama_kamar, (string) $b->nama_kamar);
        });

        return collect($baris);
    }

    /** Kategori diseragamkan: putra | putri | (nilai lain apa adanya). */
    public static function kategoriBaku(?string $kategori): string
    {
        $k = strtolower(trim((string) $kategori));

        if (str_contains($k, 'putra') || str_contains($k, 'ikhwan')) {
            return 'putra';
        }
        if (str_contains($k, 'putri') || str_contains($k, 'akhwat')) {
            return 'putri';
        }

        return $k;
    }

    /** Saring satu divisi; 'semua' atau kosong = seluruh kamar. */
    public static function divisi(Collection $baris, ?string $divisi = null): Collection
    {
        if (! $divisi || $divisi === 'semua') {
            return $baris->values();
        }

        return $baris->filter(fn ($r) => $r->kategori === $divisi)->values();
    }

    /** Kamar yang benar-benar disidak bulan itu. */
    public static function dinilai(Collection $baris): Collection
    {
        return $baris->filter(fn ($r) => $r->sidak > 0)->values();
    }

    /** Juara bulan: total skor tertinggi. */
    public static function juara(Collection $baris, int $jumlah = 3): Collection
    {
        return self::dinilai($baris)->take($jumlah)->values();
    }

    /**
     * Kamar yang perlu perhatian: rata-rata di bawah batas terkotor (<70%),
     * paling rendah dulu. Bila tidak ada, kembalian kosong (semua kamar bersih).
     */
    public static function perhatian(Collection $baris, int $jumlah = 3): Collection
    {
        return $baris
            ->filter(fn ($r) => $r->sidak > 0 && $r->rata !== null && $r->rata < self::batasTerkotor())
            ->sortBy('rata')
            ->take($jumlah)
            ->values();
    }

    /** Jumlah lembar sidak final satu bulan (opsional per divisi). */
    public static function jumlahInspeksi(?int $bulan = null, ?int $tahun = null, ?string $divisi = null): int
    {
        $q = AsramaPenilaian::where('status', 'final')
            ->whereYear('tanggal', $tahun ?: (int) now()->year)
            ->whereMonth('tanggal', $bulan ?: (int) now()->month);

        if ($divisi && $divisi !== 'semua') {
            $q->where('kategori', $divisi);
        }

        return $q->count();
    }

    /**
     * Daftar bulan yang punya sidak final (terbaru dulu): ['2026-10' => jumlah].
     */
    public static function daftarBulan(): array
    {
        $tanggal = AsramaPenilaian::where('status', 'final')
            ->whereNotNull('kamar_terbersih_id')
            ->orderBy('tanggal')
            ->pluck('tanggal');

        $daftar = [];
        foreach ($tanggal as $t) {
            $kunci = substr((string) $t, 0, 7);
            $daftar[$kunci] = ($daftar[$kunci] ?? 0) + 1;
        }
        krsort($daftar);

        return $daftar;
    }

    /** Ubah kunci bulan '2026-10' menjadi [bulan, tahun]. */
    public static function uraiBulan(string $bulan): array
    {
        [$tahun, $bln] = array_pad(explode('-', $bulan), 2, null);

        return [(int) $bln ?: (int) now()->month, (int) $tahun ?: (int) now()->year];
    }
}
