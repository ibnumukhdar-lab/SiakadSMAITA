<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SESI & PROGRES RAPOR ADAB & KEASRAMaan (19 Sep 2026).
 *
 * Satu halaman untuk pengelola (Super Admin, Tata Usaha, Kepala Sekolah, Kepala Diniyah):
 *  - tombol BUKA / TUTUP sesi isi rapor (menggantikan "Buka lembar penilaian" per musyrif
 *    dan "Finalkan" per lembar),
 *  - pantauan progres tiap musyrif/musyrifah (menggantikan halaman Kelengkapan Penilaian
 *    sebagai pintu masuk utama — rinciannya tetap bisa dibuka dari sini),
 *  - jalan ke Rekap Nilai dan Cetak Rapor.
 */
class PenilaianSesiRaporController extends Controller
{
    public function index(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('id')->get();

        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::terbukaSekarang()?->id
                ?? PenilaianPeriode::aktifSekarang()?->id
                ?? $periodeList->first()?->id
                ?? 0);
        }

        $periode = $periodeList->firstWhere('id', $periodeId);
        $divisi = trim((string) $request->input('divisi', 'semua'));

        $jumlahKriteria = [
            'adab' => PenilaianKriteria::daftarAktif('adab')->count(),
            'keasramaan' => PenilaianKriteria::daftarAktif('keasramaan')->count(),
        ];

        $kamar = AsramaKamar::with('musyrif')->where('status', 'aktif')->orderBy('nama_kamar')->get();

        $anggota = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('k.status', 'aktif')
            ->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'm.kamar_id', 'k.kategori')
            ->get();

        $anggotaPerKamar = $anggota->groupBy('kamar_id');
        $penghuniDivisi = $anggota->groupBy('kategori')->map->count();

        // ---------- Musyrif & kamar binaannya ----------
        $musyrifList = User::role('Musyrif')->orderBy('name')->get(['id', 'name', 'nipa']);
        $kamarPerMusyrif = $kamar->whereNotNull('musyrif_id')->groupBy('musyrif_id');

        // ---------- Jawaban per lembar ----------
        $sesi = $periode
            ? PenilaianSesi::where('periode_id', $periode->id)->get(['id', 'jenis', 'penilai_id', 'status'])
            : collect();

        $jawaban = DB::table('penilaian_jawaban')
            ->whereIn('sesi_id', $sesi->pluck('id')->all() ?: [0])
            ->selectRaw('sesi_id, siswa_id, COUNT(*) as jumlah')
            ->groupBy('sesi_id', 'siswa_id')
            ->get();

        // [penilai_id][jenis][siswa_id] => jumlah jawaban
        $terjawab = [];
        $jenisPerSesi = $sesi->pluck('jenis', 'id');
        $penilaiPerSesi = $sesi->pluck('penilai_id', 'id');

        foreach ($jawaban as $j) {
            $penilaiId = $penilaiPerSesi[$j->sesi_id] ?? null;
            $jenis = $jenisPerSesi[$j->sesi_id] ?? null;

            if (! $penilaiId || ! $jenis) {
                continue;
            }

            $terjawab[$penilaiId][$jenis][$j->siswa_id] = (int) $j->jumlah;
        }

        $baris = $musyrifList->map(function ($u) use ($kamarPerMusyrif, $anggota, $terjawab, $jumlahKriteria) {
            $kamarSaya = $kamarPerMusyrif->get($u->id) ?? collect();
            $kategori = $kamarSaya->pluck('kategori')->unique()->values();
            $divisiMusyrif = $kategori->count() === 1 ? $kategori->first() : null;

            $penghuni = $kategori->isNotEmpty()
                ? $anggota->whereIn('kategori', $kategori->all())
                : collect();

            $target = $penghuni->count();
            $dinilaiIds = [];

            foreach ($penghuni as $p) {
                $adab = ($terjawab[$u->id]['adab'][$p->student_id] ?? 0) >= $jumlahKriteria['adab'];
                $asrama = ($terjawab[$u->id]['keasramaan'][$p->student_id] ?? 0) >= $jumlahKriteria['keasramaan'];

                if ($adab && $asrama) {
                    $dinilaiIds[] = $p->student_id;
                }
            }

            return (object) [
                'id' => $u->id,
                'nama' => $u->name,
                'nipa' => $u->nipa,
                'divisi' => $divisiMusyrif,
                'kamar' => $kamarSaya->count(),
                'nama_kamar' => $kamarSaya->pluck('nama_kamar')->sort()->values()->all(),
                'target' => $target,
                'dinilai' => count($dinilaiIds),
                'dinilai_ids' => $dinilaiIds,
                'bermasalah' => $kamarSaya->isEmpty() || $kategori->count() > 1,
            ];
        });

        // Saring per divisi (tabel + angka ringkasan mengikuti saringan yang sama).
        if (in_array($divisi, ['putra', 'putri'], true)) {
            $baris = $baris->where('divisi', $divisi)->values();
        }

        $lengkapSiswa = [];
        foreach ($baris as $b) {
            foreach ($b->dinilai_ids as $sid) {
                $lengkapSiswa[$sid] = true;
            }
        }

        $totalTarget = $baris->sum('target');
        $totalDinilai = $baris->sum('dinilai');
        $musyrifSelesai = $baris->filter(fn ($b) => $b->target > 0 && $b->dinilai >= $b->target)->count();

        $kamarLengkap = $kamar->filter(function ($k) use ($anggotaPerKamar, $lengkapSiswa) {
            $ids = ($anggotaPerKamar[$k->id] ?? collect())->pluck('student_id');

            return $ids->count() > 0 && $ids->every(fn ($sid) => isset($lengkapSiswa[$sid]));
        })->count();

        return view('penilaian.sesi-rapor', [
            'periodeList' => $periodeList,
            'periode' => $periode,
            'divisi' => $divisi,
            'jumlahKriteria' => $jumlahKriteria,
            'baris' => $baris,
            'totalTarget' => $totalTarget,
            'totalDinilai' => $totalDinilai,
            'musyrifSelesai' => $musyrifSelesai,
            'musyrifTotal' => $baris->count(),
            'kamarLengkap' => $kamarLengkap,
            'kamarTotal' => $kamar->count(),
            'penghuniDivisi' => $penghuniDivisi,
            'musyrifTanpaKamar' => $baris->where('bermasalah', true)->values(),
        ]);
    }

    /** Buka sesi isi rapor untuk periode terpilih. */
    public function buka(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:penilaian_periode,id'],
        ]);

        $periode = PenilaianPeriode::findOrFail((int) $data['periode_id']);
        $periode->buka((int) Auth::id());

        return redirect()->route('penilaian.sesi-rapor', ['periode' => $periode->id])
            ->with('success', 'Sesi rapor periode ' . $periode->nama . ' DIBUKA — musyrif/musyrifah sekarang bisa mengisi.');
    }

    /** Tutup sesi isi rapor (pengisian berhenti, seluruh lembar dikunci). */
    public function tutup(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:penilaian_periode,id'],
        ]);

        $periode = PenilaianPeriode::findOrFail((int) $data['periode_id']);
        $periode->tutup((int) Auth::id());

        return redirect()->route('penilaian.sesi-rapor', ['periode' => $periode->id])
            ->with('success', 'Sesi rapor periode ' . $periode->nama . ' DITUTUP — pengisian berhenti, rapor tetap bisa dicetak.');
    }
}
