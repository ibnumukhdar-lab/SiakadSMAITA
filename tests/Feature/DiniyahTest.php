<?php

namespace Tests\Feature;

use App\Models\AsramaKamar;
use App\Models\DiniyahAbsensi;
use App\Models\DiniyahMapel;
use App\Models\DiniyahNilai;
use App\Models\DiniyahPeriode;
use App\Models\DiniyahPertemuan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * MODUL DINIYAH (1 Okt 2026): mata pelajaran, nilai, absensi, rapor berbahasa Arab.
 *
 * Yang dijaga di sini sesuai keputusan Kepala Sekolah:
 *   - Kepala Diniyah menetapkan mapel + guru pengampu (dari daftar guru ATAU manual);
 *   - musyrif/musyrifah mengisi nilai & absensi HANYA untuk kamar binaannya;
 *   - guru pengampu boleh mengoreksi nilai mapel yang diampu;
 *   - absensi per pertemuan → rekap hadir/sakit/izin/alpa masuk rapor otomatis;
 *   - rapor berbahasa Arab dengan terjemahan kecil Indonesia, dan wali murid
 *     hanya melihat rapor anaknya sendiri (tanpa tombol cetak).
 */
class DiniyahTest extends TestCase
{
    use RefreshDatabase;

    // =================================================================
    //  Alat bantu data
    // =================================================================

    private function peran(array $peran = []): void
    {
        foreach (['Super Admin', 'Kepala Diniyah', 'Guru', 'Musyrif', 'Tata Usaha', 'Kepala Sekolah'] as $nama) {
            Role::findOrCreate($nama, 'web');
        }

        foreach ([
            'buka-menu-diniyah', 'kelola-mapel-diniyah', 'kelola-periode-diniyah',
            'nilai-diniyah', 'absensi-diniyah', 'cetak-rapor-diniyah',
        ] as $izin) {
            Permission::findOrCreate($izin, 'web');
        }

        foreach ($peran as $nama => $daftar) {
            Role::findByName($nama, 'web')->syncPermissions($daftar);
        }
    }

    private function kepalaDiniyah(): User
    {
        $u = User::create(['name' => 'Kepala Diniyah', 'email' => 'kadini@test.id', 'password' => bcrypt('rahasia')]);
        $u->assignRole('Kepala Diniyah');

        return $u;
    }

    private function musyrif(string $nama = 'Musyrif Uji', string $email = 'musyrif@test.id'): User
    {
        $u = User::create(['name' => $nama, 'email' => $email, 'password' => bcrypt('rahasia')]);
        $u->assignRole('Musyrif');

        return $u;
    }

    private function guruPengampu(): User
    {
        $u = User::create(['name' => 'Ustadz Pengampu', 'email' => 'pengampu@test.id', 'nipa' => 'YMA. 0912 039', 'password' => bcrypt('rahasia')]);
        $u->assignRole('Guru');

        return $u;
    }

    private function siswa(string $nama, string $kelas = 'X', ?string $namaArab = null): Siswa
    {
        return Siswa::create([
            'nama_lengkap' => $nama,
            'nama_arab' => $namaArab,
            'nisn' => (string) random_int(1000000000, 1999999999),
            'kelas' => $kelas,
            'jk' => 'Laki-laki',
            'status' => 'Aktif',
            'ttl' => 'Sampit 1 Januari 2010',
        ]);
    }

    private function kamar(User $musyrif, string $namaKamar = 'Bonang', string $kategori = 'putra'): AsramaKamar
    {
        return AsramaKamar::create([
            'nama_kamar' => $namaKamar,
            'kategori' => $kategori,
            'musyrif_id' => $musyrif->id,
            'kapasitas' => 10,
            'status' => 'aktif',
        ]);
    }

    private function periode(): DiniyahPeriode
    {
        return DiniyahPeriode::create([
            'nama' => 'Semester 1 · 2026/2027',
            'tahun_ajaran' => '2026/2027',
            'semester' => 's1',
            'tahun_hijriah' => '1448 هـ',
            'terbuka' => true,
        ]);
    }

    // =================================================================
    //  1. Mata pelajaran ditetapkan Kepala Diniyah
    // =================================================================

    public function test_kepala_diniyah_menetapkan_mapel_dengan_guru_dari_daftar(): void
    {
        $this->peran(['Kepala Diniyah' => ['buka-menu-diniyah', 'kelola-mapel-diniyah', 'nilai-diniyah', 'cetak-rapor-diniyah']]);
        $guru = $this->guruPengampu();
        $this->siswa('Santri Kelas X');
        $this->siswa('Santri Kelas XI', 'XI');

        $this->actingAs($this->kepalaDiniyah())
            ->post(route('diniyah.mapel.simpan'), [
                'nama' => 'Bahasa Arab — Nahwu',
                'nama_arab' => 'النحو',
                'kelas' => ['X'],
                'guru_id' => $guru->id,
                'jp_pekan' => 2,
            ])
            ->assertRedirect();

        $mapel = DiniyahMapel::first();
        $this->assertNotNull($mapel);
        $this->assertSame('Bahasa Arab — Nahwu', $mapel->nama);
        $this->assertSame('النحو', $mapel->nama_arab);
        $this->assertSame($guru->id, $mapel->guru_id);
        $this->assertSame($guru->name, $mapel->guruTampil());
        $this->assertSame(['X'], $mapel->daftarKelas());

        // Daftar santri ditarik dari kelasnya, bukan diketik ulang.
        $this->assertCount(1, $mapel->santriIds());
    }

    public function test_guru_pengampu_boleh_diketik_manual(): void
    {
        $this->peran(['Kepala Diniyah' => ['buka-menu-diniyah', 'kelola-mapel-diniyah']]);

        $this->actingAs($this->kepalaDiniyah())
            ->post(route('diniyah.mapel.simpan'), [
                'nama' => 'Fiqih Ibadah',
                'kelas' => ['X', 'XI', 'XII'],
                'guru_nama' => 'Ustadz Abdullah',
                'guru_nipa' => 'YMA. 0001 002',
            ])
            ->assertRedirect();

        $mapel = DiniyahMapel::first();
        $this->assertNull($mapel->guru_id);
        $this->assertSame('Ustadz Abdullah', $mapel->guruTampil());
        $this->assertSame('YMA. 0001 002', $mapel->guruNipaTampil());
        $this->assertSame(['X', 'XI', 'XII'], $mapel->daftarKelas());
    }

    public function test_musyrif_tidak_boleh_menetapkan_mapel(): void
    {
        $this->peran(['Musyrif' => ['buka-menu-diniyah', 'nilai-diniyah', 'absensi-diniyah']]);

        $this->actingAs($this->musyrif())
            ->get(route('diniyah.mapel'))
            ->assertForbidden();
    }

    // =================================================================
    //  2. Nilai: musyrif mengisi, guru pengampu mengoreksi
    // =================================================================

    public function test_musyrif_hanya_menilai_santri_kamar_binaannya(): void
    {
        $this->peran(['Musyrif' => ['buka-menu-diniyah', 'nilai-diniyah', 'absensi-diniyah']]);
        $musyrif = $this->musyrif();
        $kamar = $this->kamar($musyrif);
        $periode = $this->periode();

        $milikSendiri = $this->siswa('Santri Binaan Saya', 'X');
        $orangLain = $this->siswa('Santri Kamar Lain', 'X');

        DB::table('asrama_members')->insert([
            ['kamar_id' => $kamar->id, 'student_id' => $milikSendiri->id, 'tanggal_masuk' => now()->toDateString()],
        ]);

        $mapel = DiniyahMapel::create(['nama' => 'Nahwu', 'status' => 'aktif', 'urutan' => 1]);
        DB::table('diniyah_mapel_kelas')->insert(['mapel_id' => $mapel->id, 'kelas' => 'X']);

        $this->actingAs($musyrif)
            ->post(route('diniyah.nilai.simpan'), [
                'periode_id' => $periode->id,
                'mapel_id' => $mapel->id,
                'nilai' => [$milikSendiri->id => 88, $orangLain->id => 95],
            ])
            ->assertRedirect();

        // Nilai santri binaan tersimpan…
        $this->assertDatabaseHas('diniyah_nilai', ['siswa_id' => $milikSendiri->id, 'nilai' => 88, 'penilai_id' => $musyrif->id]);
        // …nilai santri kamar lain DIABAIKAN.
        $this->assertDatabaseMissing('diniyah_nilai', ['siswa_id' => $orangLain->id]);

        // Halaman nilai hanya memuat santri binaannya.
        $this->actingAs($musyrif)->get(route('diniyah.nilai', ['mapel' => $mapel->id]))
            ->assertOk()
            ->assertSee('Santri Binaan Saya')
            ->assertDontSee('Santri Kamar Lain');
    }

    public function test_guru_pengampu_mengoreksi_nilai_dan_tercatat(): void
    {
        $this->peran(['Guru' => ['buka-menu-diniyah', 'nilai-diniyah']]);
        $guru = $this->guruPengampu();
        $periode = $this->periode();
        $santri = $this->siswa('Santri Dinilai', 'X');

        $mapel = DiniyahMapel::create(['nama' => 'Nahwu', 'status' => 'aktif', 'guru_id' => $guru->id]);
        DB::table('diniyah_mapel_kelas')->insert(['mapel_id' => $mapel->id, 'kelas' => 'X']);

        $this->actingAs($guru)
            ->post(route('diniyah.nilai.simpan'), [
                'periode_id' => $periode->id,
                'mapel_id' => $mapel->id,
                'nilai' => [$santri->id => 91],
                'catatan' => [$santri->id => 'Perlu latihan i\'rab'],
            ])
            ->assertRedirect();

        $baris = DiniyahNilai::first();
        $this->assertSame(91, $baris->nilai);
        $this->assertSame($guru->id, $baris->dikoreksi_oleh);
        $this->assertNotNull($baris->dikoreksi_pada);
        $this->assertSame('Perlu latihan i\'rab', $baris->catatan);
    }

    // =================================================================
    //  3. Absensi per pertemuan → rekap otomatis
    // =================================================================

    public function test_absensi_pertemuan_dan_rekap_masuk_rapor(): void
    {
        $this->peran(['Musyrif' => ['buka-menu-diniyah', 'nilai-diniyah', 'absensi-diniyah']]);
        $musyrif = $this->musyrif();
        $kamar = $this->kamar($musyrif);
        $periode = $this->periode();

        $santri = $this->siswa('Santri Absensi', 'X');
        DB::table('asrama_members')->insert(['kamar_id' => $kamar->id, 'student_id' => $santri->id, 'tanggal_masuk' => now()->toDateString()]);

        $mapel = DiniyahMapel::create(['nama' => 'Nahwu', 'status' => 'aktif']);
        DB::table('diniyah_mapel_kelas')->insert(['mapel_id' => $mapel->id, 'kelas' => 'X']);

        // catat pertemuan
        $this->actingAs($musyrif)
            ->post(route('diniyah.absensi.pertemuan'), [
                'mapel_id' => $mapel->id,
                'tanggal' => now()->toDateString(),
                'jam_ke' => 3,
                'materi' => 'Bab Isim & Fi\'il',
            ])
            ->assertRedirect();

        $pertemuan = DiniyahPertemuan::first();
        $this->assertNotNull($pertemuan);
        $this->assertSame('Bab Isim & Fi\'il', $pertemuan->materi);

        // isi kehadiran
        $this->actingAs($musyrif)
            ->post(route('diniyah.absensi.simpan'), [
                'pertemuan_id' => $pertemuan->id,
                'status' => [$santri->id => 'sakit'],
                'keterangan' => [$santri->id => 'demam'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('diniyah_absensi', ['siswa_id' => $santri->id, 'status' => 'sakit', 'keterangan' => 'demam']);

        // rekap terhitung otomatis
        $rekap = DiniyahAbsensi::rekapSiswa($santri->id, $periode->id);
        $this->assertSame(1, $rekap['sakit']);
        $this->assertSame(1, $rekap['total']);

        // pertemuan sama tidak dibuat dua kali (unique mapel+tanggal+jam)
        $this->actingAs($musyrif)->post(route('diniyah.absensi.pertemuan'), [
            'mapel_id' => $mapel->id,
            'tanggal' => now()->toDateString(),
            'jam_ke' => 3,
            'materi' => 'ulangan',
        ]);
        $this->assertSame(1, DiniyahPertemuan::count());
    }

    // =================================================================
    //  4. Rapor berbahasa Arab + terjemahan kecil
    // =================================================================

    public function test_rapor_diniyah_berbahasa_arab_dengan_terjemahan_dan_rekap(): void
    {
        $this->peran(['Super Admin' => ['buka-menu-diniyah', 'cetak-rapor-diniyah']]);
        $periode = $this->periode();
        $santri = $this->siswa('Ahmad Dahlan', 'X', 'أحمد دحلان');

        $mapel = DiniyahMapel::create(['nama' => 'Bahasa Arab — Nahwu', 'nama_arab' => 'النحو', 'status' => 'aktif', 'urutan' => 1]);
        DB::table('diniyah_mapel_kelas')->insert(['mapel_id' => $mapel->id, 'kelas' => 'X']);

        DiniyahNilai::create([
            'periode_id' => $periode->id, 'mapel_id' => $mapel->id, 'siswa_id' => $santri->id,
            'nilai' => 88, 'catatan' => 'Aktif bertanya',
        ]);

        $pertemuan = DiniyahPertemuan::create([
            'periode_id' => $periode->id, 'mapel_id' => $mapel->id,
            'tanggal' => now()->toDateString(), 'jam_ke' => 1,
        ]);
        DiniyahAbsensi::create(['pertemuan_id' => $pertemuan->id, 'siswa_id' => $santri->id, 'status' => 'izin']);

        $superAdmin = User::create(['name' => 'Super Admin', 'email' => 'sa@test.id', 'password' => bcrypt('rahasia')]);
        $superAdmin->assignRole('Super Admin');

        $jawaban = $this->actingAs($superAdmin)->get(route('diniyah.rapor.cetak', ['siswa' => $santri->id]));

        $jawaban->assertOk()
            ->assertSee('كشف الدرجات', false)          // judul Arab
            ->assertSee('النحو', false)                // nama mapel Arab
            ->assertSee('المادة الدراسية', false)      // label Arab
            ->assertSee('Mata pelajaran')              // terjemahan kecil Indonesia
            ->assertSee('أحمد دحلان', false)           // transliterasi nama
            ->assertSee('Ahmad Dahlan')                // nama latin
            ->assertSee('سجلّ الحضور والغياب', false)  // rekap kehadiran
            ->assertSee('Izin');

        // Nilai & predikat + rekap hadir dihitung dari absensi.
        $this->assertStringContainsString('88', $jawaban->getContent());
        $this->assertStringContainsString('جَيِّد جِدًّا', $jawaban->getContent());
    }

    public function test_wali_murid_melihat_rapor_diniyah_anaknya_tanpa_tombol_cetak(): void
    {
        $periode = $this->periode();
        $anak = $this->siswa('Ahmad Dahlan', 'X', 'أحمد دحلان');
        $anak->update(['nisn' => '0093905443']);
        $lain = $this->siswa('Zulfa Anak Lain', 'X');

        $mapel = DiniyahMapel::create(['nama' => 'Bahasa Arab — Nahwu', 'nama_arab' => 'النحو', 'status' => 'aktif']);
        DB::table('diniyah_mapel_kelas')->insert(['mapel_id' => $mapel->id, 'kelas' => 'X']);

        DiniyahNilai::create(['periode_id' => $periode->id, 'mapel_id' => $mapel->id, 'siswa_id' => $anak->id, 'nilai' => 90]);

        $this->post('/ortu/masuk', ['nisn' => '0093905443', 'sandi' => '01012010'])
            ->assertRedirect(route('ortu.dasbor'));

        $this->get(route('ortu.rapor.diniyah'))
            ->assertOk()
            ->assertSee('Ahmad Dahlan')
            ->assertDontSee('Zulfa Anak Lain')
            ->assertSee('كشف الدرجات', false)          // tetap berbahasa Arab
            ->assertDontSee('window.print()')          // wali murid tidak bisa mencetak
            ->assertSee('Untuk dilihat saja');
    }

    public function test_halaman_diniyah_terbuka_untuk_kepala_diniyah(): void
    {
        $this->peran([
            'Kepala Diniyah' => ['buka-menu-diniyah', 'kelola-mapel-diniyah', 'kelola-periode-diniyah', 'nilai-diniyah', 'absensi-diniyah', 'cetak-rapor-diniyah'],
        ]);
        $kepala = $this->kepalaDiniyah();
        $this->periode();
        $this->siswa('Santri Kelas X', 'X');

        foreach ([
            route('diniyah.mapel'),
            route('diniyah.nilai'),
            route('diniyah.absensi'),
            route('diniyah.periode'),
            route('diniyah.rapor'),
        ] as $jalur) {
            $this->actingAs($kepala)->get($jalur)->assertOk();
        }
    }

    public function test_tanpa_izin_tidak_bisa_membuka_modul_diniyah(): void
    {
        $this->peran(['Guru' => ['buka-menu-diniyah']]);
        $guru = $this->guruPengampu();

        // Punya izin masuk menu, tapi belum punya izin menetapkan mapel.
        $this->actingAs($guru)->get(route('diniyah.mapel'))->assertForbidden();
        $this->actingAs($guru)->get(route('diniyah.nilai'))->assertForbidden();
        $this->actingAs($guru)->get(route('diniyah.rapor'))->assertOk();

        // Tanpa izin sama sekali: tidak bisa membuka apa pun.
        $orangLuas = User::create(['name' => 'Tanpa Izin', 'email' => 'kosong@test.id', 'password' => bcrypt('rahasia')]);
        $this->actingAs($orangLuas)->get(route('diniyah.rapor'))->assertForbidden();
    }

    // =================================================================
    //  5. Periode
    // =================================================================

    public function test_hanya_satu_periode_terbuka(): void
    {
        $this->peran(['Kepala Diniyah' => ['buka-menu-diniyah', 'kelola-periode-diniyah']]);
        $kepala = $this->kepalaDiniyah();

        $satu = DiniyahPeriode::create(['nama' => 'Semester 2', 'semester' => 's2', 'terbuka' => true]);
        $dua = DiniyahPeriode::create(['nama' => 'Semester 1', 'semester' => 's1', 'terbuka' => false]);

        $this->actingAs($kepala)->patch(route('diniyah.periode.buka', $dua->id))->assertRedirect();

        $this->assertFalse($satu->fresh()->terbuka);
        $this->assertTrue($dua->fresh()->terbuka);
        $this->assertSame($dua->id, DiniyahPeriode::terbukaSekarang()->id);
    }
}
