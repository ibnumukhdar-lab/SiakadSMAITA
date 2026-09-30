<?php

namespace App\Http\Controllers;

use App\Models\DiniyahMapel;
use App\Models\DiniyahNilai;
use App\Models\DiniyahPeriode;
use App\Models\Siswa;
use Illuminate\Http\Request;

/**
 * INPUT NILAI KAJIAN DINIYAH (1 Okt 2026)
 * =====================================================================
 * Keputusan Kepala Sekolah: MUSYRIF/MUSYRIFAH yang mengisi, GURU PENGAMPU
 * boleh mengoreksi. Karena itu:
 *   - musyrif/musyrifah hanya melihat santri kamar binaannya;
 *   - guru pengampu mapel + Kepala Diniyah melihat seluruh kelas mapel itu;
 *   - setiap perubahan menyimpan siapa yang mengisi (penilai_id) dan, bila
 *     yang menyimpan adalah guru pengampu, siapa yang mengoreksi
 *     (dikoreksi_oleh + dikoreksi_pada) — jadi jejaknya jelas.
 *
 * Satu santri = satu nilai akhir per mapel per semester (tidak dipecah
 * tugas/UTS/UAS sesuai keputusan pemilik sekolah).
 */
class DiniyahNilaiController extends Controller
{
    public function index(Request $request)
    {
        $pengguna = ($request->user() ?? auth()->user());
        $periode = $this->periode($request);

        $mapel = DiniyahMapel::aktif()->with('kelas')->orderBy('urutan')->orderBy('nama')->get();

        // Musyrif hanya menerima mapel yang kelasnya bersinggungan dengan kamar binaannya;
        // guru pengampu menerima mapel yang diampu; pengelola menerima semua.
        $mapel = $mapel->filter(fn ($m) => count($m->santriUntukPengguna($pengguna)) > 0)->values();

        $mapelId = (int) $request->input('mapel', 0) ?: (int) ($mapel->first()->id ?? 0);
        $terpilih = $mapel->firstWhere('id', $mapelId);

        $santriIds = $terpilih ? $terpilih->santriUntukPengguna($pengguna) : [];

        $daftar = collect();

        if ($santriIds !== []) {
            $santri = Siswa::whereIn('id', $santriIds)
                ->orderByRaw("CASE kelas WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nama_arab', 'kelas', 'nisn']);

            $nilai = $periode
                ? DiniyahNilai::where('periode_id', $periode->id)->where('mapel_id', $terpilih->id)->whereIn('siswa_id', $santriIds)->get()->keyBy('siswa_id')
                : collect();

            $daftar = $santri->map(fn ($s) => (object) [
                'siswa'    => $s,
                'nilai'    => $nilai[$s->id]->nilai ?? null,
                'catatan'  => $nilai[$s->id]->catatan ?? null,
                'dikoreksi' => $nilai[$s->id]->dikoreksi_pada ?? null,
            ]);
        }

        return view('diniyah.nilai', [
            'periode'      => $periode,
            'periodeList'  => DiniyahPeriode::orderByDesc('id')->get(),
            'mapelList'    => $mapel,
            'terpilih'     => $terpilih,
            'daftar'       => $daftar,
            'bolehSemua'   => $pengguna->bolehSemuaDiniyah(),
            'adalahGuru'   => $terpilih && (int) $terpilih->guru_id === (int) $pengguna->id,
        ]);
    }

    public function simpan(Request $request)
    {
        $pengguna = ($request->user() ?? auth()->user());

        $request->validate([
            'periode_id' => 'required|integer|exists:diniyah_periode,id',
            'mapel_id'   => 'required|integer|exists:diniyah_mapels,id',
            'nilai'      => 'nullable|array',
            'nilai.*'    => 'nullable|integer|min:0|max:100',
            'catatan'    => 'nullable|array',
            'catatan.*'  => 'nullable|string|max:150',
        ]);

        $periode = DiniyahPeriode::findOrFail($request->input('periode_id'));
        $mapel = DiniyahMapel::findOrFail($request->input('mapel_id'));

        $boleh = $mapel->santriUntukPengguna($pengguna);
        $adalahGuruPengampu = (int) $mapel->guru_id === (int) $pengguna->id;

        if (! $pengguna->bolehSemuaDiniyah() && ! $adalahGuruPengampu && $boleh === []) {
            abort(403, 'Anda tidak berhak mengisi nilai untuk mata pelajaran ini.');
        }

        $tersimpan = 0;

        foreach ($request->input('nilai', []) as $siswaId => $nilai) {
            $siswaId = (int) $siswaId;

            if (! $pengguna->bolehSemuaDiniyah() && ! $adalahGuruPengampu && ! in_array($siswaId, $boleh, true)) {
                continue; // di luar kewenangan
            }

            $catatan = $request->input('catatan.' . $siswaId);

            $baris = [
                'nilai' => ($nilai === null || $nilai === '') ? null : (int) $nilai,
                'catatan' => $catatan ? trim((string) $catatan) : null,
            ];

            if ($adalahGuruPengampu && ! $pengguna->hasRole('Musyrif')) {
                // Guru pengampu = pengoreksi.
                $baris['dikoreksi_oleh'] = $pengguna->id;
                $baris['dikoreksi_pada'] = now();
            } else {
                $baris['penilai_id'] = $pengguna->id;
            }

            DiniyahNilai::updateOrCreate(
                ['periode_id' => $periode->id, 'mapel_id' => $mapel->id, 'siswa_id' => $siswaId],
                $baris
            );

            $tersimpan++;
        }

        return back()->with('success', $tersimpan . ' nilai ' . $mapel->nama . ' tersimpan untuk ' . $periode->label() . '.');
    }

    private function periode(Request $request): ?DiniyahPeriode
    {
        $id = (int) $request->input('periode', 0);

        if ($id > 0) {
            return DiniyahPeriode::find($id) ?? DiniyahPeriode::aktifSekarang();
        }

        return DiniyahPeriode::aktifSekarang();
    }
}
