<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * BAHASA ARAB UNTUK RAPOR DINIYAH (1 Okt 2026)
 * =====================================================================
 * Keputusan Kepala Sekolah: rapor berbahasa Arab DENGAN TERJEMAHAN KECIL
 * BAHASA INDONESIA di bawah tiap label (jadi setiap label selalu punya
 * pasangan Arab + Indonesia — orang tua yang belum terbiasa baca Arab tetap
 * mengerti).
 *
 * Semua label ditulis sekali di sini supaya rapor, layar, dan rekap memakai
 * istilah yang sama (jangan menulis label Arab langsung di Blade).
 */
class DiniyahArab
{
    /** Label tetap pada rapor: kunci => [arab, indonesia]. */
    public const LABEL = [
        'mapel'        => ['المادة الدراسية', 'Mata pelajaran'],
        'nilai'        => ['الدرجة', 'Nilai'],
        'predikat'     => ['التقدير', 'Predikat'],
        'catatan'      => ['ملاحظة المدرّس', 'Catatan guru'],
        'jumlah'       => ['المجموع / المعدّل', 'Jumlah / rata-rata'],
        'identitas'    => ['بيانات الطالب', 'Identitas santri'],
        'nama'         => ['اسم الطالب', 'Nama santri'],
        'nama_arab'    => ['الاسم بالعربية', 'Nama dalam tulisan Arab'],
        'nisn'         => ['رقم القيد', 'NISN'],
        'kelas'        => ['الفصل', 'Kelas'],
        'tahun'        => ['السنة الدراسية', 'Tahun ajaran'],
        'semester'     => ['الفصل الدراسي', 'Semester'],
        'kehadiran'    => ['سجلّ الحضور والغياب', 'Rekap kehadiran'],
        'hadir'        => ['الحضور', 'Hadir'],
        'sakit'        => ['المرض', 'Sakit'],
        'izin'         => ['الإذن', 'Izin'],
        'alpa'         => ['الغياب بلا عذر', 'Tanpa keterangan (alpa)'],
        'total_pertemuan' => ['مجموع الحصص', 'Jumlah pertemuan'],
        'catatan_pembina' => ['ملاحظة المربّي', 'Catatan pembina'],
        'kepala_sekolah' => ['مدير المدرسة', 'Kepala Sekolah'],
        'wali'         => ['وليّ الطالب', 'Wali santri'],
        'kepala_diniyah' => ['رئيس الكُلِّيَّة الدِّينِيَّة', 'Kepala Kulliyyat Diiniyyah'],
        'rapot'        => ['كشف الدرجات', 'Rapor hasil belajar'],
        'kop_nama'     => ['الكُلِّيَّة الدِّينِيَّة الأَرَفَاه', 'Kulliyyat Diiniyyah Al-Arafah'],
    ];

    /** Bulan hijriah (perkiraan) supaya kop rapor bisa memakai tahun hijriah. */
    public const BULAN_HIJRIAH = [
        1 => 'مُحَرَّم', 2 => 'صَفَر', 3 => 'رَبيع الأوّل', 4 => 'رَبيع الثاني',
        5 => 'جُمادى الأولى', 6 => 'جُمادى الآخرة', 7 => 'رَجَب', 8 => 'شَعْبان',
        9 => 'رَمَضان', 10 => 'شَوّال', 11 => 'ذو القَعْدة', 12 => 'ذو الحِجّة',
    ];

    public static function label(string $kunci, string $bagian = 'arab'): string
    {
        $pasangan = self::LABEL[$kunci] ?? ['—', '—'];

        return $bagian === 'id' ? $pasangan[1] : $pasangan[0];
    }

    /** Predikat Arab dari nilai 0–100. */
    public static function predikatArab(?int $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        return match (true) {
            $nilai >= 90 => 'مُمْتاز',
            $nilai >= 80 => 'جَيِّد جِدًّا',
            $nilai >= 70 => 'جَيِّد',
            $nilai >= 60 => 'مَقْبول',
            default      => 'راسِب',
        };
    }

    /** Predikat versi Indonesia (pendamping kecil di bawah predikat Arab). */
    public static function predikatIndonesia(?int $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        return match (true) {
            $nilai >= 90 => 'Mumtaz (istimewa)',
            $nilai >= 80 => 'Jayyid jiddan (baik sekali)',
            $nilai >= 70 => 'Jayyid (baik)',
            $nilai >= 60 => 'Maqbul (cukup)',
            default      => 'Rasib (belum lulus)',
        };
    }

    /** Nama kelas dalam huruf Arab: X -> العاشر, XI -> الحادي عشر, XII -> الثاني عشر. */
    public static function kelasArab(?string $kelas): string
    {
        return match (strtoupper(trim((string) $kelas))) {
            'X'   => 'العاشر',
            'XI'  => 'الحادي عشر',
            'XII' => 'الثاني عشر',
            default => (string) $kelas,
        };
    }

    /** Nama semester dalam huruf Arab. */
    public static function semesterArab(?string $semester): string
    {
        return match ($semester) {
            's1' => 'الأوّل',
            's2' => 'الثاني',
            default => '-',
        };
    }

    /** Perkiraan tahun hijriah dari tanggal masehi, mis. "1448 هـ". */
    public static function tahunHijriah($tanggal = null): string
    {
        $tanggal = $tanggal ? Carbon::parse($tanggal) : now();
        $tahun = (int) round($tanggal->format('Y') * 33 / 32 - 622);

        return $tahun . ' هـ';
    }
}
