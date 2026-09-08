<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📚 Histori Inspeksi Asrama
        </h2>
    </x-slot>

    <style>
        .k-wrapper { max-width: 1000px; margin: 0 auto; padding: 20px 15px; font-family: 'Segoe UI', sans-serif; }
        
        .k-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        
        .k-card { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; transition: transform 0.2s; }
        .k-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .k-card-header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .k-date { font-weight: 900; color: #1e293b; font-size: 16px; }
        
        .badge-putra { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-putri { background: #fce7f3; color: #be185d; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .k-card-body { padding: 20px; }
        .k-winner-box { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 10px; margin-bottom: 12px; }
        .k-box-terbersih { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .k-box-terkotor { background: #fef2f2; border: 1px solid #fecaca; }
        
        .k-kamar-name { font-weight: 900; font-size: 15px; }
        .k-title-win { color: #166534; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .k-title-lose { color: #991b1b; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        
        .k-btn-foto { background: white; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; color: #475569; cursor: pointer; text-decoration: none; transition: 0.2s; text-align: center; }
        .k-btn-foto:hover { background: #f1f5f9; color: #1e293b; }
        
        .k-card-footer { padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; font-weight: bold; display: flex; justify-content: space-between; }
    </style>

    <div class="py-6">
        <div class="k-wrapper">
            
            @if(session('success'))
                <div style="background: #10b981; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; text-align: center;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #ef4444; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; text-align: center;">
                    {{ session('error') }}
                </div>
            @endif
            
            @if($histori->isEmpty())
                <div style="text-align: center; padding: 50px 20px; background: white; border-radius: 16px; border: 1px dashed #cbd5e1;">
                    <span style="font-size: 40px;">📭</span>
                    <h3 style="font-size: 18px; font-weight: 900; color: #475569; margin-top: 15px;">Belum Ada Histori</h3>
                    <p style="color: #94a3b8; font-size: 14px;">Data histori akan muncul setelah ada inspeksi kamar yang difinalisasi.</p>
                </div>
            @else
                <div class="k-grid">
                    @foreach($histori as $h)
                        <div class="k-card">
                            <div class="k-card-header">
                                <div class="k-date">{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d M Y') }}</div>
                                @if($h->kategori == 'putra')
                                    <span class="badge-putra">👦 Divisi Putra</span>
                                @else
                                    <span class="badge-putri">👧 Divisi Putri</span>
                                @endif
                            </div>
                            
                            <div class="k-card-body">
                                
                                <!-- Box Terbersih -->
                                <div class="k-winner-box k-box-terbersih" style="flex-direction: column; align-items: stretch; gap: 10px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div class="k-title-win">👑 Terbersih (+1)</div>
                                            <div class="k-kamar-name text-green-700">{{ $h->kamarTerbersih->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                        </div>
                                        @if($h->foto_terbersih)
                                            <a href="{{ url('berkas/' . $h->foto_terbersih) }}" target="_blank" class="k-btn-foto">📷 Lihat Foto</a>
                                        @endif
                                    </div>

                                    @if(!$h->foto_terbersih)
                                        <div style="border-top: 1px dashed #bbf7d0; padding-top: 10px;">
                                            <form action="{{ route('asrama.penilaian.update-foto', $h->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                                @csrf
                                                <input type="hidden" name="jenis" value="terbersih">
                                                <input type="file" name="foto" required style="font-size: 11px; max-width: 140px; color: #166534;">
                                                <button type="submit" class="k-btn-foto" style="background: #10b981; color: white; border: none; padding: 6px 12px;">Upload</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                <!-- Box Terkotor -->
                                <div class="k-winner-box k-box-terkotor" style="flex-direction: column; align-items: stretch; gap: 10px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div class="k-title-lose">⚠️ Terkotor (-1)</div>
                                            <div class="k-kamar-name text-red-700">{{ $h->kamarTerkotor->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                        </div>
                                        @if($h->foto_terkotor)
                                            <a href="{{ url('berkas/' . $h->foto_terkotor) }}" target="_blank" class="k-btn-foto">📷 Lihat Foto</a>
                                        @endif
                                    </div>

                                    @if(!$h->foto_terkotor)
                                        <div style="border-top: 1px dashed #fecaca; padding-top: 10px;">
                                            <form action="{{ route('asrama.penilaian.update-foto', $h->id) }}" method="POST" enctype="multipart/form-data" style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                                @csrf
                                                <input type="hidden" name="jenis" value="terkotor">
                                                <input type="file" name="foto" required style="font-size: 11px; max-width: 140px; color: #991b1b;">
                                                <button type="submit" class="k-btn-foto" style="background: #f43f5e; color: white; border: none; padding: 6px 12px;">Upload</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                
                            </div>
                            
                            <div class="k-card-footer">
                                <span>Petugas Inspeksi:</span>
                                <span style="color: #2563eb;">{{ $h->musyrif->name ?? 'Admin' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>