<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsramaKamar;
use App\Models\AsramaMember;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AsramaKamarController extends Controller
{
    // ====================================================
    // 1. Halaman Kamar Binaan Saya (Khusus Musyrif)
    // ====================================================
    public function kamarBinaan()
    {
        $kamars = AsramaKamar::with('musyrif')->withCount('members')
                    ->where('musyrif_id', auth()->id()) // Hanya ambil kamar milik user yang login
                    ->orderBy('kategori', 'asc')
                    ->orderBy('nama_kamar', 'asc')
                    ->get();

        return view('asrama.kamar.binaan', compact('kamars'));
    }

    // ====================================================
    // 2. Halaman Daftar Kamar Keseluruhan
    // ====================================================
    public function index()
    {
        // Menampilkan kamar, diurutkan berdasarkan Kategori (Putra/Putri) lalu Nama Kamar
        $kamars = AsramaKamar::with('musyrif')->withCount('members')
                    ->orderBy('kategori', 'asc')
                    ->orderBy('nama_kamar', 'asc')
                    ->get();

        // Jaring Pengaman Role Spatie: Cegah Error 500 jika Role 'Musyrif' belum ada
        try {
            $teachers = User::role('Musyrif')->get(); 
        } catch (\Throwable $e) {
            $teachers = collect(); // Kembalikan array kosong jika role belum dibuat
        }

        return view('asrama.kamar.index', compact('kamars', 'teachers'));
    }

    // ====================================================
    // 3. Simpan Kamar Baru (Null-Safe & Throwable Catch)
    // ====================================================
    public function store(Request $request)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'kategori'   => 'required|in:putra,putri',
            'musyrif_id' => 'required|exists:users,id',
            'kapasitas'  => 'required|integer|min:1',
        ]);

        try {
            AsramaKamar::create([
                'nama_kamar' => $request->nama_kamar,
                'kategori'   => $request->kategori,
                'musyrif_id' => $request->musyrif_id,
                'kapasitas'  => $request->kapasitas,
                'status'     => 'aktif',
            ]);

            return back()->with('success', 'Kamar asrama baru berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat menambah kamar: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 4. Update Info Kamar
    // ====================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kamar' => 'required|string|max:255',
            'kategori'   => 'required|in:putra,putri',
            'musyrif_id' => 'required|exists:users,id',
            'kapasitas'  => 'required|integer|min:1',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        try {
            $kamar = AsramaKamar::findOrFail($id);
            $kamar->update($request->all());

            return back()->with('success', 'Informasi kamar asrama berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat mengupdate kamar: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 5. Hapus Kamar
    // ====================================================
    public function destroy($id)
    {
        try {
            $kamar = AsramaKamar::findOrFail($id);
            $kamar->delete();
            
            return back()->with('success', 'Kamar asrama berhasil dihapus secara permanen.');
        } catch (\Throwable $e) {
            // Error ini akan muncul jika kamar tidak bisa dihapus karena id-nya masih terikat dengan data sidak di tabel lain (Foreign Key Constraint)
            return back()->with('error', 'Gagal! Pastikan kamar ini sudah tidak memiliki penghuni dan tidak terkait dengan data sidak. (Pesan: ' . $e->getMessage() . ')');
        }
    }

    // ====================================================
    // BAGIAN KELOLA ANGGOTA KAMAR (PENGHUNI)
    // ====================================================

    public function show($id)
    {
        try {
            $kamar = AsramaKamar::with('musyrif')->findOrFail($id);
            $members = AsramaMember::with('student')->where('kamar_id', $id)->orderBy('tanggal_masuk', 'desc')->get();
            $students = Siswa::where('status', 'Aktif')->whereNull('deleted_at')->orderBy('nama_lengkap', 'asc')->get();

            return view('asrama.kamar.show', compact('kamar', 'members', 'students'));
        } catch (\Throwable $e) {
            return redirect()->route('asrama.kamar.index')->with('error', 'Data kamar tidak ditemukan!');
        }
    }

    public function addMember(Request $request, $id)
    {
        $request->validate([
            'student_id'   => 'required|array',
            'student_id.*' => 'exists:siswas,id',
        ]);

        try {
            $kamar = AsramaKamar::findOrFail($id);
            $berhasil = 0;

            // Gunakan Transaction agar jika di tengah looping terjadi error, semua proses dibatalkan otomatis
            DB::beginTransaction();
            foreach ($request->student_id as $student_id) {
                $cekPenghuni = AsramaMember::where('kamar_id', $kamar->id)->where('student_id', $student_id)->first();
                if (!$cekPenghuni) {
                    AsramaMember::create([
                        'kamar_id'       => $kamar->id,
                        'student_id'     => $student_id,
                        'tanggal_masuk'  => now()->toDateString(),
                    ]);
                    $berhasil++;
                }
            }
            DB::commit();

            if ($berhasil > 0) {
                return back()->with('success', "✅ $berhasil Siswa berhasil dimasukkan ke kamar ini!");
            } else {
                return back()->with('error', '❌ Semua siswa yang dipilih ternyata sudah menjadi penghuni kamar ini.');
            }
        } catch (\Throwable $e) {
            DB::rollBack(); // Batalkan semua simpanan di database jika error
            return back()->with('error', 'Terjadi Kesalahan Database saat memasukkan siswa: ' . $e->getMessage());
        }
    }

    public function removeMember($member_id)
    {
        try {
            $member = AsramaMember::findOrFail($member_id);
            $member->delete();

            return back()->with('success', '👋 Siswa berhasil dikeluarkan dari kamar.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengeluarkan siswa: ' . $e->getMessage());
        }
    }
}