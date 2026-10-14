<x-app-layout>
    @include('diniyah._gaya')
    <div class="dk-wrap">
        <div class="dk-tumpuk">
            @include('diniyah._kepala', [
                'judul' => 'Rapor Diniyah (Berbahasa Arab)',
                'keterangan' => 'Satu rapor per santri per semester: nilai tiap mapel + rekap kehadiran dari absensi pertemuan.',
                'tab' => 'rapor',
            ])

            <div class="dk-kartu">
                <form method="GET" action="{{ route('diniyah.rapor') }}" class="dk-kisi empat">
                    <label class="dk-lbl">
                        <span>Periode</span>
                        <select name="periode" class="dk-kendali">
                            @forelse($periodeList as $p)
                                <option value="{{ $p->id }}" @selected($periode && $periode->id === $p->id)>{{ $p->label() }}{{ $p->terbuka ? ' (terbuka)' : '' }}</option>
                            @empty
                                <option value="">— belum ada periode —</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="dk-lbl">
                        <span>Kelas</span>
                        <select name="kelas" class="dk-kendali">
                            <option value="">semua kelas</option>
                            @foreach(['X', 'XI', 'XII', 'Lulus'] as $k)
                                <option value="{{ $k }}" @selected($kelas === $k)>{{ $k }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="dk-lbl dk-span2">
                        <span>Cari nama / NISN (dari server)</span>
                        <input name="q" value="{{ $q }}" placeholder="mis. Ahmad atau 00xxxx" class="dk-kendali">
                    </label>
                    <div class="dk-lebar">
                        <button class="dk-tombol utama">Tampilkan</button>
                    </div>
                </form>
                <p class="dk-bantu">
                    {{ $santri->count() }} santri · {{ $jumlahMapel }} mata pelajaran aktif
                    @if($tanpaArab > 0) · <strong style="color:#b45309">{{ $tanpaArab }} santri belum punya transliterasi nama Arab</strong> (bisa diisi di Data Induk Siswa) @endif
                </p>
            </div>

            <div class="dk-kartu">
                <h2>Daftar santri <span class="dk-ket">Ketik untuk menyaring daftar di bawah — tanpa memuat ulang halaman.</span></h2>

                <label class="dk-lbl" style="max-width:420px">
                    <span>Saring cepat</span>
                    <input id="dk-cari-santri" placeholder="Ketik nama / NISN…" class="dk-kendali">
                </label>
                <p class="dk-bantu" id="dk-info-santri" style="margin-bottom:10px">{{ $santri->count() }} santri ditampilkan</p>

                @if($santri->count())
                    {{-- HP: kartu --}}
                    <div class="dk-saja-hp">
                        @foreach($santri as $i => $s)
                            <div class="dk-baris-santri" data-santri="{{ strtolower($s->nama_lengkap . ' ' . ($s->nama_arab ?: '') . ' ' . $s->nisn . ' ' . $s->kelas) }}">
                                <p class="dk-nama">{{ $i + 1 }}. {{ $s->nama_lengkap }}</p>
                                @if($s->nama_arab)<p class="dk-arab" style="margin:4px 0 0; font-size:15px">{{ $s->nama_arab }}</p>@endif
                                <p class="dk-meta" style="margin-top:6px">
                                    <span class="dk-pil">{{ $s->kelas }}</span>
                                    @if($s->nisn) &nbsp;NISN {{ $s->nisn }} @endif
                                </p>
                                <div style="margin-top:10px">
                                    <a href="{{ route('diniyah.rapor.cetak', ['siswa' => $s->id, 'periode' => $periode?->id]) }}" target="_blank" class="dk-tombol utama">Buka rapor</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- layar lebar: tabel --}}
                    <div class="dk-saja-lebar">
                        <div class="dk-tabel-bungkus">
                            <table class="dk-tabel" style="min-width:820px">
                                <colgroup>
                                    <col style="width:56px"><col><col style="width:230px">
                                    <col style="width:80px"><col style="width:150px"><col style="width:140px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="dk-tengah">No</th><th>Nama santri</th><th>Nama Arab</th>
                                        <th>Kelas</th><th>NISN</th><th class="dk-kanan">Rapor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($santri as $i => $s)
                                        <tr data-santri="{{ strtolower($s->nama_lengkap . ' ' . ($s->nama_arab ?: '') . ' ' . $s->nisn . ' ' . $s->kelas) }}">
                                            <td class="dk-nomor">{{ $i + 1 }}</td>
                                            <td class="nama">{{ $s->nama_lengkap }}</td>
                                            <td class="dk-arab">{{ $s->nama_arab ?: '—' }}</td>
                                            <td>{{ $s->kelas }}</td>
                                            <td>{{ $s->nisn }}</td>
                                            <td class="dk-kanan">
                                                <a href="{{ route('diniyah.rapor.cetak', ['siswa' => $s->id, 'periode' => $periode?->id]) }}" target="_blank" class="dk-tombol sekunder">Buka rapor</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="dk-kosong">
                        <span class="dk-ikon">🎓</span>
                        Tidak ada santri pada wewenang Anda untuk pilihan ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var kotak = document.getElementById('dk-cari-santri');
            var info = document.getElementById('dk-info-santri');
            if (! kotak) return;
            kotak.addEventListener('input', function () {
                var q = kotak.value.toLowerCase().trim();
                document.querySelectorAll('[data-santri]').forEach(function (el) {
                    el.style.display = (! q || (el.getAttribute('data-santri') || '').indexOf(q) !== -1) ? '' : 'none';
                });
                var n = Array.prototype.filter.call(document.querySelectorAll('[data-santri]'), function (e) { return e.offsetParent !== null; }).length;
                if (info) info.textContent = n + ' santri ditampilkan';
            });
        });
    </script>
</x-app-layout>
