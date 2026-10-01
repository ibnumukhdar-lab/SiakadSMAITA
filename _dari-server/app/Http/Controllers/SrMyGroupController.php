<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SrGroup;
use App\Models\SrPointCriteria;
use App\Models\SrPointEntry; // Ditambahkan untuk akses riwayat poin

class SrMyGroupController extends Controller
{
    public function index()
    {
        // 1. Cari grup di mana user yang sedang login menjadi mentornya
        $group = SrGroup::where('mentor_id', Auth::id())->where('status', 'aktif')->first();

        // Siapkan variabel default jika grup belum ada
        $members = collect([]);
        $totalPoinGrup = 0;
        $trendPoin = 0;
        $riwayatTerbaru = collect([]);

        if ($group) {
            // 2. Ambil data siswa yang berstatus aktif di grup tersebut beserta rekap poinnya
            $members = DB::table('sr_group_members')
                ->join('siswas', 'sr_group_members.student_id', '=', 'siswas.id')
                ->leftJoin('sr_point_entries', 'siswas.id', '=', 'sr_point_entries.student_id')
                ->select(
                    'siswas.id',
                    'siswas.nisn',
                    'siswas.nama_lengkap',
                    'siswas.kelas',
                    'siswas.foto', 
                    'sr_group_members.tanggal_gabung',
                    DB::raw('COALESCE(SUM(sr_point_entries.poin), 0) as total_poin')
                )
                ->where('sr_group_members.group_id', $group->id)
                ->whereNull('sr_group_members.tanggal_keluar') // Siswa belum dikeluarkan dari grup
                ->where('siswas.status', 'Aktif') // Siswa masih aktif bersekolah
                ->whereNull('siswas.deleted_at') // <-- PENGAMAN 1: Sembunyikan siswa yang sudah dihapus
                ->groupBy('siswas.id', 'siswas.nisn', 'siswas.nama_lengkap', 'siswas.kelas', 'siswas.foto', 'sr_group_members.tanggal_gabung')
                ->orderBy('total_poin', 'desc')
                ->get();

            // Ekstrak ID siswa ke dalam array untuk keperluan filter history
            $memberIds = $members->pluck('id')->toArray();

            // 3. Hitung Total Poin Grup Keseluruhan
            $totalPoinGrup = $members->sum('total_poin');

            // 4. Kalkulasi Tren Poin & Riwayat Terbaru (Hanya jika ada anggota)
            if (!empty($memberIds)) {
                // PENGAMAN 2: Gunakan withoutGlobalScopes() untuk memaksa menarik data dari GURU MANAPUN
                
                // Poin 7 hari terakhir
                $mingguIni = SrPointEntry::withoutGlobalScopes()
                    ->whereIn('student_id', $memberIds)
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->sum('poin');

                // Poin 8-14 hari yang lalu
                $mingguLalu = SrPointEntry::withoutGlobalScopes()
                    ->whereIn('student_id', $memberIds)
                    ->whereBetween('created_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])
                    ->sum('poin');

                // Selisih trend
                $trendPoin = $mingguIni - $mingguLalu;

                // 5. Ambil 15 Riwayat Aktivitas Terbaru Lintas Guru
                $riwayatTerbaru = SrPointEntry::withoutGlobalScopes()
                    ->with(['student', 'criteria'])
                    ->whereIn('student_id', $memberIds)
                    ->orderBy('created_at', 'desc')
                    ->take(15)
                    ->get();
            }
        }

        // 6. Kembalikan ke view persis sesuai dengan struktur Anda
        return view('student-root.grup-binaan.index', compact(
            'group', 'members', 'totalPoinGrup', 'trendPoin', 'riwayatTerbaru'
        ));
    }

    /**
     * Menghapus riwayat poin dan menghitung ulang total poin siswa.
     */
    public function destroy($id)
    {
        try {
            // 1. Cari data riwayat poin yang ingin dihapus
            $riwayat = SrPointEntry::findOrFail($id);
            
            // Simpan ID siswa sebelum datanya benar-benar terhapus dari sistem
            $studentId = $riwayat->student_id;

            // 2. Eksekusi hapus data riwayat
            //    CATATAN 2026-09: tidak ada kolom cache total_poin di siswas — semua rekap
            //    menghitung SUM live, jadi cukup hapus entri poinnya.
            $riwayat->delete();

            // 3. Kembalikan halaman dengan notifikasi sukses
            return back()->with('success', 'Riwayat aktivitas berhasil dihapus.');

        } catch (\Exception $e) {
            // Jika terjadi kegagalan sistem
            return back()->with('error', 'Gagal menghapus riwayat poin: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui riwayat poin dan menghitung ulang total poin siswa.
     */
    /**
     * Perbarui satu riwayat poin.
     *
     * SEJAK 17 Sep 2026: penilaian poin TIDAK bisa diketik manual lagi — petugas memilih
     * JENIS PERILAKU (kriteria) dan poin otomatis mengikuti master kriteria. Ini mencegah
     * kasus lama: poin -1 ditempelkan pada kriteria positif "Membantu guru..." sehingga
     * laporan/rapor menulis pelanggaran bernama perilaku positif.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'criteria_id' => 'required|exists:sr_point_criteria,id',
            'catatan' => 'nullable|string|max:500',
            'tanggal_kejadian' => 'nullable|date',
        ], [], [
            'criteria_id' => 'jenis perilaku',
        ]);

        try {
            // 1. Temukan datanya
            $riwayat = SrPointEntry::findOrFail($id);
            $kriteria = SrPointCriteria::findOrFail($data['criteria_id']);

            // 2. Poin mengikuti master kriteria (bukan ketikan petugas)
            $riwayat->criteria_id = $kriteria->id;
            $riwayat->poin = $kriteria->poin;
            $riwayat->catatan = $data['catatan'] ?? null;
            if (! empty($data['tanggal_kejadian'])) {
                $riwayat->tanggal_kejadian = $data['tanggal_kejadian'];
            }
            $riwayat->save();

            // 3. CATATAN 2026-09: total poin siswa TIDAK disimpan sebagai kolom cache
            //    (SUM live), jadi tidak ada update kolom total_poin di sini.
            return back()->with('success', 'Riwayat poin diperbarui — poin (' . ($kriteria->poin > 0 ? '+' . $kriteria->poin : $kriteria->poin) . ') otomatis mengikuti jenis perilaku yang dipilih.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}