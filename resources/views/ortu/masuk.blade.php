<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Orang Tua — SMA IT Arafah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-5">
            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">SMA IT Arafah</p>
            <h1 class="text-xl font-black text-gray-800">Portal Orang Tua</h1>
            <p class="text-xs font-semibold text-gray-600 mt-1">Masuk untuk melihat perkembangan ananda</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3">
                    @foreach($errors->all() as $galat)
                        <p class="text-xs font-bold text-red-800">{{ $galat }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('ortu.proses') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-black text-gray-600 uppercase tracking-widest mb-1" for="nisn">NISN siswa</label>
                    <input id="nisn" name="nisn" inputmode="numeric" autocomplete="username" required
                           value="{{ old('nisn') }}"
                           class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-600 uppercase tracking-widest mb-1" for="sandi">Kata sandi</label>
                    <input id="sandi" name="sandi" type="password" inputmode="numeric" autocomplete="current-password" required
                           class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <p class="text-xs text-gray-500 font-semibold mt-1">Kata sandi = tanggal lahir ananda, format <b>ddmmyyyy</b> (contoh: 24 Mei 2009 → 24052009).</p>
                </div>

                <button type="submit" class="w-full py-3 rounded-lg bg-gray-800 text-white text-sm font-black">Masuk</button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-500 font-semibold mt-4 leading-relaxed">
            Lupa atau belum punya kata sandi? Hubungi Tata Usaha sekolah.<br>
            Halaman ini hanya menampilkan data ananda sendiri.
        </p>
    </div>
</body>
</html>
