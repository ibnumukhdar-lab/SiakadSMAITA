<?php

namespace App\Support;

use App\Models\BeeVocab;
use Illuminate\Support\Str;

/**
 * Mesin pembuat soal evaluasi BEE Smart.
 *
 * Menghasilkan soal + kunci jawaban TERPISAH: yang dikirim ke peramban hanya 'soal'
 * (tanpa jawaban), kuncinya disimpan di kolom kunci pada percobaan. Penilaian selalu
 * di server.
 *
 * SESI TERPISAH (permintaan Fahri): satu percobaan punya satu BAHASA.
 *   Sesi INGGRIS  → pg_arti (Arab→Inggris) · susun_huruf · susun_kata · dengar
 *   Sesi ARAB     → pg_arti_ar (Inggris→Arab) · susun_kata_ar (jumlah_ar) · dengar_ar
 * Susun huruf TIDAK dipakai untuk Arab (huruf bersambung, jika diacak jadi tak terbaca).
 *
 * MESIN 7 MODE (SoalBee::buatSesi) — mengikuti latihan kosakata bilarabiya.online:
 *   pg_rumpang   kalimat berlubang → pilih 1 dari 4 kata (kunci = kata yang dihilangkan)
 *   drag_rumpang sama dengan pg_rumpang, ditandai 'seret' agar UI bisa merender berbeda
 *   susun_kata   balok kata diacak (kunci = kalimat utuh, dibandingkan PERSIS)
 *   imla_rumpang audio kata + kalimat berlubang → siswa MENGETIK kata yang hilang
 *   imla_murni   audio kata → siswa MENGETIK kata utuh (hanya baris yang PUNYA audio)
 *   ucap         uji pelafalan: kata + audio diucapkan; dinilai di peramban, kunci tetap disimpan
 *   jodoh        menjodohkan Indonesia ⇄ bahasa sasaran (5 pasang/butir)
 *
 * Pemetaan kolom (bee_vocabs): kosakata_id = kata INDONESIA · vocab_en/sentence_en/audio_vocab_en/
 * audio_sentence_en (sesi Inggris) · mufrodat_ar/jumlah_ar/audio_mufrodat_ar/audio_jumlah_ar (sesi Arab).
 * Jalur audio di peramban: /berkas/<nilai kolom audio_...>.
 */
class SoalBee
{
    public const MODE = ['pg_arti', 'susun_huruf', 'susun_kata', 'dengar'];

    public const MODE_AR = ['pg_arti_ar', 'susun_kata_ar', 'dengar_ar'];

    /**
     * Mode mesin baru yang boleh diminta lewat buatSesi().
     * Dua mode tambahan (21 Sep, permintaan Fahri): 'pg_kosakata' (kosakata satuan pilihan ganda
     * TANPA audio) dan 'dengar_kata' (audio kata TANPA tulisan katanya, siswa memilih kata tertulis).
     */
    /** Urutan sama dengan BeeEvaluasiController::MODE7 (paling mudah → paling sulit). */
    public const MODE_BARU = [
        'pg_kosakata', 'dengar_kata', 'jodoh', 'ucap', 'imla_murni',
        'pg_rumpang', 'drag_rumpang', 'susun_kata', 'imla_rumpang',
    ];

    /**
     * Rantai cadangan per mode: bila data utama kosong untuk satu baris, coba mode lain
     * yang datanya ADA, dan jangan sampai jumlah soal kurang dari yang diminta.
     * 'pg_arti' di sini = "pilih padanan bahasa sasaran dari kata Indonesia" (penutup terakhir).
     */
    private const RANTAI = [
        'pg_kosakata'  => ['pg_kosakata', 'pg_arti'],
        'dengar_kata'  => ['dengar_kata', 'pg_kosakata', 'pg_arti'],
        'pg_rumpang'   => ['pg_rumpang', 'susun_kata', 'imla_murni', 'pg_arti'],
        'drag_rumpang' => ['drag_rumpang', 'pg_rumpang', 'susun_kata', 'pg_arti'],
        'susun_kata'   => ['susun_kata', 'pg_rumpang', 'imla_murni', 'pg_arti'],
        'imla_rumpang' => ['imla_rumpang', 'imla_murni', 'pg_rumpang', 'pg_arti'],
        'imla_murni'   => ['imla_murni', 'imla_rumpang', 'pg_rumpang', 'pg_arti'],
        'ucap'         => ['ucap', 'imla_murni', 'pg_rumpang', 'pg_arti'],
        'jodoh'        => ['jodoh', 'pg_rumpang', 'pg_arti'],
    ];

    /**
     * Jodohkan (21 Sep, permintaan Fahri): papan berisi 10 PASANG.
     * Satu mode jodoh = 4 papan: sesi 1 (2 fase, teks→teks) + sesi 2 (2 fase, audio→teks),
     * jadi 40 pasang; tiap pasangan bernilai 1 poin.
     */
    private const PASANGAN_JODOH = 10;

    private const MIN_PASANGAN_JODOH = 2;

    private const LUBANG = '____';

    // ==================================================================================
    //  MESIN LAMA — tanda tangan & perilaku DIJAGA apa adanya.
    // ==================================================================================

    /**
     * Membuat daftar soal untuk satu sesi bahasa (mesin lama).
     *
     * @param  int    $jumlah  banyak soal
     * @param  array  $modulId daftar bee_week_id yang dipilih guru (kosong = seluruh bank)
     * @param  string $bahasa  'inggris' atau 'arab'
     * @return array{soal: array, kunci: array}
     */
    public static function buat(int $jumlah, array $modulId = [], string $bahasa = 'inggris'): array
    {
        $dasar = BeeVocab::query()
            ->when($modulId, fn ($q) => $q->whereIn('bee_week_id', $modulId))
            ->whereNotNull('vocab_en')->where('vocab_en', '!=', '')
            ->get();

        // Guru memilih modul tertentu → pakai modul itu APA ADANYA (soal boleh mengulang
        // kosakata yang sama dengan mode berbeda). Cadangan ke seluruh bank hanya bila
        // guru memang tidak memilih modul apa pun.
        if ($modulId === [] && $dasar->count() < 8) {
            $dasar = BeeVocab::query()
                ->whereNotNull('vocab_en')->where('vocab_en', '!=', '')
                ->get();
        }

        if ($dasar->isEmpty()) {
            return ['soal' => [], 'kunci' => []];
        }

        // Sesi Arab hanya memakai kosakata yang PUNYA padanan Arab; sesi Inggris wajib punya vocab_en.
        // Tanpa ini, kosakata tanpa padanan Arab terbuang satu per satu dan soal bisa kurang.
        if ($bahasa === 'arab') {
            $dasar = $dasar->filter(fn ($v) => filled($v->mufrodat_ar))->values();
            if ($dasar->isEmpty()) {
                return ['soal' => [], 'kunci' => []];
            }
        }

        $bankKata = $dasar->pluck('vocab_en')->filter()->unique()->values();
        $bankArabKata = $dasar->pluck('mufrodat_ar')->filter()->unique()->values();
        $modeDipakai = $bahasa === 'arab' ? self::MODE_AR : self::MODE;
        $modeCadangan = $bahasa === 'arab' ? 'pg_arti_ar' : 'pg_arti';

        $kumpul = [];

        $pilih = $dasar->shuffle();
        for ($i = 0; $i < $jumlah; $i++) {
            $v = $pilih[$i % $pilih->count()];
            $mode = $modeDipakai[$i % count($modeDipakai)];
            $nomor = $i + 1;

            // Coba mode yang seharusnya, lalu mode lain dalam sesi yang sama, terakhir mode cadangan.
            // Banyak kosakata belum punya padanan Arab/audio — tanpa rantai ini jumlah soal bisa kurang.
            $butir = null;
            foreach (array_merge([$mode], array_diff($modeDipakai, [$mode]), [$modeCadangan]) as $coba) {
                $butir = self::butir($coba, $v, $bankKata, $bankArabKata);
                if ($butir !== null) {
                    break;
                }
            }
            if ($butir === null) {
                continue;
            }

            $butir['nomor'] = $nomor;
            $kumpul[] = ['butir' => $butir, 'jawaban' => $butir['jawaban']];
        }

        // Penomoran dirapikan 1..N — ada butir yang bisa terlewat bila datanya tidak mendukung.
        $soal = [];
        $kunci = [];
        foreach ($kumpul as $i => $baris) {
            $n = $i + 1;
            $baris['butir']['nomor'] = $n;
            $kunci[$n] = $baris['jawaban'];
            unset($baris['butir']['jawaban']);   // jangan pernah dikirim ke peramban
            $soal[] = $baris['butir'];
        }

        return ['soal' => $soal, 'kunci' => $kunci];
    }

    /** Satu butir soal untuk satu baris kosakata (mesin lama). */
    private static function butir(string $mode, BeeVocab $v, $bankKata, $bankArabKata): ?array
    {
        $en = trim((string) $v->vocab_en);
        $ar = trim((string) $v->mufrodat_ar);
        $kalimat = trim((string) $v->sentence_en);

        return match ($mode) {
            'pg_arti' => self::pgArti($v, $en, $ar, $bankKata),
            'susun_huruf' => self::susunHuruf($v, $en),
            'susun_kata' => self::susunKata($v, $kalimat),
            'dengar' => self::dengar($v, $en, $kalimat, $bankKata),
            'pg_arti_ar' => self::pgArtiAr($v, $en, $ar, $bankArabKata),
            'susun_kata_ar' => self::susunKataAr($v),
            'dengar_ar' => self::dengarAr($v, $ar, $bankArabKata),
            default => null,
        };
    }

    // ===================== SESI BAHASA INGGRIS =====================

    private static function pgArti(BeeVocab $v, string $en, string $ar, $bankKata): ?array
    {
        if ($en === '' || $ar === '') {
            return null;
        }

        $pilihan = [$en];
        foreach ($bankKata->shuffle() as $kata) {
            if (count($pilihan) >= 4) { break; }
            if (strcasecmp((string) $kata, $en) !== 0) { $pilihan[] = (string) $kata; }
        }
        if (count($pilihan) < 3) {
            return null;
        }

        return [
            'mode' => 'pg_arti',
            'petunjuk' => 'Pilih arti bahasa Inggris yang tepat untuk kata Arab berikut.',
            'tanya' => $ar,
            'tanya_arab' => true,
            'pilihan' => collect($pilihan)->shuffle()->values()->all(),
            'jawaban' => $en,
        ];
    }

    private static function susunHuruf(BeeVocab $v, string $en): ?array
    {
        // Hanya kosakata satu kata dengan panjang wajar: frasa panjang kalau diacak tak terbaca.
        if (str_word_count($en) > 1) {
            return null;
        }
        $bersih = preg_replace('~[^a-zA-Z]~', '', $en);
        if (strlen((string) $bersih) < 4 || strlen((string) $bersih) > 14) {
            return null;
        }

        $huruf = str_split($bersih);
        shuffle($huruf);

        return [
            'mode' => 'susun_huruf',
            'petunjuk' => 'Susun huruf-huruf ini menjadi kata yang benar.',
            'tanya' => $v->mufrodat_ar ?: 'Susun kata',
            'huruf' => $huruf,
            'jawaban' => $bersih,
        ];
    }

    private static function susunKata(BeeVocab $v, string $kalimat): ?array
    {
        if ($kalimat === '' || str_word_count($kalimat) < 3) {
            return null;
        }

        $kata = preg_split('~\s+~', trim($kalimat));
        $acak = $kata;
        shuffle($acak);

        return [
            'mode' => 'susun_kata',
            'petunjuk' => 'Susun kata-kata berikut menjadi kalimat yang benar.',
            'tanya' => 'Susun kalimat',
            'kata' => array_values($acak),
            'jumlah_kata' => count($kata),
            'jawaban' => implode(' ', $kata),
        ];
    }

    private static function dengar(BeeVocab $v, string $en, string $kalimat, $bankKata): ?array
    {
        $audio = trim((string) $v->audio_vocab_en);
        if ($audio === '' || $en === '') {
            return null;
        }

        $pilihan = [$en];
        foreach ($bankKata->shuffle() as $kata) {
            if (count($pilihan) >= 4) { break; }
            if (strcasecmp((string) $kata, $en) !== 0) { $pilihan[] = (string) $kata; }
        }
        if (count($pilihan) < 3) {
            return null;
        }

        return [
            'mode' => 'dengar',
            'petunjuk' => 'Dengarkan audio, lalu pilih kata yang tepat.',
            'tanya' => '🎧 Audio',
            'audio' => $audio,
            'petunjuk_kalimat' => $kalimat ?: null,
            'pilihan' => collect($pilihan)->shuffle()->values()->all(),
            'jawaban' => $en,
        ];
    }

    // ===================== SESI BAHASA ARAB =====================

    /** Kata Inggris ditampilkan, siswa memilih padanan Arabnya. */
    private static function pgArtiAr(BeeVocab $v, string $en, string $ar, $bankArabKata): ?array
    {
        if ($en === '' || $ar === '') {
            return null;
        }

        $pilihan = [$ar];
        foreach ($bankArabKata->shuffle() as $kata) {
            if (count($pilihan) >= 4) { break; }
            if (trim((string) $kata) !== $ar) { $pilihan[] = (string) $kata; }
        }
        if (count($pilihan) < 3) {
            return null;
        }

        return [
            'mode' => 'pg_arti_ar',
            'petunjuk' => 'Pilih padanan bahasa Arab yang tepat untuk kata berikut.',
            'tanya' => $en,
            'pilihan' => collect($pilihan)->shuffle()->values()->all(),
            'pilihan_arab' => true,
            'jawaban' => $ar,
        ];
    }

    /** Menyusun kalimat Arab (jumlah_ar) dari kata-kata yang diacak. */
    private static function susunKataAr(BeeVocab $v): ?array
    {
        $kalimat = trim((string) ($v->jumlah_ar ?? ''));
        if ($kalimat === '' || count(preg_split('~\s+~', $kalimat)) < 3) {
            return null;
        }

        $kata = preg_split('~\s+~', $kalimat);
        $acak = $kata;
        shuffle($acak);

        return [
            'mode' => 'susun_kata_ar',
            'petunjuk' => 'Susun kata-kata berikut menjadi kalimat Arab yang benar.',
            'tanya' => 'Susun kalimat Arab',
            'kata' => array_values($acak),
            'kata_arab' => true,
            'jumlah_kata' => count($kata),
            'jawaban' => implode(' ', $kata),
        ];
    }

    /** Audio mufrodat Arab diputar, siswa memilih kata Arab yang tepat. */
    private static function dengarAr(BeeVocab $v, string $ar, $bankArabKata): ?array
    {
        $audio = trim((string) ($v->audio_mufrodat_ar ?? ''));
        if ($audio === '' || $ar === '') {
            return null;
        }

        $pilihan = [$ar];
        foreach ($bankArabKata->shuffle() as $kata) {
            if (count($pilihan) >= 4) { break; }
            if (trim((string) $kata) !== $ar) { $pilihan[] = (string) $kata; }
        }
        if (count($pilihan) < 3) {
            return null;
        }

        return [
            'mode' => 'dengar_ar',
            'petunjuk' => 'Dengarkan audio, lalu pilih kata Arab yang tepat.',
            'tanya' => '🎧 Audio',
            'audio' => $audio,
            'pilihan' => collect($pilihan)->shuffle()->values()->all(),
            'pilihan_arab' => true,
            'jawaban' => $ar,
        ];
    }

    // ==================================================================================
    //  MESIN 7 MODE — mesin baru, terpisah dari mesin lama di atas.
    // ==================================================================================

    /**
     * Membuat sesi soal untuk DAFTAR MODE yang diminta (mesin 7 mode).
     *
     * Selalu mengembalikan tepat $perMode butir per mode. Bila kolom yang dibutuhkan
     * kosong untuk sebuah baris, dipakai rantai cadangan mode yang datanya ada, dan daftar
     * kosakata diputar balik (diulang) supaya jumlah soal tidak pernah kurang.
     *
     * @param  array  $modes   daftar mode yang diminta, mis. ['pg_rumpang','jodoh'];
     *                         kosong = ketujuh mode
     * @param  int    $perMode jumlah soal per mode
     * @param  array  $modulId daftar bee_week_id (kosong = seluruh bank)
     * @param  string $bahasa  'inggris' atau 'arab'
     * @return array{modes: array, soal: array, kunci: array, peta_mode: array}
     *         'modes'     => ['pg_rumpang' => ['soal'=>[], 'kunci'=>[]], ...]  (nomor 1..perMode per mode)
     *         'soal'/'kunci' => versi gabungan bernomor 1..N (drop-in halaman ujian saat ini)
     *         'peta_mode' => [nomor => nama mode] — kirimkan ke SoalBee::nilai() agar
     *                        susun_kata dibandingkan PERSIS
     */
    public static function buatSesi(array $modes, int $perMode, array $modulId = [], string $bahasa = 'inggris', array $perModeKhusus = []): array
    {
        $bahasa = $bahasa === 'arab' ? 'arab' : 'inggris';
        $perMode = max(1, $perMode);

        $diminta = [];
        foreach ($modes as $m) {
            $m = is_string($m) ? $m : '';
            if (in_array($m, self::MODE_BARU, true) && ! in_array($m, $diminta, true)) {
                $diminta[] = $m;
            }
        }
        if ($diminta === []) {
            $diminta = self::MODE_BARU;
        }

        $k = self::kerangka($bahasa);
        [$dasar, $cadanganBank] = self::ambilDasar($modulId, $k['kata']);

        if ($dasar->isEmpty()) {
            return [
                'bahasa' => $bahasa,
                'per_mode' => $perMode,
                'modes' => [],
                'soal' => [],
                'kunci' => [],
                'peta_mode' => [],
                'cadangan_bank' => $cadanganBank,
                'pesan' => 'Tidak ada kosakata siap uji untuk sesi ini.',
            ];
        }

        $k['bankToken'] = self::bankToken($dasar, $k['kalimat'], $k['arab']);
        $k['bankSasaran'] = self::bankSasaran($dasar, $k['kata'], $k['arab']);

        $keluar = [];

        foreach ($diminta as $mode) {
            $soal = [];
            $kunci = [];
            $pilih = $dasar->shuffle()->values();

            // 'dengar_kata' WAJIB ber-audio: kalau ada baris tanpa audio kata, baris itu disisihkan
            // dari kolam mode ini supaya semua soal benar-benar audio (bukan jatuh ke mode cadangan).
            if ($mode === 'dengar_kata') {
                $kolomAudio = $k['audioKata'];
                $berAudio = $pilih->filter(fn ($v) => self::bersih((string) $v->{$kolomAudio}) !== '')->values();
                if ($berAudio->isNotEmpty()) {
                    $pilih = $berAudio;
                }
            }

            $jodohPool = $mode === 'jodoh' ? self::poolJodoh($dasar, $k) : [];
            $jodohSiap = count($jodohPool) >= self::MIN_PASANGAN_JODOH;
            $butirKe = 0;
            // jumlah butir mode ini (bisa berbeda, mis. jodoh = 4 papan × 10 pasang)
            $jmlButir = max(1, (int) ($perModeKhusus[$mode] ?? $perMode));

            for ($i = 0; $i < $jmlButir; $i++) {
                $butir = null;

                if ($mode === 'jodoh' && $jodohSiap) {
                    // papan ke-1 & ke-2 = sesi 1 (teks→teks), ke-3 & ke-4 = sesi 2 (audio→teks)
                    $variasi = $i >= 2 ? 'audio' : 'teks';
                    $poolPapan = $jodohPool;
                    if ($variasi === 'audio') {
                        // sesi 2 HARUS memakai kata yang benar-benar punya rekaman; kalau tidak,
                        // kartu kiri jadi tombol putar yang bisu.
                        $poolAudio = array_values(array_filter($jodohPool, fn ($x) => trim((string) ($x['audio'] ?? '')) !== ''));
                        if (count($poolAudio) >= self::PASANGAN_JODOH) {
                            $poolPapan = $poolAudio;
                        } else {
                            $variasi = 'teks';   // rekaman kata belum cukup → jangan paksa papan audio
                        }
                    }
                    $grup = self::grupJodoh($poolPapan, $i * self::PASANGAN_JODOH, self::PASANGAN_JODOH);
                    $butir = self::bJodoh($grup, $k, $variasi);
                } else {
                    $v = $pilih[$i % max(1, $pilih->count())];
                    foreach (self::RANTAI[$mode] as $coba) {
                        if ($coba === 'jodoh') {
                            continue;
                        }
                        $butir = self::butirBaru($coba, $v, $k);
                        if ($butir !== null) {
                            break;
                        }
                    }
                }

                if ($butir === null) {
                    continue;   // praktis tak terjadi: 'pg_arti' hampir selalu bisa
                }

                $butirKe++;
                $kunci[$butirKe] = (string) $butir['jawaban'];
                unset($butir['jawaban']);           // kunci tidak boleh ikut ke peramban
                $butir['nomor'] = $butirKe;
                $soal[] = $butir;
            }

            $keluar[$mode] = ['soal' => $soal, 'kunci' => $kunci];
        }

        // Versi gabungan bernomor 1..N — siap dimasukkan langsung ke kolom soal/kunci percobaan.
        $gabungSoal = [];
        $gabungKunci = [];
        $peta = [];
        $n = 0;
        foreach ($diminta as $mode) {
            foreach ($keluar[$mode]['soal'] as $b) {
                $nomorLama = $b['nomor'];
                $n++;
                $b['nomor'] = $n;
                $gabungSoal[] = $b;
                $gabungKunci[$n] = $keluar[$mode]['kunci'][$nomorLama] ?? '';
                $peta[$n] = $b['mode'];
            }
        }

        return [
            'bahasa' => $bahasa,
            'per_mode' => $perMode,
            'modes' => $keluar,
            'soal' => $gabungSoal,
            'kunci' => $gabungKunci,
            'peta_mode' => $peta,
            'cadangan_bank' => $cadanganBank,
        ];
    }

    /** Kerangka kolom per bahasa. */
    private static function kerangka(string $bahasa): array
    {
        $arab = $bahasa === 'arab';

        return [
            'bahasa' => $bahasa,
            'arab' => $arab,
            'kata' => $arab ? 'mufrodat_ar' : 'vocab_en',
            'kalimat' => $arab ? 'jumlah_ar' : 'sentence_en',
            'audioKata' => $arab ? 'audio_mufrodat_ar' : 'audio_vocab_en',
            'audioKalimat' => $arab ? 'audio_jumlah_ar' : 'audio_sentence_en',
            'bankToken' => [],
            'bankSasaran' => [],
        ];
    }

    /**
     * Ambil baris kosakata yang punya kolom kata sasaran. Bila modul yang dipilih sama sekali
     * tidak punya data bahasa ini, daftar DIPUTAR BALIK ke seluruh bank supaya jumlah soal tetap cukup.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: bool}
     */
    private static function ambilDasar(array $modulId, string $kolomKata): array
    {
        $kolom = in_array($kolomKata, ['vocab_en', 'mufrodat_ar'], true) ? $kolomKata : 'vocab_en';
        $siap = fn ($q) => $q->whereNotNull($kolom)->where($kolom, '!=', '')->get();

        $dasar = $modulId !== []
            ? $siap(BeeVocab::query()->whereIn('bee_week_id', array_map('intval', $modulId)))
            : $siap(BeeVocab::query());

        $cadangan = false;
        if ($dasar->isEmpty() && $modulId !== []) {
            $dasar = $siap(BeeVocab::query());
            $cadangan = true;
        }

        // Rapikan: hanya baris yang kolomnya benar-benar berisi.
        $dasar = $dasar->filter(fn ($v) => self::bersih((string) $v->{$kolom}) !== '')->values();

        return [$dasar, $cadangan];
    }

    /** Dispatcher butir mesin baru. */
    private static function butirBaru(string $coba, BeeVocab $v, array $k): ?array
    {
        return match ($coba) {
            'pg_rumpang' => self::bRumpang($v, $k, false),
            'drag_rumpang' => self::bRumpang($v, $k, true),
            'susun_kata' => self::bSusunKata($v, $k),
            'imla_rumpang' => self::bImlaRumpang($v, $k),
            'imla_murni' => self::bImlaMurni($v, $k),
            'ucap' => self::bUcap($v, $k),
            'pg_arti' => self::bPilihKata($v, $k),
            'pg_kosakata' => self::bPilihKata($v, $k, 'pg_kosakata'),
            'dengar_kata' => self::bDengarKata($v, $k),
            default => null,
        };
    }

    /** pg_rumpang + drag_rumpang: kalimat satu kata dihilangkan, siswa memilih 1 dari 4 kata. */
    private static function bRumpang(BeeVocab $v, array $k, bool $drag): ?array
    {
        $kalimat = self::bersih((string) $v->{$k['kalimat']});
        $kataSasaran = self::bersih((string) $v->{$k['kata']});
        if ($kalimat === '' || $kataSasaran === '') {
            return null;
        }

        $tokens = self::pecah($kalimat);
        if (count($tokens) < 2) {
            return null;
        }

        $idx = self::pilihLubang($tokens, $kataSasaran, $k['arab']);
        $jawaban = $tokens[$idx];

        $pilihan = self::pengecoh($jawaban, $k['bankToken'], 4, $k['arab']);
        if (count($pilihan) < 4) {
            return null;   // bank kata tidak cukup → rantai cadangan yang mengambil alih
        }

        $sisip = $tokens;
        $sisip[$idx] = self::LUBANG;

        $butir = [
            'mode' => $drag ? 'drag_rumpang' : 'pg_rumpang',
            'jenis' => $drag ? 'seret' : 'pilihan',
            'petunjuk' => $drag
                ? 'Seret atau ketuk balok ke lubang kosong agar kalimatnya lengkap.'
                : 'Lengkapi kalimat berikut. Pilih satu kata yang hilang.',
            'tanya' => implode(' ', $sisip),
            'tanya_arab' => $k['arab'],
            'lubang' => $idx + 1,
            'jumlah_lubang' => 1,
            'pilihan' => $pilihan,
            'pilihan_arab' => $k['arab'],
            'jawaban' => $jawaban,
        ];

        if ($drag) {
            $butir['seret'] = true;
            $butir['balok'] = $pilihan;   // tray balok untuk UI seret
        }

        return $butir;
    }

    /** susun_kata: balok kata kalimat diacak; kunci = kalimat utuh. */
    private static function bSusunKata(BeeVocab $v, array $k): ?array
    {
        $kalimat = self::bersih((string) $v->{$k['kalimat']});
        if ($kalimat === '') {
            return null;
        }

        $tokens = self::pecah($kalimat);
        if (count($tokens) < 2) {
            return null;
        }

        $acak = self::acakBeda($tokens);

        $butir = [
            'mode' => 'susun_kata',
            'jenis' => 'susun',
            'petunjuk' => 'Ketuk balok kata di bawah untuk menyusun kalimat yang benar.',
            'tanya' => 'Susun kalimat',
            'kata' => array_values($acak),
            'jumlah_kata' => count($tokens),
            'jawaban' => implode(' ', $tokens),
        ];
        if ($k['arab']) {
            $butir['kata_arab'] = true;
        }

        return $butir;
    }

    /** imla_rumpang: audio + kalimat berlubang → siswa MENGETIK kata yang hilang. */
    private static function bImlaRumpang(BeeVocab $v, array $k): ?array
    {
        $kalimat = self::bersih((string) $v->{$k['kalimat']});
        $kataSasaran = self::bersih((string) $v->{$k['kata']});
        $audio = self::bersih((string) $v->{$k['audioKata']});
        if ($kalimat === '' || $kataSasaran === '' || $audio === '') {
            return null;
        }

        $tokens = self::pecah($kalimat);
        if (count($tokens) < 2) {
            return null;
        }

        $idx = self::pilihLubang($tokens, $kataSasaran, $k['arab']);
        $sisip = $tokens;
        $sisip[$idx] = self::LUBANG;

        $butir = [
            'mode' => 'imla_rumpang',
            'jenis' => 'ketik',
            'petunjuk' => 'Dengarkan audio, lalu ketik kata yang hilang pada kalimat.',
            'tanya' => implode(' ', $sisip),
            'tanya_arab' => $k['arab'],
            'audio' => $audio,                    // audio KATA yang hilang (audio_vocab_en/audio_mufrodat_ar)
            'lubang' => $idx + 1,
            'jumlah_lubang' => 1,
            'jawaban' => $tokens[$idx],
        ];

        // Tambahan (bukan wajib): audio kalimat utuh, kalau guru sudah menyediakannya.
        $audioKalimat = self::bersih((string) $v->{$k['audioKalimat']});
        if ($audioKalimat !== '') {
            $butir['audio_kalimat'] = $audioKalimat;
        }

        return $butir;
    }

    /** imla_murni: audio kata diputar → siswa MENGETIK kata utuh (hanya baris yang punya audio). */
    private static function bImlaMurni(BeeVocab $v, array $k): ?array
    {
        $kata = self::bersih((string) $v->{$k['kata']});
        $audio = self::bersih((string) $v->{$k['audioKata']});
        if ($kata === '' || $audio === '') {
            return null;
        }

        return [
            'mode' => 'imla_murni',
            'jenis' => 'ketik',
            'petunjuk' => 'Dengarkan audio, lalu ketik kata utuh yang kamu dengar.',
            'tanya' => '🎧 Audio',
            'audio' => $audio,
            'jawaban' => $kata,
        ];
    }

    /** ucap: kata + audio yang harus diucapkan siswa; penilaian di peramban, kunci tetap disimpan. */
    private static function bUcap(BeeVocab $v, array $k): ?array
    {
        $kata = self::bersih((string) $v->{$k['kata']});
        if ($kata === '') {
            return null;
        }

        $audio = self::bersih((string) $v->{$k['audioKata']});

        $butir = [
            'mode' => 'ucap',
            'jenis' => 'ucap',
            'petunjuk' => 'Ucapkan katanya — dinilai otomatis, tanpa tombol periksa.',
            'tanya' => $kata,
            'ucap' => true,
            'jawaban' => $kata,
        ];
        if ($k['arab']) {
            $butir['tanya_arab'] = true;
        }
        if ($audio !== '') {
            $butir['audio'] = $audio;
        }

        return $butir;
    }

    /**
     * Kata Indonesia ditampilkan, siswa memilih padanan bahasa sasarannya (TANPA audio).
     * Dipakai dua tempat: mode 'pg_kosakata' (sub-sesi tersendiri, 2 sesi × 10 soal) dan
     * 'pg_arti' (penutup/cadangan terakhir mesin lama).
     */
    private static function bPilihKata(BeeVocab $v, array $k, string $mode = 'pg_arti'): ?array
    {
        $kata = self::bersih((string) $v->{$k['kata']});
        $arti = self::bersih((string) $v->kosakata_id);
        if ($kata === '' || $arti === '') {
            return null;   // tanpa kata Indonesia, tanya akan membocorkan kunci
        }

        $pilihan = self::pengecoh($kata, $k['bankSasaran'], 4, $k['arab']);
        if (count($pilihan) < 4) {
            return null;
        }

        $butir = [
            'mode' => $mode,
            'jenis' => 'pilihan',
            'petunjuk' => 'Pilih padanan '.($k['arab'] ? 'bahasa Arab' : 'bahasa Inggris').' yang tepat untuk kata Indonesia berikut.',
            'tanya' => $arti,
            'tanya_satuan' => true,
            'pilihan' => $pilihan,
            'pilihan_arab' => $k['arab'],
            'jawaban' => $kata,
        ];

        // Audio kata bahasa sasarannya ikut dibawa HANYA sebagai umpan balik: setelah jawaban BENAR
        // halaman memperdengarkannya dulu sebelum pindah soal (permintaan Fahri). Tidak ada tombol
        // putar di soal, jadi soal tetap tanpa audio.
        $audio = self::bersih((string) $v->{$k['audioKata']});
        if ($audio !== '') {
            $butir['audio'] = $audio;
        }

        return $butir;
    }

    /**
     * dengar_kata: AUDIO kata diputar TANPA tulisan katanya → siswa memilih kata tertulis yang tepat.
     * Bentuk mesin lama 'dengar' tapi memakai penamaan kolom mesin baru ($k) dan penanda blok sendiri.
     */
    private static function bDengarKata(BeeVocab $v, array $k): ?array
    {
        $kata = self::bersih((string) $v->{$k['kata']});
        $audio = self::bersih((string) $v->{$k['audioKata']});
        if ($kata === '' || $audio === '') {
            return null;   // tanpa audio, butir tidak layak → rantai cadangan mengambil alih
        }

        $pilihan = self::pengecoh($kata, $k['bankSasaran'], 4, $k['arab']);
        if (count($pilihan) < 4) {
            return null;
        }

        return [
            'mode' => 'dengar_kata',
            'jenis' => 'pilihan',
            'petunjuk' => 'Dengarkan audionya, lalu ketuk kata yang kamu dengar (katanya tidak ditulis).',
            'tanya' => '🎧 Audio',
            'tanya_tanpa_teks' => true,
            'audio' => $audio,
            'pilihan' => $pilihan,
            'pilihan_arab' => $k['arab'],
            'jawaban' => $kata,
        ];
    }

    /**
     * jodoh: daftar pasangan Indonesia ⇄ bahasa sasaran.
     *
     * Kolom KIRI (kata Indonesia) tampil dalam urutan tetap; kolom KANAN (kata bahasa sasaran) diacak.
     * Kunci = URUTAN PASANGAN YANG BENAR, yaitu nomor kolom kanan untuk tiap kata kiri (1..n),
     * dipisah spasi — mis. "3 1 5 4 2". Bentuk angka dipilih supaya tidak rancu: kata sasaran bisa
     * berupa frasa berisi spasi, jadi kunci berupa kata gabungan tak bisa diverifikasi dengan aman.
     */
    private static function bJodoh(array $grup, array $k, string $variasi = 'teks'): ?array
    {
        $n = count($grup);
        if ($n < self::MIN_PASANGAN_JODOH) {
            return null;
        }

        $kiri = [];
        $kanan = [];
        $audioKiri = [];
        foreach ($grup as $p) {
            $kiri[] = $p['arti'];
            $kanan[] = $p['kata'];
            // SELALU didorong, sejajar dengan urutan kartu kiri ('' = rekaman kata belum ada).
            // Jangan pernah dibuang: kalau tidak, petanya mengecil dan audio kartu jadi bisu.
            $audioKiri[] = self::bersih((string) ($p['audio'] ?? ''));
        }

        // Urutan kolom kanan yang DITAMPILKAN: permutasi posisi asli (bukan urutan asli).
        $tampil = self::acakBeda(range(1, $n));

        $kananTampil = [];
        foreach ($tampil as $posisiAsli) {
            $kananTampil[] = $kanan[$posisiAsli - 1];
        }

        // Jawaban: untuk tiap kata kiri (berurutan), nomor kolom kanan tempat padanannya berada.
        $jawab = [];
        for ($i = 1; $i <= $n; $i++) {
            $jawab[] = array_search($i, $tampil, true) + 1;
        }

        $butir = [
            'mode' => 'jodoh',
            'jenis' => 'jodoh',
            'versi' => 2,                            // format baru: per ketukan, 10 pasang, pasangan benar hilang
            'variasi' => $variasi,                   // 'teks' (kata Indonesia di kiri) atau 'audio' (audio kata di kiri)
            'petunjuk' => $variasi === 'audio'
                ? 'Ketuk tombol putar di kiri, lalu ketuk kata yang kamu dengar di kanan.'
                : 'Ketuk satu kata di kiri, lalu padanannya di kanan. Pasangan yang tepat hilang dari papan.',
            'tanya' => 'Jodohkan '.$n.' pasangan',
            'kiri' => $kiri,                         // urutan tetap (kiri 1..n)
            'kanan' => array_values($kananTampil),    // diacak, dinomori 1..n dari atas
            'kanan_arab' => $k['arab'],
            'jumlah_pasangan' => $n,
            'jawaban' => implode(' ', $jawab),       // kunci: nomor kolom kanan untuk tiap kartu kiri berurutan
        ];

        if ($variasi === 'audio') {
            $butir['audio_kiri'] = $audioKiri;       // audio kata untuk tiap kartu kiri (sejajar, '' = kosong)
        }

        return $butir;
    }

    /** Kumpulan pasangan unik (kata Indonesia unik & kata sasaran unik) untuk mode jodoh. */
    private static function poolJodoh($dasar, array $k): array
    {
        $pool = [];
        $lihatArti = [];
        $lihatKata = [];
        foreach ($dasar as $v) {
            $arti = self::bersih((string) $v->kosakata_id);
            $kata = self::bersih((string) $v->{$k['kata']});
            if ($arti === '' || $kata === '') {
                continue;
            }
            $na = mb_strtolower($arti);
            $nk = self::inti($kata, $k['arab']);
            if ($nk === '' || isset($lihatArti[$na]) || isset($lihatKata[$nk])) {
                continue;
            }
            $lihatArti[$na] = true;
            $lihatKata[$nk] = true;
            // audio kata ikut disimpan: dipakai papan variasi audio→teks (sesi 2)
            $pool[] = ['arti' => $arti, 'kata' => $kata, 'audio' => self::bersih((string) $v->{$k['audioKata']})];
        }

        return $pool;
    }

    /** Ambil $maks pasangan dari pool mulai offset tertentu, melingkar & tanpa pengulangan di dalam grup. */
    private static function grupJodoh(array $pool, int $offset, int $maks): array
    {
        $n = count($pool);
        if ($n === 0) {
            return [];
        }

        $ambil = min($maks, $n);
        $grup = [];
        for ($i = 0; $i < $ambil; $i++) {
            $grup[] = $pool[($offset + $i) % $n];
        }

        return $grup;
    }

    /** Bank kata tunggal dari seluruh kalimat (untuk pengecoh rumpang). */
    private static function bankToken($dasar, string $kolomKalimat, bool $arab): array
    {
        $keluar = [];
        $lihat = [];
        foreach ($dasar as $v) {
            foreach (self::pecah((string) $v->{$kolomKalimat}) as $tok) {
                $n = self::inti($tok, $arab);
                if ($n === '' || isset($lihat[$n])) {
                    continue;
                }
                $lihat[$n] = true;
                $keluar[] = $tok;
            }
        }

        return $keluar;
    }

    /** Bank kata sasaran utuh (untuk pengecoh pilihan & jodoh). */
    private static function bankSasaran($dasar, string $kolomKata, bool $arab): array
    {
        $keluar = [];
        $lihat = [];
        foreach ($dasar as $v) {
            $kata = self::bersih((string) $v->{$kolomKata});
            $n = self::inti($kata, $arab);
            if ($kata === '' || $n === '' || isset($lihat[$n])) {
                continue;
            }
            $lihat[$n] = true;
            $keluar[] = $kata;
        }

        return $keluar;
    }

    /** Susun daftar pilihan (jawaban benar + pengecoh). Kembalikan [] bila bank tidak cukup. */
    private static function pengecoh(string $benar, array $bank, int $jml, bool $arab): array
    {
        $opsi = [$benar];
        $terpakai = [self::inti($benar, $arab)];

        $acak = $bank;
        shuffle($acak);
        foreach ($acak as $b) {
            if (count($opsi) >= $jml) {
                break;
            }
            $b = self::bersih((string) $b);
            $n = self::inti($b, $arab);
            if ($b === '' || $n === '' || in_array($n, $terpakai, true)) {
                continue;
            }
            $opsi[] = $b;
            $terpakai[] = $n;
        }

        if (count($opsi) < $jml) {
            return [];
        }

        shuffle($opsi);

        return $opsi;
    }

    /** Pilih indeks kata yang dihilangkan: utamakan kata yang sama dengan kosakata baris, lalu kata berisi. */
    private static function pilihLubang(array $tokens, string $kataSasaran, bool $arab): int
    {
        $sasaran = [];
        foreach (self::pecah($kataSasaran) as $s) {
            $n = self::inti($s, $arab);
            if ($n !== '') {
                $sasaran[] = $n;
            }
        }

        foreach ($tokens as $i => $tok) {
            $n = self::inti($tok, $arab);
            if ($n === '') {
                continue;
            }
            if (in_array($n, $sasaran, true)) {
                return $i;
            }
        }

        $kandidat = [];
        foreach ($tokens as $i => $tok) {
            if (mb_strlen(self::inti($tok, $arab)) >= 3) {
                $kandidat[] = $i;
            }
        }
        if ($kandidat === []) {
            $kandidat = array_keys($tokens);
        }

        return $kandidat[array_rand($kandidat)];
    }

    /** Acak, tapi pastikan hasilnya TIDAK sama dengan urutan aslinya (jangan membocorkan kunci). */
    private static function acakBeda(array $asli): array
    {
        $n = count($asli);
        if ($n < 2) {
            return $asli;
        }

        $acak = $asli;
        for ($coba = 0; $coba < 12; $coba++) {
            shuffle($acak);
            if ($acak !== $asli) {
                return $acak;
            }
        }

        return $acak;
    }

    /** Rapikan: buang karakter tak terlihat (RTL mark dll.) dan rapikan spasi. */
    private static function bersih(string $t): string
    {
        $t = preg_replace('~[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}\x{FEFF}]~u', '', $t);

        return trim((string) preg_replace('~\s+~u', ' ', (string) $t));
    }

    /** Pecah kalimat jadi kata-kata. */
    private static function pecah(string $kalimat): array
    {
        $bagian = preg_split('~\s+~u', self::bersih($kalimat)) ?: [];

        return array_values(array_filter($bagian, fn ($t) => $t !== ''));
    }

    /** Inti kata untuk pembandingan: buang harakat & normalisasi huruf Arab (atau lidah Inggris). */
    private static function inti(string $t, bool $arab): string
    {
        $t = self::bersih($t);

        if ($arab) {
            $t = (string) preg_replace('~[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]~u', '', $t);
            $t = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $t);
            $t = str_replace(['ى'], 'ي', $t);
            $t = str_replace(['ة'], 'ه', $t);

            return (string) preg_replace('~[^\p{L}\p{N}]~u', '', $t);
        }

        return (string) preg_replace('~[^a-z0-9]~u', '', mb_strtolower($t));
    }

    // ==================================================================================
    //  PENILAIAN
    // ==================================================================================

    /**
     * Menilai jawaban siswa terhadap kunci.
     *
     * @param  array $kunci    nomor => jawaban benar
     * @param  array $jawaban  nomor => jawaban siswa
     * @param  array $modes    opsional: nomor => nama mode (dari buatSesi()['peta_mode']).
     *                         Bila diisi, susun_kata dinilai PERSIS (join(' '));
     *                         tanpa parameter ini perilakunya sama seperti sebelumnya (penilaian longgar).
     */
    public static function nilai(array $kunci, array $jawaban, array $modes = []): array
    {
        $benar = 0;
        $salah = 0;
        $rincian = [];

        foreach ($kunci as $nomor => $jawabBenar) {
            $jawabSiswa = $jawaban[$nomor] ?? null;
            $mode = is_array($modes) ? ($modes[$nomor] ?? null) : null;
            $cocok = self::cocokMode(is_string($mode) ? $mode : null, (string) $jawabBenar, (string) $jawabSiswa);
            $cocok ? $benar++ : $salah++;
            $rincian[$nomor] = ['jawaban' => $jawabSiswa, 'kunci' => $jawabBenar, 'benar' => $cocok];
        }

        $total = max(1, count($kunci));

        return [
            'benar' => $benar,
            'salah' => $salah,
            'nilai' => (int) round($benar / $total * 100),
            'rincian' => $rincian,
        ];
    }

    /** Bandingkan sesuai mode: susun_kata PERSIS, sisanya longgar. */
    private static function cocokMode(?string $mode, string $kunci, string $jawaban): bool
    {
        if ($mode === 'susun_kata') {
            return self::cocokPersis($kunci, $jawaban);
        }

        return self::cocok($kunci, $jawaban);
    }

    /** Pembanding PERSIS untuk susun_kata: kunci === jawaban sebagai join(' ') (hanya spasi dirapikan). */
    private static function cocokPersis(string $kunci, string $jawaban): bool
    {
        $rapi = fn (string $t): string => trim((string) preg_replace('~\s+~u', ' ', $t));

        return $rapi($kunci) !== '' && $rapi($kunci) === $rapi($jawaban);
    }

    /** Pembanding yang longgar: abaikan besar-kecil huruf, tanda baca, dan spasi berlebih. */
    private static function cocok(string $kunci, string $jawaban): bool
    {
        $rapi = fn (string $t): string => Str::lower(trim((string) preg_replace('~[^\p{L}\p{N}\s]~u', '', $t)));
        $rapiKunci = preg_replace('~\s+~', ' ', $rapi($kunci));
        $rapiJawab = preg_replace('~\s+~', ' ', $rapi($jawaban));

        return $rapiKunci !== '' && $rapiKunci === $rapiJawab;
    }
}
