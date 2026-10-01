<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SrGroup;
use App\Models\SrGroupMember;
use App\Models\Siswa;
use App\Models\User;

class SrGroupController extends Controller
{
    // Menampilkan Halaman Daftar Grup
    public function index()
    {
        // Ambil semua grup beserta data mentor dan hitung jumlah anggotanya yang masih aktif (belum keluar)
        $groups = SrGroup::with('mentor')->withCount(['members' => function ($query) {
            $query->whereNull('tanggal_keluar');
        }])->orderBy('created_at', 'desc')->get();

        // Ambil semua user dengan Role 'Guru' untuk pilihan dropdown mentor
        $teachers = User::role('Guru')->get();

        return view('student-root.grup.index', compact('groups', 'teachers'));
    }

    // Menyimpan Grup Baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_grup' => 'required|string|max:255',
            'mentor_id' => 'required|exists:users,id|unique:sr_groups,mentor_id', // Validasi: 1 Guru = 1 Grup
            'tahun_ajaran_mulai' => 'required|string|max:20',
        ], [
            'mentor_id.unique' => 'Guru ini sudah menjadi mentor di grup lain! Silakan pilih guru yang berbeda.'
        ]);

        SrGroup::create([
            'nama_grup' => $request->nama_grup,
            'mentor_id' => $request->mentor_id,
            'tahun_ajaran_mulai' => $request->tahun_ajaran_mulai,
            'status' => 'aktif',
        ]);

        return back()->with('success', '✅ Grup binaan baru berhasil dibentuk!');
    }

    // Memperbarui Info Grup
    public function update(Request $request, $id)
    {
        $group = SrGroup::findOrFail($id);

        $request->validate([
            'nama_grup' => 'required|string|max:255',
            // Pengecualian unique untuk ID grup ini sendiri agar bisa di-save tanpa error
            'mentor_id' => 'required|exists:users,id|unique:sr_groups,mentor_id,' . $id, 
            'tahun_ajaran_mulai' => 'required|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'mentor_id.unique' => 'Guru ini sudah menjadi mentor di grup lain! Silakan pilih guru yang berbeda.'
        ]);

        $mentorLama = $group->mentor_id;

        $group->update($request->only(['nama_grup', 'mentor_id', 'tahun_ajaran_mulai', 'status']));

        // Project milik grup ini mengikuti mentor grupnya — supaya tidak ada project yang
        // masih tercatat di bawah mentor lama (mentor project = mentor grup, selalu konsisten).
        if ((int) $mentorLama !== (int) $group->mentor_id) {
            \App\Models\SrProject::where('grup_id', $group->id)->update(['mentor_id' => $group->mentor_id]);
        }

        return back()->with('success', '✅ Informasi grup berhasil diperbarui!');
    }

    // Menghapus Grup
    public function destroy($id)
    {
        $group = SrGroup::findOrFail($id);
        $group->delete();
        
        return back()->with('success', '🗑️ Grup berhasil dihapus beserta seluruh relasinya.');
    }

    // --- BAGIAN KELOLA ANGGOTA SISWA ---

    // Menampilkan Halaman Detail Grup (Kelola Anggota)
    public function show($id)
    {
        $group = SrGroup::with('mentor')->findOrFail($id);
        
        // Ambil riwayat anggota di grup ini (urutkan yang masih aktif di atas)
        $members = SrGroupMember::with('student')
                    ->where('group_id', $id)
                    ->orderBy('tanggal_keluar', 'asc')
                    ->orderBy('tanggal_gabung', 'desc')
                    ->get();

        // Cari siswa yang STATUSNYA AKTIF di sekolah untuk dimasukkan ke grup
        $students = Siswa::where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();

        return view('student-root.grup.show', compact('group', 'members', 'students'));
    }

    // Menambahkan Siswa ke dalam Grup (SUDAH MENDUKUNG MULTI-SELECT/TAGGING)
    public function addMember(Request $request, $id)
    {
        // 1. Validasi pastikan student_id yang masuk adalah array
        $request->validate([
            'student_id'   => 'required|array',
            'student_id.*' => 'exists:siswas,id',
        ]);

        $group = SrGroup::findOrFail($id);
        $berhasil = 0;

        // 2. Lakukan perulangan untuk menyimpan setiap siswa yang dipilih sekaligus
        foreach ($request->student_id as $student_id) {
            // Cek apakah siswa sudah ada di grup ini dan masih aktif (belum dikeluarkan)
            $cekAnggota = SrGroupMember::where('group_id', $group->id)
                ->where('student_id', $student_id)
                ->whereNull('tanggal_keluar')
                ->first();

            // Jika belum ada, masukkan ke database
            if (!$cekAnggota) {
                SrGroupMember::create([
                    'group_id'       => $group->id,
                    'student_id'     => $student_id,
                    'tanggal_gabung' => now()->toDateString(), // Hari ini
                    'tanggal_keluar' => null, // Masih aktif
                ]);
                $berhasil++;
            }
        }

        // 3. Tampilkan notifikasi yang dinamis
        if ($berhasil > 0) {
            return back()->with('success', "✅ $berhasil Siswa berhasil ditambahkan ke dalam grup!");
        } else {
            return back()->with('error', '❌ Semua siswa yang dipilih sudah menjadi anggota di grup ini (Tidak ada yang baru).');
        }
    }

    // Mengeluarkan Siswa dari Grup (Histori tetap disimpan)
    public function removeMember($member_id)
    {
        $member = SrGroupMember::findOrFail($member_id);
        
        // Tandai tanggal keluar, BUKAN dihapus (soft update)
        $member->update([
            'tanggal_keluar' => now()->toDateString(),
        ]);

        return back()->with('success', '👋 Siswa berhasil dikeluarkan dari grup (Histori keanggotaan tetap tersimpan).');
    }
}