<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Services\RekamJejak;
use App\Support\SandiOrtu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * PORTAL ORANG TUA — /ortu (mulai 30 Sep 2026)
 * =====================================================================
 * Keputusan pemilik sekolah:
 *   - kata sandi = tanggal lahir anak, format ddmmyyyy (24 Mei 2009 -> 24052009);
 *   - orang tua boleh melihat: poin karakter (termasuk yang negatif),
 *     kebersihan kamar, absensi jam tidur, project & nilai, rapor, catatan
 *     musyrif/mentor;
 *   - orang tua TIDAK melihat: data anak lain, peringkat kelas, dan nama
 *     penilai (cukup "Musyrif"/"Mentor" tanpa nama).
 *
 * Satu NISN = satu anak. Halaman ini hanya-baca; tidak ada satu pun aksi ubah.
 * Pembatas percobaan masuk: 5 kali per 10 menit per IP+NISN.
 */
class PortalOrtuController extends Controller
{
    private const MAKS_PERCOBAAN = 5;
    private const JENDELA_MENIT = 10;

    /** Halaman masuk. */
    public function masuk(Request $request)
    {
        if ($request->session()->has('ortu_siswa_id')) {
            return redirect()->route('ortu.dasbor');
        }

        return view('ortu.masuk');
    }

    /** Proses masuk: NISN + tanggal lahir anak. */
    public function proses(Request $request)
    {
        $data = $request->validate([
            'nisn'  => 'required|string|max:20',
            'sandi' => 'required|string|max:20',
        ], [], ['nisn' => 'NISN', 'sandi' => 'kata sandi']);

        $nisn = preg_replace('/\D+/', '', (string) $data['nisn']);
        $sandi = preg_replace('/\D+/', '', (string) $data['sandi']);

        $kunci = 'ortu-masuk:'.$request->ip().':'.$nisn;

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            return back()->withInput()->withErrors([
                'sandi' => 'Terlalu banyak percobaan. Coba lagi dalam '.ceil(RateLimiter::availableIn($kunci) / 60).' menit, atau hubungi Tata Usaha.',
            ]);
        }

        $siswa = Siswa::where('nisn', $nisn)
            ->whereNull('deleted_at')
            ->where('status', 'Aktif')
            ->first();

        // Pesan sengaja sama untuk NISN salah maupun sandi salah, supaya tidak
        // bisa dipakai menebak NISN siswa lain.
        $pesanGagal = 'NISN atau kata sandi tidak cocok. Kata sandi adalah tanggal lahir ananda dengan format ddmmyyyy (contoh: 24052009).';

        if (! $siswa) {
            RateLimiter::hit($kunci, self::JENDELA_MENIT * 60);

            return back()->withInput()->withErrors(['sandi' => $pesanGagal]);
        }

        $sandiBenar = SandiOrtu::dariTtl($siswa->ttl ?? null);

        if ($sandiBenar === null) {
            return back()->withInput()->withErrors([
                'sandi' => 'Data tanggal lahir ananda belum lengkap di sistem, sehingga portal ini belum bisa dibuka. Mohon menghubungi Tata Usaha sekolah.',
            ]);
        }

        if (! hash_equals($sandiBenar, $sandi)) {
            RateLimiter::hit($kunci, self::JENDELA_MENIT * 60);

            return back()->withInput()->withErrors(['sandi' => $pesanGagal]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();
        $request->session()->put('ortu_siswa_id', $siswa->id);
        $request->session()->put('ortu_masuk_pada', now()->toDateTimeString());

        return redirect()->route('ortu.dasbor');
    }

    /** Dasbor portofolio anak. */
    public function dasbor(Request $request, RekamJejak $layanan)
    {
        $siswa = $this->siswaPortal($request);

        if (! $siswa) {
            return redirect()->route('ortu.masuk');
        }

        return view('ortu.dasbor', [
            'rj' => $layanan->susun($siswa->id),
        ]);
    }

    /** Rapor versi orang tua (siap cetak: poin karakter, project, adab & keasramaan). */
    public function rapor(Request $request, RekamJejak $layanan)
    {
        $siswa = $this->siswaPortal($request);

        if (! $siswa) {
            return redirect()->route('ortu.masuk');
        }

        return view('ortu.rapor', [
            'rj' => $layanan->susun($siswa->id),
        ]);
    }

    /** Keluar. */
    public function keluar(Request $request)
    {
        $request->session()->forget(['ortu_siswa_id', 'ortu_masuk_pada']);

        return redirect()->route('ortu.masuk');
    }

    /** Siswa yang sedang masuk lewat portal (dari sesi, bukan dari URL). */
    private function siswaPortal(Request $request): ?Siswa
    {
        $id = (int) $request->session()->get('ortu_siswa_id', 0);

        if ($id < 1) {
            return null;
        }

        return Siswa::where('id', $id)->whereNull('deleted_at')->first();
    }
}
