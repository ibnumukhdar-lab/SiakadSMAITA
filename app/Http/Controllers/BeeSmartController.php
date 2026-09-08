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
            } elseif ($request->filled('generated_' . $field)) {
                // Audio hasil tombol "✨ Generate Suara" (TTS) — file sudah tersimpan di disk public
                $data[$field] = $request->input('generated_' . $field);
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
            } elseif ($request->filled('generated_' . $field)) {
                // Audio hasil tombol "✨ Generate Suara" (TTS)
                $genPath = $request->input('generated_' . $field);
                if ($vocab->$field && $vocab->$field !== $genPath) {
                    Storage::disk('public')->delete($vocab->$field);
                }
                $data[$field] = $genPath;
            }
        }

        $vocab->update($data);

        return back()->with('success', 'Teks kosakata dan Audio berhasil diperbarui!');
    }

    // =========================================================
    // GENERATE AUDIO TTS (2026-09) — dipakai tombol "✨ Generate Suara"
    // =========================================================
    protected function tts($teks, $lang)
    {
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . $lang . '&q=' . rawurlencode($teks);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
        $data = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code !== 200 || strlen($data) < 500) {
            throw new \RuntimeException('Gagal membuat audio (HTTP ' . $code . ')' . ($err !== '' ? ' - ' . $err : ''));
        }

        $nama = 'bee_audios/tts_' . \Illuminate\Support\Str::uuid() . '.mp3';
        Storage::disk('public')->put($nama, $data);

        return $nama;
    }

    protected function bahasaDariField($field)
    {
        return str_ends_with($field, '_ar') ? 'ar' : 'en';
    }

    // Endpoint untuk form tambah/edit: teks -> file audio (belum disimpan ke kosakata)
    public function generateAudio(Request $request)
    {
        $request->validate([
            'teks' => 'required|string|max:500',
            'lang' => 'required|in:en,ar',
        ]);

        try {
            $path = $this->tts($request->teks, $request->lang);

            return response()->json(['ok' => true, 'path' => $path, 'url' => url('berkas/' . $path)]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // Endpoint untuk kosakata yang sudah tersimpan: generate + simpan langsung ke DB
    public function generateVocabAudio($id, $field)
    {
        $map = [
            'audio_vocab_en'    => 'vocab_en',
            'audio_sentence_en' => 'sentence_en',
            'audio_mufrodat_ar' => 'mufrodat_ar',
            'audio_jumlah_ar'   => 'jumlah_ar',
        ];

        if (!isset($map[$field])) {
            return response()->json(['ok' => false, 'message' => 'Slot audio tidak dikenali.'], 422);
        }

        $vocab = BeeVocab::findOrFail($id);
        $teks = trim((string) $vocab->{$map[$field]});

        if ($teks === '') {
            return response()->json(['ok' => false, 'message' => 'Teks masih kosong — isi dulu teksnya.'], 422);
        }

        try {
            $path = $this->tts($teks, $this->bahasaDariField($field));

            if ($vocab->$field && $vocab->$field !== $path) {
                Storage::disk('public')->delete($vocab->$field);
            }
            $vocab->update([$field => $path]);

            return response()->json(['ok' => true, 'path' => $path, 'url' => url('berkas/' . $path)]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // Isi otomatis semua audio yang belum ada dalam satu modul (dipanggil berulang dari JS,
    // maksimal 12 per panggilan agar aman dari batas kewajaran Google TTS)
    public function generateMissingAudio($id)
    {
        $week = BeeWeek::with('vocabs')->findOrFail($id);

        $map = [
            'audio_vocab_en'    => 'vocab_en',
            'audio_sentence_en' => 'sentence_en',
            'audio_mufrodat_ar' => 'mufrodat_ar',
            'audio_jumlah_ar'   => 'jumlah_ar',
        ];

        $antrian = [];
        foreach ($week->vocabs as $vocab) {
            foreach ($map as $field => $teksCol) {
                if (!$vocab->$field && trim((string) $vocab->{$teksCol}) !== '') {
                    $antrian[] = ['vocab' => $vocab, 'field' => $field];
                }
            }
        }

        $diproses = array_slice($antrian, 0, 12);
        $berhasil = 0;
        $gagal    = 0;

        foreach ($diproses as $item) {
            try {
                $teks  = (string) $item['vocab']->{$map[$item['field']]};
                $path  = $this->tts($teks, $this->bahasaDariField($item['field']));
                $item['vocab']->update([$item['field'] => $path]);
                $berhasil++;
            } catch (\Throwable $e) {
                // Kemungkinan kena batas rate Google -> berhenti, sisanya bisa ditekan lagi
                $gagal++;
                break;
            }
        }

        return response()->json([
            'ok'       => true,
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
            'sisa'     => count($antrian) - count($diproses),
            'total'    => count($antrian),
        ]);
    }

    // Simpan target jumlah kata modul (batas_min / batas_maks)
    public function updateBatas($id, Request $request)
    {
        $request->validate([
            'batas_min'  => 'required|integer|min:1|max:500',
            'batas_maks' => 'required|integer|min:1|max:500',
        ]);

        if ((int) $request->batas_min > (int) $request->batas_maks) {
            return back()->with('error', 'Batas minimal tidak boleh lebih besar dari batas maksimal.');
        }

        BeeWeek::findOrFail($id)->update($request->only(['batas_min', 'batas_maks']));

        return back()->with('success', "🎯 Target kata modul diperbarui: {$request->batas_min} – {$request->batas_maks} kata.");
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
        $catatan = "Menyelesaikan Kuis Bee Smart: " . $week->judul;

        // 1. Pastikan master kriteria "Kuis Bee Smart" tersedia (dibuat sekali, poin +1).
        //    CATATAN 2026-09: sebelumnya kode memakai kolom 'keterangan'/'tanggal' &
        //    kolom cache 'total_poin' yang TIDAK ADA di DB -> klaim selalu gagal.
        $kriteria = \App\Models\SrPointCriteria::firstOrCreate(
            [
                'nama_perilaku' => 'Menyelesaikan Kuis Bee Smart',
                'kategori'      => 'positif',
                'poin'          => 1,
            ],
            [
                'deskripsi' => 'Poin otomatis saat siswa menyelesaikan kuis BEE Smart di Buku Saku (klaim mandiri via NISN).',
                'tingkat'   => null,
                'status'    => 'aktif',
            ]
        );

        // 2. Cegah klaim ganda: satu siswa hanya sekali per modul (kriteria + catatan sama)
        $sudahKlaim = \Illuminate\Support\Facades\DB::table('sr_point_entries')
            ->where('student_id', $siswa->id)
            ->where('criteria_id', $kriteria->id)
            ->where('catatan', $catatan)
            ->whereNull('deleted_at')
            ->exists();

        if ($sudahKlaim) {
            return response()->json(['success' => false, 'message' => 'Ups! Kamu sudah mengklaim poin untuk modul ini sebelumnya.']);
        }

        // 3. Snapshot grup aktif siswa saat ini (kalau belum masuk grup -> null)
        $anggota = \Illuminate\Support\Facades\DB::table('sr_group_members')
            ->where('student_id', $siswa->id)
            ->whereNull('tanggal_keluar')
            ->first();
        $groupId = $anggota ? $anggota->group_id : null;

        // 4. Simpan entri poin (input_by = NULL karena ini klaim mandiri siswa;
        //    kolom input_by sudah dibuat nullable lewat migrasi 2026_09_08)
        \Illuminate\Support\Facades\DB::table('sr_point_entries')->insert([
            'id'                => (string) \Illuminate\Support\Str::uuid(),
            'student_id'        => $siswa->id,
            'group_id'          => $groupId,
            'criteria_id'       => $kriteria->id,
            'poin'              => 1,
            'catatan'           => $catatan,
            'input_by'          => null,
            'tanggal_kejadian'  => now()->toDateString(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Selamat! +1 Poin Karakter berhasil ditambahkan ke akunmu!']);
    }
}