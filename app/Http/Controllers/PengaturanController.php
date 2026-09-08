<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function edit()
    {
        // Gunakan Eloquent Model untuk menarik data pertama
        $pengaturan = Pengaturan::first();
        
        // Jika belum ada data sama sekali di database, buat otomatis
        if (!$pengaturan) {
            $pengaturan = Pengaturan::create([
                'nama_sekolah' => 'Nama Sekolah Anda',
                'motto' => 'Motto Sekolah',
            ]);
        }

        return view('pengaturan.edit', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        // Validasi inputan form
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'motto' => 'required|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sampul_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $pengaturan = Pengaturan::first();
        
        // Update teks
        $pengaturan->nama_sekolah = $request->nama_sekolah;
        $pengaturan->motto = $request->motto;

        // Proses jika ada foto logo yang diupload
        if ($request->hasFile('logo_path')) {
            // Hapus logo lama jika ada
            if ($pengaturan->logo_path) {
                Storage::disk('public')->delete($pengaturan->logo_path);
            }
            // Simpan yang baru
            $pengaturan->logo_path = $request->file('logo_path')->store('pengaturan', 'public');
        }

        // Proses jika ada foto sampul yang diupload
        if ($request->hasFile('sampul_path')) {
            // Hapus sampul lama jika ada
            if ($pengaturan->sampul_path) {
                Storage::disk('public')->delete($pengaturan->sampul_path);
            }
            // Simpan yang baru
            $pengaturan->sampul_path = $request->file('sampul_path')->store('pengaturan', 'public');
        }

        // Simpan permanen ke database
        $pengaturan->save();

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}