@php
    // $fotoAwal = path foto yang sudah tersimpan (mis. 'foto_siswa/xxx.jpg') atau null
    $fotoAwal = $fotoAwal ?? null;
    $urlAwal = $fotoAwal ? url('berkas/' . $fotoAwal) : null;
@endphp

<div x-data="pengunggahFoto({{ \Illuminate\Support\Js::from($urlAwal) }})">
    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Foto Siswa</label>

    <div class="flex items-start gap-3">
        {{-- Pratinjau --}}
        <div class="w-[68px] h-[90px] rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
            <template x-if="tampil">
                <img :src="tampil" alt="Pratinjau foto" class="w-full h-full object-cover">
            </template>
            <span x-show="!tampil" class="text-2xl text-slate-300">👤</span>
        </div>

        <div class="min-w-0 flex-1">
            {{-- Input sumber (tidak dikirim langsung) --}}
            <input type="file" accept="image/*" capture="environment" x-ref="kamera" @change="pilih($event, 'kamera')" class="hidden">
            <input type="file" accept="image/*" x-ref="galeri" @change="pilih($event, 'galeri')" class="hidden">

            {{-- Input yang benar-benar dikirim --}}
            <input type="file" name="foto" x-ref="kirim" class="hidden">

            <div class="flex flex-wrap gap-2">
                <button type="button" @click="$refs.kamera.click()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">
                    📷 Ambil dari kamera
                </button>
                <button type="button" @click="$refs.galeri.click()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">
                    🖼️ Pilih dari berkas
                </button>
                <button type="button" x-show="tampil" @click="hapus()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-rose-200 bg-white text-rose-700 text-[13px] font-semibold hover:bg-rose-50 transition">
                    ✕ Batalkan
                </button>
            </div>

            <p class="text-[11px] text-slate-400 mt-1.5" x-show="sibuk">Sedang mengecilkan foto…</p>
            <p class="text-[11px] text-slate-500 mt-1.5" x-show="!sibuk && nama" x-text="'Terpilih: ' + nama + ' — ' + ukuran"></p>
            <p class="text-[11px] text-slate-400 mt-1.5" x-show="!sibuk && !nama">
                JPG / PNG / WEBP. Foto otomatis dikecilkan sebelum dikirim (hemat kuota, maks 3 MB).
            </p>
        </div>
    </div>
</div>

@once
    <script>
        // Pengunggah foto siswa: bisa dari kamera HP, otomatis dikecilkan di perangkat.
        function pengunggahFoto(urlAwal) {
            return {
                tampil: urlAwal || '',
                nama: '',
                ukuran: '',
                sibuk: false,

                async pilih(ev, sumber) {
                    const berkas = ev.target.files && ev.target.files[0];
                    if (!berkas) return;

                    // Input lain dikosongkan supaya tidak ada dua sumber sekaligus
                    this.$refs[sumber === 'kamera' ? 'galeri' : 'kamera'].value = '';

                    this.nama = berkas.name;
                    this.ukuran = this.formatUkuran(berkas.size);

                    // Pratinjau cepat dari berkas asli
                    this.tampil = URL.createObjectURL(berkas);

                    this.sibuk = true;
                    let hasil = berkas;
                    try {
                        hasil = await this.kecilkan(berkas);
                        this.ukuran = this.formatUkuran(hasil.size) + ' (dikecilkan)';
                    } catch (e) {
                        hasil = berkas;
                    }
                    this.sibuk = false;

                    // Masukkan hasil ke input yang dikirim form
                    const dt = new DataTransfer();
                    dt.items.add(new File([hasil], 'foto.jpg', { type: hasil.type || 'image/jpeg' }));
                    this.$refs.kirim.files = dt.files;
                },

                hapus() {
                    this.tampil = '';
                    this.nama = '';
                    this.ukuran = '';
                    this.$refs.kirim.value = '';
                    this.$refs.kamera.value = '';
                    this.$refs.galeri.value = '';
                },

                formatUkuran(bita) {
                    if (bita < 1024) return bita + ' B';
                    if (bita < 1024 * 1024) return (bita / 1024).toFixed(0) + ' KB';
                    return (bita / 1024 / 1024).toFixed(2) + ' MB';
                },

                // Kecilkan memakai canvas: sisi terpanjang maks 900px, JPEG mutu 0.82
                kecilkan(berkas, maks = 900, mutu = 0.82) {
                    return new Promise((selesai, gagal) => {
                        const img = new Image();
                        const url = URL.createObjectURL(berkas);
                        img.onload = () => {
                            URL.revokeObjectURL(url);
                            let lebar = img.naturalWidth;
                            let tinggi = img.naturalHeight;
                            const skala = Math.min(1, maks / Math.max(lebar, tinggi));
                            lebar = Math.round(lebar * skala);
                            tinggi = Math.round(tinggi * skala);

                            const kanvas = document.createElement('canvas');
                            kanvas.width = lebar;
                            kanvas.height = tinggi;
                            kanvas.getContext('2d').drawImage(img, 0, 0, lebar, tinggi);

                            kanvas.toBlob(blob => {
                                if (!blob) { gagal(new Error('gagal')); return; }
                                selesai(blob);
                            }, 'image/jpeg', mutu);
                        };
                        img.onerror = () => { URL.revokeObjectURL(url); gagal(new Error('bukan gambar')); };
                        img.src = url;
                    });
                },
            };
        }
    </script>
@endonce
