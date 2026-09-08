<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🛏️ Manajemen Kamar Asrama
        </h2>
    </x-slot>

    <!-- CUSTOM CSS MURNI -->
    <style>
        .kamar-wrapper { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 1200px; margin: 0 auto; padding: 30px 15px; }
        .k-alert-success { background: #dcfce7; border-left: 5px solid #22c55e; color: #166534; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .k-alert-error { background: #fee2e2; border-left: 5px solid #ef4444; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .k-card { background: #ffffff; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; padding: 25px 30px; margin-bottom: 30px; }
        .k-card-title { font-size: 20px; font-weight: 800; color: #1e293b; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .k-form-inline { display: grid; grid-template-columns: 2fr 1.5fr 2fr 1fr auto; gap: 15px; align-items: center; }
        .k-input { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; transition: all 0.3s; background: #f8fafc; font-family: inherit; color: #334155; box-sizing: border-box; }
        .k-input:focus { border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
        .k-btn-primary { background: #1e293b; color: #ffffff; padding: 12px 24px; border-radius: 10px; font-weight: 800; cursor: pointer; transition: all 0.3s; border: none; font-size: 14px; display: inline-flex; justify-content: center; align-items: center; height: 100%; }
        .k-btn-primary:hover { background: #0f172a; transform: translateY(-2px); }
        
        .k-table-wrap { overflow-x: auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; }
        .k-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .k-table th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 13px; text-transform: uppercase; padding: 16px 20px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .k-table th.center { text-align: center; }
        .k-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 15px; vertical-align: middle; }
        .k-table tr:hover td { background: #fcfcfc; }
        
        .k-nama-kamar { font-weight: 900; color: #0f172a; text-transform: uppercase; }
        .badge-putra { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .badge-putri { background: #fce7f3; color: #be185d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
        
        .k-action-group { display: flex; gap: 8px; justify-content: center; }
        .k-btn-action { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .k-btn-manage { background: #4f46e5; color: #ffffff; }
        .k-btn-edit { background: #f59e0b; color: #ffffff; padding: 8px 12px; }
        .k-btn-delete { background: #ef4444; color: #ffffff; padding: 8px 12px; }
        
        .k-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(5px); z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .k-modal-box { background: #ffffff; border-radius: 20px; width: 100%; max-width: 450px; overflow: hidden; }
        .k-modal-header { background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .k-modal-body { padding: 25px; }
        .k-form-group { margin-bottom: 18px; }
        .k-form-label { display: block; font-size: 13px; font-weight: 800; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .k-modal-footer { padding: 20px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px; }
        
        @media (max-width: 900px) {
            .k-form-inline { grid-template-columns: 1fr 1fr; }
            .k-btn-primary { grid-column: span 2; }
        }
    </style>

    <div class="kamar-wrapper" x-data="{
        showEditModal: false,
        editForm: { actionUrl: '', nama_kamar: '', kategori: 'putra', musyrif_id: '', kapasitas: '', status: 'aktif' }
    }">
        @if(session('success'))
            <div class="k-alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="k-alert-error">Ada kesalahan input: <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul></div>
        @endif

        <!-- Form Tambah Kamar -->
        <div class="k-card">
            <h3 class="k-card-title">➕ Buat Kamar Baru</h3>
            
            <form action="{{ route('asrama.kamar.store') }}" method="POST" class="k-form-inline">
                @csrf
                <input type="text" name="nama_kamar" placeholder="Nama Kamar" class="k-input" required>
                
                <select name="kategori" class="k-input" required>
                    <option value="putra">Asrama Putra</option>
                    <option value="putri">Asrama Putri</option>
                </select>

                <select name="musyrif_id" class="k-input" required>
                    <option value="">-- Musyrif --</option>
                    @foreach($teachers as $guru)
                        <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                    @endforeach
                </select>

                <input type="number" name="kapasitas" placeholder="Kapasitas" value="4" min="1" class="k-input" required title="Kapasitas Kamar">

                <button type="submit" class="k-btn-primary">Simpan</button>
            </form>
        </div>

        <!-- Tabel Daftar Kamar -->
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>Nama Kamar</th>
                        <th>Kategori</th>
                        <th>Musyrif</th>
                        <th class="center">Kapasitas</th>
                        <th class="center">Penghuni</th>
                        <th class="center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kamars as $kamar)
                    <tr>
                        <td class="k-nama-kamar">{{ $kamar->nama_kamar }}</td>
                        <td>
                            @if($kamar->kategori == 'putra')
                                <span class="badge-putra">👦 PUTRA</span>
                            @else
                                <span class="badge-putri">👧 PUTRI</span>
                            @endif
                        </td>
                        <td>{{ $kamar->musyrif->name ?? '-' }}</td>
                        <td class="center">{{ $kamar->kapasitas }}</td>
                        <td class="center" style="font-weight: 800; color: #2563eb;">{{ $kamar->members_count }} Org</td>
                        <td class="center">
                            <div class="k-action-group">
                                <a href="{{ route('asrama.kamar.show', $kamar->id) }}" class="k-btn-action k-btn-manage">Kelola</a>
                                
                                <button type="button" @click="
                                    editForm.actionUrl = '{{ route('asrama.kamar.update', $kamar->id) }}';
                                    editForm.nama_kamar = '{{ addslashes($kamar->nama_kamar) }}';
                                    editForm.kategori = '{{ $kamar->kategori }}';
                                    editForm.musyrif_id = '{{ $kamar->musyrif_id }}';
                                    editForm.kapasitas = '{{ $kamar->kapasitas }}';
                                    editForm.status = '{{ $kamar->status }}';
                                    showEditModal = true;
                                " class="k-btn-action k-btn-edit">✏️</button>

                                <form action="{{ route('asrama.kamar.destroy', $kamar->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Hapus kamar permanen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="k-btn-action k-btn-delete">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">Belum ada kamar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- MODAL EDIT -->
        <div x-show="showEditModal" style="display: none;" class="k-modal-overlay">
            <div class="k-modal-box" @click.away="showEditModal = false">
                <div class="k-modal-header">
                    <h3 style="font-size: 18px; font-weight: 900; margin: 0;">✏️ Edit Data Kamar</h3>
                    <button @click="showEditModal = false" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>
                <form :action="editForm.actionUrl" method="POST">
                    <div class="k-modal-body">
                        @csrf @method('PUT')
                        <div class="k-form-group">
                            <label class="k-form-label">Nama Kamar</label>
                            <input type="text" name="nama_kamar" x-model="editForm.nama_kamar" class="k-input" required>
                        </div>
                        <div class="k-form-group">
                            <label class="k-form-label">Kategori Asrama</label>
                            <select name="kategori" x-model="editForm.kategori" class="k-input" required>
                                <option value="putra">Asrama Putra</option>
                                <option value="putri">Asrama Putri</option>
                            </select>
                        </div>
                        <div class="k-form-group">
                            <label class="k-form-label">Guru Musyrif</label>
                            <select name="musyrif_id" x-model="editForm.musyrif_id" class="k-input" required>
                                <option value="">-- Pilih Guru Musyrif --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="k-form-group">
                                <label class="k-form-label">Kapasitas</label>
                                <input type="number" name="kapasitas" x-model="editForm.kapasitas" min="1" class="k-input" required>
                            </div>
                            <div class="k-form-group">
                                <label class="k-form-label">Status</label>
                                <select name="status" x-model="editForm.status" class="k-input" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="k-modal-footer">
                        <button type="button" @click="showEditModal = false" style="padding: 10px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer;">Batal</button>
                        <button type="submit" style="background: #1e3a8a; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>