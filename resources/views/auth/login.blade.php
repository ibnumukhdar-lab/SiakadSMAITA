@php
    $pengaturan = \App\Models\Pengaturan::first();
@endphp

<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 py-12 bg-gray-50">
        
        <div class="mb-8 text-center w-full max-w-md">
            @if($pengaturan && $pengaturan->logo_path)
                <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="w-20 h-20 mx-auto mb-5 object-contain" alt="Logo">
            @endif
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                {{ $pengaturan->nama_sekolah ?? 'SIAKAD Sekolah' }}
            </h1>
            <p class="text-gray-500 font-semibold tracking-widest uppercase text-xs mt-2">
                {{ $pengaturan->motto ?? 'SISTEM INFORMASI' }}
            </p>
        </div>

        <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-6 sm:p-10 border border-gray-100">
            
            <div class="mb-8 text-center sm:text-left">
                <h2 class="text-2xl font-bold text-gray-900">Login Admin</h2>
                <p class="text-sm text-gray-500 mt-1">Silakan masuk menggunakan email Anda.</p>
            </div>
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-2">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-900 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors outline-none" 
                           placeholder="admin@sekolah.sch.id" required autofocus>
                </div>
                
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-2">Kata Sandi</label>
                    <input type="password" name="password" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-900 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors outline-none" 
                           placeholder="••••••••" required>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 text-base tracking-wide">
                        Masuk Sekarang
                    </button>
                </div>
            </form>
            
        </div>
        
        <p class="text-center text-sm text-gray-400 mt-10">
            &copy; {{ date('Y') }} {{ $pengaturan->nama_sekolah ?? 'SIAKAD' }}. All rights reserved.
        </p>
    </div>
</x-guest-layout>