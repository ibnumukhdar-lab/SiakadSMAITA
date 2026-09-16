<x-app-layout>
    <!-- LOGIKA ALPINE JS SUPER CERDAS (KEBAL HURUF BESAR/KECIL/SPASI/TYPO EXCEL) -->
    <div class="py-8" x-data="{ 
        qrModalOpen: false, qrUrl: '', qrName: '', qrNisn: '',
        deleteModalOpen: false, deleteFormId: '', deleteStudentName: '',
        search: '', filterKelas: '', filterJk: '', filterAngkatan: '', limit: '30', visibleCount: 0,
        hanyaNisnBermasalah: false,
        updateVisibility() {
            let count = 0;
            let rows = document.querySelectorAll('.siswa-row');
            
            // FUNGSI PEMBERSIH EKSTREM: Membuang semua spasi, strip, dan karakter aneh
            const cleanStr = (str) => (str || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            
            let searchLower = this.search.toLowerCase().trim();
            let fKelas = cleanStr(this.filterKelas);
            let fJk = cleanStr(this.filterJk); // Output pasti jadi 'lakilaki' atau 'perempuan'
            let fAngkatan = cleanStr(this.filterAngkatan);
            
            rows.forEach(row => {
                let text = row.getAttribute('data-text') || ''; // Pencarian nama dibiarkan murni
                let kelas = cleanStr(row.getAttribute('data-kelas'));
                let jk = cleanStr(row.getAttribute('data-jk'));
                let angkatan = cleanStr(row.getAttribute('data-angkatan'));
                let nisnBaku = row.getAttribute('data-nisn-baku') === '1';

                let matchSearch = !this.search || text.includes(searchLower);
                let matchKelas = !fKelas || kelas === fKelas;
                
                // JURUS JITU GENDER: 
                // Jika pilih Laki-laki, ambil semua data yang huruf depannya 'l' (termasuk L, Laki-Laki, Laki laki, dll)
                // Jika pilih Perempuan, ambil semua data yang huruf depannya 'p' (termasuk P, Perempuan, pr, dll)
                let matchJk = !fJk || 
                              (fJk === 'lakilaki' && jk.startsWith('l')) || 
                              (fJk === 'perempuan' && jk.startsWith('p'));

                let matchAngkatan = !fAngkatan || angkatan === fAngkatan;
                let matchNisnBaku = !this.hanyaNisnBermasalah || !nisnBaku;

                if (matchSearch && matchKelas && matchJk && matchAngkatan && matchNisnBaku) {
                    count++;
                    // Tampilkan baris jika masih di bawah limit yang dipilih
                    if (this.limit === 'all' || count <= parseInt(this.limit)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = 'none'; // Sembunyikan jika tidak cocok filter
                }
            });
            this.visibleCount = count;
        }
    }" x-init="
        $watch('search', () => updateVisibility());
        $watch('filterKelas', () => updateVisibility());
        $watch('filterJk', () => updateVisibility());
        $watch('filterAngkatan', () => updateVisibility());
        $watch('hanyaNisnBermasalah', () => updateVisibility());
        $watch('limit', () => updateVisibility());
        updateVisibility();
    ">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-4 sm:p-6 shadow-sm sm:rounded-2xl border border-slate-200 relative">

                <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📚 Data Induk Siswa</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Master data siswa &amp; alumni SMA IT Arafah</p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2.5 w-full md:w-auto">
                        @if($jumlahTong > 0)
                        <a href="{{ route('siswa.trash') }}" style="background-color: #f8fafc; color: #b91c1c; border: 1px solid #fecaca;" class="inline-flex items-center justify-center font-semibold h-10 px-4 rounded-lg text-sm shadow-sm hover:bg-red-50 transition duration-200 whitespace-nowrap gap-1.5">
                            🗑️ Tong Sampah ({{ $jumlahTong }})
                        </a>
                        @endif
                        <a href="{{ route('siswa.import') }}" style="background-color: #334155; color: #ffffff;" class="inline-flex items-center justify-center font-semibold h-10 px-4 rounded-lg text-sm shadow-sm hover:opacity-90 transition duration-200 whitespace-nowrap gap-1.5">
                            📥 Impor Data
                        </a>
                        <a href="{{ route('siswa.create') }}" style="background-color: #1e3a8a; color: #ffffff;" class="inline-flex items-center justify-center font-semibold h-10 px-4 rounded-lg text-sm shadow-sm hover:bg-blue-800 hover:opacity-95 transition duration-200 whitespace-nowrap gap-1.5">
                            ➕ Tambah Siswa Baru
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('masalah_impor') && count(session('masalah_impor')) > 0)
                    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-900 p-4 mb-4 rounded shadow-sm text-sm">
                        <p class="font-bold mb-2">⚠️ Baris yang tidak diimpor ({{ session('masalah_impor_total') }} total, maks 15 ditampilkan):</p>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach(session('masalah_impor') as $baris)
                                <li>{{ $baris }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($jumlahNisnBermasalah > 0)
                    <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-4 mb-4 rounded shadow-sm text-sm flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                        <span>
                            ℹ️ <strong>{{ $jumlahNisnBermasalah }} siswa</strong> NISN-nya belum 10 digit (sisa data lama). Betulkan lewat tombol Edit setelah datanya ada.
                        </span>
                        <button type="button" @click="hanyaNisnBermasalah = !hanyaNisnBermasalah"
                                class="shrink-0 inline-flex items-center justify-center h-9 px-3.5 rounded-lg border border-blue-300 bg-white text-blue-800 text-[13px] font-semibold hover:bg-blue-100 transition whitespace-nowrap">
                            <span x-text="hanyaNisnBermasalah ? 'Tampilkan semua siswa' : 'Saring yang perlu dibetulkan'"></span>
                        </button>
                    </div>
                @endif

                <!-- PANEL FILTER MENGGUNAKAN CSS MURNI (Anti Tailwind) -->
                <div class="custom-filter-panel">
                    <input type="text" x-model="search" placeholder="🔍 Cari nama siswa..." class="custom-filter-input custom-search-bar">
                    
                    <select x-model="filterKelas" class="custom-filter-input">
                        <option value="">Semua Kelas</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                    
                    <select x-model="filterJk" class="custom-filter-input">
                        <option value="">Semua Gender</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                    
                    <input type="text" x-model="filterAngkatan" placeholder="Angkatan (Tahun)" class="custom-filter-input">
                    
                    <select x-model="limit" class="custom-filter-input bold-text">
                        <option value="30">Tampilkan: 30</option>
                        <option value="50">Tampilkan: 50</option>
                        <option value="100">Tampilkan: 100</option>
                        <option value="all">Semua Data</option>
                    </select>
                </div>

                <!-- TEKS RINGKASAN DINAMIS -->
                <div class="mb-5 text-[13px] font-medium text-slate-600 bg-white p-3 rounded-lg border border-slate-200 flex items-start gap-2">
                    <span class="text-slate-400 mt-0.5">ℹ️</span>
                    <span>
                        Berikut data 
                        <strong class="text-slate-700 text-base" x-text="limit === 'all' ? visibleCount : Math.min(visibleCount, limit)"></strong> 
                        siswa
                        <template x-if="filterKelas"><span> kelas <strong class="text-slate-700" x-text="filterKelas"></strong></span></template>
                        <template x-if="filterJk"><span> (<strong class="text-slate-700" x-text="filterJk"></strong>)</span></template>
                        <template x-if="filterAngkatan"><span> angkatan <strong class="text-slate-700" x-text="filterAngkatan"></strong></span></template>
                        <template x-if="search"><span> dengan pencarian "<strong class="text-slate-700" x-text="search"></strong>"</span></template>
                        
                        <span class="text-slate-500 ml-1">
                            (Total cocok: <span x-text="visibleCount"></span> data).
                        </span>
                    </span>
                </div>

                <!-- FORM AKSI MASSAL -->
                <form action="{{ route('siswa.bulk_action') }}" method="POST" id="bulkActionForm">
                    @csrf
                    <!-- Kontainer Aksi Massal Tanpa Tailwind -->
                    <div class="custom-bulk-action-panel">
                        <select name="bulk_action_type" class="custom-filter-input" style="flex: 1; max-width: 250px;" required>
                            <option value="">-- Pilih Aksi Massal --</option>
                            <optgroup label="Akademik & Status">
                                <option value="set_x">Naik Kelas X</option>
                                <option value="set_xi">Naik Kelas XI</option>
                                <option value="set_xii">Naik Kelas XII</option>
                                <option value="set_alumni">🎓 Jadikan Alumni</option>
                                <option value="set_aktif">♻️ Kembalikan jadi Aktif</option>
                            </optgroup>
                            <optgroup label="Edit Data Cepat">
                                <option value="set_laki">Ubah Gender: Laki-laki</option>
                                <option value="set_perempuan">Ubah Gender: Perempuan</option>
                            </optgroup>
                            <optgroup label="Tindakan Bahaya">
                                <option value="delete">🗑️ Pindahkan ke Tong Sampah</option>
                            </optgroup>
                        </select>
                        
                        <input type="text" name="bulk_tahun_ajaran" placeholder="Set Thn Ajaran (Opsional)" class="custom-filter-input" style="flex: 1; max-width: 200px;">
                        
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerapkan aksi massal pada siswa yang dipilih?')" style="background-color: #1e293b; color: #ffffff;" class="font-bold py-3 px-8 rounded-lg text-sm hover:opacity-80 transition shadow-md w-full sm:w-auto text-center whitespace-nowrap tracking-wide">
                            Terapkan Aksi
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3.5 w-10 text-center"><input type="checkbox" id="selectAll" class="rounded border-slate-300"></th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">No</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">NISN</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Lengkap</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Gender</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Kelas</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($siswas) == 0)
                                    <tr>
                                        <td colspan="8" class="p-10 text-center text-slate-500 font-medium">
                                            <span class="text-3xl block mb-2">📭</span>
                                            Belum ada data siswa di sistem. Silakan tambah data baru atau lakukan impor CSV.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($siswas as $index => $siswa)
                                    <!-- Data atribut dibiarkan apa adanya, pembersihan dilakukan di JS -->
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition duration-150 siswa-row"
                                        data-text="{{ strtolower($siswa->nama_lengkap ?? '') }}"
                                        data-kelas="{{ $siswa->kelas ?? '' }}"
                                        data-jk="{{ $siswa->jk ?? '' }}"
                                        data-nisn-baku="{{ preg_match('/^\d{10}$/', (string) $siswa->nisn) ? 1 : 0 }}"
                                        data-angkatan="{{ $siswa->thn_masuk ?? '' }}">
                                        
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="rounded border-slate-300">
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500 text-center">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600 font-mono">
                                            {{ $siswa->nisn }}
                                            @unless(preg_match('/^\d{10}$/', (string) $siswa->nisn))
                                                <span class="ml-1 text-amber-600 font-sans text-xs font-bold" title="NISN belum 10 digit — perlu dibetulkan">⚠</span>
                                            @endunless
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-slate-900">{{ $siswa->nama_lengkap }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-slate-500 text-center">
                                             {{ substr($siswa->jk, 0, 1) }} <!-- Menampilkan inisial L/P saja agar ringkas -->
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700 text-center font-bold">{{ $siswa->kelas ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($siswa->status == 'Aktif')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                            @elseif($siswa->status == 'Alumni')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-sky-50 text-sky-700 border border-sky-200">Alumni</span>
                                            @elseif($siswa->status == 'Pindah')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">Pindah</span>
                                            @else
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200">{{ $siswa->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-1.5">
                                                <button type="button" 
                                                    @click="qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('siswa.public', $siswa->nisn)) }}'; qrName = '{{ addslashes($siswa->nama_lengkap) }}'; qrNisn = '{{ $siswa->nisn }}'; qrModalOpen = true" 
                                                    title="Tampilkan QR Code" 
                                                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">📱
                                                </button>
                                                
                                                <a href="{{ route('siswa.show', $siswa->id) }}" title="Lihat Profil" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👁️</a>
                                                
                                                <a href="{{ route('siswa.edit', $siswa->id) }}" title="Edit Data" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</a>
                                                
                                                <button type="button" 
                                                    @click="deleteFormId = 'delete-form-{{ $siswa->id }}'; deleteStudentName = '{{ addslashes($siswa->nama_lengkap) }}'; deleteModalOpen = true" 
                                                    title="Pindahkan ke Tong Sampah" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    <!-- BARIS JIKA PENCARIAN TIDAK DITEMUKAN -->
                                    <tr x-show="visibleCount === 0" style="display: none;">
                                        <td colspan="8" class="p-10 text-center text-slate-500 font-medium">
                                            Tidak ada data siswa yang cocok dengan filter atau pencarian Anda.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </form>

                @foreach ($siswas as $siswa)
                    <form id="delete-form-{{ $siswa->id }}" action="{{ route('siswa.destroy', $siswa->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endforeach

            </div>
        </div>

        <!-- MODAL QR CODE -->
        <div x-show="qrModalOpen" 
             style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 bg-opacity-60 backdrop-blur-sm p-4 transition-opacity duration-300" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[340px] relative overflow-hidden transform transition-all mx-auto" 
                 @click.away="qrModalOpen = false"
                 x-show="qrModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                 
                <div style="background: linear-gradient(135deg, #1e40af, #3b82f6);" class="h-3 w-full"></div>

                <button type="button" @click="qrModalOpen = false" class="absolute top-4 right-4 bg-slate-100 text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 flex items-center justify-center rounded-full font-black text-lg transition z-10">
                    &times;
                </button>
                
                <div class="p-6 sm:p-8 flex flex-col items-center text-center">
                    
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 flex items-center justify-center shadow-sm mb-5">
                        <img :src="qrUrl" class="w-36 h-36 sm:w-40 sm:h-40 bg-white p-2 rounded-xl shadow-sm border border-slate-100 object-contain">
                    </div>
                    
                    <h3 class="text-xl font-extrabold text-slate-800 mb-2 leading-tight break-words" x-text="qrName"></h3>
                    <span class="inline-block bg-blue-50 text-blue-700 text-sm font-bold px-4 py-1.5 rounded-full border border-blue-200 mb-6 shadow-sm">
                        NISN: <span class="font-mono font-black" x-text="qrNisn"></span>
                    </span>
                    
                    <div class="flex flex-col gap-3 w-full">
                        <button type="button" @click="downloadImage(qrUrl, 'QR_Code_' + qrName + '.png')" style="background-color: #10b981; color: #ffffff;" class="font-bold py-3 px-5 rounded-xl shadow hover:opacity-90 transition w-full flex items-center justify-center gap-2 text-sm tracking-wide">
                            <span>⬇️</span> Simpan QR Code
                        </button>
                        <button type="button" @click="qrModalOpen = false" style="background-color: #f8fafc; color: #475569;" class="font-bold py-3 px-5 rounded-xl border border-slate-300 hover:bg-slate-100 transition w-full text-sm tracking-wide text-center">
                            Tutup Kartu
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- MODAL HAPUS -->
        <div x-show="deleteModalOpen" 
             style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900 bg-opacity-60 backdrop-blur-sm p-4 transition-opacity duration-300" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[380px] relative overflow-hidden transform transition-all mx-auto" 
                 @click.away="deleteModalOpen = false"
                 x-show="deleteModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                 
                <div style="background: linear-gradient(135deg, #b91c1c, #ef4444);" class="h-3 w-full"></div>
                
                <div class="p-6 sm:p-8 flex flex-col items-center text-center">
                    
                    <div class="bg-red-50 text-red-500 w-16 h-16 rounded-full flex items-center justify-center mb-4 border-4 border-white shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <h3 class="text-xl font-extrabold text-slate-800 mb-2 leading-tight">Pindahkan ke Tong Sampah</h3>
                    <p class="text-sm text-slate-600 mb-6">
                        Apakah Anda yakin ingin memindahkan data siswa <br>
                        <span class="font-bold text-red-600 break-words" x-text="deleteStudentName"></span>? <br>
                        <span class="text-xs italic text-slate-400 mt-1 block">Tidak permanen — data masih bisa dipulihkan dari menu Tong Sampah.</span>
                    </p>
                    
                    <div class="flex flex-row gap-3 w-full">
                        <button type="button" @click="deleteModalOpen = false" style="background-color: #f8fafc; color: #475569;" class="font-bold py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 transition w-full text-sm tracking-wide text-center">
                            Batal
                        </button>
                        <button type="button" @click="document.getElementById(deleteFormId).submit()" style="background-color: #dc2626; color: #ffffff;" class="font-bold py-3 px-4 rounded-xl shadow hover:opacity-90 transition w-full text-sm tracking-wide text-center">
                            Ya, Pindahkan
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>

    </div>

    <!-- INJEKSI CSS MURNI ANTI-GANGGUAN UNTUK FILTER DAN AKSI MASSAL -->
    <style>
        .custom-filter-panel, .custom-bulk-action-panel {
            display: flex;
            flex-direction: column;
            gap: 12px;
            background-color: #ffffff;
            padding: 16px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        }
        
        .custom-bulk-action-panel {
            background-color: #fbfcfd;
            border: 1px dashed #cbd5e1;
        }

        /* Jika layar cukup lebar (tablet ke atas), jadikan sebaris (Kiri ke Kanan) */
        @media (min-width: 768px) {
            .custom-filter-panel, .custom-bulk-action-panel {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
            }
        }

        .custom-filter-input {
            flex: 1 1 150px;
            min-width: 140px;
            height: 40px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            color: #334155;
            background-color: #ffffff;
            outline: none;
            transition: all 0.2s ease;
        }

        .custom-filter-input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        /* Kotak pencarian lebih dominan di baris filter */
        @media (min-width: 768px) {
            .custom-search-bar {
                flex: 2 1 260px;
            }
        }

        .custom-filter-input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .bold-text {
            font-weight: 700;
            background-color: #ffffff;
        }
    </style>

    <script>
        document.getElementById('selectAll').addEventListener('change', function(e) {
            // Hanya menyeleksi checkbox pada baris yang SEDANG DITAMPILKAN (tidak di-display:none)
            let visibleRows = document.querySelectorAll('.siswa-row:not([style*="display: none"])');
            visibleRows.forEach(row => {
                let checkbox = row.querySelector('input[name="siswa_ids[]"]');
                if(checkbox) checkbox.checked = e.target.checked;
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