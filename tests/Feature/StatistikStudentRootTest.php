<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\SrGroup;
use App\Models\SrGroupMember;
use App\Models\SrPointCriteria;
use App\Models\SrPointEntry;
use App\Models\User;
use App\Services\StatistikStudentRoot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * STATISTIK STUDENT ROOT (1 Okt 2026).
 *
 * Yang dijaga di sini sesuai permintaan Kepala Sekolah:
 *   - perbandingan antar bulan (naik/turun poin) + jumlah santri yang dapat plus & minus;
 *   - aktivitas paling banyak dilakukan BESERTA catatannya;
 *   - sebaran kelas/grup dan papan santri;
 *   - panel kualitas data (entri yang catatannya tidak cocok dengan kriteria) — hanya laporan;
 *   - mentor hanya melihat angka grup binaannya, pengawas melihat semua.
 */
class StatistikStudentRootTest extends TestCase
{
    use RefreshDatabase;

    // =================================================================
    //  Alat bantu
    // =================================================================
    private function siapkanPeran(array $peranDenganIzin = []): void
    {
        foreach (['Super Admin', 'Kepala Sekolah', 'Kepala Diniyah', 'Tata Usaha', 'Guru'] as $nama) {
            Role::findOrCreate($nama, 'web');
        }
        foreach (['buka-menu-master-student-root', 'buka-menu-grup-binaan', 'buka-menu-project-sr'] as $izin) {
            Permission::findOrCreate($izin, 'web');
        }
        foreach ($peranDenganIzin as $nama => $izin) {
            Role::findByName($nama, 'web')->syncPermissions($izin);
        }
    }

    private function pengguna(string $nama, string $peran, string $email): User
    {
        $u = User::create(['name' => $nama, 'email' => $email, 'password' => bcrypt('rahasia')]);
        $u->assignRole($peran);

        return $u;
    }

    private function siswa(string $nama, string $kelas = 'X'): Siswa
    {
        return Siswa::create([
            'nama_lengkap' => $nama,
            'nisn' => (string) random_int(1000000000, 1999999999),
            'kelas' => $kelas,
            'jk' => 'Laki-laki',
            'status' => 'Aktif',
        ]);
    }

    private function kriteria(string $nama, int $poin): SrPointCriteria
    {
        return SrPointCriteria::create([
            'kategori' => $poin >= 0 ? 'positif' : 'negatif',
            'nama_perilaku' => $nama,
            'poin' => $poin,
            'tingkat' => 'ringan',
            'status' => 'aktif',
        ]);
    }

    private function entri(Siswa $siswa, SrPointCriteria $kriteria, string $tanggal, ?string $catatan = null, ?int $poin = null): SrPointEntry
    {
        return SrPointEntry::create([
            'student_id' => $siswa->id,
            'criteria_id' => $kriteria->id,
            'poin' => $poin ?? $kriteria->poin,
            'catatan' => $catatan,
            'tanggal_kejadian' => $tanggal,
        ]);
    }

    private function grup(string $nama, User $mentor, array $siswa): SrGroup
    {
        $g = SrGroup::create([
            'nama_grup' => $nama,
            'mentor_id' => $mentor->id,
            'tahun_ajaran_mulai' => '2026/2027',
            'status' => 'aktif',
        ]);

        foreach ($siswa as $s) {
            SrGroupMember::create([
                'group_id' => $g->id,
                'student_id' => $s->id,
                'tanggal_gabung' => '2026-07-01',
            ]);
        }

        return $g;
    }

    // =================================================================
    //  1. Perbandingan antar bulan (naik/turun) + santri plus/minus
    // =================================================================
    public function test_statistik_menghitung_selisih_antar_bulan_dan_santri_plus_minus(): void
    {
        $baik = $this->kriteria('Membantu guru / karyawan / staff', 1);
        $buruk = $this->kriteria('Kamar Terkotor Asrama', -1);

        $a = $this->siswa('Ahmad', 'X');
        $b = $this->siswa('Budi', 'XI');

        // Agustus: 2 entri positif (Ahmad 1, Budi 1) + 1 entri negatif (Budi)
        $this->entri($a, $baik, '2026-08-05');
        $this->entri($b, $baik, '2026-08-06');
        $this->entri($b, $buruk, '2026-08-07');

        // September: 3 positif (Ahmad 2, Budi 1) + 2 negatif (Ahmad, Budi)
        $this->entri($a, $baik, '2026-09-02');
        $this->entri($a, $baik, '2026-09-03');
        $this->entri($b, $baik, '2026-09-04');
        $this->entri($a, $buruk, '2026-09-05');
        $this->entri($b, $buruk, '2026-09-06');

        $d = app(StatistikStudentRoot::class)->susun(['dari' => null, 'sampai' => null]);

        $this->assertCount(2, $d['bulanan'], 'Dua bulan harus muncul.');
        $agu = $d['bulanan'][0];
        $sep = $d['bulanan'][1];

        $this->assertSame('Agustus 2026', $agu['label']);
        $this->assertSame(3, $agu['entri']);
        $this->assertSame(1, $agu['poin']);          // +2 -1
        $this->assertSame(2, $agu['entri_plus']);
        $this->assertSame(1, $agu['entri_min']);
        $this->assertSame(2, $agu['siswa_plus']);
        $this->assertSame(1, $agu['siswa_min']);
        $this->assertNull($agu['delta_poin'], 'Bulan pertama tidak punya pembanding.');

        $this->assertSame('September 2026', $sep['label']);
        $this->assertSame(5, $sep['entri']);
        $this->assertSame(1, $sep['poin']);          // +3 -2
        $this->assertSame(3, $sep['entri_plus']);
        $this->assertSame(2, $sep['entri_min']);
        $this->assertSame(2, $sep['siswa_plus']);
        $this->assertSame(2, $sep['siswa_min']);
        $this->assertSame(0, $sep['delta_poin'], 'September sama dengan Agustus → selisih 0.');
        $this->assertSame(0.0, (float) $sep['delta_persen']);

        // Papan santri: Ahmad +3-1 = +2 ; Budi +2-2 = 0
        $this->assertSame('Ahmad', $d['siswa']['teratas'][0]['nama']);
        $this->assertSame(2, $d['siswa']['teratas'][0]['poin']);
    }

    /** Bulan yang turun harus menghasilkan delta negatif + persen negatif. */
    public function test_bulan_yang_turun_ditandai_delta_negatif(): void
    {
        $baik = $this->kriteria('Melaksanakan sunnah ekstra', 1);
        $buruk = $this->kriteria('Masbuk sholat berjamaah', -1);
        $s = $this->siswa('Citra', 'XII');

        $this->entri($s, $baik, '2026-07-10');
        $this->entri($s, $baik, '2026-07-11');
        $this->entri($s, $baik, '2026-07-12');
        $this->entri($s, $baik, '2026-07-13');       // Juli: +4
        $this->entri($s, $buruk, '2026-08-03');      // Agustus: -1

        $d = app(StatistikStudentRoot::class)->susun([]);
        $agu = $d['bulanan'][1];

        $this->assertSame(-5, $agu['delta_poin']);
        $this->assertSame(-125.0, (float) $agu['delta_persen']);
    }

    // =================================================================
    //  2. Aktivitas terbanyak + catatannya
    // =================================================================
    public function test_aktivitas_terbanyak_menampilkan_catatan_yang_sering_diisi(): void
    {
        $sunnah = $this->kriteria('Melaksanakan sunnah ekstra atas inisiatif sendiri', 1);
        $kasar = $this->kriteria('Berkata kasar', -1);

        $s = $this->siswa('Dedi', 'X');
        $this->entri($s, $sunnah, '2026-09-01', 'Qobliyah subuh');
        $this->entri($s, $sunnah, '2026-09-02', 'Qobliyah subuh');
        $this->entri($s, $sunnah, '2026-09-03', 'qobliyah   subuh');   // spasi ganda + huruf kecil → sama
        $this->entri($s, $sunnah, '2026-09-04', 'Murajaah');
        $this->entri($s, $kasar, '2026-09-05', 'anjir');

        $d = app(StatistikStudentRoot::class)->susun([]);
        $top = $d['aktivitas']['positif'][0];

        $this->assertSame('Melaksanakan sunnah ekstra atas inisiatif sendiri', $top['nama']);
        $this->assertSame(4, $top['jumlah']);
        $this->assertSame(1, $top['siswa']);
        $this->assertSame('Qobliyah subuh', $top['catatan'][0]['teks'], 'Catatan sama dibedakan huruf besar/kecil harus digabung.');
        $this->assertSame(3, $top['catatan'][0]['jumlah']);

        $this->assertSame('Berkata kasar', $d['aktivitas']['negatif'][0]['nama']);
        $this->assertSame('anjir', $d['aktivitas']['negatif'][0]['catatan'][0]['teks']);
        $this->assertSame('Qobliyah subuh', $d['catatan'][0]['teks']);
        $this->assertSame(3, $d['catatan'][0]['jumlah']);
    }

    // =================================================================
    //  3. Kualitas data (hanya laporan)
    // =================================================================
    public function test_panel_kualitas_data_menandai_entri_yang_catatan_tidak_cocok(): void
    {
        $salah = $this->kriteria('Membantu guru / karyawan / staff dengan inisiatif sendiri', 1);
        $benar = $this->kriteria('Kamar Terbersih Asrama', 1);
        $takTerpakai = $this->kriteria('Juara lomba tingkat nasional', 8);

        $s = $this->siswa('Eka', 'XI');
        $this->entri($s, $salah, '2026-09-01', 'Kamar Terbersih Putri Asrama (Mariyah)');
        $this->entri($s, $salah, '2026-09-02', 'Kamar Terbersih Putra Asrama (Gersik)');
        $this->entri($s, $salah, '2026-09-03', null);                 // tanpa catatan
        $this->entri($s, $benar, '2026-09-04', 'Kamar Terbersih Asrama (Mariyah)');  // kriteria sudah benar → tidak dihitung salah

        $d = app(StatistikStudentRoot::class)->susun([]);

        $this->assertSame(2, $d['kualitas']['entri_salah_kriteria']);
        $this->assertSame(1, $d['kualitas']['entri_tanpa_catatan']);
        $namaTakTerpakai = array_column($d['kualitas']['kriteria_tak_terpakai'], 'nama');
        $idTakTerpakai = array_column($d['kualitas']['kriteria_tak_terpakai'], 'id');

        $this->assertContains('Juara lomba tingkat nasional', $namaTakTerpakai);
        $this->assertContains($takTerpakai->id, $idTakTerpakai, 'Kriteria yang belum dipakai harus muncul.');
        $this->assertNotContains($benar->id, $idTakTerpakai, 'Kriteria yang sudah dipakai tidak boleh dianggap belum dipakai.');
        $this->assertNotContains($salah->id, $idTakTerpakai);
    }

    // =================================================================
    //  4. Hak akses: pengawas semua, mentor grup binaannya, lain 403
    // =================================================================
    public function test_halaman_statistik_terbuka_untuk_pengawas_dan_ditolak_untuk_yang_tanpa_izin(): void
    {
        $this->siapkanPeran([
            'Tata Usaha' => ['buka-menu-master-student-root'],
            'Guru' => ['buka-menu-project-sr'],   // izin Student Root lain, TAPI bukan statistik
        ]);

        $s = $this->siswa('Fajar', 'XII');
        $c = $this->kriteria('Membantu guru / karyawan / staff', 1);
        $this->entri($s, $c, '2026-09-10', 'Membantu piket');

        $this->actingAs($this->pengguna('Petugas TU', 'Tata Usaha', 'tu@test.id'))
            ->get(route('sr.statistik'))
            ->assertOk()
            ->assertSee('Statistik Student Root');

        $this->actingAs($this->pengguna('Guru Lain', 'Guru', 'guru@test.id'))
            ->get(route('sr.statistik'))
            ->assertForbidden();
    }

    public function test_mentor_hanya_melihat_angka_grup_binaannya(): void
    {
        $this->siapkanPeran([
            'Guru' => ['buka-menu-grup-binaan'],
            'Tata Usaha' => ['buka-menu-master-student-root'],
        ]);

        $mentor = $this->pengguna('Mentor Biru', 'Guru', 'mentor@test.id');
        $lain = $this->pengguna('Mentor Merah', 'Guru', 'mentor2@test.id');

        $anakSaya = $this->siswa('Gita', 'X');
        $anakLain = $this->siswa('Hasan', 'XI');
        $this->grup('Blue', $mentor, [$anakSaya]);
        $this->grup('Red', $lain, [$anakLain]);

        $baik = $this->kriteria('Aktif dalam kegiatan pembelajaran', 1);
        $this->entri($anakSaya, $baik, '2026-09-01');
        $this->entri($anakSaya, $baik, '2026-09-02');
        $this->entri($anakLain, $baik, '2026-09-03');
        $this->entri($anakLain, $baik, '2026-09-04');
        $this->entri($anakLain, $baik, '2026-09-05');

        $respons = $this->actingAs($mentor)->get(route('sr.statistik'));
        $respons->assertOk();
        $this->assertSame(2, $respons->viewData('ringkasan')['entri'], 'Mentor hanya menghitung entri santri binaannya.');
        $this->assertSame(1, $respons->viewData('jumlah_santri'));
        $this->assertStringNotContainsString('Hasan', $respons->getContent());

        // Mengintip grup lain lewat parameter URL harus ditolak.
        $grupLain = SrGroup::where('nama_grup', 'Red')->value('id');
        $this->actingAs($mentor)->get(route('sr.statistik', ['grup' => $grupLain]))->assertForbidden();

        // Pengawas melihat keduanya.
        $tu = $this->actingAs($this->pengguna('Petugas TU', 'Tata Usaha', 'tu2@test.id'))->get(route('sr.statistik'));
        $tu->assertOk();
        $this->assertSame(5, $tu->viewData('ringkasan')['entri']);
    }

    // =================================================================
    //  5. Ekspor CSV + cetak ber-kop
    // =================================================================
    public function test_ekspor_csv_dan_halaman_cetak_berisi_angka_yang_sama(): void
    {
        $this->siapkanPeran(['Kepala Sekolah' => ['buka-menu-master-student-root']]);

        $baik = $this->kriteria('Melaksanakan sunnah ekstra', 1);
        $buruk = $this->kriteria('Terlambat Kegiatan', -1);
        $s = $this->siswa('Indra', 'XII');
        $this->entri($s, $baik, '2026-08-01', 'Puasa sunnah');
        $this->entri($s, $baik, '2026-09-01', 'Puasa sunnah');
        $this->entri($s, $buruk, '2026-09-02', 'halaqoh sore');

        $kepsek = $this->pengguna('Kepala Sekolah', 'Kepala Sekolah', 'kepsek@test.id');

        $csv = $this->actingAs($kepsek)->get(route('sr.statistik.ekspor'))->getContent();
        $this->assertStringContainsString('PER BULAN', $csv);
        $this->assertStringContainsString('AKTIVITAS POSITIF TERBANYAK', $csv);
        $this->assertStringContainsString('Agustus 2026', $csv);
        $this->assertStringContainsString('Puasa sunnah', $csv);

        $this->actingAs($kepsek)->get(route('sr.statistik.cetak'))
            ->assertOk()
            ->assertSee('SMA IT ARAFAH')
            ->assertSee('Perbandingan antar bulan')
            ->assertSee('Terlambat Kegiatan');
    }

    // =================================================================
    //  6. Tahan saat belum ada data + filter kelas
    // =================================================================
    public function test_halaman_tahan_saat_belum_ada_data_dan_pada_saat_disaring_kelas(): void
    {
        $this->siapkanPeran(['Tata Usaha' => ['buka-menu-master-student-root']]);
        $tu = $this->pengguna('Petugas TU', 'Tata Usaha', 'tu3@test.id');

        $this->actingAs($tu)->get(route('sr.statistik'))
            ->assertOk()
            ->assertSee('Belum ada entri poin pada periode ini');

        $baik = $this->kriteria('Membantu gotong royong', 2);
        $x = $this->siswa('Joko', 'X');
        $xi = $this->siswa('Kiki', 'XI');
        $this->entri($x, $baik, '2026-09-01');
        $this->entri($xi, $baik, '2026-09-02');
        $this->entri($xi, $baik, '2026-09-03');

        $respons = $this->actingAs($tu)->get(route('sr.statistik', ['kelas' => 'X']));
        $respons->assertOk();
        $this->assertSame(1, $respons->viewData('ringkasan')['entri']);
        $this->assertSame(1, $respons->viewData('jumlah_santri'));

        // Penyaring periode "semua data" mengabaikan rentang semester.
        $semua = $this->actingAs($tu)->get(route('sr.statistik', ['semester' => 'semua']));
        $this->assertSame(3, $semua->viewData('ringkasan')['entri']);

        // Periode semester berjalan (s1 = Juli–Desember) memuat September, tidak memuat Juni.
        $this->entri($x, $baik, '2026-06-15');
        $s1 = $this->actingAs($tu)->get(route('sr.statistik', ['semester' => 's1']));
        $this->assertSame(3, $s1->viewData('ringkasan')['entri']);
        $this->assertSame(4, $this->actingAs($tu)->get(route('sr.statistik', ['semester' => 'semua']))->viewData('ringkasan')['entri']);
    }

    /** Entri yang dihapus (soft delete) tidak boleh ikut dihitung. */
    public function test_entri_terhapus_tidak_dihitung(): void
    {
        $c = $this->kriteria('Ikut lomba internal', 1);
        $s = $this->siswa('Laras', 'X');
        $this->entri($s, $c, '2026-09-01');
        $hapus = $this->entri($s, $c, '2026-09-02');
        $hapus->delete();

        $d = app(StatistikStudentRoot::class)->susun([]);
        $this->assertSame(1, $d['ringkasan']['entri']);
        $this->assertSame(0, DB::table('sr_point_entries')->whereNull('deleted_at')->where('tanggal_kejadian', '2026-09-02')->count());
    }
}
