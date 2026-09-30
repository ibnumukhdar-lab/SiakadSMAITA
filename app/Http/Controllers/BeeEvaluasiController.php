<?php

namespace App\Http\Controllers;

use App\Models\BeeEvaluasi;
use App\Models\BeeEvaluasiPercobaan;
use App\Models\BeeWeek;
use App\Models\Siswa;
use App\Models\SrPointCriteria;
use App\Support\ArtiKalimat;
use App\Support\SoalBee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Evaluasi BEE Smart (triwulan & semester).
 *
 * Sisi siswa: TANPA LOGIN — cukup NIS/NISN. Tiap percobaan punya kode unik (kolom kode)
 * yang dipakai sebagai alamat halaman ujian, jadi tidak perlu sesi login.
 * Sisi guru: diatur dari /bee-smart/evaluasi (butuh izin kelola-bee-smart).
 *
 * SEJAK ALUR 7 MODE (21 Sep 2026): satu sesi bahasa = 7 SUB-SESI mode (masing-masing 20 soal),
 * mengikuti latihan kosakata bilarabiya.online — mesinnya `SoalBee::buatSesi()`.
 * Aturan: 3 NYAWA per mode (3 jawaban salah = mode itu berhenti), umpan balik langsung,
 * TANPA timer, urutan mode bebas, boleh berhenti lalu lanjut.
 * Nilai sesi bahasa = RATA-RATA mode yang sudah dikerjakan; poin Student Root diberikan
 * sekali per periode per bahasa bila nilai sesi >= KKM periode.
 *
 * Bentuk data tetap memakai tabel yang ada (tanpa kolom baru): satu baris `bee_evaluasi_percobaan`
 * per sesi bahasa berisi seluruh soal 7 mode; keadaan tiap mode (dijawab/nyawa/nilai) DIHITUNG
 * ULANG dari kolom soal/kunci/jawaban, jadi tidak ada keadaan yang bisa hilang.
 * Percobaan LAMA (mesin lama, mis. sesi percobaan kelas X yang masih berjalan) tetap
 * ditangani persis seperti sebelumnya.
 */
class BeeEvaluasiController extends Controller
{
    public const KRITERIA = 'Lulus Evaluasi Bee Smart';

    /**
     * Sub-sesi mode per sesi bahasa — URUT DARI YANG PALING MUDAH (permintaan Fahri 21 Sep:
     * "urutkan mode dari yang paling mudah dulu, yang kosakata dulu baru yang kalimat").
     *
     * Urutan ini dipakai untuk: urutan kartu di menu siswa, urutan blok soal hasil buatSesi(),
     * dan urutan tabel di halaman hasil.
     *
     * KOSAKATA (kata satuan) — mudah → sulit:
     *   1. pg_kosakata  : baca kata Indonesia → ketuk padanannya (tanpa audio). 2 sesi × 10 soal.
     *   2. dengar_kata  : dengar kata (tanpa tulisan) → ketuk kata tertulis.
     *   3. jodoh        : pasangkan kata Indonesia ⇄ kata asing.
     *   4. ucap         : ucapkan kata yang tertulis.
     *   5. imla_murni   : dengar kata → KETIK katanya (tanpa tulisan).
     * KALIMAT — mudah → sulit:
     *   6. pg_rumpang   : kalimat rumpang → ketuk kata yang hilang.
     *   7. drag_rumpang : kalimat rumpang → seret/ketuk balok kata.
     *   8. susun_kata   : susun balok kata menjadi kalimat.
     *   9. imla_rumpang : dengar audio kalimat → KETIK kata yang hilang.
     */
    public const MODE7 = [
        'pg_kosakata', 'dengar_kata', 'jodoh', 'ucap', 'imla_murni',
        'pg_rumpang', 'drag_rumpang', 'susun_kata', 'imla_rumpang',
    ];

    /** Jumlah soal tiap SESI di dalam mode pg_kosakata (2 sesi × 10 soal = 20 soal). */
    public const SESI_KOSAKATA = 10;

    /**
     * Jodohkan: satu mode = 4 papan @ 10 pasang (sesi 1 = papan 1–2 teks→teks, sesi 2 = papan 3–4
     * audio→teks). Tiap pasangan bernilai 1 poin; pasangan salah di percobaan pertama tidak dapat poin
     * walau nanti dipasangkan benar, dan salah TIDAK mengurangi nyawa.
     */
    public const PAPAN_JODOH = 4;

    public const PASANGAN_JODOH = 10;

    /** Jumlah soal tiap sub-sesi mode. */
    public const PER_MODE = 20;

    /** Nyawa tiap mode: 3 jawaban salah = mode itu berhenti. */
    public const NYAWA = 3;

    /**
     * Ambang kemiripan mode `ucap` — dihitung di server, kunci tidak pernah ke peramban.
     * 0,72 (dulu 0,8): pengenal suara peramban sering salah dengar pada kata pendek/homofon
     * (mis. "to read" tertangkap "to rent" = 0,714) — nilai 0,72 lebih adil tanpa jadi terlalu longgar.
     */
    public const MIRIP_UCAP = 0.70;

    /** Nama & keterangan tiap mode untuk tampilan (dipakai view). */
    public static function infoMode(): array
    {
        return [
            'pg_kosakata' => ['label' => 'PG Kosakata', 'ikon' => '🔤', 'cara' => 'Kata Indonesia → pilih padanannya'],
            'dengar_kata' => ['label' => 'Simak Kata', 'ikon' => '🔊', 'cara' => 'Dengar → pilih kata yang kamu dengar'],
            'jodoh' => ['label' => 'Jodohkan', 'ikon' => '🔗', 'cara' => 'Pasangkan kata kiri–kanan (2 sesi × 2 fase × 10 pasang)'],
            'ucap' => ['label' => 'Ucap', 'ikon' => '🎙️', 'cara' => 'Ucapkan kata lewat mikrofon'],
            'imla_murni' => ['label' => "Imla' Murni", 'ikon' => '🎧', 'cara' => 'Dengar → ketik katanya'],
            'pg_rumpang' => ['label' => 'Lengkapi Kalimat', 'ikon' => '📝', 'cara' => 'Kalimat rumpang → pilih kata'],
            'drag_rumpang' => ['label' => 'Seret Balok Kata', 'ikon' => '🧩', 'cara' => 'Kalimat rumpang → seret balok'],
            'susun_kata' => ['label' => 'Susun Kata', 'ikon' => '🏗️', 'cara' => 'Susun balok jadi kalimat'],
            'imla_rumpang' => ['label' => "Imla' Rumpang", 'ikon' => '⌨️', 'cara' => 'Dengar kalimat → ketik kata hilang'],
        ];
    }

    // =========================================================== SISI SISWA

    /** Halaman awal: daftar periode yang dibuka + kolom NIS/NISN. */
    public function masuk(Request $request)
    {
        $evaluasi = BeeEvaluasi::query()
            ->where('aktif', true)
            ->whereDate('mulai', '<=', now()->toDateString())
            ->whereDate('selesai', '>=', now()->toDateString())
            ->orderByDesc('mulai')
            ->get();

        return view('bee-smart.evaluasi.masuk', [
            'evaluasi' => $evaluasi,
            'pesan' => session('pesan'),
            'nis' => $request->query('nis'),
        ]);
    }

    /** Verifikasi NIS/NISN lalu terbitkan percobaan. */
    public function verifikasi(Request $request)
    {
        $data = $request->validate([
            'evaluasi_id' => ['required', 'integer'],
            'nis' => ['required', 'string', 'max:40'],
        ], [
            'nis.required' => 'Tulis NIS atau NISN kamu dulu ya.',
        ]);

        $evaluasi = BeeEvaluasi::where('aktif', true)
            ->whereDate('mulai', '<=', now()->toDateString())
            ->whereDate('selesai', '>=', now()->toDateString())
            ->find($data['evaluasi_id']);

        if (! $evaluasi) {
            return redirect()->route('evaluasi.masuk')->with('pesan', 'Periode evaluasi itu sedang tidak dibuka.');
        }

        $nis = trim($data['nis']);
        $siswa = Siswa::query()->whereNull('deleted_at')->where('status', 'Aktif')
            ->where(fn ($q) => $q->where('nis', $nis)->orWhere('nisn', $nis))
            ->first();

        if (! $siswa) {
            return redirect()->route('evaluasi.masuk')
                ->with('pesan', 'NIS/NISN '.$nis.' tidak ditemukan. Periksa lagi atau tanya gurumu.');
        }

        // Kelas dibaca dari DATA SISWA (bukan diketik siswa) — tanpa kelas, sesi tak bisa ditentukan.
        if (trim((string) $siswa->kelas) === '') {
            return redirect()->route('evaluasi.masuk')
                ->with('pesan', 'Datamu belum punya kelas, jadi belum bisa ikut evaluasi. Hubungi gurumu untuk melengkapi kelas di Data Siswa.');
        }

        $sudah = BeeEvaluasiPercobaan::query()
            ->where('bee_evaluasi_id', $evaluasi->id)
            ->where('siswa_id', $siswa->id)
            ->count();

        if ($sudah >= $evaluasi->maks_percobaan) {
            return redirect()->route('evaluasi.masuk')
                ->with('pesan', 'Kamu sudah memakai '.$sudah.' percobaan (batas '.$evaluasi->maks_percobaan.'). Hubungi gurumu bila perlu dibuka ulang.');
        }

        // Siswa dikenali — buka MENU SESI (Bahasa Inggris / Bahasa Arab), dia yang memilih.
        $request->session()->put('evaluasi_siswa', $siswa->id);
        $request->session()->put('evaluasi_periode', $evaluasi->id);

        return redirect()->route('evaluasi.sesi');
    }

    /**
     * Menu sesi: siswa yang sudah memasukkan NIS memilih mau mengerjakan
     * sesi Bahasa Inggris atau Bahasa Arab lebih dulu (nilai dicatat terpisah).
     * Untuk percobaan 7 mode, kartu sesi juga menampilkan kemajuan tiap mode.
     */
    public function sesi(Request $request)
    {
        $siswaId = (int) $request->session()->get('evaluasi_siswa');
        $siswa = $siswaId ? Siswa::find($siswaId) : null;

        if (! $siswa) {
            return redirect()->route('evaluasi.masuk')->with('pesan', 'Tulis NIS/NISN dulu ya.');
        }

        // Hanya sesi untuk KELAS SISWA (atau sesi "Semua kelas") yang ditampilkan.
        $periode = BeeEvaluasi::query()->dibuka()->untukKelas($siswa->kelas)->orderByDesc('mulai')->get();

        $percobaan = [];
        $jumlah = [];
        $ringkas = [];
        foreach ($periode as $e) {
            foreach (['inggris', 'arab'] as $b) {
                $semua = BeeEvaluasiPercobaan::query()
                    ->where('bee_evaluasi_id', $e->id)->where('siswa_id', $siswa->id)->where('bahasa', $b)
                    ->orderByDesc('id')->get();
                $jumlah[$e->id][$b] = $semua->count();
                // yang tampil: yang masih berjalan, kalau tidak ada pakai yang terbaik
                $pilih = $semua->firstWhere('status', 'berjalan') ?: $semua->sortByDesc('nilai')->first();
                $percobaan[$e->id][$b] = $pilih;
                $ringkas[$e->id][$b] = $pilih ? $this->ringkasMode7($pilih) : null;
            }
        }

        return view('bee-smart.evaluasi.sesi', [
            'siswa' => $siswa,
            'periode' => $periode,
            'percobaan' => $percobaan,
            'jumlah' => $jumlah,
            'ringkas' => $ringkas,
            'infoMode' => self::infoMode(),
            'pesan' => session('pesan'),
        ]);
    }

    /** Memulai (atau melanjutkan) satu sesi bahasa tertentu — mesin 7 mode. */
    public function mulai(Request $request)
    {
        $data = $request->validate([
            'evaluasi_id' => ['required', 'integer'],
            'bahasa' => ['required', 'in:inggris,arab'],
        ]);

        $siswa = Siswa::find((int) $request->session()->get('evaluasi_siswa'));
        if (! $siswa) {
            return redirect()->route('evaluasi.masuk')->with('pesan', 'Tulis NIS/NISN dulu ya.');
        }

        // Sesi harus dibuka DAN berlaku untuk kelas siswa (tidak bisa menyusup ke sesi kelas lain).
        $evaluasi = BeeEvaluasi::query()->dibuka()->untukKelas($siswa->kelas)->find($data['evaluasi_id']);
        if (! $evaluasi) {
            return redirect()->route('evaluasi.sesi')->with('pesan', 'Periode itu sedang tidak dibuka.');
        }

        $bahasa = $data['bahasa'];

        $semua = BeeEvaluasiPercobaan::query()
            ->where('bee_evaluasi_id', $evaluasi->id)
            ->where('siswa_id', $siswa->id)
            ->where('bahasa', $bahasa);

        if ((clone $semua)->count() >= $evaluasi->maks_percobaan) {
            return redirect()->route('evaluasi.sesi')
                ->with('pesan', 'Percobaan sesi '.($bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris').' sudah mencapai batas ('.$evaluasi->maks_percobaan.'). Hubungi gurumu bila perlu dibuka ulang.');
        }

        $terbuka = (clone $semua)->where('status', 'berjalan')->latest('id')->first();
        if ($terbuka) {
            return redirect()->route('evaluasi.kerjakan', $terbuka->kode);
        }

        $modulId = is_array($evaluasi->modul) && count($evaluasi->modul)
            ? array_map('intval', $evaluasi->modul)
            : BeeWeek::query()
                ->whereDate('tanggal_mulai', '>=', $evaluasi->mulai)
                ->whereDate('tanggal_mulai', '<=', $evaluasi->selesai)
                ->pluck('id')->all();

        // MESIN 7 MODE: 7 sub-sesi × 20 soal per sesi bahasa.
        $hasil = SoalBee::buatSesi(self::MODE7, self::PER_MODE, $modulId, $bahasa, ['jodoh' => self::PAPAN_JODOH]);

        if (count($hasil['soal'] ?? []) < self::PER_MODE) {
            $namaSesi = $bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris';
            return redirect()->route('evaluasi.sesi')
                ->with('pesan', 'Soal sesi '.$namaSesi.' belum siap: kosakata pada modul yang dipilih belum lengkap '
                    .($bahasa === 'arab' ? 'Arab' : 'Inggris').'-nya. '
                    .'Lengkapi dulu di BEE Smart → Bank Kosakata, baru sesi ini bisa diujikan. Hubungi gurumu.');
        }

        // Rekatkan penanda BLOK sub-sesi ke tiap butir. buatSesi() menyusun soal BLOK PER MODE
        // mengikuti urutan self::MODE7, jadi batas blok bisa dihitung PERSIS dari jumlah butir
        // tiap mode (meski satu butir cadangan memakai nama mode lain, mis. pg_arti).
        $blok = [];
        $n = 0;
        foreach (self::MODE7 as $m) {
            $jml = count($hasil['modes'][$m]['soal'] ?? []);
            for ($i = 0; $i < $jml; $i++) {
                $blok[++$n] = $m;
            }
        }
        $soal = [];
        $keKosakata = 0;   // hitungan butir mode pg_kosakata (untuk menandai 2 sesi × 10 soal)
        $keJodoh = 0;      // hitungan papan mode jodoh (untuk menandai sesi & fase)
        foreach ($hasil['soal'] as $b) {
            $b['blok'] = $blok[$b['nomor']] ?? (is_string($b['mode'] ?? null) ? $b['mode'] : 'pg_arti');
            if ($b['blok'] === 'pg_kosakata') {
                $keKosakata++;
                // 10 soal pertama = sesi 1, 10 berikutnya = sesi 2 (permintaan Fahri)
                $b['sesi'] = (int) ceil($keKosakata / self::SESI_KOSAKATA);
            }
            if ($b['blok'] === 'jodoh') {
                $keJodoh++;
                // papan 1–2 = sesi 1 (teks→teks), papan 3–4 = sesi 2 (audio→teks); tiap sesi 2 fase
                $b['sesi'] = (int) ceil($keJodoh / 2);
                $b['fase'] = ($keJodoh % 2 === 0) ? 2 : 1;
                if ($b['fase'] === 1) { $b['variasi'] = $b['variasi'] ?? ($b['sesi'] === 1 ? 'teks' : 'audio'); }
            }
            $soal[] = $b;
        }

        $percobaan = BeeEvaluasiPercobaan::create([
            'bee_evaluasi_id' => $evaluasi->id,
            'siswa_id' => $siswa->id,
            'bahasa' => $bahasa,
            'kode' => Str::lower(Str::random(12)),
            'soal' => $soal,
            'kunci' => $hasil['kunci'],
            'jawaban' => [],
            'status' => 'berjalan',
            'mulai_pada' => now(),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('evaluasi.kerjakan', $percobaan->kode);
    }

    /** Halaman ujian (menu 7 mode + halaman pengerjaan). */
    public function kerjakan(string $kode)
    {
        $percobaan = BeeEvaluasiPercobaan::where('kode', $kode)->firstOrFail();
        $siswa = Siswa::find($percobaan->siswa_id);

        if ($percobaan->status === 'selesai') {
            return redirect()->route('evaluasi.hasil', $kode);
        }

        $peta7 = $this->petaMode7($percobaan);

        if ($peta7) {
            // Alur 7 mode: TANPA timer. Keadaan tiap mode dihitung ulang dari jawaban tersimpan.
            return view('bee-smart.evaluasi.kerjakan', [
                'percobaan' => $percobaan,
                'evaluasi' => $percobaan->evaluasi,
                'siswa' => $siswa,
                'sisaDetik' => 0,
                'mode7' => true,
                'peta7' => $peta7,
                'ringkas' => $this->ringkasMode7($percobaan),
                // jumlah mode yang BENAR-BENAR ada di sesi ini (sesi lama 7 mode, sesi baru 9 mode)
                'jmlMode' => count(array_unique(array_values($this->petaButir($percobaan)['blok']))),
                'arti' => $this->artiKalimat7($percobaan),
                'infoMode' => self::infoMode(),
                'pesan' => session('pesan'),
            ]);
        }

        $batasDetik = (int) ($percobaan->evaluasi->durasi_menit ?? 25) * 60;
        $berjalan = (int) now()->diffInSeconds($percobaan->mulai_pada ?? now());
        $sisa = max(0, $batasDetik - $berjalan);

        return view('bee-smart.evaluasi.kerjakan', [
            'percobaan' => $percobaan,
            'evaluasi' => $percobaan->evaluasi,
            'siswa' => $siswa,
            'sisaDetik' => $sisa,
            'mode7' => false,
            'peta7' => [],
            'ringkas' => null,
            'infoMode' => self::infoMode(),
            'pesan' => session('pesan'),
        ]);
    }

    /**
     * Menyimpan satu jawaban (dipanggil peramban saat siswa menjawab).
     *
     * Alur 7 mode: tiap soal hanya boleh dijawab SEKALI (nyawa tidak bisa diatih dengan
     * mengulang), balasannya memuat benar/salah + jawaban benar (umpan balik langsung)
     * dan sisa nyawa. Mode `jodoh` boleh minta pemeriksaan satu posisi tanpa menyimpan.
     */
    public function simpan(Request $request, string $kode)
    {
        $percobaan = BeeEvaluasiPercobaan::where('kode', $kode)->firstOrFail();

        if ($percobaan->status === 'selesai') {
            return response()->json(['ok' => false, 'pesan' => 'Percobaan sudah selesai.']);
        }

        $nomor = (int) $request->input('nomor');
        $jawaban = (string) $request->input('jawaban', '');
        $isi = $percobaan->jawaban ?: [];
        $peta7 = $this->petaMode7($percobaan);

        // ---------------------------------------------------------------- alur 7 mode
        if ($peta7) {
            // AKSI KHUSUS: mulai BABAK BARU untuk satu mode (dipakai setelah 3 nyawa habis, atau
            // kalau siswa sendiri minta mengulang). Jawaban babak berjalan DIARSIPKAN ke
            // jawaban['_babak'][mode] lalu dikosongkan, jadi soal bisa dijawab lagi dari soal pertama.
            if ($request->input('aksi') === 'babak') {
                return $this->mulaiBabakBaru($percobaan, (string) $request->input('mode'), (string) $request->input('alasan', 'nyawa'));
            }

            $mode = $peta7[$nomor] ?? null;
            if ($mode === null) {
                return response()->json(['ok' => false, 'pesan' => 'Soal ini bukan bagian dari sesi 7 mode.']);
            }

            $ringkas = $this->ringkasMode7($percobaan);
            $r = $ringkas['modes'][$mode];

            $jenisPermintaan = $this->petaButir($percobaan)['jenis'][$nomor] ?? $mode;

            // JODOH (papan 10 pasang): satu ketukan = satu percobaan pasangan.
            // Benar → pasangan lenyap dari papan + 1 poin (bila percobaan pertama).
            // Salah → boleh coba lagi, tapi pasangan itu TIDAK dapat poin; nyawa tidak berkurang.
            if ($jenisPermintaan === 'jodoh' && $request->boolean('ketuk')) {
                return $this->jodohKetuk($percobaan, $mode, $nomor, $request);
            }

            // jodoh versi lama: periksa satu posisi saja (umpan balik per pasangan) — tidak disimpan.
            if ($jenisPermintaan === 'jodoh' && $request->boolean('periksa')) {
                $posisi = (int) $request->input('posisi');

                return response()->json([
                    'ok' => true,
                    'periksa' => true,
                    'mode' => $mode,
                    'posisi_benar' => $this->jodohPosisiBenar((string) ($percobaan->kunci[$nomor] ?? ''), $posisi, $jawaban),
                    'nyawa' => $r['nyawa'],
                ]);
            }

            if (trim((string) ($isi[$nomor] ?? '')) !== '') {
                return response()->json([
                    'ok' => false, 'pesan' => 'Soal ini sudah kamu jawab.', 'mode' => $mode,
                    'nyawa' => $r['nyawa'], 'sudah' => true,
                ]);
            }

            if ($r['nyawa'] <= 0) {
                return response()->json([
                    'ok' => false, 'pesan' => 'Nyawamu di mode ini sudah habis — ulangi mode ini dari soal pertama.', 'mode' => $mode,
                    'nyawa' => 0, 'tuntas' => true, 'boleh_ulang' => true, 'babak' => $r['babak'],
                    'nilai_babak' => $r['nilai_babak'], 'nilai_mode' => $r['nilai'],
                ]);
            }

            $kunciButir = (string) ($percobaan->kunci[$nomor] ?? '');
            $jenis = $this->petaButir($percobaan)['jenis'][$nomor] ?? $mode;
            $isi[$nomor] = mb_substr(trim($jawaban), 0, 500);
            $percobaan->jawaban = $isi;
            $percobaan->save();

            $lengkap = $jenis !== 'jodoh' || $this->jodohLengkap($kunciButir, $isi[$nomor]);
            $benar = $lengkap && $this->cocokButir($jenis, $kunciButir, $isi[$nomor]);
            $setelah = $this->ringkasMode7($percobaan->fresh());
            $rs = $setelah['modes'][$mode];

            return response()->json([
                'ok' => true,
                'nomor' => $nomor,
                'mode' => $mode,
                'benar' => $benar,
                'kunci' => (! $benar && $lengkap) ? $kunciButir : null,
                'nyawa' => $rs['nyawa'],
                'dijawab' => $rs['dijawab'],
                'total' => $rs['total'],
                'nilai_mode' => $rs['nilai'],
                'tuntas' => $rs['tuntas'],
                'berhenti' => $rs['nyawa'] <= 0,
                'terjawab' => $setelah['dijawab'],
                'modes_tuntas' => $setelah['mode_tuntas'],
                'babak' => $rs['babak'],
                'nilai_babak' => $rs['nilai_babak'],
                'boleh_ulang' => $rs['boleh_ulang'],
            ]);
        }

        // ---------------------------------------------------------------- alur lama (tidak diubah)
        $isi[$nomor] = mb_substr(trim($jawaban), 0, 500);
        $percobaan->jawaban = $isi;
        $percobaan->save();

        return response()->json(['ok' => true, 'nomor' => $nomor, 'terjawab' => count(array_filter($isi, fn ($j) => trim((string) $j) !== ''))]);
    }

    /** Menutup percobaan: hitung nilai, beri poin bila lulus. */
    public function selesai(Request $request, string $kode)
    {
        $percobaan = BeeEvaluasiPercobaan::where('kode', $kode)->firstOrFail();

        if ($percobaan->status === 'selesai') {
            return redirect()->route('evaluasi.hasil', $kode);
        }

        // Gabungkan jawaban yang sudah tersimpan dengan kiriman terakhir (jaga-jaga).
        $jawaban = $percobaan->jawaban ?: [];
        foreach ((array) $request->input('jawaban', []) as $nomor => $nilai) {
            if (trim((string) $nilai) !== '') {
                $jawaban[(int) $nomor] = mb_substr(trim((string) $nilai), 0, 500);
            }
        }

        // ---------------------------------------------------------------- alur 7 mode
        if ($this->petaMode7($percobaan)) {
            $percobaan->jawaban = $jawaban;
            $percobaan->save();
            $r = $this->ringkasMode7($percobaan->fresh());

            if ($r['dijawab'] < 1) {
                return redirect()->route('evaluasi.kerjakan', $kode)
                    ->with('pesan', 'Belum ada satu pun jawaban tersimpan. Kerjakan dulu salah satu mode, baru selesaikan sesinya.');
            }

            $percobaan->update([
                'benar' => $r['benar'],
                'salah' => $r['salah'],
                'nilai' => $r['nilai'],
                'status' => 'selesai',
                'selesai_pada' => now(),
            ]);

            $this->beriPoin($percobaan);

            return redirect()->route('evaluasi.hasil', $kode);
        }

        // ---------------------------------------------------------------- alur lama (tidak diubah)
        $hasil = SoalBee::nilai($percobaan->kunci ?: [], $jawaban);

        $percobaan->update([
            'jawaban' => $jawaban,
            'benar' => $hasil['benar'],
            'salah' => $hasil['salah'],
            'nilai' => $hasil['nilai'],
            'status' => 'selesai',
            'selesai_pada' => now(),
        ]);

        $this->beriPoin($percobaan);

        return redirect()->route('evaluasi.hasil', $kode);
    }

    /** Hasil + pembahasan (7 mode: nilai tiap mode + rata-rata). */
    public function hasil(string $kode)
    {
        $percobaan = BeeEvaluasiPercobaan::where('kode', $kode)->firstOrFail();

        if ($percobaan->status !== 'selesai') {
            return redirect()->route('evaluasi.kerjakan', $kode);
        }

        $siswa = Siswa::find($percobaan->siswa_id);
        $evaluasi = $percobaan->evaluasi;

        // Peringkat percobaan terbaik di periode yang sama (untuk konteks).
        $jumlahPeserta = BeeEvaluasiPercobaan::where('bee_evaluasi_id', $evaluasi->id)
            ->where('status', 'selesai')->distinct('siswa_id')->count('siswa_id');

        $peta7 = $this->petaMode7($percobaan);
        $ringkas = $peta7 ? $this->ringkasMode7($percobaan) : null;

        $nilai = $peta7
            ? $this->nilai7($percobaan)
            : SoalBee::nilai($percobaan->kunci ?: [], $percobaan->jawaban ?: []);

        // Bukti poin Student Root benar-benar masuk (sekali per periode per bahasa).
        $poin = $this->cekPoin($percobaan);

        return view('bee-smart.evaluasi.hasil', [
            'percobaan' => $percobaan,
            'nilai' => $nilai,
            'siswa' => $siswa,
            'evaluasi' => $evaluasi,
            'jumlahPeserta' => $jumlahPeserta,
            'mode7' => (bool) $peta7,
            'ringkas' => $ringkas,
            'infoMode' => self::infoMode(),
            'poin' => $poin,
        ]);
    }

    /** Memberi poin Student Root SEKALI per periode per bahasa bila lulus. */
    private function beriPoin(BeeEvaluasiPercobaan $percobaan): void
    {
        $evaluasi = $percobaan->evaluasi;
        if (! $evaluasi || $percobaan->nilai < $evaluasi->kkm || $evaluasi->poin_lulus < 1) {
            return;
        }

        $kriteria = SrPointCriteria::firstOrCreate(
            ['nama_perilaku' => self::KRITERIA],
            ['kategori' => 'positif', 'poin' => $evaluasi->poin_lulus, 'deskripsi' => 'Poin otomatis saat siswa lulus evaluasi BEE Smart.', 'tingkat' => null, 'status' => 'aktif'],
        );

        // Catatan TIDAK memuat nilainya, supaya percobaan ulang pada periode & bahasa
        // yang sama tidak menambah poin dua kali (sekali per periode per bahasa).
        $catatan = 'Lulus '.$evaluasi->judul.' — '.($percobaan->bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris');
        $sudahAda = DB::table('sr_point_entries')
            ->where('student_id', $percobaan->siswa_id)
            ->where('criteria_id', $kriteria->id)
            ->where('catatan', $catatan)
            ->whereNull('deleted_at')
            ->exists();

        if ($sudahAda) {
            return;
        }

        $anggota = DB::table('sr_group_members')
            ->where('student_id', $percobaan->siswa_id)
            ->whereNull('tanggal_keluar')
            ->first();

        DB::table('sr_point_entries')->insert([
            'id' => (string) Str::uuid(),
            'student_id' => $percobaan->siswa_id,
            'group_id' => $anggota->group_id ?? null,
            'criteria_id' => $kriteria->id,
            'poin' => $evaluasi->poin_lulus,
            'catatan' => $catatan,
            'input_by' => null,
            'tanggal_kejadian' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Apakah poin Student Root untuk percobaan ini sudah ada di basis data? */
    private function cekPoin(BeeEvaluasiPercobaan $percobaan): array
    {
        $evaluasi = $percobaan->evaluasi;
        $kriteria = SrPointCriteria::where('nama_perilaku', self::KRITERIA)->first();
        $catatan = 'Lulus '.($evaluasi->judul ?? '').' — '.($percobaan->bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris');
        $entri = $kriteria ? DB::table('sr_point_entries')
            ->where('student_id', $percobaan->siswa_id)
            ->where('criteria_id', $kriteria->id)
            ->where('catatan', $catatan)
            ->whereNull('deleted_at')
            ->first() : null;

        return [
            'ada' => (bool) $entri,
            'poin' => $entri->poin ?? ($evaluasi->poin_lulus ?? 0),
            'catatan' => $catatan,
        ];
    }

    // =============================================== MESIN 7 MODE (BANTUAN)

    /**
     * Peta butir percobaan 7 mode: [nomor => blok sub-sesi] + [nomor => jenis butir].
     *
     * `blok`  : sub-sesi mode yang MEMINTA butir itu (dipakai untuk mengelompokkan soal, nilai
     *           tiap mode, dan nyawa). Direkatkan controller saat `mulai()` karena `buatSesi()`
     *           menyusun soal blok per mode mengikuti urutan self::MODE7.
     * `jenis` : mode asli butir (`$s['mode']`) — dipakai memilih pembanding penilaian, supaya
     *           butir cadangan (mis. `pg_arti`) tetap dinilai dengan pembanding yang benar.
     *
     * @return array{blok: array<int,string>, jenis: array<int,string>} blok kosong = bukan sesi 7 mode
     */
    private function petaButir(BeeEvaluasiPercobaan $percobaan): array
    {
        $soal = $percobaan->soal ?: [];
        if (count($soal) < 3) {
            return ['blok' => [], 'jenis' => []];
        }

        $blok = [];
        $jenis = [];
        foreach ($soal as $s) {
            $nomor = (int) ($s['nomor'] ?? 0);
            if ($nomor < 1) {
                continue;
            }
            $b = $s['blok'] ?? null;
            if (is_string($b) && in_array($b, self::MODE7, true)) {
                $blok[$nomor] = $b;
            }
            $jenis[$nomor] = (is_string($s['mode'] ?? null) && $s['mode'] !== '') ? $s['mode'] : 'pg_arti';
        }

        // Sesi lama (mesin lama) tidak punya penanda blok → jalur lama.
        return count($blok) >= 3 && count($blok) === count($soal) ? ['blok' => $blok, 'jenis' => $jenis] : ['blok' => [], 'jenis' => []];
    }

    /** Peta [nomor => blok sub-sesi] (kosong = bukan percobaan 7 mode). */
    private function petaMode7(BeeEvaluasiPercobaan $percobaan): array
    {
        return $this->petaButir($percobaan)['blok'];
    }

    /**
     * Ringkasan keadaan tiap mode — dihitung ULANG dari yang tersimpan (tanpa kolom baru).
     *
     * MODE BISA DIULANG (BABAK): setelah 3 nyawa habis, siswa boleh memulai babak baru dari soal
     * pertama. Jawaban babak-babak yang sudah lewat disimpan di `jawaban['_babak'][mode]`, dan
     * **nilai mode = RATA-RATA nilai tiap babak** (tiap babak bernilai benar/20×100).
     *
     * @return array|null null bila bukan percobaan 7 mode
     */
    private function ringkasMode7(BeeEvaluasiPercobaan $percobaan): ?array
    {
        $peta = $this->petaButir($percobaan);
        if (! $peta['blok']) {
            return null;
        }
        $kunci = $percobaan->kunci ?: [];
        $jawaban = $percobaan->jawaban ?: [];
        $arsip = is_array($jawaban['_babak'] ?? null) ? $jawaban['_babak'] : [];

        // peta butir per nomor (dibutuhkan penilaian jodoh: jumlah pasangan tiap papan)
        $butirPeta = [];
        foreach (($percobaan->soal ?: []) as $b) {
            if (isset($b['nomor'])) { $butirPeta[(int) $b['nomor']] = $b; }
        }

        $modes = [];
        foreach (self::MODE7 as $m) {
            $modes[$m] = [
                'mode' => $m, 'nomor' => [], 'total' => 0, 'dijawab' => 0, 'benar' => 0, 'salah' => 0,
                'nyawa' => self::NYAWA, 'nilai' => 0, 'dinilai' => false, 'tuntas' => false, 'terjawab' => [],
                'benar_nomor' => [], 'babak' => 1, 'nilai_babak' => [], 'boleh_ulang' => false,
                'babak_habis' => false, 'dijawab_semua' => 0, 'benar_semua' => 0, 'salah_semua' => 0,
            ];
        }
        foreach ($peta['blok'] as $nomor => $m) {
            $modes[$m]['nomor'][] = $nomor;
        }

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $jumlahDijawab = 0;
        $jumlahTotal = 0;
        $jmlDinilai = 0;
        $jmlTuntas = 0;
        $jmlBabak = 0;

        foreach ($modes as $m => $x) {
            $nilaiBabak = [];

            // (a) babak-babak yang sudah selesai
            foreach (($arsip[$m] ?? []) as $babak) {
                $h = $this->hitungJawaban($x['nomor'], $peta['jenis'], $kunci, is_array($babak['jawaban'] ?? null) ? $babak['jawaban'] : [], $butirPeta);
                $nilaiBabak[] = $h['nilai'];
                $x['dijawab_semua'] += $h['dijawab'];
                $x['benar_semua'] += $h['benar'];
                $x['salah_semua'] += $h['salah'];
            }

            // (b) babak yang sedang berjalan
            $h = $this->hitungJawaban($x['nomor'], $peta['jenis'], $kunci, $jawaban, $butirPeta);
            $x['dijawab'] = $h['dijawab'];
            $x['benar'] = $h['benar'];
            $x['salah'] = $h['salah'];
            $x['terjawab'] = $h['terjawab'];
            $x['benar_nomor'] = $h['benar_nomor'];
            if ($h['dijawab'] > 0) {
                $nilaiBabak[] = $h['nilai'];
            }
            $x['dijawab_semua'] += $h['dijawab'];
            $x['benar_semua'] += $h['benar'];
            $x['salah_semua'] += $h['salah'];

            // total = pembagi nilai (mode jodoh memakai jumlah PASANGAN, bukan jumlah butir)
            $x['total'] = (int) ($h['total'] ?? count($x['nomor']));
            $x['jumlah_butir'] = count($x['nomor']);
            $x['tanpa_poin'] = (int) ($x['tanpa_poin'] ?? 0) + (int) ($h['tanpa_poin'] ?? 0);
            $x['babak'] = count($arsip[$m] ?? []) + 1;
            $x['nilai_babak'] = $nilaiBabak;
            $x['nilai'] = $nilaiBabak ? (int) round(array_sum($nilaiBabak) / count($nilaiBabak)) : 0;
            $x['nyawa'] = max(0, self::NYAWA - $x['salah']);
            $x['dinilai'] = $x['dijawab_semua'] > 0;
            $x['boleh_ulang'] = $x['dijawab'] > 0;
            $x['babak_habis'] = $x['boleh_ulang'] && $x['salah'] >= self::NYAWA;
            $x['tuntas'] = $x['dijawab'] > 0 && $x['dijawab'] >= $x['total'];

            $jumlahBenar += $x['benar_semua'];
            $jumlahSalah += $x['salah_semua'];
            $jumlahDijawab += $x['dijawab_semua'];
            $jumlahTotal += $x['total'];
            $jmlBabak += $x['babak'];
            if ($x['dinilai']) {
                $jmlDinilai++;
            }
            if ($x['tuntas']) {
                $jmlTuntas++;
            }
            $modes[$m] = $x;
        }

        // Nilai sesi bahasa = rata-rata nilai mode yang sudah dikerjakan (nilai mode = rata-rata babak).
        $rata = $jmlDinilai ? (int) round(array_sum(array_map(fn ($x) => $x['dinilai'] ? $x['nilai'] : 0, $modes)) / $jmlDinilai) : 0;

        return [
            'modes' => $modes,
            'nilai' => $rata,
            'rata' => $rata,
            'benar' => $jumlahBenar,
            'salah' => $jumlahSalah,
            'dijawab' => $jumlahDijawab,
            'total' => $jumlahTotal,
            'mode_dinilai' => $jmlDinilai,
            'mode_tuntas' => $jmlTuntas,
            'jumlah_babak' => $jmlBabak,
            // mode yang BENAR-BENAR ada di sesi ini (sesi lama bisa 7 mode, sesi baru 9 mode)
            'modes_pakai' => array_values(array_filter(array_keys($modes), fn ($m) => ($modes[$m]['total'] ?? 0) > 0)),
            'dinilai' => array_values(array_filter(array_keys($modes), fn ($m) => $modes[$m]['dinilai'])),
        ];
    }

    /** Hitung satu BABAK: dijawab/benar/salah + nilainya (benar ÷ 20 × 100). */
    private function hitungJawaban(array $nomorMode, array $jenis, array $kunci, array $jawaban, array $butir = []): array
    {
        $dijawab = 0;      // butir soal selesai (untuk jodoh: pasangan yang sudah terpasang)
        $benar = 0;        // jawaban benar (untuk jodoh: POIN yang didapat)
        $salah = 0;        // jawaban salah yang menghabiskan nyawa (jodoh: selalu 0)
        $pembagi = 0;      // pembagi nilai (untuk jodoh: jumlah pasangan, bukan jumlah butir)
        $tanpaPoin = 0;    // khusus jodoh: pasangan yang tidak dapat poin
        $terjawab = [];
        $benarNomor = [];

        foreach ($nomorMode as $nomor) {
            $jns = $jenis[$nomor] ?? 'pg_arti';
            $jawab = trim((string) ($jawaban[$nomor] ?? ''));

            if ($jns === 'jodoh') {
                // Papan jodoh dinilai PER PASANGAN: 1 pasangan benar di percobaan pertama = 1 poin.
                $papan = $this->jodohBaca($jawab);
                $jumlah = (int) ($butir[$nomor]['jumlah_pasangan'] ?? 0);
                if ($jumlah < 1) {
                    $k = preg_split('~\s+~', trim((string) ($kunci[$nomor] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $jumlah = count($k);
                }
                $poin = 0;
                $terpasang = 0;
                $hangus = 0;
                foreach ($papan as $entri) {
                    $st = (int) ($entri['status'] ?? 0);
                    if ($st === 1) { $poin++; }
                    if ($st === 1 || $st === 3) { $terpasang++; }
                    if ($st === 2 || $st === 3) { $hangus++; }   // dicoba tapi tidak dapat poin
                }
                $pembagi += max(1, $jumlah);
                $dijawab += $terpasang;
                $benar += $poin;
                $tanpaPoin += $hangus;
                if ($this->jodohLengkap((string) ($kunci[$nomor] ?? ''), $jawab)) { $terjawab[$nomor] = $jawab; }   // papan dianggap selesai bila SEMUA pasangan terpasang
                continue;
            }

            $pembagi += 1;
            if ($jawab === '') {
                continue;
            }
            $dijawab++;
            $terjawab[$nomor] = $jawab;
            if ($this->cocokButir($jns, (string) ($kunci[$nomor] ?? ''), $jawab)) {
                $benar++;
                $benarNomor[$nomor] = true;
            } else {
                $salah++;
            }
        }

        return [
            'dijawab' => $dijawab, 'benar' => $benar, 'salah' => $salah,
            'total' => max(1, $pembagi), 'tanpa_poin' => $tanpaPoin,
            'terjawab' => $terjawab, 'benar_nomor' => $benarNomor,
            'nilai' => (int) round($benar / max(1, $pembagi) * 100),
        ];
    }

    /**
     * Terjemahan bahasa Indonesia untuk butir "Lengkapi Kalimat" (pg_rumpang / drag_rumpang),
     * dipetakan per NOMOR SOAL pada sesi ini.
     *
     * Bank kosakata tidak menyimpan terjemahan kalimat, jadi terjemahan dibaca dari
     * `resources/data/arti-kalimat.json` (lihat App\Support\ArtiKalimat). Bila sebuah kalimat
     * belum punya terjemahan, butir itu cukup ditampilkan tanpa baris arti (tidak error).
     */
    private function artiKalimat7(BeeEvaluasiPercobaan $percobaan): array
    {
        $keluar = [];

        foreach (($percobaan->soal ?: []) as $butir) {
            $mode = (string) ($butir['blok'] ?? $butir['mode'] ?? '');
            if ($mode !== 'pg_rumpang' && $mode !== 'drag_rumpang') {
                continue;
            }

            // kata yang dihilangkan diambil dari kolom `kunci` (butir soal tidak menyimpan jawaban)
            $kalimat = ArtiKalimat::kalimatButir($butir, (string) ($percobaan->kunci[$butir['nomor'] ?? 0] ?? ''));
            $arti = $kalimat ? ArtiKalimat::cari($kalimat) : null;
            if ($arti !== null) {
                $keluar[(int) ($butir['nomor'] ?? 0)] = $arti;
            }
        }

        return $keluar;
    }

    /** Mulai BABAK BARU untuk satu mode: arsipkan jawaban babak berjalan, lalu kosongkan. */
    private function mulaiBabakBaru(BeeEvaluasiPercobaan $percobaan, string $mode, string $alasan)
    {
        if (! in_array($mode, self::MODE7, true)) {
            return response()->json(['ok' => false, 'pesan' => 'Mode tidak dikenal.']);
        }

        $peta = $this->petaButir($percobaan);
        $jawaban = $percobaan->jawaban ?: [];
        $kunci = $percobaan->kunci ?: [];
        $nomorMode = array_keys(array_filter($peta['blok'], fn ($b) => $b === $mode));

        $kini = [];
        foreach ($nomorMode as $nomor) {
            if (isset($jawaban[$nomor]) && trim((string) $jawaban[$nomor]) !== '') {
                $kini[$nomor] = (string) $jawaban[$nomor];
            }
        }
        if ($kini === []) {
            return response()->json([
                'ok' => false, 'mode' => $mode,
                'pesan' => 'Babak ini belum ada jawabannya — tinggal dikerjakan.',
            ]);
        }

        $arsip = is_array($jawaban['_babak'] ?? null) ? $jawaban['_babak'] : [];
        $arsip[$mode][] = [
            'jawaban' => $kini,
            'alasan' => in_array($alasan, ['nyawa', 'ulang'], true) ? $alasan : 'ulang',
            'pada' => now()->toDateTimeString(),
        ];
        foreach (array_keys($kini) as $nomor) {
            unset($jawaban[$nomor]);
        }
        $jawaban['_babak'] = $arsip;

        $percobaan->jawaban = $jawaban;
        $percobaan->save();

        $setelah = $this->ringkasMode7($percobaan->fresh());
        $r = $setelah['modes'][$mode];

        return response()->json([
            'ok' => true, 'aksi' => 'babak', 'mode' => $mode,
            'babak' => $r['babak'], 'nilai_babak' => $r['nilai_babak'], 'nilai_mode' => $r['nilai'],
            'nyawa' => $r['nyawa'], 'dijawab' => $r['dijawab'], 'total' => $r['total'],
            'tuntas' => $r['tuntas'], 'boleh_ulang' => $r['boleh_ulang'],
            'pesan' => 'Babak '.$r['babak'].' dimulai dari soal pertama. Nilai mode = rata-rata semua babak.',
        ]);
    }

    /** Rincian per soal untuk halaman hasil (mode-aware, termasuk kemiripan `ucap`). */
    private function nilai7(BeeEvaluasiPercobaan $percobaan): array
    {
        $kunci = $percobaan->kunci ?: [];
        $jawaban = $percobaan->jawaban ?: [];
        $peta = $this->petaButir($percobaan);

        $benar = 0;
        $salah = 0;
        $rincian = [];

        foreach ($kunci as $nomor => $kunciButir) {
            // jawaban yang ditampilkan = jawaban TERAKHIR siswa untuk soal itu (babak terbaru yang mengisinya)
            $j = $this->jawabanTerakhir($jawaban, (int) $nomor);
            $mode = $peta['blok'][$nomor] ?? null;
            $jns = $peta['jenis'][$nomor] ?? null;
            $cocok = $j !== null && trim((string) $j) !== ''
                && (is_string($jns) ? $this->cocokButir($jns, (string) $kunciButir, (string) $j) : false);
            $cocok ? $benar++ : $salah++;
            $rincian[$nomor] = ['jawaban' => $j, 'kunci' => $kunciButir, 'benar' => $cocok, 'mode' => $mode];
        }

        return ['benar' => $benar, 'salah' => $salah, 'nilai' => $percobaan->nilai, 'rincian' => $rincian];
    }

    /** Jawaban terakhir siswa untuk satu nomor: babak yang sedang berjalan, lalu babak-babak arsip (terbaru menang). */
    private function jawabanTerakhir(array $jawaban, int $nomor): ?string
    {
        if (isset($jawaban[$nomor]) && trim((string) $jawaban[$nomor]) !== '') {
            return (string) $jawaban[$nomor];
        }

        $arsip = is_array($jawaban['_babak'] ?? null) ? $jawaban['_babak'] : [];
        $ketemu = null;
        foreach ($arsip as $daftar) {
            foreach ($daftar as $babak) {
                $isi = is_array($babak['jawaban'] ?? null) ? $babak['jawaban'] : [];
                if (isset($isi[$nomor]) && trim((string) $isi[$nomor]) !== '') {
                    $ketemu = (string) $isi[$nomor];
                }
            }
        }

        return $ketemu;
    }

    /** Benar/salah satu butir menurut modenya (kunci TIDAK pernah dikirim ke peramban). */
    private function cocokButir(string $mode, string $kunci, string $jawaban): bool
    {
        if ($mode === 'ucap') {
            return $this->mirip($kunci, $jawaban) >= self::MIRIP_UCAP;
        }

        // sisanya diserahkan ke mesin soal (susun_kata persis; lain longgar)
        return (bool) SoalBee::nilai([1 => $kunci], [1 => $jawaban], [1 => $mode])['benar'];
    }

    /** Kemiripan 0..1: huruf kecil, tanda baca/harakat dibuang, Levenshtein per karakter. */
    private function mirip(string $a, string $b): float
    {
        $rapi = function (string $t): string {
            $t = mb_strtolower(trim($t));
            $t = (string) preg_replace('~[^\p{L}\p{N}\s]~u', ' ', $t);

            return trim((string) preg_replace('~\s+~u', ' ', $t));
        };

        $x = $rapi($a);
        $y = $rapi($b);

        if ($x === '' || $y === '') {
            return 0.0;
        }
        if ($x === $y) {
            return 1.0;
        }

        $p = preg_split('//u', $x, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $q = preg_split('//u', $y, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $n = count($p);
        $m = count($q);

        if ($n === 0 || $m === 0) {
            return 0.0;
        }

        $sebelum = range(0, $m);
        for ($i = 1; $i <= $n; $i++) {
            $kini = [$i];
            for ($j = 1; $j <= $m; $j++) {
                $biaya = ($p[$i - 1] === $q[$j - 1]) ? 0 : 1;
                $kini[$j] = min($sebelum[$j] + 1, $kini[$j - 1] + 1, $sebelum[$j - 1] + $biaya);
            }
            $sebelum = $kini;
        }

        return 1 - ($sebelum[$m] / max($n, $m));
    }

    /** Kunci jodoh ("3 5 1 2 4") sudah terisi lengkap? */
    private function jodohLengkap(string $kunci, string $jawaban): bool
    {
        $k = preg_split('~\s+~', trim($kunci), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($k === []) {
            return false;
        }

        $papan = $this->jodohBaca($jawaban);
        foreach (array_keys($k) as $i) {
            $st = (int) ($papan[$i + 1]['status'] ?? 0);
            if ($st !== 1 && $st !== 3) {
                return false;   // masih ada kartu kiri yang belum terpasang
            }
        }

        return true;
    }

    /**
     * Baca jawaban papan jodoh versi 2: entri "kiri-kanan-status" dipisah spasi.
     * status: 1 = pasangan benar di percobaan pertama (dapat poin, lenyap dari papan),
     *         2 = percobaan pertama salah (hangus — boleh dicoba lagi, tanpa poin, kartu tetap tampil),
     *         3 = akhirnya terpasang setelah sempat salah (tanpa poin, lenyap dari papan).
     *
     * @return array<int, array{kanan:int, status:int}>
     */
    private function jodohBaca(string $jawaban): array
    {
        $keluar = [];
        foreach (preg_split('~\s+~', trim($jawaban), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $potongan) {
            $bagian = explode('-', $potongan);
            if (count($bagian) !== 3) {
                continue;
            }
            [$kiri, $kanan, $status] = $bagian;
            if (! ctype_digit($kiri) || ! ctype_digit($kanan) || ! ctype_digit($status)) {
                continue;
            }
            $keluar[(int) $kiri] = ['kanan' => (int) $kanan, 'status' => (int) $status];
        }

        return $keluar;
    }

    /** Tulis kembali entri papan jodoh ke bentuk string "kiri-kanan-status" (urut kiri menaik). */
    private function jodohTulis(array $entri): string
    {
        ksort($entri);
        $bagian = [];
        foreach ($entri as $kiri => $x) {
            $bagian[] = ((int) $kiri).'-'.((int) ($x['kanan'] ?? 0)).'-'.((int) ($x['status'] ?? 0));
        }

        return implode(' ', $bagian);
    }

    /**
     * Satu KETUKAN pada papan jodoh (versi 2): kiri = kartu kiri (1..n), kanan = nomor kolom kanan.
     * Benar di percobaan pertama → pasangan lenyap + 1 poin. Salah → pasangan "hangus" (boleh dicoba
     * lagi sampai terpasang, tapi tanpa poin) dan NYAWA TIDAK BERKURANG. Semua tercatat di kolom
     * `jawaban` yang sudah ada, jadi papan tetap benar setelah siswa menutup & membuka lagi halamannya.
     */
    private function jodohKetuk(BeeEvaluasiPercobaan $percobaan, string $mode, int $nomor, Request $request)
    {
        $isi = $percobaan->jawaban ?: [];
        $kunci = preg_split('~\s+~', trim((string) ($percobaan->kunci[$nomor] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $butirPeta = [];
        foreach (($percobaan->soal ?: []) as $b) {
            if (isset($b['nomor'])) { $butirPeta[(int) $b['nomor']] = $b; }
        }
        $jumlah = (int) ($butirPeta[$nomor]['jumlah_pasangan'] ?? count($kunci));

        $kiri = (int) $request->input('kiri');
        $kanan = (int) $request->input('kanan');
        if ($jumlah < 1 || $kiri < 1 || $kiri > $jumlah || $kanan < 1 || $kanan > $jumlah) {
            return response()->json(['ok' => false, 'pesan' => 'Ketukan tidak dikenali.', 'mode' => $mode]);
        }

        $entri = $this->jodohBaca((string) ($isi[$nomor] ?? ''));
        $statusKini = (int) ($entri[$kiri]['status'] ?? 0);
        $benarPasangan = ((int) ($kunci[$kiri - 1] ?? 0) === $kanan);

        if ($benarPasangan) {
            if ($statusKini === 1 || $statusKini === 3) {
                $status = $statusKini;                                  // sudah selesai → jangan dihitung dua kali
            } elseif ($statusKini === 2) {
                $entri[$kiri] = ['kanan' => $kanan, 'status' => 3];     // dulu salah → terpasang tanpa poin
                $status = 3;
            } else {
                $entri[$kiri] = ['kanan' => $kanan, 'status' => 1];     // percobaan pertama benar → 1 poin
                $status = 1;
            }
        } else {
            if ($statusKini === 0) {
                $entri[$kiri] = ['kanan' => $kanan, 'status' => 2];     // hangus, boleh dicoba lagi
            }
            $status = 2;
        }

        $isi[$nomor] = mb_substr($this->jodohTulis($entri), 0, 500);
        $percobaan->jawaban = $isi;
        $percobaan->save();

        $setelah = $this->ringkasMode7($percobaan->fresh());
        $rs = $setelah['modes'][$mode];

        return response()->json([
            'ok' => true, 'ketuk' => true, 'mode' => $mode, 'nomor' => $nomor,
            'pasangan_benar' => $benarPasangan, 'status' => $status, 'dapat_poin' => ($status === 1),
            'papan' => $entri,
            'nyawa' => $rs['nyawa'], 'dijawab' => $rs['dijawab'], 'total' => $rs['total'],
            'nilai_mode' => $rs['nilai'], 'tuntas' => $rs['tuntas'], 'berhenti' => false,
            'tanpa_poin' => $rs['tanpa_poin'] ?? 0, 'babak' => $rs['babak'], 'nilai_babak' => $rs['nilai_babak'],
            'boleh_ulang' => $rs['boleh_ulang'], 'terjawab' => $setelah['terjawab'] ?? [],
        ]);
    }

    /** Apakah pasangan pada posisi ke-$posisi sudah benar? (umpan balik per ketukan jodoh) */
    private function jodohPosisiBenar(string $kunci, int $posisi, string $jawaban): bool
    {
        $k = preg_split('~\s+~', trim($kunci), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $j = preg_split('~\s+~', trim($jawaban), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($posisi < 1 || ! isset($k[$posisi - 1], $j[$posisi - 1])) {
            return false;
        }

        return (string) $k[$posisi - 1] === (string) $j[$posisi - 1];
    }

    // =========================================================== SISI GURU

    /** Daftar periode evaluasi + formulir. */
    public function daftar()
    {
        // Jumlah kosakata per modul + berapa yang SUDAH LENGKAP (Inggris DAN Arab).
        // Modul hanya layak diujikan bila kosakatanya lengkap dua bahasa — ini yang ditampilkan ke guru.
        $jumlahKosakata = \App\Models\BeeVocab::query()
            ->selectRaw("bee_week_id, COUNT(*) as jml,
                SUM(CASE WHEN COALESCE(vocab_en,'') <> '' AND COALESCE(mufrodat_ar,'') <> '' THEN 1 ELSE 0 END) as siap,
                SUM(CASE WHEN COALESCE(vocab_en,'') = '' OR COALESCE(mufrodat_ar,'') = '' THEN 1 ELSE 0 END) as kurang")
            ->groupBy('bee_week_id')->get()->keyBy('bee_week_id');

        // Daftar kelas dibaca dari DATA SISWA — kelas baru yang ditambahkan di sana otomatis muncul.
        $kelasList = \App\Models\Siswa::query()->whereNull('deleted_at')
            ->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas')->all();

        // Ringkasan tiap sesi: untuk laporan silang antar kelas dalam satu periode.
        $ringkasSesi = [];
        foreach (BeeEvaluasi::orderByDesc('mulai')->get() as $e) {
            $top = $e->percobaan->groupBy('siswa_id')->map(fn ($x) => $x->sortByDesc('nilai')->first());
            $ringkasSesi[$e->id] = [
                'peserta' => $top->count(),
                'lulus' => $top->filter(fn ($x) => $x->nilai >= $e->kkm)->count(),
                'rata' => $top->count() ? (int) round($top->avg('nilai')) : 0,
            ];
        }

        return view('bee-smart.evaluasi.daftar', [
            'evaluasi' => BeeEvaluasi::orderByDesc('mulai')->get(),
            'jumlahKosakata' => $jumlahKosakata,
            'kelasList' => $kelasList,
            'ringkasSesi' => $ringkasSesi,
            'minggu' => BeeWeek::orderByDesc('tanggal_mulai')->get(['id', 'judul', 'status', 'tanggal_mulai']),
            'pesan' => session('pesan'),
            'tab' => request()->query('ubah'),
        ]);
    }

    /** Menyalin sesi (mis. dari kelas X ke kelas XI) — tinggal ganti kelas & centangan modulnya. */
    public function salin(Request $request, int $id)
    {
        $asli = BeeEvaluasi::findOrFail($id);
        $baru = $asli->replicate();
        $baru->judul = $asli->judul.' (salinan)';
        $baru->aktif = false;   // sengaja mati dulu supaya tidak ikut terbuka sebelum diperiksa
        $baru->save();

        return redirect()->route('bee.evaluasi', ['ubah' => $baru->id])
            ->with('pesan', 'Sesi disalin. Ganti kelas & centangan modulnya, lalu simpan dan nyalakan Aktif.');
    }

    public function simpanPeriode(Request $request)
    {
        $data = $this->validasi($request);

        if ($id = $request->input('id')) {
            BeeEvaluasi::findOrFail($id)->update($data);
            $pesan = 'Periode evaluasi diperbarui.';
        } else {
            $data['dibuat_oleh'] = auth()->id();
            BeeEvaluasi::create($data);
            $pesan = 'Periode evaluasi dibuat. Buka saklar "Aktif" saat siap dikerjakan siswa.';
        }

        return redirect()->route('bee.evaluasi')->with('pesan', $pesan);
    }

    public function hapus($id)
    {
        $e = BeeEvaluasi::findOrFail($id);

        if (BeeEvaluasiPercobaan::where('bee_evaluasi_id', $e->id)->exists()) {
            return redirect()->route('bee.evaluasi')->with('pesan', 'Tidak bisa dihapus: sudah ada siswa yang mengerjakan. Matikan saja saklar Aktif.');
        }

        $e->delete();

        return redirect()->route('bee.evaluasi')->with('pesan', 'Periode evaluasi dihapus.');
    }

    /** Hasil per periode (nilai terbaik tiap siswa). */
    public function hasilGuru($id)
    {
        $evaluasi = BeeEvaluasi::findOrFail($id);

        $percobaan = BeeEvaluasiPercobaan::where('bee_evaluasi_id', $id)
            ->orderByDesc('nilai')->orderByDesc('selesai_pada')->get();

        $terbaik = $percobaan->groupBy('siswa_id')->map(fn ($baris) => $baris->sortByDesc('nilai')->first());

        // Ringkasan per bahasa (sesi terpisah).
        $perBahasa = [];
        foreach (['inggris', 'arab'] as $b) {
            $bhs = $percobaan->where('bahasa', $b);
            $top = $bhs->groupBy('siswa_id')->map(fn ($x) => $x->sortByDesc('nilai')->first());
            $perBahasa[$b] = [
                'peserta' => $top->count(),
                'lulus' => $top->filter(fn ($p) => $p->nilai >= $evaluasi->kkm)->count(),
                'rata' => $top->count() ? (int) round($top->avg('nilai')) : 0,
            ];
        }
        $siswa = Siswa::query()->whereIn('id', $terbaik->keys())->get()->keyBy('id');

        $sudah = $terbaik->count();
        $lulus = $terbaik->filter(fn ($p) => $p->nilai >= $evaluasi->kkm)->count();
        $rata = $terbaik->count() ? (int) round($terbaik->avg('nilai')) : 0;

        return view('bee-smart.evaluasi.hasil-guru', [
            'evaluasi' => $evaluasi,
            'terbaik' => $terbaik,
            'siswa' => $siswa,
            'jumlahPercobaan' => $percobaan->count(),
            'ringkas' => ['sudah' => $sudah, 'lulus' => $lulus, 'rata' => $rata],
            'perBahasa' => $perBahasa,
            'infoMode' => self::infoMode(),
        ]);
    }

    /** Unduh hasil sebagai CSV. */
    public function hasilEkspor($id)
    {
        $evaluasi = BeeEvaluasi::findOrFail($id);
        $percobaan = BeeEvaluasiPercobaan::where('bee_evaluasi_id', $id)->orderByDesc('nilai')->get();
        $terbaik = $percobaan->groupBy('siswa_id')->map(fn ($b) => $b->sortByDesc('nilai')->first());
        $siswa = Siswa::query()->whereIn('id', $terbaik->keys())->get()->keyBy('id');

        $keluar = "\xEF\xBB\xBF";
        $keluar .= 'Evaluasi;'.$evaluasi->judul."\n";
        $keluar .= 'Periode;'.$evaluasi->mulai->format('d/m/Y').' – '.$evaluasi->selesai->format('d/m/Y')."\n";
        $keluar .= 'KKM;'.$evaluasi->kkm."\n\n";
        $keluar .= "Nama;Kelas;NIS;Nilai terbaik;Percobaan;Lulus\n";

        foreach ($terbaik->sortByDesc('nilai') as $p) {
            $s = $siswa->get($p->siswa_id);
            $jml = $percobaan->where('siswa_id', $p->siswa_id)->count();
            $keluar .= $this->sel($s->nama_lengkap ?? '(tidak ada)').';'.$this->sel($s->kelas ?? '-').';'
                .$this->sel($s->nis ?? '-').';'.$p->nilai.';'.$jml.';'
                .($p->nilai >= $evaluasi->kkm ? 'Lulus' : 'Belum lulus')."\n";
        }

        return response($keluar, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="hasil-evaluasi-bee-'.$id.'-'.date('Ymd-His').'.csv"',
        ]);
    }

    /** Membuka ulang satu siswa (untuk yang terkendala). */
    public function bukaUlang($id, $siswaId)
    {
        $hapus = BeeEvaluasiPercobaan::where('bee_evaluasi_id', $id)->where('siswa_id', $siswaId)->delete();

        return redirect()->route('bee.evaluasi.hasil', $id)
            ->with('pesan', $hapus ? 'Percobaan siswa itu dihapus — dia bisa mengerjakan lagi dari awal.' : 'Tidak ada percobaan siswa itu.');
    }

    private function sel($nilai): string
    {
        $nilai = str_replace('"', '""', (string) $nilai);

        return (str_contains($nilai, ';') || str_contains($nilai, '"')) ? '"'.$nilai.'"' : $nilai;
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:160'],
            'jenis' => ['required', 'in:triwulan,semester'],
            'kelas' => ['required', 'string', 'max:20'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'integer', 'in:1,2'],
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after_or_equal:mulai'],
            'jumlah_soal' => ['required', 'integer', 'min:5', 'max:100'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:180'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'poin_lulus' => ['required', 'integer', 'min:0', 'max:20'],
            'maks_percobaan' => ['required', 'integer', 'min:1', 'max:5'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $data['aktif'] = $request->boolean('aktif');
        // Saklar "otomatis dari rentang tanggal" → modul = null.
        // Kalau memilih modul, simpan daftarnya (boleh banyak modul sekaligus).
        $data['modul'] = $request->boolean('modul_otomatis')
            ? null
            : (collect((array) $request->input('modul', []))->filter()->map('intval')->values()->all() ?: null);

        return $data;
    }
}
