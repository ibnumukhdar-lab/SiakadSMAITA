<x-app-layout>
    <div class="py-5 sm:py-8" x-data="{
        filtBuka: false,
        pilihSemua: false,
        hapusModalOpen: false, hapusFormId: '', hapusNama: '',
        ringkasNama: '{{ '' }}'
    }">
        <div class="max-w-[1500px] mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ================= KEPALA HALAMAN ================= --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📚 Data Induk Siswa</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        {{ number_format($ringkasan['aktif'], 0, ',', '.') }} siswa aktif
                        @if($ringkasan['tong_sampah'] > 0)
                            <span class="text-slate-300 mx-1">·</span>
                            <a href="{{ route('siswa.trash') }}" class="text-rose-600 hover:underline">{{ $ringkasan['tong_sampah'] }} di tong sampah</a>
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($ringkasan['tong_sampah'] > 0)
                        <a href="{{ route('siswa.trash') }}" title="Tong Sampah"
                           class="inline-flex items-center justify-center h-10 px-3 rounded-lg border border-rose-200 bg-white text-rose-700 text-[12.5px] font-semibold hover:bg-rose-50 transition">🗑️ <span class="ml-1.5">Tong Sampah</span></a>
                    @endif
                    <a href="{{ route('siswa.import') }}" title="Impor CSV"
                       class="inline-flex items-center justify-center h-10 px-3 rounded-lg bg-slate-700 text-white text-[12.5px] font-semibold hover:opacity-90 transition">📥 <span class="ml-1.5">Impor</span></a>
                    <a href="{{ route('siswa.create') }}" title="Tambah siswa"
                       class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 text-white text-[12.5px] font-semibold hover:bg-blue-800 transition">➕ <span class="ml-1.5">Tambah Siswa</span></a>
                </div>
            </div>

            {{-- ================= PESAN ================= --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif
            @if(session('masalah_impor') && count(session('masalah_impor')) > 0)
                <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-900 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <p class="font-bold mb-1.5">⚠️ Baris yang tidak diimpor ({{ session('masalah_impor_total') }} total):</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach(session('masalah_impor') as $baris)
                            <li>{{ $baris }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($ringkasan['nisn_perlu'] > 0)
                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[13px] flex flex-col sm:flex-row sm:items-center gap-2 justify-between">
                    <span>ℹ️ <strong>{{ $ringkasan['nisn_perlu'] }} siswa</strong> NISN-nya belum 10 digit.</span>
                    <a href="{{ route('siswa.index', ['perlu' => 'nisn']) }}"
                       class="shrink-0 inline-flex items-center justify-center h-9 px-3 rounded-lg border border-blue-300 bg-white text-blue-800 text-[13px] font-semibold hover:bg-blue-100 transition whitespace-nowrap">
                        Saring yang perlu dibetulkan
                    </a>
                </div>
            @endif

            {{-- ================= FILTER (server-side) ================= --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-3">
                <button type="button" @click="filtBuka = !filtBuka"
                        class="w-full flex items-center justify-between px-3.5 py-3 md:hidden text-left">
                    <span class="text-[13px] font-bold text-slate-700">🔍 Pencarian &amp; Filter
                        @if($filter['q'] || $filter['kelas'] || $filter['status'] || $filter['jk'] || $filter['angkatan'] || $filter['perlu'])
                            <span class="ml-1 text-blue-800">•</span>
                        @endif
                    </span>
                    <span class="text-slate-400 text-xs" x-text="filtBuka ? '▲' : '▼'"></span>
                </button>

                <form method="GET" action="{{ route('siswa.index') }}" :class="filtBuka ? 'block' : 'hidden md:block'" class="px-3.5 pb-3.5 md:pt-3.5 border-t border-slate-100 md:border-t-0">
                    <div class="grid grid-cols-2 md:grid-cols-12 gap-2">
                        <div class="col-span-2 md:col-span-2">
                            <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama / NISN / NIS"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <select name="kelas" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">Semua kelas</option>
                                @foreach(['X', 'XI', 'XII', 'Lulus'] as $k)
                                    <option value="{{ $k }}" @selected($filter['kelas'] === $k)>Kelas {{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">Semua status</option>
                                @foreach(['Aktif', 'Alumni', 'Pindah'] as $st)
                                    <option value="{{ $st }}" @selected($filter['status'] === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <select name="jk" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">L/P</option>
                                <option value="Laki-laki" @selected($filter['jk'] === 'Laki-laki')>L</option>
                                <option value="Perempuan" @selected($filter['jk'] === 'Perempuan')>P</option>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <input type="text" name="angkatan" value="{{ $filter['angkatan'] }}" inputmode="numeric" placeholder="Angkatan"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-2 md:col-span-2">
                            <select name="perlu" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">Semua data</option>
                                <option value="nisn" @selected($filter['perlu'] === 'nisn')>NISN belum 10 digit</option>
                                <option value="foto" @selected($filter['perlu'] === 'foto')>Belum ada foto</option>
                                <option value="kontak" @selected($filter['perlu'] === 'kontak')>Belum ada no HP ortu</option>
                                <option value="alamat" @selected($filter['perlu'] === 'alamat')>Belum ada alamat</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <select name="urut" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                                <option value="baru" @selected($filter['urut'] === 'baru')>Terbaru</option>
                                <option value="nama" @selected($filter['urut'] === 'nama')>Nama A-Z</option>
                                <option value="kelas" @selected($filter['urut'] === 'kelas')>Kelas</option>
                                <option value="nisn" @selected($filter['urut'] === 'nisn')>NISN</option>
                                <option value="lama" @selected($filter['urut'] === 'lama')>Terlama</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <button type="submit" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-blue-900 text-white text-[13px] font-semibold hover:bg-blue-800 transition">Terapkan</button>
                        @if($filter['q'] || $filter['kelas'] || $filter['status'] || $filter['jk'] || $filter['angkatan'] || $filter['perlu'] || $filter['urut'] !== 'baru')
                            <a href="{{ route('siswa.index') }}" class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Bersihkan</a>
                        @endif

                        <span class="hidden sm:block w-px h-6 bg-slate-200 mx-1"></span>

                        <a href="{{ route('siswa.ekspor', request()->query()) }}" title="Unduh CSV sesuai filter"
                           class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">⬇️ <span class="ml-1.5 hidden sm:inline">Ekspor CSV</span></a>
                        <a href="{{ route('siswa.cetak', request()->query()) }}" target="_blank" title="Cetak daftar sesuai filter"
                           class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">🖨️ <span class="ml-1.5 hidden sm:inline">Cetak Daftar</span></a>
                    </div>
                </form>
            </div>

            {{-- ================= DAFTAR ================= --}}
            <form action="{{ route('siswa.bulk_action') }}" method="POST" id="formAksiMassal">
                @csrf

                {{-- Panel aksi massal --}}
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl mb-3" x-data="{ aksiBuka: false }">
                    <button type="button" @click="aksiBuka = !aksiBuka"
                            class="w-full flex items-center justify-between px-3.5 py-3 md:hidden text-left">
                        <span class="text-[13px] font-bold text-slate-700">⚙️ Aksi massal (ubah banyak siswa sekaligus)</span>
                        <span class="text-slate-400 text-xs" x-text="aksiBuka ? '▲' : '▼'"></span>
                    </button>

                    <div :class="aksiBuka ? 'block' : 'hidden md:block'" class="px-3.5 pb-3.5 md:py-3.5 border-t border-slate-200 md:border-t-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <select name="bulk_action_type" class="h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none w-full sm:w-auto" required>
                                <option value="">-- Pilih aksi massal --</option>
                                <optgroup label="Akademik &amp; status">
                                    <option value="set_x">Naik Kelas X</option>
                                    <option value="set_xi">Naik Kelas XI</option>
                                    <option value="set_xii">Naik Kelas XII</option>
                                    <option value="set_alumni">🎓 Jadikan Alumni</option>
                                    <option value="set_aktif">♻️ Kembalikan jadi Aktif</option>
                                </optgroup>
                                <optgroup label="Edit cepat">
                                    <option value="set_laki">Ubah gender: Laki-laki</option>
                                    <option value="set_perempuan">Ubah gender: Perempuan</option>
                                </optgroup>
                                <optgroup label="Tindakan">
                                    <option value="delete">🗑️ Pindahkan ke Tong Sampah</option>
                                </optgroup>
                            </select>
                            <input type="text" name="bulk_tahun_ajaran" placeholder="Set tahun ajaran (opsional)"
                                   class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none w-full sm:w-52">
                            <button type="submit" onclick="return confirm('Terapkan aksi massal pada siswa yang dipilih?')"
                                    class="h-10 px-4 rounded-lg bg-slate-800 text-white text-[13px] font-bold hover:opacity-90 transition w-full sm:w-auto whitespace-nowrap">Terapkan</button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">
                            Pilih siswa lewat kotak centang.
                            <button type="button" class="underline hover:text-slate-600" @click="pilihSemua = !pilihSemua; document.querySelectorAll('.centang-siswa').forEach(c => c.checked = pilihSemua)">Pilih/lepas semua di halaman ini</button>.
                        </p>
                    </div>
                </div>

                {{-- Ringkasan jumlah --}}
                <div class="mb-2.5 text-[12px] text-slate-500">
                    Menampilkan
                    <strong class="text-slate-700">{{ $siswas->count() }}</strong> dari
                    <strong class="text-slate-700">{{ number_format($siswas->total(), 0, ',', '.') }}</strong> siswa
                    @if($filter['q']) · cari "{{ $filter['q'] }}"@endif
                    @if($filter['kelas']) · kelas {{ $filter['kelas'] }}@endif
                    @if($filter['status']) · {{ $filter['status'] }}@endif
                    @if($filter['jk']) · {{ $filter['jk'] }}@endif
                    @if($filter['angkatan']) · angkatan {{ $filter['angkatan'] }}@endif
                    @if($filter['perlu']) · filter: {{ ['nisn' => 'NISN belum 10 digit', 'foto' => 'belum ada foto', 'kontak' => 'belum ada no HP ortu', 'alamat' => 'belum ada alamat'][$filter['perlu']] ?? $filter['perlu'] }}@endif
                </div>

                @if($siswas->isEmpty())
                    <div class="bg-white border border-slate-200 rounded-xl p-10 text-center text-slate-500">
                        <span class="text-3xl block mb-2">📭</span>
                        <p class="font-medium">Tidak ada siswa yang cocok dengan filter ini.</p>
                        <a href="{{ route('siswa.index') }}" class="inline-block mt-3 text-[13px] font-semibold text-blue-800 hover:underline">Bersihkan filter</a>
                    </div>
                @else

                    {{-- ==== TABEL (tablet ke atas) ==== --}}
                    <div class="hidden md:block bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200">
                                        <th class="px-3 py-3 w-10 text-center">
                                            <input type="checkbox" @click="pilihSemua = !pilihSemua; document.querySelectorAll('.centang-siswa').forEach(c => c.checked = pilihSemua)"
                                                   :checked="pilihSemua" class="rounded border-slate-300">
                                        </th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center w-12">No</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">NISN</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Siswa</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center w-14">JK</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center w-20">Kelas</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center w-24">Status</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center w-36">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswas as $i => $siswa)
                                        <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                            <td class="px-3 py-2.5 text-center">
                                                <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="centang-siswa rounded border-slate-300">
                                            </td>
                                            <td class="px-3 py-2.5 text-[13px] text-slate-400 text-center">{{ $siswas->firstItem() + $i }}</td>
                                            <td class="px-3 py-2.5 text-[13px] text-slate-600 font-mono">
                                                {{ $siswa->nisn }}
                                                @unless(preg_match('/^\d{10}$/', (string) $siswa->nisn))
                                                    <span class="ml-1 text-amber-600 font-sans text-xs font-bold" title="NISN belum 10 digit">⚠</span>
                                                @endunless
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div class="flex items-center gap-2.5">
                                                    <x-avatar-siswa :siswa="$siswa" />
                                                    <span class="text-[13px] font-bold text-slate-900">{{ $siswa->nama_lengkap }}</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2.5 text-[12px] font-bold text-slate-500 text-center">{{ $siswa->jk ? substr($siswa->jk, 0, 1) : '-' }}</td>
                                            <td class="px-3 py-2.5 text-[13px] text-slate-700 text-center font-bold">{{ $siswa->kelas ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-center">
                                                <x-lencana-status :status="$siswa->status" />
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div class="flex justify-center gap-1">
                                                    <a href="{{ route('siswa.kartu', $siswa->id) }}" target="_blank" title="Kartu pelajar"
                                                       class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-800 hover:bg-blue-50 transition">🪪</a>
                                                    <a href="{{ route('siswa.show', $siswa->id) }}" title="Lembar induk"
                                                       class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition">👁️</a>
                                                    <a href="{{ route('siswa.edit', $siswa->id) }}" title="Edit data"
                                                       class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</a>
                                                    <button type="button" title="Pindahkan ke Tong Sampah"
                                                            @click="hapusFormId = 'hapus-{{ $siswa->id }}'; hapusNama = '{{ addslashes($siswa->nama_lengkap) }}'; hapusModalOpen = true"
                                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ==== KARTU (ponsel) ==== --}}
                    <div class="md:hidden space-y-2">
                        @foreach($siswas as $i => $siswa)
                            <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="centang-siswa mt-1 rounded border-slate-300 shrink-0">
                                    <x-avatar-siswa :siswa="$siswa" ukuran="besar" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[14px] font-bold text-slate-900 leading-snug break-words">{{ $siswa->nama_lengkap }}</p>
                                        <p class="text-[12px] text-slate-500 font-mono mt-0.5">
                                            {{ $siswa->nisn }}
                                            @unless(preg_match('/^\d{10}$/', (string) $siswa->nisn))
                                                <span class="text-amber-600 font-sans font-bold" title="NISN belum 10 digit">⚠</span>
                                            @endunless
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Kelas {{ $siswa->kelas ?? '-' }}</span>
                                            <x-lencana-status :status="$siswa->status" />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-1.5 mt-2.5 pt-2.5 border-t border-slate-100">
                                    <a href="{{ route('siswa.kartu', $siswa->id) }}" target="_blank"
                                       class="h-9 px-3 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 text-slate-600 text-[12px] font-semibold hover:bg-slate-50 transition">🪪 Kartu</a>
                                    <a href="{{ route('siswa.show', $siswa->id) }}"
                                       class="h-9 px-3 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 text-slate-600 text-[12px] font-semibold hover:bg-slate-50 transition">👁️ Profil</a>
                                    <a href="{{ route('siswa.edit', $siswa->id) }}"
                                       class="h-9 px-3 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 text-amber-700 text-[12px] font-semibold hover:bg-amber-50 transition">✏️ Edit</a>
                                    <button type="button"
                                            @click="hapusFormId = 'hapus-{{ $siswa->id }}'; hapusNama = '{{ addslashes($siswa->nama_lengkap) }}'; hapusModalOpen = true"
                                            class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-rose-200 text-rose-600 text-[12px] hover:bg-rose-50 transition">🗑️</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ==== PAGINASI ==== --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mt-3">
                        <p class="text-[12px] text-slate-500 order-2 sm:order-1">
                            Halaman <strong class="text-slate-700">{{ $siswas->currentPage() }}</strong> dari {{ $siswas->lastPage() }}
                        </p>
                        <div class="flex items-center gap-1 order-1 sm:order-2 flex-wrap justify-center">
                            @if($siswas->onFirstPage())
                                <span class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-200 text-slate-300 text-[13px] font-semibold">‹ Sebelumnya</span>
                            @else
                                <a href="{{ $siswas->previousPageUrl() }}" class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">‹ Sebelumnya</a>
                            @endif

                            <div class="hidden sm:flex items-center gap-1">
                                @foreach($siswas->getUrlRange(max(1, $siswas->currentPage() - 2), min($siswas->lastPage(), $siswas->currentPage() + 2)) as $hal => $url)
                                    @if($hal === $siswas->currentPage())
                                        <span class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-blue-900 text-white text-[13px] font-bold">{{ $hal }}</span>
                                    @else
                                        <a href="{{ $url }}" class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">{{ $hal }}</a>
                                    @endif
                                @endforeach
                            </div>

                            @if($siswas->hasMorePages())
                                <a href="{{ $siswas->nextPageUrl() }}" class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">Berikutnya ›</a>
                            @else
                                <span class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-200 text-slate-300 text-[13px] font-semibold">Berikutnya ›</span>
                            @endif
                        </div>
                    </div>

                @endif
            </form>

            {{-- Kontrol jumlah per halaman — di LUAR form aksi massal (form tidak boleh bersarang) --}}
            @if($siswas->total() > 0)
                <form method="GET" action="{{ route('siswa.index') }}" class="flex items-center justify-end gap-2 mt-2.5">
                    @foreach(request()->except('per_halaman', 'page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <label class="text-[12px] text-slate-500 whitespace-nowrap">Tampilkan per halaman</label>
                    <select name="per_halaman" onchange="this.form.submit()"
                            class="h-8 w-[74px] rounded-lg border border-slate-300 bg-white pl-2 pr-6 text-[12px] font-semibold text-slate-700 outline-none focus:border-blue-500">
                        @foreach([25, 50, 100, 200] as $n)
                            <option value="{{ $n }}" @selected($filter['per_halaman'] === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            {{-- Form hapus (di luar form aksi massal supaya tidak bersarang) --}}
            @foreach($siswas as $siswa)
                <form id="hapus-{{ $siswa->id }}" action="{{ route('siswa.destroy', $siswa->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach

        </div>

        {{-- ================= MODAL HAPUS ================= --}}
        <div x-show="hapusModalOpen" x-cloak style="display:none"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[380px] overflow-hidden" @click.away="hapusModalOpen = false">
                <div class="h-1.5 w-full" style="background: linear-gradient(135deg,#b91c1c,#ef4444)"></div>
                <div class="p-6 text-center">
                    <div class="bg-red-50 text-red-500 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 border-4 border-white shadow-sm text-2xl">🗑️</div>
                    <h3 class="text-lg font-extrabold text-slate-800 mb-1">Pindahkan ke Tong Sampah</h3>
                    <p class="text-sm text-slate-600 mb-1">
                        <span class="font-bold text-red-600 break-words" x-text="hapusNama"></span>
                    </p>
                    <p class="text-[11px] italic text-slate-400 mb-5">Tidak permanen — data masih bisa dipulihkan dari menu Tong Sampah.</p>
                    <div class="flex gap-3">
                        <button type="button" @click="hapusModalOpen = false"
                                class="w-full h-10 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="button" @click="document.getElementById(hapusFormId).submit()"
                                class="w-full h-10 rounded-xl bg-red-600 text-white text-sm font-bold shadow hover:opacity-90 transition">Ya, pindahkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
