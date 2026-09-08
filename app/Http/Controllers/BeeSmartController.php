<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeeWeek;
use App\Models\BeeVocab;
use Illuminate\Support\Facades\Storage;

class BeeSmartController extends Controller
{
    public function index()
    {
        $weeks = BeeWeek::withCount('vocabs')->orderBy('id', 'desc')->get();
        return view('bee-smart.index', compact('weeks'));
    }

    public function storeWeek(Request $request)
    {
        $request->validate(['judul' => 'required|string|max:255']);
        BeeWeek::create([
            'judul'         => $request->judul,
            'tanggal_mulai' => now()->toDateString(),
            'status'        => 'draft'
        ]);
        return back()->with('success', 'Modul BEE Smart baru berhasil ditambahkan!');
    }

    // --- UPDATE: Logika Maksimal 3 Modul Aktif ---
    public function updateStatus(Request $request, $id)
    {
        $week = BeeWeek::findOrFail($id);
        
        // Update status modul yang dipilih
        $week->update(['status' => $request->status]);

        // Jika modul ini diaktifkan, pastikan maksimal hanya ada 3 modul aktif di database
        if ($request->status == 'aktif') {
            // Ambil semua modul aktif, urutkan berdasarkan yang paling baru diupdate/diaktifkan
            $activeWeeks = BeeWeek::where('status', 'aktif')->orderBy('updated_at', 'desc')->get();

            // Jika jumlahnya lebih dari 3, ambil sisanya (modul yang paling lama) dan jadikan arsip
            if ($activeWeeks->count() > 3) {
                // Slice(3) berarti mengambil data ke-4, ke-5, dst.
                $toArchiveIds = $activeWeeks->slice(3)->pluck('id');
                BeeWeek::whereIn('id', $toArchiveIds)->update(['status' => 'arsip']);
            }
        }

        return back()->with('success', 'Status modul berhasil diperbarui (Maksimal 3 Tayang di TV)!');
    }

    public function updateWeek(Request $request, $id)
    {
        $request->validate(['judul' => 'required|string|max:255']);
        BeeWeek::findOrFail($id)->update(['judul' => $request->judul]);
        return back()->with('success', 'Judul modul berhasil diperbarui!');
    }

    public function destroyWeek($id)
    {
        $week = BeeWeek::with('vocabs')->findOrFail($id);
        
        // 1. Bersihkan semua file audio fisik di server
        foreach ($week->vocabs as $vocab) {
            $audio_fields = ['audio_vocab_en', 'audio_sentence_en', 'audio_mufrodat_ar', 'audio_jumlah_ar'];
            foreach ($audio_fields as $field) {
                if ($vocab->$field) {
                    Storage::disk('public')->delete($vocab->$field);
                }
            }
        }
        
        // 2. Hard Delete semua kosakata bawaan
        $week->vocabs()->delete(); 
        
        // 3. Hard Delete Modul Utama
        $week->delete(); 

        return back()->with('success', 'Modul beserta seluruh data kosakata dan rekaman audionya berhasil dihapus permanen!');
    }

    public function manage($id)
    {
        $week = BeeWeek::with('vocabs')->findOrFail($id);
        return view('bee-smart.manage', compact('week'));
    }

    public function storeVocab(Request $request, $id)
    {
        $request->validate([
            'kosakata_id' => 'required|string|max:255',
            'audio_vocab_en' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_sentence_en' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_mufrodat_ar' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_jumlah_ar' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
        ]);

        $data = $request->only(['kosakata_id', 'vocab_en', 'sentence_en', 'mufrodat_ar', 'jumlah_ar']);
        $data['bee_week_id'] = $id;

        $audio_fields = ['audio_vocab_en', 'audio_sentence_en', 'audio_mufrodat_ar', 'audio_jumlah_ar'];
        foreach ($audio_fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('bee_audios', 'public');
            }
        }

        BeeVocab::create($data);

        return back()->with('success', 'Kosakata & Audio berhasil ditambahkan!');
    }

    public function destroyVocab($id)
    {
        $vocab = BeeVocab::findOrFail($id);
        
        $audio_fields = ['audio_vocab_en', 'audio_sentence_en', 'audio_mufrodat_ar', 'audio_jumlah_ar'];
        foreach ($audio_fields as $field) {
            if ($vocab->$field) {
                Storage::disk('public')->delete($vocab->$field);
            }
        }

        $vocab->delete();
        return back()->with('success', 'Kosakata beserta audionya berhasil dihapus!');
    }

    // --- UPDATE: Logika Edit Kosakata BESERTA File Audio Baru ---
    public function updateVocab(Request $request, $id)
    {
        $request->validate([
            'kosakata_id' => 'required|string|max:255',
            'vocab_en'    => 'required|string|max:255',
            'sentence_en' => 'nullable|string',
            'mufrodat_ar' => 'required|string|max:255',
            'jumlah_ar'   => 'nullable|string',
            // Tambahkan validasi file audio
            'audio_vocab_en' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_sentence_en' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_mufrodat_ar' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
            'audio_jumlah_ar' => 'nullable|file|mimetypes:audio/*,video/webm,video/mp4|max:5120',
        ]);

        $vocab = BeeVocab::findOrFail($id);
        
        $data = $request->only(['kosakata_id', 'vocab_en', 'sentence_en', 'mufrodat_ar', 'jumlah_ar']);

        // Logika untuk mendeteksi, menghapus audio lama, dan menyimpan audio baru
        $audio_fields = ['audio_vocab_en', 'audio_sentence_en', 'audio_mufrodat_ar', 'audio_jumlah_ar'];
        foreach ($audio_fields as $field) {
            if ($request->hasFile($field)) {
                // Hapus audio lama dari server (jika sebelumnya sudah ada audionya)
                if ($vocab->$field) {
                    Storage::disk('public')->delete($vocab->$field);
                }
                // Simpan file audio yang baru direkam
                $data[$field] = $request->file($field)->store('bee_audios', 'public');
            }
        }

        $vocab->update($data);

        return back()->with('success', 'Teks kosakata dan Audio berhasil diperbarui!');
    }

    // --- UPDATE: MODE PRESENTASI KELAS (DENGAN PILIHAN DROPDOWN) ---
    public function classroom(Request $request)
    {
        // Tarik maksimal 3 data modul yang berstatus aktif (diurutkan dari yang paling baru diaktifkan)
        $activeWeeks = BeeWeek::with('vocabs')
            ->where('status', 'aktif')
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();
        
        if ($activeWeeks->isEmpty()) {
            $activeWeek = null;
        } else {
            // Cek apakah guru memilih modul spesifik dari dropdown TV
            if ($request->has('modul_id') && $request->modul_id != 'semua') {
                $activeWeek = $activeWeeks->firstWhere('id', $request->modul_id);
                // Fallback jika ID yang dikirim tidak valid/tidak aktif
                if (!$activeWeek) $activeWeek = $activeWeeks->first();
            } else {
                // Mode Default (Semua): Gabungkan semua kosakata dari ke-3 modul tersebut menjadi 1 antrean
                $mergedVocabs = collect();
                foreach ($activeWeeks as $week) {
                    $mergedVocabs = $mergedVocabs->merge($week->vocabs);
                }

                // Buat objek virtual untuk dikirim ke tampilan TV Kelas
                $activeWeek = (object) [
                    'id' => 'semua',
                    'judul' => 'GABUNGAN ' . $activeWeeks->count() . ' MODUL',
                    'vocabs' => $mergedVocabs
                ];
            }
        }
        
        // Kirim $activeWeeks juga agar bisa ditampilkan di menu dropdown TV
        return view('bee-smart.classroom', compact('activeWeek', 'activeWeeks'));
    }

    public function bukuSaku()
    {
        $weeks = BeeWeek::withCount('vocabs')
                    ->whereIn('status', ['aktif', 'arsip'])
                    ->orderBy('id', 'desc')
                    ->get();
        
        return view('bee-smart.buku-saku.index', compact('weeks'));
    }

    public function bukuSakuShow($id)
    {
        $week = BeeWeek::with('vocabs')->findOrFail($id);
        return view('bee-smart.buku-saku.show', compact('week'));
    }

    public function claimPoint(Request $request, $id)
    {
        $request->validate(['nisn' => 'required']);
        
        $siswa = \App\Models\Siswa::where('nisn', $request->nisn)->orWhere('nis', $request->nisn)->first();
        
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa dengan NIS / NISN tersebut tidak ditemukan.']);
        }

        $week = BeeWeek::findOrFail($id);
        $keterangan = "Menyelesaikan Kuis BEE Smart: " . $week->judul;

        $sudahKlaim = \Illuminate\Support\Facades\DB::table('sr_point_entries')
            ->where('student_id', $siswa->id)
            ->where('keterangan', $keterangan)
            ->exists();

        if ($sudahKlaim) {
            return response()->json(['success' => false, 'message' => 'Ups! Kamu sudah mengklaim poin untuk modul ini sebelumnya.']);
        }

        $anggota = \Illuminate\Support\Facades\DB::table('sr_group_members')
            ->where('student_id', $siswa->id)
            ->whereNull('tanggal_keluar')
            ->first();
        $groupId = $anggota ? $anggota->group_id : null;

        \Illuminate\Support\Facades\DB::table('sr_point_entries')->insert([
            'student_id' => $siswa->id,
            'group_id' => $groupId,
            'poin' => 1,
            'keterangan' => $keterangan,
            'tanggal' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $totalSiswa = \Illuminate\Support\Facades\DB::table('sr_point_entries')->where('student_id', $siswa->id)->sum('poin');
        $siswa->update(['total_poin' => $totalSiswa]);

        if ($groupId) {
            $studentIds = \Illuminate\Support\Facades\DB::table('sr_group_members')
                ->where('group_id', $groupId)->whereNull('tanggal_keluar')->pluck('student_id');
            $totalGrup = \App\Models\Siswa::whereIn('id', $studentIds)->sum('total_poin');
            \Illuminate\Support\Facades\DB::table('sr_groups')->where('id', $groupId)->update(['total_poin' => $totalGrup]);
        }

        return response()->json(['success' => true, 'message' => 'Selamat! +1 Poin Karakter berhasil ditambahkan ke akunmu!']);
    }
}