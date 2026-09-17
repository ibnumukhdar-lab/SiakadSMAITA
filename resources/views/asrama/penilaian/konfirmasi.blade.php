<x-app-layout>
    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">⚖️ Putusan Final Sidak Asrama {{ ucfirst($kategori) }}</h3>
                    <p class="text-[13px] font-bold text-blue-900 mt-1 mb-0">{{ \App\Http\Controllers\AsramaPenilaianController::labelSesi($sesi) }} · jam penilaian {{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi($sesi) }}</p>
                    <p class="text-sm text-slate-500 mt-0.5">Tentukan pemenang mutlak dari kandidat di bawah, lalu sertakan bukti foto untuk TV Display Lobi.
                        Kamar terkotor hanya sah bila nilainya di bawah {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}%.</p>
                </div>
            </div>

            <!-- ================= ALERT ERROR ================= -->
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3.5 mb-5 rounded-xl text-sm font-semibold">
                    ❌ {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3.5 mb-5 rounded-xl text-sm font-semibold">
                    <p class="mb-2">Terjadi kesalahan pada input Anda:</p>
                    <ul class="list-disc list-inside ml-4 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tambahkan ID form-finalisasi agar lebih mudah dieksekusi Javascript -->
            <form id="form-finalisasi" action="{{ route('asrama.penilaian.finalisasi') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kategori" value="{{ $kategori }}">
                <input type="hidden" name="sesi" value="{{ $sesi }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">

                    <!-- ================= KANDIDAT TERBERSIH ================= -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm p-5 sm:p-6">
                        <h4 class="font-extrabold text-emerald-800 text-base mb-4 flex items-center gap-2">
                            🏆 Kandidat Terbersih
                            <span class="bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded-md text-xs font-bold">Skor: {{ $maxSkor }}</span>
                        </h4>

                        <div class="space-y-2.5">
                            @foreach($kandidatBersih as $k)
                                <label class="flex items-center gap-4 p-4 bg-white border border-emerald-200 rounded-xl cursor-pointer hover:bg-emerald-100/60 hover:border-emerald-300 transition shadow-sm">
                                    <input type="radio" name="kamar_terbersih_id" value="{{ $k->kamar_id }}" class="w-5 h-5 accent-emerald-600" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="font-bold text-slate-800">{{ $k->kamar->nama_kamar }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-5 pt-4 border-t border-emerald-200">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-800 mb-1.5">📸 Upload Bukti Foto (Opsional)</label>
                            <input type="file" name="foto_terbersih" accept="image/*" data-kompres class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer bg-white rounded-xl border border-emerald-200">
                            <span data-info-kompres class="block text-[11px] font-semibold text-emerald-700 mt-1.5"></span>
                            <p class="text-[11px] font-semibold text-emerald-700 mt-2">* Foto ini akan langsung tayang di TV Display. Foto besar dari HP dikecilkan otomatis sebelum dikirim.</p>
                        </div>
                    </div>

                    <!-- ================= KANDIDAT TERKOTOR ================= -->
                    <div class="bg-rose-50 border border-rose-200 rounded-2xl shadow-sm p-5 sm:p-6">
                        @if($adaTerkotor)
                            <h4 class="font-extrabold text-rose-800 text-base mb-2 flex items-center gap-2">
                                ⚠️ Perhatian Ekstra
                                <span class="bg-rose-200 text-rose-800 px-2 py-0.5 rounded-md text-xs font-bold">Terendah: {{ $minSkor }} / 25</span>
                            </h4>
                            <p class="text-[12px] font-semibold text-rose-700 mb-3.5">
                                Hanya kamar bernilai <b>di bawah {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}%</b>
                                (kurang dari {{ $batas }} poin) yang bisa ditandai terkotor. Kamar terpilih menerima poin −1 untuk seluruh penghuninya.
                            </p>

                            <div class="space-y-2.5">
                                @foreach($kandidatKotor as $k)
                                    <label class="flex items-center gap-4 p-4 bg-white border border-rose-200 rounded-xl cursor-pointer hover:bg-rose-100/60 hover:border-rose-300 transition shadow-sm">
                                        <input type="radio" name="kamar_terkotor_id" value="{{ $k->kamar_id }}" class="w-5 h-5 accent-rose-500" {{ $loop->first ? 'checked' : '' }}>
                                        <span class="font-bold text-slate-800">{{ $k->kamar->nama_kamar }}</span>
                                        <span class="ms-auto text-[12px] font-black text-rose-700 whitespace-nowrap">{{ $k->total_skor }}/25 · {{ \App\Http\Controllers\AsramaPenilaianController::persenSkor($k->total_skor) }}%</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <h4 class="font-extrabold text-emerald-800 text-base mb-2">✅ Semua Kamar Dinilai Bersih</h4>
                            <div class="rounded-xl bg-white border border-emerald-200 p-4 text-[13px] text-emerald-800 font-semibold">
                                Semua kamar bernilai <b>{{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}% ke atas</b>, jadi <b>tidak ada kamar terkotor</b> dan <b>tidak ada poin −1</b> pada inspeksi ini.
                            </div>

                            <div class="mt-4">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700 mb-1.5">⚠️ Perlu Diperhatikan (nilai terbawah)</div>
                                <div class="space-y-2">
                                    @foreach($terbawah as $t)
                                        <div class="flex items-center justify-between gap-3 bg-white border border-amber-200 rounded-xl px-4 py-3">
                                            <span class="font-bold text-slate-800">{{ $t->kamar->nama_kamar }}</span>
                                            <span class="text-[12px] font-black text-amber-700 whitespace-nowrap">{{ $t->total_skor }}/25 · {{ \App\Http\Controllers\AsramaPenilaianController::persenSkor($t->total_skor) }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-[11px] font-semibold text-amber-700 mt-2">Kamar ini masuk daftar perlu diperhatikan (tanpa poin pengurangan) dan bisa kamu beri catatan di bawah.</p>
                            </div>
                        @endif

                        <div class="mt-5 pt-4 border-t border-rose-200">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-rose-800 mb-1.5">📸 {{ $adaTerkotor ? 'Upload Bukti Foto Kamar Terkotor (Opsional)' : 'Upload Foto Kamar Perlu Diperhatikan (Opsional)' }}</label>
                            <input type="file" name="foto_terkotor" accept="image/*" data-kompres class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-rose-500 file:text-white hover:file:bg-rose-600 cursor-pointer bg-white rounded-xl border border-rose-200">
                            <span data-info-kompres class="block text-[11px] font-semibold text-rose-700 mt-1.5"></span>
                            <p class="text-[11px] font-semibold text-rose-700 mt-2">* Foto peringatan untuk tayang di TV Display. Foto besar dari HP dikecilkan otomatis sebelum dikirim.</p>
                        </div>
                    </div>

                </div>

                <!-- ================= CATATAN INSPEKTOR ================= -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                    <label for="catatan_inspektor" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">📝 Catatan Inspektor (opsional)</label>
                    <textarea id="catatan_inspektor" name="catatan_inspektor" rows="3" maxlength="1000"
                              placeholder="Contoh: lemari kamar Utsman belum rapi dan lantai depan masih berdebu — mohon diperbaiki besok."
                              class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">{{ old('catatan_inspektor') }}</textarea>
                    <p class="text-[11px] font-semibold text-slate-400 mt-1.5">Tampil di halaman Histori Inspeksi bersama kamar yang perlu diperhatikan.</p>
                </div>

                <!-- ================= TOMBOL EKSEKUSI ================= -->
                <div class="flex flex-col sm:flex-row items-center justify-between border-t border-slate-200 pt-6 mt-2 gap-4">
                    <a href="{{ route('asrama.penilaian.hariIni') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:w-auto">
                        ⬅️ Kembali
                    </a>

                    <button type="button" onclick="confirmFinalize()" class="inline-flex items-center justify-center gap-2 h-12 px-6 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-sm transition whitespace-nowrap w-full sm:w-auto">
                        🔒 Kunci Finalisasi &amp; Umumkan
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Script Konfirmasi SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmFinalize() {
            Swal.fire({
                title: 'Kunci Hasil Sidak?',
                text: "Data akan disimpan permanen, poin akan disuntikkan ke Student Root, dan foto akan ditayangkan di TV Lobi. Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Kunci & Umumkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Data...',
                        html: 'Menganalisis foto dan menyuntikkan poin. Mohon tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    // Metode ini 100% lebih dijamin berjalan daripada memanggil .click() pada tombol tersembunyi
                    document.getElementById('form-finalisasi').submit();
                }
            })
        }
    </script>

    @include('partials.kompres-foto')
</x-app-layout>
