{{-- Tabel rincian aspek rapor: NILAI 0-100 + predikat (skor 1-5 tidak ditampilkan).
     $judul = judul tabel, $data = hasil rincian() dari PenilaianRaporController, $ambang = ambang predikat --}}
<div class="judul-tabel">{{ $judul }}</div>
<table>
    <thead>
        <tr>
            <th style="width:26px;">No</th>
            <th>Aspek Penilaian</th>
            <th style="width:74px;">Nilai</th>
            <th style="width:64px;">Predikat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['aspek'] as $i => $a)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $a['pertanyaan'] }}</td>
                <td class="c">
                    @if($a['nilai'] !== null)
                        <strong>{{ number_format($a['nilai'], 2) }}</strong>
                        <div class="bar-skor"><span style="width: {{ min(100, (int) round($a['nilai'])) }}%"></span></div>
                    @else
                        <span class="kosong">-</span>
                    @endif
                </td>
                <td class="c">{{ $a['predikat'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="c kosong">Belum ada aspek penilaian aktif.</td></tr>
        @endforelse
        @if($data['aspek_terisi'] > 0)
            <tr>
                <td colspan="2" style="text-align:right; font-weight:700;">Rata-rata nilai {{ $data['label_jenis'] ?? '' }}</td>
                <td class="c"><strong>{{ number_format($data['nilai_aspek'], 2) }}</strong></td>
                <td class="c"><strong>{{ $data['predikat_aspek'] }}</strong></td>
            </tr>
        @endif
    </tbody>
</table>
<p class="skala">
    Nilai 0–100 = rata-rata semua musyrif/musyrifah yang menilai aspek ini.
    Predikat: A ≥ {{ (int) ($ambang['a'] ?? 90) }} · B ≥ {{ (int) ($ambang['b'] ?? 80) }} · C ≥ {{ (int) ($ambang['c'] ?? 70) }} · D &lt; {{ (int) ($ambang['c'] ?? 70) }}.
</p>
