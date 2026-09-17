<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Catatan rapor per santri per periode (18 Sep 2026).
 *
 * Dipakai dua rapor:
 *  - 'adab'         → Catatan Musyrif / Pembina pada rapor Adab & Keasramaan (kunci: penilaian_periode_id)
 *  - 'student_root' → Catatan Mentor pada rapor Student Root (kunci: tahun_ajaran + semester)
 */
class CatatanRaport extends Model
{
    protected $table = 'catatan_rapot';

    protected $fillable = [
        'jenis', 'siswa_id', 'penilaian_periode_id', 'tahun_ajaran', 'semester', 'isi', 'penulis_id',
    ];

    public const JENIS = [
        'adab' => 'Adab & Keasramaan',
        'student_root' => 'Student Root',
    ];

    /** Panjang maksimal catatan (huruf). */
    public const MAKS = 600;

    public function penulis()
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Tahun ajaran + semester berjalan (patokan sama dengan rapor Student Root:
     * Juli–Desember = semester 1, Januari–Juni = semester 2).
     *
     * @return array{0: string, 1: string} [tahun ajaran, semester]
     */
    public static function periodeSrBerjalan(): array
    {
        $tahun = (int) now()->format('Y');
        $awalTahunAjaran = (int) now()->format('n') >= 7 ? $tahun : $tahun - 1;

        return ["{$awalTahunAjaran}/" . ($awalTahunAjaran + 1), $awalTahunAjaran === $tahun ? 's1' : 's2'];
    }

    /** Label semester untuk tampilan: "Semester 1 · TA 2026/2027". */
    public static function labelSr(?string $tahunAjaran, ?string $semester): string
    {
        $nama = $semester === 's2' ? 'Semester 2 · Januari–Juni' : 'Semester 1 · Juli–Desember';

        return trim($nama . ($tahunAjaran ? ' · TA ' . $tahunAjaran : ''));
    }

    // =====================================================================
    //  Hak mengisi catatan (dipakai controller & halaman isian)
    // =====================================================================

    /** Divisi seorang musyrif: 'putra'/'putri' dari kategori kamar binaannya (null = belum terpetakan). */
    public static function divisiMusyrif(int $userId): ?string
    {
        $kategori = \App\Models\AsramaKamar::where('musyrif_id', $userId)
            ->where('status', 'aktif')
            ->distinct()
            ->pluck('kategori')
            ->all();

        return count($kategori) === 1 ? $kategori[0] : null;
    }

    /** Divisi seorang santri: dari kamar asrama yang ia tempati sekarang. */
    public static function divisiSiswa(int $siswaId): ?string
    {
        $kategori = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->where('m.student_id', $siswaId)
            ->whereNull('m.tanggal_keluar')
            ->value('k.kategori');

        return $kategori ?: null;
    }

    /** Boleh mengisi catatan Adab? Super Admin, Kepala Diniyah, atau musyrif pada DIVISI santri itu. */
    public static function bolehTulisAdab(?User $user, ?Siswa $siswa): bool
    {
        if (! $user || ! $siswa) {
            return false;
        }

        if ($user->hasRole('Super Admin') || $user->hasRole('Kepala Diniyah')) {
            return true;
        }

        if (! $user->can('nilai-adab') && ! $user->can('nilai-keasramaan')) {
            return false;
        }

        $divisi = self::divisiMusyrif((int) $user->id);

        return $divisi !== null && $divisi === self::divisiSiswa((int) $siswa->id);
    }

    /** Boleh mengisi catatan Student Root? Super Admin atau mentor grup santri itu. */
    public static function bolehTulisSr(?User $user, ?Siswa $siswa): bool
    {
        if (! $user || ! $siswa) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $mentorId = DB::table('sr_group_members as m')
            ->join('sr_groups as g', 'g.id', '=', 'm.group_id')
            ->where('m.student_id', $siswa->id)
            ->whereNull('m.tanggal_keluar')
            ->value('g.mentor_id');

        return $mentorId && (int) $mentorId === (int) $user->id;
    }

    /** Catatan Adab per siswa untuk satu periode penilaian (keyBy siswa_id). */
    public static function untukAdab(array $siswaIds, ?int $periodeId)
    {
        if (empty($siswaIds) || ! $periodeId) {
            return collect();
        }

        return static::with('penulis')
            ->where('jenis', 'adab')
            ->where('penilaian_periode_id', $periodeId)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');
    }

    /** Catatan Student Root per siswa untuk satu semester (keyBy siswa_id). */
    public static function untukSr(array $siswaIds, ?string $tahunAjaran, ?string $semester)
    {
        if (empty($siswaIds) || ! $tahunAjaran || ! $semester) {
            return collect();
        }

        return static::with('penulis')
            ->where('jenis', 'student_root')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');
    }
}
