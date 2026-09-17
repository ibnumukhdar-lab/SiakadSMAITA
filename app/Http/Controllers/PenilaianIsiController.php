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
 * Pengisian penilaian Adab & Keasramaan (kuesioner skala 1-5 PER SISWA).
 *
 * ATURAN (permintaan Fahri, 17 Sep 2026):
 * 1. Yang menilai HANYA musyrif/musyrifah pemilik lembar (izin nilai-adab / nilai-keasramaan).
 *    Musyrif hanya mengakses anak KAMAR BINAANNYA (asrama_kamars.musyrif_id).
 * 2. Alur per anak: daftar kamar binaan -> daftar penghuni kamar -> formulir satu anak.
 *    Tidak ada daftar seluruh siswa sekolah di satu halaman.
 * 3. TIDAK ada penilaian serentak per kamar (isi cepat dihapus); semua dinilai satu per satu.
 *
 * Satu "sesi" = satu lembar penilaian dari satu penilai untuk satu periode.
 * Super Admin tetap bisa membuka kamar mana pun sebagai jalan perbaikan data.
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

        $kamarBinaan = $this->kamarSaya();
        $totalBinaan = DB::table('asrama_members')
            ->whereIn('kamar_id', $kamarBinaan->pluck('id')->all() ?: [0])
            ->whereNull('tanggal_keluar')
            ->count();

        return view('penilaian.isi.index', [
            'jenis' => $jenis,
            'labelJenis' => PenilaianSesi::JENIS[$jenis],
            'periodeAktif' => $periodeAktif,
            'periodeList' => $periodeList,
            'sesiSaya' => $sesiSaya,
            'sesiLain' => $sesiLain,
            'jumlahKriteria' => $jumlahKriteria,
            'peranPilihan' => PenilaianSesi::PERAN_PILIHAN,
            'peranBawaan' => $this->peranBawaan(),
            'jumlahKamarBinaan' => $kamarBinaan->count(),
            'totalBinaan' => $totalBinaan,
        ]);
    }

    /** Buat (atau buka) sesi penilaian saya untuk periode terpilih. */
    public function buatSesi(Request $request, string $jenis)
    {
        abort_unless(array_key_exists($jenis, PenilaianSesi::JENIS), 404);
        abort_unless(Auth::user()->can('nilai-' . $jenis), 403);

        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:penilaian_periode,id'],
        ]);

        if (PenilaianKriteria::daftarAktif($jenis)->count() === 0) {
            return redirect()->route('penilaian.master')
                ->with('error', 'Belum ada pertanyaan aktif untuk ' . PenilaianSesi::JENIS[$jenis] . '. Tambahkan dulu di Master Penilaian.');
        }

        if ($this->kamarSaya()->isEmpty() && ! Auth::user()->hasRole('Super Admin')) {
            return redirect()->route('penilaian.isi', $jenis)
                ->with('error', 'Kamar binaan Anda belum dipetakan, jadi belum ada santri yang bisa dinilai. Hubungi Kepala Diniyah untuk memetakan kamar Anda.');
        }

        $sesi = PenilaianSesi::firstOrCreate(
            ['jenis' => $jenis, 'periode_id' => $data['periode_id'], 'penilai_id' => Auth::id()],
            ['penilai_peran' => $this->peranBawaan(), 'status' => 'draft']
        );

        return redirect()->route('penilaian.sesi', $sesi->id)
            ->with('success', 'Lembar penilaian ' . PenilaianSesi::JENIS[$jenis] . ' periode ' . ($sesi->periode->nama ?? '-') . ' siap diisi.');
    }

    /**
     * Halaman lembar: HANYA daftar kamar binaan + progresnya.
     * Sengaja tidak menampilkan daftar siswa (dinilai per anak lewat halaman kamar).
     */
    public function sesi(Request $request, $id)
    {
        $sesi = PenilaianSesi::with(['periode', 'penilai'])->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);

        $hasil = $sesi->hasilPerSiswa();
        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);

        $jumlahKriteria = $kriteria->count();
        $jumlahKamar = $this->kamarSaya()->count();
        $tanpaKamar = $this->kamarSaya()->isEmpty() && ! Auth::user()->hasRole('Super Admin');

        $kamarList = $this->kamarSaya()
            ->map(function ($kamar) use ($hasil, $jumlahKriteria) {
                $anggotaIds = $this->anggotaKamarIds($kamar->id);

                $sudah = 0;
                $lengkap = 0;
                foreach ($anggotaIds as $sid) {
                    if (! isset($hasil[$sid])) {
                        continue;
                    }
                    $sudah++;
                    if ($jumlahKriteria > 0 && ($hasil[$sid]['jumlah'] ?? 0) >= $jumlahKriteria) {
                        $lengkap++;
                    }
                }

                return (object) [
                    'id' => $kamar->id,
                    'nama' => $kamar->nama_kamar,
                    'kategori' => $kamar->kategori,
                    'musyrif' => optional($kamar->musyrif)->name,
                    'total' => count($anggotaIds),
                    'sudah' => $sudah,
                    'lengkap' => $lengkap,
                ];
            })
            ->sortBy('nama')
            ->values();

        $terisi = $kamarList->sum('sudah');
        $totalSiswa = $kamarList->sum('total');
        $kamarSelesai = $kamarList->filter(fn ($k) => $k->total > 0 && $k->lengkap >= $k->total)->count();

        return view('penilaian.isi.sesi', [
            'sesi' => $sesi,
            'kriteria' => $kriteria,
            'kamarList' => $kamarList,
            'terisi' => $terisi,
            'totalSiswa' => $totalSiswa,
            'kamarSelesai' => $kamarSelesai,
            'jumlahKamar' => $jumlahKamar,
            'tanpaKamar' => $tanpaKamar,
            'jumlahKriteria' => $jumlahKriteria,
        ]);
    }

    /**
     * Daftar penghuni satu kamar (satu per satu anak dinilai dari sini).
     * Tidak ada form serentak — hanya tautan menuju formulir tiap anak.
     */
    public function kamar($id, $kamarId)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);
        abort_if($sesi->status === 'final', 403, 'Penilaian sudah difinalkan.');

        $kamar = AsramaKamar::with('musyrif')->findOrFail($kamarId);
        abort_unless($this->bolehKamar($kamar), 403, 'Kamar ini bukan kamar binaan Anda.');

        $anggotaIds = $this->anggotaKamarIds($kamar->id);
        $anggota = Siswa::whereIn('id', $anggotaIds ?: [0])
            ->where('status', 'Aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $hasil = $sesi->hasilPerSiswa();
        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);

        $sudah = $anggota->filter(fn ($a) => isset($hasil[$a->id]))->count();

        return view('penilaian.isi.kamar', [
            'sesi' => $sesi,
            'kamar' => $kamar,
            'anggota' => $anggota,
            'hasil' => $hasil,
            'kriteria' => $kriteria,
            'sudah' => $sudah,
        ]);
    }

    /** Form penilaian SATU siswa. */
    public function form($id, $siswaId, Request $request)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $this->izinkanAkses($sesi, hanyaPenilai: true);
        abort_if($sesi->status === 'final', 403, 'Penilaian ini sudah difinalkan.');

        $siswa = Siswa::findOrFail($siswaId);
        $this->pastikanSiswaBinaan($siswa->id);

        $kamar = $this->kamarSiswa($siswa->id);
        $kamarId = $this->kamarIdSiswa($siswa->id);
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
            'berikutnya' => $this->siswaBerikutnya($sesi, $siswa->id, $kamar),
            'kamar' => $kamar,
            'kamarId' => $kamarId,
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

        $this->pastikanSiswaBinaan((int) $siswaId);

        $kriteria = PenilaianKriteria::daftarAktif($sesi->jenis);
        if ($kriteria->isEmpty()) {
            return redirect()->route('penilaian.master')->with('error', 'Belum ada pertanyaan aktif.');
        }

        $aturan = [];
        foreach ($kriteria as $k) {
            $aturan['skor.' . $k->id] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $request->validate($aturan, [], $this->namaAtribut($kriteria));

        $siswa = Siswa::findOrFail($siswaId);

        DB::transaction(function () use ($sesi, $kriteria, $request, $siswa) {
            foreach ($kriteria as $k) {
                PenilaianJawaban::updateOrCreate(
                    ['sesi_id' => $sesi->id, 'siswa_id' => $siswa->id, 'kriteria_id' => $k->id],
                    ['skor' => (int) $request->input('skor.' . $k->id)]
                );
            }
        });

        $kamar = $this->kamarSiswa($siswa->id);

        if ($request->boolean('lanjut') && $request->filled('berikutnya')) {
            return redirect()->route('penilaian.sesi.form', [$sesi->id, $request->input('berikutnya')])
                ->with('success', 'Nilai ' . $siswa->nama_lengkap . ' tersimpan. Lanjut ke anak berikutnya di kamar yang sama.');
        }

        return redirect()->route('penilaian.sesi', $sesi->id)
            ->with('success', 'Nilai ' . $siswa->nama_lengkap . ($kamar ? ' (kamar ' . $kamar . ')' : '') . ' tersimpan.');
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

    /**
     * Hapus satu lembar penilaian beserta jawabannya.
     * Boleh: pemilik lembar sendiri atau Super Admin (jalan perbaikan/uji coba).
     * Lembar yang sudah FINAL hanya bisa dihapus Super Admin.
     */
    public function hapusSesi($id)
    {
        $sesi = PenilaianSesi::with('periode')->findOrFail($id);
        $user = Auth::user();

        $milikSaya = (int) $sesi->penilai_id === (int) $user->id;

        abort_unless($milikSaya || $user->hasRole('Super Admin'), 403, 'Anda tidak berhak menghapus lembar penilaian ini.');

        if ($sesi->status === 'final' && ! $user->hasRole('Super Admin')) {
            return redirect()->route('penilaian.sesi', $sesi->id)
                ->with('error', 'Lembar ini sudah final. Minta Super Admin membuka kembali atau menghapusnya.');
        }

        $jumlah = PenilaianJawaban::where('sesi_id', $sesi->id)->count();
        $jenis = $sesi->label_jenis;
        $periode = $sesi->periode->nama ?? '-';
        $jenisKunci = $sesi->jenis;

        DB::transaction(function () use ($sesi) {
            PenilaianJawaban::where('sesi_id', $sesi->id)->delete();
            $sesi->delete();
        });

        return redirect()->route('penilaian.isi', $jenisKunci)
            ->with('success', '🗑️ Lembar penilaian ' . $jenis . ' periode ' . $periode . ' dihapus (' . $jumlah . ' jawaban ikut terhapus).');
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

        abort_unless($user->can('nilai-' . $sesi->jenis), 403, 'Hanya musyrif/musyrifah yang bisa mengisi penilaian ini.');

        if ($hanyaPenilai && $sesi->penilai_id !== $user->id) {
            abort(403, 'Lembar penilaian ini milik penilai lain.');
        }
    }

    /** Kamar yang boleh diakses: musyrif = kamar binaannya; Super Admin = semua kamar. */
    private function kamarSaya()
    {
        $query = AsramaKamar::with('musyrif')->where('status', 'aktif');

        if (! Auth::user()->hasRole('Super Admin')) {
            $query->where('musyrif_id', Auth::id());
        }

        return $query->get();
    }

    private function bolehKamar(AsramaKamar $kamar): bool
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return true;
        }

        return (int) $kamar->musyrif_id === (int) Auth::id();
    }

    /** Daftar id siswa (penghuni aktif) sebuah kamar. */
    private function anggotaKamarIds($kamarId): array
    {
        return DB::table('asrama_members')
            ->where('kamar_id', $kamarId)
            ->whereNull('tanggal_keluar')
            ->pluck('student_id')
            ->all();
    }

    /** Kamar (nama) tempat siswa bernaung saat ini. */
    private function kamarSiswa($siswaId): ?string
    {
        $nama = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('m.student_id', $siswaId)
            ->whereNull('m.tanggal_keluar')
            ->value('k.nama_kamar');

        return $nama ?: null;
    }

    /** Id kamar tempat siswa bernaung saat ini. */
    private function kamarIdSiswa($siswaId): ?int
    {
        $id = DB::table('asrama_members')
            ->where('student_id', $siswaId)
            ->whereNull('tanggal_keluar')
            ->value('kamar_id');

        return $id ? (int) $id : null;
    }

    /** Pastikan siswa memang penghuni kamar binaan penilai (Super Admin bebas). */
    private function pastikanSiswaBinaan(int $siswaId): void
    {
        if (Auth::user()->hasRole('Super Admin')) {
            return;
        }

        $kamarIds = $this->kamarSaya()->pluck('id')->all();

        $milikSaya = DB::table('asrama_members')
            ->where('student_id', $siswaId)
            ->whereNull('tanggal_keluar')
            ->whereIn('kamar_id', $kamarIds ?: [0])
            ->exists();

        abort_unless($milikSaya, 403, 'Santri ini bukan penghuni kamar binaan Anda.');
    }

    private function peranBawaan(): string
    {
        return 'musyrif';
    }

    private function namaAtribut($kriteria): array
    {
        $nama = [];
        foreach ($kriteria as $k) {
            $nama['skor.' . $k->id] = '"' . $k->pertanyaan . '"';
        }

        return $nama;
    }

    /** Anak berikutnya yang belum dinilai DI KAMAR YANG SAMA (untuk tombol "simpan & lanjut"). */
    private function siswaBerikutnya(PenilaianSesi $sesi, int $siswaIdSetelah, ?string $namaKamar): ?int
    {
        if (! $namaKamar) {
            return null;
        }

        $kamarId = AsramaKamar::where('nama_kamar', $namaKamar)->value('id');

        if (! $kamarId) {
            return null;
        }

        $sudah = array_keys($sesi->hasilPerSiswa());
        $anggotaIds = $this->anggotaKamarIds($kamarId);

        return Siswa::whereIn('id', $anggotaIds ?: [0])
            ->where('status', 'Aktif')
            ->whereNotIn('id', $sudah ?: [0])
            ->orderBy('nama_lengkap')
            ->value('id');
    }
}
