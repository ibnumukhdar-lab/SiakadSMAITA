<x-app-layout>
    <div class="py-5 sm:py-8" x-data="{ formBuka: false, hapusBuka: false, hapusFormId: '', hapusNama: '', hapusInfo: '' }">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🏫 Kelola Kelas</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Daftar kelas yang bisa dipilih saat mengelompokkan siswa</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('siswa.index') }}"
                       class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">🎓 Data Siswa</a>
                    <button type="button" @click="formBuka = !formBuka"
                            class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 text-white text-[12.5px] font-semibold hover:bg-blue-800 transition whitespace-nowrap">
                        ➕ Tambah Kelas
                    </button>
                </div>
            </div>

            {{-- ===== PESAN ===== --}}
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
                ℹ️ Kelas di halaman ini hanya <strong>daftar pilihan</strong> untuk Data Siswa. Pengelompokan yang sudah berjalan —
                kamar <strong>Asrama</strong> dan grup <strong>Student Root</strong> — tetap memakai data siswanya masing-masing, tidak ikut berubah.
            </div>

            {{-- ===== FORM TAMBAH ===== --}}
            <div x-show="formBuka" x-cloak style="display:none" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                <h4 class="text-[14px] font-bold text-slate-800 mb-3">Tambah kelas baru</h4>
                <form action="{{ route('kelas.store') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                    @csrf
                    <div class="col-span-2 sm:col-span-4">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama kelas *</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" maxlength="60" required placeholder="Contoh: X IPA 1"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tingkat</label>
                        <select name="tingkat" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                            <option value="">-</option>
                            @foreach(\App\Models\Kelas::TINGKAT as $t)
                                <option value="{{ $t }}" @selected(old('tingkat') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Urutan</label>
                        <input type="number" name="urutan" value="{{ old('urutan', 0) }}" min="0" max="999"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="150" placeholder="Opsional"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-12 flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-[13px] text-slate-600">
                            <input type="checkbox" name="aktif" value="1" checked class="rounded border-slate-300"> Aktif (muncul di pilihan Data Siswa)
                        </label>
                        <button type="submit" class="h-10 px-4 rounded-lg bg-blue-900 text-white text-[13px] font-bold hover:bg-blue-800 transition">Simpan Kelas</button>
                        <button type="button" @click="formBuka = false" class="h-10 px-3 rounded-lg border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Batal</button>
                    </div>
                </form>
            </div>

            {{-- ===== DAFTAR KELAS ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h4 class="text-[13px] font-bold uppercase tracking-wider text-slate-500">Daftar kelas</h4>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $daftar->count() }} kelas</span>
                </div>

                @if($daftar->isEmpty())
                    <div class="p-10 text-center text-slate-500">
                        <span class="text-3xl block mb-2">🏫</span>
                        Belum ada kelas. Tambahkan lewat tombol "Tambah Kelas".
                    </div>
                @else
                    {{-- Tabel (tablet ke atas) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3 w-12 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">No</th>
                                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Kelas</th>
                                    <th class="px-4 py-3 w-24 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Tingkat</th>
                                    <th class="px-4 py-3 w-24 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Siswa</th>
                                    <th class="px-4 py-3 w-28 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                    <th class="px-4 py-3 w-36 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($daftar as $i => $k)
                                    @php
                                        $aktif = (int) ($jumlah[$k->nama] ?? 0);
                                        $semua = (int) ($jumlahSemua[$k->nama] ?? 0);
                                        $tong = max(0, $semua - $aktif);
                                    @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3 text-[13px] text-slate-400 text-center">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <span class="text-[13.5px] font-bold text-slate-900">{{ $k->nama }}</span>
                                            @if($k->keterangan)
                                                <span class="block text-[11.5px] text-slate-400">{{ $k->keterangan }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $k->tingkat ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-[13px] font-bold {{ $aktif > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ $aktif }}</span>
                                            @if($tong > 0)
                                                <span class="block text-[10.5px] text-slate-400">+{{ $tong }} di tong sampah</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($k->aktif)
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                            @else
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-1">
                                                <a href="{{ route('kelas.edit', $k->id) }}" title="Edit kelas"
                                                   class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</a>
                                                <button type="button" title="{{ $semua > 0 ? 'Masih dipakai '.$semua.' data siswa' : 'Hapus kelas' }}"
                                                        @click="hapusFormId = 'hapus-kelas-{{ $k->id }}'; hapusNama = '{{ addslashes($k->nama) }}'; hapusInfo = '{{ $semua > 0 ? 'Masih dipakai '.$semua.' data siswa, jadi belum bisa dihapus.' : 'Belum dipakai data siswa.' }}'; hapusBuka = true"
                                                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Kartu (ponsel) --}}
                    <ul class="md:hidden divide-y divide-slate-100">
                        @foreach($daftar as $k)
                            @php
                                $aktif = (int) ($jumlah[$k->nama] ?? 0);
                                $semua = (int) ($jumlahSemua[$k->nama] ?? 0);
                                $tong = max(0, $semua - $aktif);
                            @endphp
                            <li class="p-3.5 hover:bg-slate-50/70 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[14px] font-bold text-slate-900 break-words">{{ $k->nama }}</p>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Tingkat {{ $k->tingkat ?? '-' }}</span>
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $aktif > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">{{ $aktif }} siswa</span>
                                            @if($tong > 0)
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-50 text-slate-500 border border-slate-200">+{{ $tong }} di tong sampah</span>
                                            @endif
                                            @if($k->aktif)
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                            @else
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                            @endif
                                        </div>
                                        @if($k->keterangan)
                                            <p class="text-[11.5px] text-slate-400 mt-1">{{ $k->keterangan }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ route('kelas.edit', $k->id) }}"
                                           class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-200 text-amber-700 text-[12px] font-semibold hover:bg-amber-50 transition">✏️ Edit</a>
                                        <button type="button"
                                                @click="hapusFormId = 'hapus-kelas-{{ $k->id }}'; hapusNama = '{{ addslashes($k->nama) }}'; hapusInfo = '{{ $semua > 0 ? 'Masih dipakai '.$semua.' data siswa, jadi belum bisa dihapus.' : 'Belum dipakai data siswa.' }}'; hapusBuka = true"
                                                class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach($daftar as $k)
                <form id="hapus-kelas-{{ $k->id }}" action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach

            {{-- ===== KELAS LAMA YANG BELUM TERDAFTAR ===== --}}
            @if(!empty($belumTerdaftar))
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mt-3">
                    <h4 class="text-[13.5px] font-bold text-amber-900 mb-1">⚠️ Ada {{ count($belumTerdaftar) }} nama kelas di data siswa yang belum terdaftar</h4>
                    <p class="text-[12.5px] text-amber-800 leading-relaxed mb-3">
                        Data siswa memakai nama kelas di bawah ini, tapi belum ada di daftar kelas. Daftarkan dulu supaya
                        nama kelasnya rapi dan bisa dipakai memindahkan siswa lewat Data Siswa.
                    </p>
                    <ul class="space-y-2">
                        @foreach($belumTerdaftar as $nama => $jumlah)
                            <li class="flex flex-col sm:flex-row sm:items-center gap-2 justify-between bg-white/70 border border-amber-200 rounded-xl px-3 py-2">
                                <span class="text-[13px] font-bold text-amber-900">
                                    {{ $nama }}
                                    <span class="font-normal text-amber-700">— {{ $jumlah }} siswa</span>
                                </span>
                                <form action="{{ route('kelas.store') }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="nama" value="{{ $nama }}">
                                    <input type="hidden" name="tingkat" value="{{ in_array($nama, \App\Models\Kelas::TINGKAT, true) ? $nama : '' }}">
                                    <input type="hidden" name="aktif" value="1">
                                    <button type="submit" class="h-9 px-3.5 rounded-lg bg-amber-600 text-white text-[12.5px] font-bold hover:bg-amber-700 transition whitespace-nowrap">
                                        Daftarkan sebagai kelas
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-[11.5px] text-amber-700 mt-2">Catatan: pendaftaran ini tidak mengubah data siswa — hanya menambah nama kelas ke daftar.</p>
                </div>
            @endif
        </div>

        {{-- ===== MODAL HAPUS ===== --}}
        <div x-show="hapusBuka" x-cloak style="display:none"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[380px] overflow-hidden" @click.away="hapusBuka = false">
                <div class="h-1.5 w-full" style="background: linear-gradient(135deg,#b91c1c,#ef4444)"></div>
                <div class="p-6 text-center">
                    <div class="bg-red-50 text-red-500 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border-4 border-white shadow-sm text-2xl">🗑️</div>
                    <h3 class="text-lg font-extrabold text-slate-800 mb-1">Hapus kelas?</h3>
                    <p class="text-sm text-slate-600 mb-1"><span class="font-bold text-red-600 break-words" x-text="hapusNama"></span></p>
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
