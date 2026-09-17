<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kelengkapan Penilaian Adab & Keasramaan.
 *
 * ATURAN (Fahri, 17 Sep 2026): setiap musyrif WAJIB menilai SELURUH santri divisinya
 * (musyrif = putra, musyrifah = putri) supaya rapor memuat rata-rata yang sahih.
 * Halaman ini memantau siapa sudah/belum menilai, per periode dan per kamar.
 */
class PenilaianKelengkapanController extends Controller
{
    public function index(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();

        $periodeId = (int) $request->input('periode');
        if (! $periodeId) {
            $periodeId = (int) (optional(PenilaianPeriode::aktifSekarang())->id ?: optional($periodeList->first())->id);
        }
        $periode = $periodeList->firstWhere('id', $periodeId) ?? $periodeList->first();

        $jenisList = PenilaianSesi::JENIS;

        // ---------- Kamar aktif & penghuninya ----------
        $kamar = AsramaKamar::where('status', 'aktif')->orderBy('nama_kamar')->get();
        $kamarIds = $kamar->pluck('id')->all();

        $anggota = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('k.status', 'aktif')
            ->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'm.kamar_id', 'k.kategori')
            ->get();

        $anggotaPerKamar = $anggota->groupBy('kamar_id');
        $siswaKamar = $anggota->pluck('kamar_id', 'student_id'); // siswa_id => kamar_id

        // Total santri per divisi
        $totalDivisi = $anggota->groupBy('kategori')->map->count();

        // ---------- Musyrif & divisinya (dari kamar yang dipetakan) ----------
        $musyrif = User::whereHas('roles', function ($q) {
            $q->where('name', 'Musyrif');
        })->orderBy('name')->get(['id', 'name', 'email']);

        $kamarMusyrif = AsramaKamar::where('status', 'aktif')
            ->whereIn('musyrif_id', $musyrif->pluck('id'))
            ->get()
            ->groupBy('musyrif_id');

        $jumlahMusyrifDivisi = ['putra' => 0, 'putri' => 0];
        foreach ($musyrif as $u) {
            $divisi = $this->divisiMusyrif($kamarMusyrif->get($u->id));
            if ($divisi) {
                $jumlahMusyrifDivisi[$divisi]++;
            }
        }

        // ---------- Sesi & jumlah santri yang sudah dinilai ----------
        $sesi = PenilaianSesi::where('periode_id', $periodeId)->get();
        $sesiPerPenilai = $sesi->groupBy(fn ($s) => $s->penilai_id . '|' . $s->jenis);

        $dinilaiPerSesi = DB::table('penilaian_jawaban')
            ->whereIn('sesi_id', $sesi->pluck('id')->all() ?: [0])
            ->selectRaw('sesi_id, COUNT(DISTINCT siswa_id) as jml, MAX(updated_at) as terakhir')
            ->groupBy('sesi_id')
            ->get()
            ->keyBy('sesi_id');

        // Siapa saja (penilai) yang sudah menilai seorang santri, per jenis: [jenis][siswa_id] => [penilai_id => true]
        $penilaiPerSiswa = [];
        $penilaiJenis = DB::table('penilaian_jawaban as j')
            ->join('penilaian_sesi as s', 's.id', '=', 'j.sesi_id')
            ->where('s.periode_id', $periodeId)
            ->whereIn('s.penilai_id', $musyrif->pluck('id')->all() ?: [0])
            ->select('s.jenis', 's.penilai_id', 'j.siswa_id')
            ->distinct()
            ->get();

        foreach ($penilaiJenis as $p) {
            $penilaiPerSiswa[$p->jenis][$p->siswa_id][$p->penilai_id] = true;
        }

        // ---------- Baris per musyrif ----------
        $musyrifRows = [];
        foreach ($musyrif as $u) {
            $kamarSaya = $kamarMusyrif->get($u->id) ?? collect();
            $divisi = $this->divisiMusyrif($kamarSaya);
            $totalWajib = $divisi ? (int) ($totalDivisi[$divisi] ?? 0) : 0;

            $perJenis = [];
            foreach ($jenisList as $kunci => $label) {
                $s = $sesiPerPenilai->get($u->id . '|' . $kunci)?->first();
                $terisi = $s ? (int) ($dinilaiPerSesi[$s->id]->jml ?? 0) : 0;
                $persen = $totalWajib > 0 ? min(100, (int) round($terisi / $totalWajib * 100)) : 0;

                $perJenis[$kunci] = [
                    'label' => $label,
                    'terisi' => $terisi,
                    'status' => $s?->status,
                    'sesi_id' => $s?->id,
                    'terakhir' => $s ? ($dinilaiPerSesi[$s->id]->terakhir ?? null) : null,
                    'persen' => $persen,
                    'kurang' => max(0, $totalWajib - $terisi),
                ];
            }

            $santai = $totalWajib === 0;

            $musyrifRows[] = [
                'id' => $u->id,
                'nama' => $u->name,
                'email' => $u->email,
                'divisi' => $divisi,
                'labelDivisi' => $divisi ? ucfirst($divisi) : 'belum dipetakan',
                'jumlah_kamar' => $kamarSaya->count(),
                'kamar' => $kamarSaya->pluck('nama_kamar')->sort()->values()->all(),
                'total_wajib' => $totalWajib,
                'jenis' => $perJenis,
                'lengkap' => ! $santai && collect($perJenis)->every(fn ($j) => $j['kurang'] === 0),
            ];
        }

        // ---------- Ringkasan per divisi ----------
        $ringkasDivisi = [];
        foreach (['putra', 'putri'] as $div) {
            $santriDiv = (int) ($totalDivisi[$div] ?? 0);
            $musyrifDiv = $jumlahMusyrifDivisi[$div];
            $perJenis = [];

            foreach ($jenisList as $kunci => $label) {
                $masuk = 0;
                foreach ($musyrifRows as $r) {
                    if ($r['divisi'] === $div) {
                        $masuk += $r['jenis'][$kunci]['terisi'];
                    }
                }
                $target = $santriDiv * $musyrifDiv;
                $perJenis[$kunci] = [
                    'label' => $label,
                    'masuk' => $masuk,
                    'target' => $target,
                    'persen' => $target > 0 ? min(100, (int) round($masuk / $target * 100)) : 0,
                ];
            }

            $ringkasDivisi[$div] = [
                'label' => ucfirst($div),
                'santri' => $santriDiv,
                'musyrif' => $musyrifDiv,
                'jenis' => $perJenis,
            ];
        }

        // ---------- Kelengkapan per kamar ----------
        $kamarPerJenis = [];
        foreach ($jenisList as $kunci => $label) {
            $baris = [];
            foreach ($kamar as $k) {
                $penghuni = $anggotaPerKamar->get($k->id) ?? collect();
                $jumlahMusyrif = $jumlahMusyrifDivisi[$k->kategori] ?? 0;

                $penuh = 0;
                $belum = 0;
                $totalMasuk = 0;
                foreach ($penghuni as $a) {
                    $masuk = count($penilaiPerSiswa[$kunci][$a->student_id] ?? []);
                    $totalMasuk += $masuk;
                    if ($jumlahMusyrif > 0 && $masuk >= $jumlahMusyrif) {
                        $penuh++;
                    } else {
                        $belum++;
                    }
                }

                $baris[] = [
                    'id' => $k->id,
                    'nama' => $k->nama_kamar,
                    'kategori' => $k->kategori,
                    'musyrif_binaan' => optional($k->musyrif)->name,
                    'santri' => $penghuni->count(),
                    'penuh' => $penuh,
                    'belum' => $belum,
                    'penilaian_masuk' => $totalMasuk,
                    'target' => $penghuni->count() * $jumlahMusyrif,
                ];
            }
            $kamarPerJenis[$kunci] = $baris;
        }

        return view('penilaian.kelengkapan', [
            'periodeList' => $periodeList,
            'periode' => $periode,
            'periodeId' => $periodeId,
            'jenisList' => $jenisList,
            'musyrifRows' => $musyrifRows,
            'ringkasDivisi' => $ringkasDivisi,
            'kamarPerJenis' => $kamarPerJenis,
            'totalSantri' => $anggota->count(),
            'jumlahKriteria' => collect($jenisList)->map(fn ($l, $k) => PenilaianKriteria::daftarAktif($k)->count())->all(),
        ]);
    }

    /** Divisi musyrif = kategori kamar binaannya (harus satu kategori). */
    private function divisiMusyrif($kamarSaya): ?string
    {
        $kategori = collect($kamarSaya)->pluck('kategori')->unique()->values()->all();

        return count($kategori) === 1 ? $kategori[0] : null;
    }
}
