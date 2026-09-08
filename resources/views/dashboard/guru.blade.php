<x-app-layout>
    @php
        if (!isset($pengaturan)) { $pengaturan = \App\Models\Pengaturan::first(); }
        $roleChips = auth()->user()->getRoleNames();
    @endphp
    @include('dashboard._styles')

    <div class="sa-page">
        <!-- HERO -->
        <div class="sa-hero">
            <div>
                <h1>Binaan &amp; Karakter Saya</h1>
                <div class="sub">Ringkasan binaan <b>{{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</b> · {{ now()->format('d M Y') }}</div>
                @if($roleChips->isNotEmpty())
                <div class="sa-chips">
                    @foreach($roleChips as $r)
                    <span class="sa-chip"><span class="sa-dot" style="background:#6366f1;"></span>{{ $r }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="sa-actions">
                <a href="{{ route('sr.poin.create') }}" class="sa-btn sa-btn-primary">📝 Input Poin Sikap</a>
                <a href="{{ route('sr.dashboard') }}" class="sa-btn sa-btn-ghost">📊 Dashboard Karakter</a>
                @can('buka-menu-grup-binaan')
                <a href="{{ route('sr.mygroup') }}" class="sa-btn sa-btn-ghost">👥 Grup Binaan Saya</a>
                @endcan
            </div>
        </div>

        <!-- METRIK -->
        <div class="sa-grid-metrics">
            <div class="sa-metric"><div class="lbl">Grup Binaan</div><div class="val">{{ $grupBinaan->count() }} <small>aktif</small></div></div>
            <div class="sa-metric"><div class="lbl">Anggota Binaan</div><div class="val">{{ $totalAnggota }} <small>siswa</small></div></div>
            <div class="sa-metric"><div class="lbl">Total Poin Grup</div><div class="val">{{ $totalPoinGrup }} <small>semua waktu</small></div></div>
            <div class="sa-metric"><div class="lbl">Modul BEE Tayang</div><div class="val">{{ $beeAktif->count() }} <small>modul</small></div></div>
        </div>

        <div class="sa-grid2">
            <!-- KIRI: GRUP BINAAN SAYA -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>👥 Grup Binaan Saya</h2>
                    @can('buka-menu-grup-binaan')
                    <a class="lnk" href="{{ route('sr.mygroup') }}">Kelola grup →</a>
                    @endcan
                </div>
                <div class="sa-card-b">
                    @forelse($grupBinaan as $g)
                    <div class="sa-row">
                        <span class="sa-dot" style="background:{{ $g->warna_grup }};"></span>
                        <div class="t">
                            <div class="nm">{{ $g->nama_grup }}</div>
                            <div class="ds">{{ $g->tahun_ajaran_mulai ? 'TA ' . $g->tahun_ajaran_mulai . ' · ' : '' }}{{ $g->anggota }} anggota</div>
                        </div>
                        @if(($g->poin ?? 0) >= 0)
                        <span class="sa-pill sa-pill-pos">+{{ $g->poin }}</span>
                        @else
                        <span class="sa-pill sa-pill-neg">{{ $g->poin }}</span>
                        @endif
                    </div>
                    @empty
                    <div class="sa-empty"><span class="big">🌱</span>Anda belum menjadi pembina grup aktif.<br>Hubungi admin bila grup binaan Anda belum terdaftar.</div>
                    @endforelse
                </div>
            </div>

            <!-- KANAN: MODUL BEE -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🐝 Bahasa — Sedang Tayang</h2>
                        <a class="lnk" href="{{ route('bee.index') }}">Buka modul →</a>
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

                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🎯 Pintasan Mengajar</h2>
                    </div>
                    <div class="sa-card-b">
                        <a href="{{ route('bee.classroom') }}" target="_blank" class="sa-btn sa-btn-ghost" style="margin:.35rem .25rem;">👨‍🏫 Mode Kelas (TV)</a>
                        <a href="{{ route('bee.buku-saku') }}" target="_blank" class="sa-btn sa-btn-ghost" style="margin:.35rem .25rem;">📱 Buku Saku Siswa</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
