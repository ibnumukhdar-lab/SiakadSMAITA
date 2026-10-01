<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',   // Tambahan baru
        'jabatan',  // Tambahan baru
        'no_hp',    // Tambahan baru
        'nipa',     // Nomor Induk Pegawai Arafah (format: YMA. 0000 000)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Rapikan NIPA ke format baku "YMA. 0000 000".
     *
     * Menerima angka apa pun (dengan titik/spasi/strip) -> ambil 7 angka pentingnya.
     * Kosong => null (NIPA boleh belum diisi). Kalau jumlah angkanya tidak 7,
     * nilai dikembalikan apa adanya (huruf besar, dirapikan spasinya) supaya tetap bisa disimpan.
     */
    public static function rapikanNipa(?string $nilai): ?string
    {
        $teks = trim((string) $nilai);

        if ($teks === '') {
            return null;
        }

        $angka = preg_replace('/\D/', '', $teks);

        if (strlen($angka) === 7) {
            return 'YMA. ' . substr($angka, 0, 4) . ' ' . substr($angka, 4);
        }

        return preg_replace('/\s+/', ' ', strtoupper($teks));
    }

    /** NIPA siap tampil (garis bila belum diisi). */
    public function getNipaTampilAttribute(): string
    {
        return $this->nipa ?: '—';
    }

    /** Ambil 7 angka NIPA saja (untuk input yang diformat otomatis). */
    public function getNipaAngkaAttribute(): string
    {
        return preg_replace('/\D/', '', (string) $this->nipa);
    }

    /**
     * DINIYAH (1 Okt 2026) — apakah pengguna ini boleh menilai/melihat SELURUH
     * kelas diniyah, bukan hanya santri kamar binaannya?
     * Yang boleh: Kepala Diniyah, Super Admin, Kepala Sekolah, Tata Usaha, dan
     * siapa pun yang memegang izin `kelola-mapel-diniyah` / `kelola-periode-diniyah`.
     * Musyrif/musyrifah TIDAK termasuk — mereka hanya santri kamar binaannya.
     */
    public function bolehSemuaDiniyah(): bool
    {
        if ($this->hasAnyRole(['Super Admin', 'Kepala Diniyah', 'Kepala Sekolah', 'Tata Usaha'])) {
            return true;
        }

        return $this->can('kelola-mapel-diniyah') || $this->can('kelola-periode-diniyah');
    }

    /** Apakah pengguna ini guru pengampu salah satu mapel diniyah? */
    public function mapelDiniyahDiampu()
    {
        return \App\Models\DiniyahMapel::where('guru_id', $this->id)->orderBy('urutan')->get();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}