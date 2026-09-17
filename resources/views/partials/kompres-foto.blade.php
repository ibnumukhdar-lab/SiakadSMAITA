{{-- Kompresi foto di sisi browser: perkecil foto HP sebelum dikirim (hemat kuota & hindari gagal karena ukuran).
     Pakai: tambahkan atribut data-kompres pada <input type="file">, lalu @include('partials.kompres-foto') sekali per halaman. --}}
<script>
document.addEventListener('change', function (e) {
    const input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
    if (!input.hasAttribute('data-kompres')) return;

    const berkas = input.files && input.files[0];
    if (!berkas) return;
    if (!berkas.type || !berkas.type.startsWith('image/')) return;      // PDF/dokumen: kirim apa adanya
    if (berkas.type === 'image/heic' || berkas.type === 'image/heif') return; // browser tak bisa mengecilkan HEIC
    if (berkas.size < 600 * 1024) return;                                // sudah kecil: kirim apa adanya

    const wadah = input.closest('div, form, label');
    const info = wadah ? wadah.querySelector('[data-info-kompres]') : null;
    const tulisInfo = (teks) => { if (info) info.textContent = teks; };

    tulisInfo('⏳ Memperkecil foto...');
    const url = URL.createObjectURL(berkas);
    const img = new Image();

    img.onload = function () {
        try {
            const maks = 1600;
            const skala = Math.min(1, maks / Math.max(img.width, img.height));
            const cv = document.createElement('canvas');
            cv.width = Math.max(1, Math.round(img.width * skala));
            cv.height = Math.max(1, Math.round(img.height * skala));
            cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);

            cv.toBlob(function (blob) {
                URL.revokeObjectURL(url);
                if (!blob || blob.size >= berkas.size) { tulisInfo(''); return; }

                const namaBaru = (berkas.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
                const berkasBaru = new File([blob], namaBaru, { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(berkasBaru);
                input.files = dt.files;

                tulisInfo('✅ Foto diperkecil otomatis: ' + Math.round(berkas.size / 1024) + ' KB → ' + Math.round(blob.size / 1024) + ' KB');
            }, 'image/jpeg', 0.82);
        } catch (err) {
            URL.revokeObjectURL(url);
            tulisInfo('');
        }
    };

    img.onerror = function () {
        URL.revokeObjectURL(url);
        tulisInfo('ℹ️ Foto ini tidak bisa diperkecil di HP ini — akan dikirim apa adanya.');
    };

    img.src = url;
}, true);
</script>
