<x-app-layout>
    <style>
        .ev-bungkus { max-width: 1080px; margin: 0 auto; padding: 8px 16px 48px; }
        .ev-judul { font-size: 21px; font-weight: 800; color: #0f172a; }
        .ev-anak { font-size: 13.5px; color: #64748b; margin-top: 4px; }
        .ev-kartu { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; margin-top: 16px; }
        .ev-kartu h3 { margin: 0 0 14px; font-size: 15px; font-weight: 800; color: #1e3a8a; }
        .ev-baris { display: grid; gap: 12px; grid-template-columns: repeat(2, 1fr); }
        @media (min-width: 860px) { .ev-baris { grid-template-columns: repeat(4, 1fr); } }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 5px; }
        input[type=text], input[type=number], input[type=date], select { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; color: #0f172a; }
        .ev-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .ev-tbl th { text-align: left; background: #f8fafc; color: #475569; font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; padding: 11px 12px; border-bottom: 1px solid #e2e8f0; }
        .ev-tbl td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; }
        .pil { display: inline-block; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: #f1f5f9; color: #475569; }
        .pil.buka { background: #dcfce7; color: #166534; }
        .pil.tutup { background: #fee2e2; color: #991b1b; }
        .tombol { border: 0; border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-block; }
        .utama { background: #1e3a8a; color: #fff; }
        .kedua { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .bahaya { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .pesan { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; margin-top: 14px; }
        /* pemilih modul kosakata */
        .pilih-modul { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; background: #f8fafc; }
        .pilih-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
        .pilih-bar .hitung { margin-left: auto; font-size: 12.5px; font-weight: 800; color: #1e3a8a; background: #e0ebff; border-radius: 999px; padding: 5px 12px; }
        .daftar-modul { max-height: 260px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .baris-modul { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; cursor: pointer; margin: 0; }
        .baris-modul:last-child { border-bottom: 0; }
        .baris-modul:hover { background: #f8fafc; }
        .baris-modul input[type=checkbox] { width: 17px; height: 17px; flex: none; }
        .baris-modul .nama { font-weight: 600; color: #0f172a; }
        .baris-modul .kosakata { margin-left: auto; color: #64748b; font-size: 12px; white-space: nowrap; }
        .baris-modul.kosong .nama { color: #94a3b8; font-weight: 500; }
        .baris-modul.kosong .kosakata { color: #94a3b8; }
        .baris-modul.belum { background: #fffbeb; }
        .baris-modul.belum .kosakata { color: #92400e; }
        .baris-modul .tanda-kurang { color: #b45309; }
        .catatan-lengkap {
            background: #fffbeb; border: 1px solid #fcd34d; color: #78350f;
            border-radius: 12px; padding: 12px 14px; font-size: 13px; line-height: 1.65; margin-top: 12px;
        }
        .catatan-lengkap strong { color: #92400e; }
        .awas {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            border-radius: 11px; padding: 11px 13px; font-size: 12.5px; line-height: 1.6;
            margin: 8px 0 0; font-weight: 600;
        }
        .otomatis { display: block; margin-top: 10px; font-size: 12.5px; font-weight: 600; color: #334155; }
        .otomatis input { width: 16px; height: 16px; margin-right: 6px; vertical-align: -2px; }
        .bantuan { font-size: 11.5px; color: #94a3b8; line-height: 1.6; margin: 8px 0 0; }
        @media (max-width: 700px) {
            .ev-tbl thead { display: none; }
            .ev-tbl tr { display: block; border-bottom: 1px solid #e2e8f0; padding: 10px 12px; }
            .ev-tbl td { display: flex; justify-content: space-between; gap: 12px; border: 0; padding: 5px 0; }
            .ev-tbl td::before { content: attr(data-l); color: #64748b; font-size: 12px; font-weight: 600; }
        }
    </style>

    <div class="ev-bungkus">
        <h3 class="ev-judul">🐝 Evaluasi BEE Smart (Triwulan &amp; Semester)</h3>
        <p class="ev-anak">
            Siswa mengerjakan lewat <strong>{{ url('/evaluasi-bee') }}</strong> tanpa login — cukup NIS/NISN.
            Buka saklar <em>Aktif</em> saat siap dikerjakan; soal diambil otomatis dari modul BEE yang tanggalnya masuk rentang periode.
        </p>

        <div class="ev-kartu">
            <h3>Bagikan ke siswa</h3>
            <p class="ev-anak">Siswa membuka alamat ini di HP, menulis NIS/NISN, lalu memilih sesi kelasnya. Tanpa login.</p>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:12px">
                <input id="tautanEvaluasi" type="text" readonly value="{{ url('/evaluasi-bee') }}"
                       style="flex:1 1 240px;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 12px;font-size:14px;background:#f8fafc;color:#0f172a">
                <button type="button" class="tombol kedua" id="salinTautan">Salin tautan</button>
                <a class="tombol kedua" target="_blank" rel="noopener"
                   href="https://wa.me/?text={{ rawurlencode('Evaluasi BEE Smart — buka '.url('/evaluasi-bee').' lalu tulis NIS/NISN kamu. Tanpa login.') }}">Bagikan via WhatsApp</a>
            </div>

            <div style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;margin-top:16px">
                <div id="qrEvaluasi" style="background:#fff;padding:8px;border:1px solid #e2e8f0;border-radius:12px;flex:none"></div>
                <div style="max-width:360px;font-size:12.5px;color:#64748b;line-height:1.7">
                    <strong style="color:#1e3a8a">QR untuk dipajang di kelas atau proyektor.</strong><br>
                    Tampilkan di TV kelas / tempel di dinding — siswa memindai dan langsung masuk ke halaman evaluasi.
                    Tautannya juga bisa dipasang di bio Instagram atau grup WhatsApp kelas.
                </div>
            </div>
            <p id="pesanSalin" style="display:none;color:#166534;font-weight:800;font-size:12.5px;margin:10px 0 0">Tautan tersalin ✓</p>
        </div>
        <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
        <script>
            (function () {
                const pesan = document.getElementById('pesanSalin');
                const inp = document.getElementById('tautanEvaluasi');
                function tampil() { pesan.style.display = 'block'; setTimeout(function () { pesan.style.display = 'none'; }, 2500); }
                document.getElementById('salinTautan').addEventListener('click', function () {
                    inp.select(); inp.setSelectionRange(0, 999);
                    if (navigator.clipboard) { navigator.clipboard.writeText(inp.value).then(tampil).catch(tampil); }
                    else { document.execCommand('copy'); tampil(); }
                });
                const kotak = document.getElementById('qrEvaluasi');
                if (window.QRCode && kotak) {
                    new QRCode(kotak, { text: inp.value, width: 148, height: 148, correctLevel: QRCode.CorrectLevel.M });
                }
            })();
        </script>

        @if ($pesan)
            <div class="pesan">{{ $pesan }}</div>
        @endif

        <div class="ev-kartu">
            <h3>{{ $tab ? 'Ubah periode evaluasi' : 'Buat periode evaluasi baru' }}</h3>
            @php $ubah = $tab ? $evaluasi->firstWhere('id', (int) $tab) : null; @endphp
            <form method="post" action="{{ route('bee.evaluasi.simpan') }}">
                @csrf
                @if ($ubah)<input type="hidden" name="id" value="{{ $ubah->id }}">@endif

                <div class="ev-baris">
                    <div style="grid-column: span 2">
                        <label>Judul</label>
                        <input type="text" name="judul" required value="{{ old('judul', $ubah->judul ?? '') }}" placeholder="Evaluasi Triwulan I 2026/2027">
                    </div>
                    <div>
                        <label>Kelas yang diujikan</label>
                        <select name="kelas" required id="pilihKelas">
                            @foreach (array_merge($kelasList, ['SEMUA']) as $k)
                                <option value="{{ $k }}" @selected(old('kelas', $ubah->kelas ?? '') === $k)>{{ $k === 'SEMUA' ? 'Semua kelas' : 'Kelas '.$k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Jenis</label>
                        <select name="jenis">
                            <option value="triwulan" @selected(old('jenis', $ubah->jenis ?? '') === 'triwulan')>Triwulan (3 bulan)</option>
                            <option value="semester" @selected(old('jenis', $ubah->jenis ?? '') === 'semester')>Semesteran</option>
                        </select>
                    </div>
                    <div>
                        <label>Tahun ajaran</label>
                        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $ubah->tahun_ajaran ?? '2026/2027') }}">
                    </div>
                    <div>
                        <label>Mulai</label>
                        <input type="date" name="mulai" required value="{{ old('mulai', isset($ubah) ? $ubah->mulai->format('Y-m-d') : now()->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label>Selesai</label>
                        <input type="date" name="selesai" required value="{{ old('selesai', isset($ubah) ? $ubah->selesai->format('Y-m-d') : now()->addDays(7)->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label>Jumlah soal</label>
                        <input type="number" name="jumlah_soal" min="5" max="100" required value="{{ old('jumlah_soal', $ubah->jumlah_soal ?? 30) }}">
                    </div>
                    <div>
                        <label>Durasi (menit)</label>
                        <input type="number" name="durasi_menit" min="5" max="180" required value="{{ old('durasi_menit', $ubah->durasi_menit ?? 25) }}">
                    </div>
                    <div>
                        <label>Nilai lulus (KKM)</label>
                        <input type="number" name="kkm" min="0" max="100" required value="{{ old('kkm', $ubah->kkm ?? 70) }}">
                    </div>
                    <div>
                        <label>Poin Student Root bila lulus</label>
                        <input type="number" name="poin_lulus" min="0" max="20" required value="{{ old('poin_lulus', $ubah->poin_lulus ?? 3) }}">
                    </div>
                    <div>
                        <label>Maks percobaan</label>
                        <input type="number" name="maks_percobaan" min="1" max="5" required value="{{ old('maks_percobaan', $ubah->maks_percobaan ?? 2) }}">
                    </div>
                    <div>
                        <label>Semester (opsional)</label>
                        <select name="semester">
                            <option value="">—</option>
                            <option value="1" @selected((string) old('semester', $ubah->semester ?? '') === '1')>1</option>
                            <option value="2" @selected((string) old('semester', $ubah->semester ?? '') === '2')>2</option>
                        </select>
                    </div>
                    <div>
                        <label>Saklar</label>
                        <label style="font-weight:600;color:#0f172a"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $ubah->aktif ?? false))> Aktif (bisa dikerjakan siswa)</label>
                    </div>
                </div>

                <div style="margin-top:16px">
                    <label>Modul kosakata yang diujikan (boleh pilih beberapa)</label>

                    <div class="catatan-lengkap">
                        <strong>Syarat wajib: kosakata modul harus sudah lengkap Arab DAN Inggris.</strong>
                        Kosakata yang salah satu bahasanya masih kosong <em>tidak ikut jadi soal</em> — kalau banyak yang
                        kosong, jumlah soal per sesi bisa kurang dari yang diminta, dan sesi Arab/Inggris bisa ditolak
                        ("soal belum siap"). Lengkapi dulu di <strong>BEE Smart → Bank Kosakata</strong>, baru modul itu
                        bisa diujikan. Baris di bawah menunjukkan berapa kosakata yang sudah lengkap.
                    </div>

                    <div class="pilih-modul">
                        <div class="pilih-bar">
                            <button type="button" class="tombol kedua" id="pilihSemua">Pilih semua</button>
                            <button type="button" class="tombol kedua" id="pilihKelas">Sesuai kelas</button>
                            <button type="button" class="tombol kedua" id="pilihKosong">Kosongkan</button>
                            <span class="hitung" id="hitungModul">0 modul · 0 kosakata siap</span>
                        </div>

                        <div class="daftar-modul">
                            @foreach ($minggu as $m)
                                @php
                                    $st = $jumlahKosakata[$m->id] ?? null;
                                    $jml = (int) ($st->jml ?? 0);
                                    $siap = (int) ($st->siap ?? 0);
                                    $kurang = (int) ($st->kurang ?? 0);
                                @endphp
                                <label class="baris-modul {{ $siap === 0 ? 'kosong' : '' }} {{ $kurang > 0 ? 'belum' : '' }}">
                                    <input type="checkbox" name="modul[]" value="{{ $m->id }}"
                                           data-jml="{{ $jml }}" data-siap="{{ $siap }}" data-kurang="{{ $kurang }}"
                                           data-judul="{{ $m->judul }}"
                                           @checked(in_array($m->id, old('modul', $ubah->modul ?? []), true))>
                                    <span class="nama">{{ $m->judul }}</span>
                                    <span class="pil {{ $m->status === 'aktif' ? 'buka' : '' }}">{{ $m->status }}</span>
                                    <span class="kosakata">
                                        @if ($jml === 0)
                                            belum ada kosakata — belum bisa diujikan
                                        @elseif ($kurang === 0)
                                            {{ $jml }} kosakata · lengkap Arab &amp; Inggris ✓
                                        @else
                                            {{ $jml }} kosakata · siap {{ $siap }} ·
                                            <strong class="tanda-kurang">{{ $kurang }} belum lengkap</strong>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <label class="otomatis">
                            <input type="checkbox" name="modul_otomatis" value="1" id="saklarOtomatis"
                                   @checked(old('modul_otomatis', isset($ubah) ? empty($ubah->modul) : true))>
                            Ambil otomatis dari rentang tanggal periode (abaikan pilihan di atas)
                        </label>
                        <p class="bantuan">
                            Dipilih: modul-modul di atas saja. Tidak dipilih + saklar mati = seluruh bank kosakata.
                            Baris abu = kosakatanya belum lengkap Arab &amp; Inggris → belum bisa diujikan.
                        </p>
                        <p class="awas" id="awasModul" style="display:none"></p>
                    </div>
                </div>

                <script>
                    (function () {
                        const kotak = Array.from(document.querySelectorAll('input[name="modul[]"]'));
                        const saklar = document.getElementById('saklarOtomatis');
                        const hitung = document.getElementById('hitungModul');

                        function perbarui() {
                            const terpilih = kotak.filter(k => k.checked);
                            const siap = terpilih.reduce((t, k) => t + parseInt(k.dataset.siap || '0', 10), 0);
                            const kurang = terpilih.reduce((t, k) => t + parseInt(k.dataset.kurang || '0', 10), 0);
                            hitung.textContent = terpilih.length + ' modul · ' + siap + ' kosakata siap uji';

                            const awas = document.getElementById('awasModul');
                            if (kurang > 0) {
                                awas.textContent = '⚠️ ' + kurang + ' kosakata pada modul terpilih belum lengkap Arab/Inggris — '
                                    + 'tidak bisa diujikan, jadi soal bisa kurang dari jumlah yang diminta. '
                                    + 'Lengkapi dulu di BEE Smart → Bank Kosakata.';
                                awas.style.display = 'block';
                            } else {
                                awas.style.display = 'none';
                            }
                            kotak.forEach(k => k.disabled = saklar.checked);
                        }

                        document.getElementById('pilihSemua').addEventListener('click', function () {
                            saklar.checked = false;
                            kotak.forEach(k => { if (parseInt(k.dataset.siap || '0', 10) > 0) { k.checked = true; } });
                            perbarui();
                        });
                        document.getElementById('pilihKosong').addEventListener('click', function () {
                            kotak.forEach(k => k.checked = false);
                            perbarui();
                        });

                        // Centang otomatis sesuai kelas: judul week memuat label kelasnya
                        // (mis. "5th Week grade X" vs "5th Week grade XI and XII").
                        function cocokKelas(judul, kelas) {
                            const t = (judul || '').toUpperCase();
                            if (kelas === 'SEMUA') { return true; }
                            if (kelas === 'X') { return /GRADE\s*X\b/.test(t) && !/XI|XII/.test(t); }
                            return t.indexOf(kelas.toUpperCase()) !== -1;
                        }

                        function centangSesuaiKelas() {
                            const kelas = document.getElementById('pilihKelas').value;
                            saklar.checked = false;
                            kotak.forEach(k => {
                                k.checked = cocokKelas(k.dataset.judul || '', kelas);
                            });
                            perbarui();
                        }

                        document.getElementById('pilihKelas').addEventListener('click', centangSesuaiKelas);
                        // Ganti kelas → centangannya menyesuaikan sendiri (bisa diubah manual setelahnya).
                        document.getElementById('pilihKelas').addEventListener('change', centangSesuaiKelas);
                        kotak.forEach(k => k.addEventListener('change', perbarui));
                        saklar.addEventListener('change', perbarui);
                        perbarui();
                    })();
                </script>

                <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
                    <button class="tombol utama" type="submit">{{ $ubah ? 'Simpan perubahan' : 'Buat periode' }}</button>
                    @if ($ubah)<a class="tombol kedua" href="{{ route('bee.evaluasi') }}">Batal</a>@endif
                </div>
            </form>
        </div>

        <div class="ev-kartu">
            <h3>Daftar periode</h3>
            @if ($evaluasi->isEmpty())
                <p style="color:#94a3b8;font-size:13.5px">Belum ada periode evaluasi.</p>
            @else
                <table class="ev-tbl">
                    <thead><tr><th>Judul</th><th>Kelas</th><th>Jenis</th><th>Periode</th><th>Soal</th><th>KKM</th><th>Peserta</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($evaluasi as $e)
                            @php $r = $ringkasSesi[$e->id] ?? ['peserta' => 0, 'lulus' => 0, 'rata' => 0]; @endphp
                            <tr>
                                <td data-l="Judul">{{ $e->judul }}</td>
                                <td data-l="Kelas"><span class="pil {{ ($e->kelas ?? '') === 'SEMUA' ? 'buka' : '' }}">{{ $e->label_kelas }}</span></td>
                                <td data-l="Jenis">{{ $e->label_jenis }}</td>
                                <td data-l="Periode">{{ $e->mulai->format('d/m/y') }} – {{ $e->selesai->format('d/m/y') }}</td>
                                <td data-l="Soal">{{ $e->jumlah_soal }} soal · {{ $e->durasi_menit }} mnt</td>
                                <td data-l="KKM">{{ $e->kkm }} (+{{ $e->poin_lulus }} poin)</td>
                                <td data-l="Peserta">
                                    @if ($r['peserta'] === 0)
                                        <span style="color:#94a3b8">belum ada</span>
                                    @else
                                        {{ $r['peserta'] }} siswa · rata {{ $r['rata'] }} · lulus {{ $r['lulus'] }}
                                    @endif
                                </td>
                                <td data-l="Status"><span class="pil {{ $e->aktif ? 'buka' : 'tutup' }}}">{{ $e->aktif ? 'Dibuka' : 'Ditutup' }}</span></td>
                                <td data-l="Aksi">
                                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                                        <a class="tombol kedua" href="{{ route('bee.evaluasi', ['ubah' => $e->id]) }}">Ubah</a>
                                        <a class="tombol utama" href="{{ route('bee.evaluasi.hasil', $e->id) }}">Hasil</a>
                                        <form method="post" action="{{ route('bee.evaluasi.salin', $e->id) }}" onsubmit="return confirm('Salin sesi ini? Salinan langsung dimatikan (Ditutup) supaya bisa diperiksa dulu.')">
                                            @csrf
                                            <button class="tombol kedua" type="submit">Salin</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
