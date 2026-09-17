<?php

namespace App\Http\Controllers;

use App\Models\AsramaAbsensi;
use App\Models\AsramaIzin;
use App\Models\AsramaKamar;
use App\Models\AsramaMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ABSENSI ASRAMA (Tahap B, 17 Sep 2026)
 * Absensi diisi SATU kali sehari (malam / jam tidur) per kamar binaan musyrif.
 * Kepala Diniyah (pemegang buka-menu-manajemen-kamar) bisa semua kamar.
 */
class AsramaAbsensiController extends Controller
{
    private function bolehSemuaKamar(): bool
    {
        $u = Auth::user();

        return (bool) ($u && $u->can('buka-menu-manajemen-kamar'));
    }

    private function daftarKamar()
    {
        $q = AsramaKamar::query();

        if (! $this->bolehSemuaKamar()) {
            $q->where('musyrif_id', Auth::id());
        }

        return $q;
    }

    private function pastikanBoleh(AsramaKamar $kamar): void
    {
        if ($this->bolehSemuaKamar()) {
            return;
        }

        if ((int) $kamar->musyrif_id !== (int) Auth::id()) {
            abort(403, 'Kamar ini bukan kamar binaan Anda.');
        }
    }

    private function tanggalValid(Request $request): string
    {
        $t = (string) $request->input('tanggal');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)) {
            return now()->toDateString();
        }

        // Tidak boleh mengisi absensi untuk tanggal yang belum terjadi
        return $t > now()->toDateString() ? now()->toDateString() : $t;
    }

    /**
     * Hanya SATU sesi absensi sehari: malam / jam tidur (permintaan Fahri, 17 Sep 2026).
     * Nilai sesi dipatok di server supaya halaman lama yang masih mengirim ?sesi=… tetap aman.
     */
    private function sesiValid(Request $request): string
    {
        return AsramaAbsensi::SESI_UTAMA;
    }

    // =========================================================
    // 1. Pilih tanggal, lihat status pengisian absensi malam tiap kamar
    // =========================================================
    public function index(Request $request)
    {
        $tanggal  = $this->tanggalValid($request);
        $sesi     = $this->sesiValid($request);
        $kategori = in_array($request->input('kategori'), ['putra', 'putri'], true) ? $request->input('kategori') : null;

        $kamarQuery = $this->daftarKamar()
            ->with('musyrif')
            ->withCount(['members as penghuni_count' => fn ($q) => $q->whereNull('tanggal_keluar')]);

        if ($kategori) {
            $kamarQuery->where('kategori', $kategori);
        }

        $kamar = $kamarQuery->orderBy('kategori')->orderBy('nama_kamar')->get();

        $tersimpan = DB::table('asrama_absensi')
            ->where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->whereIn('kamar_id', $kamar->pluck('id')->all() ?: [0])
            ->selectRaw("kamar_id, COUNT(*) AS jml, SUM(status <> 'hadir') AS nonhadir")
            ->groupBy('kamar_id')
            ->get()
            ->keyBy('kamar_id');

        $bermasalah = DB::table('asrama_absensi')
            ->where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->whereIn('kamar_id', $kamar->pluck('id')->all() ?: [0])
            ->whereIn('status', ['alpa', 'telat', 'sakit', 'izin', 'pulang'])
            ->selectRaw('kamar_id, status, COUNT(*) AS jml')
            ->groupBy('kamar_id', 'status')
            ->get()
            ->groupBy('kamar_id');

        $jumlahKamar = $kamar->count();
        $kamarSelesai = $kamar->filter(function ($k) use ($tersimpan) {
            return isset($tersimpan[$k->id]) && (int) $k->penghuni_count > 0 && (int) $tersimpan[$k->id]->jml >= (int) $k->penghuni_count;
        })->count();

        return view('asrama.absensi.index', compact(
            'tanggal', 'sesi', 'kategori', 'kamar', 'tersimpan', 'bermasalah', 'jumlahKamar', 'kamarSelesai'
        ));
    }

    // =========================================================
    // 2. Form isi absensi satu kamar (isi cepat: bawaan Hadir)
    // =========================================================
    public function form(Request $request, $kamar_id)
    {
        $kamar = AsramaKamar::with('musyrif')->findOrFail($kamar_id);
        $this->pastikanBoleh($kamar);

        $tanggal = $this->tanggalValid($request);
        $sesi    = $this->sesiValid($request);

        $anggota = AsramaMember::with('student')
            ->where('kamar_id', $kamar->id)
            ->whereNull('tanggal_keluar')
            ->get()
            ->sortBy(fn ($a) => optional($a->student)->nama_lengkap)
            ->values();

        $tersimpan = AsramaAbsensi::where('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->where('kamar_id', $kamar->id)
            ->get()
            ->keyBy('student_id');

        $izin = AsramaIzin::whereIn('student_id', $anggota->pluck('student_id')->all() ?: [0])
            ->where('status', 'disetujui')
            ->where('mulai', '<=', $tanggal)
            ->where('sampai', '>=', $tanggal)
            ->get()
            ->keyBy('student_id');

        $statusAwal = [];
        $keteranganAwal = [];
        foreach ($anggota as $a) {
            $sid = $a->student_id;
            if (isset($tersimpan[$sid])) {
                $statusAwal[$sid]     = $tersimpan[$sid]->status;
                $keteranganAwal[$sid] = $tersimpan[$sid]->keterangan;
            } elseif (isset($izin[$sid])) {
                $statusAwal[$sid]     = $izin[$sid]->statusAbsensi();
                $keteranganAwal[$sid] = 'Izin ' . $izin[$sid]->labelJenis() . ' s/d ' . Carbon::parse($izin[$sid]->sampai)->translatedFormat('d M Y');
            } else {
                $statusAwal[$sid] = 'hadir';
            }
        }

        $sudahAda = $tersimpan->isNotEmpty();

        return view('asrama.absensi.form', compact(
            'kamar', 'tanggal', 'sesi', 'anggota', 'statusAwal', 'keteranganAwal', 'izin', 'sudahAda'
        ));
    }

    // =========================================================
    // 3. Simpan absensi satu kamar
    // =========================================================
    public function simpan(Request $request)
    {
        $request->validate([
            'kamar_id'     => 'required|exists:asrama_kamars,id',
            'tanggal'      => 'required|date',
            // Nilai sesi DIPATOK server (SESI_UTAMA = tidur); halaman/browser lama
            // yang masih mengirim ?sesi=isya tidak boleh ditolak validasi.
            'sesi'         => 'nullable|string|max:20',
            'status'       => 'required|array',
            'status.*'     => 'required|in:' . implode(',', array_keys(AsramaAbsensi::STATUS)),
            'keterangan'   => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:200',
        ], [
            'status.required' => 'Status kehadiran wajib diisi untuk setiap siswa.',
            'status.*.in'     => 'Ada status kehadiran yang tidak dikenal sistem.',
        ]);

        $kamar = AsramaKamar::findOrFail($request->kamar_id);
        $this->pastikanBoleh($kamar);

        $sesi    = AsramaAbsensi::SESI_UTAMA;   // satu-satunya sesi: malam / jam tidur
        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        if ($tanggal > now()->toDateString()) {
            return back()->with('error', 'Absensi tidak bisa diisi untuk tanggal yang belum terjadi.');
        }

        $anggotaIds = AsramaMember::where('kamar_id', $kamar->id)
            ->whereNull('tanggal_keluar')
            ->pluck('student_id')
            ->all();

        if (! $anggotaIds) {
            return back()->with('error', "Kamar {$kamar->nama_kamar} belum punya penghuni aktif.");
        }

        DB::beginTransaction();

        try {
            $jumlah = 0;
            foreach ($anggotaIds as $sid) {
                $status = $request->input('status.' . $sid);
                if (! $status || ! array_key_exists($status, AsramaAbsensi::STATUS)) {
                    continue;
                }

                $ket = $request->input('keterangan.' . $sid);
                $ket = is_string($ket) ? trim($ket) : null;

                AsramaAbsensi::updateOrCreate(
                    ['tanggal' => $tanggal, 'sesi' => $sesi, 'student_id' => $sid],
                    [
                        'kamar_id'     => $kamar->id,
                        'status'       => $status,
                        'keterangan'   => $ket !== '' ? $ket : null,
                        'dicatat_oleh' => Auth::id(),
                    ]
                );
                $jumlah++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }

        return redirect()
            ->route('asrama.absensi.index', ['tanggal' => $tanggal, 'kategori' => $kamar->kategori])
            ->with('success', '✅ Absensi malam ' . $kamar->nama_kamar . ' tersimpan untuk ' . $jumlah . ' siswa.');
    }

    // =========================================================
    // 4. Rekap bulanan per kamar + siswa bermasalah
    // =========================================================
    public function rekap(Request $request)
    {
        $bulan = (string) $request->input('bulan');
        if (! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $kategori = in_array($request->input('kategori'), ['putra', 'putri'], true) ? $request->input('kategori') : null;

        $awal  = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->toDateString();
        $akhir = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->toDateString();

        $kamarIds = $this->daftarKamar()->pluck('id')->all() ?: [0];

        $baris = DB::table('asrama_absensi as a')
            ->join('asrama_kamars as k', 'k.id', '=', 'a.kamar_id')
            ->whereBetween('a.tanggal', [$awal, $akhir])
            ->whereIn('a.kamar_id', $kamarIds)
            ->when($kategori, fn ($q) => $q->where('k.kategori', $kategori))
            ->selectRaw("a.kamar_id, k.nama_kamar, k.kategori, k.kapasitas,
                COUNT(*) AS catatan,
                COUNT(DISTINCT a.tanggal) AS hari,
                SUM(a.status = 'hadir') AS hadir,
                SUM(a.status = 'telat') AS telat,
                SUM(a.status = 'izin') AS izin,
                SUM(a.status = 'sakit') AS sakit,
                SUM(a.status = 'pulang') AS pulang,
                SUM(a.status = 'alpa') AS alpa")
            ->groupBy('a.kamar_id', 'k.nama_kamar', 'k.kategori', 'k.kapasitas')
            ->orderBy('k.kategori')
            ->orderByRaw("SUM(a.status IN ('alpa','telat')) DESC")
            ->get();

        $siswaBermasalah = DB::table('asrama_absensi as a')
            ->join('siswas as s', 's.id', '=', 'a.student_id')
            ->leftJoin('asrama_kamars as k', 'k.id', '=', 'a.kamar_id')
            ->whereBetween('a.tanggal', [$awal, $akhir])
            ->whereIn('a.kamar_id', $kamarIds)
            ->when($kategori, fn ($q) => $q->where('k.kategori', $kategori))
            ->whereIn('a.status', ['alpa', 'telat'])
            ->selectRaw("a.student_id, s.nama_lengkap, s.kelas, k.nama_kamar,
                SUM(a.status = 'alpa') AS alpa, SUM(a.status = 'telat') AS telat")
            ->groupBy('a.student_id', 's.nama_lengkap', 's.kelas', 'k.nama_kamar')
            ->orderByRaw("SUM(a.status = 'alpa') DESC")
            ->orderByRaw("SUM(a.status = 'telat') DESC")
            ->limit(20)
            ->get();

        $kamarList = $this->daftarKamar()->orderBy('kategori')->orderBy('nama_kamar')->get();

        return view('asrama.absensi.rekap', compact(
            'bulan', 'kategori', 'baris', 'siswaBermasalah', 'kamarList'
        ));
    }
}
