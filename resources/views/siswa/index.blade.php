<x-app-layout>
    <!-- LOGIKA ALPINE JS SUPER CERDAS (KEBAL HURUF BESAR/KECIL/SPASI/TYPO EXCEL) -->
    <div class="py-8" x-data="{ 
        qrModalOpen: false, qrUrl: '', qrName: '', qrNisn: '',
        deleteModalOpen: false, deleteFormId: '', deleteStudentName: '',
        search: '', filterKelas: '', filterJk: '', filterAngkatan: '', limit: '30', visibleCount: 0,
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

                let matchSearch = !this.search || text.includes(searchLower);
                let matchKelas = !fKelas || kelas === fKelas;
                
                // JURUS JITU GENDER: 
                // Jika pilih Laki-laki, ambil semua data yang huruf depannya 'l' (termasuk L, Laki-Laki, Laki laki, dll)
                // Jika pilih Perempuan, ambil semua data yang huruf depannya 'p' (termasuk P, Perempuan, pr, dll)
                let matchJk = !fJk || 
                              (fJk === 'lakilaki' && jk.startsWith('l')) || 
                              (fJk === 'perempuan' && jk.startsWith('p'));

                let matchAngkatan = !fAngkatan || angkatan === fAngkatan;

                if (matchSearch && matchKelas && matchJk && matchAngkatan) {
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
        $watch('limit', () => updateVisibility());
        updateVisibility();
    ">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-4 sm:p-6 shadow-sm sm:rounded-2xl border border-slate-200 relative">
 
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight w-full text-center md:text-left">📚 Data Induk Siswa</h3>
                    
                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <a href="{{ route('siswa.import') }}" style="background-color: #334155; color: #ffffff;" class="font-bold py-3 px-5 rounded-lg shadow hover:opacity-80 transition duration-200 text-center w-full sm:w-auto whitespace-nowrap">
                            📥 Impor Data
                        </a>
                        <a href="{{ route('siswa.create') }}" style="background-color: #1e40af; color: #ffffff;" class="font-bold py-3 px-5 rounded-lg shadow-lg hover:opacity-80 transition duration-200 text-center w-full sm:w-auto whitespace-nowrap">
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
                <div class="mb-5 text-sm font-medium text-slate-700 bg-sky-50/50 p-3.5 rounded-lg border border-sky-100 flex items-start gap-2 shadow-sm">
                    <span class="text-sky-500 mt-0.5">ℹ️</span>
                    <span>
                        Berikut data 
                        <strong class="text-sky-700 text-base" x-text="limit === 'all' ? visibleCount : Math.min(visibleCount, limit)"></strong> 
                        siswa
                        <template x-if="filterKelas"><span> kelas <strong class="text-sky-700" x-text="filterKelas"></strong></span></template>
                        <template x-if="filterJk"><span> (<strong class="text-sky-700" x-text="filterJk"></strong>)</span></template>
                        <template x-if="filterAngkatan"><span> angkatan <strong class="text-sky-700" x-text="filterAngkatan"></strong></span></template>
                        <template x-if="search"><span> dengan pencarian "<strong class="text-sky-700" x-text="search"></strong>"</span></template>
                        
                        <span class="text-slate-500 ml-1">
                            (Total cocok: <span x-text="visibleCount"></span> data).
                        </span>
                    </span>
                </div>

                <!-- FORM AKSI MASSAL -->
                <form action="/public/siswa/bulk-action" method="POST" id="bulkActionForm">
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
                            </optgroup>
                            <optgroup label="Edit Data Cepat">
                                <option value="set_laki">Ubah Gender: Laki-laki</option>
                                <option value="set_perempuan">Ubah Gender: Perempuan</option>
                            </optgroup>
                            <optgroup label="Tindakan Bahaya">
                                <option value="delete">🗑️ Hapus Data</option>
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
                                <tr class="bg-blue-50/70 border-b border-blue-100">
                                    <th class="p-4 w-10 text-center"><input type="checkbox" id="selectAll" class="rounded border-gray-400"></th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700 text-center">No</th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700">NISN</th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700">Nama Lengkap</th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700 text-center">Gender</th> <!-- Tambahan indikator kolom Gender -->
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700 text-center">Kelas</th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700 text-center">Status</th>
                                    <th class="p-4 text-sm font-semibold tracking-wide text-slate-700 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($siswas) == 0)
                                    <tr>
                                        <td colspan="8" class="p-8 text-center text-slate-500 font-medium bg-slate-50">
                                            Belum ada data siswa di sistem. Silakan tambah data baru atau lakukan impor CSV.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($siswas as $index => $siswa)
                                    <!-- Data atribut dibiarkan apa adanya, pembersihan dilakukan di JS -->
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition duration-150 siswa-row"
                                        data-text="{{ strtolower($siswa->nama_lengkap ?? '') }}"
                                        data-kelas="{{ $siswa->kelas ?? '' }}"
                                        data-jk="{{ $siswa->jk ?? '' }}"
                                        data-angkatan="{{ $siswa->thn_masuk ?? '' }}">
                                        
                                        <td class="p-4 text-center">
                                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="rounded border-gray-400">
                                        </td>
                                        <td class="p-4 text-sm text-slate-700 text-center">{{ $index + 1 }}</td>
                                        <td class="p-4 text-sm text-slate-700 font-mono">{{ $siswa->nisn }}</td>
                                        <td class="p-4 text-sm font-bold text-sky-600">{{ $siswa->nama_lengkap }}</td>
                                        <td class="p-4 text-xs font-bold text-slate-500 text-center">
                                             {{ substr($siswa->jk, 0, 1) }} <!-- Menampilkan inisial L/P saja agar ringkas -->
                                        </td>
                                        <td class="p-4 text-sm text-slate-700 text-center font-bold">{{ $siswa->kelas ?? '-' }}</td>
                                        <td class="p-4 text-sm text-center">
                                            @if($siswa->status == 'Aktif')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 shadow-sm border border-green-200">Aktif</span>
                                            @elseif($siswa->status == 'Alumni')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-800 shadow-sm border border-sky-200">Alumni</span>
                                            @elseif($siswa->status == 'Pindah')
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 shadow-sm border border-amber-200">Pindah</span>
                                            @else
                                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 shadow-sm border border-red-200">{{ $siswa->status }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <div class="flex justify-center gap-2">
                                                <button type="button" 
                                                    @click="qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('siswa.public', $siswa->nisn)) }}'; qrName = '{{ addslashes($siswa->nama_lengkap) }}'; qrNisn = '{{ $siswa->nisn }}'; qrModalOpen = true" 
                                                    title="Tampilkan QR Code" 
                                                    style="background-color: #1f2937; color: #ffffff;" 
                                                    class="px-3 py-2 rounded hover:opacity-80 text-xs font-bold shadow-sm">
                                                    📱 QR
                                                </button>
                                                
                                                <a href="{{ route('siswa.show', $siswa->id) }}" title="Lihat Profil" style="background-color: #2563eb; color: #ffffff;" class="px-3 py-2 rounded hover:opacity-80 text-xs font-bold shadow-sm">👁️ Lihat</a>
                                                
                                                <a href="{{ route('siswa.edit', $siswa->id) }}" title="Edit Data" style="background-color: #f59e0b; color: #ffffff;" class="px-3 py-2 rounded hover:opacity-80 text-xs font-bold shadow-sm">✏️ Edit</a>
                                                
                                                <button type="button" 
                                                    @click="deleteFormId = 'delete-form-{{ $siswa->id }}'; deleteStudentName = '{{ addslashes($siswa->nama_lengkap) }}'; deleteModalOpen = true" 
                                                    title="Hapus" style="background-color: #dc2626; color: #ffffff;" class="px-3 py-2 rounded hover:opacity-80 text-xs font-bold shadow-sm">
                                                    🗑️ Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    <!-- BARIS JIKA PENCARIAN TIDAK DITEMUKAN -->
                                    <tr x-show="visibleCount === 0" style="display: none;">
                                        <td colspan="8" class="p-8 text-center text-slate-500 font-medium bg-slate-50">
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

                    <h3 class="text-xl font-extrabold text-slate-800 mb-2 leading-tight">Konfirmasi Hapus</h3>
                    <p class="text-sm text-slate-600 mb-6">
                        Apakah Anda yakin ingin menghapus data siswa <br>
                        <span class="font-bold text-red-600 break-words" x-text="deleteStudentName"></span>? <br>
                        <span class="text-xs italic text-slate-400 mt-1 block">Tindakan ini tidak dapat dibatalkan.</span>
                    </p>
                    
                    <div class="flex flex-row gap-3 w-full">
                        <button type="button" @click="deleteModalOpen = false" style="background-color: #f8fafc; color: #475569;" class="font-bold py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 transition w-full text-sm tracking-wide text-center">
                            Batal
                        </button>
                        <button type="button" @click="document.getElementById(deleteFormId).submit()" style="background-color: #dc2626; color: #ffffff;" class="font-bold py-3 px-4 rounded-xl shadow hover:opacity-90 transition w-full text-sm tracking-wide text-center">
                            Ya, Hapus
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
            background-color: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .custom-bulk-action-panel {
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
        }

        /* Jika layar cukup lebar (tablet ke atas), jadikan sebaris (Kiri ke Kanan) */
        @media (min-width: 768px) {
            .custom-filter-panel, .custom-bulk-action-panel {
                flex-direction: row;
                align-items: center;
            }
        }

        .custom-filter-input {
            flex: 1;
            min-width: 130px;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: all 0.2s ease;
        }

        .custom-filter-input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }

        .custom-search-bar {
            flex: 2;
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