<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsramaPeringkatController extends Controller
{
    /**
     * Peringkat kebersihan kamar PER BULAN.
     *
     * Sumber angka: tabel `asrama_penilaians` yang sudah difinalkan
     * (kolom kamar_terbersih_id = +1, kamar_terkotor_id = -1 — sama dengan
     * poin yang disuntikkan ke Student Root saat finalisasi).
     * Jadi peringkat di sini = cermin poin yang diterima penghuni kamar.
     */
    public function index(Request $request)
    {
        $inspeksi = DB::table('asrama_penilaians')
            ->where('status', 'final')
            ->whereNotNull('kamar_terbersih_id')
            ->orderBy('tanggal')
            ->get(['tanggal', 'kategori', 'kamar_terbersih_id', 'kamar_terkotor_id']);

        // ---- daftar bulan yang punya data (terbaru dulu) ----
        $daftarBulan = [];
        foreach ($inspeksi as $i) {
            $b = substr((string) $i->tanggal, 0, 7);
            $daftarBulan[$b] = ($daftarBulan[$b] ?? 0) + 1;
        }
        krsort($daftarBulan);

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

        // ---- ringkasan bulan terpilih ----
        $ringkasan = [
            'sidak'    => 0,
            'putra'    => 0,
            'putri'    => 0,
            'terakhir' => null,
        ];
        foreach ($inspeksi as $i) {
            if (substr((string) $i->tanggal, 0, 7) !== $bulanTerpilih) {
                continue;
            }
            $ringkasan['sidak']++;
            $ringkasan[$i->kategori] = ($ringkasan[$i->kategori] ?? 0) + 1;
            $ringkasan['terakhir'] = max((string) $ringkasan['terakhir'], (string) $i->tanggal);
        }

        $peringkat = $this->susunPeringkat($inspeksi, $bulanTerpilih, $divisi, $kamar);

        // 5 poin tertinggi & 5 poin terendah (yang pernah dinilai bulan itu)
        $teratas   = array_slice($peringkat, 0, 5);
        $terbawah  = array_slice(array_reverse($peringkat), 0, 5);
        // buang kamar yang muncul di kedua daftar (mis. data < 5 kamar)
        $idTeratas = array_column($teratas, 'kamar_id');
        $terbawah  = array_values(array_filter($terbawah, fn ($r) => ! in_array($r['kamar_id'], $idTeratas, true)));

        // ---- rekap semua bulan: juara 3 besar tiap bulan ----
        $rekapBulan = [];
        foreach (array_keys($daftarBulan) as $b) {
            $urut = $this->susunPeringkat($inspeksi, $b, 'semua', $kamar);
            $juara = array_slice($urut, 0, 3);
            $rekapBulan[$b] = [
                'sidak' => $daftarBulan[$b],
                'juara' => $juara,
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
     * Hitung poin tiap kamar pada satu bulan.
     * poin = jumlah dipilih terbersih − jumlah dipilih terkotor.
     */
    private function susunPeringkat($inspeksi, string $bulan, string $divisi, $kamar): array
    {
        $skor = [];

        foreach ($inspeksi as $i) {
            if (substr((string) $i->tanggal, 0, 7) !== $bulan) {
                continue;
            }
            if ($divisi !== 'semua' && $i->kategori !== $divisi) {
                continue;
            }

            if ($i->kamar_terbersih_id) {
                $skor[$i->kamar_terbersih_id]['bersih'] = ($skor[$i->kamar_terbersih_id]['bersih'] ?? 0) + 1;
            }
            if ($i->kamar_terkotor_id) {
                $skor[$i->kamar_terkotor_id]['kotor'] = ($skor[$i->kamar_terkotor_id]['kotor'] ?? 0) + 1;
            }
        }

        $baris = [];
        foreach ($skor as $kamarId => $s) {
            $bersih = $s['bersih'] ?? 0;
            $kotor  = $s['kotor'] ?? 0;
            $baris[] = [
                'kamar_id'  => (int) $kamarId,
                'bersih'    => $bersih,
                'kotor'     => $kotor,
                'poin'      => $bersih - $kotor,
            ];
        }

        // Urut: poin tertinggi → terbersih terbanyak → nama kamar
        usort($baris, function ($a, $b) use ($kamar) {
            if ($a['poin'] !== $b['poin']) {
                return $b['poin'] <=> $a['poin'];
            }
            if ($a['bersih'] !== $b['bersih']) {
                return $b['bersih'] <=> $a['bersih'];
            }

            return strcmp(
                (string) optional($kamar->get($a['kamar_id']))->nama_kamar,
                (string) optional($kamar->get($b['kamar_id']))->nama_kamar
            );
        });

        return $baris;
    }

    /** Label bulan untuk tampilan: 2026-08 → Agustus 2026 */
    public static function labelBulan(string $bulan): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
        } catch (\Throwable $e) {
            return $bulan;
        }
    }
}
