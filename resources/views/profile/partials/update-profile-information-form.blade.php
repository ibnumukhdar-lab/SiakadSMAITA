<section>
    <!-- Memanggil pustaka Cropper.js dari CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <!-- CSS Biasa Untuk Warna Tombol -->
    <style>
        .btn-warna-utama {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            border: none;
        }
        .btn-warna-utama:hover {
            background-color: #152b68 !important; /* Lebih gelap saat di-hover */
        }
        
        .btn-warna-abu {
            background-color: #e5e7eb !important;
            color: #374151 !important;
            border: none;
        }
        .btn-warna-abu:hover {
            background-color: #d1d5db !important;
        }

        /* Warna untuk tombol input "Pilih File" bawaan browser */
        .input-file-kustom::file-selector-button {
            background-color: #eff6ff !important;
            color: #1e3a8a !important;
            border: none;
        }
        .input-file-kustom::file-selector-button:hover {
            background-color: #dbeafe !important;
        }
    </style>

    <header>
        <h2 class="text-lg font-extrabold text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Perbarui foto profil, nama, dan detail informasi akun Anda.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profile-update-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Bagian Foto Profil (Avatar) -->
        <div class="flex items-center gap-6">
            <div class="shrink-0 relative group cursor-pointer" onclick="document.getElementById('avatar').click()">
                @if(Auth::user()->avatar)
                    <img class="h-24 w-24 object-cover rounded-full shadow-md border-2" style="border-color: #1e3a8a;" src="{{ url('berkas/' . Auth::user()->avatar) }}" alt="Foto Profil" />
                @else
                    <div class="h-24 w-24 rounded-full text-white flex items-center justify-center font-bold text-3xl shadow-md border-2" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
                <!-- Overlay Hover untuk ganti foto -->
                <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-white text-xs font-bold">Ubah Foto</span>
                </div>
            </div>
            
            <div>
                <!-- Input file disembunyikan secara visual -->
                <x-input-label for="avatar" value="{{ __('Ubah Foto Profil') }}" />
                <input type="file" id="avatar" name="avatar" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:text-sm file:font-bold transition input-file-kustom" onchange="openCropper(event)"/>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                <p class="text-xs text-gray-400 mt-1">Pilih gambar, otomatis potong 1:1, dan simpan.</p>
            </div>
        </div>

        <!-- Grid 2 Kolom untuk Inputan Teks -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" value="{{ __('Nama Lengkap') }}" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-50" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="{{ __('Alamat Email') }}" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-50" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="jabatan" value="{{ __('Jabatan / Posisi') }}" />
                <x-text-input id="jabatan" name="jabatan" type="text" class="mt-1 block w-full bg-gray-50" :value="old('jabatan', $user->jabatan)" placeholder="Contoh: Guru Matematika" />
                <x-input-error class="mt-2" :messages="$errors->get('jabatan')" />
            </div>

            <div>
                <x-input-label for="no_hp" value="{{ __('Nomor Telepon / WhatsApp') }}" />
                <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full bg-gray-50" :value="old('no_hp', $user->no_hp)" placeholder="Contoh: 08123456789" />
                <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
            <x-primary-button class="btn-warna-utama transition">{{ __('Simpan Profil') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-bold"
                >{{ __('✅ Tersimpan.') }}</p>
            @endif
        </div>
    </form>

    <!-- MODAL POP-UP UNTUK CROPPER -->
    <div id="cropperModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden bg-black bg-opacity-80 backdrop-blur-sm p-4 sm:p-6 overflow-hidden">
        
        <!-- Wadah Modal -->
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl flex flex-col max-h-[95vh] overflow-hidden">
            
            <!-- Bagian Atas (Header) -->
            <div class="p-5 border-b border-gray-100 shrink-0 bg-white">
                <h3 class="text-xl font-extrabold text-gray-800">Sesuaikan Foto</h3>
                <p class="text-xs text-gray-500 mt-1">Geser atau cubit untuk memperbesar gambar agar pas dengan area kotak.</p>
            </div>
            
            <!-- Bagian Tengah (Wadah Gambar) -->
            <div class="p-4 sm:p-5 flex-1 overflow-y-auto bg-gray-50">
                <div class="w-full bg-gray-900 rounded-xl overflow-hidden shadow-inner relative flex items-center justify-center" style="height: 45vh; min-height: 250px; max-height: 400px;">
                    <img id="imageToCrop" src="" class="block max-w-full max-h-full" alt="Picture">
                </div>
            </div>
            
            <!-- Bagian Bawah (Tombol Action) -->
            <div class="p-5 border-t border-gray-100 shrink-0 flex justify-end gap-3 bg-white">
                <button type="button" onclick="closeCropper()" class="px-5 py-2.5 rounded-lg font-bold transition btn-warna-abu">Batal</button>
                <button type="button" onclick="cropAndSubmit()" class="px-5 py-2.5 rounded-lg font-bold shadow-md transition flex items-center gap-2 btn-warna-utama">
                    <span id="cropBtnText">✂️ Crop & Simpan</span>
                </button>
            </div>

        </div>
    </div>

    <!-- SCRIPT CROPPER & SUBMIT OTOMATIS -->
    <script>
        let cropper;
        const cropperModal = document.getElementById('cropperModal');
        const imageToCrop = document.getElementById('imageToCrop');
        const avatarInput = document.getElementById('avatar');
        const form = document.getElementById('profile-update-form');
        const cropBtnText = document.getElementById('cropBtnText');

        function openCropper(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imageToCrop.src = e.target.result;
                    cropperModal.classList.remove('hidden');
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                };
                reader.readAsDataURL(files[0]);
            }
        }

        function closeCropper() {
            cropperModal.classList.add('hidden');
            avatarInput.value = ''; 
            if (cropper) cropper.destroy();
        }

        function cropAndSubmit() {
            if (!cropper) return;
            
            cropBtnText.innerHTML = '⏳ Memproses...';

            cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                
                let file = new File([blob], "avatar_cropped.jpg", { type: "image/jpeg", lastModified: new Date().getTime() });
                let container = new DataTransfer();
                container.items.add(file);
                avatarInput.files = container.files;
                
                cropperModal.classList.add('hidden');
                form.submit();
                
            }, 'image/jpeg', 0.9);
        }
    </script>
</section>