<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar;
use App\Models\AsramaMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AsramaPenilaianController extends Controller
{
    // =========================================================
    // ATURAN BATAS KAMAR TERKOTOR (permintaan Fahri, 17 Sep 2026)
    // Kamar hanya boleh ditandai TERKOTOR bila nilainya di bawah
    // BATAS_TERKOTOR_PERSEN dari skor maksimal. Bila semua kamar >= batas itu,
    // statusnya BERSIH (tidak ada poin -1) dan kamar paling bawah hanya masuk
    // daftar "perlu diperhatikan" + catatan inspektor.
    // =========================================================
    public const SKOR_MAKS = 25;                 // 5 kriteria × skor maksimal 5
    public const BATAS_TERKOTOR_PERSEN = 70;     // batas bawah (%) untuk menyebut kamar "terkotor"

    /** Ambang nilai absolut: 70% dari 25 = 17,5 → kamar dengan total < 17,5 (yaitu maks 17) yang boleh disebut terkotor. */
    public static function batasTerkotor(): float
    {
        return self::SKOR_MAKS * self::BATAS_TERKOTOR_PERSEN / 100;
    }

    /** Persentase nilai kamar terhadap skor maksimal. */
    public static function persenSkor($total): float
    {
        return round(((float) $total) / self::SKOR_MAKS * 100, 1);
    }

    // =========================================================
    // 1. FUNGSI INDEX (HALAMAN HISTORI INSPEKSI) -> asrama/penilaian/index.blade.php
    // =========================================================
    public function index()
    {
        $histori = AsramaPenilaian::with(['kamarTerbersih', 'kamarTerkotor', 'kamarPerhatian', 'musyrif', 'rincianKamars'])
                    ->where('status', 'final')
                    ->orderBy('tanggal', 'desc')
                    ->get();

        return view('asrama.penilaian.index', compact('histori'));
    }

    // =========================================================
    // 2. FUNGSI DASHBOARD (KLASEMEN) -> asrama/dashboard.blade.php
    // =========================================================
    public function dashboard()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $semuaKamar = AsramaKamar::where('status', 'aktif')->get();
        if($semuaKamar->count() == 0) {
            $semuaKamar = AsramaKamar::all(); 
        }
        
        $totalKamarPutra = $semuaKamar->filter(fn($k) => strtolower($k->kategori ?? '') == 'putra')->count();
        $totalKamarPutri = $semuaKamar->filter(fn($k) => strtolower($k->kategori ?? '') == 'putri')->count();

        $inspeksiFinalBulanIni = AsramaPenilaian::where('status', 'final')
                                    ->whereMonth('tanggal', $bulanIni)
                                    ->whereYear('tanggal', $tahunIni)
                                    ->pluck('id');

        $totalInspeksiPutra = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putra')->count();
        $totalInspeksiPutri = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putri')->count();

        $penilaianDetail = AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksiFinalBulanIni)->get();
        $users = DB::table('users')->pluck('name', 'id');

        $rankingKamarRaw = $semuaKamar->map(function ($kamar) use ($penilaianDetail, $users) {
            $sidakKamarIni = $penilaianDetail->where('kamar_id', $kamar->id);
            $jumlahSidak = $sidakKamarIni->count();
            
            $rataRata = $jumlahSidak > 0 ? $sidakKamarIni->avg('total_skor') : 0;

            $musyrifId = $kamar->musyrif_id ?? $kamar->pembina_id ?? $kamar->guru_id ?? null;
            $namaMusyrif = $musyrifId ? ($users[$musyrifId] ?? 'Belum Diatur') : 'Belum Diatur';

            return (object) [
                'id' => $kamar->id,
                'nama_kamar' => $kamar->nama_kamar ?? 'Kamar ' . $kamar->id,
                'nama_musyrif' => $namaMusyrif,
                'kategori' => strtolower($kamar->kategori ?? ''),
                'jumlah_sidak' => $jumlahSidak,
                'rata_rata_skor' => (float) $rataRata,
            ];
        });

        $rankingKamar = $rankingKamarRaw->sortByDesc('rata_rata_skor')->values();

        $rankingPutra = $rankingKamar->where('kategori', 'putra')->values();
        $putraAktif = $rankingPutra->filter(fn($k) => $k->jumlah_sidak > 0)->values(); 
        $putraTerbersih = $putraAktif->take(3); 
        // Aturan 17 Sep 2026: "Perhatian Ekstra" hanya untuk kamar dengan rata-rata DI BAWAH 70% (17,5 dari 25).
        // Kamar yang rata-ratanya 70% ke atas dianggap bersih, tidak dipajang sebagai terkotor.
        $putraTerkotor = $putraAktif->filter(fn($k) => $k->rata_rata_skor < self::batasTerkotor())
                                    ->reverse()->take(3)->values();

        $rankingPutri = $rankingKamar->where('kategori', 'putri')->values();
        $putriAktif = $rankingPutri->filter(fn($k) => $k->jumlah_sidak > 0)->values(); 
        $putriTerbersih = $putriAktif->take(3); 
        $putriTerkotor = $putriAktif->filter(fn($k) => $k->rata_rata_skor < self::batasTerkotor())
                                    ->reverse()->take(3)->values();

        return view('asrama.dashboard', compact(
            'totalKamarPutra', 'totalKamarPutri', 
            'totalInspeksiPutra', 'totalInspeksiPutri',
            'rankingPutra', 'rankingPutri', 
            'putraTerbersih', 'putraTerkotor', 
            'putriTerbersih', 'putriTerkotor'
        ));
    }

    // =========================================================
    // 3. FUNGSI HARI INI (DILENGKAPI AUTO-CLEANUP DATA HANTU)
    // =========================================================
    public function hariIni()
    {
        $tanggal_hari_ini = now()->toDateString();
        
        $kamars = AsramaKamar::where('status', 'aktif')->orderBy('nama_kamar', 'asc')->get();
        if ($kamars->count() == 0) {
            $kamars = AsramaKamar::orderBy('nama_kamar', 'asc')->get(); 
        }
        
        $kamar_putra = $kamars->filter(fn($k) => strtolower($k->kategori ?? '') == 'putra');
        $kamar_putri = $kamars->filter(fn($k) => strtolower($k->kategori ?? '') == 'putri');

        if ($kamars->count() == 0) {
            return redirect('/asrama/kamar')->with('error', 'Belum ada data kamar sama sekali di database.');
        }

        $draft_putra = AsramaPenilaian::firstOrCreate(['tanggal' => $tanggal_hari_ini, 'kategori' => 'putra'], ['status' => 'draft']);
        $draft_putri = AsramaPenilaian::firstOrCreate(['tanggal' => $tanggal_hari_ini, 'kategori' => 'putri'], ['status' => 'draft']);

        // =========================================================================
        // PERBAIKAN: AUTO-CLEANUP DATA HANTU
        // Menghapus data penilaian kamar yang sudah dihapus/dinonaktifkan
        // agar angka progress kembali sinkron (contoh: 11 / 11)
        // =========================================================================
        $kamar_putra_ids = $kamar_putra->pluck('id')->toArray();
        if (!empty($kamar_putra_ids)) {
            AsramaPenilaianKamar::where('penilaian_id', $draft_putra->id)
                ->whereNotIn('kamar_id', $kamar_putra_ids)
                ->delete();
        }

        $kamar_putri_ids = $kamar_putri->pluck('id')->toArray();
        if (!empty($kamar_putri_ids)) {
            AsramaPenilaianKamar::where('penilaian_id', $draft_putri->id)
                ->whereNotIn('kamar_id', $kamar_putri_ids)
                ->delete();
        }
        // =========================================================================

        $dinilai_putra = AsramaPenilaianKamar::where('penilaian_id', $draft_putra->id)->pluck('kamar_id')->toArray();
        $selesai_putra = ($kamar_putra->count() > 0 && count($dinilai_putra) == $kamar_putra->count());
        $kandidat_putra = $selesai_putra ? AsramaPenilaianKamar::with('kamar')->where('penilaian_id', $draft_putra->id)->orderBy('total_skor', 'desc')->get() : null;

        $dinilai_putri = AsramaPenilaianKamar::where('penilaian_id', $draft_putri->id)->pluck('kamar_id')->toArray();
        $selesai_putri = ($kamar_putri->count() > 0 && count($dinilai_putri) == $kamar_putri->count());
        $kandidat_putri = $selesai_putri ? AsramaPenilaianKamar::with('kamar')->where('penilaian_id', $draft_putri->id)->orderBy('total_skor', 'desc')->get() : null;

        return view('asrama.penilaian.hari-ini', compact(
            'kamar_putra', 'kamar_putri', 'draft_putra', 'draft_putri',
            'dinilai_putra', 'dinilai_putri', 'selesai_putra', 'selesai_putri',
            'kandidat_putra', 'kandidat_putri'
        ));
    }

    // =========================================================
    // 4. FUNGSI SIMPAN SKOR
    // =========================================================
    public function simpanSkor(Request $request, $kamar_id)
    {
        $kamar = AsramaKamar::findOrFail($kamar_id);
        
        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
            ->where('kategori', $kamar->kategori)
            ->where('status', 'draft')
            ->firstOrFail();
        
        $request->validate([
            'skor_1' => 'required|integer|min:1|max:5',
            'skor_2' => 'required|integer|min:1|max:5',
            'skor_3' => 'required|integer|min:1|max:5',
            'skor_4' => 'required|integer|min:1|max:5',
            'skor_5' => 'required|integer|min:1|max:5',
        ]);

        $total_skor = $request->skor_1 + $request->skor_2 + $request->skor_3 + $request->skor_4 + $request->skor_5;

        AsramaPenilaianKamar::updateOrCreate(
            ['penilaian_id' => $penilaian->id, 'kamar_id' => $kamar->id],
            [
                'skor_1' => $request->skor_1, 'skor_2' => $request->skor_2, 'skor_3' => $request->skor_3,
                'skor_4' => $request->skor_4, 'skor_5' => $request->skor_5, 'total_skor' => $total_skor,
                'catatan' => $request->catatan,
            ]
        );

        return back()->with('success', "Skor kamar {$kamar->nama_kamar} berhasil disimpan!");
    }

    // =========================================================
    // 5. FUNGSI KONFIRMASI PENGUNCIAN
    // =========================================================
    public function konfirmasi(Request $request)
    {
        $kategori = $request->kategori ?? 'putra'; 

        $tanggal_hari_ini = now()->toDateString();
        $penilaian = AsramaPenilaian::where('tanggal', $tanggal_hari_ini)
                    ->where('kategori', $kategori)
                    ->where('status', 'draft')
                    ->first();

        if (!$penilaian) {
            return redirect('/asrama/penilaian/hari-ini')->with('error', "Inspeksi $kategori hari ini sudah dikunci (final).");
        }

        $dinilai = AsramaPenilaianKamar::with('kamar')->where('penilaian_id', $penilaian->id)->get();
        
        if($dinilai->count() == 0) {
            return redirect('/asrama/penilaian/hari-ini')->with('error', "Belum ada kamar $kategori yang dinilai hari ini.");
        }

        $dinilai = $dinilai->sortByDesc('total_skor')->values();

        $maxSkor = (int) $dinilai->max('total_skor');
        $minSkor = (int) $dinilai->min('total_skor');

        $kandidatBersih = $dinilai->where('total_skor', $maxSkor)->values();

        // --- Aturan batas bawah: hanya kamar di bawah 70% yang boleh disebut TERKOTOR ---
        $batas = self::batasTerkotor();
        $bawahAmbang = $dinilai->filter(fn ($k) => (float) $k->total_skor < $batas)
                               ->sortBy('total_skor')->values();
        $adaTerkotor = $bawahAmbang->isNotEmpty();
        $kandidatKotor = $adaTerkotor ? $bawahAmbang : collect();

        // Kamar paling bawah (untuk daftar "perlu diperhatikan" bila semua kamar bersih)
        $terbawah = $dinilai->where('total_skor', $minSkor)->values();

        return view('asrama.penilaian.konfirmasi', compact(
            'penilaian', 'kategori', 'kandidatBersih', 'kandidatKotor', 'maxSkor', 'minSkor',
            'adaTerkotor', 'terbawah', 'batas'
        ));
    }

    // =========================================================
    // 6. FUNGSI FINALISASI
    // =========================================================
    public function finalisasi(Request $request)
    {
        $request->validate([
            'kategori'           => 'required|in:putra,putri',
            'kamar_terbersih_id' => 'required|exists:asrama_kamars,id',
            'kamar_terkotor_id'  => 'nullable|exists:asrama_kamars,id',
            'catatan_inspektor'  => 'nullable|string|max:1000',
            'foto_terbersih'     => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
            'foto_terkotor'      => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
        ], [
            'kamar_terbersih_id.required' => 'Pilih dulu kamar terbersih sebelum mengunci finalisasi.',
            'foto_terbersih.mimes' => 'Foto kamar terbersih harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto_terkotor.mimes'  => 'Foto kamar terkotor harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto_terbersih.max'   => 'Foto kamar terbersih terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
            'foto_terkotor.max'    => 'Foto kamar terkotor terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
            'catatan_inspektor.max' => 'Catatan inspektor terlalu panjang (maksimal 1000 karakter).',
        ]);

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                        ->where('kategori', $request->kategori)
                        ->where('status', 'draft')
                        ->firstOrFail();

        // ------------------------------------------------------------------
        // ATURAN BATAS BAWAH (17 Sep 2026):
        // kamar hanya boleh disebut TERKOTOR bila nilainya < 70% skor maksimal.
        // Skor dihitung ULANG di server, tidak percaya kiriman form.
        // ------------------------------------------------------------------
        $skorKamar = AsramaPenilaianKamar::where('penilaian_id', $penilaian->id)->pluck('total_skor', 'kamar_id');

        if ($skorKamar->isEmpty()) {
            return back()->with('error', 'Belum ada kamar yang dinilai pada lembar ini, finalisasi dibatalkan.');
        }

        $batas = self::batasTerkotor();
        $kandidatTerkotor = $skorKamar->filter(fn ($s) => (float) $s < $batas);

        $kamarTerkotorId = $request->filled('kamar_terkotor_id') ? (int) $request->kamar_terkotor_id : null;

        if ($kamarTerkotorId) {
            $skorPilih = $skorKamar[$kamarTerkotorId] ?? null;

            if ($skorPilih === null) {
                return back()->with('error', 'Kamar yang dipilih sebagai terkotor tidak dinilai pada lembar inspeksi ini.');
            }

            if ((float) $skorPilih >= $batas) {
                return back()->with('error', 'Kamar itu tidak bisa ditandai terkotor: nilainya ' . self::persenSkor($skorPilih) . '%, sedangkan batas terkotor adalah di bawah ' . self::BATAS_TERKOTOR_PERSEN . '%.');
            }
        } elseif ($kandidatTerkotor->isNotEmpty()) {
            return back()->with('error', 'Masih ada kamar bernilai di bawah ' . self::BATAS_TERKOTOR_PERSEN . '% — pilih salah satu sebagai kamar terkotor sebelum mengunci.');
        }

        // Kamar "perlu diperhatikan": terkotor bila ada, kalau tidak → kamar paling bawah
        $kamarPerhatianId = $kamarTerkotorId ?: (int) $skorKamar->sort()->keys()->first();

        DB::beginTransaction();
        try {
            $path_terbersih = null;
            if ($request->hasFile('foto_terbersih')) {
                $path_terbersih = $request->file('foto_terbersih')->store('asrama/'.now()->format('Y/m'), 'public');
            }

            $path_terkotor = null;
            if ($request->hasFile('foto_terkotor')) {
                $path_terkotor = $request->file('foto_terkotor')->store('asrama/'.now()->format('Y/m'), 'public');
            }

            $penilaian->update([
                'status' => 'final',
                'musyrif_id' => Auth::id(),
                'kamar_terbersih_id' => $request->kamar_terbersih_id,
                'kamar_terkotor_id' => $kamarTerkotorId,
                'kamar_perhatian_id' => $kamarPerhatianId,
                'catatan_inspektor' => $request->filled('catatan_inspektor') ? trim($request->catatan_inspektor) : null,
                'foto_terbersih' => $path_terbersih,
                'foto_terkotor' => $path_terkotor,
            ]);

            // =====================================================
            // PERBAIKAN: KRITERIA KHUSUS KEBERSIHAN ASRAMA
            // =====================================================
            $injeksiPoin = function($kamar_id, $poin, $gelar) {
                $kamar = AsramaKamar::find($kamar_id);
                if (!$kamar) return; 

                $anggotaKamar = AsramaMember::where('kamar_id', $kamar_id)->get();
                
                $namaKriteria = ($poin > 0) ? 'Kamar Terbersih Asrama' : 'Kamar Terkotor Asrama';
                $kategoriKriteria = ($poin > 0) ? 'positif' : 'negatif';

                // Cari kriteria sesuai kolom ASLI DB (kategori positif/negatif).
                // CATATAN 2026-09: sebelumnya memakai kolom 'jenis' yang tidak ada
                // => selalu jatuh ke fallback kriteria pertama (criteria_id SALAH).
                $kriteriaAsrama = DB::table('sr_point_criteria')
                    ->where('nama_perilaku', $namaKriteria)
                    ->where('kategori', $kategoriKriteria)
                    ->where('poin', $poin)
                    ->first();

                if (!$kriteriaAsrama) {
                    $kriteriaIdBaru = Str::uuid()->toString();
                    DB::table('sr_point_criteria')->insert([
                        'id'             => $kriteriaIdBaru,
                        'nama_perilaku'  => $namaKriteria,
                        'kategori'       => $kategoriKriteria,
                        'deskripsi'      => 'Poin otomatis hasil inspeksi kebersihan asrama (finalisasi harian).',
                        'poin'           => $poin,
                        'tingkat'        => null,
                        'status'         => 'aktif',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    $kriteriaValid = $kriteriaIdBaru;
                } else {
                    $kriteriaValid = $kriteriaAsrama->id;
                }
                
                foreach($anggotaKamar as $anggota) {
                    $grupSiswa = DB::table('sr_group_members')->where('student_id', $anggota->student_id)->whereNull('tanggal_keluar')->first();
                    
                    DB::table('sr_point_entries')->insert([
                        'id' => Str::uuid()->toString(), 
                        'student_id' => $anggota->student_id, 
                        'group_id' => $grupSiswa ? $grupSiswa->group_id : null,
                        'criteria_id' => $kriteriaValid, 
                        'input_by' => Auth::id(), 
                        'tanggal_kejadian' => now()->toDateString(), 
                        'poin' => $poin, 
                        'catatan' => "$gelar Asrama ($kamar->nama_kamar)",
                        'created_at' => now(), 
                        'updated_at' => now(),
                    ]);
                }
            };

            $KategoriJudul = ucfirst($request->kategori);
            $injeksiPoin($request->kamar_terbersih_id, 1, "Kamar Terbersih $KategoriJudul");

            // Poin -1 HANYA diberikan bila memang ada kamar di bawah batas (70%)
            if ($kamarTerkotorId) {
                $injeksiPoin($kamarTerkotorId, -1, "Kamar Terkotor $KategoriJudul");
            }

            DB::commit();

            $namaPerhatian = optional(AsramaKamar::find($kamarPerhatianId))->nama_kamar;

            if ($kamarTerkotorId) {
                $pesan = "✨ Inspeksi Divisi $KategoriJudul selesai! Poin +1 (kamar terbersih) dan −1 (kamar terkotor) sudah disuntikkan, bukti foto dikirim ke TV Display.";
            } else {
                $pesan = "✅ Inspeksi Divisi $KategoriJudul selesai — semua kamar bernilai " . self::BATAS_TERKOTOR_PERSEN . "% ke atas, jadi tidak ada kamar terkotor dan tidak ada poin pengurangan. Kamar " . ($namaPerhatian ?: '-') . " masuk daftar perlu diperhatikan.";
            }

            return redirect('/asrama/penilaian')->with('success', $pesan);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi Kesalahan Sistem: ' . $e->getMessage() . ' (pada baris ' . $e->getLine() . ')');
        }
    }

    // =========================================================
    // 7. FUNGSI UPLOAD / GANTI FOTO (DARI HALAMAN HISTORI)
    // =========================================================
    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:terbersih,terkotor',
            'foto'  => 'required|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
        ], [
            'foto.required' => 'Pilih dulu file fotonya sebelum menekan Upload.',
            'foto.mimes'    => 'Berkas harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto.max'      => 'Fotonya terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
        ]);

        $penilaian = AsramaPenilaian::findOrFail($id);
        $kolom = $request->jenis === 'terbersih' ? 'foto_terbersih' : 'foto_terkotor';

        DB::beginTransaction();
        try {
            $path = $request->file('foto')->store('asrama/' . now()->format('Y/m'), 'public');

            // Hapus berkas lama supaya tidak menumpuk di hosting
            $lama = $penilaian->{$kolom};
            if ($lama && $lama !== $path && Storage::disk('public')->exists($lama)) {
                Storage::disk('public')->delete($lama);
            }

            $penilaian->update([$kolom => $path]);

            DB::commit();

            return back()->with('success', '📸 Foto ' . $request->jenis . ' berhasil diunggah dan disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengunggah foto: ' . $e->getMessage());
        }
    }

    // =========================================================
    // 8. FUNGSI RESET PENILAIAN HARI INI
    // =========================================================
    public function reset(Request $request)
    {
        $request->validate(['kategori' => 'required|in:putra,putri']);
        $kategori = $request->kategori;

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                    ->where('kategori', $kategori)
                    ->where('status', 'draft')
                    ->first();

        if ($penilaian) {
            // Hapus semua detail skor kamar yang sudah diinput hari ini
            AsramaPenilaianKamar::where('penilaian_id', $penilaian->id)->delete();
            $label = ucfirst($kategori);
            return back()->with('success', "🔄 Penilaian Divisi $label hari ini berhasil direset. Silakan mulai ulang.");
        }

        return back()->with('error', 'Penilaian divisi ini sudah difinalisasi atau belum ada data untuk direset.');
    }
}