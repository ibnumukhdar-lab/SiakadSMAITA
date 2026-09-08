<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Induk Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 1, prestasi: {{ json_encode($siswa->prestasi ?? []) }}, pelanggaran: {{ json_encode($siswa->pelanggaran ?? []) }} }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 shadow sm:rounded-lg">
                
                <div class="flex border-b-2 border-gray-200 mb-6 overflow-x-auto">
                    <button @click="tab = 1" :class="{ 'border-blue-600 text-blue-600': tab === 1 }" class="py-3 px-6 border-b-2 border-transparent font-bold focus:outline-none whitespace-nowrap">👤 1. Identitas & Akademik</button>
                    <button @click="tab = 2" :class="{ 'border-blue-600 text-blue-600': tab === 2 }" class="py-3 px-6 border-b-2 border-transparent font-bold focus:outline-none whitespace-nowrap">👨‍👩‍👦 2. Keluarga & Wali</button>
                    <button @click="tab = 3" :class="{ 'border-blue-600 text-blue-600': tab === 3 }" class="py-3 px-6 border-b-2 border-transparent font-bold focus:outline-none whitespace-nowrap">🏫 3. Domisili & Dapodik</button>
                    <button @click="tab = 4" :class="{ 'border-blue-600 text-blue-600': tab === 4 }" class="py-3 px-6 border-b-2 border-transparent font-bold focus:outline-none whitespace-nowrap">🏆 4. Logbook</button>
                </div>

                <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div x-show="tab === 1" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold mb-1">Foto Siswa</label>
                            <input type="file" name="foto" accept="image/*" class="border p-2 w-full rounded bg-gray-50">
                        </div>
                        <div><label class="block text-sm font-bold mb-1">Nama Lengkap *</label><input type="text" name="nama_lengkap" value="{{ $siswa->nama_lengkap }}" class="w-full border rounded p-2" required></div>
                        <div><label class="block text-sm font-bold mb-1">NISN *</label><input type="text" name="nisn" value="{{ $siswa->nisn }}" class="w-full border rounded p-2" required></div>
                        <div><label class="block text-sm font-bold mb-1">NIS Lokal</label><input type="text" name="nis" value="{{ $siswa->nis }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Tempat, Tgl Lahir</label><input type="text" name="ttl" value="{{ $siswa->ttl }}" class="w-full border rounded p-2"></div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Jenis Kelamin</label>
                            <select name="jk" class="w-full border rounded p-2">
                                <option value="Laki-laki" {{ $siswa->jk == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ $siswa->jk == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Status Siswa</label>
                            <select name="status" class="w-full border rounded p-2">
                                <option value="Aktif" {{ $siswa->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Alumni" {{ $siswa->status == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                <option value="Pindah" {{ $siswa->status == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Kelas</label>
                            <select name="kelas" class="w-full border rounded p-2">
                                <option value="X" {{ $siswa->kelas == 'X' ? 'selected' : '' }}>Kelas X</option>
                                <option value="XI" {{ $siswa->kelas == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                <option value="XII" {{ $siswa->kelas == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                                <option value="Lulus" {{ $siswa->kelas == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-bold mb-1">Tahun Ajaran Saat Ini</label><input type="text" name="tahun_ajaran" value="{{ $siswa->tahun_ajaran }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Tahun Masuk</label><input type="text" name="thn_masuk" value="{{ $siswa->thn_masuk }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Tahun Lulus</label><input type="text" name="thn_lulus" value="{{ $siswa->thn_lulus }}" class="w-full border rounded p-2"></div>
                        
                        <div class="md:col-span-2 text-right mt-4"><button type="button" @click="tab = 2" class="bg-gray-800 text-white px-4 py-2 rounded">Selanjutnya &raquo;</button></div>
                    </div>

                    <div x-show="tab === 2" style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 font-bold text-blue-700 border-b pb-2">Data Ayah</div>
                        <div><label class="block text-sm font-bold mb-1">Nama Ayah</label><input type="text" name="nama_ayah" value="{{ $siswa->nama_ayah }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Pekerjaan Ayah</label><input type="text" name="pekerjaan_ayah" value="{{ $siswa->pekerjaan_ayah }}" class="w-full border rounded p-2"></div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Status Ayah</label>
                            <select name="status_ayah" class="w-full border rounded p-2">
                                <option value="Masih Hidup" {{ $siswa->status_ayah == 'Masih Hidup' ? 'selected' : '' }}>Masih Hidup</option>
                                <option value="Meninggal Dunia" {{ $siswa->status_ayah == 'Meninggal Dunia' ? 'selected' : '' }}>Meninggal Dunia</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2 font-bold text-blue-700 border-b pb-2 mt-4">Data Ibu</div>
                        <div><label class="block text-sm font-bold mb-1">Nama Ibu</label><input type="text" name="nama_ibu" value="{{ $siswa->nama_ibu }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Pekerjaan Ibu</label><input type="text" name="pekerjaan_ibu" value="{{ $siswa->pekerjaan_ibu }}" class="w-full border rounded p-2"></div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Status Ibu</label>
                            <select name="status_ibu" class="w-full border rounded p-2">
                                <option value="Masih Hidup" {{ $siswa->status_ibu == 'Masih Hidup' ? 'selected' : '' }}>Masih Hidup</option>
                                <option value="Meninggal Dunia" {{ $siswa->status_ibu == 'Meninggal Dunia' ? 'selected' : '' }}>Meninggal Dunia</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 font-bold text-blue-700 border-b pb-2 mt-4">Data Wali (Jika Ada)</div>
                        <div><label class="block text-sm font-bold mb-1">Nama Wali</label><input type="text" name="nama_wali" value="{{ $siswa->nama_wali }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Pekerjaan Wali</label><input type="text" name="pekerjaan_wali" value="{{ $siswa->pekerjaan_wali }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">No HP Wali</label><input type="text" name="hp_wali" value="{{ $siswa->hp_wali }}" class="w-full border rounded p-2"></div>

                        <div class="md:col-span-2 flex justify-between mt-4">
                            <button type="button" @click="tab = 1" class="bg-gray-300 px-4 py-2 rounded">&laquo; Kembali</button>
                            <button type="button" @click="tab = 3" class="bg-gray-800 text-white px-4 py-2 rounded">Selanjutnya &raquo;</button>
                        </div>
                    </div>

                    <div x-show="tab === 3" style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-bold mb-1">No HP Orang Tua/Utama</label><input type="text" name="hp_ortu" value="{{ $siswa->hp_ortu }}" class="w-full border rounded p-2"></div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Tinggal Bersama</label>
                            <select name="tinggal_bersama" class="w-full border rounded p-2">
                                <option value="Orang Tua" {{ $siswa->tinggal_bersama == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                                <option value="Wali" {{ $siswa->tinggal_bersama == 'Wali' ? 'selected' : '' }}>Wali</option>
                                <option value="Asrama" {{ $siswa->tinggal_bersama == 'Asrama' ? 'selected' : '' }}>Asrama</option>
                                <option value="Kos" {{ $siswa->tinggal_bersama == 'Kos' ? 'selected' : '' }}>Kos</option>
                            </select>
                        </div>
                        <div class="md:col-span-2"><label class="block text-sm font-bold mb-1">Alamat Lengkap</label><textarea name="alamat" rows="2" class="w-full border rounded p-2">{{ $siswa->alamat }}</textarea></div>
                        <div><label class="block text-sm font-bold mb-1">Jarak ke Sekolah (Km)</label><input type="text" name="jarak" value="{{ $siswa->jarak }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Transportasi</label><input type="text" name="transportasi" value="{{ $siswa->transportasi }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">No. KIP/PKH (Kesejahteraan)</label><input type="text" name="kesejahteraan" value="{{ $siswa->kesejahteraan }}" class="w-full border rounded p-2"></div>
                        <div><label class="block text-sm font-bold mb-1">Asal Sekolah</label><input type="text" name="asal_sekolah" value="{{ $siswa->asal_sekolah }}" class="w-full border rounded p-2"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-bold mb-1">Riwayat Penyakit</label><input type="text" name="penyakit" value="{{ $siswa->penyakit }}" class="w-full border rounded p-2"></div>

                        <div class="md:col-span-2 flex justify-between mt-4">
                            <button type="button" @click="tab = 2" class="bg-gray-300 px-4 py-2 rounded">&laquo; Kembali</button>
                            <button type="button" @click="tab = 4" class="bg-gray-800 text-white px-4 py-2 rounded">Selanjutnya &raquo;</button>
                        </div>
                    </div>

                    <div x-show="tab === 4" style="display: none;">
                        <div class="mb-6 p-4 border-l-4 border-green-500 bg-green-50">
                            <h3 class="font-bold text-green-700 mb-2">🏆 Edit Prestasi</h3>
                            <template x-for="(p, index) in prestasi" :key="index">
                                <div class="flex gap-2 mb-2">
                                    <input type="text" x-model="p.tgl" :name="`prestasi[${index}][tgl]`" placeholder="Tahun/Tgl" class="border rounded p-2 w-1/4">
                                    <input type="text" x-model="p.tingkat" :name="`prestasi[${index}][tingkat]`" placeholder="Tingkat" class="border rounded p-2 w-1/4">
                                    <input type="text" x-model="p.nama" :name="`prestasi[${index}][nama]`" placeholder="Nama Lomba" class="border rounded p-2 w-2/4">
                                    <button type="button" @click="prestasi.splice(index, 1)" class="bg-red-500 text-white px-3 rounded font-bold">X</button>
                                </div>
                            </template>
                            <button type="button" @click="prestasi.push({tgl: '', tingkat: '', nama: ''})" class="bg-green-200 text-green-800 px-4 py-2 rounded font-bold text-sm">+ Tambah Baris</button>
                        </div>

                        <div class="mb-6 p-4 border-l-4 border-red-500 bg-red-50">
                            <h3 class="font-bold text-red-700 mb-2">⚠️ Edit Catatan Merah</h3>
                            <template x-for="(pl, index) in pelanggaran" :key="index">
                                <div class="flex gap-2 mb-2">
                                    <input type="text" x-model="pl.tgl" :name="`pelanggaran[${index}][tgl]`" placeholder="Tgl Kejadian" class="border rounded p-2 w-1/4">
                                    <input type="text" x-model="pl.kategori" :name="`pelanggaran[${index}][kategori]`" placeholder="Kategori" class="border rounded p-2 w-1/4">
                                    <input type="text" x-model="pl.kasus" :name="`pelanggaran[${index}][kasus]`" placeholder="Kasus" class="border rounded p-2 w-1/4">
                                    <input type="text" x-model="pl.tindakan" :name="`pelanggaran[${index}][tindakan]`" placeholder="Sanksi" class="border rounded p-2 w-1/4">
                                    <button type="button" @click="pelanggaran.splice(index, 1)" class="bg-red-500 text-white px-3 rounded font-bold">X</button>
                                </div>
                            </template>
                            <button type="button" @click="pelanggaran.push({tgl: '', kategori: '', kasus: '', tindakan: ''})" class="bg-red-200 text-red-800 px-4 py-2 rounded font-bold text-sm">+ Tambah Baris</button>
                        </div>

                        <div class="flex justify-between mt-6 pt-4 border-t">
                            <button type="button" @click="tab = 3" class="bg-gray-300 px-4 py-2 rounded">&laquo; Kembali</button>
                            <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded shadow hover:bg-blue-700">💾 Simpan Perubahan Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>