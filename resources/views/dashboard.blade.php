<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ikhtisar Sistem Tata Usaha') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border border-gray-200">
                <div class="p-6 text-gray-900 font-medium flex items-center gap-3">
                    <span class="text-2xl">👋</span>
                    <p>Selamat datang kembali, <span class="font-bold" style="color: #2563eb;">{{ Auth::user()->name }}</span>! Berikut adalah ringkasan data administrasi SMA IT Arafah hari ini.</p>
                </div>
            </div>

            <h4 class="text-lg font-bold text-gray-800 mb-4 px-1">📁 Ringkasan Data Arsip</h4>
            <div class="flex flex-col lg:flex-row gap-6 mb-10">
                
                <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $total_surat ?? 0 }}</h3>
                        <p style="color: #dbeafe;" class="text-sm font-semibold mt-1">Total Dokumen</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 opacity-30 text-7xl">📁</div>
                </div>

                <div style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $surat_masuk ?? 0 }}</h3>
                        <p style="color: #dcfce7;" class="text-sm font-semibold mt-1">Surat Masuk</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 opacity-30 text-7xl">📥</div>
                </div>

                <div style="background: linear-gradient(135deg, #fb923c, #ea580c); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $surat_keluar ?? 0 }}</h3>
                        <p style="color: #ffedd5;" class="text-sm font-semibold mt-1">Surat Keluar</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 opacity-30 text-7xl">📤</div>
                </div>

            </div> 

            <h4 class="text-lg font-bold text-gray-800 mb-4 px-1">📊 Rincian Data Siswa</h4>
            <div class="flex flex-col lg:flex-row gap-6 mb-10">
                
                <div style="background: linear-gradient(135deg, #4f46e5, #7e22ce); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $total_siswa ?? 0 }}</h3>
                        <p style="color: #e0e7ff;" class="text-sm font-semibold mt-1">Total Terdaftar</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 opacity-20 text-7xl">🎓</div>
                </div>

                <div style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $siswa_x ?? 0 }}</h3>
                        <p style="color: #e0f2fe;" class="text-sm font-semibold mt-1">Kelas X</p>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $siswa_xi ?? 0 }}</h3>
                        <p style="color: #d1fae5;" class="text-sm font-semibold mt-1">Kelas XI</p>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $siswa_xii ?? 0 }}</h3>
                        <p style="color: #fef3c7;" class="text-sm font-semibold mt-1">Kelas XII</p>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, #e11d48, #be123c); color: #ffffff;" class="flex-1 w-full rounded-xl shadow-md p-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold">{{ $alumni ?? 0 }}</h3>
                        <p style="color: #ffe4e6;" class="text-sm font-semibold mt-1">Alumni</p>
                    </div>
                    <div class="absolute -bottom-4 -right-4 opacity-20 text-7xl">📜</div>
                </div>

            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Pintasan Cepat</h4>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('arsip.index') }}" style="background-color: #f3f4f6; color: #1f2937; border-color: #d1d5db;" class="text-center font-bold py-3 px-6 rounded-lg shadow-sm border hover:opacity-80 transition duration-300">
                            📁 Ruang E-Arsip ➔
                        </a>
                        <a href="{{ route('siswa.index') }}" style="background-color: #2563eb; color: #ffffff;" class="text-center font-bold py-3 px-6 rounded-lg shadow-sm hover:opacity-90 transition duration-300">
                            🎓 Database Siswa ➔
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>