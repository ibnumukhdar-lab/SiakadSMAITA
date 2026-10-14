<x-app-layout>
    @include('diniyah._gaya')
    <div class="dk-wrap">
        <div class="dk-tumpuk">
            @include('diniyah._kepala', [
                'judul' => 'Input Nilai Kajian Diniyah',
                'keterangan' => $bolehSemua
                    ? 'Anda dapat menilai seluruh kelas (pengelola / guru pengampu).'
                    : 'Anda menilai santri kamar binaan Anda saja.',
                'tab' => 'nilai',
            ])

            <div class="dk-kartu">
                <form method="GET" action="{{ route('diniyah.nilai') }}" class="dk-kisi dua">
                    <label class="dk-lbl">
                        <span>Periode</span>
                        <select name="periode" onchange="this.form.submit()" class="dk-kendali">
                            @forelse($periodeList as $p)
                                <option value="{{ $p->id }}" @selected($periode && $periode->id === $p->id)>{{ $p->label() }}{{ $p->terbuka ? ' (terbuka)' : '' }}</option>
                            @empty
                                <option value="">— belum ada periode —</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="dk-lbl">
                        <span>Mata pelajaran</span>
                        <select name="mapel" onchange="this.form.submit()" class="dk-kendali">
                            @forelse($mapelList as $m)
                                <option value="{{ $m->id }}" @selected($terpilih && $terpilih->id === $m->id)>{{ $m->nama }} ({{ implode(', ', $m->daftarKelas()) }})</option>
                            @empty
                                <option value="">— belum ada mapel untuk Anda —</option>
                            @endforelse
                        </select>
                    </label>
                </form>
                @if($periode && ! $periode->terbuka)
                    <p class="dk-bantu"><strong style="color:#b45309">Periode ini masih tertutup</strong> — nilai tetap bisa disimpan sebagai draf; buka dulu di menu Periode bila penilaian sudah berjalan.</p>
                @endif
            </div>

            @if($terpilih)
                <form method="POST" action="{{ route('diniyah.nilai.simpan') }}" class="dk-kartu">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $periode?->id }}">
                    <input type="hidden" name="mapel_id" value="{{ $terpilih->id }}">

                    <h2>
                        {{ $terpilih->nama }}@if($terpilih->nama_arab) <span class="dk-arab">· {{ $terpilih->nama_arab }}</span>@endif
                        <span class="dk-ket">Guru pengampu: {{ $terpilih->guruTampil() }} · {{ count($daftar) }} santri · nilai 0–100, satu nilai akhir per mapel</span>
                    </h2>

                    {{-- HP: kartu per santri --}}
                    <div class="dk-saja-hp">
                        @forelse($daftar as $i => $baris)
                            <div class="dk-baris-santri dk-bar-santri">
                                <p class="dk-nama">{{ $i + 1 }}. {{ $baris->siswa->nama_lengkap }}@if($baris->dikoreksi) <span style="font-size:10.5px; font-weight:800; color:#0369a1">· dikoreksi guru</span>@endif</p>
                                <p class="dk-meta" style="margin-top:4px">
                                    <span class="dk-pil">{{ $baris->siswa->kelas }}</span>
                                    @if(\App\Support\DiniyahArab::predikatArab($baris->nilai)) &nbsp;Predikat: {{ \App\Support\DiniyahArab::predikatArab($baris->nilai) }} @endif
                                </p>
                                <label class="dk-lbl" style="margin-top:10px">
                                    <span>Nilai (0–100)</span>
                                    <input type="number" min="0" max="100" name="nilai[{{ $baris->siswa->id }}]" value="{{ $baris->nilai }}" class="dk-kendali">
                                </label>
                                <label class="dk-lbl" style="margin-top:10px">
                                    <span>Catatan (opsional)</span>
                                    <input name="catatan[{{ $baris->siswa->id }}]" value="{{ $baris->catatan }}" placeholder="mis. perlu latihan i'rab" class="dk-kendali dk-kecil">
                                </label>
                            </div>
                        @empty
                            <div class="dk-kosong">Tidak ada santri pada mapel ini untuk Anda (cek kamar binaan atau kelas mapel).</div>
                        @endforelse
                    </div>

                    {{-- layar lebar: tabel --}}
                    <div class="dk-saja-lebar">
                        <div class="dk-tabel-bungkus">
                            <table class="dk-tabel" style="min-width:860px">
                                <colgroup>
                                    <col style="width:56px"><col><col style="width:74px">
                                    <col style="width:120px"><col style="width:110px"><col style="width:280px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="dk-tengah">No</th>
                                        <th>Nama santri</th>
                                        <th>Kelas</th>
                                        <th class="dk-tengah">Nilai</th>
                                        <th class="dk-tengah">Predikat</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftar as $i => $baris)
                                        <tr class="dk-bar-santri">
                                            <td class="dk-nomor">{{ $i + 1 }}</td>
                                            <td class="nama">
                                                {{ $baris->siswa->nama_lengkap }}
                                                @if($baris->dikoreksi)<span style="font-size:10.5px; font-weight:800; color:#0369a1"> · dikoreksi guru</span>@endif
                                            </td>
                                            <td>{{ $baris->siswa->kelas }}</td>
                                            <td class="dk-tengah">
                                                <input type="number" min="0" max="100" name="nilai[{{ $baris->siswa->id }}]" value="{{ $baris->nilai }}" class="dk-kendali dk-kecil dk-tengah" style="width:86px">
                                            </td>
                                            <td class="dk-tengah dk-arab">{{ \App\Support\DiniyahArab::predikatArab($baris->nilai) ?: '—' }}</td>
                                            <td>
                                                <input name="catatan[{{ $baris->siswa->id }}]" value="{{ $baris->catatan }}" placeholder="—" class="dk-kendali dk-kecil">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="dk-kosong">Tidak ada santri pada mapel ini untuk Anda.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(count($daftar) > 0)
                        <div class="dk-bar">
                            <span id="dk-hitung" class="dk-hitung" data-kata="santri" data-dasar="Terisi {{ $daftar->whereNotNull('nilai')->count() }} dari {{ count($daftar) }}"></span>
                            <div class="dk-cari">
                                <button class="dk-tombol utama">Simpan nilai</button>
                            </div>
                        </div>
                    @endif
                </form>
            @else
                <div class="dk-kartu">
                    <div class="dk-kosong">
                        <span class="dk-ikon">📝</span>
                        Belum ada mata pelajaran untuk dinilai.<br>
                        Tetapkan dulu di <a href="{{ route('diniyah.mapel') }}" style="color:#1f3a5f; font-weight:800">Mata Pelajaran</a>.
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
