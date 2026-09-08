<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🛏️ Kelola Penghuni: <span style="font-weight: 900; color: #1d4ed8;">{{ $kamar->nama_kamar }}</span>
            </h2>
            <a href="{{ route('asrama.kamar.index') }}" style="background-color: #e2e8f0; color: #1e293b; font-weight: bold; font-size: 14px; padding: 8px 16px; border-radius: 8px; text-decoration: none; transition: 0.2s;">
                ⬅️ Kembali ke Daftar Kamar
            </a>
        </div>
    </x-slot>

    <!-- MEMANGGIL CSS TOM SELECT -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
    <style>
        .ts-control { border-radius: 0.5rem !important; border: 1px solid #d1d5db !important; padding: 0.5rem 0.625rem !important; font-size: 0.875rem !important; box-shadow: none !important; background-color: white !important; min-height: 46px; }
        .ts-control.focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 1px #3b82f6 !important; }
        .ts-dropdown { border-radius: 0.5rem !important; font-size: 0.875rem !important; border: 1px solid #d1d5db !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .ts-dropdown .active { background-color: #eff6ff !important; color: #1e3a8a !important; }
        .ts-control .item { background: #e0e7ff !important; color: #1e40af !important; border-radius: 4px !important; border: 1px solid #bfdbfe !important; padding: 4px 8px !important; font-weight: bold !important; margin-bottom: 4px !important; }
    </style>

    <div class="py-12 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Info Musyrif Kamar -->
            <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin-bottom: 24px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <p style="color: #1e40af; margin: 0;"><strong>Guru Musyrif:</strong> {{ $kamar->musyrif->name ?? 'Belum Ditentukan' }}</p>
                <p style="font-size: 14px; color: #2563eb; margin: 4px 0 0 0;">Gunakan kotak di bawah ini untuk menambahkan penghuni baru. Siswa yang dikeluarkan dari daftar ini akan dihapus secara permanen dari kamar tanpa histori.</p>
            </div>

            <!-- Notifikasi -->
            @if(session('success'))
                <div style="background-color: #dcfce7; border-left: 4px solid #22c55e; color: #166534; padding: 16px; margin-bottom: 24px; border-radius: 8px; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 16px; margin-bottom: 24px; border-radius: 8px; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Tambah Penghuni Kamar -->
            <div style="background-color: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; margin-bottom: 32px;">
                <form action="{{ route('asrama.kamar.addMember', $kamar->id) }}" method="POST" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                    @csrf
                    <label style="font-weight: 800; color: #1f2937; white-space: nowrap; margin: 0;">➕ Masukkan Siswa:</label>
                    
                    <div style="flex: 1; min-width: 250px;">
                        <select id="cari-siswa" name="student_id[]" multiple required>
                            @foreach($students as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas: {{ $siswa->kelas }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TOMBOL YANG DIPERBAIKI -->
                    <button type="submit" style="background-color: #1e3a8a; color: white; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: bold; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">
                        Tambahkan ke Kamar
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Penghuni Kamar -->
            <div style="background-color: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; overflow-x: auto;">
                <h3 style="font-size: 18px; font-weight: 900; color: #1f2937; border-bottom: 2px solid #f3f4f6; padding-bottom: 12px; margin-top: 0; margin-bottom: 16px;">Daftar Penghuni Saat Ini</h3>
                <table style="width: 100%; text-align: left; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 16px; font-size: 14px; font-weight: 800; color: #475569; text-transform: uppercase;">Nama Siswa</th>
                            <th style="padding: 16px; font-size: 14px; font-weight: 800; color: #475569; text-transform: uppercase;">Kelas</th>
                            <th style="padding: 16px; font-size: 14px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center;">Tanggal Masuk</th>
                            <th style="padding: 16px; font-size: 14px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px; font-weight: bold; color: #1e293b;">{{ $member->student->nama_lengkap ?? 'Siswa Dihapus' }}</td>
                            <td style="padding: 16px; color: #64748b;">{{ $member->student->kelas ?? '-' }}</td>
                            <td style="padding: 16px; text-align: center; color: #64748b;">{{ \Carbon\Carbon::parse($member->tanggal_masuk)->translatedFormat('d M Y') }}</td>
                            <td style="padding: 16px; text-align: center;">
                                <form action="{{ route('asrama.kamar.removeMember', $member->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Keluarkan siswa ini dari kamar? Data penghuni akan dihapus permanen dari daftar ini.');">
                                    @csrf @method('PUT')
                                    <button type="submit" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: 0.2s;">Keluarkan</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: #94a3b8; font-style: italic;">Belum ada penghuni di kamar ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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