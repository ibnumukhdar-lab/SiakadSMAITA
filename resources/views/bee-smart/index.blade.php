<x-app-layout>
    <x-slot name="header">
        <h2 class="bee-page-title">
            🐝 <span>BEE Smart Dashboard</span>
        </h2>
    </x-slot>

    <div class="bee-container">
        
        @if(session('success'))
            <div class="bee-alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="bee-card">
            <!-- Header Card Gradient -->
            <div class="bee-card-header">
                <div class="bee-header-text">
                    <h3>Bilingual Environment Enhancement</h3>
                    <p>Kelola modul kosakata mingguan untuk layar TV dan papan interaktif kelas.</p>
                </div>
                
                <!-- Form Tambah Minggu -->
                <form action="{{ route('bee.storeWeek') }}" method="POST" class="bee-form-add">
                    @csrf
                    <input type="text" name="judul" placeholder="Contoh: Minggu 1: Lingkungan" required class="bee-input">
                    <button type="submit" class="bee-btn-submit">
                        ➕ Buat Modul
                    </button>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="bee-table-responsive">
                <table class="bee-table">
                    <thead>
                        <tr>
                            <th class="text-left">Judul Modul</th>
                            <th>Isi Kosakata</th>
                            <th>Status Tayang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weeks as $week)
                        <tr>
                            <td class="text-left">
                                <div class="bee-modul-title">{{ $week->judul }}</div>
                                <div class="bee-modul-date">Dibuat: {{ \Carbon\Carbon::parse($week->tanggal_mulai)->translatedFormat('d F Y') }}</div>
                            </td>
                            <td>
                                <span class="bee-badge-count">
                                    {{ $week->vocabs_count }} / 10 Kata
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('bee.updateStatus', $week->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="bee-select-status {{ $week->status }}">
                                        <option value="draft" {{ $week->status == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                        <option value="aktif" {{ $week->status == 'aktif' ? 'selected' : '' }}>📺 TAYANG DI TV</option>
                                        <option value="arsip" {{ $week->status == 'arsip' ? 'selected' : '' }}>📚 Arsip</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-group">
                                    <!-- Tombol Kelola -->
                                    <a href="{{ route('bee.manage', $week->id) }}" class="bee-btn-action">
                                        ✍️ Input Kata
                                    </a>

                                    <!-- Tombol Edit Judul (Memicu JS) -->
                                    <button type="button" onclick="editJudul('{{ $week->id }}', '{{ addslashes($week->judul) }}')" class="bee-btn-edit">
                                        ✏️ Edit Judul
                                    </button>

                                    <!-- Form Tersembunyi untuk Edit Judul -->
                                    <form id="edit-form-{{ $week->id }}" action="{{ route('bee.updateWeek', $week->id) }}" method="POST" style="display: none;">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="judul" id="edit-input-{{ $week->id }}">
                                    </form>

                                    <!-- Tombol Hapus (Hard Delete) -->
                                    <form action="{{ route('bee.destroyWeek', $week->id) }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN KERAS!\n\nYakin ingin menghapus modul ini?\n\nSeluruh KOSAKATA beserta REKAMAN AUDIO di dalamnya akan TERHAPUS PERMANEN (Hard Delete) dan tidak dapat dikembalikan.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bee-btn-delete">🗑️ Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="bee-empty-state">
                                Belum ada modul kosakata. Silakan buat modul pertama Anda!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- SCRIPT UNTUK POP-UP EDIT JUDUL -->
    <script>
        function editJudul(id, oldJudul) {
            let newJudul = prompt("Masukkan Judul Modul yang baru:", oldJudul);
            if (newJudul != null && newJudul.trim() !== "") {
                document.getElementById('edit-input-' + id).value = newJudul;
                document.getElementById('edit-form-' + id).submit();
            }
        }
    </script>

    <!-- ========================================== -->
    <!-- CSS MURNI EKSKLUSIF UNTUK BEE SMART        -->
    <!-- ========================================== -->
    <style>
        /* Tipografi & Layout Dasar */
        .bee-page-title { font-family: 'Segoe UI', system-ui, sans-serif; font-weight: 600; font-size: 1.25rem; color: #1f2937; display: flex; align-items: center; gap: 8px; margin: 0; }
        .bee-container { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        /* Alert Sukses */
        .bee-alert-success { background-color: #dcfce7; border-left: 5px solid #22c55e; color: #15803d; padding: 1rem 1.5rem; margin-bottom: 1.5rem; border-radius: 6px; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        /* Wadah Utama (Card) */
        .bee-card { background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f3f4f6; overflow: hidden; margin-bottom: 2rem; }
        
        /* Header Card (Warna Kuning Lebah) - Responsif Mobile First */
        .bee-card-header { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.2rem; align-items: center; text-align: center; }
        @media(min-width: 768px) { .bee-card-header { flex-direction: row; padding: 1.5rem 2rem; justify-content: space-between; text-align: left; } }
        
        .bee-header-text h3 { margin: 0; font-size: 1.3rem; font-weight: 900; color: #111827; letter-spacing: -0.025em; }
        @media(min-width: 768px) { .bee-header-text h3 { font-size: 1.5rem; } }
        .bee-header-text p { margin: 5px 0 0 0; color: #713f12; font-weight: 600; font-size: 0.85rem; }
        
        /* Form & Tombol Tambah - Responsif Mobile First */
        .bee-form-add { display: flex; flex-direction: column; width: 100%; gap: 10px; }
        @media(min-width: 768px) { .bee-form-add { flex-direction: row; width: auto; } }
        
        .bee-input { padding: 0.8rem 1.2rem; border-radius: 8px; border: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); font-weight: 700; color: #374151; width: 100%; outline: none; }
        @media(min-width: 768px) { .bee-input { min-width: 300px; padding: 0.6rem 1.2rem; width: auto; } }
        .bee-input:focus { box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.5); }
        
        .bee-btn-submit { background-color: #111827; color: #ffffff; border: none; padding: 0.8rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; display: flex; justify-content: center; align-items: center; gap: 5px; }
        @media(min-width: 768px) { .bee-btn-submit { width: auto; padding: 0.6rem 1.5rem; display: inline-block; } }
        .bee-btn-submit:hover { background-color: #1f2937; transform: translateY(-1px); }
        
        /* Tabel & Isinya */
        .bee-table-responsive { overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch; }
        .bee-table { width: 100%; border-collapse: collapse; min-width: 850px; }
        
        .bee-table th { background-color: #f9fafb; padding: 1.2rem 1rem; font-size: 0.8rem; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e5e7eb; text-align: center; }
        .bee-table th.text-left { text-align: left; }
        
        .bee-table td { padding: 1.2rem 1rem; border-bottom: 1px solid #f3f4f6; text-align: center; vertical-align: middle; }
        .bee-table td.text-left { text-align: left; }
        
        .bee-table tbody tr { transition: background-color 0.2s; }
        .bee-table tbody tr:hover { background-color: #fefce8; }
        
        .bee-modul-title { font-weight: 800; color: #1f2937; font-size: 1.1rem; }
        .bee-modul-date { font-size: 0.75rem; color: #9ca3af; font-weight: 600; margin-top: 4px; }
        
        .bee-badge-count { display: inline-block; background-color: #f3f4f6; color: #4b5563; font-weight: 900; font-size: 0.85rem; padding: 4px 14px; border-radius: 999px; }
        
        /* Select Dropdown Status */
        .bee-select-status { padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; cursor: pointer; border: 1px solid #d1d5db; outline: none; appearance: none; text-align: center; transition: all 0.2s; min-width: 130px; }
        .bee-select-status.aktif { background-color: #dcfce7; color: #15803d; border-color: #86efac; }
        .bee-select-status.draft { background-color: #f3f4f6; color: #4b5563; border-color: #e5e7eb; }
        .bee-select-status.arsip { background-color: #ffedd5; color: #c2410c; border-color: #fdba74; }
        
        /* Grup Tombol Aksi */
        .action-group { display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap; }
        
        .bee-btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 6px; background-color: #facc15; color: #713f12; font-weight: 800; font-size: 0.85rem; padding: 8px 14px; border-radius: 6px; text-decoration: none; box-shadow: 0 2px 4px rgba(250, 204, 21, 0.2); transition: all 0.2s; }
        .bee-btn-action:hover { background-color: #eab308; color: #451a03; transform: translateY(-1px); }

        .bee-btn-edit { background-color: #e0f2fe; color: #1e40af; font-weight: 800; font-size: 0.85rem; padding: 8px 14px; border-radius: 6px; border: 1px solid #bfdbfe; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .bee-btn-edit:hover { background-color: #bfdbfe; transform: translateY(-1px); }

        .bee-btn-delete { background-color: #fee2e2; color: #b91c1c; font-weight: 800; font-size: 0.85rem; padding: 8px 14px; border-radius: 6px; border: 1px solid #fca5a5; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .bee-btn-delete:hover { background-color: #fca5a5; transform: translateY(-1px); }
        
        .bee-empty-state { padding: 3rem !important; color: #9ca3af; font-weight: 600; font-size: 1rem; }
    </style>
</x-app-layout>