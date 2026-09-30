<?php

namespace App\Http\Controllers;

use App\Models\DiniyahAbsensi;
use App\Models\DiniyahNilai;
use App\Models\DiniyahPeriode;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\User;
use App\Support\DiniyahArab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RAPOR DINIYAH BERBAHASA ARAB (1 Okt 2026)
 * =====================================================================
 * Keputusan Kepala Sekolah:
 *   - rapor berbahasa Arab dengan TERJEMAHAN KECIL bahasa Indonesia di bawah
 *     tiap label;
 *   - nama santri Latin disertai kolom transliterasi Arab (`siswas.nama_arab`);
 *   - nilai = satu nilai akhir per mapel per semester; rekap kehadiran
 *     (hadir/sakit/izin/alpa) diambil otomatis dari absensi pertemuan.
 *
 * `halamanRapor()` sengaja PUBLIC tanpa pemeriksaan izin di dalamnya supaya
 * bisa dipakai DUA jalur: (1) petugas lewat cetak() yang sudah memeriksa izin,
 * dan (2) portal orang tua untuk anaknya sendiri. Satu jalur kode = rapor
 * yang dilihat orang tua persis sama dengan yang dicetak sekolah.
 */
class DiniyahRaporController extends Controller
{
    /** Halaman pemilih santri (petugas). */
    public function index(Request $request)
    {
        $pengguna = ($request->user() ?? auth()->user());
        $periode = $this->periode($request);

        $query = Siswa::query()->whereNull('deleted_at')->where('status', 'Aktif');

        if (! $pengguna->bolehSemuaDiniyah()) {
            $kamarIds = DB::table('asrama_kamars')->where('musyrif_id', $pengguna->id)->pluck('id')->all();

            $anggota = $kamarIds === [] ? [] : DB::table('asrama_members')
                ->whereIn('kamar_id', $kamarIds)->whereNull('tanggal_keluar')->pluck('student_id')->all();

            $query->whereIn('id', $anggota ?: [0]);
        }

        if ($kelas = (string) $request->input('kelas')) {
            $query->where('kelas', $kelas);
        }

        if ($cari = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('nama_lengkap', 'like', "%{$cari}%")->orWhere('nisn', 'like', "%{$cari}%"));
        }

        $santri = $query->orderByRaw("CASE kelas WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nama_arab', 'nisn', 'kelas']);

        // Progres: berapa mapel yang sudah bernilai untuk tiap santri.
        $jumlahMapel = DB::table('diniyah_mapels')->where('status', 'aktif')->count();

        return view('diniyah.rapor-pilih', [
            'periode'     => $periode,
            'periodeList' => DiniyahPeriode::orderByDesc('id')->get(),
            'santri'      => $santri,
            'kelas'       => (string) $request->input('kelas'),
            'q'           => (string) $request->input('q'),
            'jumlahMapel' => $jumlahMapel,
            'tanpaArab'   => $santri->whereNull('nama_arab')->count(),
        ]);
    }

    /** Cetak untuk petugas (sudah lewat middleware izin `cetak-rapor-diniyah`). */
    public function cetak(Request $request)
    {
        $siswaId = (int) $request->input('siswa', 0);

        if ($siswaId < 1) {
            return redirect()->route('diniyah.rapor')->with('error', 'Pilih dulu santri yang mau dicetak rapornya.');
        }

        return $this->halamanRapor($request, $siswaId);
    }

    /**
     * HALAMAN RAPOR SESUNGGUHNYA. Dipakai petugas dan portal orang tua.
     * Pemanggil WAJIB memastikan hak akses (portal: anak sesuai NISN yang masuk).
     */
    public function halamanRapor(Request $request, int $siswaId)
    {
        $siswa = Siswa::where('id', $siswaId)->whereNull('deleted_at')->first();

        if (! $siswa) {
            return redirect()->route('diniyah.rapor')->with('error', 'Santri tidak ditemukan.');
        }

        $periode = $this->periode($request);
        $nilai = DiniyahNilai::untukRapor($siswa->id, $periode?->id);
        $rekap = DiniyahAbsensi::rekapSiswa($siswa->id, $periode?->id);
        $rekapMapel = DiniyahAbsensi::rekapSiswaPerMapel($siswa->id, $periode?->id);

        return view('diniyah.rapot', [
            'siswa'        => $siswa,
            'periode'      => $periode,
            'nilai'        => $nilai,
            'rekap'        => $rekap,
            'rekapMapel'   => $rekapMapel,
            'pengaturan'   => Pengaturan::first(),
            'kepala'       => $this->pejabat('Kepala Sekolah'),
            'kepalaDiniyah' => $this->pejabat('Kepala Diniyah'),
            'kamar'        => $this->kamar($siswa->id),
            'kembaliKe'    => $request->input('kembaliKe'),
            'modeOrtu'     => (bool) $request->input('modeOrtu'),
            'tahunHijriah' => $periode?->tahun_hijriah ?: DiniyahArab::tahunHijriah(),
        ]);
    }

    private function periode(Request $request): ?DiniyahPeriode
    {
        $id = (int) $request->input('periode', 0);

        if ($id > 0) {
            return DiniyahPeriode::find($id) ?? DiniyahPeriode::aktifSekarang();
        }

        return DiniyahPeriode::aktifSekarang();
    }

    /** Nama + NIPA pejabat berdasarkan perannya (untuk kolom tanda tangan). */
    private function pejabat(string $peran): ?object
    {
        $baris = DB::table('users as u')
            ->join('model_has_roles as mr', 'mr.model_id', '=', 'u.id')
            ->join('roles as r', function ($j) use ($peran) {
                $j->on('r.id', '=', 'mr.role_id')->where('r.name', '=', $peran);
            })
            ->select('u.name as nama', 'u.nipa')
            ->first();

        return $baris ?: null;
    }

    private function kamar(int $siswaId): ?object
    {
        return DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->leftJoin('users as u', 'u.id', '=', 'k.musyrif_id')
            ->where('m.student_id', $siswaId)
            ->whereNull('m.tanggal_keluar')
            ->select('k.nama_kamar', 'u.name as musyrif')
            ->first();
    }
}
