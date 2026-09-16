<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\PenilaianJawaban;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pengisian penilaian Adab & Keasramaan (kuesioner skala 1-5 per siswa).
 *
 * Satu "sesi" = satu lembar penilaian dari satu penilai untuk satu periode.
 * Adab boleh dinilai dua pihak (Kepala Diniyah & Penanggungjawab Asrama) — masing-masing
 * punya sesinya sendiri, lalu nilainya dirata-ratakan di halaman rekap.
 */
class PenilaianIsiController extends Controller
{
    public function index(Request $request, string $jenis)
    {
        abort_unless(array_key_exists($jenis, PenilaianSesi::JENIS), 404);
        abort_unless(Auth::user()->can('nilai-' . $jenis), 403, 'Anda tidak punya izin mengisi penilaian ' . PenilaianSesi::JENIS[$jenis] . '.');

        $periodeAktif = PenilaianPeriode::aktifSekarang();
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();

        $sesiSaya = PenilaianSesi::with('periode')
            ->where('jenis', $jenis)
            ->where('penilai_id', Auth::id())
            ->orderByDesc('id')
            ->get();

        $sesiLain = PenilaianSesi::with(['periode', 'penilai'])
            ->where('jenis', $jenis)
            ->where('penilai_id', '!=', Auth::id())
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $jumlahKriteria = PenilaianKriteria::daftarAktif($jenis)->count();

        return view('penilaian.isi.index', [
            'jenis' => $jenis,
            'labelJenis' => PenilaianSesi::JENIS[$jenis],
            'periodeAktif' => $periodeAktif,
            'periodeList' => $periodeList,
            'sesiSaya' => $sesiSaya,
            'sesiLain' => $sesiLain,
            'jumlahKriteria' => $jumlahKriteria,
            'peranPilihan' => PenilaianSesi::PERAN,
            'peranBawaan' => $this->peranBawaan(),
        ]);
    }

    /** Buat (atau buka) sesi penilaian saya untuk periode terpilih. */
    public function buatSesi(Request $request, string $jenis)
    {
        abort_unless(array_key_exists($jenis, PenilaianSesi::JENIS), 404);
        abort_unless(Auth::user()->can('nilai-' . $jenis), 403);

        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:penilaian_periode,id'],
            'penilai_peran' => ['nullable', 'string', 'max:40'],
        ]);

        if (PenilaianKriteria::daftarAktif($jenis)->count() === 0) {
            return redirect()->route('penilaian.master')
                ->with('error', 'Belum ada pertanyaan aktif untuk ' . PenilaianSesi::JENIS[$jenis] . '. Tambahkan dulu di Master Penilaian.');
        }

        $sesi = PenilaianSesi::firstOrCreate(
            ['jenis' => $jenis, 'periode_id' => $data['periode_id'], 'penilai_id' => Auth::id()],
            ['penilai_peran' => $data['penilai_peran'] ?: $this->peranBawaan(), 'status' => 'draft']
        );

        return redirect()->route('penilaian.sesi', $sesi->id)
            ->with('success', 'Lembar penilaian ' . PenilaianSesi::JENIS[$jenis] . ' periode ' . ($sesi->periode->nama ?? '-') . ' siap diisi.');
    }

    /** Daftar siswa + progres pengisian satu sesi. */
    public function sesi(Request $request, $id)
    {
        $sesi = PenilaianSesi::with(['periode', 'penilai'])->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);

        $hasil = $sesi->hasilPerSiswa();
        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);
        $terisi = count($hasil);

        $filter = [
            'q' => trim((string) $request->input('q', '')),
            'kelas' => trim((string) $request->input('kelas', '')),
            'kamar' => (int) $request->input('kamar', 0),
            'status' => in_array($request->input('status'), ['sudah', 'belum'], true) ? $request->input('status') : '',
        ];

        $query = Siswa::query()->where('status', 'Aktif');

        if ($filter['q'] !== '') {
            $q = $filter['q'];
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%");
            });
        }
        if ($filter['kelas'] !== '') {
            $query->where('kelas', $filter['kelas']);
        }
        if ($filter['status'] === 'sudah') {
            $query->whereIn('id', array_keys($hasil) ?: [0]);
        } elseif ($filter['status'] === 'belum') {
            $query->whereNotIn('id', array_keys($hasil) ?: [0]);
        }
        if ($filter['kamar'] > 0) {
            $anggota = DB::table('asrama_members')->where('kamar_id', $filter['kamar'])->whereNull('tanggal_keluar')->pluck('student_id')->all();
            $query->whereIn('id', $anggota ?: [0]);
        }

        $siswa = $query->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")->orderBy('nama_lengkap')->paginate(40)->withQueryString();

        // Peta kamar tiap siswa (dipakai untuk tampilan keasramaan)
        $petaKamar = $this->petaKamar($siswa->pluck('id')->all());

        $daftarKamar = PenilaianSesi::JENIS[$sesi->jenis] && $sesi->jenis === 'keasramaan'
            ? AsramaKamar::where('status', 'aktif')->orderBy('nama_kamar')->get()
            : collect();

        $progresKamar = [];
        if ($sesi->jenis === 'keasramaan' && $daftarKamar->isNotEmpty()) {
            $anggotaKamar = DB::table('asrama_members')->whereIn('kamar_id', $daftarKamar->pluck('id'))->whereNull('tanggal_keluar')->get()->groupBy('kamar_id');
            foreach ($daftarKamar as $kamar) {
                $anggota = $anggotaKamar[$kamar->id] ?? collect();
                $sudah = 0;
                foreach ($anggota as $a) {
                    if (isset($hasil[$a->student_id])) {
                        $sudah++;
                    }
                }
                $progresKamar[$kamar->id] = ['nama' => $kamar->nama_kamar, 'total' => $anggota->count(), 'sudah' => $sudah];
            }
        }

        return view('penilaian.isi.sesi', [
            'sesi' => $sesi,
            'siswa' => $siswa,
            'hasil' => $hasil,
            'kriteria' => $kriteria,
            'terisi' => $terisi,
            'filter' => $filter,
            'petaKamar' => $petaKamar,
            'daftarKamar' => $daftarKamar,
            'progresKamar' => $progresKamar,
            'daftarKelas' => \App\Models\Kelas::daftarNama(true),
            'totalSiswa' => Siswa::where('status', 'Aktif')->count(),
        ]);
    }

    /** Form penilaian satu siswa. */
    public function form($id, $siswaId, Request $request)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);
        abort_if($sesi->status === 'final', 403, 'Penilaian ini sudah difinalkan.');

        $siswa = Siswa::findOrFail($siswaId);
        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);

        $jawaban = PenilaianJawaban::where('sesi_id', $sesi->id)
            ->where('siswa_id', $siswa->id)
            ->pluck('skor', 'kriteria_id')
            ->toArray();

        return view('penilaian.isi.form', [
            'sesi' => $sesi,
            'siswa' => $siswa,
            'kriteria' => $kriteria,
            'jawaban' => $jawaban,
            'berikutnya' => $this->siswaBerikutnya($sesi, $siswa->id),
            'kamar' => $this->petaKamar([$siswa->id])[$siswa->id] ?? null,
        ]);
    }

    /** Simpan jawaban satu siswa. */
    public function simpan(Request $request, $id, $siswaId)
    {
        $sesi = PenilaianSesi::findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);

        if ($sesi->status === 'final') {
            return redirect()->route('penilaian.sesi', $sesi->id)->with('error', 'Penilaian sudah difinalkan, tidak bisa diubah.');
        }

        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);
        if ($kriteria->isEmpty()) {
            return redirect()->route('penilaian.master')->with('error', 'Belum ada pertanyaan aktif.');
        }

        $aturan = [];
        foreach ($kriteria as $k) {
            $aturan['skor.' . $k->id] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $request->validate($aturan, [], $this->namaAtribut($kriteria));

        Siswa::findOrFail($siswaId);

        DB::transaction(function () use ($sesi, $kriteria, $request, $siswaId) {
            foreach ($kriteria as $k) {
                PenilaianJawaban::updateOrCreate(
                    ['sesi_id' => $sesi->id, 'siswa_id' => $siswaId, 'kriteria_id' => $k->id],
                    ['skor' => (int) $request->input('skor.' . $k->id)]
                );
            }
        });

        if ($request->boolean('lanjut') && $request->filled('berikutnya')) {
            return redirect()->route('penilaian.sesi.form', [$sesi->id, $request->input('berikutnya')])
                ->with('success', 'Nilai tersimpan. Lanjut ke siswa berikutnya.');
        }

        return redirect()->route('penilaian.sesi', $sesi->id)->with('success', 'Nilai berhasil disimpan.');
    }

    /** Isi cepat satu kamar (skor sama untuk semua penghuni) — khusus keasramaan. */
    public function kamar($id, $kamarId)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);
        abort_unless($sesi->jenis === 'keasramaan', 404);
        abort_if($sesi->status === 'final', 403, 'Penilaian sudah difinalkan.');

        $kamar = AsramaKamar::findOrFail($kamarId);
        $anggotaIds = DB::table('asrama_members')->where('kamar_id', $kamar->id)->whereNull('tanggal_keluar')->pluck('student_id')->all();
        $anggota = Siswa::whereIn('id', $anggotaIds ?: [0])->where('status', 'Aktif')->orderBy('nama_lengkap')->get();

        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);

        // Nilai yang sudah ada dijadikan contoh awal bila semua penghuni seragam.
        $jawaban = [];
        if ($anggota->isNotEmpty()) {
            $perKriteria = PenilaianJawaban::where('sesi_id', $sesi->id)
                ->whereIn('siswa_id', $anggota->pluck('id'))
                ->get()
                ->groupBy('kriteria_id');

            foreach ($perKriteria as $kriteriaId => $kumpulan) {
                $skor = $kumpulan->pluck('skor')->unique();
                if ($skor->count() === 1) {
                    $jawaban[$kriteriaId] = (int) $skor->first();
                }
            }
        }

        return view('penilaian.isi.kamar', [
            'sesi' => $sesi,
            'kamar' => $kamar,
            'anggota' => $anggota,
            'kriteria' => $kriteria,
            'jawaban' => $jawaban,
        ]);
    }

    public function kamarSimpan(Request $request, $id, $kamarId)
    {
        $sesi = PenilaianSesi::findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);

        if ($sesi->status === 'final') {
            return redirect()->route('penilaian.sesi', $sesi->id)->with('error', 'Penilaian sudah difinalkan.');
        }

        $kamar = AsramaKamar::findOrFail($kamarId);
        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);

        $aturan = [];
        foreach ($kriteria as $k) {
            $aturan['skor.' . $k->id] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $request->validate($aturan, [], $this->namaAtribut($kriteria));

        $anggotaIds = DB::table('asrama_members')->where('kamar_id', $kamar->id)->whereNull('tanggal_keluar')->pluck('student_id')->all();
        $anggota = Siswa::whereIn('id', $anggotaIds ?: [0])->where('status', 'Aktif')->pluck('id');

        DB::transaction(function () use ($sesi, $kriteria, $request, $anggota) {
            foreach ($anggota as $siswaId) {
                foreach ($kriteria as $k) {
                    PenilaianJawaban::updateOrCreate(
                        ['sesi_id' => $sesi->id, 'siswa_id' => $siswaId, 'kriteria_id' => $k->id],
                        ['skor' => (int) $request->input('skor.' . $k->id)]
                    );
                }
            }
        });

        return redirect()->route('penilaian.sesi', $sesi->id)
            ->with('success', 'Nilai kamar ' . $kamar->nama_kamar . ' tersimpan untuk ' . $anggota->count() . ' siswa. Silakan koreksi per siswa bila ada yang berbeda.');
    }

    public function finalkan($id)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);

        $terisi = count($sesi->hasilPerSiswa());
        $sesi->update(['status' => 'final', 'difinalkan_pada' => now()]);

        return redirect()->route('penilaian.sesi', $sesi->id)
            ->with('success', 'Penilaian ' . $sesi->label_jenis . ' periode ' . ($sesi->periode->nama ?? '-') . ' difinalkan (' . $terisi . ' siswa terisi).');
    }

    public function buka($id)
    {
        $sesi = PenilaianSesi::findOrFail($id);
        abort_unless(Auth::user()->hasRole('Super Admin'), 403, 'Hanya Super Admin yang bisa membuka penilaian yang sudah final.');

        $sesi->update(['status' => 'draft', 'difinalkan_pada' => null]);

        return redirect()->route('penilaian.sesi', $sesi->id)->with('success', 'Penilaian dibuka kembali (status draft).');
    }

    // ---------------- Bantuan ----------------

    private function izinkanAkses(PenilaianSesi $sesi, bool $hanyaPenilai = false): void
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            return;
        }

        abort_unless($user->can('nilai-' . $sesi->jenis) || $user->can('buka-menu-penilaian'), 403);

        if ($hanyaPenilai && $sesi->penilai_id !== $user->id && ! $user->hasRole('Tata Usaha')) {
            abort(403, 'Lembar penilaian ini milik penilai lain.');
        }
    }

    private function peranBawaan(): string
    {
        return Auth::user()->hasRole('Kepala Diniyah') ? 'kepala_diniyah' : 'penanggungjawab_asrama';
    }

    private function namaAtribut($kriteria): array
    {
        $nama = [];
        foreach ($kriteria as $k) {
            $nama['skor.' . $k->id] = '"' . $k->pertanyaan . '"';
        }

        return $nama;
    }

    /** siswa_id => nama kamar */
    private function petaKamar(array $idSiswa): array
    {
        if (empty($idSiswa)) {
            return [];
        }

        $baris = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereIn('m.student_id', $idSiswa)
            ->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'k.nama_kamar')
            ->get();

        $hasil = [];
        foreach ($baris as $b) {
            $hasil[$b->student_id] = $b->nama_kamar;
        }

        return $hasil;
    }

    /** Siswa berikutnya yang belum dinilai (untuk tombol "simpan & lanjut"). */
    private function siswaBerikutnya(PenilaianSesi $sesi, int $siswaIdSetelah): ?int
    {
        $sudah = array_keys($sesi->hasilPerSiswa());

        return Siswa::where('status', 'Aktif')
            ->whereNotIn('id', $sudah ?: [0])
            ->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")
            ->orderBy('nama_lengkap')
            ->value('id');
    }
}
