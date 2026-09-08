<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArsipSurat; // Memanggil model e-Arsip
use App\Models\Siswa;      // Memanggil model Siswa

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Menghitung statistik surat 
        $total_surat  = ArsipSurat::count();
        $surat_masuk  = ArsipSurat::where('jenis_surat', 'Surat Masuk')->count();
        $surat_keluar = ArsipSurat::where('jenis_surat', 'Surat Keluar')->count();
        
        // 2. Menghitung total keseluruhan data siswa
        $total_siswa = Siswa::count();

        // 3. Menghitung rincian demografi siswa berdasarkan kelas
        $siswa_x   = Siswa::where('kelas', 'X')->count();
        $siswa_xi  = Siswa::where('kelas', 'XI')->count();
        $siswa_xii = Siswa::where('kelas', 'XII')->count();
        
        // Nah, bagian ini yang kita perbaiki! Mencari "Lulus" di kolom "kelas"
        $alumni    = Siswa::where('kelas', 'Lulus')->count();

        // 4. Mengirim semua hasil hitungan ke halaman dashboard
        return view('dashboard', compact(
            'total_surat', 'surat_masuk', 'surat_keluar', 
            'total_siswa', 'siswa_x', 'siswa_xi', 'siswa_xii', 'alumni'
        ));
    }
}