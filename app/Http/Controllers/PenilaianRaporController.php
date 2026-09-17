<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\Kelas;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPengaturan;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RAPOT CETAK Penilaian Adab & Keasramaan (17 Sep 2026).
 *
 * Satu halaman A4 per siswa: kop sekolah, identitas santri, nilai akhir + predikat
 * untuk Adab dan Keasramaan, rincian per aspek (skor 1–5 = rata-rata antar penilai),
 * catatan, dan kolom tanda tangan.
 *
 * Sumber angka sama dengan halaman Rekap (nilai akhir = rata-rata persentase antar
 * penilai pada periode terpilih) supaya tidak ada selisih antara layar dan cetakan.
 */
class PenilaianRaporController extends Controller
{
    /** Label skala 1–5 untuk keterangan per aspek. */
    public const SKALA = [
        1 => 'Sangat kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat baik',
    ];

    public static function labelSkala($skor): string
    {
        $bulat = (int) round((float) $skor);

        return self::SKALA[$bulat] ?? '-';
    }

    /** Halaman pemilih: pilih periode + kamar/kelas, lalu cetak per anak atau per kamar. */
    public function index(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? $periodeList->first()?->id ?? 0);
        }

        $kamarId = (int) $request->input('kamar', 0);
        $q = trim((string) $request->input('q', ''));

        $kamarList = AsramaKamar::with('musyrif')->where('status', 'aktif')->orderBy('kategori')->orderBy('nama_kamar')->get();

        $hasilAdab = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'adab') : [];
        $hasilAsrama = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'keasramaan') : [];

        $query = Siswa::query()->where('status', 'Aktif');

        if ($kamarId > 0) {
            $anggota = DB::table('asrama_members')->where('kamar_id', $kamarId)->whereNull('tanggal_keluar')->pluck('student_id')->all();
            $query->whereIn('id', $anggota ?: [0]);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $siswa = $query->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")->orderBy('nama_lengkap')->get();

        $petaKamar = $siswa->isEmpty() ? [] : DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereIn('m.student_id', $siswa->pluck('id'))
            ->whereNull('m.tanggal_keluar')
            ->pluck('k.nama_kamar', 'm.student_id')
            ->toArray();

        $baris = $siswa->map(function ($s) use ($hasilAdab, $hasilAsrama, $petaKamar) {
            return [
                'id' => $s->id,
                'nama' => $s->nama_lengkap,
                'nisn' => $s->nisn,
                'kelas' => $s->kelas,
                'kamar' => $petaKamar[$s->id] ?? null,
                'adab' => $this->ringkas($hasilAdab[$s->id] ?? []),
                'keasramaan' => $this->ringkas($hasilAsrama[$s->id] ?? []),
            ];
        });

        return view('penilaian.rapot-pilih', [
            'periodeList' => $periodeList,
            'periodeId' => $periodeId,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'kamarId' => $kamarId,
            'kamarList' => $kamarList,
            'q' => $q,
            'baris' => $baris,
        ]);
    }

    /** Halaman cetak: satu siswa, atau seluruh penghuni satu kamar (satu halaman per anak). */
    public function cetak(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? $periodeList->first()?->id ?? 0);
        }

        $siswaId = (int) $request->input('siswa', 0);
        $kamarId = (int) $request->input('kamar', 0);

        if ($siswaId === 0 && $kamarId === 0) {
            return redirect()->route('penilaian.rapot', ['periode' => $periodeId])
                ->with('error', 'Pilih dulu kamar atau siswa yang mau dicetak rapotnya.');
        }

        $kamar = $kamarId > 0 ? AsramaKamar::with('musyrif')->find($kamarId) : null;

        if ($siswaId > 0) {
            $daftarSiswa = Siswa::where('id', $siswaId)->get();
        } else {
            $anggota = DB::table('asrama_members')->where('kamar_id', $kamarId)->whereNull('tanggal_keluar')->pluck('student_id')->all();
            $daftarSiswa = Siswa::whereIn('id', $anggota ?: [0])->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        }

        $hasilAdab = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'adab') : [];
        $hasilAsrama = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'keasramaan') : [];

        $petaKamar = $daftarSiswa->isEmpty() ? collect() : DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->leftJoin('users as u', 'u.id', '=', 'k.musyrif_id')
            ->whereIn('m.student_id', $daftarSiswa->pluck('id'))
            ->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'k.id as kamar_id', 'k.nama_kamar', 'k.kategori', 'u.name as musyrif')
            ->get()
            ->keyBy('student_id');

        $daftar = $daftarSiswa->map(function ($s) use ($hasilAdab, $hasilAsrama, $petaKamar, $periodeId) {
            $info = $petaKamar[$s->id] ?? null;

            return [
                'siswa' => $s,
                'kamar' => $info->nama_kamar ?? null,
                'kategori' => $info->kategori ?? null,
                'musyrif' => $info->musyrif ?? null,
                'adab' => $this->rincian($s->id, $periodeId, 'adab', $hasilAdab[$s->id] ?? []),
                'keasramaan' => $this->rincian($s->id, $periodeId, 'keasramaan', $hasilAsrama[$s->id] ?? []),
            ];
        });

        return view('penilaian.rapot', [
            'daftar' => $daftar,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'periodeId' => $periodeId,
            'periodeList' => $periodeList,
            'kamar' => $kamar,
            'pengaturan' => \App\Models\Pengaturan::first(),
            'ambang' => PenilaianPengaturan::ambang(),
            'kepalaDiniyah' => $this->namaKepalaDiniyah(),
        ]);
    }

    /** Nilai akhir satu jenis dari daftar sesi (rata-rata persentase antar penilai). */
    private function ringkas(array $entri): array
    {
        $angka = [];
        $pengisi = [];
        $adaDraft = false;

        foreach ($entri as $e) {
            if ($e['persentase'] !== null) {
                $angka[] = $e['persentase'];
            }
            if (! empty($e['penilai_nama'])) {
                $pengisi[] = $e['penilai_nama'];
            }
            if (($e['status'] ?? 'draft') === 'draft') {
                $adaDraft = true;
            }
        }

        $rata = $angka ? round(array_sum($angka) / count($angka), 2) : null;

        return [
            'rata' => $rata,
            'predikat' => PenilaianPengaturan::predikat($rata),
            'penilai' => array_values(array_unique($pengisi)),
            'ada_draft' => $adaDraft,
        ];
    }

    /** Ringkasan + rincian per aspek satu siswa untuk satu jenis. */
    private function rincian(int $siswaId, int $periodeId, string $jenis, array $entri): array
    {
        $kriteria = PenilaianKriteria::daftarAktif($jenis);

        // Skor rata-rata per aspek dari SEMUA penilai pada periode ini.
        $rataAspek = [];
        if ($kriteria->isNotEmpty()) {
            $baris = DB::table('penilaian_jawaban as j')
                ->join('penilaian_sesi as s', 's.id', '=', 'j.sesi_id')
                ->where('s.periode_id', $periodeId)
                ->where('s.jenis', $jenis)
                ->where('j.siswa_id', $siswaId)
                ->whereIn('j.kriteria_id', $kriteria->pluck('id'))
                ->select('j.kriteria_id', DB::raw('AVG(j.skor) as rata'), DB::raw('COUNT(*) as jumlah'))
                ->groupBy('j.kriteria_id')
                ->get();

            foreach ($baris as $b) {
                $rataAspek[$b->kriteria_id] = ['rata' => (float) $b->rata, 'jumlah' => (int) $b->jumlah];
            }
        }

        $aspek = $kriteria->map(function ($k) use ($rataAspek) {
            $data = $rataAspek[$k->id] ?? null;

            return [
                'pertanyaan' => $k->pertanyaan,
                'rata' => $data ? round($data['rata'], 2) : null,
                'label' => $data ? self::labelSkala($data['rata']) : null,
                'jumlah_penilai' => $data['jumlah'] ?? 0,
            ];
        });

        $terisi = $aspek->filter(fn ($a) => $a['rata'] !== null)->count();
        $rataAspekSemua = $terisi > 0
            ? round($aspek->sum(fn ($a) => $a['rata'] ?? 0) / $terisi, 2)
            : null;

        return array_merge($this->ringkas($entri), [
            'aspek' => $aspek,
            'jumlah_aspek' => $kriteria->count(),
            'aspek_terisi' => $terisi,
            'rata_aspek' => $rataAspekSemua,
        ]);
    }

    private function namaKepalaDiniyah(): ?string
    {
        $nama = DB::table('users as u')
            ->join('model_has_roles as mr', 'mr.model_id', '=', 'u.id')
            ->join('roles as r', function ($j) {
                $j->on('r.id', '=', 'mr.role_id')->where('r.name', '=', 'Kepala Diniyah');
            })
            ->value('u.name');

        return $nama ?: null;
    }
}
