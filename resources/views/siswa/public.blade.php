@php
    $pengaturan = \App\Models\Pengaturan::first();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keabsahan Siswa - {{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between antialiased">

    <header class="bg-white shadow-sm border-b border-slate-200 py-4">
        <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                
                @if($pengaturan && $pengaturan->logo_path)
                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                    <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="w-10 h-10 rounded-lg object-contain bg-white shadow-sm border border-slate-100">
                @else
                    <div style="background: linear-gradient(135deg, #1e40af, #1e3a8a);" class="w-10 h-10 text-white rounded-lg flex items-center justify-center font-bold text-xl shadow-md">
                        {{ substr($pengaturan->nama_sekolah ?? 'S', 0, 1) }}
                    </div>
                @endif

                <div class="flex flex-col">
                    <span class="font-extrabold text-slate-900 text-base leading-none tracking-tight">{{ $pengaturan->nama_sekolah ?? 'SMA IT ARAFAH' }}</span>
                    <span class="text-[10px] text-blue-600 font-bold tracking-widest mt-1 uppercase">{{ $pengaturan->motto ?? 'Cerdas & Beradab' }}</span>
                </div>
            </div>
            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200 shadow-sm">
                Sistem Valid Resmi
            </span>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 my-6">
        <div class="bg-white max-w-md w-full rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 text-center relative overflow-hidden">
            
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-3xl mx-auto shadow-inner mb-3 font-bold">
                    ✓
                </div>
                <span class="text-xs font-extrabold text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200 uppercase tracking-wider shadow-sm">
                    Siswa Terverifikasi Aktif
                </span>
            </div>

            <div class="w-28 h-36 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm mx-auto mb-4">
                @if($siswa->foto)
                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                    <img src="{{ url('berkas/' . $siswa->foto) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-5xl text-slate-300">👤</span>
                @endif
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-1">{{ $siswa->nama_lengkap }}</h2>
            <p class="text-blue-600 font-mono font-bold text-sm mb-6">NISN: {{ $siswa->nisn }}</p>

            <div class="bg-slate-50 rounded-xl border border-slate-100 p-4 text-left space-y-3">
                <div class="flex justify-between border-b border-slate-200 pb-2 text-sm">
                    <span class="text-slate-500 font-medium">Jenis Kelamin</span>
                    <span class="font-bold text-slate-800">{{ $siswa->jk }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-2 text-sm">
                    <span class="text-slate-500 font-medium">Kelas</span>
                    <span class="font-bold text-slate-800">Kelas {{ $siswa->kelas ?? '-' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-2 text-sm">
                    <span class="text-slate-500 font-medium">Angkatan</span>
                    <span class="font-bold text-slate-800">Tahun {{ $siswa->thn_masuk ?? '-' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Status Keanggotaan</span>
                    <span class="font-bold text-green-600">{{ $siswa->status }}</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 mt-6 leading-relaxed">
                🔒 Detail informasi domisili, logbook kedisiplinan, catatan prestasi, dan data orang tua dilindungi hak privasinya dan hanya dapat diakses penuh oleh manajemen tata usaha sekolah melalui otentikasi login resmi.
            </p>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-400 font-medium">
        &copy; {{ date('Y') }} SIAKAD {{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}. All Rights Reserved.
    </footer>

</body>
</html>