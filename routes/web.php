<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArsipSuratController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\DB;        // <-- Ditambahkan untuk Route Sinkronisasi
use Illuminate\Support\Facades\Schema;  // <-- Ditambahkan untuk Route Sinkronisasi

Route::get('/', function () {
    return view('welcome');
});

// --- RUTE TV DISPLAY (AKSES PUBLIK / TANPA LOGIN) ---
Route::get('student-root/tv-display', [App\Http\Controllers\SrDisplaySettingController::class, 'showTv'])->name('sr.display.tv');

// --- RUTE BUKU SAKU DIGITAL BEE SMART & KLAIM POIN (AKSES PUBLIK SISWA) ---
Route::get('/buku-saku-bahasa', [App\Http\Controllers\BeeSmartController::class, 'bukuSaku'])->name('bee.buku-saku');
Route::get('/buku-saku-bahasa/{id}', [App\Http\Controllers\BeeSmartController::class, 'bukuSakuShow'])->name('bee.buku-saku.show');
Route::post('/buku-saku-bahasa/{id}/claim', [App\Http\Controllers\BeeSmartController::class, 'claimPoint'])->name('bee.buku-saku.claim');

// --- RUTE DASHBOARD UTAMA (IKHTISAR) ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- RUTE PROFIL PUBLIK ---
Route::get('/siswa/profil/{nisn}', [SiswaController::class, 'showPublic'])->name('siswa.public');

// --- RUTE VERIFIKASI ARSIP PUBLIK ---
Route::get('/arsip/{id}', [ArsipSuratController::class, 'show'])->name('arsip.show')->where('id', '[0-9]+'); 

// --- SEMUA RUTE DENGAN LOGIN (AUTH UMUM) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rute untuk kembali ke akun asli (Leave Impersonate)
    Route::get('/impersonate/leave', [App\Http\Controllers\KelolaAkunController::class, 'leaveImpersonate'])->name('impersonate.leave');
});

// --- 1. MODUL E-ARSIP SEKOLAH ---
Route::middleware(['auth', 'permission:buka-menu-arsip'])->group(function () {
    Route::delete('/arsip/bulk', [ArsipSuratController::class, 'destroyBulk'])->name('arsip.destroyBulk');
    Route::post('/arsip/bulk-export', [ArsipSuratController::class, 'exportBulk'])->name('arsip.exportBulk');
    Route::get('/arsip', [ArsipSuratController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/tambah', [ArsipSuratController::class, 'create'])->name('arsip.create');
    Route::post('/arsip', [ArsipSuratController::class, 'store'])->name('arsip.store');
    Route::get('/arsip/{id}/edit', [ArsipSuratController::class, 'edit'])->name('arsip.edit')->where('id', '[0-9]+');
    Route::put('/arsip/{id}', [ArsipSuratController::class, 'update'])->name('arsip.update')->where('id', '[0-9]+');
    Route::delete('/arsip/{id}', [ArsipSuratController::class, 'destroy'])->name('arsip.destroy')->where('id', '[0-9]+');
});

// --- 2. MODUL PENGATURAN WEB ---
Route::middleware(['auth', 'permission:buka-menu-pengaturan'])->group(function () {
    Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
});

// --- 3. MODUL DATA SISWA ---
Route::middleware(['auth', 'permission:buka-menu-siswa'])->group(function () {
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/tambah', [SiswaController::class, 'create'])->name('siswa.create');
    Route::post('/siswa/tambah', [SiswaController::class, 'store'])->name('siswa.store');
    Route::get('/siswa/import', [SiswaController::class, 'importForm'])->name('siswa.import');
    Route::post('/siswa/import', [SiswaController::class, 'prosesImport'])->name('siswa.prosesImport');
    Route::get('/siswa/import/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.downloadTemplate');
    Route::post('/siswa/bulk-action', [SiswaController::class, 'bulkAction'])->name('siswa.bulk_action');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit')->where('id', '[0-9]+');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update')->where('id', '[0-9]+');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy')->where('id', '[0-9]+');
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show')->where('id', '[0-9]+');
});

// --- 4. MODUL KELOLA AKUN ---
Route::middleware(['auth', 'permission:buka-menu-kelola-akun'])->group(function () {
    Route::get('/kelola-akun', [App\Http\Controllers\KelolaAkunController::class, 'index'])->name('kelola-akun.index');
    Route::put('/kelola-akun/user/{id}', [App\Http\Controllers\KelolaAkunController::class, 'updateUser'])->name('kelola-akun.updateUser');
    
    // --- TAMBAHAN RUTE BULK ACTION ---
    Route::put('/kelola-akun/bulk-user', [App\Http\Controllers\KelolaAkunController::class, 'bulkUpdateUser'])->name('kelola-akun.bulkUpdateUser');
    
    Route::put('/kelola-akun/matrix', [App\Http\Controllers\KelolaAkunController::class, 'updateMatrix'])->name('kelola-akun.updateMatrix');
    Route::post('/kelola-akun/permission', [App\Http\Controllers\KelolaAkunController::class, 'storePermission'])->name('kelola-akun.storePermission');
    
    // --- FITUR IMPERSONATE (Hanya untuk yang bisa buka kelola akun) ---
    Route::get('/impersonate/{id}', [App\Http\Controllers\KelolaAkunController::class, 'impersonate'])->name('impersonate');
});


// =====================================================================
// TRIK BYPASS STORAGE GANDA (MEMPERBAIKI SEMUA GAMBAR LAMA & BARU)
// =====================================================================

// Melayani file storage publik via /berkas/... (satu-satunya prefix yang dipakai aplikasi).
// CATATAN 2026-09: framework Laravel 12 punya rute internal '/storage/{path}' (storage.local)
// yang menimpa & mem-403 rute aplikasi => jangan pernah definisikan rute /storage sendiri;
// semua tautan file memakai /berkas/ (lihat layouts/*, profile, view lama).
// PENGAMAN: tolak path traversal ('..'); pastikan hasil realpath benar-benar
// berada di dalam storage/app/public sebelum file disajikan.
$serveStorageFile = function (string $path) {
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    $base = realpath(storage_path('app/public'));
    $full = realpath($base . DIRECTORY_SEPARATOR . $path);

    if ($base === false || $full === false) {
        abort(404);
    }
    if ($full !== $base && !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
        abort(404);
    }
    if (!is_file($full)) {
        abort(404);
    }

    return response()->file($full);
};

Route::get('/berkas/{path}', $serveStorageFile)->where('path', '.*');

// =====================================================================


// --- RUTE UTILITAS & SETUP MATRIKS (KHUSUS SUPER ADMIN) ---
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
Route::get('/setup-modul', function() {
    $moduls = ['buka-menu-arsip', 'buka-menu-siswa', 'buka-menu-pengaturan', 'buka-menu-kelola-akun'];
    foreach($moduls as $m) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $m]);
    }
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    return 'BERHASIL! 4 Modul Utama sudah disiapkan ke dalam Matriks. Silakan refresh halaman Kelola Akun Anda.';
});

// --- RUTE UTILITAS (Pembersih Cache) ---
Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return 'Cache berhasil dibersihkan! Silakan tekan tombol kembali (back) dan coba lagi fiturnya.';
});

// --- RUTE UTILITAS (Sinkronisasi & Pembersih Data Yatim) ---
Route::get('/sinkron-database', function() {
    try {
        // 0. Ambil ID siswa yang benar-benar aktif (Abaikan yang kena Soft Deletes)
        $querySiswaAktif = DB::table('siswas');
        if (Schema::hasColumn('siswas', 'deleted_at')) {
            $querySiswaAktif->whereNull('deleted_at');
        }
        $validStudentIds = $querySiswaAktif->pluck('id');

        // 1. HAPUS DATA YATIM DI TABEL POIN
        $hapusYatimPoin = DB::table('sr_point_entries')
            ->whereNotIn('student_id', $validStudentIds)
            ->delete();

        // 2. HAPUS DATA YATIM DI TABEL ANGGOTA GRUP
        $hapusYatimGrup = 0;
        if (Schema::hasTable('sr_group_members')) {
            $hapusYatimGrup = DB::table('sr_group_members')
                ->whereNotIn('student_id', $validStudentIds)
                ->delete();
        }

        // 3. PINDAHKAN POIN LAMA KE GRUP YANG BARU (Update Sinkronisasi)
        //    CATATAN 2026-09: kolom cache total_poin (siswas/sr_groups) TIDAK dipakai lagi —
        //    semua perhitungan memakai SUM live, jadi tidak ada update kolom hantu di sini.
        $updateGrup = 0;
        if (Schema::hasColumn('sr_point_entries', 'group_id')) {
            $anggotaGrup = DB::table('sr_group_members')->whereNull('tanggal_keluar')->get();
                
            foreach($anggotaGrup as $anggota) {
                $affected = DB::table('sr_point_entries')
                    ->where('student_id', $anggota->student_id)
                    ->where(function($q) use ($anggota) {
                        $q->whereNull('group_id')
                          ->orWhere('group_id', '!=', $anggota->group_id);
                    })
                    ->update(['group_id' => $anggota->group_id]);
                $updateGrup += $affected;
            }
        }

        return "
            <div style='font-family: sans-serif; padding: 40px; max-width: 600px; margin: 0 auto; line-height: 1.6;'>
                <h2 style='color: #16a34a;'>✅ SINKRONISASI DATABASE BERHASIL!</h2>
                <ul style='font-size: 16px; color: #334155; background: #f8fafc; padding: 20px 40px; border-radius: 10px;'>
                    <li style='margin-bottom: 10px;'>🗑️ <b>$hapusYatimPoin</b> riwayat poin dari siswa yang terhapus berhasil dibersihkan.</li>
                    <li style='margin-bottom: 10px;'>🧹 <b>$hapusYatimGrup</b> data anggota grup usang berhasil dihapus.</li>
                    <li style='margin-bottom: 10px;'>🔄 <b>$updateGrup</b> riwayat poin lama disinkronkan ke grup saat ini.</li>
                </ul>
                <a href='/' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1e3a8a; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Kembali ke Aplikasi</a>
            </div>
        ";
                
    } catch (\Exception $e) {
        return "Terjadi Kesalahan: " . $e->getMessage();
    }
});
});

// ==========================================
// MODUL STUDENT ROOT
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Halaman 1: Form Input Poin Sikap (Bisa diakses semua role yang login)
    Route::get('student-root/input-poin', [App\Http\Controllers\SrPointEntryController::class, 'create'])->name('sr.poin.create');
    Route::post('student-root/input-poin', [App\Http\Controllers\SrPointEntryController::class, 'store'])->name('sr.poin.store');
    
    // Rute Histori Poin Siswa (Untuk tombol Lihat History)
    Route::get('student-root/poin/history/{id}', [App\Http\Controllers\SrPointEntryController::class, 'history'])->name('sr.poin.history');

    // Halaman 2: Dashboard Karakter (Rekap Poin)
    Route::get('student-root/dashboard', [App\Http\Controllers\SrDashboardController::class, 'index'])->name('sr.dashboard');
    
    // Halaman 3: Manajemen Master Kriteria Poin (Terkunci oleh Matriks Akses)
    Route::resource('student-root/kriteria', App\Http\Controllers\SrPointCriteriaController::class)
        ->names('sr.kriteria')
        ->except(['create', 'show', 'edit', 'destroy'])
        ->middleware('permission:buka-menu-master-student-root');

    // Halaman 5: Manajemen Grup & Anggota (Terkunci oleh Matriks Akses)
    Route::resource('student-root/grup', App\Http\Controllers\SrGroupController::class)
        ->names('sr.grup')
        ->except(['create', 'edit'])
        ->middleware('permission:buka-menu-master-student-root');
        
    // Sub-rute untuk Menambah & Mengeluarkan Anggota Grup
    Route::post('student-root/grup/{id}/tambah-anggota', [App\Http\Controllers\SrGroupController::class, 'addMember'])
        ->name('sr.grup.addMember')
        ->middleware('permission:buka-menu-master-student-root');
    Route::put('student-root/grup/keluarkan-anggota/{member_id}', [App\Http\Controllers\SrGroupController::class, 'removeMember'])
        ->name('sr.grup.removeMember')
        ->middleware('permission:buka-menu-master-student-root');
        
    // Halaman 4: Grup Binaan Saya (Khusus Guru Mentor)
    Route::get('student-root/grup-binaan', [App\Http\Controllers\SrMyGroupController::class, 'index'])
        ->name('sr.mygroup')
        ->middleware('permission:buka-menu-grup-binaan');
    
    // --- Pengaturan Display TV ---
    Route::get('student-root/pengaturan-display', [App\Http\Controllers\SrDisplaySettingController::class, 'edit'])->name('sr.display.setting');
    Route::put('student-root/pengaturan-display', [App\Http\Controllers\SrDisplaySettingController::class, 'update'])->name('sr.display.update');
    
    // --- Rute Edit dan Hapus Poin ---
    Route::delete('/poin/{id}', [App\Http\Controllers\SrMyGroupController::class, 'destroy'])->name('poin.destroy');
    Route::put('/poin/{id}', [App\Http\Controllers\SrMyGroupController::class, 'update'])->name('poin.update');
    
    // ==========================================
    // MODUL ASRAMA (permission sesuai matriks Kelola Akun:
    // buka-menu-asrama = Musyrif & Kepala Diniyah;
    // buka-menu-manajemen-kamar = Kepala Diniyah saja)
    // ==========================================
    Route::prefix('asrama')->name('asrama.')->middleware('permission:buka-menu-asrama')->group(function () {
        
        // --- Dashboard Asrama ---
        Route::get('dashboard', [App\Http\Controllers\AsramaDashboardController::class, 'index'])->name('dashboard');

        // 0. Kamar Binaan Saya (Khusus Musyrif)
        Route::get('kamar-binaan', [App\Http\Controllers\AsramaKamarController::class, 'kamarBinaan'])->name('kamar.binaan');

        // 1. Manajemen Kamar (hanya pemegang buka-menu-manajemen-kamar)
        Route::resource('kamar', App\Http\Controllers\AsramaKamarController::class)
            ->middleware('permission:buka-menu-manajemen-kamar');
        Route::post('kamar/{id}/add-member', [App\Http\Controllers\AsramaKamarController::class, 'addMember'])
            ->name('kamar.addMember')->middleware('permission:buka-menu-manajemen-kamar');
        Route::put('kamar/member/{id}/remove', [App\Http\Controllers\AsramaKamarController::class, 'removeMember'])
            ->name('kamar.removeMember')->middleware('permission:buka-menu-manajemen-kamar');

        // 2. Penilaian Harian (Inspeksi)
        Route::get('penilaian', [App\Http\Controllers\AsramaPenilaianController::class, 'index'])->name('penilaian.index'); // Histori Penilaian
        Route::get('penilaian/hari-ini', [App\Http\Controllers\AsramaPenilaianController::class, 'hariIni'])->name('penilaian.hariIni'); // Halaman Sidak
        
        // --- Rute Reset Penilaian Hari Ini ---
        Route::post('penilaian/reset', [App\Http\Controllers\AsramaPenilaianController::class, 'reset'])->name('penilaian.reset');
        
        // --- Rute Konfirmasi Sebelum Finalisasi ---
        Route::get('penilaian/konfirmasi', [App\Http\Controllers\AsramaPenilaianController::class, 'konfirmasi'])->name('penilaian.konfirmasi'); 
        
        Route::post('penilaian/simpan-skor/{kamar_id}', [App\Http\Controllers\AsramaPenilaianController::class, 'simpanSkor'])->name('penilaian.simpanSkor'); 
        Route::post('penilaian/finalisasi', [App\Http\Controllers\AsramaPenilaianController::class, 'finalisasi'])->name('penilaian.finalisasi'); // Tombol Magic Eksekusi
        
        Route::post('penilaian/update-foto/{id}', [\App\Http\Controllers\AsramaPenilaianController::class, 'updateFoto'])->name('penilaian.update-foto');
    });

    // ==========================================
    // MODUL BEE SMART (BAHASA)
    // ==========================================
    Route::prefix('bee-smart')->name('bee.')->group(function () {
        Route::get('/', [App\Http\Controllers\BeeSmartController::class, 'index'])->name('index');
        Route::post('/minggu-baru', [App\Http\Controllers\BeeSmartController::class, 'storeWeek'])->name('storeWeek');
        Route::put('/status/{id}', [App\Http\Controllers\BeeSmartController::class, 'updateStatus'])->name('updateStatus');
        
        // --- EDIT JUDUL & HAPUS MODUL ---
        Route::put('/minggu/{id}', [App\Http\Controllers\BeeSmartController::class, 'updateWeek'])->name('updateWeek');
        Route::delete('/minggu/{id}', [App\Http\Controllers\BeeSmartController::class, 'destroyWeek'])->name('destroyWeek');
        
        // Rute Kelola & Input Kata
        Route::get('/kelola/{id}', [App\Http\Controllers\BeeSmartController::class, 'manage'])->name('manage');
        Route::post('/kelola/{id}/tambah', [App\Http\Controllers\BeeSmartController::class, 'storeVocab'])->name('storeVocab');
        Route::delete('/kosakata/{id}', [App\Http\Controllers\BeeSmartController::class, 'destroyVocab'])->name('destroyVocab');
        
        // --- Edit Teks Kosakata ---
        Route::put('/kosakata/{id}', [App\Http\Controllers\BeeSmartController::class, 'updateVocab'])->name('updateVocab');

        // --- Mode Presentasi Kelas (Interaktif) ---
        Route::get('/classroom', [App\Http\Controllers\BeeSmartController::class, 'classroom'])->name('classroom');
    });

});

require __DIR__.'/auth.php';