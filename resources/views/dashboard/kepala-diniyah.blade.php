<x-app-layout>
    @php
        if (!isset($pengaturan)) { $pengaturan = \App\Models\Pengaturan::first(); }
        $roleChips = auth()->user()->getRoleNames();
        $poinBersih = $poinBulan['net'];
    @endphp
    @include('dashboard._styles')

    <div class="sa-page">
        <!-- HERO -->
        <div class="sa-hero">
            <div>
                <h1>Kendali Asrama &amp; Karakter</h1>
                <div class="sub">Ringkasan <b>{{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</b> · {{ now()->format('d M Y') }}</div>
                @if($roleChips->isNotEmpty())
                <div class="sa-chips">
                    @foreach($roleChips as $r)
                    <span class="sa-chip"><span class="sa-dot" style="background:#7c3aed;"></span>{{ $r }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="sa-actions">
                @can('buka-menu-asrama')
                <a href="{{ route('asrama.dashboard') }}" class="sa-btn sa-btn-primary">🛏️ Dashboard Asrama</a>
                @endcan
                @can('buka-menu-manajemen-kamar')
                <a href="{{ route('asrama.kamar.index') }}" class="sa-btn sa-btn-ghost">🏢 Manajemen Kamar</a>
                @endcan
                <a href="{{ route('sr.dashboard') }}" class="sa-btn sa-btn-ghost">📊 Dashboard Karakter</a>
            </div>
        </div>

        <!-- METRIK -->
        <div class="sa-grid-metrics">
            <div class="sa-metric"><div class="lbl">Kamar Putra</div><div class="val">{{ $kamarPutra }} <small>aktif</small></div></div>
            <div class="sa-metric"><div class="lbl">Kamar Putri</div><div class="val">{{ $kamarPutri }} <small>aktif</small></div></div>
            <div class="sa-metric"><div class="lbl">Penghuni Asrama</div><div class="val">{{ $penghuniPutra + $penghuniPutri }} <small>siswa</small></div></div>
            <div class="sa-metric"><div class="lbl">Inspeksi Final · Bulan Ini</div><div class="val">{{ $inspeksiBulan['total'] }} <small>{{ now()->format('M Y') }}</small></div></div>
            <div class="sa-metric"><div class="lbl">Grup Binaan Aktif</div><div class="val">{{ $grupAktif }} <small>{{ $anggotaBinaan }} anggota</small></div></div>
        </div>

        <div class="sa-grid2">
            <!-- KIRI: ASRAMA -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>🛏️ Asrama · Bulan Ini</h2>
                    @can('buka-menu-asrama')
                    <a class="lnk" href="{{ route('asrama.dashboard') }}">Dashboard →</a>
                    @endcan
                </div>
                <div class="sa-card-b">
                    <div class="sa-row">
                        <span class="sa-ava" style="background:#eff6ff;color:#1e40af;">P</span>
                        <div class="t"><div class="nm">Divisi Putra</div><div class="ds">{{ $kamarPutra }} kamar · {{ $penghuniPutra }} penghuni</div></div>
                        <span class="sa-num-mini">{{ $inspeksiBulan['putra'] }} <small>final</small></span>
                    </div>
                    <div class="sa-row">
                        <span class="sa-ava" style="background:#fdf2f8;color:#9d174d;">W</span>
                        <div class="t"><div class="nm">Divisi Putri</div><div class="ds">{{ $kamarPutri }} kamar · {{ $penghuniPutri }} penghuni</div></div>
                        <span class="sa-num-mini">{{ $inspeksiBulan['putri'] }} <small>final</small></span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Aktivitas Hari Ini</div><div class="ds">semua status penilaian</div></div>
                        <span class="sa-num-mini">{{ $inspeksiHariIni }} <small>penilaian</small></span>
                    </div>
                </div>
            </div>

            <!-- KANAN: KARAKTER + BEE -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🌱 Karakter · Bulan Ini</h2>
                        <a class="lnk" href="{{ route('sr.dashboard') }}">Rekap →</a>
                    </div>
                    <div class="sa-card-b">
                        <div class="sa-row">
                            <div class="t"><div class="nm">Entri Poin</div><div class="ds">tercatat bulan ini</div></div>
                            <span class="sa-num-mini">{{ $poinBulan['jml'] }}</span>
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Poin Bersih</div><div class="ds">seluruh kriteria</div></div>
                            @if($poinBersih >= 0)
                            <span class="sa-pill sa-pill-pos">+{{ $poinBersih }}</span>
                            @else
                            <span class="sa-pill sa-pill-neg">{{ $poinBersih }}</span>
                            @endif
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Grup &amp; Anggota</div><div class="ds">aktif saat ini</div></div>
                            <span class="sa-num-mini">{{ $grupAktif }} <small>/ {{ $anggotaBinaan }} siswa</small></span>
                        </div>
                    </div>
                </div>

                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🐝 Bahasa — Sedang Tayang</h2>
                        <a class="lnk" href="{{ route('bee.index') }}">Kelola →</a>
                    </div>
                    <div class="sa-card-b">
                        @forelse($beeAktif as $w)
                        <div class="sa-row">
                            <span class="sa-dot sa-dot-ok"></span>
                            <div class="t"><div class="nm">{{ $w->judul }}</div></div>
                            <span class="sa-num-mini">{{ $w->vocabs_count }} <small>kata</small></span>
                        </div>
                        @empty
                        <div class="sa-empty"><span class="big">📭</span>Tidak ada modul aktif saat ini.</div>
                        @endforelse
                        @if($beeAktif->isNotEmpty())
                        <div style="padding:.6rem .5rem .75rem; font-size:.76rem; color:var(--muted); border-top:1px dashed var(--line);">
                            Total <b>{{ $beeKataAktif }}</b> kata yang sedang tayang.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
