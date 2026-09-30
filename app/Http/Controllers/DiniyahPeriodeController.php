<?php

namespace App\Http\Controllers;

use App\Models\DiniyahPeriode;
use Illuminate\Http\Request;

/**
 * PERIODE (SEMESTER) PENILAIAN DINIYAH — dibuka/ditutup Kepala Diniyah.
 * Selama periode belum dibuka, nilai & absensi tetap bisa diisi sebagai draf
 * tetapi diingatkan bahwa sesi belum dibuka (pola yang sama dengan modul Adab).
 */
class DiniyahPeriodeController extends Controller
{
    public function index()
    {
        $periode = DiniyahPeriode::orderByDesc('terbuka')->orderByDesc('id')->get();

        return view('diniyah.periode', [
            'periode'      => $periode,
            'aktif'        => DiniyahPeriode::aktifSekarang(),
            'semesterList' => DiniyahPeriode::SEMESTER,
            'usulanNama'   => 'Semester ' . (DiniyahPeriode::semesterKalender() === 's1' ? '1' : '2') . ' · ' . DiniyahPeriode::tahunAjaranKalender(),
            'usulanTahun'  => DiniyahPeriode::tahunAjaranKalender(),
            'usulanHijriah' => \App\Support\DiniyahArab::tahunHijriah(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:120',
            'tahun_ajaran'   => 'nullable|string|max:20',
            'semester'       => 'required|in:s1,s2',
            'tahun_hijriah'  => 'nullable|string|max:20',
        ]);

        DiniyahPeriode::create([
            'nama'          => trim((string) $request->input('nama')),
            'tahun_ajaran'  => trim((string) $request->input('tahun_ajaran')) ?: null,
            'semester'      => $request->input('semester'),
            'tahun_hijriah' => trim((string) $request->input('tahun_hijriah')) ?: null,
            'terbuka'       => false,
        ]);

        return back()->with('success', 'Periode diniyah dibuat (masih tertutup — buka dulu bila penilaian sudah siap).');
    }

    public function buka($id)
    {
        $periode = DiniyahPeriode::findOrFail($id);

        // Hanya satu periode yang boleh terbuka supaya angka rapor tidak bercampur.
        DiniyahPeriode::where('id', '!=', $periode->id)->where('terbuka', true)->each(fn ($p) => $p->tutup(auth()->id()));

        $periode->buka(auth()->id());

        return back()->with('success', 'Periode "' . $periode->nama . '" dibuka. Musyrif/musyrifah dapat mengisi nilai & absensi.');
    }

    public function tutup($id)
    {
        $periode = DiniyahPeriode::findOrFail($id);
        $periode->tutup(auth()->id());

        return back()->with('success', 'Periode "' . $periode->nama . '" ditutup.');
    }
}
