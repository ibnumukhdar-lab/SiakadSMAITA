<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;

class SrDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 1. Query Agregator Grup (SUDAH DIPERBAIKI)
        $group_points = DB::table('sr_groups')
            ->leftJoin('sr_point_entries', 'sr_groups.id', '=', 'sr_point_entries.group_id')
            // Tambahkan relasi ke tabel users untuk mengambil data mentor
            ->leftJoin('users', 'sr_groups.mentor_id', '=', 'users.id')
            ->select(
                'sr_groups.id', 
                'sr_groups.nama_grup', 
                'sr_groups.warna_grup', // Tarik warna grup dari database
                'users.name as nama_mentor', // Tarik nama mentor
                'users.avatar as avatar_mentor', // Tarik foto mentor
                DB::raw('COALESCE(SUM(sr_point_entries.poin), 0) as total_poin')
            )
            ->where('sr_groups.status', 'aktif')
            ->groupBy(
                'sr_groups.id', 
                'sr_groups.nama_grup', 
                'sr_groups.warna_grup',
                'users.name',
                'users.avatar'
            )
            ->orderBy('total_poin', 'desc')
            ->get();

        // 2. Query Utama: Rekap Semua Siswa (Digabung dengan tabel poin)
        $query = DB::table('siswas')
            ->leftJoin('sr_point_entries', 'siswas.id', '=', 'sr_point_entries.student_id')
            ->select(
                'siswas.id', 
                'siswas.nisn', 
                'siswas.nama_lengkap', 
                'siswas.kelas', 
                DB::raw('COALESCE(SUM(sr_point_entries.poin), 0) as total_poin')
            )
            ->where('siswas.status', 'Aktif')
            ->whereNull('siswas.deleted_at') // <-- PENGAMAN SOFT DELETES
            ->groupBy('siswas.id', 'siswas.nisn', 'siswas.nama_lengkap', 'siswas.kelas');

        // Fitur Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('siswas.nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('siswas.kelas', 'like', '%' . $search . '%')
                  ->orWhere('siswas.nisn', 'like', '%' . $search . '%');
            });
        }

        // Ambil data dengan Paginasi
        $rekap_siswa = $query->orderBy('total_poin', 'desc')
                             ->orderBy('siswas.nama_lengkap', 'asc')
                             ->paginate(20);

        // 3. Query Top 5 Prestasi (Poin > 0 terbanyak)
        $top_prestasi = DB::table('siswas')
            ->join('sr_point_entries', 'siswas.id', '=', 'sr_point_entries.student_id')
            ->select('siswas.nama_lengkap', 'siswas.kelas', DB::raw('SUM(sr_point_entries.poin) as total_poin'))
            ->where('siswas.status', 'Aktif')
            ->whereNull('siswas.deleted_at') // <-- PENGAMAN SOFT DELETES
            ->groupBy('siswas.id', 'siswas.nama_lengkap', 'siswas.kelas')
            ->having('total_poin', '>', 0)
            ->orderBy('total_poin', 'desc')
            ->limit(5)
            ->get();

        // 4. Query Top 5 Pelanggaran (Poin < 0 paling minus)
        $top_pelanggaran = DB::table('siswas')
            ->join('sr_point_entries', 'siswas.id', '=', 'sr_point_entries.student_id')
            ->select('siswas.nama_lengkap', 'siswas.kelas', DB::raw('SUM(sr_point_entries.poin) as total_poin'))
            ->where('siswas.status', 'Aktif')
            ->whereNull('siswas.deleted_at') // <-- PENGAMAN SOFT DELETES
            ->groupBy('siswas.id', 'siswas.nama_lengkap', 'siswas.kelas')
            ->having('total_poin', '<', 0)
            ->orderBy('total_poin', 'asc')
            ->limit(5)
            ->get();

        return view('student-root.dashboard.index', compact('rekap_siswa', 'top_prestasi', 'top_pelanggaran', 'search', 'group_points'));
    }
}