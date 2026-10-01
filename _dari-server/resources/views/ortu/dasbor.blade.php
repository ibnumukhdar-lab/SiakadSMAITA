@php
    $s = $rj['siswa'];
    $poin = $rj['poin'];
    $inspeksi = $rj['asrama']['inspeksi'];
    $absensi = $rj['asrama']['absensi'];
    $persenKebersihan = $inspeksi['rata'] !== null ? round($inspeksi['rata'] / 25 * 100) : null;
    $inisial = collect(explode(' ', trim((string) $s->nama_lengkap)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $projectJalan = collect($rj['project'])->where('status', 'berjalan');
    $projectSelesai = collect($rj['project'])->where('status', 'selesai');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perkembangan {{ $s->nama_lengkap }} — Portal Orang Tua</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">

<header class="bg-gray-800 text-white">
    <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <div>
            <p class="text-xs font-black uppercase tracking-widest opacity-80">Portal Orang Tua</p>
            <p class="text-sm font-black">SMA IT Arafah</p>
        </div>
        <form method="POST" action="{{ route('ortu.keluar') }}">
            @csrf
            <button type="submit" class="px-3 py-2 rounded-lg bg-white text-gray-800 text-xs font-black">Keluar</button>
        </form>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-5 space-y-4">

    {{-- Identitas anak --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-lg font-black text-gray-500">{{ $inisial }}</div>
            <div class="flex-1">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Perkembangan ananda</p>
                <p class="text-lg font-black text-gray-800">{{ $s->nama_lengkap }}</p>
                <p class="text-xs font-semibold text-gray-600 mt-0.5">
                    Kelas {{ $s->kelas ?: '-' }} · Kamar {{ $rj['kamar']?->nama_kamar ?? '-' }} · Grup {{ $rj['grup']?->nama_grup ?? '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Ringkasan bahasa manusia --}}
    <div class="bg-gray-800 text-white rounded-xl p-5">
        <p class="text-xs font-black uppercase tracking-widest opacity-80">Ringkasan</p>
        <p class="text-sm font-semibold mt-2 leading-relaxed">
            Alhamdulillah, sampai hari ini ananda <b>{{ $s->nama_lengkap }}</b>
            @if($poin['entri'] > 0)
                telah mengumpulkan <b>{{ $poin['total'] > 0 ? '+' : '' }}{{ $poin['total'] }} poin karakter</b> dari <b>{{ $poin['entri'] }} kejadian</b>
                @if($poin['positif'] > 0 || $poin['negatif'] > 0)
                    <span>({{ $poin['positif'] }} positif, {{ $poin['negatif'] }} negatif)</span>
                @endif
                .
            @else
                belum memiliki catatan poin karakter.
            @endif
            @if($persenKebersihan !== null)
                Kebersihan kamarnya rata-rata <b>{{ $persenKebersihan }}%</b>
                @if($inspeksi['terbersih'] > 0)
                    <span>dan <b>{{ $inspeksi['terbersih'] }}×</b> dinilai sebagai kamar terbersih</span>
                @endif
                .
            @endif
            @if($projectJalan->isNotEmpty())
                Project yang sedang dikerjakan: <b>{{ $projectJalan->pluck('nama')->implode(', ') }}</b>.
            @endif
        </p>
    </div>

    {{-- Kartu angka --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Poin karakter</p>
            <p class="text-2xl font-black {{ $poin['total'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $poin['total'] > 0 ? '+' : '' }}{{ $poin['total'] }}</p>
            <p class="text-xs text-gray-600 font-semibold">{{ $poin['entri'] }} kejadian</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Kebersihan kamar</p>
            @if($persenKebersihan !== null)
                <p class="text-2xl font-black text-gray-800">{{ $persenKebersihan }}%</p>
                <p class="text-xs text-gray-600 font-semibold">{{ $inspeksi['terbersih'] }}× terbersih</p>
            @else
                <p class="text-2xl font-black text-gray-400">—</p>
                <p class="text-xs text-gray-600 font-semibold">belum ada inspeksi</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Project</p>
            <p class="text-2xl font-black text-gray-800">{{ count($rj['project']) }}</p>
            <p class="text-xs text-gray-600 font-semibold">{{ $projectJalan->count() }} berjalan · {{ $projectSelesai->count() }} selesai</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Adab &amp; keasramaan</p>
            @if($rj['adab'] !== [])
                <p class="text-2xl font-black text-gray-800">{{ count($rj['adab']) }}</p>
                <p class="text-xs text-gray-600 font-semibold">periode sudah dinilai</p>
            @else
                <p class="text-2xl font-black text-gray-400">—</p>
                <p class="text-xs text-amber-700 font-bold">belum ada penilaian</p>
            @endif
        </div>
    </div>

    {{-- Perilaku terbanyak --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-black text-gray-800 mb-3">Yang paling sering ananda lakukan</h2>
        @forelse($poin['terbanyak'] as $t)
            <div class="flex items-center gap-3 py-1.5 border-b border-gray-100 last:border-0">
                <span class="text-xs font-bold text-gray-700 flex-1">{{ $t->nama_perilaku }}</span>
                <span class="text-xs font-black {{ $t->poin >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $t->poin > 0 ? '+' : '' }}{{ $t->poin }}</span>
                <span class="text-xs font-black text-gray-800 w-12 text-right">{{ $t->jumlah }}×</span>
            </div>
        @empty
            <p class="text-xs font-semibold text-gray-500">Belum ada catatan poin.</p>
        @endforelse
    </div>

    {{-- Project --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-black text-gray-800 mb-3">Project &amp; kegiatan</h2>
        @forelse($rj['project'] as $pr)
            <div class="border border-gray-200 rounded-lg p-3 mb-3 last:mb-0">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-sm font-black text-gray-800">{{ $pr['nama'] }}</p>
                    <span class="px-2 py-1 rounded text-xs font-black border {{ $pr['status'] === 'selesai' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">{{ strtoupper($pr['status']) }}</span>
                </div>
                <p class="text-xs font-semibold text-gray-600 mt-1">
                    Mulai {{ $pr['mulai'] ? \Carbon\Carbon::parse($pr['mulai'])->locale('id')->translatedFormat('d F Y') : '-' }}
                    · Nilai ananda: {{ $pr['nilai_akhir'] !== null ? str_replace('.', ',', (string) $pr['nilai_akhir']) : 'belum dinilai' }}
                </p>
            </div>
        @empty
            <p class="text-xs font-semibold text-gray-500">Belum ada project pada grup ananda.</p>
        @endforelse
    </div>

    {{-- Asrama ringkas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-black text-gray-800 mb-3">Asrama</h2>
        <div class="grid grid-cols-2 gap-3 text-xs font-semibold text-gray-700">
            <p>Kamar: <b>{{ $rj['kamar']?->nama_kamar ?? '-' }}</b></p>
            <p>Lembar inspeksi: <b>{{ $inspeksi['lembar'] }}</b></p>
            <p>Rata-rata kebersihan: <b>{{ $inspeksi['rata'] !== null ? str_replace('.', ',', (string) $inspeksi['rata']) . ' / 25' : '—' }}</b></p>
            <p>Kamar terbersih: <b>{{ $inspeksi['terbersih'] }}×</b></p>
            <p>Absensi jam tidur: <b>{{ $absensi->sum() }}</b> catatan</p>
            <p>Hadir: <b>{{ $absensi->get('hadir', 0) }}</b> · Telat: <b>{{ $absensi->get('telat', 0) }}</b> · Izin/sakit: <b>{{ $absensi->get('izin', 0) + $absensi->get('sakit', 0) }}</b> · Alpa: <b>{{ $absensi->get('alpa', 0) }}</b></p>
        </div>
    </div>

    {{-- Adab & catatan --}}
    @if($rj['adab'] !== [] || $rj['catatan'] !== [])
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-black text-gray-800 mb-3">Penilaian &amp; catatan</h2>
            @foreach($rj['adab'] as $blok)
                <p class="text-xs font-black text-gray-700">{{ $blok['periode']->nama }}</p>
                @foreach($blok['jenis'] as $j)
                    <p class="text-xs font-semibold text-gray-700">• {{ $j['label'] }}: <b>{{ str_replace('.', ',', (string) $j['persen']) }}%</b> (predikat {{ $j['predikat'] }})</p>
                @endforeach
            @endforeach
            @foreach($rj['catatan'] as $cat)
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest mt-3">{{ $cat->jenis === 'adab' ? 'Catatan musyrif' : 'Catatan mentor' }}</p>
                <p class="text-xs font-semibold text-gray-700">{{ $cat->isi }}</p>
            @endforeach
        </div>
    @endif

    {{-- Rapor --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-black text-gray-800 mb-3">Rapor</h2>
        <a href="{{ route('ortu.rapor') }}" class="block text-center px-4 py-3 rounded-lg bg-gray-800 text-white text-sm font-black">Lihat Rapor Ananda</a>
        <p class="text-xs text-gray-500 font-semibold mt-3">
            Halaman ini hanya-baca. Bila ada data yang perlu diperbaiki, mohon menghubungi Tata Usaha sekolah.
        </p>
    </div>

    <p class="text-center text-xs text-gray-500 font-semibold pb-6">
        Masuk sejak {{ \Carbon\Carbon::parse(session('ortu_masuk_pada'))->locale('id')->translatedFormat('d F Y H:i') }} WIB
    </p>
</main>
</body>
</html>
