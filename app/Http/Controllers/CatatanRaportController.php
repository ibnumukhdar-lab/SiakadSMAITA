<?php

namespace App\Http\Controllers;

use App\Models\CatatanRaport;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Isian catatan rapor (18 Sep 2026) — bagian "Catatan Musyrif / Pembina" pada rapor
 * Adab & Keasramaan dan "Catatan Mentor" pada rapor Student Root.
 *
 * Hak mengisi:
 *  - adab         : Musyrif divisi santri itu (putra/putri dibaca dari kamar binaannya),
 *                   Kepala Diniyah, dan Super Admin.
 *  - student_root : mentor grup Student Root santri itu, dan Super Admin.
 *  - Kepala Sekolah / Tata Usaha / guru lain hanya bisa melihat (tidak bisa menyimpan).
 *
 * Isi dikosongkan → baris catatan dihapus (rapor kembali menampilkan garis tulis tangan).
 */
class CatatanRaportController extends Controller
{
    public function simpan(Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required|in:adab,student_root',
            'siswa_id' => 'required|integer',
            'isi' => 'nullable|string|max:' . CatatanRaport::MAKS,
            'penilaian_periode_id' => 'nullable|integer',
            'tahun_ajaran' => 'nullable|string|max:12',
            'semester' => 'nullable|in:s1,s2',
        ]);

        $siswa = Siswa::findOrFail((int) $data['siswa_id']);
        $jenis = (string) $data['jenis'];

        $this->pastikanBolehTulis($jenis, $siswa);

        $kunci = $jenis === 'adab'
            ? ['penilaian_periode_id' => $data['penilaian_periode_id'] ?? null]
            : ['tahun_ajaran' => $data['tahun_ajaran'] ?? null, 'semester' => $data['semester'] ?? null];

        abort_if($jenis === 'adab' && empty($kunci['penilaian_periode_id']), 422, 'Periode penilaian tidak dikenali.');

        $catatan = CatatanRaport::firstOrNew(array_merge(
            ['jenis' => $jenis, 'siswa_id' => $siswa->id],
            $kunci
        ));

        $isi = trim((string) ($data['isi'] ?? ''));

        if ($isi === '') {
            if ($catatan->exists) {
                $catatan->delete();
            }

            return back()->with('success', 'Catatan rapor dikosongkan — rapor kembali menyediakan ruang tulis tangan.');
        }

        $catatan->isi = $isi;
        $catatan->penulis_id = Auth::id();
        $catatan->save();

        return back()->with('success', 'Catatan rapor ' . $siswa->nama_lengkap . ' disimpan.');
    }

    public function hapus(Request $request, $id)
    {
        $catatan = CatatanRaport::findOrFail((int) $id);
        $siswa = Siswa::findOrFail($catatan->siswa_id);

        $this->pastikanBolehTulis($catatan->jenis, $siswa);

        $catatan->delete();

        return back()->with('success', 'Catatan rapor dihapus.');
    }

    // =====================================================================
    //  Hak akses
    // =====================================================================
    private function pastikanBolehTulis(string $jenis, Siswa $siswa): void
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            return;
        }

        if ($jenis === 'adab') {
            abort_unless(
                CatatanRaport::bolehTulisAdab($user, $siswa),
                403,
                'Hanya musyrif pada divisi santri ini (atau Kepala Diniyah) yang boleh mengisi catatan rapor.'
            );

            return;
        }

        abort_unless(
            CatatanRaport::bolehTulisSr($user, $siswa),
            403,
            'Hanya mentor grup Student Root santri ini yang boleh mengisi catatan.'
        );
    }
}
