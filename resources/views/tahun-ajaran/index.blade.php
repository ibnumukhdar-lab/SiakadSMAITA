<x-app-layout>
    <div class="py-5 sm:py-8" x-data="{ formBuka: false, hapusBuka: false, hapusFormId: '', hapusNama: '', hapusInfo: '' }">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📅 Tahun Ajaran</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        @if($daftar->firstWhere('aktif', true))
                            Aktif sekarang: <span class="font-bold text-blue-900">{{ $daftar->firstWhere('aktif', true)->nama }}</span>
                        @else
                            Belum ada tahun ajaran aktif
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('kenaikan.index') }}"
                       class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">⬆️ Kenaikan Kelas</a>
                    <button type="button" @click="formBuka = !formBuka"
                            class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 text-white text-[12.5px] font-semibold hover:bg-blue-800 transition whitespace-nowrap">➕ Tambah Tahun Ajaran</button>
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

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Tahun ajaran aktif dipakai sebagai isian bawaan saat menyimpan/mengimpor siswa dan saat proses
                <strong>Kenaikan Kelas</strong>. Mengubah tahun aktif tidak mengubah data siswa yang sudah ada.
            </div>

            {{-- Form tambah --}}
            <div x-show="formBuka" x-cloak style="display:none" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                <h4 class="text-[14px] font-bold text-slate-800 mb-3">Tambah tahun ajaran</h4>
                <form action="{{ route('tahun-ajaran.store') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                    @csrf
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="2026/2027" maxlength="20"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mulai</label>
                        <input type="date" name="awal" value="{{ old('awal') }}"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Berakhir</label>
                        <input type="date" name="akhir" value="{{ old('akhir') }}"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="150" placeholder="Opsional"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-12 flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-[13px] text-slate-600">
                            <input type="checkbox" name="aktif" value="1" @checked(old('aktif')) class="rounded border-slate-300"> Jadikan tahun ajaran aktif
                        </label>
                        <button type="submit" class="h-10 px-4 rounded-lg bg-blue-900 text-white text-[13px] font-bold hover:bg-blue-800 transition">Simpan</button>
                        <button type="button" @click="formBuka = false" class="h-10 px-3 rounded-lg border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Batal</button>
                    </div>
                </form>
            </div>

            {{-- Daftar --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider text-slate-500">Daftar tahun ajaran</h4>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $daftar->count() }}</span>
                </div>

                @if($daftar->isEmpty())
                    <div class="p-10 text-center text-slate-500">
                        <span class="text-3xl block mb-2">📅</span>
                        Belum ada tahun ajaran.
                    </div>
                @else
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3 w-12 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">No</th>
                                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun Ajaran</th>
                                    <th class="px-4 py-3 w-52 text-[11px] font-bold uppercase tracking-wider text-slate-400">Periode</th>
                                    <th class="px-4 py-3 w-24 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Siswa</th>
                                    <th class="px-4 py-3 w-28 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                    <th class="px-4 py-3 w-44 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftar as $i => $t)
                                    @php $jumlah = (int) ($jumlahSiswa[$t->nama] ?? 0); @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3 text-[13px] text-slate-400 text-center">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <span class="text-[13.5px] font-bold text-slate-900">{{ $t->nama }}</span>
                                            @if($t->keterangan)
                                                <span class="block text-[11.5px] text-slate-400">{{ $t->keterangan }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[12.5px] text-slate-600">
                                            @if($t->awal || $t->akhir)
                                                {{ $t->awal?->format('d/m/Y') ?? '—' }} s.d. {{ $t->akhir?->format('d/m/Y') ?? '—' }}
                                            @else
                                                <span class="text-slate-300">belum diisi</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-[13px] font-bold {{ $jumlah > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ $jumlah }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($t->aktif)
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                            @else
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-1">
                                                @unless($t->aktif)
                                                    <form action="{{ route('tahun-ajaran.aktifkan', $t->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" title="Jadikan tahun ajaran aktif"
                                                                class="h-8 px-2.5 inline-flex items-center rounded-lg text-blue-800 border border-blue-200 hover:bg-blue-50 transition text-[12px] font-semibold">Aktifkan</button>
                                                    </form>
                                                @endunless
                                                <a href="{{ route('tahun-ajaran.edit', $t->id) }}" title="Edit"
                                                   class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</a>
                                                <button type="button" title="Hapus"
                                                        @click="hapusFormId = 'hapus-ta-{{ $t->id }}'; hapusNama = '{{ addslashes($t->nama) }}'; hapusInfo = '{{ $t->aktif ? 'Sedang aktif — aktifkan tahun lain dulu.' : ($jumlah > 0 ? 'Masih dipakai '.$jumlah.' data siswa.' : 'Belum dipakai data siswa.') }}'; hapusBuka = true"
                                                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <ul class="md:hidden divide-y divide-slate-100">
                        @foreach($daftar as $t)
                            @php $jumlah = (int) ($jumlahSiswa[$t->nama] ?? 0); @endphp
                            <li class="p-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[14px] font-bold text-slate-900">{{ $t->nama }}</p>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                            @if($t->aktif)
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                            @else
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                            @endif
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $jumlah > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">{{ $jumlah }} siswa</span>
                                            @if($t->awal || $t->akhir)
                                                <span class="text-[11px] text-slate-500">{{ $t->awal?->format('d/m/Y') ?? '—' }} – {{ $t->akhir?->format('d/m/Y') ?? '—' }}</span>
                                            @endif
                                        </div>
                                        @if($t->keterangan)
                                            <p class="text-[11.5px] text-slate-400 mt-1">{{ $t->keterangan }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('tahun-ajaran.edit', $t->id) }}"
                                               class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-200 text-amber-700 text-[12px] font-semibold hover:bg-amber-50 transition">✏️ Edit</a>
                                            <button type="button"
                                                    @click="hapusFormId = 'hapus-ta-{{ $t->id }}'; hapusNama = '{{ addslashes($t->nama) }}'; hapusInfo = '{{ $t->aktif ? 'Sedang aktif — aktifkan tahun lain dulu.' : ($jumlah > 0 ? 'Masih dipakai '.$jumlah.' data siswa.' : 'Belum dipakai data siswa.') }}'; hapusBuka = true"
                                                    class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                        </div>
                                        @unless($t->aktif)
                                            <form action="{{ route('tahun-ajaran.aktifkan', $t->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="h-8 px-3 rounded-lg border border-blue-200 text-blue-800 text-[12px] font-semibold hover:bg-blue-50 transition whitespace-nowrap">Jadikan aktif</button>
                                            </form>
                                        @endunless
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach($daftar as $t)
                <form id="hapus-ta-{{ $t->id }}" action="{{ route('tahun-ajaran.destroy', $t->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach
        </div>

        <div x-show="hapusBuka" x-cloak style="display:none"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[380px] overflow-hidden" @click.away="hapusBuka = false">
                <div class="h-1.5 w-full" style="background: linear-gradient(135deg,#b91c1c,#ef4444)"></div>
                <div class="p-6 text-center">
                    <div class="bg-red-50 text-red-500 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border-4 border-white shadow-sm text-2xl">🗑️</div>
                    <h3 class="text-lg font-extrabold text-slate-800 mb-1">Hapus tahun ajaran?</h3>
                    <p class="text-sm text-slate-600 mb-1"><span class="font-bold text-red-600" x-text="hapusNama"></span></p>
                    <p class="text-[11.5px] italic text-slate-400 mb-5" x-text="hapusInfo"></p>
                    <div class="flex gap-3">
                        <button type="button" @click="hapusBuka = false"
                                class="w-full h-10 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="button" @click="document.getElementById(hapusFormId).submit()"
                                class="w-full h-10 rounded-xl bg-red-600 text-white text-sm font-bold shadow hover:opacity-90 transition">Ya, hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
