<x-app-layout>
    <!-- MEMANGGIL CSS TOM SELECT -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
    <style>
        .ts-control { border-radius: 0.5rem !important; border: 1px solid #d1d5db !important; padding: 0.5rem 0.625rem !important; font-size: 0.875rem !important; box-shadow: none !important; background-color: white !important; min-height: 46px; }
        .ts-control.focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 1px #3b82f6 !important; }
        .ts-dropdown { border-radius: 0.5rem !important; font-size: 0.875rem !important; border: 1px solid #d1d5db !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .ts-dropdown .active { background-color: #eff6ff !important; color: #1e3a8a !important; }
        .ts-control .item { background: #e0e7ff !important; color: #1e40af !important; border-radius: 4px !important; border: 1px solid #bfdbfe !important; padding: 4px 8px !important; font-weight: bold !important; margin-bottom: 4px !important; }
    </style>

    @php
        $p = $members->count();
        $kap = (int) $kamar->kapasitas;
        $persenIsi = $kap > 0 ? min(100, (int) round($p / $kap * 100)) : 0;
        $sisa = max(0, $kap - $p);
    @endphp

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛏️ Kelola Penghuni: <span class="text-blue-900">{{ $kamar->nama_kamar }}</span></h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kamar {{ ucfirst($kamar->kategori) }} · Musyrif: {{ $kamar->musyrif->name ?? 'Belum Ditentukan' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('asrama.kamar.cetak', ['kamar_id' => $kamar->id]) }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">🖨️ Cetak Penghuni</a>
                    <a href="{{ route('asrama.kamar.index') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">⬅️ Daftar Kamar</a>
                </div>
            </div>

            <!-- Info keterisian -->
            <div class="bg-sky-50 border border-sky-200 rounded-xl px-4 py-3.5 mb-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-bold text-blue-900">{{ $p }} / {{ $kap }} penghuni</span>
                    <div class="flex-1 min-w-[120px] max-w-[260px]">
                        <div class="h-1.5 rounded-full bg-white overflow-hidden">
                            <div class="h-1.5 rounded-full {{ $p > $kap ? 'bg-rose-500' : 'bg-blue-600' }}" style="width: {{ $persenIsi }}%"></div>
                        </div>
                    </div>
                    @if($p > $kap)
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Over kapasitas</span>
                    @elseif($sisa === 0)
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Penuh</span>
                    @else
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-white text-sky-700 border border-sky-200">Sisa {{ $sisa }} tempat</span>
                    @endif
                </div>
                <p class="text-[13px] text-sky-900/70 mt-2 m-0">
                    Hanya siswa <strong>{{ $jkSah }}</strong> yang bisa dimasukkan ke kamar ini. Siswa yang dikeluarkan <strong>tidak dihapus</strong> — keanggotaannya ditutup sebagai riwayat mutasi.
                </p>
            </div>

            <!-- Notifikasi -->
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <p class="font-bold mb-1">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if(session('masalah_penghuni'))
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3.5 rounded-xl text-sm">
                    <p class="font-bold mb-1.5">⚠️ {{ count(session('masalah_penghuni')) }} siswa belum bisa dimasukkan:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach(session('masalah_penghuni') as $alasan)<li>{{ $alasan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if($jumlahSiswaTanpaGender > 0)
                <div class="mb-4 bg-slate-50 border border-slate-200 text-slate-600 px-4 py-3 rounded-xl text-[13px] font-medium">
                    ℹ️ {{ $jumlahSiswaTanpaGender }} siswa aktif belum punya jenis kelamin (Laki-laki/Perempuan) sehingga tidak muncul di daftar pilihan. Perbaikannya lewat menu Data Siswa oleh Tata Usaha.
                </div>
            @endif

            <!-- Form Tambah Penghuni Kamar -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                <h4 class="text-[15px] font-bold text-slate-800 mb-1">➕ Tambah Penghuni Baru</h4>
                <p class="text-[13px] text-slate-500 mb-4">Daftar di bawah sudah tersaring: hanya siswa Aktif berjenis kelamin {{ $jkSah }}. Siswa yang sudah punya kamar ditandai "(kamar ...)".</p>
                <form action="{{ route('asrama.kamar.addMember', $kamar->id) }}" method="POST">
                    @csrf
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[260px]">
                            <label for="cari-siswa" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pilih Siswa</label>
                            <select id="cari-siswa" name="student_id[]" multiple required>
                                @foreach($students as $siswa)
                                    @php $kamarLain = $kamarSiswa[$siswa->id] ?? null; @endphp
                                    <option value="{{ $siswa->id }}">
                                        {{ $siswa->nama_lengkap }} (Kelas: {{ $siswa->kelas }}){{ $kamarLain ? ' — ' . ($namaKamar[$kamarLain] ?? 'kamar lain') : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">➕ Tambahkan ke Kamar</button>
                    </div>

                    <details class="mt-4">
                        <summary class="cursor-pointer text-[13px] font-semibold text-slate-500 hover:text-slate-700">Opsi lanjutan (pindah kamar / lewat kapasitas / catatan)</summary>
                        <div class="mt-3 space-y-3 bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <label class="flex items-start gap-2.5 text-[13px] font-medium text-slate-700">
                                <input type="checkbox" name="pindahkan" value="1" class="mt-0.5 rounded border-slate-300">
                                <span>Pindahkan dari kamar lamanya — keanggotaan di kamar lama ditutup hari ini (riwayat tetap tersimpan).</span>
                            </label>
                            <label class="flex items-start gap-2.5 text-[13px] font-medium text-slate-700">
                                <input type="checkbox" name="paksa_kapasitas" value="1" class="mt-0.5 rounded border-slate-300">
                                <span>Izinkan melebihi kapasitas kamar ({{ $kap }} orang) — hanya bila memang darurat.</span>
                            </label>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Catatan (opsional)</label>
                                <input type="text" name="catatan" maxlength="255" placeholder="cth: pindahan dari kamar Ali" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                        </div>
                    </details>
                </form>
            </div>

            <!-- Tabel Daftar Penghuni Kamar -->
            <div class="bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm mb-6">
                <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between gap-3">
                    <h4 class="text-[15px] font-bold text-slate-800">Daftar Penghuni Saat Ini</h4>
                    <span class="text-[13px] font-semibold text-slate-400">{{ $p }} orang</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[720px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Siswa</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Tanggal Masuk</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Catatan</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $member->student->nama_lengkap ?? 'Siswa Dihapus' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $member->student->kelas ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 text-center font-medium">{{ \Carbon\Carbon::parse($member->tanggal_masuk)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-[13px] text-slate-500">{{ $member->catatan ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('asrama.kamar.removeMember', $member->id) }}" method="POST" class="flex items-center justify-center gap-2" onsubmit="return confirm('Keluarkan {{ addslashes($member->student->nama_lengkap ?? 'siswa ini') }} dari kamar? Riwayatnya tetap tersimpan.');">
                                        @csrf @method('PUT')
                                        <input type="text" name="catatan" maxlength="255" placeholder="alasan (opsional)" class="w-40 h-8 rounded-lg border border-slate-300 bg-white px-2 text-[12px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">🚪 Keluarkan</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-500 font-medium">
                                    <span class="text-4xl block mb-3">🛏️</span>
                                    Belum ada penghuni di kamar ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat keluar kamar ini -->
            @if($riwayat->isNotEmpty())
                <div class="bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between gap-3">
                        <h4 class="text-[15px] font-bold text-slate-800">📜 Riwayat Keluar / Pindah (terbaru)</h4>
                        <a href="{{ route('asrama.kamar.riwayat', ['kamar_id' => $kamar->id]) }}" class="text-[13px] font-semibold text-blue-900 hover:underline">Lihat semua →</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($riwayat as $r)
                            <div class="px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                                <div class="text-[13px] font-semibold text-slate-700">{{ $r->student->nama_lengkap ?? 'Siswa Dihapus' }} <span class="text-slate-400 font-medium">· {{ $r->student->kelas ?? '-' }}</span></div>
                                <div class="text-[12px] text-slate-500 font-medium">
                                    {{ \Carbon\Carbon::parse($r->tanggal_keluar)->translatedFormat('d M Y') }} · {{ $r->catatan ?: 'tanpa catatan' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MEMANGGIL SCRIPT TOM SELECT -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#cari-siswa", {
                plugins: ['remove_button'],
                create: false,
                sortField: { field: "text", direction: "asc" },
                maxOptions: null,
                placeholder: "Ketik dan pilih beberapa siswa sekaligus...",
            });
        });
    </script>
</x-app-layout>
