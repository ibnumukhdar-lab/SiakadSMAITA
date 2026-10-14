<x-app-layout>
    @include('diniyah._gaya')
    <div class="dk-wrap">
        <div class="dk-tumpuk">
            @include('diniyah._kepala', [
                'judul' => 'Absensi Kajian Diniyah',
                'keterangan' => 'Dicatat per pertemuan (tanggal + mapel + jam ke). Rekapnya masuk rapor otomatis.',
                'tab' => 'absensi',
            ])

            <div class="dk-kartu">
                <form method="GET" action="{{ route('diniyah.absensi') }}" class="dk-kisi dua">
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
                    <label class="dk-lbl">
                        <span>Pertemuan</span>
                        <select name="pertemuan" onchange="this.form.submit()" class="dk-kendali">
                            @forelse($pertemuanList as $pt)
                                <option value="{{ $pt->id }}" @selected($pertemuan && $pertemuan->id === $pt->id)>
                                    {{ $pt->tanggal->format('d/m/Y') }}@if($pt->jam_ke) · jam ke-{{ $pt->jam_ke }}@endif @if($pt->materi) · {{ \Illuminate\Support\Str::limit($pt->materi, 28) }} @endif
                                </option>
                            @empty
                                <option value="">— belum ada pertemuan —</option>
                            @endforelse
                        </select>
                    </label>
                </form>
                @if($periode)
                    <p class="dk-bantu">
                        Periode aktif: {{ $periode->label() }}@if(! $periode->terbuka) — <strong style="color:#b45309">masih tertutup, absensi tersimpan sebagai draf</strong>@endif
                    </p>
                @endif
            </div>

            @if($terpilih)
                <div class="dk-kartu">
                    <h2>Catat pertemuan baru <span class="dk-ket">Isi tanggal &amp; materi, lalu langsung isi kehadiran di daftar bawah.</span></h2>
                    <form method="POST" action="{{ route('diniyah.absensi.pertemuan') }}" class="dk-kisi empat">
                        @csrf
                        <input type="hidden" name="mapel_id" value="{{ $terpilih->id }}">
                        <label class="dk-lbl">
                            <span>Tanggal</span>
                            <input type="date" name="tanggal" required value="{{ now()->toDateString() }}" class="dk-kendali">
                        </label>
                        <label class="dk-lbl">
                            <span>Jam ke</span>
                            <input type="number" name="jam_ke" min="1" max="20" class="dk-kendali">
                        </label>
                        <label class="dk-lbl dk-span2">
                            <span>Materi / topik kajian</span>
                            <input name="materi" placeholder="mis. Bab Isim &amp; Fi'il — latihan i'rab" class="dk-kendali">
                        </label>
                        <div class="dk-lebar">
                            <button class="dk-tombol utama">Simpan pertemuan &amp; isi absensi</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="dk-kartu">
                    <div class="dk-kosong">
                        <span class="dk-ikon">📋</span>
                        Belum ada mata pelajaran untuk Anda.<br>
                        Buka <a href="{{ route('diniyah.mapel') }}" style="color:#1f3a5f; font-weight:800">Mata Pelajaran</a> untuk menetapkan mapel &amp; kelasnya lebih dulu.
                    </div>
                </div>
            @endif

            @if($pertemuan)
                <form method="POST" action="{{ route('diniyah.absensi.simpan') }}" class="dk-kartu">
                    @csrf
                    <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">

                    <h2>
                        {{ $terpilih->nama }} — {{ $pertemuan->tanggal->format('d/m/Y') }}@if($pertemuan->jam_ke) (jam ke-{{ $pertemuan->jam_ke }})@endif
                        <span class="dk-ket">{{ $pertemuan->materi ?: 'belum ada materi' }} · catatan tersimpan: {{ $sudahTercatat }} · daftar santri sekarang: {{ count($daftar) }}</span>
                    </h2>

                    {{-- HP: satu kartu per santri, status sekali sentuh --}}
                    <div class="dk-saja-hp">
                        @forelse($daftar as $i => $baris)
                            <div class="dk-baris-santri dk-bar-santri">
                                <p class="dk-nama">{{ $i + 1 }}. {{ $baris->siswa->nama_lengkap }}</p>
                                <p class="dk-meta" style="margin-top:4px"><span class="dk-pil">{{ $baris->siswa->kelas }}</span></p>
                                <div class="dk-pilih">
                                    @foreach($statusList as $kode => $label)
                                        <label>
                                            <input type="radio" name="status[{{ $baris->siswa->id }}]" value="{{ $kode }}" @checked($baris->status === $kode)>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <label class="dk-lbl" style="margin-top:10px">
                                    <span>Keterangan (opsional)</span>
                                    <input name="keterangan[{{ $baris->siswa->id }}]" value="{{ $baris->keterangan }}" placeholder="mis. sakit demam" class="dk-kendali dk-kecil">
                                </label>
                            </div>
                        @empty
                            <div class="dk-kosong">Tidak ada santri untuk mapel ini pada wewenang Anda.</div>
                        @endforelse
                    </div>

                    {{-- layar lebar: tabel --}}
                    <div class="dk-saja-lebar">
                        <div class="dk-tabel-bungkus">
                            <table class="dk-tabel" style="min-width:860px">
                                <colgroup>
                                    <col style="width:56px"><col><col style="width:74px">
                                    <col style="width:200px"><col style="width:280px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="dk-tengah">No</th>
                                        <th>Nama santri</th>
                                        <th>Kelas</th>
                                        <th>Status kehadiran</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftar as $i => $baris)
                                        <tr class="dk-bar-santri">
                                            <td class="dk-nomor">{{ $i + 1 }}</td>
                                            <td class="nama">{{ $baris->siswa->nama_lengkap }}</td>
                                            <td>{{ $baris->siswa->kelas }}</td>
                                            <td>
                                                <select name="status[{{ $baris->siswa->id }}]" class="dk-kendali dk-kecil">
                                                    @foreach($statusList as $kode => $label)
                                                        <option value="{{ $kode }}" @selected($baris->status === $kode)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input name="keterangan[{{ $baris->siswa->id }}]" value="{{ $baris->keterangan }}" placeholder="—" class="dk-kendali dk-kecil">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="dk-kosong">Tidak ada santri untuk mapel ini pada wewenang Anda.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dk-bar">
                        <span id="dk-hitung" class="dk-hitung" data-kata="santri dalam daftar"></span>
                        <div class="dk-cari">
                            <button type="button" class="dk-tombol sekunder" onclick="dkSemuaHadir()">Semua hadir</button>
                            <button class="dk-tombol utama">Simpan absensi</button>
                        </div>
                    </div>

                    @if($rekap)
                        <p class="dk-bantu">
                            Tercatat pada pertemuan ini: {{ $rekap['hadir'] }} hadir · {{ $rekap['sakit'] }} sakit · {{ $rekap['izin'] }} izin · {{ $rekap['alpa'] }} alpa
                        </p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
