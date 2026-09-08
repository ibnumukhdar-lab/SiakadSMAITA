<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SrPointEntry;
use App\Models\SrPointCriteria;
use App\Models\Siswa;
use App\Models\SrGroupMember;
use Illuminate\Support\Facades\Auth;

class SrPointEntryController extends Controller
{
    // Menampilkan Form Input Poin
    public function create()
    {
        // 1. Ambil data siswa yang aktif saja
        $students = Siswa::where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();
        
        // 2. Ambil kriteria yang aktif
        $criterias = SrPointCriteria::where('status', 'aktif')->orderBy('nama_perilaku', 'asc')->get();
        
        // 3. Ambil 10 riwayat input terakhir oleh guru yang sedang login (untuk tabel history di bawah form)
        $recent_entries = SrPointEntry::with(['student', 'criteria'])
                            ->where('input_by', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();

        return view('student-root.poin.create', compact('students', 'criterias', 'recent_entries'));
    }

    // Menyimpan Data Poin ke Database
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:siswas,id',
            'criteria_id' => 'required|exists:sr_point_criteria,id',
            'tanggal_kejadian' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ], [
            'tanggal_kejadian.before_or_equal' => 'Tanggal kejadian tidak boleh mendahului hari ini!'
        ]);

        // 1. Cari kriteria untuk memastikan nilai poin otomatis sesuai master
        $criteria = SrPointCriteria::findOrFail($request->criteria_id);

        // 2. Lacak Grup Aktif (Mentor) siswa saat ini
        $active_group = SrGroupMember::where('student_id', $request->student_id)
                            ->whereNull('tanggal_keluar')
                            ->first();

        // 3. Simpan ke Database
        SrPointEntry::create([
            'student_id' => $request->student_id,
            // Jika siswa belum punya grup, tetap bisa diinput tapi group_id bernilai null
            'group_id' => $active_group ? $active_group->group_id : null, 
            'criteria_id' => $criteria->id,
            'poin' => $criteria->poin, 
            'catatan' => $request->catatan,
            'input_by' => Auth::id(), // Rekam siapa guru yang menginput
            'tanggal_kejadian' => $request->tanggal_kejadian,
        ]);

        return back()->with('success', '✅ Poin sikap berhasil dicatat ke dalam sistem!');
    }

    // Menampilkan Histori Poin per Siswa
    public function history($id)
    {
        $siswa = Siswa::findOrFail($id);
        
        // Ambil riwayat poin beserta nama kriteria dan nama guru yang menginput
        $histories = SrPointEntry::with('criteria')
            ->select('sr_point_entries.*', 'users.name as nama_guru')
            ->leftJoin('users', 'sr_point_entries.input_by', '=', 'users.id')
            ->where('student_id', $id)
            ->orderBy('tanggal_kejadian', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Hitung total akumulasi poin
        $total_poin = SrPointEntry::where('student_id', $id)->sum('poin');

        return view('student-root.poin.history', compact('siswa', 'histories', 'total_poin'));
    }
}