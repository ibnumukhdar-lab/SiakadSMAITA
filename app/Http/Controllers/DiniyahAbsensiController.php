<?php

namespace App\Http\Controllers;

use App\Models\DiniyahAbsensi;
use App\Models\DiniyahMapel;
use App\Models\DiniyahPeriode;
use App\Models\DiniyahPertemuan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ABSENSI KAJIAN DINIYAH (1 Okt 2026)
 * =====================================================================
 * Keputusan Kepala Sekolah: absensi dicatat PER PERTEMUAN kajian
 * (tanggal + mapel + jam ke) dan rekapnya (hadir/sakit/izin/alpa) masuk rapor
 * otomatis — bukan diketik manual.
 *
 * Alur pakai: pilih mapel → catat pertemuan baru (tanggal, jam ke, materi) →
 * tandai status tiap santri. Ada tombol "semua hadir" supaya pengisian cepat,
 * lalu tinggal mengubah yang tidak hadir.
 *
 * Musyrif/musyrifah hanya melihat santri kamar binaannya; guru pengampu &
 * Kepala Diniyah melihat seluruh kelas mapel.
 */
class DiniyahAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $pengguna = ($request->user() ?? auth()->user());
        $periode = DiniyahPeriode::aktifSekarang();

        $mapelList = DiniyahMapel::aktif()->with('kelas')->orderBy('urutan')->orderBy('nama')->get()
            ->filter(fn ($m) => count($m->santriUntukPengguna($pengguna)) > 0)->values();

        $mapelId = (int) $request->input('mapel', 0) ?: (int) ($mapelList->first()->id ?? 0);
        $terpilih = $mapelList->firstWhere('id', $mapelId);

        $pertemuanList = $terpilih
            ? DiniyahPertemuan::where('mapel_id', $terpilih->id)->orderByDesc('tanggal')->orderByDesc('jam_ke')->limit(12)->get()
            : collect();

        $pertemuanId = (int) $request->input('pertemuan', 0) ?: (int) ($pertemuanList->first()->id ?? 0);
        $pertemuan = $pertemuanList->firstWhere('id', $pertemuanId);

        $santriIds = $terpilih ? $terpilih->santriUntukPengguna($pengguna) : [];
        $daftar = collect();

        if ($pertemuan && $santriIds !== []) {
            $santri = Siswa::whereIn('id', $santriIds)
                ->orderByRaw("CASE kelas WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nama_arab', 'kelas']);

            $tersimpan = DiniyahAbsensi::where('pertemuan_id', $pertemuan->id)->get()->keyBy('siswa_id');

            $daftar = $santri->map(fn ($s) => (object) [
                'siswa'      => $s,
                'status'     => $tersimpan[$s->id]->status ?? 'hadir',
                'keterangan' => $tersimpan[$s->id]->keterangan ?? null,
                'pernah'     => isset($tersimpan[$s->id]),
            ]);
        }

        return view('diniyah.absensi', [
            'periode'       => $periode,
            'mapelList'     => $mapelList,
            'terpilih'      => $terpilih,
            'pertemuanList' => $pertemuanList,
            'pertemuan'     => $pertemuan,
            'daftar'        => $daftar,
            'statusList'    => DiniyahAbsensi::STATUS,
            'rekap'         => $pertemuan ? $pertemuan->rekap() : null,
            'sudahTercatat' => $pertemuan ? DiniyahAbsensi::where('pertemuan_id', $pertemuan->id)->count() : 0,
        ]);
    }

    /** Catat pertemuan baru (tanggal + jam ke + materi). */
    public function simpanPertemuan(Request $request)
    {
        $request->validate([
            'mapel_id'  => 'required|integer|exists:diniyah_mapels,id',
            'tanggal'   => 'required|date',
            'jam_ke'    => 'nullable|integer|min:1|max:20',
            'materi'    => 'nullable|string|max:180',
        ]);

        $mapel = DiniyahMapel::findOrFail($request->input('mapel_id'));
        $periode = DiniyahPeriode::aktifSekarang();

        $pertemuan = DiniyahPertemuan::firstOrCreate(
            [
                'mapel_id' => $mapel->id,
                'tanggal'  => $request->input('tanggal'),
                'jam_ke'   => $request->input('jam_ke') ?: null,
            ],
            [
                'periode_id'     => $periode?->id,
                'materi'         => trim((string) $request->input('materi')) ?: null,
                'guru_pengampu'  => $mapel->guruTampil(),
                'dicatat_oleh'   => auth()->id(),
            ]
        );

        if (! $pertemuan->wasRecentlyCreated) {
            $pertemuan->update(['materi' => trim((string) $request->input('materi')) ?: $pertemuan->materi]);
        }

        return redirect()->route('diniyah.absensi', ['mapel' => $mapel->id, 'pertemuan' => $pertemuan->id])
            ->with('success', 'Pertemuan ' . $pertemuan->tanggal->format('d/m/Y') . ' — ' . $mapel->nama . ' siap diisi absensinya.');
    }

    /** Simpan kehadiran seluruh santri pada satu pertemuan. */
    public function simpanAbsensi(Request $request)
    {
        $pengguna = ($request->user() ?? auth()->user());

        $request->validate([
            'pertemuan_id' => 'required|integer|exists:diniyah_pertemuan,id',
            'status'       => 'nullable|array',
            'status.*'     => 'in:hadir,sakit,izin,alpa',
            'keterangan'   => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:150',
        ]);

        $pertemuan = DiniyahPertemuan::with('mapel')->findOrFail($request->input('pertemuan_id'));
        $boleh = $pertemuan->mapel->santriUntukPengguna($pengguna);

        if (! $pengguna->bolehSemuaDiniyah() && $boleh === []) {
            abort(403, 'Anda tidak berhak mengisi absensi mapel ini.');
        }

        $jumlah = 0;

        DB::transaction(function () use ($request, $pertemuan, $pengguna, $boleh, &$jumlah) {
            foreach ($request->input('status', []) as $siswaId => $status) {
                $siswaId = (int) $siswaId;

                if (! $pengguna->bolehSemuaDiniyah() && ! in_array($siswaId, $boleh, true)) {
                    continue;
                }

                $keterangan = $request->input('keterangan.' . $siswaId);

                DiniyahAbsensi::updateOrCreate(
                    ['pertemuan_id' => $pertemuan->id, 'siswa_id' => $siswaId],
                    ['status' => $status ?: 'hadir', 'keterangan' => $keterangan ? trim((string) $keterangan) : null]
                );

                $jumlah++;
            }
        });

        return back()->with('success', 'Absensi ' . $pertemuan->mapel->nama . ' (' . $pertemuan->tanggal->format('d/m/Y') . ') tersimpan: ' . $jumlah . ' santri.');
    }
}
