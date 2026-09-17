<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class KelolaAkunController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        // Ambil daftar jabatan (kecuali Super Admin karena dia punya Kunci Master)
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $permissions = Permission::all();
        
        return view('kelola-akun.index', compact('users', 'roles', 'permissions'));
    }

    // TAB 1: Mengatur Jabatan Seseorang (Satuan) - SEKARANG MENDUKUNG MULTI-ROLE
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Cek apakah ada role yang dikirim dari checkbox (bentuknya array)
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        } else {
            // Jika tidak ada checkbox yang dicentang, cabut semua role-nya
            $user->syncRoles([]);
        }

        return back()->with('success', 'Jabatan untuk akun ' . $user->name . ' berhasil diperbarui.');
    }

    /** NIPA (Nomor Induk Pegawai Arafah) satu akun — diisi/diperbaiki admin. */
    public function updateNipa(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nipa' => ['nullable', 'string', 'max:30'],
        ]);

        $user->nipa = User::rapikanNipa($data['nipa'] ?? null);
        $user->save();

        return back()->with('success', '✅ NIPA akun ' . $user->name . ' disimpan: ' . ($user->nipa ?: '(dikosongkan)'));
    }

    /**
     * TAB 1: Memperbarui jabatan/role pengguna secara massal (Bulk Action).
     */
    public function bulkUpdateUser(Request $request)
    {
        // 1. Validasi kiriman data dari form
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'bulk_role' => 'required|string'
        ]);

        // 2. Ambil semua data user yang dicentang
        $users = User::whereIn('id', $request->user_ids)->get();

        // 3. Looping dan ubah jabatannya
        foreach ($users as $user) {
            if ($request->bulk_role == 'Cabut Jabatan') {
                // Jika pilih cabut jabatan, kosongkan rolenya
                $user->syncRoles([]); 
            } else {
                // Jika pilih role tertentu, timpa role lama dengan yang baru
                $user->syncRoles([$request->bulk_role]); 
            }
        }

        // 4. Kembalikan ke halaman dengan pesan sukses
        $jumlah = count($request->user_ids);
        return back()->with('success', "✅ Berhasil! Jabatan untuk {$jumlah} akun pengguna telah diperbarui secara massal.");
    }

    // TAB 2: Menyimpan Semua Checklist Matriks Sekaligus
    public function updateMatrix(Request $request)
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        
        foreach ($roles as $role) {
            // Ambil daftar yang dicentang untuk jabatan ini
            $permissionsForRole = $request->input('matrix.' . $role->id, []);
            // Terapkan centangnya (yang tidak dicentang otomatis terhapus hak aksesnya)
            $role->syncPermissions($permissionsForRole);
        }

        return back()->with('success', '✅ Matriks hak akses semua jabatan berhasil disimpan.');
    }

    // Fitur Ekstra: Jika nanti butuh tambah menu baru ke dalam matriks
    public function storePermission(Request $request)
    {
        $request->validate(['name' => 'required']);

        // 1. Bersihkan inputan (ubah spasi jadi setrip dan jadikan huruf kecil)
        $namaModul = strtolower(str_replace(' ', '-', $request->name));

        // 2. Otomatisasi: Tambahkan "buka-menu-" jika belum ada
        if (!str_starts_with($namaModul, 'buka-menu-')) {
            $namaModul = 'buka-menu-' . $namaModul;
        }

        // 3. Cek apakah modul tersebut sudah pernah dibuat sebelumnya
        if (Permission::where('name', $namaModul)->exists()) {
            return back()->with('error', 'Gagal: Modul tersebut sudah ada di tabel matriks.');
        }

        // 4. Simpan ke database
        Permission::create(['name' => $namaModul]);
        
        return back()->with('success', '✅ Modul baru berhasil ditambahkan ke tabel matriks.');
    }

    // --- FITUR IMPERSONATE (LOGIN SEBAGAI USER LAIN) ---
    public function impersonate($id)
    {
        $user = User::findOrFail($id);
        
        // Mencegah admin login sebagai dirinya sendiri atau sesama Super Admin
        if ($user->id === auth()->id() || $user->hasRole('Super Admin')) {
            return back()->with('error', 'Tidak bisa login sebagai akun ini.');
        }

        // Simpan ID Super Admin ke dalam session sebelum menyamar
        session()->put('impersonate_by', auth()->id());
        
        // Login sebagai user yang dipilih
        auth()->loginUsingId($id);

        return redirect()->route('dashboard')->with('success', '🕵️‍♂️ Anda sekarang menyamar dan login sebagai ' . $user->name);
    }

    public function leaveImpersonate()
    {
        // Cek apakah ada session menyamar
        if (session()->has('impersonate_by')) {
            $originalId = session()->pull('impersonate_by'); // Ambil dan hapus session
            
            // Login kembali sebagai Super Admin
            auth()->loginUsingId($originalId);
            
            return redirect()->route('kelola-akun.index')->with('success', 'Berhasil kembali ke akun Super Admin Anda.');
        }

        return redirect()->route('dashboard');
    }
}