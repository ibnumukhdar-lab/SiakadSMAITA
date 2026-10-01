<x-guest-layout>
    <!-- CSS Murni untuk Memaksa Tampilan Rapi di Tengah -->
    <style>
        /* Mengatur background halaman */
        body {
            background-color: #f3f4f6 !important; /* Warna abu-abu terang */
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Wadah utama untuk menengahkan form */
        .auth-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        /* Desain Kartu Form */
        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px; /* Lebar maksimal form */
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-top: 5px solid #1e3a8a; /* Aksen garis biru di atas */
        }

        /* Logo Avatar Bulat */
        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .logo-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.4);
        }

        /* Kustomisasi Input Form */
        .label-kustom {
            font-size: 0.875rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.4rem;
            display: block;
        }
        .input-kustom {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            transition: all 0.2s;
            box-sizing: border-box;
            font-size: 0.95rem;
        }
        .input-kustom:focus {
            border-color: #1e3a8a !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.2) !important;
            background-color: #ffffff;
        }

        /* Tombol Utama */
        .btn-warna-utama {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
            padding: 0.875rem;
            border-radius: 0.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            margin-top: 1rem;
        }
        .btn-warna-utama:hover {
            background-color: #152b68 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Pembungkus Grup Form */
        .form-group {
            margin-bottom: 1.25rem;
        }
    </style>

    <!-- Pembungkus Layar Penuh -->
    <div class="auth-container">
        
        <!-- Kartu Form Registrasi -->
        <div class="auth-card">
            
            <!-- Bagian Ikon / Logo -->
            <div class="logo-container">
                <div class="logo-circle">
                    🏫
                </div>
            </div>

            <!-- Header / Judul Form & Arahan -->
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <h2 style="font-size: 1.75rem; font-weight: 900; color: #1e3a8a; margin-bottom: 0.5rem; letter-spacing: -0.025em;">Portal Registrasi</h2>
                <p style="font-size: 0.9rem; color: #6b7280; font-weight: 500; line-height: 1.5;">Mari bergabung dengan sistem akademik cerdas SMA IT Arafah. Lengkapi identitas Anda untuk memulai.</p>
            </div>

            <!-- Form Registrasi -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="name" class="label-kustom">{{ __('Nama Lengkap') }}</label>
                    <input id="name" class="input-kustom" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Ketik nama lengkap Anda..." />
                    <x-input-error :messages="$errors->get('name')" style="margin-top: 0.5rem; color: #dc2626; font-size: 0.8rem;" />
                </div>

                <!-- Alamat Email -->
                <div class="form-group">
                    <label for="email" class="label-kustom">{{ __('Alamat Email Aktif') }}</label>
                    <input id="email" class="input-kustom" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="contoh: user@belajar.id" />
                    <x-input-error :messages="$errors->get('email')" style="margin-top: 0.5rem; color: #dc2626; font-size: 0.8rem;" />
                </div>

                <!-- Kata Sandi -->
                <div class="form-group">
                    <label for="password" class="label-kustom">{{ __('Buat Kata Sandi') }}</label>
                    <input id="password" class="input-kustom" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter rahasia" />
                    <x-input-error :messages="$errors->get('password')" style="margin-top: 0.5rem; color: #dc2626; font-size: 0.8rem;" />
                </div>

                <!-- Konfirmasi Kata Sandi -->
                <div class="form-group">
                    <label for="password_confirmation" class="label-kustom">{{ __('Ulangi Kata Sandi') }}</label>
                    <input id="password_confirmation" class="input-kustom" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi di atas" />
                    <x-input-error :messages="$errors->get('password_confirmation')" style="margin-top: 0.5rem; color: #dc2626; font-size: 0.8rem;" />
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn-warna-utama">
                    {{ __('Daftar Sekarang') }}
                </button>

                <!-- Link Kembali ke Login -->
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="{{ route('login') }}" style="font-size: 0.9rem; color: #4b5563; text-decoration: none; font-weight: 600; transition: color 0.2s;">
                        {{ __('Sudah terdaftar? ') }} <span style="color: #1e3a8a; text-decoration: underline;">Masuk di sini</span>
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>