<?php

namespace App\Http\Controllers;

use App\Models\DiniyahMapel;
use App\Models\DiniyahMapelKelas;
use App\Models\DiniyahNilai;
use App\Models\DiniyahPeriode;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MATA PELAJARAN KAJIAN DINIYAH (1 Okt 2026)
 * =====================================================================
 * Hanya Kepala Diniyah (izin `kelola-mapel-diniyah`) yang menetapkan mapel,
 * kelas sasaran, dan guru pengampu. Guru pengampu dipilih dari database guru
 * (`users`) ATAU diketik manual bila bukan pengguna SIAKAD.
 *
 * Begitu mapel tersimpan, daftar santri ditarik otomatis dari kelasnya
 * (Data Induk Siswa) — tidak ada pengetikan ulang daftar nama.
 */
class DiniyahMapelController extends Controller
{
    public function index()
    {
        $periode = DiniyahPeriode::aktifSekarang();

        $mapel = DiniyahMapel::with('kelas')->orderBy('urutan')->orderBy('nama')->get();

        $daftarKelas = $this->daftarKelas();

        $ringkas = $mapel->map(function ($m) use ($periode) {
            $santri = $m->santriIds();

            return (object) [
                'mapel'   => $m,
                'kelas'   => $m->daftarKelas(),
                'santri'  => count($santri),
                'terisi'  => DiniyahNilai::jumlahTerisi($m->id, $periode?->id, $santri),
            ];
        });

        return view('diniyah.mapel', [
            'ringkas'     => $ringkas,
            'periode'     => $periode,
            'daftarKelas' => $daftarKelas,
            'daftarGuru'  => $this->daftarGuru(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $mapel = DiniyahMapel::create($data['mapel'] + ['dibuat_oleh' => auth()->id()]);

        $this->simpanKelas($mapel, $data['kelas']);

        return back()->with('success', 'Mata pelajaran "' . $mapel->nama . '" tersimpan untuk kelas ' . implode(', ', $data['kelas']) . '.');
    }

    public function update(Request $request, $id)
    {
        $mapel = DiniyahMapel::findOrFail($id);
        $data = $this->validasi($request);

        $mapel->update($data['mapel']);
        $this->simpanKelas($mapel, $data['kelas']);

        return back()->with('success', 'Mata pelajaran "' . $mapel->nama . '" diperbarui.');
    }

    /** Aktif/nonaktifkan mapel (tidak dihapus supaya nilai lama tetap utuh). */
    public function status(Request $request, $id)
    {
        $mapel = DiniyahMapel::findOrFail($id);
        $mapel->update(['status' => $mapel->status === 'aktif' ? 'nonaktif' : 'aktif']);

        return back()->with('success', 'Mata pelajaran "' . $mapel->nama . '" kini ' . $mapel->status . '.');
    }

    // =================================================================
    //  Bantu
    // =================================================================

    private function validasi(Request $request): array
    {
        $request->validate([
            'nama'       => 'required|string|max:120',
            'nama_arab'  => 'nullable|string|max:120',
            'jp_pekan'   => 'nullable|integer|min:1|max:20',
            'urutan'     => 'nullable|integer|min:0|max:999',
            'kelas'      => 'required|array|min:1',
            'kelas.*'    => 'string|max:20',
            'guru_id'    => 'nullable|integer|exists:users,id',
            'guru_nama'  => 'nullable|string|max:150',
            'guru_nipa'  => 'nullable|string|max:40',
        ], [], ['kelas' => 'kelas sasaran', 'nama' => 'nama mata pelajaran']);

        // Guru pengampu: kalau memilih dari daftar, nama & NIPA ikut dari users.
        $guruId = $request->input('guru_id') ?: null;
        $guruNama = null;
        $guruNipa = null;

        if ($guruId) {
            $guru = User::find($guruId);
            $guruNama = $guru?->name;
            $guruNipa = $guru?->nipa;
        } elseif (trim((string) $request->input('guru_nama')) !== '') {
            $guruNama = trim((string) $request->input('guru_nama'));
            $guruNipa = trim((string) $request->input('guru_nipa')) ?: null;
        }

        return [
            'mapel' => [
                'nama'       => trim((string) $request->input('nama')),
                'nama_arab'  => trim((string) $request->input('nama_arab')) ?: null,
                'jp_pekan'   => $request->input('jp_pekan') ?: null,
                'urutan'     => (int) $request->input('urutan', 0),
                'status'     => $request->input('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
                'guru_id'    => $guruId,
                'guru_nama'  => $guruNama,
                'guru_nipa'  => $guruNipa,
            ],
            'kelas' => array_values(array_unique($request->input('kelas', []))),
        ];
    }

    private function simpanKelas(DiniyahMapel $mapel, array $kelas): void
    {
        DiniyahMapelKelas::where('mapel_id', $mapel->id)->whereNotIn('kelas', $kelas)->delete();

        foreach ($kelas as $k) {
            DiniyahMapelKelas::firstOrCreate(['mapel_id' => $mapel->id, 'kelas' => $k]);
        }
    }

    /** Kelas yang benar-benar dipakai di Data Induk + jumlah santrinya. */
    private function daftarKelas(): array
    {
        $dariInduk = DB::table('siswas')
            ->whereNull('deleted_at')
            ->where('status', 'Aktif')
            ->selectRaw('kelas, COUNT(*) as j')
            ->groupBy('kelas')
            ->pluck('j', 'kelas')
            ->all();

        $hasil = [];

        foreach (Kelas::daftarNama(true) as $nama) {
            $hasil[$nama] = (int) ($dariInduk[$nama] ?? 0);
        }

        foreach ($dariInduk as $nama => $j) {
            if (! array_key_exists($nama, $hasil)) {
                $hasil[$nama] = (int) $j;
            }
        }

        return $hasil;
    }

    /** Sumber "call dari database guru": guru, musyrif, dan pengelola. */
    private function daftarGuru()
    {
        return User::query()
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['Guru', 'Musyrif', 'Kepala Diniyah', 'Kepala Sekolah']))
                    ->orWhereNotNull('nipa');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'nipa', 'jabatan']);
    }
}
