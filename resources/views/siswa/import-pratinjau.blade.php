<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🔍 Pratinjau Impor</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Belum ada data yang masuk — periksa dulu di sini</p>
                </div>
                <a href="{{ route('siswa.import') }}" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">← Ganti berkas</a>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-3.5 mb-3 text-[12.5px] text-slate-600">
                <p><strong class="text-slate-800">Berkas:</strong> {{ $data['berkas'] ?? '-' }}</p>
                <p class="mt-1">
                    <strong class="text-slate-800">Cara membaca kolom:</strong>
                    @if(($data['peta']['mode'] ?? 'urutan') === 'judul')
                        dari judul kolom —
                        @foreach(($data['peta']['dikenali'] ?? []) as $field => $judulAsli)
                            <span class="inline-block bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 mr-1 mt-1">{{ $judulAsli }} → {{ $field }}</span>
                        @endforeach
                    @else
                        judul kolom tidak dikenali, dipakai urutan kolom template bawaan.
                    @endif
                </p>
            </div>

            {{-- Ringkasan --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                    <p class="text-2xl font-extrabold text-green-700">{{ count($data['siap'] ?? []) }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-green-700 mt-0.5">Siap diimpor</p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                    <p class="text-2xl font-extrabold text-amber-700">{{ count($data['duplikat'] ?? []) }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-amber-700 mt-0.5">Sudah terdaftar</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                    <p class="text-2xl font-extrabold text-red-700">{{ count($data['masalah'] ?? []) }}</p>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-red-700 mt-0.5">Bermasalah</p>
                </div>
            </div>

            {{-- Aksi --}}
            <div class="flex flex-col sm:flex-row gap-2 mb-4">
                @if(count($data['siap'] ?? []) > 0)
                    <form action="{{ route('siswa.eksekusiImpor') }}" method="POST" class="flex-1"
                          onsubmit="return confirm('Simpan {{ count($data['siap']) }} siswa ke Data Induk sekarang?')">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="w-full h-11 rounded-lg bg-blue-900 text-white text-sm font-bold hover:bg-blue-800 transition">
                            ✅ Impor {{ count($data['siap']) }} siswa sekarang
                        </button>
                    </form>
                @else
                    <div class="flex-1 bg-slate-100 border border-slate-200 rounded-lg h-11 flex items-center justify-center text-[13px] font-semibold text-slate-500">
                        Tidak ada baris yang bisa diimpor
                    </div>
                @endif

                @if(count($data['masalah'] ?? []) > 0 || count($data['duplikat'] ?? []) > 0)
                    <a href="{{ route('siswa.laporanImpor', ['token' => $token]) }}"
                       class="h-11 px-4 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">
                        ⬇️ Unduh laporan masalah
                    </a>
                @endif
            </div>

            {{-- Daftar yang akan masuk --}}
            @if(count($data['siap'] ?? []) > 0)
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-4">
                    <div class="px-3.5 py-3 border-b border-slate-200 flex items-center justify-between">
                        <h4 class="text-[13px] font-bold uppercase tracking-wider text-slate-500">Yang akan masuk</h4>
                        <span class="text-[11px] text-slate-400">maks 40 ditampilkan</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[520px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">NISN</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">JK</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Kelas</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($data['siap'], 0, 40) as $baris)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-3 py-2 text-[13px] font-semibold text-slate-800">{{ $baris['nama_lengkap'] }}</td>
                                        <td class="px-3 py-2 text-[13px] text-slate-600 font-mono">{{ $baris['nisn'] }}</td>
                                        <td class="px-3 py-2 text-[12px] text-slate-500 text-center">{{ $baris['jk'] ? substr($baris['jk'], 0, 1) : '-' }}</td>
                                        <td class="px-3 py-2 text-[12px] text-slate-600 text-center">{{ $baris['kelas'] ?? '-' }}</td>
                                        <td class="px-3 py-2 text-[12px] text-slate-600 text-center">{{ $baris['status'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Masalah --}}
            @if(count($data['masalah'] ?? []) > 0)
                <div class="bg-red-50 border border-red-200 rounded-xl p-3.5 mb-4">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider text-red-700 mb-2">Baris bermasalah (tidak diimpor)</h4>
                    <ul class="space-y-1 text-[12.5px] text-red-900">
                        @foreach(array_slice($data['masalah'], 0, 25) as $m)
                            <li><span class="font-mono text-red-700">Baris {{ $m['baris'] }}</span> — <strong>{{ $m['nama'] }}</strong>: {{ $m['alasan'] }}</li>
                        @endforeach
                    </ul>
                    @if(count($data['masalah']) > 25)
                        <p class="text-[12px] text-red-700 mt-2">…dan {{ count($data['masalah']) - 25 }} baris lain (unduh laporan untuk semuanya).</p>
                    @endif
                </div>
            @endif

            {{-- Duplikat --}}
            @if(count($data['duplikat'] ?? []) > 0)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider text-amber-700 mb-2">Sudah terdaftar (dilewati)</h4>
                    <ul class="space-y-1 text-[12.5px] text-amber-900">
                        @foreach(array_slice($data['duplikat'], 0, 15) as $d)
                            <li><span class="font-mono text-amber-700">Baris {{ $d['baris'] }}</span> — <strong>{{ $d['nama'] }}</strong>: {{ $d['alasan'] }}</li>
                        @endforeach
                    </ul>
                    @if(count($data['duplikat']) > 15)
                        <p class="text-[12px] text-amber-700 mt-2">…dan {{ count($data['duplikat']) - 15 }} baris lain.</p>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
