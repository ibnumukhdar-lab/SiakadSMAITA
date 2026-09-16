<x-app-layout>
    <div class="py-8" x-data="{
        hapusModalOpen: false,
        hapusFormId: '',
        hapusNama: '',
        hapusNisn: ''
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Judul halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🗑️ Tong Sampah Data Siswa</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Data yang dipindahkan ke sini masih bisa dipulihkan</p>
                </div>
                <a href="{{ route('siswa.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    ← Kembali ke Data Induk
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-4 mb-4 rounded shadow-sm text-sm">
                ℹ️ Menghapus data dari halaman Data Induk <strong>tidak</strong> menghapus permanen — datanya masuk ke sini.
                Hapus permanen hanya bisa dilakukan dari halaman ini dan hanya oleh pemegang izin <em>hapus-permanen-siswa</em>.
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 flex items-center justify-between gap-3">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider text-slate-500">Isi tong sampah</h4>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $siswas->count() }} data</span>
                </div>

                @if($siswas->isEmpty())
                    <div class="p-10 text-center text-slate-500 font-medium">
                        <span class="text-3xl block mb-2">📭</span>
                        Tong sampah kosong.
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach($siswas as $siswa)
                            <li class="px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center gap-3 hover:bg-slate-50/70 transition">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $siswa->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <span class="font-mono">{{ $siswa->nisn }}</span>
                                        <span class="mx-1">·</span>Kelas {{ $siswa->kelas ?? '-' }}
                                        <span class="mx-1">·</span>Status {{ $siswa->status }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Dipindahkan {{ $siswa->deleted_at ? $siswa->deleted_at->format('d/m/Y H:i') : '-' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('siswa.restore', $siswa->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Pulihkan data"
                                                class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg border border-green-300 bg-white text-green-800 text-[13px] font-semibold hover:bg-green-50 transition whitespace-nowrap">
                                            ↩️ Pulihkan
                                        </button>
                                    </form>

                                    @can('hapus-permanen-siswa')
                                        <button type="button" title="Hapus permanen"
                                                @click="hapusFormId = 'hapus-permanen-{{ $siswa->id }}'; hapusNama = '{{ addslashes($siswa->nama_lengkap) }}'; hapusNisn = '{{ $siswa->nisn }}'; hapusModalOpen = true"
                                                class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg border border-red-200 bg-white text-red-700 text-[13px] font-semibold hover:bg-red-50 transition whitespace-nowrap">
                                            🗑️ Hapus permanen
                                        </button>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach($siswas as $siswa)
                @can('hapus-permanen-siswa')
                    <form id="hapus-permanen-{{ $siswa->id }}" action="{{ route('siswa.forceDestroy', $siswa->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endcan
            @endforeach
        </div>

        {{-- MODAL KONFIRMASI HAPUS PERMANEN --}}
        <div x-show="hapusModalOpen" x-cloak style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[400px] overflow-hidden" @click.away="hapusModalOpen = false">
                <div class="h-1.5 w-full bg-gradient-to-r from-red-700 to-red-500"></div>
                <div class="p-6 text-center">
                    <div class="bg-red-50 text-red-500 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border-4 border-white shadow-sm">
                        <span class="text-2xl">🗑️</span>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-800 mb-1">Hapus permanen?</h3>
                    <p class="text-sm text-slate-600 mb-1">
                        <span class="font-bold text-red-600 break-words" x-text="hapusNama"></span>
                        <span class="text-slate-400 font-mono text-xs">(NISN: <span x-text="hapusNisn"></span>)</span>
                    </p>
                    <p class="text-xs text-slate-400 italic mb-5">Data dan fotonya akan hilang dari sistem dan tidak bisa dikembalikan.</p>
                    <div class="flex gap-3">
                        <button type="button" @click="hapusModalOpen = false"
                                class="w-full h-10 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="button" @click="document.getElementById(hapusFormId).submit()"
                                class="w-full h-10 rounded-xl bg-red-600 text-white text-sm font-bold shadow hover:opacity-90 transition">Ya, hapus permanen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
