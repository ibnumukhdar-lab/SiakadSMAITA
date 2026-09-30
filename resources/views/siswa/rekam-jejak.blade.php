<x-app-layout>
    <style>
        @media print {
            nav, header, .min-h-screen > nav, .min-h-screen > header, .sembunyikan-saat-print { display: none !important; }
            body, main, .min-h-screen, .bg-gray-100 { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            .py-12, .py-8 { padding-top: 0 !important; padding-bottom: 0 !important; }
            .shadow, .shadow-sm, .shadow-md { box-shadow: none !important; }
            .jangan-terpotong { page-break-inside: avoid !important; break-inside: avoid !important; margin-bottom: 18px !important; }
        }
    </style>

    @php
        $s = $rj['siswa'];
        $inisial = collect(explode(' ', trim((string) $s->nama_lengkap)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
        $poin = $rj['poin'];
        $inspeksi = $rj['asrama']['inspeksi'];
        $absensi = $rj['asrama']['absensi'];
        $persenKebersihan = $inspeksi['rata'] !== null ? round($inspeksi['rata'] / 25 * 100) : null;
        $nomor = 0;
    @endphp

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Judul + tombol --}}
            <div class="flex flex-wrap items-center justify-between gap-3 sembunyikan-saat-print">
                <div>
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Data Induk Siswa › Rekam Jejak</p>
                    <h1 class="text-xl font-black text-gray-800">{{ $s->nama_lengkap }}</h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-xs font-black">Cetak Rekam Jejak</button>
                    <a href="{{ route('siswa.rekamJejak.ringkas', ['id' => $s->id]) }}" target="_blank" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800 border border-gray-300 text-xs font-black">Ringkas untuk WhatsApp</a>
                    <a href="{{ route('siswa.show', ['id' => $s->id]) }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800 border border-gray-300 text-xs font-black">Data Induk</a>
                </div>
            </div>

            {{-- Identitas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-lg font-black text-gray-500">{{ $inisial }}</div>
                    <div class="flex-1">
                        <p class="text-lg font-black text-gray-800">{{ $s->nama_lengkap }}</p>
                        <p class="text-xs text-gray-600 font-semibold mt-1">
                            NISN {{ $s->nisn ?: '-' }} · NIS {{ $s->nis ?: '-' }} · Kelas {{ $s->kelas ?: '-' }} · {{ $s->jk ?: '-' }} · {{ $s->status ?: '-' }}
                        </p>
                        <p class="text-xs text-gray-600 font-semibold mt-1">
                            {{ $s->tempat_lahir ?: \App\Support\SandiOrtu::tempatLahir($s->ttl) }}{{ $s->tanggal_lahir ? ', '.\Carbon\Carbon::parse($s->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : ($s->ttl ? ', '.(\App\Support\SandiOrtu::tanggalLahir($s->ttl) ?: $s->ttl) : ' — tanggal lahir belum lengkap, orang tua belum bisa masuk portal') }}
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2 py-1 rounded bg-gray-100 border border-gray-200 text-xs font-bold text-gray-700">Kamar: {{ $rj['kamar']?->nama_kamar ?? 'belum ada' }}</span>
                            <span class="px-2 py-1 rounded bg-gray-100 border border-gray-200 text-xs font-bold text-gray-700">Grup SR: {{ $rj['grup']?->nama_grup ?? 'belum ada' }}</span>
                            <span class="px-2 py-1 rounded bg-gray-100 border border-gray-200 text-xs font-bold text-gray-700">Mentor: {{ $rj['grup']?->mentor ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 jangan-terpotong">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Poin Karakter</p>
                    <p class="text-2xl font-black {{ $poin['total'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $poin['total'] > 0 ? '+' : '' }}{{ $poin['total'] }}</p>
                    <p class="text-xs text-gray-600 font-semibold">{{ $poin['entri'] }} kejadian · {{ $poin['negatif'] }} negatif</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Project</p>
                    <p class="text-2xl font-black text-gray-800">{{ count($rj['project']) }}</p>
                    <p class="text-xs text-gray-600 font-semibold">berjalan: {{ collect($rj['project'])->where('status', 'berjalan')->count() }} · selesai: {{ collect($rj['project'])->where('status', 'selesai')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Kebersihan Kamar</p>
                    @if($persenKebersihan !== null)
                        <p class="text-2xl font-black text-gray-800">{{ $persenKebersihan }}%</p>
                        <p class="text-xs text-gray-600 font-semibold">rata {{ str_replace('.', ',', (string) $inspeksi['rata']) }} / 25 · {{ $inspeksi['terbersih'] }}× terbersih</p>
                    @else
                        <p class="text-2xl font-black text-gray-400">—</p>
                        <p class="text-xs text-gray-600 font-semibold">belum ada inspeksi kamar</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Adab &amp; Keasramaan</p>
                    @if($rj['adab'] !== [])
                        <p class="text-2xl font-black text-gray-800">{{ count($rj['adab']) }}</p>
                        <p class="text-xs text-gray-600 font-semibold">periode sudah dinilai</p>
                    @else
                        <p class="text-2xl font-black text-gray-400">—</p>
                        <p class="text-xs text-amber-700 font-bold">belum ada penilaian masuk</p>
                    @endif
                </div>
            </div>

            {{-- Poin karakter --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                <h2 class="text-sm font-black text-gray-800 mb-3">Poin Karakter (Student Root) — perilaku terbanyak</h2>
                @forelse($poin['terbanyak'] as $t)
                    <div class="flex items-center gap-3 py-1.5 border-b border-gray-100 last:border-0">
                        <span class="text-xs font-bold text-gray-700 flex-1">{{ $t->nama_perilaku }}</span>
                        <span class="text-xs font-black {{ $t->poin >= 0 ? 'text-green-700' : 'text-red-700' }} w-10 text-right">{{ $t->poin > 0 ? '+' : '' }}{{ $t->poin }}</span>
                        <span class="text-xs font-black text-gray-800 w-12 text-right">{{ $t->jumlah }}×</span>
                    </div>
                @empty
                    <p class="text-xs font-semibold text-gray-500">Belum ada catatan poin untuk siswa ini.</p>
                @endforelse

                @if($poin['terbaru']->isNotEmpty())
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mt-4 mb-2">10 catatan terbaru</p>
                    @foreach($poin['terbaru'] as $c)
                        <div class="flex items-start gap-3 py-1.5 border-b border-gray-100 last:border-0">
                            <span class="text-xs font-semibold text-gray-500 w-24">{{ $c->tanggal_kejadian ? \Carbon\Carbon::parse($c->tanggal_kejadian)->format('d/m/Y') : '-' }}</span>
                            <span class="text-xs font-bold text-gray-700 flex-1">{{ $c->nama_perilaku }}{{ $c->catatan ? " — ".$c->catatan : "" }}</span>
                            <span class="text-xs font-black {{ $c->poin >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $c->poin > 0 ? '+' : '' }}{{ $c->poin }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Project --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                <h2 class="text-sm font-black text-gray-800 mb-3">Project Student Root (grup {{ $rj['grup']?->nama_grup ?? '-' }})</h2>
                @forelse($rj['project'] as $pr)
                    <div class="border border-gray-200 rounded-lg p-4 mb-3 last:mb-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-black text-gray-800">{{ $pr['nama'] }}</p>
                                <p class="text-xs text-gray-600 font-semibold">
                                    Mulai {{ $pr['mulai'] ? \Carbon\Carbon::parse($pr['mulai'])->format('d/m/Y') : '-' }}
                                    {{ $pr['selesai'] ? ' · Selesai '.\Carbon\Carbon::parse($pr['selesai'])->format('d/m/Y') : '' }}
                                    · Portofolio: {{ $pr['portofolio'] ? 'sudah disusun' : 'belum disusun' }}
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-black border {{ $pr['status'] === 'selesai' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">{{ strtoupper($pr['status']) }}</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-3">
                            @foreach($pr['tahap'] as $t)
                                <div class="rounded-lg border {{ $t['skor'] !== null ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} p-2 text-center">
                                    <p class="text-xs font-black text-gray-600">{{ $t['nama'] }}</p>
                                    <p class="text-xs font-black text-gray-800">{{ $t['skor'] !== null ? str_replace('.', ',', (string) $t['skor']) : '—' }}</p>
                                    <p class="text-xs text-gray-500 font-semibold">bobot {{ (int) $t['bobot'] }}%</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="text-xs font-bold text-gray-700 mt-2">Nilai akhir: {{ $pr['nilai_akhir'] !== null ? str_replace('.', ',', (string) $pr['nilai_akhir']) : 'belum dinilai' }}</p>
                    </div>
                @empty
                    <p class="text-xs font-semibold text-gray-500">Belum ada project untuk grup siswa ini.</p>
                @endforelse
            </div>

            {{-- Asrama --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                <h2 class="text-sm font-black text-gray-800 mb-3">Asrama — kamar {{ $rj['kamar']?->nama_kamar ?? '-' }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Lembar inspeksi</p>
                        <p class="text-lg font-black text-gray-800">{{ $inspeksi['lembar'] }}</p>
                        <p class="text-xs text-gray-600 font-semibold">pagi {{ $inspeksi['pagi'] }} · sore {{ $inspeksi['sore'] }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Rata-rata skor</p>
                        <p class="text-lg font-black text-gray-800">{{ $inspeksi['rata'] !== null ? str_replace('.', ',', (string) $inspeksi['rata']) : '—' }} / 25</p>
                        <p class="text-xs text-gray-600 font-semibold">terbaik {{ $inspeksi['terbaik'] ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Kamar terbersih</p>
                        <p class="text-lg font-black text-green-700">{{ $inspeksi['terbersih'] }}×</p>
                        <p class="text-xs text-gray-600 font-semibold">terkotor {{ $inspeksi['terkotor'] }}×</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Absensi jam tidur</p>
                        <p class="text-lg font-black text-gray-800">{{ $absensi->sum() }}</p>
                        <p class="text-xs text-gray-600 font-semibold">{{ $absensi->get('hadir', 0) }} hadir · {{ $absensi->get('telat', 0) }} telat · {{ $absensi->get('alpa', 0) }} alpa</p>
                    </div>
                </div>

                @if($rj['asrama']['izin']->isNotEmpty())
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mt-4 mb-2">Izin terakhir</p>
                    @foreach($rj['asrama']['izin'] as $iz)
                        <div class="text-xs font-semibold text-gray-700 py-1 border-b border-gray-100 last:border-0">
                            {{ ucfirst($iz->jenis) }} · {{ \Carbon\Carbon::parse($iz->mulai)->format('d/m/Y') }}{{ $iz->sampai ? ' – '.\Carbon\Carbon::parse($iz->sampai)->format('d/m/Y') : '' }} · status {{ $iz->status }}{{ $iz->terlambat_menit ? ' · terlambat '.$iz->terlambat_menit.' menit' : '' }}
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Adab & Keasramaan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                <h2 class="text-sm font-black text-gray-800 mb-3">Penilaian Adab &amp; Keasramaan</h2>
                @forelse($rj['adab'] as $blok)
                    <div class="mb-4 last:mb-0">
                        <p class="text-xs font-black text-gray-700">{{ $blok['periode']->nama }}{{ $blok['periode']->tahun_ajaran ? ' · '.$blok['periode']->tahun_ajaran : '' }}</p>
                        @foreach($blok['jenis'] as $j)
                            <p class="text-xs font-black text-gray-500 uppercase tracking-widest mt-2">{{ $j['label'] }} — {{ str_replace('.', ',', (string) $j['persen']) }}% ({{ $j['predikat'] }})</p>
                            @foreach($j['aspek'] as $a)
                                <div class="flex items-center gap-2 py-1 border-b border-gray-100 last:border-0">
                                    <span class="text-xs font-semibold text-gray-700 flex-1">{{ $a->pertanyaan }}</span>
                                    <span class="text-xs font-black text-gray-800">{{ str_replace('.', ',', (string) $a->rata) }} / 5</span>
                                    <span class="text-xs text-gray-500 w-16 text-right">{{ $a->penilai }} penilai</span>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @empty
                    <p class="text-xs font-semibold text-amber-700">Belum ada penilaian Adab &amp; Keasramaan yang masuk untuk periode mana pun. Penilaian diisi oleh musyrif/musyrifah melalui menu Rapor Adab &amp; Keasramaan.</p>
                @endforelse
            </div>

            {{-- Catatan rapot --}}
            @if($rj['catatan'] !== [])
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 jangan-terpotong">
                    <h2 class="text-sm font-black text-gray-800 mb-3">Catatan Rapor</h2>
                    @foreach($rj['catatan'] as $cat)
                        <div class="mb-3 last:mb-0">
                            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $cat->jenis === 'adab' ? 'Adab & Keasramaan' : 'Student Root' }} · {{ $cat->penulis ?? 'penulis tidak diketahui' }}</p>
                            <p class="text-xs font-semibold text-gray-700 mt-1">{{ $cat->isi }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Info portal orang tua --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sembunyikan-saat-print">
                <h2 class="text-sm font-black text-gray-800 mb-2">Portal Orang Tua</h2>
                @if($sandiSiap)
                    <p class="text-xs font-semibold text-gray-600">Orang tua dapat membuka <b>{{ $alamatOrtu }}</b> lalu memasukkan NISN anak dan kata sandi berupa tanggal lahir anak dengan format ddmmyyyy.</p>
                @else
                    <p class="text-xs font-semibold text-amber-700">Tanggal lahir siswa ini belum lengkap, jadi portal orang tua belum bisa dibuka. Lengkapi kolom tempat/tanggal lahir di Data Induk supaya orang tua bisa masuk.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
