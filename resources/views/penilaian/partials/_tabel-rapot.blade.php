{{-- Tabel rincian aspek untuk rapot (dipakai dua kali: Adab & Keasramaan).
     $judul = judul tabel, $data = hasil rincian() dari PenilaianRaporController --}}
<div class="judul-tabel">{{ $judul }}</div>
<table>
    <thead>
        <tr>
            <th style="width:26px;">No</th>
            <th>Aspek Penilaian</th>
            <th style="width:64px;">Skor</th>
            <th style="width:110px;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['aspek'] as $i => $a)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $a['pertanyaan'] }}</td>
                <td class="c">
                    @if($a['rata'] !== null)
                        <strong>{{ number_format($a['rata'], 2) }}</strong> <span style="color:#94a3b8;">/ 5</span>
                        <div class="bar-skor"><span style="width: {{ min(100, (int) round($a['rata'] / 5 * 100)) }}%"></span></div>
                    @else
                        <span class="kosong">-</span>
                    @endif
                </td>
                <td>{{ $a['label'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="c kosong">Belum ada aspek penilaian aktif.</td></tr>
        @endforelse
        @if($data['aspek_terisi'] > 0)
            <tr>
                <td colspan="2" style="text-align:right; font-weight:700;">Rata-rata skor aspek</td>
                <td class="c"><strong>{{ number_format($data['rata_aspek'], 2) }}</strong> <span style="color:#94a3b8;">/ 5</span></td>
                <td>{{ \App\Http\Controllers\PenilaianRaporController::labelSkala($data['rata_aspek']) }}</td>
            </tr>
        @endif
    </tbody>
</table>
<p class="skala">Skala: 1 Sangat kurang · 2 Kurang · 3 Cukup · 4 Baik · 5 Sangat baik. Nilai per aspek = rata-rata dari semua musyrif/musyrifah yang menilai.</p>
