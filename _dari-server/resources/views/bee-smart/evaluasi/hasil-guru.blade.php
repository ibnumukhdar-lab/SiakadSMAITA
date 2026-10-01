<x-app-layout>
    <style>
        .hg-bungkus { max-width: 1080px; margin: 0 auto; padding: 8px 16px 48px; }
        .hg-judul { font-size: 21px; font-weight: 800; color: #0f172a; }
        .hg-anak { font-size: 13.5px; color: #64748b; margin-top: 4px; }
        .hg-kartu { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; margin-top: 16px; }
        .hg-ringkas { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px; }
        @media (min-width: 720px) { .hg-ringkas { grid-template-columns: repeat(4, 1fr); } }
        .hg-angka { padding: 16px; }
        .hg-angka p:first-child { font-size: 28px; font-weight: 800; color: #1e3a8a; line-height: 1; margin: 0; }
        .hg-angka p:last-child { font-size: 12.5px; color: #64748b; margin: 6px 0 0; }
        .hg-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .hg-tbl th { text-align: left; background: #f8fafc; color: #475569; font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; padding: 11px 12px; border-bottom: 1px solid #e2e8f0; }
        .hg-tbl td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
        .pil { display: inline-block; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .pil.lulus { background: #dcfce7; color: #166534; }
        .pil.belum { background: #fee2e2; color: #991b1b; }
        .tombol { border: 0; border-radius: 10px; padding: 9px 14px; font-size: 12.5px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-block; }
        .utama { background: #1e3a8a; color: #fff; }
        .kedua { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .pesan { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; margin-top: 14px; }
        .peringatan { background: #fef3c7; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 12px 14px; font-size: 13px; margin-top: 14px; }
        @media (max-width: 700px) {
            .hg-tbl thead { display: none; }
            .hg-tbl tr { display: block; border-bottom: 1px solid #e2e8f0; padding: 10px 12px; }
            .hg-tbl td { display: flex; justify-content: space-between; gap: 12px; border: 0; padding: 5px 0; }
            .hg-tbl td::before { content: attr(data-l); color: #64748b; font-size: 12px; font-weight: 600; }
        }
    </style>

    <div class="hg-bungkus">
        <h3 class="hg-judul">Hasil: {{ $evaluasi->judul }}</h3>
        <p class="hg-anak">
            <strong>{{ $evaluasi->label_kelas }}</strong> · {{ $evaluasi->label_jenis }} · {{ $evaluasi->mulai->format('d M Y') }} – {{ $evaluasi->selesai->format('d M Y') }} ·
            KKM {{ $evaluasi->kkm }} · poin +{{ $evaluasi->poin_lulus }} ·
            <strong>{{ $evaluasi->aktif ? 'sedang dibuka' : 'ditutup' }}</strong>
            <br><a href="{{ route('bee.evaluasi') }}" style="color:#1e3a8a;font-weight:700">← Daftar periode</a>
        </p>

        @if (session('pesan'))
            <div class="pesan">{{ session('pesan') }}</div>
        @endif

        @if ($ringkas['sudah'] === 0)
            <div class="peringatan">Belum ada siswa yang mengerjakan evaluasi ini.</div>
        @endif

        <div class="hg-ringkas">
            <div class="hg-kartu hg-angka"><p>{{ $ringkas['sudah'] }}</p><p>Siswa sudah mengerjakan</p></div>
            <div class="hg-kartu hg-angka"><p>{{ $ringkas['lulus'] }}</p><p>Lulus (≥ {{ $evaluasi->kkm }})</p></div>
            <div class="hg-kartu hg-angka"><p>{{ $ringkas['rata'] }}</p><p>Nilai rata-rata</p></div>
            <div class="hg-kartu hg-angka"><p>{{ $jumlahPercobaan }}</p><p>Total percobaan</p></div>
        </div>

        <div class="hg-kartu">
            <strong style="color:#1e3a8a;font-size:14px">Ringkasan per sesi bahasa</strong>
            <table class="hg-tbl" style="margin-top:10px">
                <thead><tr><th>Sesi</th><th>Peserta</th><th>Lulus</th><th>Nilai rata-rata</th></tr></thead>
                <tbody>
                    @foreach (['inggris' => '🇬🇧 Bahasa Inggris', 'arab' => '🇸🇦 Bahasa Arab'] as $kode => $label)
                        <tr>
                            <td data-l="Sesi">{{ $label }}</td>
                            <td data-l="Peserta">{{ $perBahasa[$kode]['peserta'] ?? 0 }}</td>
                            <td data-l="Lulus">{{ $perBahasa[$kode]['lulus'] ?? 0 }}</td>
                            <td data-l="Rata-rata">{{ $perBahasa[$kode]['rata'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="font-size:12px;color:#94a3b8;margin:10px 0 0;line-height:1.6">
                Tabel peringkat di bawah menggabungkan kedua sesi (nilai terbaik per siswa).
            </p>
        </div>

        <div class="hg-kartu" style="padding:0;overflow:hidden">
            <div style="padding:16px 18px 6px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <strong style="color:#1e3a8a">Peringkat (nilai terbaik tiap siswa)</strong>
                <a class="tombol utama" href="{{ route('bee.evaluasi.hasil.ekspor', $evaluasi->id) }}">Unduh CSV</a>
            </div>
            @if ($terbaik->isEmpty())
                <p style="padding:0 18px 18px;color:#94a3b8;font-size:13.5px">Belum ada hasil.</p>
            @else
                <table class="hg-tbl">
                    <thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th>NIS</th><th>Sesi</th><th>Nilai</th><th>Percobaan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($terbaik->sortByDesc('nilai') as $urut => $p)
                            @php $s = $siswa->get($p->siswa_id); @endphp
                            <tr>
                                <td data-l="#"> {{ $loop->iteration }}</td>
                                <td data-l="Nama">{{ $s->nama_lengkap ?? '(siswa terhapus)' }}</td>
                                <td data-l="Kelas">{{ $s->kelas ?? '-' }}</td>
                                <td data-l="NIS">{{ $s->nis ?? '-' }}</td>
                                <td data-l="Sesi">{{ $p->bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris' }}</td>
                                <td data-l="Nilai"><strong>{{ $p->nilai }}</strong></td>
                                <td data-l="Percobaan">{{ $jumlahPercobaan ? $evaluasi->percobaan->where('siswa_id', $p->siswa_id)->count() : 1 }}</td>
                                <td data-l="Status">
                                    <span class="pil {{ $p->nilai >= $evaluasi->kkm ? 'lulus' : 'belum' }}">{{ $p->nilai >= $evaluasi->kkm ? 'Lulus' : 'Belum' }}</span>
                                </td>
                                <td data-l="Aksi">
                                    <form method="post" action="{{ route('bee.evaluasi.bukaulang', [$evaluasi->id, $p->siswa_id]) }}" onsubmit="return confirm('Hapus semua percobaan siswa ini supaya bisa mengerjakan ulang?')">
                                        @csrf
                                        <button class="tombol kedua" type="submit">Buka ulang</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
