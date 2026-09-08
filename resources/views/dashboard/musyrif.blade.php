<x-app-layout>
    @php
        if (!isset($pengaturan)) { $pengaturan = \App\Models\Pengaturan::first(); }
        $roleChips = auth()->user()->getRoleNames();
        $jmlKamar = $kamarBinaan->count();
        $jmlPutra = $kamarBinaan->where('kategori', 'putra')->count();
        $jmlPutri = $kamarBinaan->where('kategori', 'putri')->count();
    @endphp
    @include('dashboard._styles')

    <div class="sa-page">
        <!-- HERO -->
        <div class="sa-hero">
            <div>
                <h1>Binaan Asrama Saya</h1>
                <div class="sub">Ringkasan binaan <b>{{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</b> · {{ now()->format('d M Y') }}</div>
                @if($roleChips->isNotEmpty())
                <div class="sa-chips">
                    @foreach($roleChips as $r)
                    <span class="sa-chip"><span class="sa-dot" style="background:#2563eb;"></span>{{ $r }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="sa-actions">
                <a href="{{ route('asrama.penilaian.hariIni') }}" class="sa-btn sa-btn-primary">📝 Inspeksi Hari Ini</a>
                <a href="{{ route('asrama.penilaian.index') }}" class="sa-btn sa-btn-ghost">📚 Histori Inspeksi</a>
                <a href="{{ route('asrama.kamar.binaan') }}" class="sa-btn sa-btn-ghost">🏠 Kamar Binaan Saya</a>
            </div>
        </div>

        <!-- METRIK -->
        <div class="sa-grid-metrics">
            <div class="sa-metric"><div class="lbl">Kamar Binaan</div><div class="val">{{ $jmlKamar }} <small>{{ $jmlPutra }} putra · {{ $jmlPutri }} putri</small></div></div>
            <div class="sa-metric"><div class="lbl">Penghuni Binaan</div><div class="val">{{ $totalPenghuni }} <small>siswa aktif</small></div></div>
            <div class="sa-metric"><div class="lbl">Final · Bulan Ini</div><div class="val">{{ $finalBulanIni }} <small>inspeksi saya</small></div></div>
            <div class="sa-metric">
                <div class="lbl">Status Hari Ini</div>
                @if($finalHariIni > 0)
                <div class="val" style="color:#047857;">{{ $finalHariIni }} <small>final ✓</small></div>
                @elseif($draftHariIni > 0)
                <div class="val" style="color:#b45309;">{{ $draftHariIni }} <small>draft — segera kunci</small></div>
                @else
                <div class="val" style="color:#64748b;">— <small>belum inspeksi</small></div>
                @endif
            </div>
        </div>

        <div class="sa-grid2">
            <!-- KIRI: KAMAR BINAAN SAYA -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>🏠 Kamar Binaan Saya</h2>
                    <a class="lnk" href="{{ route('asrama.kamar.binaan') }}">Kelola kamar →</a>
                </div>
                <div class="sa-card-b">
                    @forelse($kamarBinaan as $k)
                    <div class="sa-row">
                        <span class="sa-ava" style="{{ $k->kategori === 'putri' ? 'background:#fdf2f8;color:#9d174d;' : 'background:#eff6ff;color:#1e40af;' }}">{{ $k->kategori === 'putri' ? 'W' : 'P' }}</span>
                        <div class="t">
                            <div class="nm">{{ $k->nama_kamar }}</div>
                            <div class="ds">Kapasitas {{ $k->kapasitas }} orang</div>
                        </div>
                        @if($k->kategori === 'putri')
                        <span class="sa-pill sa-pill-amber">Putri</span>
                        @else
                        <span class="sa-pill sa-pill-blue">Putra</span>
                        @endif
                    </div>
                    @empty
                    <div class="sa-empty"><span class="big">🏕️</span>Anda belum memiliki kamar binaan aktif.<br>Hubungi Kepala Diniyah bila kamar Anda belum dipetakan.</div>
                    @endforelse
                </div>
            </div>

            <!-- KANAN: RINGKASAN + RIWAYAT -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>📌 Ringkasan Bulan Ini</h2>
                        <a class="lnk" href="{{ route('asrama.penilaian.index') }}">Histori →</a>
                    </div>
                    <div class="sa-card-b">
                        <div class="sa-row">
                            <div class="t"><div class="nm">Inspeksi Final</div><div class="ds">{{ now()->format('M Y') }}</div></div>
                            <span class="sa-num-mini">{{ $finalBulanIni }}</span>
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Draft Hari Ini</div><div class="ds">menunggu finalisasi</div></div>
                            @if($draftHariIni > 0)
                            <span class="sa-pill sa-pill-amber">{{ $draftHariIni }} draft</span>
                            @else
                            <span class="sa-pill sa-pill-soft">bersih</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🕒 Inspeksi Terakhir</h2>
                        <a class="lnk" href="{{ route('asrama.penilaian.hariIni') }}">Isi sekarang →</a>
                    </div>
                    <div class="sa-card-b">
                        @forelse($riwayat as $r)
                        <div class="sa-row">
                            <span class="sa-ava" style="{{ $r->kategori === 'putri' ? 'background:#fdf2f8;color:#9d174d;' : 'background:#eff6ff;color:#1e40af;' }}">{{ $r->kategori === 'putri' ? 'W' : 'P' }}</span>
                            <div class="t"><div class="nm">{{ \Carbon\Carbon::parse($r->tanggal)->format('D, d M Y') }}</div></div>
                            @if($r->status === 'final')
                            <span class="sa-pill sa-pill-pos">Final</span>
                            @else
                            <span class="sa-pill sa-pill-amber">Draft</span>
                            @endif
                        </div>
                        @empty
                        <div class="sa-empty"><span class="big">🕒</span>Belum ada inspeksi tercatat.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
