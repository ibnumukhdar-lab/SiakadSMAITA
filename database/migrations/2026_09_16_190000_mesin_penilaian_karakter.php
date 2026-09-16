<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * MESIN PENILAIAN KARAKTER (Tahap A: Adab & Keasramaan).
 *
 * Semua tabel BARU — tidak menyentuh `siswas`, `asrama_members`, `sr_group_members`,
 * maupun `sr_point_entries`. Jadi kamar asrama, grup binaan, dan poin sikap tetap utuh.
 *
 * Bentuk penilaian: kuesioner skala Likert 1-5 per pertanyaan per siswa.
 *   persentase = (jumlah skor / (jumlah pertanyaan x 5)) x 100
 *
 * Adab dinilai DUA pihak (Kepala Diniyah & Penanggungjawab Asrama) → sesi terpisah,
 * nilai akhir = rata-rata. Keasramaan dinilai penanggungjawab asrama/musyrif.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Master pertanyaan (bisa diubah lewat menu Master Penilaian)
        if (! Schema::hasTable('penilaian_kriteria')) {
            Schema::create('penilaian_kriteria', function (Blueprint $table) {
                $table->id();
                $table->enum('jenis', ['adab', 'keasramaan']);
                $table->unsignedInteger('urutan')->default(0);
                $table->string('pertanyaan', 255);
                $table->string('keterangan', 255)->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                $table->unique(['jenis', 'pertanyaan'], 'penilaian_kriteria_unik');
            });
        }

        // 2. Periode penilaian (mis. Semester 1 2026/2027) — satu yang aktif
        if (! Schema::hasTable('penilaian_periode')) {
            Schema::create('penilaian_periode', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 60)->unique();
                $table->string('tahun_ajaran', 20)->nullable();
                $table->date('tanggal_awal')->nullable();
                $table->date('tanggal_akhir')->nullable();
                $table->boolean('aktif')->default(false);
                $table->string('keterangan', 150)->nullable();
                $table->timestamps();
            });
        }

        // 3. Sesi penilaian = satu lembar penilaian dari satu penilai untuk satu periode
        if (! Schema::hasTable('penilaian_sesi')) {
            Schema::create('penilaian_sesi', function (Blueprint $table) {
                $table->id();
                $table->enum('jenis', ['adab', 'keasramaan']);
                $table->unsignedBigInteger('periode_id')->index();
                $table->unsignedBigInteger('penilai_id')->index();
                $table->string('penilai_peran', 40)->nullable();   // snapshot peran saat menilai
                $table->string('sasaran', 60)->nullable();          // mis. "Semua siswa" / "Kamar A"
                $table->enum('status', ['draft', 'final'])->default('draft');
                $table->text('catatan')->nullable();
                $table->timestamp('difinalkan_pada')->nullable();
                $table->timestamps();
                $table->unique(['jenis', 'periode_id', 'penilai_id'], 'penilaian_sesi_unik');
            });
        }

        // 4. Jawaban per siswa per pertanyaan (skor 1-5)
        if (! Schema::hasTable('penilaian_jawaban')) {
            Schema::create('penilaian_jawaban', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sesi_id')->index();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->unsignedBigInteger('kriteria_id')->index();
                $table->unsignedTinyInteger('skor');
                $table->timestamps();
                $table->unique(['sesi_id', 'siswa_id', 'kriteria_id'], 'penilaian_jawaban_unik');
            });
        }

        // 5. Pengaturan sederhana (ambang predikat) — key/value, tidak menyentuh tabel lama
        if (! Schema::hasTable('penilaian_pengaturan')) {
            Schema::create('penilaian_pengaturan', function (Blueprint $table) {
                $table->string('kunci', 40)->primary();
                $table->string('nilai', 100)->nullable();
                $table->timestamps();
            });
        }

        // ---- Isian awal ----
        $kriteriaAdab = [
            'Adab kepada guru & ustadz (salam, sopan, menghormati)',
            'Adab kepada orang tua & keluarga',
            'Adab kepada teman (tidak menyakiti, tidak menggunjing)',
            'Adab berbicara (jujur, tidak kasar, tidak memotong pembicaraan)',
            'Kedisiplinan ibadah (sholat berjamaah, tilawah, doa harian)',
            'Kerapian penampilan & kebersihan diri',
            'Tanggung jawab terhadap tugas & amanah',
            'Kejujuran & integritas',
        ];
        $kriteriaAsrama = [
            'Kebersihan & kerapian kamar',
            'Kedisiplinan waktu (bangun, sholat, tidur)',
            'Kerjasama & kekeluargaan antar penghuni kamar',
            'Kepatuhan pada peraturan asrama',
            'Kemandirian (mencuci, merapikan, mengurus diri)',
            'Adab makan & menjaga kebersihan lingkungan',
            'Keterlibatan kegiatan asrama (piket, halaqah, kebersamaan)',
            'Kesehatan & kebugaran (olahraga, tidak begadang)',
        ];

        foreach ([['adab', $kriteriaAdab], ['keasramaan', $kriteriaAsrama]] as [$jenis, $daftar]) {
            foreach ($daftar as $i => $pertanyaan) {
                DB::table('penilaian_kriteria')->updateOrInsert(
                    ['jenis' => $jenis, 'pertanyaan' => $pertanyaan],
                    ['urutan' => $i + 1, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Periode awal mengikuti tahun ajaran aktif pada data siswa.
        if (DB::table('penilaian_periode')->count() === 0) {
            $tahun = DB::table('tahun_ajaran')->where('aktif', true)->value('nama')
                ?: (date('Y') . '/' . (date('Y') + 1));

            DB::table('penilaian_periode')->insert([
                'nama' => 'Semester 1 ' . $tahun,
                'tahun_ajaran' => $tahun,
                'tanggal_awal' => null,
                'tanggal_akhir' => null,
                'aktif' => true,
                'keterangan' => 'Periode awal dibuat otomatis saat pemasangan modul penilaian.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ambang predikat (bisa diubah di menu Master Penilaian)
        foreach (['predikat_a' => '90', 'predikat_b' => '80', 'predikat_c' => '70'] as $kunci => $nilai) {
            DB::table('penilaian_pengaturan')->updateOrInsert(
                ['kunci' => $kunci],
                ['nilai' => $nilai, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ---- Izin & peran ----
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $izin = [
            'buka-menu-penilaian',        // lihat menu + rekap
            'nilai-adab',                 // mengisi penilaian adab
            'nilai-keasramaan',           // mengisi penilaian keasramaan
            'kelola-master-penilaian',    // master pertanyaan, periode, ambang predikat
        ];
        foreach ($izin as $nama) {
            Permission::firstOrCreate(['name' => $nama]);
        }

        $peta = [
            'Kepala Diniyah' => ['buka-menu-penilaian', 'nilai-adab', 'nilai-keasramaan'],
            'Musyrif' => ['buka-menu-penilaian', 'nilai-adab', 'nilai-keasramaan'],
            'Tata Usaha' => ['buka-menu-penilaian', 'kelola-master-penilaian'],
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
        Schema::dropIfExists('penilaian_jawaban');
        Schema::dropIfExists('penilaian_sesi');
        Schema::dropIfExists('penilaian_kriteria');
        Schema::dropIfExists('penilaian_periode');
        Schema::dropIfExists('penilaian_pengaturan');
    }
};
