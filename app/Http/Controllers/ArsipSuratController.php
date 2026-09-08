<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArsipSurat;

class ArsipSuratController extends Controller
{
    public function index()
    {
        $arsips = ArsipSurat::latest()->get();
        return view('arsip.index', compact('arsips'));
    }

    public function create()
    {
        return view('arsip.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if ($request->hasFile('file_surat')) {
            $data['file_surat'] = $request->file('file_surat')->store('arsip_dokumen', 'public');
        }
        ArsipSurat::create($data);
        return redirect()->route('arsip.index');
    }

    public function show($id)
    {
        $arsip = ArsipSurat::findOrFail($id);
        return view('arsip.show', compact('arsip'));
    }

    // --- FITUR BARU: EDIT & HAPUS DI BAWAH INI ---

    public function edit($id)
    {
        // Cari data yang mau diedit, lalu bawa ke halaman form edit
        $arsip = ArsipSurat::findOrFail($id);
        return view('arsip.edit', compact('arsip'));
    }

    public function update(Request $request, $id)
    {
        // Cari data lamanya
        $arsip = ArsipSurat::findOrFail($id);
        $data = $request->all();

        // Kalau user upload file baru, timpa file lamanya
        if ($request->hasFile('file_surat')) {
            $data['file_surat'] = $request->file('file_surat')->store('arsip_dokumen', 'public');
        }

        // Simpan pembaruan ke database
        $arsip->update($data);
        return redirect()->route('arsip.index');
    }

    public function destroy($id)
    {
        // Cari data yang mau dihapus, lalu hancurkan!
        $arsip = ArsipSurat::findOrFail($id);
        $arsip->delete();
        return redirect()->route('arsip.index');
    }
}