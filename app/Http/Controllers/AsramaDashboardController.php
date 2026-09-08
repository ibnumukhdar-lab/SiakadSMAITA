<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar; // <-- Pastikan Model Detail dipanggil
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsramaDashboardController extends Controller
{
    public function index()
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // 1. Tarik Data Master Kamar & User (Ambil yang aktif saja sebagai prioritas)
        $semuaKamar = AsramaKamar::where('status', 'aktif')->get();
        if($semuaKamar->count() == 0) {
            $semuaKamar = AsramaKamar::all(); 
        }
        $users = DB::table('users')->pluck('name', 'id');

        // 2. Tarik Data Inspeksi Bulan Ini & Filter Otomatis (Hanya yang berstatus 'final')
        $inspeksiFinalBulanIni = AsramaPenilaian::where('status', 'final')
                            ->whereMonth('tanggal', $bulanIni)
                            ->whereYear('tanggal', $tahunIni)
                            ->pluck('id');

        // Tarik Total Inspeksi Per Kategori (Untuk Angka di Kartu Statistik)
        $totalInspeksiPutra = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putra')->count();
        $totalInspeksiPutri = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putri')->count();

        // Tarik Data Skor Kamar dari Tabel Detail
        $penilaianDetail = AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksiFinalBulanIni)->get();

        // 3. Rakit data klasemen Global
        $rankingKamarRaw = $semuaKamar->map(function ($kamar) use ($penilaianDetail, $users) {
            // Cocokkan ID kamar dengan ID kamar di tabel detail skor
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

        // Urutkan dari skor tertinggi ke terendah
        $rankingKamar = $rankingKamarRaw->sortByDesc('rata_rata_skor')->values();

        // 4. Pisahkan Klasemen Putra & Putri (Beserta Kandidat Top 3)
        
        // ZONA PUTRA
        $rankingPutra = $rankingKamar->where('kategori', 'putra')->values();
        $putraAktif = $rankingPutra->filter(fn($k) => $k->jumlah_sidak > 0)->values(); 
        $putraTerbersih = $putraAktif->take(3); 
        $putraTerkotor = $putraAktif->reverse()->take(3)->values(); 
        $totalKamarPutra = $rankingPutra->count();

        // ZONA PUTRI
        $rankingPutri = $rankingKamar->where('kategori', 'putri')->values();
        $putriAktif = $rankingPutri->filter(fn($k) => $k->jumlah_sidak > 0)->values(); 
        $putriTerbersih = $putriAktif->take(3); 
        $putriTerkotor = $putriAktif->reverse()->take(3)->values(); 
        $totalKamarPutri = $rankingPutri->count();

        return view('asrama.dashboard', compact(
            'totalKamarPutra', 'totalKamarPutri', 
            'totalInspeksiPutra', 'totalInspeksiPutri',
            'rankingPutra', 'rankingPutri', 
            'putraTerbersih', 'putraTerkotor', 
            'putriTerbersih', 'putriTerkotor'
        ));
    }
}