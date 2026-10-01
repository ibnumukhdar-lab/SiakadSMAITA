<x-app-layout>
    <div class="py-5 sm:py-8" x-data="{
        dari: [],
        jumlah: {
            @foreach($kelas as $k)
                '{{ addslashes($k->nama) }}': {{ (int) ($jumlahPerKelas[$k->nama] ?? 0) }},
            @endforeach
        },
        ke: '',
        tahun: '{{ $tahunAktif?->nama }}',
        jadikanAktif: true,
        get totalPilih() {
            return this.dari.reduce((n, k) => n + (this.jumlah[k] || 0), 0);
        },
        get siapProses() {
            return this.dari.length > 0 && this.ke !== '' && !(this.dari.length === 1 && this.dari[0] === this.ke);
        }
    }">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">⬆️ Kenaikan Kelas</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Pindahkan seluruh siswa satu kelas sekaligus (massal)</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('kelas.index') }}"
                       class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">🏫 Kelola Kelas</a>
                    <a href="{{ route('siswa.index') }}"
                       class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">🎓 Data Siswa</a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-4 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Proses ini hanya mengubah <strong>kelas</strong>, <strong>status</strong>, dan <strong>tahun ajaran</strong> siswa.
                Kamar <strong>Asrama</strong> dan grup <strong>Student Root</strong> tidak tersentuh — keduanya membaca data siswa
                berdasarkan ID siswa, jadi tetap utuh. Jumlah {{ number_format($totalSiswa, 0, ',', '.') }} siswa aktif akan dihitung ulang setelah proses.
            </div>

            <form action="{{ route('kenaikan.proses') }}" method="POST"
                  onsubmit="return confirm('Proses kenaikan/per pindahan kelas ini sekarang?')">
                @csrf

                {{-- Langkah 1 --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                    <div class="flex items-center justify-between gap-3 mb-2.5">
                        <h4 class="text-[13.5px] font-bold text-slate-800">1. Pilih kelas asal</h4>
                        <button type="button" @click="dari = (dari.length === {{ $kelas->count() }}) ? [] : {{ \Illuminate\Support\Js::from($kelas->pluck('nama')->all()) }}"
                                class="text-[12px] font-semibold text-blue-800 hover:underline">Pilih semua / kosongkan</button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($kelas as $k)
                            @php $jumlahKelas = (int) ($jumlahPerKelas[$k->nama] ?? 0); @endphp
                            <label class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 cursor-pointer transition"
                                   :class="dari.includes('{{ addslashes($k->nama) }}') ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                <input type="checkbox" name="dari[]" value="{{ $k->nama }}" x-model="dari" class="rounded border-slate-300">
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-bold text-slate-800 truncate">{{ $k->nama }}</span>
                                    <span class="block text-[11px] {{ $jumlahKelas > 0 ? 'text-slate-500' : 'text-slate-300' }}">{{ $jumlahKelas }} siswa</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @if($kelas->isEmpty())
                        <p class="text-[12.5px] text-slate-500">Belum ada kelas. <a href="{{ route('kelas.index') }}" class="text-blue-800 underline">Tambahkan di Kelola Kelas</a>.</p>
                    @endif
                </div>

                {{-- Langkah 2 --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                    <h4 class="text-[13.5px] font-bold text-slate-800 mb-2.5">2. Kelas tujuan</h4>
                    <select name="ke" x-model="ke" required
                            class="w-full h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                        <option value="">-- pilih kelas tujuan --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                        @endforeach
                        <option value="__lulus__">Lulus / Alumni</option>
                    </select>
                    <p class="text-[11.5px] text-slate-400 mt-1.5">
                        Pilih <strong>Lulus / Alumni</strong> untuk menamatkan siswa kelas XII — kelasnya menjadi "Lulus" dan statusnya Alumni.
                    </p>
                </div>

                {{-- Langkah 3 --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                    <h4 class="text-[13.5px] font-bold text-slate-800 mb-2.5">3. Tahun ajaran &amp; status</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
                        <div class="col-span-2 sm:col-span-3">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tahun ajaran baru</label>
                            <select name="tahun_ajaran" x-model="tahun"
                                    class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">-- jangan ubah --</option>
                                @foreach($daftarTahun as $namaTahun)
                                    <option value="{{ $namaTahun }}">{{ $namaTahun }}{{ $tahunAktif && $tahunAktif->nama === $namaTahun ? ' (aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-3">
                            <label class="inline-flex items-center gap-2 text-[13px] text-slate-600 pb-2.5">
                                <input type="checkbox" name="jadikan_aktif" value="1" x-model="jadikanAktif" class="rounded border-slate-300">
                                Set status siswa menjadi Aktif
                            </label>
                        </div>
                    </div>
                    @if(! $tahunAktif)
                        <p class="text-[11.5px] text-amber-700 mt-1.5">
                            Belum ada tahun ajaran aktif. <a href="{{ route('tahun-ajaran.index') }}" class="underline">Atur di menu Tahun Ajaran</a>.
                        </p>
                    @endif
                </div>

                {{-- Ringkasan --}}
                <div class="bg-slate-900 text-white rounded-2xl p-4 sm:p-5 mb-3">
                    <p class="text-[12px] uppercase tracking-wider text-slate-400 font-bold mb-1">Ringkasan</p>
                    <p class="text-[15px] font-bold leading-relaxed">
                        <span x-text="totalPilih"></span> siswa akan dipindahkan
                        <template x-if="dari.length > 0">
                            <span> dari kelas <span class="text-blue-300" x-text="dari.join(', ')"></span></span>
                        </template>
                        <template x-if="ke !== ''">
                            <span> ke <span class="text-emerald-300" x-text="ke === '__lulus__' ? 'Lulus/Alumni' : ke"></span></span>
                        </template>
                        <template x-if="tahun !== ''">
                            <span> · tahun ajaran <span class="text-amber-300" x-text="tahun"></span></span>
                        </template>
                    </p>
                    <p class="text-[12px] text-slate-400 mt-1" x-show="!siapProses">
                        Pilih minimal satu kelas asal dan satu kelas tujuan (tidak boleh sama).
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" :disabled="!siapProses"
                            class="h-11 px-5 rounded-xl bg-blue-900 text-white text-sm font-bold hover:bg-blue-800 transition disabled:opacity-40 disabled:cursor-not-allowed w-full sm:w-auto">
                        ⬆️ Proses kenaikan / pindah kelas
                    </button>
                    <a href="{{ route('siswa.index') }}"
                       class="h-11 px-4 inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
