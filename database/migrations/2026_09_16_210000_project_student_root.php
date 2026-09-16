<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * PENILAIAN PROJECT STUDENT ROOT (5 tahap).
 *
 * Bentuk penilaian yang dipilih:
 *  - Tiap project punya 5 tahap: Observasi, Perencanaan, Perancangan, Validasi Ahli, Presentasi Publik.
 *  - Status tahap di level PROJECT (belum / berjalan / selesai / tidak dipakai) untuk memantau progres,
 *    dan tahap "tidak dipakai" otomatis dikeluarkan dari perhitungan bobot (bobot dinormalisasi ulang).
 *  - Nilai diisi per SISWA per TAHAP (0-100), jadi setiap anak punya nilai sendiri — bukan hanya nilai kelompok.
 *  - Nilai akhir project per siswa = rata-rata berbobot tahap yang terisi.
 *  - Jumlah project BEBAS: mentor menambah project sesuai kebutuhan grupnya.
 *
 * Semua tabel BARU (prefix sr_project_*). Tabel `sr_groups`, `sr_group_members`, dan
 * `sr_point_entries` tidak disentuh, jadi grup binaan dan poin sikap tetap utuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Master tahap penilaian (nama, urutan, bobot, panduan indikator)
        if (! Schema::hasTable('sr_project_tahap')) {
            Schema::create('sr_project_tahap', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 60);
                $table->unsignedInteger('urutan')->default(0);
                $table->decimal('bobot', 5, 2)->default(0);
                $table->text('panduan')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        // 2. Project milik grup Student Root (jumlah bebas per grup)
        if (! Schema::hasTable('sr_projects')) {
            Schema::create('sr_projects', function (Blueprint $table) {
                $table->id();
                $table->string('grup_id', 36)->index();          // -> sr_groups.id (uuid)
                $table->string('nama', 120);
                $table->text('deskripsi')->nullable();           // tema / tujuan project
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->enum('status', ['rencana', 'berjalan', 'selesai'])->default('rencana');
                $table->unsignedBigInteger('mentor_id')->nullable()->index();
                $table->unsignedBigInteger('dibuat_oleh')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        // 3. Status tiap tahap di level project (progres, tanggal, catatan)
        if (! Schema::hasTable('sr_project_tahap_status')) {
            Schema::create('sr_project_tahap_status', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('tahap_id')->index();
                $table->enum('status', ['belum', 'berjalan', 'selesai', 'tidak_dipakai'])->default('belum');
                $table->date('tanggal')->nullable();
                $table->string('catatan', 255)->nullable();
                $table->timestamps();
                $table->unique(['project_id', 'tahap_id'], 'sr_tahap_status_unik');
            });
        }

        // 4. Nilai per siswa per tahap per project
        if (! Schema::hasTable('sr_project_nilai')) {
            Schema::create('sr_project_nilai', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('tahap_id')->index();
                $table->unsignedTinyInteger('skor')->nullable();   // 0-100
                $table->string('catatan', 255)->nullable();
                $table->unsignedBigInteger('dinilai_oleh')->nullable();
                $table->timestamps();
                $table->unique(['project_id', 'siswa_id', 'tahap_id'], 'sr_project_nilai_unik');
            });
        }

        // ---- Isian awal: 5 tahap + panduan indikator ----
        $tahap = [
            ['observasi', 'Observasi', 1, 15, 'Mengamati masalah/kebutuhan nyata: ketepatan sasaran, kelengkapan data awal, kejelasan latar belakang masalah.'],
            ['perencanaan', 'Perencanaan', 2, 20, 'Menyusun rencana: tujuan, sasaran, langkah kerja, pembagian tugas, dan jadwal — dinilai dari kejelasan dan kelayakannya.'],
            ['perancangan', 'Perancangan', 3, 25, 'Membuat karya/rancangan: kualitas desain, kelengkapan komponen, kreativitas, dan kesesuaian dengan rencana.'],
            ['validasi_ahli', 'Validasi Ahli', 4, 20, 'Menghadapi uji ahli/pembimbing: kemampuan menjelaskan, menerima masukan, dan memperbaiki karya.'],
            ['presentasi_publik', 'Presentasi Publik', 5, 20, 'Menyajikan hasil kepada publik: kejelasan penyampaian, kerja sama tim, penguasaan materi, dan dokumentasi.'],
        ];

        foreach ($tahap as [$kode, $nama, $urutan, $bobot, $panduan]) {
            DB::table('sr_project_tahap')->updateOrInsert(
                ['kode' => $kode],
                ['nama' => $nama, 'urutan' => $urutan, 'bobot' => $bobot, 'panduan' => $panduan, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ---- Izin ----
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['buka-menu-project-sr', 'kelola-project-sr', 'nilai-project-sr', 'kelola-master-project-sr'] as $nama) {
            Permission::firstOrCreate(['name' => $nama]);
        }

        $peta = [
            'Guru' => ['buka-menu-project-sr', 'kelola-project-sr', 'nilai-project-sr'],
            'Tata Usaha' => ['buka-menu-project-sr', 'kelola-master-project-sr'],
        ];
        foreach ($peta as $namaRole => $daftarIzin) {
            $role = Role::where('name', $namaRole)->first();
            if ($role) {
                $role->givePermissionTo($daftarIzin);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_project_nilai');
        Schema::dropIfExists('sr_project_tahap_status');
        Schema::dropIfExists('sr_projects');
        Schema::dropIfExists('sr_project_tahap');
    }
};
