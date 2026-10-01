<?php

namespace App\Http\Controllers;

use App\Models\SrGroup;
use App\Services\StatistikStudentRoot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * STATISTIK STUDENT ROOT (1 Okt 2026, permintaan Kepala Sekolah).
 *
 * Halaman BACA-SAJA: membandingkan bulan ke bulan (naik/turun poin, jumlah santri
 * yang dapat plus dan minus), menampilkan aktivitas yang paling banyak dilakukan
 * beserta catatannya, sebaran kelas/grup, dan panel kualitas data.
 *
 * HAK AKSES (mengikuti aturan rapor Student Root yang sudah ada):
 *  - Super Admin, Kepala Diniyah, Kepala Sekolah, Tata Usaha → semua santri;
 *  - mentor Student Root → HANYA santri grup binaannya (angka dihitung dari anggota
 *    grup itu, bukan angka sekolah lalu dipotong).
 * Tidak ada izin baru; pemegang izin lama (buka-menu-master-student-root /
 * buka-menu-grup-binaan) yang menentukan siapa boleh membuka.
 */
class SrStatistikController extends Controller
{
    public const SEMESTER = [
        's1' => 'Semester 1 · Juli–Desember',
        's2' => 'Semester 2 · Januari–Juni',
        'semua' => 'Semua data (sejak awal)',
    ];

    public const KELAS = ['X', 'XI', 'XII'];

    public function __construct(private StatistikStudentRoot $statistik) {}

    public function index(Request $request)
    {
        $data = $this->siapkan($request);

        return view('student-root.statistik', $data);
    }

    /** Hasil cetak: ber-kop sekolah, siap ditandatangani. */
    public function cetak(Request $request)
    {
        $data = $this->siapkan($request);
        $data['pengaturan'] = \App\Models\Pengaturan::first();

        return view('student-root.statistik-cetak', $data);
    }

    /** Ekspor CSV: ringkasan bulanan + aktivitas teratas + catatan. */
    public function ekspor(Request $request)
    {
        $this->pastikanBolehMasuk();
        [$dari, $sampai, $preset, $tahunAjaran] = $this->rentangTanggal($request);
        $grupId = $this->grupTerpilihAtauNull($request);
        $kelas = $this->kelasTerpilih($request);

        $d = $this->statistik->susun([
            'dari' => $dari,
            'sampai' => $sampai,
            'kelas' => $kelas,
            'grup' => $grupId,
            'siswa_ids' => $this->batasSantri(),
        ]);

        $nama = 'statistik-student-root-' . ($preset === 'semua' ? 'semua-data' : $tahunAjaran . '-' . $preset) . '.csv';

        // Dibangun di memori lalu dikirim sebagai respons biasa (mudah diuji & cukup untuk CSV).
        $f = fopen('php://temp', 'r+');
            fwrite($f, "\xEF\xBB\xBF");   // BOM supaya Excel membaca huruf beraksen dengan benar

            fputcsv($f, ['STATISTIK STUDENT ROOT — SMA IT ARAFAH']);
            fputcsv($f, ['Periode', ($d['dari'] ?: 'awal') . ' s.d. ' . ($d['sampai'] ?: 'sekarang')]);
            fputcsv($f, ['Kelas', $d['kelas'] ?: 'semua', 'Grup', $d['grup'] ?: 'semua', 'Jumlah santri', $d['jumlah_santri']]);
            fputcsv($f, []);

            fputcsv($f, ['PER BULAN']);
            fputcsv($f, ['Bulan', 'Entri', 'Total poin', 'Entri plus', 'Entri minus', 'Santri dapat plus', 'Santri dapat minus', 'Selisih poin', 'Selisih %']);
            foreach ($d['bulanan'] as $b) {
                fputcsv($f, [$b['label'], $b['entri'], $b['poin'], $b['entri_plus'], $b['entri_min'],
                    $b['siswa_plus'], $b['siswa_min'], $b['delta_poin'] ?? '', $b['delta_persen'] ?? '']);
            }
            fputcsv($f, []);

            foreach (['positif' => 'AKTIVITAS POSITIF TERBANYAK', 'negatif' => 'AKTIVITAS NEGATIF TERBANYAK'] as $kunci => $judul) {
                fputcsv($f, [$judul]);
                fputcsv($f, ['Aktivitas', 'Poin/kegiatan', 'Jumlah entri', 'Jumlah santri', 'Catatan terbanyak']);
                foreach ($d['aktivitas'][$kunci] as $a) {
                    $catatan = collect($a['catatan'])->map(fn ($c) => $c['teks'] . ' (' . $c['jumlah'] . '×)')->implode('; ');
                    fputcsv($f, [$a['nama'], $a['poin_kriteria'], $a['jumlah'], $a['siswa'], $catatan]);
                }
                fputcsv($f, []);
            }

            fputcsv($f, ['CATATAN PALING SERING DITULIS']);
            fputcsv($f, ['Catatan', 'Jumlah', 'Kriteria terbanyak']);
            foreach ($d['catatan'] as $c) {
                fputcsv($f, [$c['teks'], $c['jumlah'], $c['kriteria']]);
            }
            fputcsv($f, []);

            fputcsv($f, ['SEBARAN PER KELAS']);
            fputcsv($f, ['Kelas', 'Santri', 'Entri', 'Total poin', 'Rata-rata per santri']);
            foreach ($d['per_kelas'] as $k) {
                fputcsv($f, [$k['kelas'], $k['siswa'], $k['entri'], $k['poin'], $k['rata']]);
            }
            fputcsv($f, []);

            fputcsv($f, ['SEBARAN PER GRUP BINAAN']);
            fputcsv($f, ['Grup', 'Anggota', 'Entri', 'Total poin']);
            foreach ($d['per_grup'] as $g) {
                fputcsv($f, [$g['grup'], $g['anggota'], $g['entri'], $g['poin']]);
            }

            rewind($f);
            $isi = stream_get_contents($f);
            fclose($f);

        return response()->make($isi ?: '', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    // =====================================================================
    //  Penyiapan data untuk halaman & cetak
    // =====================================================================
    private function siapkan(Request $request): array
    {
        $this->pastikanBolehMasuk();
        [$dari, $sampai, $preset, $tahunAjaran] = $this->rentangTanggal($request);
        $grupId = $this->grupTerpilihAtauNull($request);
        $kelas = $this->kelasTerpilih($request);

        $data = $this->statistik->susun([
            'dari' => $dari,
            'sampai' => $sampai,
            'kelas' => $kelas,
            'grup' => $grupId,
            'siswa_ids' => $this->batasSantri(),
        ]);

        $bolehSemua = $this->bolehSemua();
        $grupSaya = $this->grupSaya();

        return array_merge($data, [
            'preset' => $preset,
            'tahunAjaran' => $tahunAjaran,
            'semesterList' => self::SEMESTER,
            'bolehSemua' => $bolehSemua,
            'grupSaya' => $grupSaya,
            'grupList' => $bolehSemua
                ? SrGroup::where('status', 'aktif')->orderBy('nama_grup')->get(['id', 'nama_grup'])
                : $grupSaya,
            'labelPeriode' => $preset === 'semua' ? 'Semua data' : (self::SEMESTER[$preset] . ' · ' . $tahunAjaran),
        ]);
    }

    // =====================================================================
    //  Hak akses
    // =====================================================================
    private function pastikanBolehMasuk(): void
    {
        abort_unless($this->bolehSemua() || $this->grupSaya()->isNotEmpty(), 403,
            'Hanya mentor Student Root (atau pengawas) yang bisa membuka statistik ini.');
    }

    private function bolehSemua(): bool
    {
        $u = Auth::user();

        foreach (['Super Admin', 'Kepala Diniyah', 'Kepala Sekolah', 'Tata Usaha'] as $peran) {
            if ($u->hasRole($peran)) {
                return true;
            }
        }

        return false;
    }

    private function grupSaya()
    {
        return SrGroup::where('mentor_id', Auth::id())->where('status', 'aktif')->orderBy('nama_grup')->get(['id', 'nama_grup']);
    }

    /** null = semua santri (pengawas); array = batasan mentor (anggota grup binaannya). */
    private function batasSantri(): ?array
    {
        if ($this->bolehSemua()) {
            return null;
        }

        $grupIds = $this->grupSaya()->pluck('id')->all();
        $anggota = $this->statistik->anggotaGrup($grupIds);

        return $anggota === [] ? [0] : array_map('intval', $anggota);
    }

    private function grupTerpilihAtauNull(Request $request): ?string
    {
        $grupId = (string) $request->input('grup', '');
        if ($grupId === '') {
            return null;
        }

        $boleh = $this->bolehSemua()
            ? SrGroup::where('status', 'aktif')->pluck('id')->all()
            : $this->grupSaya()->pluck('id')->all();

        abort_unless(in_array($grupId, $boleh, true), 403, 'Grup ini bukan binaan Anda.');

        return $grupId;
    }

    private function kelasTerpilih(Request $request): ?string
    {
        $kelas = (string) $request->input('kelas', '');

        return in_array($kelas, self::KELAS, true) ? $kelas : null;
    }

    /**
     * Rentang tanggal. Preset semester seperti rapor Student Root (s1 Juli–Des, s2 Jan–Jun),
     * ditambah pilihan "semua" supaya bisa melihat riwayat penuh.
     *
     * @return array{0:?string,1:?string,2:string,3:string}
     */
    private function rentangTanggal(Request $request): array
    {
        $tahun = (int) now()->format('Y');
        $awalTahunAjaran = (int) now()->format('n') >= 7 ? $tahun : $tahun - 1;
        $semesterAktif = $awalTahunAjaran === $tahun ? 's1' : 's2';

        $preset = (string) $request->input('semester', $semesterAktif);
        if (! array_key_exists($preset, self::SEMESTER)) {
            $preset = $semesterAktif;
        }

        $tahunAjaran = "{$awalTahunAjaran}/" . ($awalTahunAjaran + 1);

        if ($preset === 'semua') {
            return [null, null, 'semua', $tahunAjaran];
        }

        [$dari, $sampai] = $preset === 's1'
            ? ["{$awalTahunAjaran}-07-01", "{$awalTahunAjaran}-12-31"]
            : [($awalTahunAjaran + 1) . '-01-01', ($awalTahunAjaran + 1) . '-06-30'];

        return [$dari, $sampai, $preset, $tahunAjaran];
    }
}
