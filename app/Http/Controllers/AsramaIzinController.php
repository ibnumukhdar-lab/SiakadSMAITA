<?php

namespace App\Http\Controllers;

use App\Models\AsramaIzin;
use App\Models\AsramaKamar;
use App\Models\AsramaMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * IZIN PULANG / KELUAR SANTRI (Tahap B, 17 Sep 2026)
 * Musyrif mengajukan izin santri kamar binaannya; Kepala Diniyah menyetujui/menolak.
 * Izin yang disetujui otomatis menjadi status bawaan saat mengisi absensi.
 */
class AsramaIzinController extends Controller
{
    private function bolehSemuaKamar(): bool
    {
        $u = Auth::user();

        return (bool) ($u && $u->can('buka-menu-manajemen-kamar'));
    }

    /** Batasi kueri izin: musyrif hanya kamar binaannya / yang dia ajukan. */
    private function scopeMilikSaya($query)
    {
        if ($this->bolehSemuaKamar()) {
            return $query;
        }

        $kamarIds = AsramaKamar::where('musyrif_id', Auth::id())->pluck('id')->all() ?: [0];

        return $query->where(function ($w) use ($kamarIds) {
            $w->whereIn('kamar_id', $kamarIds)->orWhere('diajukan_oleh', Auth::id());
        });
    }

    /** Daftar penghuni yang boleh diizinkan oleh user ini. */
    private function daftarPenghuni()
    {
        $q = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->whereNull('m.tanggal_keluar')
            ->whereNull('s.deleted_at')
            ->where('s.status', 'Aktif');

        if (! $this->bolehSemuaKamar()) {
            $q->where('k.musyrif_id', Auth::id());
        }

        return $q->orderBy('k.kategori')->orderBy('s.nama_lengkap')
            ->selectRaw('s.id AS student_id, s.nama_lengkap, s.kelas, k.id AS kamar_id, k.nama_kamar, k.kategori')
            ->get();
    }

    // =========================================================
    // 1. Daftar izin
    // =========================================================
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->input('status'), AsramaIzin::STATUS) ? (string) $request->input('status') : '';
        $q = trim((string) $request->input('q'));

        $query = $this->scopeMilikSaya(AsramaIzin::with(['student', 'kamar', 'pengaju', 'penyetuju']));

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->whereHas('student', fn ($s) => $s->where('nama_lengkap', 'like', "%{$q}%"));
        }

        $daftar = $query->orderByDesc('mulai')->orderByDesc('id')->paginate(15)->withQueryString();

        $hariIni = now()->toDateString();

        $diLuar = $this->scopeMilikSaya(AsramaIzin::with(['student', 'kamar']))
            ->where('status', 'disetujui')
            ->where('mulai', '<=', $hariIni)
            ->where('sampai', '>=', $hariIni)
            ->get();

        $terlambat = $this->scopeMilikSaya(AsramaIzin::with(['student', 'kamar']))
            ->where('status', 'disetujui')
            ->where('sampai', '<', $hariIni)
            ->orderBy('sampai')
            ->get();

        $ringkasan = [
            'diajukan'  => (clone $this->scopeMilikSaya(AsramaIzin::query()))->where('status', 'diajukan')->count(),
            'disetujui' => $diLuar->count(),
            'terlambat' => $terlambat->count(),
        ];

        return view('asrama.izin.index', compact('daftar', 'status', 'q', 'diLuar', 'terlambat', 'ringkasan'));
    }

    // =========================================================
    // 2. Form pengajuan izin
    // =========================================================
    public function create(Request $request)
    {
        $penghuni = $this->daftarPenghuni();
        $pilihSiswa = (int) $request->input('student_id');

        return view('asrama.izin.create', compact('penghuni', 'pilihSiswa'));
    }

    // =========================================================
    // 3. Simpan pengajuan
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'student_id'        => 'required|exists:siswas,id',
            'jenis'             => 'required|in:' . implode(',', array_keys(AsramaIzin::JENIS)),
            'mulai'             => 'required|date',
            'sampai'            => 'required|date|after_or_equal:mulai',
            'alasan'            => 'required|string|max:1000',
            'tujuan'            => 'nullable|string|max:150',
            'penanggung_jawab'  => 'nullable|string|max:150',
        ], [
            'student_id.required' => 'Pilih dulu siswa yang mengajukan izin.',
            'sampai.after_or_equal' => 'Tanggal kembali tidak boleh lebih awal dari tanggal mulai.',
            'alasan.required'     => 'Alasan izin wajib diisi.',
        ]);

        // Musyrif hanya boleh mengajukan untuk penghuni kamar binaannya
        $anggota = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('m.student_id', $request->student_id)
            ->whereNull('m.tanggal_keluar')
            ->selectRaw('m.kamar_id, k.musyrif_id')
            ->first();

        if (! $anggota) {
            return back()->withInput()->with('error', 'Siswa itu tidak tercatat sebagai penghuni kamar asrama.');
        }

        if (! $this->bolehSemuaKamar() && (int) $anggota->musyrif_id !== (int) Auth::id()) {
            return back()->withInput()->with('error', 'Siswa itu bukan penghuni kamar binaan Anda.');
        }

        // Tidak boleh menumpuk izin aktif yang sama
        $bentrok = AsramaIzin::where('student_id', $request->student_id)
            ->whereIn('status', ['diajukan', 'disetujui'])
            ->where('mulai', '<=', $request->sampai)
            ->where('sampai', '>=', $request->mulai)
            ->first();

        if ($bentrok) {
            return back()->withInput()->with('error', 'Sudah ada izin ' . $bentrok->labelJenis() . ' pada rentang tanggal itu (status: ' . $bentrok->labelStatus() . ').');
        }

        AsramaIzin::create([
            'student_id'        => $request->student_id,
            'kamar_id'          => $anggota->kamar_id,
            'jenis'             => $request->jenis,
            'mulai'             => $request->mulai,
            'sampai'            => $request->sampai,
            'alasan'            => $request->alasan,
            'tujuan'            => $request->tujuan,
            'penanggung_jawab'  => $request->penanggung_jawab,
            'status'            => 'diajukan',
            'diajukan_oleh'     => Auth::id(),
        ]);

        return redirect()->route('asrama.izin.index')->with('success', '📨 Pengajuan izin tersimpan dan menunggu persetujuan Kepala Diniyah.');
    }

    private function ambilIzin($id): AsramaIzin
    {
        $izin = $this->scopeMilikSaya(AsramaIzin::with('student'))->where('id', $id)->first();

        abort_if(! $izin, 404, 'Data izin tidak ditemukan.');

        return $izin;
    }

    // =========================================================
    // 4. Setujui (Kepala Diniyah)
    // =========================================================
    public function setujui($id)
    {
        $izin = $this->ambilIzin($id);

        if (! $this->bolehSemuaKamar()) {
            return back()->with('error', 'Hanya Kepala Diniyah / pengelola kamar yang bisa menyetujui izin.');
        }

        if ($izin->status !== 'diajukan') {
            return back()->with('error', 'Izin ini sudah diproses sebelumnya (' . $izin->labelStatus() . ').');
        }

        $izin->update(['status' => 'disetujui', 'disetujui_oleh' => Auth::id()]);

        return back()->with('success', '✅ Izin ' . optional($izin->student)->nama_lengkap . ' disetujui. Status izin otomatis muncul saat absensi diisi.');
    }

    // =========================================================
    // 5. Tolak (Kepala Diniyah)
    // =========================================================
    public function tolak(Request $request, $id)
    {
        $izin = $this->ambilIzin($id);

        if (! $this->bolehSemuaKamar()) {
            return back()->with('error', 'Hanya Kepala Diniyah / pengelola kamar yang bisa menolak izin.');
        }

        $request->validate(['catatan_penolakan' => 'required|string|max:500'], [
            'catatan_penolakan.required' => 'Tulis alasan penolakan supaya musyrif tahu sebabnya.',
        ]);

        $izin->update([
            'status'             => 'ditolak',
            'disetujui_oleh'     => Auth::id(),
            'catatan_penolakan'  => $request->catatan_penolakan,
        ]);

        return back()->with('success', 'Izin ditolak dan alasannya sudah dicatat.');
    }

    // =========================================================
    // 6. Catat kembali ke asrama
    // =========================================================
    public function kembali(Request $request, $id)
    {
        $izin = $this->ambilIzin($id);

        if ($izin->status !== 'disetujui') {
            return back()->with('error', 'Hanya izin yang sudah disetujui yang bisa dicatat kembali.');
        }

        $request->validate([
            'kembali_pada'    => 'required|date',
            'catatan_kembali' => 'nullable|string|max:500',
        ]);

        $izin->update([
            'status'          => 'selesai',
            'kembali_pada'    => $request->kembali_pada,
            'catatan_kembali' => $request->catatan_kembali,
        ]);

        return back()->with('success', '🏠 Kepulangan ' . optional($izin->student)->nama_lengkap . ' sudah dicatat.');
    }

    // =========================================================
    // 7. Batalkan pengajuan (pengaju / pengelola)
    // =========================================================
    public function destroy($id)
    {
        $izin = $this->ambilIzin($id);

        if ($izin->status === 'disetujui' || $izin->status === 'selesai') {
            return back()->with('error', 'Izin yang sudah berjalan tidak bisa dihapus. Catat saja kepulangannya.');
        }

        $izin->delete();

        return back()->with('success', 'Pengajuan izin dibatalkan.');
    }

    // =========================================================
    // 8. Cetak surat izin (bukti untuk gerbang / wali)
    // =========================================================
    public function cetak($id)
    {
        $izin = $this->ambilIzin($id);

        return view('asrama.izin.cetak', [
            'izin'       => $izin,
            'pengaturan' => \App\Models\Pengaturan::first(),
        ]);
    }
}
