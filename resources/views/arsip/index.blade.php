<x-app-layout>
    {{-- Gaya khusus halaman arsip: lebar kolom tetap + pergantian tabel/kartu.
         Ditulis di sini agar tidak bergantung pada build Tailwind (Vite). --}}
    <style>
        .tabel-arsip { width: 100%; min-width: 1000px; table-layout: fixed; border-collapse: collapse; text-align: left; }
        .tabel-arsip th, .tabel-arsip td { padding: 13px 14px; vertical-align: middle; word-break: normal; overflow-wrap: break-word; hyphens: none; }
        .tabel-arsip col.c-cek { width: 44px; }
        .tabel-arsip col.c-no { width: 56px; }        /* cukup untuk nomor 2 digit, tidak terbelah */
        .tabel-arsip col.c-jenis { width: 110px; }
        .tabel-arsip col.c-nomor { width: 292px; }    /* nomor surat panjang: pecah di tanda / , bukan di tengah angka */
        .tabel-arsip col.c-tgl { width: 104px; }
        .tabel-arsip col.c-aksi { width: 142px; }
        .tabel-arsip th.c-cek, .tabel-arsip td.c-no { text-align: center; white-space: nowrap; }
        .tabel-arsip td.c-tgl, .tabel-arsip th.c-aksi, .tabel-arsip td.c-aksi { white-space: nowrap; }
        .tabel-arsip td.c-nomor { line-height: 1.4; font-size: 12.5px; }
        .tabel-arsip th.c-nomor, .tabel-arsip td.c-nomor { padding-left: 12px; padding-right: 12px; }
        .tabel-arsip th { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #475569; }
        .tabel-arsip td { font-size: 13px; }

        /* baris penyaring: 3 kolom di HP, satu baris di layar lebar */
        .penyaring { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 16px; }
        .penyaring > div { min-width: 0; }
        @media (min-width: 640px) {
            .penyaring { display: flex; flex-wrap: wrap; align-items: flex-end; column-gap: 16px; row-gap: 12px; }
            .penyaring > div { width: 150px; }
        }

        .arsip-tabel { display: none; }
        .arsip-kartu { display: block; }
        @media (min-width: 768px) {
            .arsip-tabel { display: block; }
            .arsip-kartu { display: none; }
        }
    </style>

    @php
        $dataArsip = $arsips->map(function ($a) {
            $t = \Carbon\Carbon::parse($a->tanggal_surat);
            return [
                'nomor' => (string) $a->nomor_surat,
                'perihal' => (string) $a->perihal,
                'jenis' => (string) $a->jenis_surat,
                'bulan' => $t->format('m'),
                'tahun' => $t->format('Y'),
            ];
        })->values();
    @endphp

    <div class="py-6 sm:py-8" x-data="{
        search: '',
        filterJenis: '',
        filterBulan: '',
        filterTahun: '',
        items: @js($dataArsip),
        deleteSingleModalOpen: false,
        deleteSingleUrl: '',
        deleteBulkModalOpen: false,
        bulkMethod: 'POST',
        selectedItems: [],
        qrModalOpen: false,
        qrImageUrl: '',
        qrDownloadName: '',

        cocok(nomor, perihal, jenis, bulan, tahun) {
            const q = this.search.trim().toLowerCase();
            if (q && !((nomor + ' ' + perihal).toLowerCase().includes(q))) return false;
            if (this.filterJenis && jenis !== this.filterJenis) return false;
            if (this.filterBulan && bulan !== this.filterBulan) return false;
            if (this.filterTahun && tahun !== this.filterTahun) return false;
            return true;
        },
        get jumlahTampil() {
            return this.items.filter(i => this.cocok(i.nomor, i.perihal, i.jenis, i.bulan, i.tahun)).length;
        },
        terapkanAksi() {
            let action = document.getElementById('bulkActionType').value;
            if (action === '') { alert('Silakan pilih aksi terlebih dahulu.'); return; }
            let checked = document.querySelectorAll('.arsip-checkbox:checked');
            if (checked.length === 0) { alert('Silakan centang minimal satu data terlebih dahulu.'); return; }
            let form = document.getElementById('bulkActionForm');
            if (action === 'delete') {
                this.selectedItems = Array.from(checked).map(cb => cb.getAttribute('data-identitas'));
                form.action = '{{ route('arsip.destroyBulk') }}';
                this.bulkMethod = 'DELETE';
                this.deleteBulkModalOpen = true;
            } else if (action === 'export') {
                form.action = '{{ route('arsip.exportBulk') }}';
                this.bulkMethod = 'POST';
                form.submit();
            }
        }
    }">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Data Arsip Surat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola arsip surat masuk &amp; keluar</p>
                </div>
                <a href="{{ route('arsip.create') }}" class="inline-flex items-center justify-center gap-1.5 bg-blue-900 hover:bg-blue-800 text-white font-semibold text-xs py-2 px-4 rounded-lg shadow-sm transition duration-200 self-start sm:self-auto whitespace-nowrap">
                    <span class="text-sm leading-none">+</span> Tambah Surat
                </a>
            </div>

            <form action="#" method="POST" id="bulkActionForm">
                @csrf
                <input type="hidden" name="_method" :value="bulkMethod">

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-5">
                    <div class="p-4 sm:p-5 flex flex-col gap-4">
                        {{-- Baris 1: pencarian + aksi massal --}}
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:gap-5">
                            <div class="flex-1 min-w-0">
                                <label for="cariSurat" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cari Surat</label>
                                <input type="text" id="cariSurat" x-model="search" placeholder="Perihal / No. surat..." class="w-full h-10 rounded-lg border border-slate-300 bg-slate-50/40 px-3.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:bg-white outline-none transition">
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-end gap-2.5">
                                <div class="w-full sm:w-[210px]">
                                    <label for="bulkActionType" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Aksi Massal</label>
                                    <select name="bulk_action_type" id="bulkActionType" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                        <option value="">Pilih aksi...</option>
                                        <option value="delete">Hapus terpilih</option>
                                        <option value="export">Ekspor Excel</option>
                                    </select>
                                </div>
                                <button type="button" @click="terapkanAksi()" class="h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                    Terapkan
                                </button>
                            </div>
                        </div>

                        {{-- Baris 2: filter kategori / bulan / tahun --}}
                        <div class="penyaring">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
                                <select x-model="filterJenis" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    <option value="">Semua</option>
                                    <option value="Surat Masuk">Masuk</option>
                                    <option value="Surat Keluar">Keluar</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bulan</label>
                                <select x-model="filterBulan" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    <option value="">Semua</option>
                                    <option value="01">Jan</option><option value="02">Feb</option>
                                    <option value="03">Mar</option><option value="04">Apr</option>
                                    <option value="05">Mei</option><option value="06">Jun</option>
                                    <option value="07">Jul</option><option value="08">Agu</option>
                                    <option value="09">Sep</option><option value="10">Okt</option>
                                    <option value="11">Nov</option><option value="12">Des</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tahun</label>
                                <select x-model="filterTahun" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    <option value="">Semua</option>
                                    @php $tahunSekarang = date('Y'); @endphp
                                    @for($i = $tahunSekarang; $i >= $tahunSekarang - 5; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= TABEL (tablet & desktop) ================= --}}
                <div class="arsip-tabel bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="tabel-arsip">
                            <colgroup>
                                <col class="c-cek"><col class="c-no"><col class="c-jenis"><col class="c-nomor"><col class="c-perihal"><col class="c-tgl"><col class="c-aksi">
                            </colgroup>
                            <thead>
                                <tr class="bg-blue-50 border-b border-blue-100">
                                    <th class="c-cek text-center"><input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-900 focus:ring-blue-600 w-4 h-4 align-middle"></th>
                                    <th class="c-no text-center">No</th>
                                    <th class="c-jenis">Kategori</th>
                                    <th class="c-nomor">No. Surat</th>
                                    <th>Perihal</th>
                                    <th class="c-tgl">Tanggal</th>
                                    <th class="c-aksi text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($arsips as $index => $arsip)
                                @php
                                    $bulan = \Carbon\Carbon::parse($arsip->tanggal_surat)->format('m');
                                    $tahun = \Carbon\Carbon::parse($arsip->tanggal_surat)->format('Y');
                                @endphp
                                <tr data-baris class="hover:bg-slate-50 transition duration-150"
                                    x-show="cocok(@js((string) $arsip->nomor_surat), @js((string) $arsip->perihal), @js((string) $arsip->jenis_surat), '{{ $bulan }}', '{{ $tahun }}')">
                                    <td class="c-cek text-center">
                                        <input type="checkbox" name="arsip_ids[]" value="{{ $arsip->id }}" data-identitas="{{ $arsip->nomor_surat }} ({{ Str::limit($arsip->perihal, 30) }})" class="rounded border-slate-400 text-blue-600 focus:ring-blue-500 arsip-checkbox w-4 h-4 align-middle">
                                    </td>
                                    <td class="c-no text-center text-slate-500">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-full border whitespace-nowrap {{ $arsip->jenis_surat == 'Surat Masuk' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                            {{ $arsip->jenis_surat == 'Surat Masuk' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td class="c-nomor font-semibold text-slate-800">{{ $arsip->nomor_surat }}</td>
                                    <td class="text-slate-600">{{ $arsip->perihal }}</td>
                                    <td class="c-tgl text-slate-600">{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('d M Y') }}</td>
                                    <td class="c-aksi">
                                        <div class="flex justify-center gap-1">
                                            <button type="button" title="QR Code" @click="qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('arsip.show', $arsip->id)) }}'; qrDownloadName = '{{ preg_replace('/[^A-Za-z0-9\\-]/', '_', $arsip->nomor_surat) }}'; qrModalOpen = true;" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition text-sm">📱</button>

                                            <a href="{{ route('arsip.show', $arsip->id) }}" title="Cek Detail" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition text-sm">👁️</a>

                                            <a href="{{ route('arsip.edit', $arsip->id) }}" title="Edit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-sm">✏️</a>

                                            <button type="button" title="Hapus" @click="deleteSingleUrl = '{{ route('arsip.destroy', $arsip->id) }}'; deleteSingleModalOpen = true;" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-sm">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-slate-500 font-medium bg-slate-50">
                                        <span class="text-4xl block mb-3">📭</span>
                                        Belum ada data surat yang diarsipkan.
                                    </td>
                                </tr>
                                @endforelse
                                @if ($arsips->count())
                                <tr x-show="jumlahTampil === 0">
                                    <td colspan="7" class="p-10 text-center text-slate-500 font-medium bg-slate-50">
                                        <span class="text-3xl block mb-2">🔍</span>
                                        Tidak ada surat yang cocok dengan pencarian/filter.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ================= KARTU (HP) ================= --}}
                <div class="arsip-kartu">
                    @forelse ($arsips as $index => $arsip)
                    @php
                        $bulan = \Carbon\Carbon::parse($arsip->tanggal_surat)->format('m');
                        $tahun = \Carbon\Carbon::parse($arsip->tanggal_surat)->format('Y');
                    @endphp
                    <div data-baris class="bg-white border border-slate-200 rounded-xl shadow-sm mb-3 p-4"
                         x-show="cocok(@js((string) $arsip->nomor_surat), @js((string) $arsip->perihal), @js((string) $arsip->jenis_surat), '{{ $bulan }}', '{{ $tahun }}')">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="arsip_ids[]" value="{{ $arsip->id }}" data-identitas="{{ $arsip->nomor_surat }} ({{ Str::limit($arsip->perihal, 30) }})" class="arsip-checkbox mt-0.5 rounded border-slate-400 text-blue-600 focus:ring-blue-500 w-4 h-4 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-full border whitespace-nowrap {{ $arsip->jenis_surat == 'Surat Masuk' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                            {{ $arsip->jenis_surat == 'Surat Masuk' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 truncate">No. {{ $index + 1 }}</span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('d M Y') }}</span>
                                </div>
                                <p class="text-sm font-semibold text-slate-800 break-words leading-snug">{{ $arsip->nomor_surat }}</p>
                                <p class="text-sm text-slate-600 mt-1 break-words leading-snug">{{ $arsip->perihal }}</p>
                                <div class="flex items-center gap-1 mt-3 pt-3 border-t border-slate-100">
                                    <button type="button" @click="qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('arsip.show', $arsip->id)) }}'; qrDownloadName = '{{ preg_replace('/[^A-Za-z0-9\\-]/', '_', $arsip->nomor_surat) }}'; qrModalOpen = true;" class="h-9 px-2.5 inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition text-[11px] font-medium">📱 QR</button>
                                    <a href="{{ route('arsip.show', $arsip->id) }}" class="h-9 px-2.5 inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition text-[11px] font-medium">👁️ Detail</a>
                                    <a href="{{ route('arsip.edit', $arsip->id) }}" class="h-9 px-2.5 inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition text-[11px] font-medium">✏️ Edit</a>
                                    <button type="button" title="Hapus" @click="deleteSingleUrl = '{{ route('arsip.destroy', $arsip->id) }}'; deleteSingleModalOpen = true;" style="margin-left:auto" class="h-9 px-2.5 inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition text-[11px] font-medium">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white border border-slate-200 rounded-xl p-10 text-center text-slate-500 font-medium">
                        <span class="text-4xl block mb-3">📭</span>
                        Belum ada data surat yang diarsipkan.
                    </div>
                    @endforelse
                    @if ($arsips->count())
                    <div x-show="jumlahTampil === 0" class="bg-white border border-slate-200 rounded-xl p-10 text-center text-slate-500 font-medium">
                        <span class="text-3xl block mb-2">🔍</span>
                        Tidak ada surat yang cocok dengan pencarian/filter.
                    </div>
                    @endif
                </div>
            </form>
        </div>

        <div x-show="deleteSingleModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 bg-opacity-60 backdrop-blur-sm p-4 transition-opacity duration-300">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center relative" @click.away="deleteSingleModalOpen = false">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 font-bold border-4 border-white shadow-sm -mt-10">⚠️</div>
                <h3 class="text-lg font-extrabold text-slate-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin menghapus data arsip ini secara permanen?</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" @click="deleteSingleModalOpen = false" class="bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold py-2.5 px-4 rounded-xl transition w-1/2 text-sm">Batal</button>
                    <form :action="deleteSingleUrl" method="POST" class="w-1/2 m-0 p-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white hover:bg-red-700 font-bold py-2.5 px-4 rounded-xl transition w-full text-sm shadow-md">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="deleteBulkModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 bg-opacity-60 backdrop-blur-sm p-4 transition-opacity duration-300">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-center relative flex flex-col max-h-[90vh]" @click.away="deleteBulkModalOpen = false">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 font-bold border-4 border-white shadow-sm -mt-10 flex-shrink-0">⚠️</div>
                <h3 class="text-lg font-extrabold text-slate-800 mb-2">Hapus Data Massal</h3>
                <p class="text-sm text-slate-500 mb-4">Anda akan menghapus data berikut secara permanen:</p>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-6 text-left overflow-y-auto flex-grow">
                    <ul class="list-disc list-inside text-sm text-slate-700 space-y-1 px-2">
                        <template x-for="item in selectedItems" :key="item">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>

                <div class="flex gap-3 justify-center flex-shrink-0">
                    <button type="button" @click="deleteBulkModalOpen = false" class="bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold py-2.5 px-4 rounded-xl transition w-1/2 text-sm">Batal</button>
                    <button type="button" @click="document.getElementById('bulkActionForm').submit()" class="bg-red-600 text-white hover:bg-red-700 font-bold py-2.5 px-4 rounded-xl transition w-1/2 text-sm shadow-md">Ya, Hapus Semua</button>
                </div>
            </div>
        </div>

        <div x-show="qrModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 bg-opacity-60 backdrop-blur-sm p-4 transition-opacity duration-300"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95">

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[320px] sm:max-w-[360px] relative text-center overflow-hidden transform transition-all" @click.away="qrModalOpen = false">
                <div style="background: linear-gradient(135deg, #1e40af, #3b82f6);" class="h-3 w-full"></div>
                <button type="button" @click="qrModalOpen = false" class="absolute top-5 right-5 bg-slate-100 text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 flex items-center justify-center rounded-full font-black text-lg transition">&times;</button>

                <div class="p-6 sm:p-8 pt-5">
                    <h3 class="text-lg font-extrabold text-slate-800 mb-1">QR Code Arsip</h3>
                    <p class="text-sm font-bold text-slate-600 mb-5 break-words" x-text="qrDownloadName.replace(/_/g, ' ')"></p>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 inline-block mb-6 shadow-sm">
                        <img :src="qrImageUrl" class="w-44 h-44 sm:w-48 sm:h-48 mx-auto bg-white p-2 rounded-lg shadow-sm border border-slate-200 object-contain">
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="button" @click="downloadImage(qrImageUrl, 'QR_Arsip_' + qrDownloadName + '.png')" style="background-color: #10b981; color: #ffffff;" class="font-bold py-2.5 px-4 rounded-xl shadow hover:opacity-90 transition w-full flex items-center justify-center gap-2 text-sm">
                            <span>⬇️</span> Simpan QR Code
                        </button>
                        <button type="button" @click="qrModalOpen = false" style="background-color: #f8fafc; color: #475569;" class="font-bold py-2.5 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 transition w-full text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('selectAll').addEventListener('change', function(e) {
            document.querySelectorAll('.arsip-checkbox').forEach(function(cb) {
                var baris = cb.closest('[data-baris]');
                var tampak = baris && baris.style.display !== 'none' && baris.offsetParent !== null;
                cb.checked = (tampak && e.target.checked);
            });
        });

        function downloadImage(url, filename) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(err => {
                    window.open(url, '_blank');
                });
        }
    </script>
</x-app-layout>
