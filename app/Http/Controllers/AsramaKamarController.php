<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\AsramaMember;
use App\Models\AsramaPenilaianKamar;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AsramaKamarController extends Controller
{
    public const JK_PUTRA = 'Laki-laki';
    public const JK_PUTRI = 'Perempuan';

    /** Jenis kelamin yang sah untuk sebuah kamar. */
    public static function jkUntuk($kategori): string
    {
        return strtolower((string) $kategori) === 'putri' ? self::JK_PUTRI : self::JK_PUTRA;
    }

    /** Penghuni aktif (belum keluar) sebuah kamar. */
    public static function penghuniAktif($kamarId): int
    {
        return AsramaMember::where('kamar_id', $kamarId)->whereNull('tanggal_keluar')->count();
    }

    /** Scope penghuni aktif untuk withCount(). */
    private static function scopePenghuniAktif($query)
    {
        return $query->whereNull('tanggal_keluar');
    }

    // ====================================================
    // 1. Halaman Kamar Binaan Saya (Khusus Musyrif)
    // ====================================================
    public function kamarBinaan()
    {
        $kamars = AsramaKamar::with('musyrif')
                    ->withCount(['members as penghuni_count' => fn ($q) => self::scopePenghuniAktif($q)])
                    ->where('musyrif_id', auth()->id())
                    ->orderBy('kategori', 'asc')
                    ->orderBy('nama_kamar', 'asc')
                    ->get();

        return view('asrama.kamar.binaan', compact('kamars'));
    }

    // ====================================================
    // 2. Halaman Daftar Kamar Keseluruhan (+ okupansi & saring)
    // ====================================================
    public function index(Request $request)
    {
        $kategori = in_array($request->input('kategori'), ['putra', 'putri'], true) ? $request->input('kategori') : null;
        $isi      = in_array($request->input('isi'), ['kosong', 'tersedia', 'penuh', 'over'], true) ? $request->input('isi') : null;
        $q        = trim((string) $request->input('q'));

        $query = AsramaKamar::with('musyrif')
                    ->withCount(['members as penghuni_count' => fn ($qq) => self::scopePenghuniAktif($qq)]);

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_kamar', 'like', "%{$q}%")
                  ->orWhereHas('musyrif', fn ($m) => $m->where('name', 'like', "%{$q}%"));
            });
        }

        $kamars = $query->orderBy('kategori', 'asc')->orderBy('nama_kamar', 'asc')->get();

        if ($isi) {
            $kamars = $kamars->filter(function ($k) use ($isi) {
                $p = (int) $k->penghuni_count;
                $kap = (int) $k->kapasitas;

                return match ($isi) {
                    'kosong'   => $p === 0,
                    'penuh'    => $kap > 0 && $p === $kap,
                    'over'     => $kap > 0 && $p > $kap,
                    'tersedia' => $kap > 0 && $p < $kap,
                    default    => true,
                };
            })->values();
        }

        // Ringkasan okupansi (selalu dari SELURUH kamar, bukan hasil saring)
        $semua = AsramaKamar::withCount(['members as penghuni_count' => fn ($qq) => self::scopePenghuniAktif($qq)])->get();
        $ringkasan = [
            'kamar'     => $semua->count(),
            'kapasitas' => (int) $semua->sum('kapasitas'),
            'penghuni'  => (int) $semua->sum('penghuni_count'),
            'over'      => $semua->filter(fn ($k) => (int) $k->penghuni_count > (int) $k->kapasitas)->count(),
            'kosong'    => $semua->filter(fn ($k) => (int) $k->penghuni_count === 0)->count(),
        ];
        $ringkasan['persen'] = $ringkasan['kapasitas'] > 0
            ? (int) round($ringkasan['penghuni'] / $ringkasan['kapasitas'] * 100)
            : 0;

        try {
            $teachers = User::role('Musyrif')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $teachers = collect();
        }

        return view('asrama.kamar.index', compact('kamars', 'teachers', 'kategori', 'isi', 'q', 'ringkasan'));
    }

    // ====================================================
    // 3. Simpan Kamar Baru
    // ====================================================
    public function store(Request $request)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'kategori'   => 'required|in:putra,putri',
            'musyrif_id' => 'required|exists:users,id',
            'kapasitas'  => 'required|integer|min:1|max:40',
        ]);

        try {
            AsramaKamar::create([
                'nama_kamar' => $request->nama_kamar,
                'kategori'   => $request->kategori,
                'musyrif_id' => $request->musyrif_id,
                'kapasitas'  => $request->kapasitas,
                'status'     => 'aktif',
            ]);

            return back()->with('success', 'Kamar asrama baru berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat menambah kamar: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 4. Update Info Kamar
    // ====================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'kategori'   => 'required|in:putra,putri',
            'musyrif_id' => 'required|exists:users,id',
            'kapasitas'  => 'required|integer|min:1|max:40',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        try {
            $kamar = AsramaKamar::findOrFail($id);

            // Pengaman: memindah kategori kamar yang masih berpenghuni akan
            // membuat penghuninya tidak sesuai gender.
            if ($kamar->kategori !== $request->kategori && self::penghuniAktif($kamar->id) > 0) {
                return back()->with('error', 'Kategori kamar tidak bisa diubah karena masih ada ' . self::penghuniAktif($kamar->id) . ' penghuni. Keluarkan dulu penghuninya.');
            }

            $kamar->update($request->only(['nama_kamar', 'kategori', 'musyrif_id', 'kapasitas', 'status']));

            return back()->with('success', 'Informasi kamar asrama berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengupdate kamar: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 5. Hapus Kamar (dengan pengaman riwayat)
    // ====================================================
    public function destroy($id)
    {
        try {
            $kamar = AsramaKamar::findOrFail($id);

            $penghuni = self::penghuniAktif($kamar->id);
            if ($penghuni > 0) {
                return back()->with('error', "Kamar {$kamar->nama_kamar} masih berpenghuni ({$penghuni} orang). Keluarkan atau pindahkan penghuninya dulu.");
            }

            $riwayatPenghuni = AsramaMember::where('kamar_id', $kamar->id)->count();
            $riwayatSidak = AsramaPenilaianKamar::where('kamar_id', $kamar->id)->count();

            if ($riwayatPenghuni > 0 || $riwayatSidak > 0) {
                return back()->with('error', "Kamar {$kamar->nama_kamar} tidak boleh dihapus karena punya riwayat ({$riwayatPenghuni} baris penghuni, {$riwayatSidak} baris nilai inspeksi). Nonaktifkan saja agar laporan lama tetap utuh.");
            }

            $kamar->delete();

            return back()->with('success', 'Kamar asrama berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus kamar: ' . $e->getMessage());
        }
    }

    // ====================================================
    // BAGIAN KELOLA ANGGOTA KAMAR (PENGHUNI)
    // ====================================================

    public function show($id)
    {
        try {
            $kamar = AsramaKamar::with('musyrif')->findOrFail($id);

            $members = AsramaMember::with('student')
                        ->where('kamar_id', $id)
                        ->whereNull('tanggal_keluar')
                        ->orderBy('tanggal_masuk')
                        ->get();

            $riwayat = AsramaMember::with('student')
                        ->where('kamar_id', $id)
                        ->whereNotNull('tanggal_keluar')
                        ->orderByDesc('tanggal_keluar')
                        ->limit(25)
                        ->get();

            $jkSah = self::jkUntuk($kamar->kategori);

            // Siswa yang sudah punya kamar lain (untuk peringatan di daftar pilihan)
            $kamarSiswa = AsramaMember::whereNull('tanggal_keluar')->pluck('kamar_id', 'student_id');
            $namaKamar = AsramaKamar::pluck('nama_kamar', 'id');

            // Hanya siswa dengan gender yang sesuai yang boleh dipilih
            $students = Siswa::where('status', 'Aktif')
                        ->whereNull('deleted_at')
                        ->where('jk', $jkSah)
                        ->orderBy('nama_lengkap', 'asc')
                        ->get();

            $jumlahSiswaTanpaGender = Siswa::where('status', 'Aktif')
                        ->whereNull('deleted_at')
                        ->where(fn ($w) => $w->whereNull('jk')->orWhereNotIn('jk', [self::JK_PUTRA, self::JK_PUTRI]))
                        ->count();

            return view('asrama.kamar.show', compact(
                'kamar', 'members', 'riwayat', 'students', 'kamarSiswa', 'namaKamar', 'jkSah', 'jumlahSiswaTanpaGender'
            ));
        } catch (\Throwable $e) {
            return redirect()->route('asrama.kamar.index')->with('error', 'Data kamar tidak ditemukan!');
        }
    }

    // ====================================================
    // Tambah penghuni + penjagaan gender, kamar ganda, kapasitas
    // ====================================================
    public function addMember(Request $request, $id)
    {
        $request->validate([
            'student_id'   => 'required|array',
            'student_id.*' => 'exists:siswas,id',
            'catatan'      => 'nullable|string|max:255',
        ]);

        $kamar = AsramaKamar::findOrFail($id);
        $jkSah = self::jkUntuk($kamar->kategori);
        $kapasitas = (int) $kamar->kapasitas;
        $penghuni = self::penghuniAktif($kamar->id);

        $pindahkan = $request->boolean('pindahkan');
        $paksaKapasitas = $request->boolean('paksa_kapasitas');

        $diterima = [];
        $ditolak = [];
        $dipindah = [];

        DB::beginTransaction();
        try {
            foreach (array_unique($request->student_id) as $student_id) {
                $siswa = Siswa::find($student_id);
                if (! $siswa) {
                    $ditolak[] = "ID {$student_id} tidak ditemukan";
                    continue;
                }

                $nama = $siswa->nama_lengkap ?: ('Siswa #' . $siswa->id);

                // 1) Penjaga gender
                if (! in_array($siswa->jk, [self::JK_PUTRA, self::JK_PUTRI], true)) {
                    $ditolak[] = "{$nama} — jenis kelamin belum diisi (perbaiki dulu di Data Siswa)";
                    continue;
                }
                if ($siswa->jk !== $jkSah) {
                    $ditolak[] = "{$nama} — {$siswa->jk}, tidak sesuai kamar " . ucfirst($kamar->kategori);
                    continue;
                }

                // 2) Sudah penghuni kamar ini?
                $sudahDisini = AsramaMember::where('kamar_id', $kamar->id)
                                ->where('student_id', $siswa->id)
                                ->whereNull('tanggal_keluar')
                                ->exists();
                if ($sudahDisini) {
                    $ditolak[] = "{$nama} — sudah menjadi penghuni kamar ini";
                    continue;
                }

                // 3) Masih tercatat di kamar lain?
                $lain = AsramaMember::with('kamar')
                            ->where('student_id', $siswa->id)
                            ->whereNull('tanggal_keluar')
                            ->first();
                if ($lain) {
                    if (! $pindahkan) {
                        $namaKamarLain = optional($lain->kamar)->nama_kamar ?: ('kamar #' . $lain->kamar_id);
                        $ditolak[] = "{$nama} — masih tercatat di kamar {$namaKamarLain}. Centang “Pindahkan” bila memang mau dipindah.";
                        continue;
                    }
                }

                // 4) Penjaga kapasitas
                if ($kapasitas > 0 && ! $paksaKapasitas && ($penghuni + count($diterima) + 1) > $kapasitas) {
                    $ditolak[] = "{$nama} — kamar sudah penuh (kapasitas {$kapasitas} orang)";
                    continue;
                }

                // Boleh masuk: kalau dia masih di kamar lain, tutup keanggotaan lama
                if ($lain) {
                    $namaKamarLain = optional($lain->kamar)->nama_kamar ?: ('kamar #' . $lain->kamar_id);
                    $lain->update([
                        'tanggal_keluar' => now()->toDateString(),
                        'catatan'        => 'Dipindahkan ke kamar ' . $kamar->nama_kamar,
                        'dicatat_oleh'   => Auth::id(),
                    ]);
                    $dipindah[] = "{$nama} (dari {$namaKamarLain})";
                }

                AsramaMember::create([
                    'kamar_id'     => $kamar->id,
                    'student_id'   => $siswa->id,
                    'tanggal_masuk' => now()->toDateString(),
                    'catatan'      => $request->filled('catatan') ? trim($request->catatan) : ($lain ? 'Pindahan' : null),
                    'dicatat_oleh' => Auth::id(),
                ]);

                $diterima[] = $nama;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi Kesalahan Database saat memasukkan siswa: ' . $e->getMessage());
        }

        if ($ditolak) {
            session()->flash('masalah_penghuni', $ditolak);
        }

        if ($diterima) {
            $pesan = '✅ ' . count($diterima) . ' siswa masuk kamar ' . $kamar->nama_kamar . '.';
            if ($dipindah) {
                $pesan .= ' Dipindahkan dari kamar lain: ' . implode(', ', $dipindah) . '.';
            }

            return back()->with('success', $pesan);
        }

        return back()->with('error', 'Tidak ada siswa yang bisa dimasukkan ke kamar ini. Lihat daftar alasan di bawah.');
    }

    // ====================================================
    // Keluarkan penghuni — dicatat sebagai riwayat (bukan dihapus)
    // ====================================================
    public function removeMember(Request $request, $member_id)
    {
        try {
            $member = AsramaMember::with('student', 'kamar')->findOrFail($member_id);

            if ($member->tanggal_keluar) {
                return back()->with('error', 'Siswa ini sudah tercatat keluar dari kamar pada ' . Carbon::parse($member->tanggal_keluar)->translatedFormat('d M Y') . '.');
            }

            $member->update([
                'tanggal_keluar' => now()->toDateString(),
                'catatan'        => $request->filled('catatan') ? trim($request->catatan) : 'Dikeluarkan dari kamar',
                'dicatat_oleh'   => Auth::id(),
            ]);

            if ($request->filled('buat_izin') && $request->boolean('buat_izin')) {
                \App\Models\AsramaIzin::create([
                    'student_id'         => $member->student_id,
                    'kamar_id'           => $member->kamar_id,
                    'jenis'              => 'pulang',
                    'mulai'              => now()->toDateString(),
                    'sampai'             => now()->toDateString(),
                    'alasan'             => $request->filled('catatan') ? trim($request->catatan) : 'Keluar dari kamar asrama',
                    'status'             => 'disetujui',
                    'diajukan_oleh'      => Auth::id(),
                    'disetujui_oleh'     => Auth::id(),
                ]);
            }

            $nama = optional($member->student)->nama_lengkap ?: 'Siswa';

            return back()->with('success', "👋 {$nama} tercatat keluar dari kamar (riwayat tetap tersimpan).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengeluarkan siswa: ' . $e->getMessage());
        }
    }

    // ====================================================
    // Riwayat mutasi penghuni (masuk / keluar / pindah)
    // ====================================================
    public function riwayat(Request $request)
    {
        $bulan = (string) $request->input('bulan');
        if (! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = '';
        }

        $kamarId = (int) $request->input('kamar_id');
        $jenis = in_array($request->input('jenis'), ['masuk', 'keluar'], true) ? $request->input('jenis') : 'semua';

        $query = AsramaMember::with(['student', 'kamar']);

        if ($kamarId) {
            $query->where('kamar_id', $kamarId);
        }

        $rows = $query->orderByDesc('updated_at')->get();

        $peristiwa = [];
        foreach ($rows as $r) {
            if ($jenis !== 'keluar' && $r->tanggal_masuk) {
                $peristiwa[] = [
                    'tanggal' => Carbon::parse($r->tanggal_masuk)->toDateString(),
                    'jenis'   => 'masuk',
                    'siswa'   => optional($r->student)->nama_lengkap ?: 'Siswa dihapus',
                    'kelas'   => optional($r->student)->kelas,
                    'kamar'   => optional($r->kamar)->nama_kamar ?: '-',
                    'catatan' => $r->catatan,
                ];
            }
            if ($jenis !== 'masuk' && $r->tanggal_keluar) {
                $peristiwa[] = [
                    'tanggal' => Carbon::parse($r->tanggal_keluar)->toDateString(),
                    'jenis'   => str_contains((string) $r->catatan, 'Dipindahkan') ? 'pindah' : 'keluar',
                    'siswa'   => optional($r->student)->nama_lengkap ?: 'Siswa dihapus',
                    'kelas'   => optional($r->student)->kelas,
                    'kamar'   => optional($r->kamar)->nama_kamar ?: '-',
                    'catatan' => $r->catatan,
                ];
            }
        }

        if ($bulan !== '') {
            $peristiwa = array_values(array_filter($peristiwa, fn ($p) => substr($p['tanggal'], 0, 7) === $bulan));
        }

        usort($peristiwa, fn ($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        $peristiwa = array_slice($peristiwa, 0, 400);

        $daftarBulan = AsramaMember::whereNotNull('tanggal_masuk')
            ->pluck('tanggal_masuk')
            ->map(fn ($t) => substr((string) $t, 0, 7))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $kamarList = AsramaKamar::orderBy('kategori')->orderBy('nama_kamar')->get();

        return view('asrama.kamar.riwayat', compact('peristiwa', 'daftarBulan', 'kamarList', 'bulan', 'kamarId', 'jenis'));
    }

    // ====================================================
    // Cetak daftar penghuni per kamar (untuk ditempel di pintu kamar)
    // ====================================================
    public function cetak(Request $request)
    {
        $kategori = in_array($request->input('kategori'), ['putra', 'putri'], true) ? $request->input('kategori') : null;
        $kamarId = (int) $request->input('kamar_id');

        $query = AsramaKamar::with('musyrif')
            ->with(['members' => function ($q) {
                $q->whereNull('tanggal_keluar')->with('student')->orderBy('tanggal_masuk');
            }]);

        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        if ($kamarId) {
            $query->where('id', $kamarId);
        }

        $kamars = $query->orderBy('kategori')->orderBy('nama_kamar')->get();

        return view('asrama.kamar.cetak', [
            'kamars'      => $kamars,
            'kategori'    => $kategori,
            'pengaturan'  => \App\Models\Pengaturan::first(),
            'dicetakOleh' => Auth::user()->name ?? '-',
        ]);
    }
}
