<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * MODUL DINIYAH (1 Okt 2026) — mata pelajaran, nilai, absensi, rapor berbahasa Arab.
 * =====================================================================
 * Keputusan pemilik sekolah (30 Sep 2026):
 *   1. Kepala Diniyah menetapkan mata pelajaran per kelas + guru pengampu
 *      (dipilih dari `users` atau diketik manual).
 *   2. Nilai diisi musyrif/musyrifah; guru pengampu boleh MENGOREKSI.
 *   3. Satu nilai akhir per mapel per semester (paling ringkas).
 *   4. Absensi dicatat PER PERTEMUAN kajian (tanggal + mapel + jam ke);
 *      rekap hadir/sakit/izin/alpa masuk rapor otomatis.
 *   5. Rapor berbahasa Arab dengan terjemahan kecil bahasa Indonesia di bawah
 *      tiap label; nama Latin punya kolom transliterasi Arab.
 *
 * Tabel semuanya berawalan `diniyah_` — tidak menyentuh modul lama
 * (Student Root, Adab & Keasramaan, asrama, penilaian).
 *
 * Aman diulang: setiap pembuatan dicek dengan hasTable/hasColumn.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. PERIODE (semester) — dibuka/ditutup Kepala Diniyah
        if (! Schema::hasTable('diniyah_periode')) {
            Schema::create('diniyah_periode', function (Blueprint $table) {
                $table->id();
                $table->string('nama');                      // mis. "Semester 1 · 2026/2027"
                $table->string('tahun_ajaran')->nullable();
                $table->string('semester', 2)->default('s1'); // s1 | s2
                $table->string('tahun_hijriah')->nullable();  // mis. "1448 هـ"
                $table->boolean('terbuka')->default(false);
                $table->foreignId('dibuka_oleh')->nullable();
                $table->timestamp('dibuka_pada')->nullable();
                $table->foreignId('ditutup_oleh')->nullable();
                $table->timestamp('ditutup_pada')->nullable();
                $table->timestamps();
            });
        }

        // 2. MATA PELAJARAN
        if (! Schema::hasTable('diniyah_mapels')) {
            Schema::create('diniyah_mapels', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('nama_arab')->nullable();       // untuk rapor
                $table->unsignedTinyInteger('jp_pekan')->nullable();
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->foreignId('guru_id')->nullable();      // dari database guru (users)
                $table->string('guru_nama')->nullable();       // atau diketik manual
                $table->string('guru_nipa')->nullable();
                $table->string('status', 12)->default('aktif');
                $table->foreignId('dibuat_oleh')->nullable();
                $table->timestamps();
                $table->index(['status', 'urutan']);
            });
        }

        // 3. MAPEL ↔ KELAS (satu mapel bisa untuk beberapa kelas)
        if (! Schema::hasTable('diniyah_mapel_kelas')) {
            Schema::create('diniyah_mapel_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mapel_id');
                $table->string('kelas', 20);
                $table->timestamps();
                $table->unique(['mapel_id', 'kelas']);
            });
        }

        // 4. PERTEMUAN (dasar absensi)
        if (! Schema::hasTable('diniyah_pertemuan')) {
            Schema::create('diniyah_pertemuan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periode_id')->nullable();
                $table->foreignId('mapel_id');
                $table->date('tanggal');
                $table->unsignedTinyInteger('jam_ke')->nullable();
                $table->string('materi')->nullable();
                $table->string('guru_pengampu')->nullable();
                $table->foreignId('dicatat_oleh')->nullable();
                $table->timestamps();
                $table->unique(['mapel_id', 'tanggal', 'jam_ke']);
                $table->index(['periode_id', 'tanggal']);
            });
        }

        // 5. ABSENSI per santri
        if (! Schema::hasTable('diniyah_absensi')) {
            Schema::create('diniyah_absensi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pertemuan_id');
                $table->foreignId('siswa_id');
                $table->string('status', 12)->default('hadir'); // hadir|sakit|izin|alpa
                $table->string('keterangan')->nullable();
                $table->timestamps();
                $table->unique(['pertemuan_id', 'siswa_id']);
                $table->index(['siswa_id', 'status']);
            });
        }

        // 6. NILAI akhir per mapel per periode (boleh dikoreksi guru pengampu)
        if (! Schema::hasTable('diniyah_nilai')) {
            Schema::create('diniyah_nilai', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periode_id');
                $table->foreignId('mapel_id');
                $table->foreignId('siswa_id');
                $table->unsignedTinyInteger('nilai')->nullable();  // 0-100
                $table->string('catatan')->nullable();
                $table->foreignId('penilai_id')->nullable();        // musyrif/musyrifah
                $table->foreignId('dikoreksi_oleh')->nullable();     // guru pengampu
                $table->timestamp('dikoreksi_pada')->nullable();
                $table->timestamps();
                $table->unique(['periode_id', 'mapel_id', 'siswa_id']);
            });
        }

        // 7. Transliterasi nama Arab di data induk siswa (dipakai rapor diniyah)
        if (! Schema::hasColumn('siswas', 'nama_arab')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('nama_arab', 150)->nullable()->after('nama_lengkap');
            });
        }

        // 8. IZIN & PEMBAGIAN PERAN
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $izin = [
            'buka-menu-diniyah',    // melihat halaman diniyah
            'kelola-mapel-diniyah', // menetapkan mapel + guru pengampu
            'kelola-periode-diniyah', // membuka/menutup semester
            'nilai-diniyah',        // mengisi & mengoreksi nilai
            'absensi-diniyah',      // mencatat absensi pertemuan
            'cetak-rapor-diniyah',  // mencetak rapor (petugas)
        ];

        foreach ($izin as $nama) {
            Permission::firstOrCreate(['name' => $nama, 'guard_name' => 'web']);
        }

        $pembagian = [
            // Kepala Diniyah: semua (pemilik modul)
            'Kepala Diniyah' => $izin,
            // Musyrif/musyrifah: mengisi nilai & absensi
            'Musyrif' => ['buka-menu-diniyah', 'nilai-diniyah', 'absensi-diniyah'],
            // Pengawas & administrasi: melihat + mencetak
            'Super Admin' => $izin,
            'Tata Usaha' => ['buka-menu-diniyah', 'cetak-rapor-diniyah'],
            'Kepala Sekolah' => ['buka-menu-diniyah', 'cetak-rapor-diniyah'],
        ];

        foreach ($pembagian as $namaPeran => $daftar) {
            $role = Role::where('name', $namaPeran)->first();

            if (! $role) {
                continue;
            }

            foreach ($daftar as $namaIzin) {
                if (! $role->hasPermissionTo($namaIzin)) {
                    $role->givePermissionTo($namaIzin);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['buka-menu-diniyah', 'kelola-mapel-diniyah', 'kelola-periode-diniyah', 'nilai-diniyah', 'absensi-diniyah', 'cetak-rapor-diniyah'] as $nama) {
            Permission::where('name', $nama)->delete();
        }

        if (Schema::hasColumn('siswas', 'nama_arab')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('nama_arab');
            });
        }

        Schema::dropIfExists('diniyah_nilai');
        Schema::dropIfExists('diniyah_absensi');
        Schema::dropIfExists('diniyah_pertemuan');
        Schema::dropIfExists('diniyah_mapel_kelas');
        Schema::dropIfExists('diniyah_mapels');
        Schema::dropIfExists('diniyah_periode');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
