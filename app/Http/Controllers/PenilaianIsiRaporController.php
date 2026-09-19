<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\CatatanRaport;
use App\Models\PenilaianJawaban;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ISI RAPOR ADAB & KEASRAMaan (19 Sep 2026) — alur sederhana pengganti
 * "buat lembar sendiri per jenis".
 *
 * ATURAN:
 * 1. Sesi dibuka/ditutup SEKALI oleh pengelola (TU / Super Admin / Kepala Sekolah /
 *    Kepala Diniyah) di halaman Sesi & Progres. Selama sesi terbuka musyrif mengisi;
 *    setelah ditutup semua pengisian berhenti (tidak ada lagi tombol finalkan per lembar).
 * 2. Yang mengisi HANYA musyrif/musyrifah (izin nilai-adab / nilai-keasramaan).
 * 3. Cakupan = SELURUH DIVISI (putra semua / putri semua) supaya rata-rata rapor sahih.
 * 4. SATU santri = SATU formulir berisi bagian A (Adab) + B (Keasramaan); lembar
 *    penilaian per jenis tetap dibuat otomatis di belakang layar (unik jenis+periode+penilai).
 * 5. Navigasi: daftar kamar divisi -> daftar penghuni kamar -> formulir satu anak.
 */
class PenilaianIsiRaporController extends Controller
{
    /** Halaman utama: status sesi + daftar kamar divisi beserta progres. */
    public function index()
    {
        $user = Auth::user();
        abort_unless($this->punyaIzinIsi($user), 403, 'Hanya musyrif/musyrifah yang bisa mengisi rapor Adab & Keasramaan.');

        $periode = PenilaianPeriode::terbukaSekarang();
        $divisi = $this->divisiSaya();
        $kamar = $this->kamarDivisi();
        $kamarBinaanIds = $this->kamarSaya()->pluck('id')->all();

        $kriteria = [
            'adab' => PenilaianKriteria::daftarAktif('adab')->count(),
            'keasramaan' => PenilaianKriteria::daftarAktif('keasramaan')->count(),
        ];

        $status = $this->statusPerSiswa($periode);

        $semuaKamar = $kamar->map(function ($k) use ($status, $kamarBinaanIds) {
            $anggota = $this->anggotaKamarIds($k->id);
            $lengkap = 0;
            $sebagian = 0;

            foreach ($anggota as $sid) {
                if (($status[$sid]['lengkap'] ?? false)) {
                    $lengkap++;
                } elseif (($status[$sid]['terisi'] ?? false)) {
                    $sebagian++;
                }
            }

            return (object) [
                'id' => $k->id,
                'nama' => $k->nama_kamar,
                'kategori' => $k->kategori,
                'musyrif' => optional($k->musyrif)->name,
                'binaan' => in_array($k->id, $kamarBinaanIds, true),
                'total' => count($anggota),
                'lengkap' => $lengkap,
                'sebagian' => $sebagian,
            ];
        });

        $kamarBinaan = $semuaKamar->where('binaan', true)->sortBy('nama')->values();
        $kamarLain = $semuaKamar->where('binaan', false)->sortBy('nama')->values();

        return view('penilaian.isi-rapor.index', [
            'periode' => $periode,
            'periodeAktif' => PenilaianPeriode::aktifSekarang(),
            'terbuka' => (bool) ($periode?->terbuka),
            'divisi' => $divisi,
            'jumlahKriteria' => $kriteria,
            'kamarBinaan' => $kamarBinaan,
            'kamarLain' => $kamarLain,
            'totalTarget' => $semuaKamar->sum('total'),
            'totalLengkap' => $semuaKamar->sum('lengkap'),
            'totalSebagian' => $semuaKamar->sum('sebagian'),
            'kamarSelesai' => $semuaKamar->filter(fn ($k) => $k->total > 0 && $k->lengkap >= $k->total)->count(),
            'jumlahKamar' => $semuaKamar->count(),
        ]);
    }

    /** Daftar penghuni satu kamar + tombol menuju formulir tiap anak. */
    public function kamar($kamarId)
    {
        $user = Auth::user();
        abort_unless($this->punyaIzinIsi($user), 403, 'Hanya musyrif/musyrifah yang bisa mengisi rapor.');

        $periode = PenilaianPeriode::terbukaSekarang();
        $kamar = AsramaKamar::with('musyrif')->findOrFail($kamarId);

        abort_unless($this->bolehKamar($kamar), 403, 'Kamar ini bukan bagian dari divisi Anda.');

        $anggota = Siswa::whereIn('id', $this->anggotaKamarIds($kamar->id) ?: [0])
            ->where('status', 'Aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $status = $this->statusPerSiswa($periode, $anggota->pluck('id')->all());
        $binaanSaya = in_array($kamar->id, $this->kamarSaya()->pluck('id')->all(), true);

        return view('penilaian.isi-rapor.kamar', [
            'periode' => $periode,
            'terbuka' => (bool) ($periode?->terbuka),
            'kamar' => $kamar,
            'binaanSaya' => $binaanSaya,
            'anggota' => $anggota,
            'status' => $status,
            'lengkap' => $anggota->filter(fn ($a) => $status[$a->id]['lengkap'] ?? false)->count(),
        ]);
    }

    /** Formulir SATU santri: bagian A Adab + B Keasramaan + catatan rapor. */
    public function form($siswaId)
    {
        $user = Auth::user();
        abort_unless($this->punyaIzinIsi($user), 403, 'Hanya musyrif/musyrifah yang bisa mengisi rapor.');

        $periode = PenilaianPeriode::terbukaSekarang();
        $siswa = Siswa::findOrFail($siswaId);
        $this->pastikanSiswaDivisi((int) $siswa->id);

        $kamar = $this->kamarSiswa($siswa->id);
        $kamarId = $this->kamarIdSiswa($siswa->id);

        $kriteria = [
            'adab' => PenilaianKriteria::daftarAktif('adab'),
            'keasramaan' => PenilaianKriteria::daftarAktif('keasramaan'),
        ];

        $jawaban = [];
        foreach (array_keys($kriteria) as $jenis) {
            $sesi = $this->sesiSaya($periode, $jenis, hanyaBaca: true);
            $jawaban[$jenis] = $sesi
                ? PenilaianJawaban::where('sesi_id', $sesi->id)->where('siswa_id', $siswa->id)->pluck('skor', 'kriteria_id')->toArray()
                : [];
        }

        $anggota = $kamarId ? $this->anggotaKamarIds($kamarId) : [];

        return view('penilaian.isi-rapor.form', [
            'periode' => $periode,
            'terbuka' => (bool) ($periode?->terbuka),
            'siswa' => $siswa,
            'kamar' => $kamar,
            'kamarId' => $kamarId,
            'kriteria' => $kriteria,
            'jawaban' => $jawaban,
            'catatan' => $periode ? CatatanRaport::where('jenis', 'adab')
                ->where('siswa_id', $siswa->id)
                ->where('penilaian_periode_id', $periode->id)
                ->first() : null,
            'berikutnya' => $this->siswaBerikutnya($siswa->id, $anggota),
            'urutan' => array_search((int) $siswa->id, array_map('intval', $anggota), true),
            'jumlahAnggota' => count($anggota),
        ]);
    }

    /** Simpan jawaban bagian A + B satu santri (dan catatan rapor bila diisi). */
    public function simpan(Request $request, $siswaId)
    {
        $user = Auth::user();
        abort_unless($this->punyaIzinIsi($user), 403, 'Hanya musyrif/musyrifah yang bisa mengisi rapor.');

        $periode = PenilaianPeriode::terbukaSekarang();

        if (! $periode || ! $periode->terbuka) {
            return redirect()->route('penilaian.isi.rapor')
                ->with('error', 'Sesi isi rapor sudah ditutup. Hubungi TU / Kepala Diniyah bila perlu dibuka kembali.');
        }

        $siswa = Siswa::findOrFail($siswaId);
        $this->pastikanSiswaDivisi((int) $siswa->id);

        $kriteria = [
            'adab' => PenilaianKriteria::daftarAktif('adab'),
            'keasramaan' => PenilaianKriteria::daftarAktif('keasramaan'),
        ];

        if ($kriteria['adab']->isEmpty() && $kriteria['keasramaan']->isEmpty()) {
            return redirect()->route('penilaian.master')->with('error', 'Belum ada pertanyaan aktif pada master penilaian.');
        }

        $aturan = [];
        $namaAtribut = [];
        foreach ($kriteria as $jenis => $daftar) {
            foreach ($daftar as $k) {
                $aturan["skor.{$jenis}.{$k->id}"] = ['required', 'integer', 'min:1', 'max:5'];
                $namaAtribut["skor.{$jenis}.{$k->id}"] = PenilaianSesi::JENIS[$jenis] . ' — "' . $k->pertanyaan . '"';
            }
        }

        $request->validate($aturan, [], $namaAtribut);

        DB::transaction(function () use ($periode, $kriteria, $request, $siswa) {
            foreach ($kriteria as $jenis => $daftar) {
                if ($daftar->isEmpty()) {
                    continue;
                }

                $sesi = $this->sesiSaya($periode, $jenis);

                foreach ($daftar as $k) {
                    PenilaianJawaban::updateOrCreate(
                        ['sesi_id' => $sesi->id, 'siswa_id' => $siswa->id, 'kriteria_id' => $k->id],
                        ['skor' => (int) $request->input("skor.{$jenis}.{$k->id}")]
                    );
                }
            }

            $this->simpanCatatan($request, $periode, $siswa);
        });

        $kamar = $this->kamarSiswa($siswa->id);
        $pesan = 'Nilai ' . $siswa->nama_lengkap . ($kamar ? ' (kamar ' . $kamar . ')' : '') . ' tersimpan — Adab & Keasramaan sekaligus.';

        if ($request->boolean('lanjut') && $request->filled('berikutnya')) {
            return redirect()->route('penilaian.isi.form', $request->input('berikutnya'))->with('success', $pesan . ' Lanjut ke anak berikutnya.');
        }

        if ($request->input('kembali') === 'kamar' && $this->kamarIdSiswa($siswa->id)) {
            return redirect()->route('penilaian.isi.kamar', $this->kamarIdSiswa($siswa->id))->with('success', $pesan);
        }

        return redirect()->route('penilaian.isi.rapor')->with('success', $pesan);
    }

    // =====================================================================
    //  Bantuan
    // =====================================================================

    /** Catatan rapor ikut disimpan dari formulir anak (opsional). */
    private function simpanCatatan(Request $request, PenilaianPeriode $periode, Siswa $siswa): void
    {
        if (! $request->has('catatan')) {
            return;
        }

        $user = Auth::user();

        if (! CatatanRaport::bolehTulisAdab($user, $siswa)) {
            return;
        }

        $catatan = CatatanRaport::firstOrNew([
            'jenis' => 'adab',
            'siswa_id' => $siswa->id,
            'penilaian_periode_id' => $periode->id,
        ]);

        $isi = trim((string) $request->input('catatan', ''));

        if ($isi === '') {
            if ($catatan->exists) {
                $catatan->delete();
            }

            return;
        }

        $catatan->isi = mb_substr($isi, 0, CatatanRaport::MAKS);
        $catatan->penulis_id = $user->id;
        $catatan->save();
    }

    private function punyaIzinIsi($user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('nilai-adab') || $user->can('nilai-keasramaan');
    }

    /** Lembar penilaian saya (dibuat otomatis di belakang layar, tidak perlu ditekan musyrif). */
    private function sesiSaya(?PenilaianPeriode $periode, string $jenis, bool $hanyaBaca = false): ?PenilaianSesi
    {
        if (! $periode) {
            return null;
        }

        if ($hanyaBaca) {
            return PenilaianSesi::where('jenis', $jenis)
                ->where('periode_id', $periode->id)
                ->where('penilai_id', Auth::id())
                ->first();
        }

        return PenilaianSesi::firstOrCreate(
            ['jenis' => $jenis, 'periode_id' => $periode->id, 'penilai_id' => Auth::id()],
            ['penilai_peran' => 'musyrif', 'status' => 'draft']
        );
    }

    /**
     * Status penilaian tiap santri pada periode berjalan:
     * siswa_id => ['terisi' => bool (ada jawaban), 'lengkap' => bool (Adab & Keasramaan penuh)].
     */
    private function statusPerSiswa(?PenilaianPeriode $periode, ?array $siswaIds = null): array
    {
        if (! $periode) {
            return [];
        }

        $jumlah = [
            'adab' => PenilaianKriteria::daftarAktif('adab')->count(),
            'keasramaan' => PenilaianKriteria::daftarAktif('keasramaan')->count(),
        ];

        $sesiIds = PenilaianSesi::where('periode_id', $periode->id)
            ->where('penilai_id', Auth::id())
            ->pluck('id', 'jenis');

        if ($sesiIds->isEmpty()) {
            return [];
        }

        $query = PenilaianJawaban::query()
            ->whereIn('sesi_id', $sesiIds->values()->all())
            ->selectRaw('siswa_id, sesi_id, COUNT(*) as jumlah')
            ->groupBy('siswa_id', 'sesi_id');

        if (! empty($siswaIds)) {
            $query->whereIn('siswa_id', $siswaIds);
        }

        $jenisPerSesi = $sesiIds->flip(); // sesi_id => jenis
        $status = [];

        foreach ($query->get() as $baris) {
            $jenis = $jenisPerSesi[$baris->sesi_id] ?? null;

            if (! $jenis) {
                continue;
            }

            $status[$baris->siswa_id]['terisi'] = true;
            $status[$baris->siswa_id][$jenis] = ((int) $baris->jumlah) >= ($jumlah[$jenis] ?? 0);
        }

        foreach ($status as $sid => $d) {
            $status[$sid]['lengkap'] = ($d['adab'] ?? false) && ($d['keasramaan'] ?? false);
        }

        return $status;
    }

    /** Kamar BINAAN saya (Super Admin = semua kamar aktif). */
    private function kamarSaya()
    {
        $query = AsramaKamar::with('musyrif')->where('status', 'aktif');

        if (! Auth::user()->hasRole('Super Admin')) {
            $query->where('musyrif_id', Auth::id());
        }

        return $query->get();
    }

    /** Divisi penilai: 'putra'/'putri' dari kategori kamar binaannya (null = belum dipetakan). */
    private function divisiSaya(): ?string
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return null;
        }

        $kategori = AsramaKamar::where('musyrif_id', Auth::id())
            ->where('status', 'aktif')
            ->distinct()
            ->pluck('kategori')
            ->all();

        return count($kategori) === 1 ? $kategori[0] : null;
    }

    /** Seluruh kamar aktif pada divisi saya (Super Admin: semua). */
    private function kamarDivisi()
    {
        $query = AsramaKamar::with('musyrif')->where('status', 'aktif');

        if (Auth::user()->hasRole('Super Admin')) {
            return $query->orderBy('nama_kamar')->get();
        }

        $divisi = $this->divisiSaya();

        if ($divisi === null) {
            return collect();
        }

        return $query->where('kategori', $divisi)->orderBy('nama_kamar')->get();
    }

    private function bolehKamar(AsramaKamar $kamar): bool
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return true;
        }

        $divisi = $this->divisiSaya();

        return $divisi !== null && $kamar->kategori === $divisi;
    }

    private function anggotaKamarIds($kamarId): array
    {
        return DB::table('asrama_members')
            ->where('kamar_id', $kamarId)
            ->whereNull('tanggal_keluar')
            ->pluck('student_id')
            ->all();
    }

    private function kamarSiswa($siswaId): ?string
    {
        $nama = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('m.student_id', $siswaId)
            ->whereNull('m.tanggal_keluar')
            ->value('k.nama_kamar');

        return $nama ?: null;
    }

    private function kamarIdSiswa($siswaId): ?int
    {
        $id = DB::table('asrama_members')
            ->where('student_id', $siswaId)
            ->whereNull('tanggal_keluar')
            ->value('kamar_id');

        return $id ? (int) $id : null;
    }

    /** Pastikan santri memang penghuni kamar pada DIVISI penilai (Super Admin bebas). */
    private function pastikanSiswaDivisi(int $siswaId): void
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return;
        }

        $kamarIds = $this->kamarDivisi()->pluck('id')->all();

        $satuDivisi = DB::table('asrama_members')
            ->where('student_id', $siswaId)
            ->whereNull('tanggal_keluar')
            ->whereIn('kamar_id', $kamarIds ?: [0])
            ->exists();

        abort_unless($satuDivisi, 403, 'Santri ini bukan bagian dari divisi Anda (putra/putri).');
    }

    /** Anak berikutnya DI KAMAR YANG SAMA (untuk tombol simpan & lanjut). */
    private function siswaBerikutnya(int $siswaId, array $anggotaKamar): ?int
    {
        $anggotaKamar = array_values(array_map('intval', $anggotaKamar));
        $posisi = array_search($siswaId, $anggotaKamar, true);

        if ($posisi === false) {
            return null;
        }

        return $anggotaKamar[$posisi + 1] ?? null;
    }
}
