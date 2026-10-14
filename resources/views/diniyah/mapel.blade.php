<x-app-layout>
    @include('diniyah._gaya')
    <div class="dk-wrap">
        <div class="dk-tumpuk">
            @include('diniyah._kepala', [
                'judul' => 'Mata Pelajaran Kajian Diniyah',
                'keterangan' => 'Ditetapkan Kepala Diniyah. Daftar santri ditarik otomatis dari kelas di Data Induk Siswa.',
                'tab' => 'mapel',
            ])

            <div class="dk-kartu">
                <h2>Tambah mata pelajaran</h2>
                <form method="POST" action="{{ route('diniyah.mapel.simpan') }}" class="dk-kisi dua">
                    @csrf
                    <label class="dk-lbl">
                        <span>Nama mata pelajaran</span>
                        <input name="nama" required value="{{ old('nama') }}" placeholder="mis. Bahasa Arab — Nahwu" class="dk-kendali">
                    </label>
                    <label class="dk-lbl">
                        <span>Nama Arab (untuk rapor)</span>
                        <input name="nama_arab" value="{{ old('nama_arab') }}" dir="rtl" placeholder="النحو" class="dk-kendali dk-arab">
                    </label>

                    <div class="dk-lebar">
                        <span class="dk-label">Berlaku untuk kelas</span>
                        @if(count($daftarKelas))
                            <div class="dk-baris-kendali">
                                @foreach($daftarKelas as $namaKelas => $jumlah)
                                    <label class="dk-cip">
                                        <input type="checkbox" name="kelas[]" value="{{ $namaKelas }}">
                                        <span style="text-transform:none; letter-spacing:0; color:inherit; font-size:12.5px; margin:0; font-weight:700">{{ $namaKelas }}</span>
                                        <span style="color:#94a3b8; font-weight:700">({{ $jumlah }})</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="dk-bantu">Santri di kelas terpilih otomatis masuk daftar absensi &amp; penilaian.</p>
                        @else
                            <p class="dk-bantu">Belum ada santri berkelas di Data Induk Siswa.</p>
                        @endif
                    </div>

                    <div>
                        <label class="dk-lbl">
                            <span>Guru pengampu (dari daftar)</span>
                            <select name="guru_id" class="dk-kendali">
                                <option value="">— pilih dari daftar guru —</option>
                                @foreach($daftarGuru as $g)
                                    <option value="{{ $g->id }}" @selected(old('guru_id') == $g->id)>{{ $g->name }}@if($g->nipa) · {{ $g->nipa }}@endif</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="dk-lbl" style="margin-top:10px">
                            <span>Atau ketik manual (bila bukan pengguna SIAKAD)</span>
                            <div class="dk-baris-kendali">
                                <input name="guru_nama" value="{{ old('guru_nama') }}" placeholder="Nama guru" class="dk-kendali" style="flex:1 1 180px">
                                <input name="guru_nipa" value="{{ old('guru_nipa') }}" placeholder="NIPA (opsional)" class="dk-kendali" style="flex:0 1 150px">
                            </div>
                        </label>
                    </div>

                    <div class="dk-kisi dua" style="grid-template-columns:1fr; gap:12px">
                        <label class="dk-lbl">
                            <span>Jam pelajaran / pekan</span>
                            <input name="jp_pekan" type="number" min="1" max="20" value="{{ old('jp_pekan') }}" class="dk-kendali">
                        </label>
                        <label class="dk-lbl">
                            <span>Urutan di rapor</span>
                            <input name="urutan" type="number" min="0" max="999" value="{{ old('urutan', 0) }}" class="dk-kendali">
                        </label>
                    </div>

                    <div class="dk-lebar">
                        <button class="dk-tombol utama">Simpan mata pelajaran</button>
                    </div>
                </form>
            </div>

            <div class="dk-kartu">
                <h2>Daftar mata pelajaran <span class="dk-ket">Mata pelajaran tidak dihapus, hanya dinonaktifkan — supaya nilai semester lalu tetap utuh.</span></h2>

                @if($ringkas->count())
                    {{-- layar lebar: tabel --}}
                    <div class="dk-saja-lebar">
                        <div class="dk-tabel-bungkus">
                            <table class="dk-tabel" style="min-width:900px">
                                <colgroup>
                                    <col><col style="width:110px"><col style="width:180px">
                                    <col style="width:56px"><col style="width:72px"><col style="width:92px">
                                    <col style="width:92px"><col style="width:110px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Mata pelajaran</th><th>Kelas</th><th>Guru pengampu</th>
                                        <th class="dk-tengah">JP</th><th class="dk-tengah">Santri</th><th class="dk-tengah">Terisi</th>
                                        <th class="dk-tengah">Status</th><th class="dk-kanan">Ubah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ringkas as $r)
                                        <tr>
                                            <td class="nama">
                                                {{ $r->mapel->nama }}
                                                @if($r->mapel->nama_arab)<span class="dk-arab"> · {{ $r->mapel->nama_arab }}</span>@endif
                                            </td>
                                            <td>{{ implode(', ', $r->kelas) ?: '—' }}</td>
                                            <td>{{ $r->mapel->guruTampil() }}</td>
                                            <td class="dk-tengah">{{ $r->mapel->jp_pekan ?? '—' }}</td>
                                            <td class="dk-tengah">{{ $r->santri }}</td>
                                            <td class="dk-tengah">
                                                <span style="font-weight:800; color:{{ ($r->terisi >= $r->santri && $r->santri > 0) ? '#15803d' : '#b45309' }}">{{ $r->terisi }}/{{ $r->santri }}</span>
                                            </td>
                                            <td class="dk-tengah">
                                                <span style="display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:800; border:1px solid {{ $r->mapel->status === 'aktif' ? '#bbf7d0' : '#e2e8f0' }}; background:{{ $r->mapel->status === 'aktif' ? '#f0fdf4' : '#f8fafc' }}; color:{{ $r->mapel->status === 'aktif' ? '#166534' : '#475569' }}">{{ $r->mapel->status }}</span>
                                            </td>
                                            <td class="dk-kanan">
                                                <form method="POST" action="{{ route('diniyah.mapel.status', $r->mapel->id) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="dk-tombol polos">{{ $r->mapel->status === 'aktif' ? 'nonaktifkan' : 'aktifkan' }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- HP: kartu --}}
                    <div class="dk-saja-hp">
                        @foreach($ringkas as $r)
                            <div class="dk-baris-santri">
                                <p class="dk-nama">
                                    {{ $r->mapel->nama }}
                                    @if($r->mapel->nama_arab)<span class="dk-arab" style="font-size:14px"> · {{ $r->mapel->nama_arab }}</span>@endif
                                </p>
                                <p class="dk-meta" style="margin-top:4px">
                                    <span class="dk-pil">{{ implode(', ', $r->kelas) ?: '—' }}</span>
                                    &nbsp;{{ $r->mapel->guruTampil() }}
                                </p>
                                <p class="dk-meta" style="margin-top:6px">
                                    {{ $r->mapel->jp_pekan ?? '—' }} JP/pekan · {{ $r->santri }} santri ·
                                    <span style="color:{{ ($r->terisi >= $r->santri && $r->santri > 0) ? '#15803d' : '#b45309' }}">{{ $r->terisi }} nilai terisi</span>
                                </p>
                                <div class="dk-baris-kendali" style="margin-top:10px; align-items:center; justify-content:space-between">
                                    <span style="display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:800; border:1px solid {{ $r->mapel->status === 'aktif' ? '#bbf7d0' : '#e2e8f0' }}; background:{{ $r->mapel->status === 'aktif' ? '#f0fdf4' : '#f8fafc' }}; color:{{ $r->mapel->status === 'aktif' ? '#166534' : '#475569' }}">{{ $r->mapel->status }}</span>
                                    <form method="POST" action="{{ route('diniyah.mapel.status', $r->mapel->id) }}">
                                        @csrf @method('PATCH')
                                        <button class="dk-tombol sekunder">{{ $r->mapel->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dk-kosong">
                        <span class="dk-ikon">📚</span>
                        Belum ada mata pelajaran.<br>Tambah lewat formulir di atas — santri kelas terpilih otomatis masuk daftar.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
