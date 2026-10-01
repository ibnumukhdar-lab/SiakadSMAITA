<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SrPointCriteria;

class SrPointCriteriaController extends Controller
{
    // Menampilkan halaman tabel kriteria
    public function index()
    {
        // Mengurutkan berdasarkan kategori (Negatif/Positif) lalu waktu pembuatan
        $criterias = SrPointCriteria::orderBy('kategori', 'asc')->orderBy('created_at', 'desc')->get();
        return view('student-root.kriteria.index', compact('criterias'));
    }

    // Menyimpan kriteria baru
    public function store(Request $request)
    {
        $request->validate([
            'kategori'      => 'required|in:positif,negatif',
            'nama_perilaku' => 'required|string|max:255',
            'poin'          => 'required|numeric',
            'tingkat'       => 'nullable|in:ringan,sedang,berat',
        ]);

        // Automasi: Pastikan poin negatif selalu minus, positif selalu plus
        $poin = $request->poin;
        if ($request->kategori === 'negatif' && $poin > 0) $poin = -$poin;
        if ($request->kategori === 'positif' && $poin < 0) $poin = abs($poin);

        SrPointCriteria::create([
            'kategori'      => $request->kategori,
            'nama_perilaku' => $request->nama_perilaku,
            'deskripsi'     => $request->deskripsi,
            'poin'          => $poin,
            'tingkat'       => $request->tingkat,
            'status'        => 'aktif', // Default selalu aktif saat baru dibuat
        ]);

        return back()->with('success', '✅ Kriteria poin baru berhasil ditambahkan!');
    }

    // Memperbarui kriteria (Edit & Nonaktifkan)
    public function update(Request $request, $id)
    {
        $criteria = SrPointCriteria::findOrFail($id);
        
        $request->validate([
            'kategori'      => 'required|in:positif,negatif',
            'nama_perilaku' => 'required|string|max:255',
            'poin'          => 'required|numeric',
            'tingkat'       => 'nullable|in:ringan,sedang,berat',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        // Automasi poin
        $poin = $request->poin;
        if ($request->kategori === 'negatif' && $poin > 0) $poin = -$poin;
        if ($request->kategori === 'positif' && $poin < 0) $poin = abs($poin);

        $criteria->update([
            'kategori'      => $request->kategori,
            'nama_perilaku' => $request->nama_perilaku,
            'deskripsi'     => $request->deskripsi,
            'poin'          => $poin,
            'tingkat'       => $request->tingkat,
            'status'        => $request->status,
        ]);

        return back()->with('success', '✅ Kriteria poin berhasil diperbarui!');
    }
}