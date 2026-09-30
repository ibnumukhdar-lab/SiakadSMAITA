<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * HALAMAN REKAM JEJEK SISWA (30 Sep 2026).
 *
 * Penjaga khusus dibuat setelah bug 1 Okt 2026: layanan RekamJejak memakai
 * DB::table sehingga kolom tanggal_lahir kembali menjadi TEKS, padahal view
 * memanggil ->locale()/->translatedFormat() → halaman 500 begitu santri punya
 * tanggal lahir. Uji ini memastikan santri BER-tanggal lahir tetap tampil.
 */
class RekamJejakTest extends TestCase
{
    use RefreshDatabase;

    private ?User $pengelola = null;

    private function pengelola(): User
    {
        if ($this->pengelola) {
            return $this->pengelola;
        }

        Role::findOrCreate('Super Admin', 'web');
        Permission::findOrCreate('buka-menu-siswa', 'web');

        $u = User::create(['name' => 'Pengelola', 'email' => 'rk@test.id', 'password' => bcrypt('rahasia')]);
        $u->assignRole('Super Admin');

        return $this->pengelola = $u;
    }

    public function test_halaman_rekam_jejak_tampil_walau_santri_punya_tanggal_lahir(): void
    {
        $siswa = Siswa::create([
            'nama_lengkap'  => 'Ahmad Dahlan',
            'nisn'          => '0093905443',
            'kelas'         => 'X',
            'jk'            => 'Laki-laki',
            'status'        => 'Aktif',
            'ttl'           => 'Sampit, 24 Mei 2009',
            'tempat_lahir'  => 'Sampit',
            'tanggal_lahir' => '2009-05-24',
        ]);

        $this->actingAs($this->pengelola())
            ->get(route('siswa.rekamJejak', $siswa->id))
            ->assertOk()
            ->assertSee('Ahmad Dahlan')
            ->assertSee('Sampit')
            ->assertSee('24 Mei 2009');   // tanggal diformat, bukan galat

        // Versi ringkas (untuk WhatsApp) juga harus jalan.
        $this->actingAs($this->pengelola())
            ->get(route('siswa.rekamJejak.ringkas', $siswa->id))
            ->assertOk()
            ->assertSee('REKAM JEJEK SISWA');
    }

    public function test_santri_tanpa_tanggal_lahir_tetap_tampil_dengan_pesan_jujur(): void
    {
        $siswa = Siswa::create([
            'nama_lengkap' => 'Tanpa Tanggal',
            'nisn'         => '0091111111',
            'kelas'        => 'X',
            'status'       => 'Aktif',
        ]);

        $this->actingAs($this->pengelola())
            ->get(route('siswa.rekamJejak', $siswa->id))
            ->assertOk()
            ->assertSee('belum lengkap', false);
    }
}
