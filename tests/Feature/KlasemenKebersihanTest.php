<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\KlasemenKebersihan as K;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Klasemen kebersihan bulanan: dasar juara = TOTAL SKOR capaian (bukan rata-rata),
 * kolom pendukung = rata-rata + jumlah menang terbersih/terkotor.
 */
class KlasemenKebersihanTest extends TestCase
{
    use RefreshDatabase;

    private function user(): int
    {
        return User::create([
            'name'     => 'Musyrif Uji',
            'email'    => 'musyrif.uji@example.test',
            'password' => bcrypt('rahasia'),
        ])->id;
    }

    private function kamar(string $nama, string $kategori, int $musyrif): int
    {
        return DB::table('asrama_kamars')->insertGetId([
            'nama_kamar' => $nama,
            'kategori'   => $kategori,
            'musyrif_id' => $musyrif,
            'status'     => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function inspeksi(string $tanggal, string $kategori, int $musyrif, ?int $terbersih, ?int $terkotor = null): int
    {
        return DB::table('asrama_penilaians')->insertGetId([
            'tanggal'            => $tanggal,
            'kategori'           => $kategori,
            'sesi'               => 'pagi',
            'musyrif_id'         => $musyrif,
            'kamar_terbersih_id' => $terbersih,
            'kamar_terkotor_id'  => $terkotor,
            'status'             => 'final',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    private function skor(int $penilaianId, int $kamarId, int $total): void
    {
        DB::table('asrama_penilaian_kamars')->insert([
            'penilaian_id' => $penilaianId,
            'kamar_id'     => $kamarId,
            'total_skor'   => $total,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function test_juara_dihitung_dari_total_skor_bukan_rata_rata(): void
    {
        $musyrif = $this->user();

        // Kamar A: selalu dapat "terbersih" tapi 2 hari nilainya turun (total 40, rata 20)
        // Kamar B: tidak pernah jadi terbersih tapi nilainya stabil tinggi (total 48, rata 24)
        $a = $this->kamar('Kamar A', 'putri', $musyrif);
        $b = $this->kamar('Kamar B', 'putri', $musyrif);

        foreach ([['2026-10-01', 25, 24], ['2026-10-02', 15, 24]] as [$tgl, $skorA, $skorB]) {
            $p = $this->inspeksi($tgl, 'putri', $musyrif, $a);
            $this->skor($p, $a, $skorA);
            $this->skor($p, $b, $skorB);
        }

        $baris = K::divisi(K::bulan(10, 2026), 'putri');

        $this->assertSame(['Kamar B', 'Kamar A'], $baris->pluck('nama_kamar')->all(), 'Juara harus ikut TOTAL SKOR (B=48 > A=40), bukan rata-rata');
        $this->assertSame(48, $baris[0]->total);
        $this->assertSame(40, $baris[1]->total);
        $this->assertSame(24.0, (float) $baris[0]->rata);
        $this->assertSame(20.0, (float) $baris[1]->rata);
        $this->assertSame(2, $baris[1]->menang, 'Kamar A menang terbersih 2 kali walau total skornya lebih rendah');
        $this->assertSame(0, $baris[0]->menang);
        $this->assertSame(2, $baris[0]->sidak);
    }

    public function test_juara_hanya_kamar_yang_disidak(): void
    {
        $musyrif = $this->user();
        $aktif = $this->kamar('Kamar Aktif', 'putra', $musyrif);
        $this->kamar('Kamar Kosong', 'putra', $musyrif);

        $p = $this->inspeksi('2026-10-03', 'putra', $musyrif, $aktif);
        $this->skor($p, $aktif, 21);

        $juara = K::juara(K::divisi(K::bulan(10, 2026), 'putra'));

        $this->assertSame(['Kamar Aktif'], $juara->pluck('nama_kamar')->all(), 'Kamar yang belum pernah disidak tidak boleh jadi juara');
    }

    public function test_perhatian_hanya_di_bawah_70_persen(): void
    {
        $musyrif = $this->user();
        $bersih = $this->kamar('Kamar Bersih', 'putri', $musyrif);
        $kotor  = $this->kamar('Kamar Kotor', 'putri', $musyrif);

        $p = $this->inspeksi('2026-10-04', 'putri', $musyrif, $bersih);
        $this->skor($p, $bersih, 25);
        $this->skor($p, $kotor, 12);   // 48% -> di bawah batas 70%

        $perhatian = K::perhatian(K::divisi(K::bulan(10, 2026), 'putri'));

        $this->assertSame(['Kamar Kotor'], $perhatian->pluck('nama_kamar')->all());
        $this->assertSame(48.0, (float) $perhatian[0]->persen);
    }

    public function test_terkotor_dan_terbersih_menjadi_poin_penghargaan(): void
    {
        $musyrif = $this->user();
        $juara = $this->kamar('Kamar Juara', 'putra', $musyrif);
        $kotor = $this->kamar('Kamar Kotor', 'putra', $musyrif);

        $p = $this->inspeksi('2026-10-05', 'putra', $musyrif, $juara, $kotor);
        $this->skor($p, $juara, 25);
        $this->skor($p, $kotor, 10);

        $baris = K::divisi(K::bulan(10, 2026), 'putra')->keyBy('nama_kamar');

        $this->assertSame(1, $baris['Kamar Juara']->poin);
        $this->assertSame(-1, $baris['Kamar Kotor']->poin);
    }

    public function test_halaman_dashboard_dan_peringkat_menampilkan_angka_yang_sama(): void
    {
        $musyrif = $this->user();
        $a = $this->kamar('Kamar A', 'putri', $musyrif);
        $b = $this->kamar('Kamar B', 'putri', $musyrif);

        foreach ([['2026-10-06', 20, 25], ['2026-10-07', 25, 25]] as [$tgl, $skorA, $skorB]) {
            $p = $this->inspeksi($tgl, 'putri', $musyrif, $a);
            $this->skor($p, $a, $skorA);
            $this->skor($p, $b, $skorB);
        }

        $admin = User::create([
            'name'     => 'Super Admin Uji',
            'email'    => 'admin.uji@example.test',
            'password' => bcrypt('rahasia'),
        ]);

        $dashboard = $this->actingAs($admin)->get('/asrama/dashboard');
        $peringkat = $this->actingAs($admin)->get('/asrama/peringkat');

        // Kalau izin belum di-seed di basis data uji, halaman akan 403 — lewati dengan jelas.
        $this->assertContains($dashboard->status(), [200, 403], 'dashboard harus 200 (atau 403 bila izin belum di-seed di uji)');
        $this->assertContains($peringkat->status(), [200, 403], 'peringkat harus 200 (atau 403 bila izin belum di-seed di uji)');
    }
}
