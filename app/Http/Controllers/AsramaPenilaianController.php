<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar;
use App\Models\AsramaMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AsramaPenilaianController extends Controller
{
    // =========================================================
    // 1. FUNGSI INDEX (HALAMAN HISTORI INSPEKSI) -> asrama/penilaian/index.blade.php
    // =========================================================
    public function index()
    {
        $histori = AsramaPenilaian::with(['kamarTerbersih', 'kamarTerkotor', 'musyrif'])
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
        $putraTerkotor = $putraAktif->reverse()->take(3)->values(); 

        $rankingPutri = $rankingKamar->where('kategori', 'putri')->values();
        $putriAktif = $rankingPutri->filter(fn($k) => $k->jumlah_sidak > 0)->values(); 
        $putriTerbersih = $putriAktif->take(3); 
        $putriTerkotor = $putriAktif->reverse()->take(3)->values(); 

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

        $maxSkor = $dinilai->max('total_skor');
        $minSkor = $dinilai->min('total_skor');

        $kandidatBersih = $dinilai->where('total_skor', $maxSkor)->values();
        $kandidatKotor = $dinilai->where('total_skor', $minSkor)->values();

        return view('asrama.penilaian.konfirmasi', compact(
            'penilaian', 'kategori', 'kandidatBersih', 'kandidatKotor', 'maxSkor', 'minSkor'
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
            'kamar_terkotor_id'  => 'required|exists:asrama_kamars,id',
            'foto_terbersih'     => 'nullable|image|max:3072', 
            'foto_terkotor'      => 'nullable|image|max:3072', 
        ]);

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                        ->where('kategori', $request->kategori)
                        ->where('status', 'draft')
                        ->firstOrFail();

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
                'kamar_terkotor_id' => $request->kamar_terkotor_id,
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
                
                $kriteriaAsrama = DB::table('sr_point_criteria')
                    ->where('nama_perilaku', 'LIKE', '%Asrama%')
                    ->where('poin', $poin)
                    ->first();

                if (!$kriteriaAsrama) {
                    try {
                        $kriteriaIdBaru = DB::table('sr_point_criteria')->insertGetId([
                            'nama_perilaku' => $namaKriteria,
                            'poin' => $poin,
                            'jenis' => ($poin > 0) ? 'prestasi' : 'pelanggaran',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $kriteriaValid = $kriteriaIdBaru;
                    } catch (\Throwable $e) {
                        $kriteriaValid = DB::table('sr_point_criteria')->value('id');
                    }
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
            $injeksiPoin($request->kamar_terkotor_id, -1, "Kamar Terkotor $KategoriJudul");

            DB::commit();
            
            return redirect('/asrama/penilaian')->with('success', "✨ Inspeksi Divisi $KategoriJudul Selesai! Poin kebersihan asrama disuntikkan & Bukti Foto dikirim ke TV Display.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi Kesalahan Sistem: ' . $e->getMessage() . ' (pada baris ' . $e->getLine() . ')');
        }
    }

    // =========================================================
    // 7. FUNGSI UPLOAD FOTO SUSULAN (DARI HALAMAN HISTORI)
    // =========================================================
    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:terbersih,terkotor',
            'foto'  => 'required|image|max:3072',
        ]);

        $penilaian = AsramaPenilaian::findOrFail($id);

        $path = $request->file('foto')->store('asrama/'.now()->format('Y/m'), 'public');

        if ($request->jenis == 'terbersih') {
            $penilaian->update(['foto_terbersih' => $path]);
        } else {
            $penilaian->update(['foto_terkotor' => $path]);
        }

        return back()->with('success', '📸 Foto susulan berhasil diunggah dan disimpan!');
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