<x-app-layout>
    <style>
        /* Laporan BEE Smart — gaya mandiri supaya tidak bergantung CSS ter-build */
        .lap-bungkus { max-width: 1080px; margin: 0 auto; padding: 8px 16px 48px; }
        .lap-judul { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -.01em; }
        .lap-anak { font-size: 13.5px; color: #64748b; margin-top: 4px; }
        .lap-kartu { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; }
        .lap-saring { padding: 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-top: 18px; }
        .lap-saring label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 5px; }
        .lap-saring select { min-width: 240px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #fff; color: #0f172a; }
        .lap-saring input[type=text] { border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; width: 180px; }
        .lap-tbl { border: 0; border-radius: 10px; padding: 9px 16px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .lap-utama { background: #1e3a8a; color: #fff; }
        .lap-kedua { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
        .lap-ringkas { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px; }
        @media (min-width: 720px) { .lap-ringkas { grid-template-columns: repeat(4, 1fr); } }
        .lap-angka { padding: 16px; }
        .lap-angka p:first-child { font-size: 28px; font-weight: 800; color: #1e3a8a; line-height: 1; }
        .lap-angka p:last-child { font-size: 12.5px; color: #64748b; margin-top: 6px; }
        .lap-tabel { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .lap-tabel th { text-align: left; background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; padding: 11px 14px; border-bottom: 1px solid #e2e8f0; }
        .lap-tabel td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
        .lap-tabel tr:hover td { background: #f8fafc; }
        .lap-lencana { display: inline-block; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .lap-sudah { background: #dcfce7; color: #166534; }
        .lap-belum { background: #fee2e2; color: #991b1b; }
        .lap-kelas { font-weight: 700; color: #1e3a8a; }
        .lap-kosong { padding: 28px; text-align: center; color: #94a3b8; font-size: 13.5px; }
        @media (max-width: 640px) {
            .lap-tabel thead { display: none; }
            .lap-tabel tr { display: block; border-bottom: 1px solid #e2e8f0; padding: 10px 12px; }
            .lap-tabel td { display: flex; justify-content: space-between; gap: 12px; border: 0; padding: 5px 0; }
            .lap-tabel td::before { content: attr(data-l); color: #64748b; font-size: 12px; font-weight: 600; }
        }
    </style>

    <div class="lap-bungkus">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h3 class="lap-judul">🐝 Laporan Klaim BEE Smart</h3>
                <p class="lap-anak">
                    Siapa yang sudah dan belum mengklaim poin kosakata tiap modul.
                    <a href="{{ route('bee.index') }}" class="font-semibold" style="color:#1e3a8a">← Kembali ke BEE Smart</a>
                </p>
            </div>
        </div>

        <form method="get" class="lap-kartu lap-saring">
            <div>
                <label for="modul">Modul</label>
                <select name="modul" id="modul">
                    @foreach ($minggu as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $mingguTerpilih?->id)>
                            {{ $m->judul }} — {{ $m->status }}{{ $m->tanggal_mulai ? ' · '.\Illuminate\Support\Carbon::parse($m->tanggal_mulai)->format('d/m/Y') : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kelas">Kelas</label>
                <select name="kelas" id="kelas">
                    <option value="">Semua kelas</option>
                    @foreach ($kelasSemua as $k)
                        <option value="{{ $k }}" @selected($kelasDipilih === $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lap-tbl lap-utama">Tampilkan</button>
            @if ($mingguTerpilih)
                <a class="lap-tbl lap-kedua" href="{{ route('bee.laporan.ekspor', ['modul' => $mingguTerpilih->id, 'kelas' => $kelasDipilih ?: null]) }}">Unduh CSV</a>
            @endif
        </form>

        @if (! $mingguTerpilih)
            <div class="lap-kartu lap-kosong" style="margin-top:16px">Belum ada modul BEE Smart. Buat modul dulu di halaman BEE Smart.</div>
        @else
            <div class="lap-ringkas">
                <div class="lap-kartu lap-angka"><p>{{ $ringkas['siswa'] }}</p><p>Siswa {{ $kelasDipilih ? 'kelas '.$kelasDipilih : 'seluruh kelas' }} ({{ $mingguTerpilih->status }})</p></div>
                <div class="lap-kartu lap-angka"><p>{{ $ringkas['sudah'] }}</p><p>Sudah klaim</p></div>
                <div class="lap-kartu lap-angka"><p>{{ $ringkas['belum'] }}</p><p>Belum klaim</p></div>
                <div class="lap-kartu lap-angka"><p>{{ $ringkas['persen'] }}%</p><p>Partisipasi</p></div>
            </div>

            <h4 class="font-bold text-slate-800" style="margin:24px 0 10px;font-size:15px">Rekap per kelas</h4>
            <div class="lap-kartu" style="overflow:hidden">
                <table class="lap-tabel">
                    <thead><tr><th>Kelas</th><th>Siswa</th><th>Sudah</th><th>Belum</th><th>Partisipasi</th></tr></thead>
                    <tbody>
                        @foreach ($perKelas as $baris)
                            <tr>
                                <td data-l="Kelas"><span class="lap-kelas">{{ $baris['kelas'] }}</span></td>
                                <td data-l="Siswa">{{ $baris['siswa'] }}</td>
                                <td data-l="Sudah">{{ $baris['sudah'] }}</td>
                                <td data-l="Belum">{{ $baris['belum'] }}</td>
                                <td data-l="Partisipasi">{{ $baris['persen'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h4 class="font-bold text-slate-800" style="margin:24px 0 10px;font-size:15px">Rincian per siswa</h4>
            <div class="lap-kartu" style="overflow:hidden">
                @if (count($daftar))
                    <table class="lap-tabel">
                        <thead><tr><th>Nama</th><th>Kelas</th><th>Status</th><th>Waktu klaim</th></tr></thead>
                        <tbody>
                            @foreach ($daftar as $s)
                                <tr>
                                    <td data-l="Nama">{{ $s['nama'] }}</td>
                                    <td data-l="Kelas">{{ $s['kelas'] }}</td>
                                    <td data-l="Status">
                                        <span class="lap-lencana {{ $s['sudah'] ? 'lap-sudah' : 'lap-belum' }}">{{ $s['sudah'] ? 'Sudah klaim' : 'Belum klaim' }}</span>
                                    </td>
                                    <td data-l="Waktu klaim">{{ $s['waktu'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="lap-kosong">Tidak ada siswa pada saringan ini.</div>
                @endif
            </div>

            <p class="lap-anak" style="margin-top:14px">
                Modul arsip/draft tetap bisa dilihat di sini, tetapi siswa hanya bisa mengklaim modul berstatus <strong>aktif</strong>.
            </p>
        @endif
    </div>
</x-app-layout>
