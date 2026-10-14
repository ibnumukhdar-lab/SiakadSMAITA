<?php

namespace App\Http\Controllers;

use App\Services\KlasemenKebersihan;

class AsramaDashboardController extends Controller
{
    /**
     * Dashboard klasemen kebersihan kamar bulan berjalan.
     *
     * Seluruh angka diambil dari App\Services\KlasemenKebersihan supaya
     * dashboard, slide TV, dan halaman Peringkat Bulanan selalu berbunyi sama.
     */
    public function index()
    {
        $bulan = (int) now()->month;
        $tahun = (int) now()->year;

        $baris = KlasemenKebersihan::bulan($bulan, $tahun);

        $rankingPutra = KlasemenKebersihan::divisi($baris, 'putra');
        $rankingPutri = KlasemenKebersihan::divisi($baris, 'putri');

        // Juara bulan = TOTAL SKOR capaian tertinggi (bukan rata-rata)
        $putraTerbersih = KlasemenKebersihan::juara($rankingPutra);
        $putriTerbersih = KlasemenKebersihan::juara($rankingPutri);

        // Perlu perhatian = rata-rata di bawah batas terkotor (tetap aturan 17 Sep 2026)
        $putraTerkotor = KlasemenKebersihan::perhatian($rankingPutra);
        $putriTerkotor = KlasemenKebersihan::perhatian($rankingPutri);

        $totalKamarPutra = $rankingPutra->count();
        $totalKamarPutri = $rankingPutri->count();
        $totalInspeksiPutra = KlasemenKebersihan::jumlahInspeksi($bulan, $tahun, 'putra');
        $totalInspeksiPutri = KlasemenKebersihan::jumlahInspeksi($bulan, $tahun, 'putri');

        $labelBulan = now()->locale('id')->translatedFormat('F Y');

        return view('asrama.dashboard', compact(
            'totalKamarPutra', 'totalKamarPutri',
            'totalInspeksiPutra', 'totalInspeksiPutri',
            'rankingPutra', 'rankingPutri',
            'putraTerbersih', 'putraTerkotor',
            'putriTerbersih', 'putriTerkotor',
            'labelBulan'
        ));
    }
}
