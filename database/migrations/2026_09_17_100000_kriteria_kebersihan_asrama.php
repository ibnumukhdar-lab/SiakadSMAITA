<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Kriteria poin kebersihan asrama yang BENAR.
 *
 * Sebelum ini, injeksi poin finalisasi sidak gagal menemukan kriteria yang cocok
 * (kolom 'kriteria' yang dicari tidak ada) sehingga semua entri poin asrama
 * menempel ke kriteria pertama — "Membantu guru / karyawan / staff dengan
 * inisiatif sendiri" (+1) — termasuk hukuman kamar terkotor.
 *
 * Migrasi ini HANYA menambahkan kriteria baru untuk pemakaian ke depan.
 * Entri poin lama SENGAJA tidak diubah (permintaan Fahri: biarkan, perbaikan
 * data lama = urusan admin/TU).
 */
return new class extends Migration
{
    private array $kriteria = [
        [
            'nama_perilaku' => 'Kamar Terbersih Asrama',
            'kategori'      => 'positif',
            'poin'          => 1,
            'deskripsi'     => 'Poin otomatis bagi seluruh penghuni kamar terbersih hasil finalisasi inspeksi kebersihan asrama (sidak harian).',
        ],
        [
            'nama_perilaku' => 'Kamar Terkotor Asrama',
            'kategori'      => 'negatif',
            'poin'          => -1,
            'deskripsi'     => 'Poin otomatis bagi seluruh penghuni kamar terkotor hasil finalisasi inspeksi kebersihan asrama (sidak harian).',
        ],
    ];

    public function up(): void
    {
        foreach ($this->kriteria as $k) {
            $sudahAda = DB::table('sr_point_criteria')
                ->where('nama_perilaku', $k['nama_perilaku'])
                ->where('kategori', $k['kategori'])
                ->where('poin', $k['poin'])
                ->exists();

            if ($sudahAda) {
                continue;
            }

            DB::table('sr_point_criteria')->insert([
                'id'            => Str::uuid()->toString(),
                'nama_perilaku' => $k['nama_perilaku'],
                'kategori'      => $k['kategori'],
                'deskripsi'     => $k['deskripsi'],
                'poin'          => $k['poin'],
                'tingkat'       => null,
                'status'        => 'aktif',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Hapus hanya bila belum pernah dipakai oleh entri poin mana pun.
        $ids = DB::table('sr_point_criteria')
            ->whereIn('nama_perilaku', array_column($this->kriteria, 'nama_perilaku'))
            ->pluck('id');

        foreach ($ids as $id) {
            if (DB::table('sr_point_entries')->where('criteria_id', $id)->exists()) {
                continue;
            }

            DB::table('sr_point_criteria')->where('id', $id)->delete();
        }
    }
};
