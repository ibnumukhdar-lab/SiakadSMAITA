<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Services\KlasemenKebersihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Peringkat kebersihan kamar PER BULAN.
 *
 * Sejak 14 Okt 2026 seluruh angka diambil dari App\Services\KlasemenKebersihan
 * — sumber yang SAMA dengan dashboard asrama dan slide TV. Dasar juara kini
 * TOTAL SKOR capaian (jumlah skor sidak harian), sedangkan angka +n/−n tetap
 * ditampilkan sebagai POIN PENGHARGAAN (kamar terbersih +1, terkotor −1) yang
 * disuntikkan ke Student Root saat finalisasi.
 */
class AsramaPeringkatController extends Controller
{
    public function index(Request $request)
    {
        $daftarBulan = KlasemenKebersihan::daftarBulan();

        $bulanTerpilih = (string) $request->input('bulan');
        if ($bulanTerpilih === '' || ! isset($daftarBulan[$bulanTerpilih])) {
            $bulanTerpilih = array_key_first($daftarBulan) ?: now()->format('Y-m');
        }

        $divisi = in_array($request->input('divisi'), ['putra', 'putri'], true)
            ? $request->input('divisi')
            : 'semua';

        $kamar = AsramaKamar::with('musyrif:id,name')->get()->keyBy('id');
        $penghuni = DB::table('asrama_members')
            ->whereNull('tanggal_keluar')
            ->selectRaw('kamar_id, COUNT(*) AS jml')
            ->groupBy('kamar_id')
            ->pluck('jml', 'kamar_id');

        // ---- ringkasan bulan terpilih (jumlah lembar sidak final) ----
        $ringkasan = [
            'sidak'    => 0,
            'putra'    => 0,
            'putri'    => 0,
            'terakhir' => null,
        ];
        $inspeksiBulanIni = DB::table('asrama_penilaians')
            ->where('status', 'final')
            ->whereNotNull('kamar_terbersih_id')
            ->where('tanggal', 'like', $bulanTerpilih . '%')
            ->get(['tanggal', 'kategori']);

        foreach ($inspeksiBulanIni as $i) {
            $ringkasan['sidak']++;
            $ringkasan[$i->kategori] = ($ringkasan[$i->kategori] ?? 0) + 1;
            $ringkasan['terakhir'] = max((string) $ringkasan['terakhir'], (string) $i->tanggal);
        }

        $peringkat = $this->susunPeringkat($bulanTerpilih, $divisi);

        // 5 total skor tertinggi & 5 terendah (kamar yang benar-benar disidak)
        $teratas  = array_slice($peringkat, 0, 5);
        $terbawah = array_slice(array_reverse($peringkat), 0, 5);
        $idTeratas = array_column($teratas, 'kamar_id');
        $terbawah  = array_values(array_filter($terbawah, fn ($r) => ! in_array($r['kamar_id'], $idTeratas, true)));

        // ---- rekap semua bulan: juara 3 besar tiap bulan ----
        $rekapBulan = [];
        foreach (array_keys($daftarBulan) as $b) {
            $urut  = $this->susunPeringkat($b, 'semua');
            $rekapBulan[$b] = [
                'sidak' => $daftarBulan[$b],
                'juara' => array_slice($urut, 0, 3),
            ];
        }

        return view('asrama.peringkat', compact(
            'daftarBulan',
            'bulanTerpilih',
            'divisi',
            'ringkasan',
            'teratas',
            'terbawah',
            'rekapBulan',
            'kamar',
            'penghuni'
        ));
    }

    /**
     * Baris peringkat satu bulan (urut: total skor tertinggi dulu).
     * Bentuk keluaran dipertahankan (bersih/kotor/poin) + tambahan total/rata/persen.
     */
    private function susunPeringkat(string $bulan, string $divisi): array
    {
        [$bln, $thn] = KlasemenKebersihan::uraiBulan($bulan);

        $baris = KlasemenKebersihan::divisi(
            KlasemenKebersihan::dinilai(KlasemenKebersihan::bulan($bln, $thn)),
            $divisi === 'semua' ? null : $divisi
        );

        return $baris->map(fn ($r) => [
            'kamar_id' => $r->kamar_id,
            'bersih'   => $r->menang,
            'kotor'    => $r->kalah,
            'poin'     => $r->poin,
            'sidak'    => $r->sidak,
            'total'    => $r->total,
            'rata'     => $r->rata,
            'persen'   => $r->persen,
        ])->all();
    }

    /** Label bulan untuk tampilan: 2026-08 → Agustus 2026 */
    public static function labelBulan(string $bulan): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $bulan)->locale('id')->translatedFormat('F Y');
        } catch (\Throwable $e) {
            return $bulan;
        }
    }
}
