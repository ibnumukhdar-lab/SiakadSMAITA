<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Project Student Root milik satu grup binaan.
 *
 * Jumlah project bebas: mentor menambah project sesuai kebutuhan grupnya. Setiap project
 * dinilai dalam 5 tahap (Observasi → Perencanaan → Perancangan → Validasi Ahli → Presentasi Publik),
 * nilai diisi per siswa (0-100) dan dirata-ratakan berbobot menjadi nilai akhir project untuk anak itu.
 */
class SrProject extends Model
{
    protected $table = 'sr_projects';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'aktif' => 'boolean',
    ];

    public const STATUS = [
        'rencana' => 'Rencana',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
    ];

    public function grup()
    {
        return $this->belongsTo(SrGroup::class, 'grup_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function statusTahap()
    {
        return $this->hasMany(SrProjectTahapStatus::class, 'project_id');
    }

    public function nilai()
    {
        return $this->hasMany(SrProjectNilai::class, 'project_id');
    }

    /** Anggota grup yang masih aktif (urutan tetap, tanpa siswa terhapus). */
    public function anggota()
    {
        $ids = DB::table('sr_group_members')
            ->where('group_id', $this->grup_id)
            ->whereNull('tanggal_keluar')
            ->pluck('student_id')
            ->all();

        if (empty($ids)) {
            return collect();
        }

        return Siswa::whereIn('id', $ids)->orderBy('nama_lengkap')->get();
    }

    /** [tahap_id => baris status] untuk project ini. */
    public function petaStatusTahap(): array
    {
        return $this->statusTahap()->get()->keyBy('tahap_id')->toArray();
    }

    /** Peta bobot tahap yang dipakai project ini (tahap 'tidak_dipakai' dikecualikan). */
    public function bobotTerpakai(): array
    {
        $status = $this->petaStatusTahap();
        $peta = [];

        foreach (SrProjectTahap::daftarAktif() as $tahap) {
            if (($status[$tahap->id]['status'] ?? 'belum') === 'tidak_dipakai') {
                continue;
            }
            $peta[$tahap->id] = (float) $tahap->bobot;
        }

        // Kalau semua bobot 0, pakai bobot rata.
        if (array_sum($peta) <= 0 && $peta) {
            $peta = array_map(fn () => 1.0, $peta);
        }

        return $peta;
    }

    /** Nilai akhir (berbobot) dari daftar [tahap_id => skor]. */
    public function hitungRata(array $skorPerTahap): ?float
    {
        $bobot = $this->bobotTerpakai();
        $totalBobot = 0.0;
        $jumlah = 0.0;

        foreach ($skorPerTahap as $tahapId => $skor) {
            if ($skor === null || ! isset($bobot[$tahapId])) {
                continue;
            }
            $totalBobot += $bobot[$tahapId];
            $jumlah += ((float) $skor) * $bobot[$tahapId];
        }

        return $totalBobot > 0 ? round($jumlah / $totalBobot, 2) : null;
    }

    /** Nilai per siswa: [siswa_id => ['per_tahap' => [...], 'rata' => ?, 'predikat' => ?, 'terisi' => n, 'catatan' => [...]]] */
    public function nilaiPerSiswa(): array
    {
        $tahapAktif = SrProjectTahap::daftarAktif()->pluck('id')->all();
        $hasil = [];

        foreach ($this->nilai()->get() as $n) {
            if ($n->skor === null) {
                continue;
            }
            $hasil[$n->siswa_id]['per_tahap'][$n->tahap_id] = (int) $n->skor;
            if ($n->catatan) {
                $hasil[$n->siswa_id]['catatan'][$n->tahap_id] = $n->catatan;
            }
        }

        foreach ($hasil as $siswaId => $data) {
            $rata = $this->hitungRata($data['per_tahap'] ?? []);
            $hasil[$siswaId]['rata'] = $rata;
            $hasil[$siswaId]['predikat'] = PenilaianPengaturan::predikat($rata);
            $hasil[$siswaId]['terisi'] = count($data['per_tahap'] ?? []);
            $hasil[$siswaId]['lengkap'] = count($data['per_tahap'] ?? []) >= count($this->bobotTerpakai() ?: $tahapAktif);
        }

        return $hasil;
    }

    /** Rata-rata nilai akhir seluruh anggota yang sudah dinilai (nilai project sebagai kelompok). */
    public function rataProject(): ?float
    {
        $angka = [];
        foreach ($this->nilaiPerSiswa() as $data) {
            if (($data['rata'] ?? null) !== null) {
                $angka[] = $data['rata'];
            }
        }

        return $angka ? round(array_sum($angka) / count($angka), 2) : null;
    }

    /** Tahap yang sudah selesai / total tahap yang dipakai (mis. "3/5"). */
    public function progresTahap(): string
    {
        $status = $this->petaStatusTahap();
        $dipakai = 0;
        $selesai = 0;

        foreach ($this->bobotTerpakai() as $tahapId => $b) {
            $dipakai++;
            if (($status[$tahapId]['status'] ?? 'belum') === 'selesai') {
                $selesai++;
            }
        }

        return $selesai . '/' . $dipakai;
    }

    public function scopeMilikMentor($query, $userId)
    {
        $grupIds = SrGroup::where('mentor_id', $userId)->pluck('id')->all();

        return $query->whereIn('grup_id', $grupIds ?: ['-']);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
