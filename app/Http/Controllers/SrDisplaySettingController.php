<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SrDisplaySetting;
use App\Models\BeeWeek; // <-- TAMBAHAN: Memanggil Data BEE Smart
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SrDisplaySettingController extends Controller
{
    public function edit()
    {
        // Ambil data pengaturan pertama, jika kosong buatkan data default
        $setting = SrDisplaySetting::firstOrCreate([
            'id' => 1
        ], [
            'judul_utama' => 'STUDENT ROOT AGREGATOR',
            'durasi_slide' => 60
        ]);

        return view('student-root.display-setting.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'judul_utama' => 'required|string|max:255',
            'durasi_slide' => 'required|integer|min:10',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'background_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $setting = SrDisplaySetting::first();

        // Data yang akan diupdate
        $data = [
            'judul_utama' => $request->judul_utama,
            'durasi_slide' => $request->durasi_slide,
        ];

        // Proses Upload Logo jika ada
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('display', 'public');
        }

        // Proses Upload Background jika ada
        if ($request->hasFile('background_image')) {
            // Hapus background lama jika ada
            if ($setting->background_image && Storage::disk('public')->exists($setting->background_image)) {
                Storage::disk('public')->delete($setting->background_image);
            }
            $data['background_image'] = $request->file('background_image')->store('display', 'public');
        }

        $setting->update($data);

        return back()->with('success', 'Pengaturan Display TV berhasil diperbarui!');
    }

    // --- FUNGSI UNTUK MENAMPILKAN TV DISPLAY (GABUNGAN SR & BEE SMART) ---
    public function showTv()
    {
        // 1. Ambil pengaturan (jika belum disetting, panggil default)
        $setting = SrDisplaySetting::first();
        if (!$setting) {
            $setting = (object) [
                'judul_utama' => 'STUDENT ROOT AGREGATOR',
                'durasi_slide' => 60,
                'logo' => null,
                'background_image' => null
            ];
        }

        // 2. Data Semua Grup (Untuk Slide 1 & Top 5 Grup di Slide 2)
        $group_points = DB::table('sr_groups')
            ->leftJoin('sr_point_entries', 'sr_groups.id', '=', 'sr_point_entries.group_id')
            ->leftJoin('users', 'sr_groups.mentor_id', '=', 'users.id')
            ->select(
                'sr_groups.id',
                'sr_groups.nama_grup',
                'sr_groups.warna_grup',
                'users.name as nama_mentor',
                'users.avatar as avatar_mentor',
                DB::raw('COALESCE(SUM(sr_point_entries.poin), 0) as total_poin')
            )
            ->where('sr_groups.status', 'aktif')
            ->groupBy('sr_groups.id', 'sr_groups.nama_grup', 'sr_groups.warna_grup', 'users.name', 'users.avatar')
            ->orderBy('total_poin', 'desc')
            ->get();

        // 3. Data Top 5 Siswa Kontributor
        $top_contributors = DB::table('siswas')
            ->join('sr_point_entries', 'siswas.id', '=', 'sr_point_entries.student_id')
            ->leftJoin('sr_groups', 'sr_point_entries.group_id', '=', 'sr_groups.id')
            ->select('siswas.nama_lengkap', 'sr_groups.nama_grup', DB::raw('SUM(sr_point_entries.poin) as total_poin'))
            ->where('siswas.status', 'Aktif')
            ->whereNull('siswas.deleted_at') // <-- Pengaman Soft Deletes
            ->groupBy('siswas.id', 'siswas.nama_lengkap', 'sr_groups.nama_grup')
            ->having('total_poin', '>', 0)
            ->orderBy('total_poin', 'desc')
            ->limit(5)
            ->get();

        // 4. Data 5 Histori Poin Terbaru (Real-time tracking)
        $recent_points = DB::table('sr_point_entries')
            ->join('siswas', 'sr_point_entries.student_id', '=', 'siswas.id')
            ->leftJoin('sr_groups', 'sr_point_entries.group_id', '=', 'sr_groups.id')
            ->select(
                'siswas.nama_lengkap', 
                'sr_groups.nama_grup', 
                'sr_point_entries.*' 
            )
            ->whereNull('siswas.deleted_at') // <-- Pengaman Soft Deletes
            ->orderBy('sr_point_entries.created_at', 'desc')
            ->limit(5)
            ->get();

        // 5. TAMBAHAN: Tarik Data BEE Smart yang statusnya Aktif
        $activeBeeWeek = BeeWeek::with('vocabs')->where('status', 'aktif')->first();

        return view('student-root.display-setting.tv', compact('setting', 'group_points', 'top_contributors', 'recent_points', 'activeBeeWeek'));
    }
}