<x-app-layout>
    <div class="py-8" x-data="{
        showEditModal: false,
        editForm: { actionUrl: '', nama_kamar: '', kategori: 'putra', musyrif_id: '', kapasitas: '', status: 'aktif' }
    }">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛏️ Manajemen Kamar Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola kamar, musyrif, kapasitas, dan penghuni</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('asrama.kamar.cetak') }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">🖨️ Cetak Penghuni</a>
                    <a href="{{ route('asrama.kamar.riwayat') }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📜 Riwayat Mutasi</a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    Ada kesalahan input:
                    <ul class="list-disc list-inside ml-4 mt-1 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ringkasan okupansi --}}
            @php
                $kartu = [
                    ['label' => 'Kamar', 'nilai' => $ringkasan['kamar'], 'ikon' => '🛏️', 'warna' => 'text-slate-900'],
                    ['label' => 'Kapasitas', 'nilai' => $ringkasan['kapasitas'], 'ikon' => '📐', 'warna' => 'text-slate-900'],
                    ['label' => 'Penghuni', 'nilai' => $ringkasan['penghuni'], 'ikon' => '👥', 'warna' => 'text-blue-900'],
                    ['label' => 'Terisi', 'nilai' => $ringkasan['persen'] . '%', 'ikon' => '📊', 'warna' => $ringkasan['persen'] >= 90 ? 'text-rose-600' : 'text-emerald-600'],
                    ['label' => 'Over / Kosong', 'nilai' => $ringkasan['over'] . ' / ' . $ringkasan['kosong'], 'ikon' => '⚠️', 'warna' => $ringkasan['over'] > 0 ? 'text-rose-600' : 'text-slate-500'],
                ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
                @foreach($kartu as $k)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $k['ikon'] }} {{ $k['label'] }}</div>
                        <div class="text-xl font-extrabold {{ $k['warna'] }} mt-0.5">{{ $k['nilai'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Saring --}}
            <form method="GET" action="{{ route('asrama.kamar.index') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cari Kamar / Musyrif</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="nama kamar atau musyrif" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Divisi</label>
                        <select name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua divisi</option>
                            <option value="putra" @selected($kategori === 'putra')>Putra</option>
                            <option value="putri" @selected($kategori === 'putri')>Putri</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status Isi</label>
                        <select name="isi" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua</option>
                            <option value="tersedia" @selected($isi === 'tersedia')>Masih ada tempat</option>
                            <option value="penuh" @selected($isi === 'penuh')>Penuh</option>
                            <option value="over" @selected($isi === 'over')>Over kapasitas</option>
                            <option value="kosong" @selected($isi === 'kosong')>Kosong</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Saring</button>
                        @if($q !== '' || $kategori || $isi)
                            <a href="{{ route('asrama.kamar.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-3 rounded-lg bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Bersihkan</a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Form Tambah Kamar --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-6">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h4 class="text-[15px] font-bold text-slate-800">➕ Tambah Kamar Baru</h4>
                </div>
                <form action="{{ route('asrama.kamar.store') }}" method="POST" class="p-5 sm:p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label for="nama_kamar" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Kamar</label>
                            <input type="text" id="nama_kamar" name="nama_kamar" placeholder="cth: Al-Amin" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>
                        <div>
                            <label for="kategori" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
                            <select id="kategori" name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="putra">Asrama Putra</option>
                                <option value="putri">Asrama Putri</option>
                            </select>
                        </div>
                        <div>
                            <label for="musyrif_id" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Musyrif</label>
                            <select id="musyrif_id" name="musyrif_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="">-- Musyrif --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="kapasitas" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kapasitas</label>
                            <input type="number" id="kapasitas" name="kapasitas" value="8" min="1" max="40" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required title="Kapasitas Kamar">
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan</button>
                    </div>
                </form>
            </div>

            {{-- Daftar kamar: tabel di layar lebar, kartu di HP --}}
            <div class="hidden md:block bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Kamar</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Musyrif</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Keterisian</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kamars as $kamar)
                                @php
                                    $p = (int) $kamar->penghuni_count;
                                    $kap = (int) $kamar->kapasitas;
                                    $persenIsi = $kap > 0 ? min(100, (int) round($p / $kap * 100)) : 0;
                                    if ($kap > 0 && $p > $kap) { $warnaBar = 'bg-rose-500'; $chip = ['Over kapasitas', 'bg-rose-50 text-rose-700 border-rose-200']; }
                                    elseif ($p === 0) { $warnaBar = 'bg-amber-400'; $chip = ['Kosong', 'bg-amber-50 text-amber-700 border-amber-200']; }
                                    elseif ($kap > 0 && $p === $kap) { $warnaBar = 'bg-emerald-500'; $chip = ['Penuh', 'bg-emerald-50 text-emerald-700 border-emerald-200']; }
                                    else { $warnaBar = 'bg-blue-600'; $chip = ['Masih ada tempat', 'bg-sky-50 text-sky-700 border-sky-200']; }
                                    $adaRiwayat = $kamar->penghuni_count > 0;
                                @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 text-sm font-bold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($kamar->kategori == 'putra')
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $kamar->musyrif->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-28">
                                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-1.5 rounded-full {{ $warnaBar }}" style="width: {{ $persenIsi }}%"></div>
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold text-slate-800">{{ $p }}<span class="text-slate-400 font-semibold">/{{ $kap }}</span></span>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $chip[1] }}">{{ $chip[0] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-1.5">
                                            <a href="{{ route('asrama.kamar.show', $kamar->id) }}" title="Kelola Penghuni" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👥</a>
                                            <a href="{{ route('asrama.kamar.cetak', ['kamar_id' => $kamar->id]) }}" title="Cetak daftar penghuni" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">🖨️</a>
                                            <button type="button" @click="
                                                editForm.actionUrl = '{{ route('asrama.kamar.update', $kamar->id) }}';
                                                editForm.nama_kamar = '{{ addslashes($kamar->nama_kamar) }}';
                                                editForm.kategori = '{{ $kamar->kategori }}';
                                                editForm.musyrif_id = '{{ $kamar->musyrif_id }}';
                                                editForm.kapasitas = '{{ $kamar->kapasitas }}';
                                                editForm.status = '{{ $kamar->status }}';
                                                showEditModal = true;
                                            " title="Edit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</button>
                                            @if($adaRiwayat)
                                                <span title="Kamar masih berpenghuni — hapus dikunci demi keamanan riwayat" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-200 cursor-not-allowed">🗑️</span>
                                            @else
                                                <form action="{{ route('asrama.kamar.destroy', $kamar->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus kamar ini? Kamar yang punya riwayat penghuni/inspeksi akan ditolak sistem.');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Hapus" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-slate-500 font-medium">
                                        <span class="text-4xl block mb-3">🛏️</span>
                                        Tidak ada kamar yang cocok dengan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kartu untuk HP --}}
            <div class="md:hidden space-y-3">
                @forelse($kamars as $kamar)
                    @php
                        $p = (int) $kamar->penghuni_count;
                        $kap = (int) $kamar->kapasitas;
                        $persenIsi = $kap > 0 ? min(100, (int) round($p / $kap * 100)) : 0;
                        $warnaBar = ($kap > 0 && $p > $kap) ? 'bg-rose-500' : ($p === 0 ? 'bg-amber-400' : ($p === $kap ? 'bg-emerald-500' : 'bg-blue-600'));
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</div>
                                <div class="text-[12px] text-slate-500 mt-0.5">{{ $kamar->musyrif->name ?? 'Belum ada musyrif' }}</div>
                            </div>
                            @if($kamar->kategori == 'putra')
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-3">
                            <div class="flex-1">
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $warnaBar }}" style="width: {{ $persenIsi }}%"></div>
                                </div>
                            </div>
                            <span class="text-[13px] font-bold text-slate-800">{{ $p }}/{{ $kap }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <a href="{{ route('asrama.kamar.show', $kamar->id) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-blue-900 text-white text-[13px] font-semibold">👥 Penghuni</a>
                            <a href="{{ route('asrama.kamar.cetak', ['kamar_id' => $kamar->id]) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 text-[13px] font-semibold">🖨️ Cetak</a>
                            <button type="button" @click="
                                editForm.actionUrl = '{{ route('asrama.kamar.update', $kamar->id) }}';
                                editForm.nama_kamar = '{{ addslashes($kamar->nama_kamar) }}';
                                editForm.kategori = '{{ $kamar->kategori }}';
                                editForm.musyrif_id = '{{ $kamar->musyrif_id }}';
                                editForm.kapasitas = '{{ $kamar->kapasitas }}';
                                editForm.status = '{{ $kamar->status }}';
                                showEditModal = true;
                            " class="h-9 w-9 inline-flex items-center justify-center rounded-lg bg-white border border-slate-300 text-slate-500">✏️</button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center text-slate-500 text-sm">Tidak ada kamar yang cocok dengan filter ini.</div>
                @endforelse
            </div>

        </div>

        <!-- MODAL EDIT -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-opacity duration-300">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showEditModal = false">
                <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between bg-slate-50/60">
                    <h3 class="text-[15px] font-bold text-slate-800">✏️ Edit Data Kamar</h3>
                    <button type="button" @click="showEditModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition text-xl leading-none">&times;</button>
                </div>
                <form :action="editForm.actionUrl" method="POST">
                    @csrf @method('PUT')
                    <div class="p-5 sm:p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Kamar</label>
                            <input type="text" name="nama_kamar" x-model="editForm.nama_kamar" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori Asrama</label>
                            <select name="kategori" x-model="editForm.kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="putra">Asrama Putra</option>
                                <option value="putri">Asrama Putri</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Kategori tidak bisa diubah bila kamar masih berpenghuni.</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Guru Musyrif</label>
                            <select name="musyrif_id" x-model="editForm.musyrif_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="">-- Pilih Guru Musyrif --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kapasitas</label>
                                <input type="number" name="kapasitas" x-model="editForm.kapasitas" min="1" max="40" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                                <select name="status" x-model="editForm.status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 flex justify-end gap-2.5">
                        <button type="button" @click="showEditModal = false" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
