<x-app-layout>
    <div class="py-8" x-data="{ 
        search: '', 
        filterJenis: '', 
        filterBulan: '', 
        filterTahun: '',
        deleteSingleModalOpen: false,
        deleteSingleUrl: '',
        deleteBulkModalOpen: false,
        bulkMethod: 'POST',
        selectedItems: [],
        qrModalOpen: false,
        qrImageUrl: '',
        qrDownloadName: ''
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6 flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Data Arsip Surat</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola arsip surat masuk &amp; keluar</p>
                </div>
                <a href="{{ route('arsip.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition duration-200 w-full md:w-auto text-center whitespace-nowrap">
                    ➕ Tambah Surat Baru
                </a>
            </div>

            <form action="#" method="POST" id="bulkActionForm">
                @csrf
                <input type="hidden" name="_method" :value="bulkMethod">
                
                <div class="bg-white p-5 rounded-2xl border border-slate-200 mb-6 shadow-sm flex flex-col lg:flex-row items-center gap-4">
                    
                    <div class="flex items-center gap-2 w-full lg:w-[25%]">
                        <select name="bulk_action_type" id="bulkActionType" class="border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-blue-500 w-[65%] shadow-sm" required>
                            <option value="">Pilih Aksi...</option>
                            <option value="delete">🗑️ Hapus</option>
                            <option value="export">📊 Ekspor Excel</option> 
                        </select>
                        <button type="button" @click="
                            let action = document.getElementById('bulkActionType').value;
                            if (action === '') {
                                alert('Silakan pilih aksi terlebih dahulu.');
                                return;
                            }
                            let checked = document.querySelectorAll('.arsip-checkbox:checked');
                            if (checked.length === 0) {
                                alert('Silakan centang minimal satu data terlebih dahulu.');
                                return;
                            }
                            
                            let form = document.getElementById('bulkActionForm');
                            if (action === 'delete') {
                                selectedItems = Array.from(checked).map(cb => cb.getAttribute('data-identitas'));
                                form.action = '{{ route('arsip.destroyBulk') }}';
                                bulkMethod = 'DELETE'; // Ubah ke DELETE untuk aksi hapus
                                deleteBulkModalOpen = true;
                            } else if (action === 'export') {
                                form.action = '{{ route('arsip.exportBulk') }}';
                                bulkMethod = 'POST'; // Kembalikan ke POST untuk export
                                form.submit();
                            }
                        " class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-3 rounded-lg text-sm transition shadow-sm w-[35%] text-center">
                            Terapkan
                        </button>
                    </div>

                    <div class="w-full lg:w-[30%]">
                        <input type="text" x-model="search" placeholder="🔍 Cari Perihal / No. Surat..." class="border-slate-300 rounded-lg py-2.5 px-4 text-sm w-full focus:ring-blue-500 shadow-sm">
                    </div>

                    <div class="flex flex-row items-center gap-2 w-full lg:w-[45%]">
                        <select x-model="filterJenis" class="border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-blue-500 shadow-sm w-1/3">
                            <option value="">Kategori</option>
                            <option value="Surat Masuk">Masuk</option>
                            <option value="Surat Keluar">Keluar</option>
                        </select>
                        
                        <select x-model="filterBulan" class="border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-blue-500 shadow-sm w-1/3">
                            <option value="">Bulan</option>
                            <option value="01">Jan</option><option value="02">Feb</option>
                            <option value="03">Mar</option><option value="04">Apr</option>
                            <option value="05">Mei</option><option value="06">Jun</option>
                            <option value="07">Jul</option><option value="08">Agu</option>
                            <option value="09">Sep</option><option value="10">Okt</option>
                            <option value="11">Nov</option><option value="12">Des</option>
                        </select>
                        
                        <select x-model="filterTahun" class="border-slate-300 rounded-lg py-2.5 px-3 text-sm focus:ring-blue-500 shadow-sm w-1/3">
                            <option value="">Tahun</option>
                            @php $tahunSekarang = date('Y'); @endphp
                            @for($i = $tahunSekarang; $i >= $tahunSekarang - 5; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1000px]">
                            <thead>
                                <tr class="bg-blue-50/70 border-b border-blue-100">
                                    <th class="p-4 w-12 text-center"><input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-900 focus:ring-blue-600 w-4 h-4"></th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600 w-12 text-center">No</th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600">Kategori</th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600">No. Surat</th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600">Perihal</th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600">Tanggal</th>
                                    <th class="p-4 text-xs font-bold tracking-wider uppercase text-slate-600 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($arsips as $index => $arsip)
                                <tr class="hover:bg-slate-50 transition duration-150"
                                    x-show="(!search || String(@js(strtolower($arsip->nomor_surat . ' ' . ($arsip->perihal ?? '')))).includes(search.toLowerCase())) && 
                                            (!filterJenis || '{{ $arsip->jenis_surat }}' == filterJenis) &&
                                            (!filterBulan || '{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('m') }}' == filterBulan) &&
                                            (!filterTahun || '{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('Y') }}' == filterTahun)">
                                    
                                    <td class="p-5 text-center">
                                        <input type="checkbox" name="arsip_ids[]" value="{{ $arsip->id }}" data-identitas="{{ $arsip->nomor_surat }} ({{ Str::limit($arsip->perihal, 30) }})" class="rounded border-slate-400 text-blue-600 focus:ring-blue-500 arsip-checkbox w-4 h-4">
                                    </td>
                                    
                                    <td class="p-5 text-sm text-slate-700 text-center font-medium">{{ $index + 1 }}</td>
                                    <td class="p-5 text-sm">
                                        <span class="px-4 py-1.5 text-xs font-bold rounded-full shadow-sm border {{ $arsip->jenis_surat == 'Surat Masuk' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                            {{ $arsip->jenis_surat }}
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm font-bold text-slate-800">{{ $arsip->nomor_surat }}</td>
                                    <td class="p-5 text-sm text-slate-600 font-medium">{{ $arsip->perihal }}</td>
                                    <td class="p-5 text-sm text-slate-600 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('d M Y') }}</td>
                                    <td class="p-5">
                                        <div class="flex justify-center gap-2">
                                            
                                            <button type="button" @click="qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(route('arsip.show', $arsip->id)) }}'; qrDownloadName = '{{ preg_replace('/[^A-Za-z0-9\-]/', '_', $arsip->nomor_surat) }}'; qrModalOpen = true;" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-4 py-2 rounded-lg text-xs font-bold border border-indigo-300 shadow-sm transition flex items-center gap-1">
                                                📱 QR
                                            </button>

                                            <a href="{{ route('arsip.show', $arsip->id) }}" class="bg-white text-slate-700 hover:bg-slate-100 hover:text-blue-600 px-4 py-2 rounded-lg text-xs font-bold border border-slate-300 shadow-sm transition flex items-center gap-1">
                                                👁️ Cek
                                            </a>
                                            
                                            <a href="{{ route('arsip.edit', $arsip->id) }}" class="bg-amber-50 text-amber-700 hover:bg-amber-100 px-4 py-2 rounded-lg text-xs font-bold border border-amber-300 shadow-sm transition flex items-center gap-1">
                                                ✏️ Edit
                                            </a>
                                            
                                            <button type="button" @click="deleteSingleUrl = '{{ route('arsip.destroy', $arsip->id) }}'; deleteSingleModalOpen = true;" class="bg-rose-50 text-rose-700 hover:bg-rose-100 px-4 py-2 rounded-lg text-xs font-bold border border-rose-300 shadow-sm transition flex items-center gap-1">
                                                🗑️ Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-slate-500 font-medium bg-slate-50 rounded-b-2xl">
                                        <span class="text-4xl block mb-3">📭</span>
                                        Belum ada data surat yang diarsipkan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                    <p class="text-sm font-bold text-slate-600 mb-5" x-text="qrDownloadName.replace(/_/g, ' ')"></p>
                    
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
            let checkboxes = document.querySelectorAll('.arsip-checkbox');
            checkboxes.forEach(cb => {
                if(cb.closest('tr').style.display !== 'none') {
                    cb.checked = e.target.checked;
                } else {
                    cb.checked = false;
                }
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