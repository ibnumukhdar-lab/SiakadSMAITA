<x-app-layout>
    <div class="py-8" x-data="{ tab: 1, prestasi: [], pelanggaran: [] }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Judul halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Tambah Data Induk Siswa</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Lengkapi data induk siswa baru SMA IT Arafah</p>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 mb-4 rounded shadow-sm text-sm">
                    <p class="font-bold mb-1">⚠️ Data belum bisa disimpan:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                {{-- Navigasi Tab --}}
                <div class="flex overflow-x-auto border-b border-slate-200">
                    <button @click="tab = 1" :class="{ 'border-blue-700 text-blue-700': tab === 1, 'border-transparent text-slate-400 hover:text-slate-600': tab !== 1 }" class="-mb-px py-3.5 px-5 border-b-2 font-bold text-sm focus:outline-none whitespace-nowrap transition">👤 1. Identitas & Akademik</button>
                    <button @click="tab = 2" :class="{ 'border-blue-700 text-blue-700': tab === 2, 'border-transparent text-slate-400 hover:text-slate-600': tab !== 2 }" class="-mb-px py-3.5 px-5 border-b-2 font-bold text-sm focus:outline-none whitespace-nowrap transition">👨‍👩‍👦 2. Keluarga & Wali</button>
                    <button @click="tab = 3" :class="{ 'border-blue-700 text-blue-700': tab === 3, 'border-transparent text-slate-400 hover:text-slate-600': tab !== 3 }" class="-mb-px py-3.5 px-5 border-b-2 font-bold text-sm focus:outline-none whitespace-nowrap transition">🏫 3. Domisili & Dapodik</button>
                    <button @click="tab = 4" :class="{ 'border-blue-700 text-blue-700': tab === 4, 'border-transparent text-slate-400 hover:text-slate-600': tab !== 4 }" class="-mb-px py-3.5 px-5 border-b-2 font-bold text-sm focus:outline-none whitespace-nowrap transition">🏆 4. Logbook</button>
                </div>

                <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6">
                    @csrf

                    <!-- TAB 1: Identitas & Akademik -->
                    <div x-show="tab === 1" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            @include('siswa.partials.foto-unggah', ['fotoAwal' => null])
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">NISN *</label>
                            <input type="text" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" maxlength="20" placeholder="10 digit angka" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            <p class="text-[11px] text-slate-400 mt-1">Wajib 10 digit angka. Spasi / karakter tak terlihat otomatis dibuang.</p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">NIS Lokal</label>
                            <input type="text" name="nis" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tempat, Tgl Lahir</label>
                            <input type="text" name="ttl" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Contoh: Jakarta, 12 Mei 2008">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Jenis Kelamin</label>
                            <select name="jk" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status Siswa</label>
                            <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="Aktif">Aktif</option>
                                <option value="Alumni">Alumni</option>
                                <option value="Pindah">Pindah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kelas</label>
                            <select name="kelas" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                                <option value="Lulus">Lulus</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tahun Ajaran Saat Ini</label>
                            <input type="text" name="tahun_ajaran" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Contoh: 2026/2027">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tahun Masuk</label>
                            <input type="text" name="thn_masuk" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tahun Lulus</label>
                            <input type="text" name="thn_lulus" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Jika Alumni">
                        </div>

                        <div class="md:col-span-2 text-right mt-4">
                            <button type="button" @click="tab = 2" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Selanjutnya &raquo;</button>
                        </div>
                    </div>

                    <!-- TAB 2: Keluarga & Wali -->
                    <div x-show="tab === 2" style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 border-b border-slate-200 pb-2 text-[12px] font-bold uppercase tracking-wider text-slate-600">Data Ayah</div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pekerjaan Ayah</label>
                            <input type="text" name="pekerjaan_ayah" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status Ayah</label>
                            <select name="status_ayah" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="Masih Hidup">Masih Hidup</option>
                                <option value="Meninggal Dunia">Meninggal Dunia</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 border-b border-slate-200 pb-2 mt-4 text-[12px] font-bold uppercase tracking-wider text-slate-600">Data Ibu</div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pekerjaan Ibu</label>
                            <input type="text" name="pekerjaan_ibu" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status Ibu</label>
                            <select name="status_ibu" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="Masih Hidup">Masih Hidup</option>
                                <option value="Meninggal Dunia">Meninggal Dunia</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 border-b border-slate-200 pb-2 mt-4 text-[12px] font-bold uppercase tracking-wider text-slate-600">Data Wali (Jika Ada)</div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Wali</label>
                            <input type="text" name="nama_wali" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pekerjaan Wali</label>
                            <input type="text" name="pekerjaan_wali" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">No HP Wali</label>
                            <input type="text" name="hp_wali" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div class="md:col-span-2 flex justify-between gap-3 mt-4">
                            <button type="button" @click="tab = 1" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">&laquo; Kembali</button>
                            <button type="button" @click="tab = 3" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Selanjutnya &raquo;</button>
                        </div>
                    </div>

                    <!-- TAB 3: Domisili & Dapodik -->
                    <div x-show="tab === 3" style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">No HP Orang Tua/Utama</label>
                            <input type="text" name="hp_ortu" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tinggal Bersama</label>
                            <select name="tinggal_bersama" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                <option value="Orang Tua">Orang Tua</option>
                                <option value="Wali">Wali</option>
                                <option value="Asrama">Asrama</option>
                                <option value="Kos">Kos</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"></textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Jarak ke Sekolah (Km)</label>
                            <input type="text" name="jarak" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Contoh: 2.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Transportasi</label>
                            <input type="text" name="transportasi" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">No. KIP/PKH (Kesejahteraan)</label>
                            <input type="text" name="kesejahteraan" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Asal Sekolah</label>
                            <input type="text" name="asal_sekolah" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Riwayat Penyakit</label>
                            <input type="text" name="penyakit" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div class="md:col-span-2 flex justify-between gap-3 mt-4">
                            <button type="button" @click="tab = 2" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">&laquo; Kembali</button>
                            <button type="button" @click="tab = 4" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Selanjutnya &raquo;</button>
                        </div>
                    </div>

                    <!-- TAB 4: Logbook Dinamis -->
                    <div x-show="tab === 4" style="display: none;">
                        <!-- Bagian Prestasi -->
                        <div class="mb-6 p-4 rounded-xl border bg-green-50 border-green-200">
                            <h3 class="font-bold text-green-700 mb-3 text-sm uppercase tracking-wide">🏆 Tambah Prestasi</h3>
                            <template x-for="(p, index) in prestasi" :key="index">
                                <div class="flex gap-2 mb-2 items-center">
                                    <input type="text" x-model="p.tgl" :name="`prestasi[${index}][tgl]`" placeholder="Tahun/Tgl" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <input type="text" x-model="p.tingkat" :name="`prestasi[${index}][tingkat]`" placeholder="Tingkat (Nasional, dll)" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <input type="text" x-model="p.nama" :name="`prestasi[${index}][nama]`" placeholder="Nama Lomba" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-2/4">
                                    <button type="button" @click="prestasi.splice(index, 1)" class="h-10 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-sm transition flex items-center justify-center">X</button>
                                </div>
                            </template>
                            <button type="button" @click="prestasi.push({tgl: '', tingkat: '', nama: ''})" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg bg-green-100 text-green-800 hover:bg-green-200 text-sm font-bold transition">+ Tambah Baris Prestasi</button>
                        </div>

                        <!-- Bagian Pelanggaran -->
                        <div class="mb-6 p-4 rounded-xl border bg-red-50 border-red-200">
                            <h3 class="font-bold text-red-700 mb-3 text-sm uppercase tracking-wide">⚠️ Tambah Catatan Merah</h3>
                            <template x-for="(pl, index) in pelanggaran" :key="index">
                                <div class="flex flex-wrap gap-2 mb-2 items-center">
                                    <input type="text" x-model="pl.tgl" :name="`pelanggaran[${index}][tgl]`" placeholder="Tgl Kejadian" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <input type="text" x-model="pl.kategori" :name="`pelanggaran[${index}][kategori]`" placeholder="Kategori" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <input type="text" x-model="pl.kasus" :name="`pelanggaran[${index}][kasus]`" placeholder="Kasus" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <input type="text" x-model="pl.tindakan" :name="`pelanggaran[${index}][tindakan]`" placeholder="Sanksi" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition w-1/4">
                                    <button type="button" @click="pelanggaran.splice(index, 1)" class="h-10 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-sm transition flex items-center justify-center">X</button>
                                </div>
                            </template>
                            <button type="button" @click="pelanggaran.push({tgl: '', kategori: '', kasus: '', tindakan: ''})" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg bg-red-100 text-red-800 hover:bg-red-200 text-sm font-bold transition">+ Tambah Baris Pelanggaran</button>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-between gap-3 mt-6 pt-5 border-t border-slate-200">
                            <button type="button" @click="tab = 3" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">&laquo; Kembali</button>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">💾 Simpan Data Siswa</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
