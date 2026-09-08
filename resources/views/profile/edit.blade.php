<x-app-layout>
    {{-- Judul halaman --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Profil Saya</h3>
                <p class="text-sm text-slate-500 mt-0.5">Kelola foto profil, informasi akun, kata sandi, dan keamanan</p>
            </div>
        </div>

        <div class="space-y-6 max-w-2xl">
            @include('profile.partials.update-profile-information-form')

            @include('profile.partials.update-password-form')

            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
