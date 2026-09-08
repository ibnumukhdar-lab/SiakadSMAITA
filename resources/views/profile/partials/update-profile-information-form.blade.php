<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <!-- Memanggil pustaka Cropper.js dari CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <!-- Judul kartu -->
    <div class="px-5 py-4 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
        <div>
            <h4 class="text-[15px] font-bold text-slate-800">Informasi Profil</h4>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui foto profil, nama, dan detail informasi akun Anda.</p>
        </div>
    </div>

    <div class="p-5 sm:p-6">
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form id="profile-update-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('patch')

            <!-- Bagian Foto Profil (Avatar) -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <div class="shrink-0 relative group cursor-pointer" onclick="document.getElementById('avatar').click()">
                    @if(Auth::user()->avatar)
                        <img class="h-24 w-24 object-cover rounded-full shadow-sm border-2 border-blue-900" src="{{ url('berkas/' . Auth::user()->avatar) }}" alt="Foto Profil" />
                    @else
                        <div class="h-24 w-24 rounded-full text-white flex items-center justify-center font-bold text-3xl shadow-sm bg-blue-900 border-2 border-blue-900">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                    <!-- Overlay Hover untuk ganti foto -->
                    <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="text-white text-xs font-bold">Ubah Foto</span>
                    </div>
                </div>

                <div class="w-full flex-1 min-w-0">
                    <label for="avatar" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Ubah Foto Profil</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" onchange="openCropper(event)"
                        class="block w-full text-sm text-slate-500 rounded-lg border border-slate-300 bg-white px-3 py-2.5 transition file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-blue-900 hover:file:bg-blue-100 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    <p class="text-xs text-slate-400 mt-1">Pilih gambar, otomatis potong 1:1, dan simpan.</p>
                </div>
            </div>

            <!-- Grid 2 Kolom untuk Inputan Teks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                        class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Alamat Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                        class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <label for="jabatan" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Jabatan / Posisi</label>
                    <input id="jabatan" name="jabatan" type="text" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Guru Matematika"
                        class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    <x-input-error class="mt-2" :messages="$errors->get('jabatan')" />
                </div>

                <div>
                    <label for="no_hp" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 08123456789"
                        class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                    <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-5 border-t border-slate-100">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan Profil</button>

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
    </div>

    <!-- MODAL POP-UP UNTUK CROPPER -->
    <div id="cropperModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden bg-black bg-opacity-80 backdrop-blur-sm p-4 sm:p-6 overflow-hidden">

        <!-- Wadah Modal -->
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl flex flex-col max-h-[95vh] overflow-hidden">

            <!-- Bagian Atas (Header) -->
            <div class="p-5 border-b border-slate-100 shrink-0 bg-white">
                <h3 class="text-lg font-extrabold text-slate-800">Sesuaikan Foto</h3>
                <p class="text-xs text-slate-500 mt-1">Geser atau cubit untuk memperbesar gambar agar pas dengan area kotak.</p>
            </div>

            <!-- Bagian Tengah (Wadah Gambar) -->
            <div class="p-4 sm:p-5 flex-1 overflow-y-auto bg-slate-50">
                <div class="w-full bg-slate-900 rounded-xl overflow-hidden shadow-inner relative flex items-center justify-center" style="height: 45vh; min-height: 250px; max-height: 400px;">
                    <img id="imageToCrop" src="" class="block max-w-full max-h-full" alt="Picture">
                </div>
            </div>

            <!-- Bagian Bawah (Tombol Action) -->
            <div class="p-5 border-t border-slate-100 shrink-0 flex justify-end gap-3 bg-white">
                <button type="button" onclick="closeCropper()" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</button>
                <button type="button" onclick="cropAndSubmit()" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    <span id="cropBtnText">✂️ Crop &amp; Simpan</span>
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
