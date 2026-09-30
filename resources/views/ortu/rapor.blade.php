@php
    $s = $rj['siswa'];
    $poin = $rj['poin'];
    $inspeksi = $rj['asrama']['inspeksi'];
    $absensi = $rj['asrama']['absensi'];
    $persenKebersihan = $inspeksi['rata'] !== null ? round($inspeksi['rata'] / 25 * 100) : null;
    $projectJalan = collect($rj['project'])->where('status', 'berjalan');
    $projectSelesai = collect($rj['project'])->where('status', 'selesai');
    $predikat = fn ($n) => $n === null ? '—' : \App\Models\PenilaianPengaturan::predikat((float) $n);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Ananda {{ $s->nama_lengkap }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .sembunyikan-saat-print { display: none !important; }
            body { background: white !important; }
            .jangan-terpotong { page-break-inside: avoid !important; break-inside: avoid !important; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-6">
<div class="max-w-3xl mx-auto px-4">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 sembunyikan-saat-print">
        <a href="{{ route('ortu.dasbor') }}" class="text-xs font-black text-gray-700">&larr; Kembali ke dasbor</a>
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-xs font-black">Cetak / Simpan PDF</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center border-b border-gray-300 pb-4">
            <p class="text-sm font-black text-gray-800 uppercase tracking-wide">SMA IT Arafah</p>
            <p class="text-xs font-bold text-gray-600">Rapor Perkembangan Karakter &amp; Keasramaan</p>
            <p class="text-xs font-semibold text-gray-500 mt-1">Dicetak dari Portal Orang Tua pada {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        </div>

        <table class="w-full text-xs font-semibold text-gray-700 mt-4">
            <tr><td class="py-0.5 w-32 font-black">Nama</td><td class="py-0.5">: {{ $s->nama_lengkap }}</td></tr>
            <tr><td class="py-0.5 font-black">NISN</td><td class="py-0.5">: {{ $s->nisn ?: '-' }}</td></tr>
            <tr><td class="py-0.5 font-black">Kelas</td><td class="py-0.5">: {{ $s->kelas ?: '-' }}</td></tr>
            <tr><td class="py-0.5 font-black">Kamar / Grup</td><td class="py-0.5">: {{ $rj['kamar']?->nama_kamar ?? '-' }} / {{ $rj['grup']?->nama_grup ?? '-' }}</td></tr>
        </table>

        {{-- A. Poin karakter --}}
        <div class="mt-5 jangan-terpotong">
            <p class="text-xs font-black text-gray-800 uppercase tracking-widest">A. Poin Karakter (Student Root)</p>
            <table class="w-full text-xs font-semibold text-gray-700 mt-2 border border-gray-200">
                <tr class="bg-gray-50">
                    <td class="p-2 border border-gray-200 font-black">Total poin bersih</td>
                    <td class="p-2 border border-gray-200">{{ $poin['total'] > 0 ? '+' : '' }}{{ $poin['total'] }}</td>
                    <td class="p-2 border border-gray-200 font-black">Jumlah kejadian</td>
                    <td class="p-2 border border-gray-200">{{ $poin['entri'] }} ({{ $poin['positif'] }} positif, {{ $poin['negatif'] }} negatif)</td>
                </tr>
            </table>
            @if($poin['terbanyak']->isNotEmpty())
                <table class="w-full text-xs font-semibold text-gray-700 mt-2 border border-gray-200">
                    <tr class="bg-gray-50">
                        <td class="p-2 border border-gray-200 font-black">Perilaku</td>
                        <td class="p-2 border border-gray-200 font-black w-20 text-center">Poin</td>
                        <td class="p-2 border border-gray-200 font-black w-24 text-center">Frekuensi</td>
                    </tr>
                    @foreach($poin['terbanyak'] as $t)
                        <tr>
                            <td class="p-2 border border-gray-200">{{ $t->nama_perilaku }}</td>
                            <td class="p-2 border border-gray-200 text-center">{{ $t->poin > 0 ? '+' : '' }}{{ $t->poin }}</td>
                            <td class="p-2 border border-gray-200 text-center">{{ $t->jumlah }}×</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        {{-- B. Keasramaan --}}
        <div class="mt-5 jangan-terpotong">
            <p class="text-xs font-black text-gray-800 uppercase tracking-widest">B. Keasramaan</p>
            <table class="w-full text-xs font-semibold text-gray-700 mt-2 border border-gray-200">
                <tr><td class="p-2 border border-gray-200 font-black w-56">Kamar</td><td class="p-2 border border-gray-200">{{ $rj['kamar']?->nama_kamar ?? '-' }}</td></tr>
                <tr><td class="p-2 border border-gray-200 font-black">Rata-rata kebersihan</td><td class="p-2 border border-gray-200">{{ $inspeksi['rata'] !== null ? str_replace('.', ',', (string) $inspeksi['rata']) . ' / 25 (' . $persenKebersihan . '%)' : 'belum ada inspeksi' }}</td></tr>
                <tr><td class="p-2 border border-gray-200 font-black">Dinilai kamar terbersih</td><td class="p-2 border border-gray-200">{{ $inspeksi['terbersih'] }}×</td></tr>
                <tr><td class="p-2 border border-gray-200 font-black">Absensi jam tidur</td><td class="p-2 border border-gray-200">{{ $absensi->sum() }} catatan · {{ $absensi->get('hadir', 0) }} hadir · {{ $absensi->get('telat', 0) }} telat · {{ $absensi->get('izin', 0) + $absensi->get('sakit', 0) }} izin/sakit · {{ $absensi->get('alpa', 0) }} alpa</td></tr>
            </table>
        </div>

        {{-- C. Project --}}
        <div class="mt-5 jangan-terpotong">
            <p class="text-xs font-black text-gray-800 uppercase tracking-widest">C. Project &amp; Kegiatan</p>
            @if($rj['project'] !== [])
                <table class="w-full text-xs font-semibold text-gray-700 mt-2 border border-gray-200">
                    <tr class="bg-gray-50">
                        <td class="p-2 border border-gray-200 font-black">Project</td>
                        <td class="p-2 border border-gray-200 font-black w-24 text-center">Status</td>
                        <td class="p-2 border border-gray-200 font-black w-20 text-center">Nilai</td>
                        <td class="p-2 border border-gray-200 font-black w-16 text-center">Predikat</td>
                    </tr>
                    @foreach($rj['project'] as $pr)
                        <tr>
                            <td class="p-2 border border-gray-200">{{ $pr['nama'] }}</td>
                            <td class="p-2 border border-gray-200 text-center">{{ ucfirst($pr['status']) }}</td>
                            <td class="p-2 border border-gray-200 text-center">{{ $pr['nilai_akhir'] !== null ? str_replace('.', ',', (string) $pr['nilai_akhir']) : '—' }}</td>
                            <td class="p-2 border border-gray-200 text-center">{{ $predikat($pr['nilai_akhir']) }}</td>
                        </tr>
                    @endforeach
                </table>
                <p class="text-xs font-semibold text-gray-500 mt-1">Berjalan: {{ $projectJalan->count() }} · Selesai: {{ $projectSelesai->count() }}</p>
            @else
                <p class="text-xs font-semibold text-gray-500 mt-1">Belum ada project pada grup ananda.</p>
            @endif
        </div>

        {{-- D. Adab & Keasramaan --}}
        <div class="mt-5 jangan-terpotong">
            <p class="text-xs font-black text-gray-800 uppercase tracking-widest">D. Penilaian Adab &amp; Keasramaan</p>
            @forelse($rj['adab'] as $blok)
                <p class="text-xs font-black text-gray-700 mt-2">{{ $blok['periode']->nama }}</p>
                <table class="w-full text-xs font-semibold text-gray-700 border border-gray-200">
                    <tr class="bg-gray-50">
                        <td class="p-2 border border-gray-200 font-black">Aspek</td>
                        <td class="p-2 border border-gray-200 font-black w-20 text-center">Nilai</td>
                    </tr>
                    @foreach($blok['jenis'] as $j)
                        @foreach($j['aspek'] as $a)
                            <tr>
                                <td class="p-2 border border-gray-200">{{ $j['label'] }} — {{ $a->pertanyaan }}</td>
                                <td class="p-2 border border-gray-200 text-center">{{ str_replace('.', ',', (string) $a->rata) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="p-2 border border-gray-200 font-black">Rata-rata {{ $j['label'] }} ({{ $j['aspek']->count() }} aspek)</td>
                            <td class="p-2 border border-gray-200 text-center font-black">{{ str_replace('.', ',', (string) $j['persen']) }}% · {{ $j['predikat'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @empty
                <p class="text-xs font-semibold text-gray-500 mt-1">Belum ada penilaian Adab &amp; Keasramaan yang masuk untuk periode mana pun.</p>
            @endforelse
        </div>

        {{-- E. Catatan --}}
        <div class="mt-5 jangan-terpotong">
            <p class="text-xs font-black text-gray-800 uppercase tracking-widest">E. Catatan</p>
            @forelse($rj['catatan'] as $cat)
                <p class="text-xs font-semibold text-gray-700 mt-1">
                    <b>{{ $cat->jenis === 'adab' ? 'Musyrif' : 'Mentor' }}:</b> {{ $cat->isi }}
                </p>
            @empty
                <p class="text-xs font-semibold text-gray-500 mt-1">Belum ada catatan dari musyrif/mentor.</p>
            @endforelse
        </div>

        <p class="text-xs font-semibold text-gray-500 mt-6 pt-3 border-t border-gray-200">
            Halaman ini dihasilkan otomatis dari data SIAKAD SMA IT Arafah dan hanya memuat data ananda.
            Bila terdapat kekeliruan data, mohon menghubungi Tata Usaha sekolah.
        </p>
    </div>
</div>
</body>
</html>
