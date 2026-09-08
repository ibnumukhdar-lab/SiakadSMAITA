<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 Kamar Binaan Saya
        </h2>
    </x-slot>

    <style>
        .kamar-wrapper { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 1200px; margin: 0 auto; padding: 30px 15px; }
        .k-card { background: #ffffff; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; padding: 25px 30px; margin-bottom: 30px; }
        .k-card-title { font-size: 20px; font-weight: 900; color: #1e293b; margin-top: 0; margin-bottom: 5px; }
        
        .k-table-wrap { overflow-x: auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; }
        .k-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .k-table th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 13px; text-transform: uppercase; padding: 16px 20px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .k-table th.center { text-align: center; }
        .k-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 15px; vertical-align: middle; }
        
        .k-nama-kamar { font-weight: 900; color: #0f172a; text-transform: uppercase; }
        .badge-putra { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .badge-putri { background: #fce7f3; color: #be185d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
        
        .k-btn-manage { background: #4f46e5; color: #ffffff; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; text-decoration: none; display: inline-block; transition: 0.2s; }
        .k-btn-manage:hover { background: #4338ca; transform: translateY(-2px); }
    </style>

    <div class="kamar-wrapper">
        
        <div class="k-card" style="border-left: 5px solid #10b981;">
            <h3 class="k-card-title">Assalamu'alaikum, {{ Auth::user()->name }}</h3>
            <p style="color: #64748b; font-size: 14px; margin: 0;">Berikut adalah daftar kamar asrama yang berada di bawah tanggung jawab/binaan Anda.</p>
        </div>

        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>Nama Kamar</th>
                        <th>Kategori</th>
                        <th class="center">Kapasitas</th>
                        <th class="center">Penghuni Saat Ini</th>
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
                        <td class="center">{{ $kamar->kapasitas }}</td>
                        <td class="center" style="font-weight: 900; color: {{ $kamar->members_count >= $kamar->kapasitas ? '#ef4444' : '#10b981' }};">
                            {{ $kamar->members_count }} Anak
                        </td>
                        <td class="center">
                            <a href="{{ route('asrama.kamar.show', $kamar->id) }}" class="k-btn-manage">👥 Kelola Anggota</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">Anda belum ditugaskan sebagai Musyrif di kamar manapun.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>