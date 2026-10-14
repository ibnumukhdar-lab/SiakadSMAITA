<x-app-layout>
    @include('diniyah._gaya')
    <div class="dk-wrap">
        <div class="dk-tumpuk">
            @include('diniyah._kepala', [
                'judul' => 'Periode Penilaian Diniyah',
                'keterangan' => 'Hanya satu periode boleh terbuka supaya angka rapor tidak bercampur. Dibuka/ditutup Kepala Diniyah.',
                'tab' => 'periode',
                'periode' => $aktif,
            ])

            <div class="dk-kartu">
                <h2>Tambah periode</h2>
                <form method="POST" action="{{ route('diniyah.periode.simpan') }}" class="dk-kisi dua">
                    @csrf
                    <label class="dk-lbl dk-lebar">
                        <span>Nama periode</span>
                        <input name="nama" required value="{{ old('nama', $usulanNama) }}" class="dk-kendali">
                    </label>
                    <label class="dk-lbl">
                        <span>Tahun ajaran</span>
                        <input name="tahun_ajaran" value="{{ old('tahun_ajaran', $usulanTahun) }}" class="dk-kendali">
                    </label>
                    <label class="dk-lbl">
                        <span>Semester</span>
                        <select name="semester" class="dk-kendali">
                            @foreach($semesterList as $kode => $label)
                                <option value="{{ $kode }}" @selected(\App\Models\DiniyahPeriode::semesterKalender() === $kode)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="dk-lbl dk-lebar">
                        <span>Tahun hijriah (kop rapor)</span>
                        <input name="tahun_hijriah" value="{{ old('tahun_hijriah', $usulanHijriah) }}" class="dk-kendali">
                    </label>
                    <div class="dk-lebar">
                        <button class="dk-tombol utama">Simpan periode</button>
                    </div>
                </form>
            </div>

            <div class="dk-kartu">
                <h2>Daftar periode <span class="dk-ket">Periode terbuka = tujuan penilaian &amp; absensi yang sedang berjalan.</span></h2>
                @forelse($periode as $p)
                    <div class="dk-item {{ $p->terbuka ? 'buka' : '' }}">
                        <div style="min-width:0">
                            <p style="margin:0; font-size:14px; font-weight:800; color:#1e293b">{{ $p->label() }}</p>
                            <p class="dk-meta" style="margin:4px 0 0">
                                {{ $semesterList[$p->semester] ?? $p->semester }}
                                @if($p->tahun_hijriah) · {{ $p->tahun_hijriah }} @endif
                                @if($p->dibuka_pada) · dibuka {{ $p->dibuka_pada->format('d/m/Y H:i') }} @endif
                                @if($p->ditutup_pada) · ditutup {{ $p->ditutup_pada->format('d/m/Y H:i') }} @endif
                            </p>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px">
                            <span style="display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:800; border:1px solid {{ $p->terbuka ? '#bbf7d0' : '#e2e8f0' }}; background:{{ $p->terbuka ? '#dcfce7' : '#f8fafc' }}; color:{{ $p->terbuka ? '#166534' : '#475569' }}">{{ $p->terbuka ? 'TERBUKA' : 'TERTUTUP' }}</span>
                            <form method="POST" action="{{ $p->terbuka ? route('diniyah.periode.tutup', $p->id) : route('diniyah.periode.buka', $p->id) }}">
                                @csrf @method('PATCH')
                                <button class="dk-tombol {{ $p->terbuka ? 'sekunder' : 'hijau' }}">{{ $p->terbuka ? 'Tutup' : 'Buka' }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="dk-kosong">
                        <span class="dk-ikon">🗓️</span>
                        Belum ada periode.<br>Buat periode pertama di formulir di atas supaya penilaian &amp; absensi bisa dimulai.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
