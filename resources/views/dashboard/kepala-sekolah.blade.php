<x-app-layout>
    @php
        if (!isset($pengaturan)) { $pengaturan = \App\Models\Pengaturan::first(); }
        $roleChips = auth()->user()->getRoleNames();
        $peranTerisi = count($roleRows);
    @endphp
    @include('dashboard._styles')

    <div class="sa-page">
        <!-- HERO -->
        <div class="sa-hero">
            <div>
                <h1>Pantauan Umum</h1>
                <div class="sub">Ringkasan <b>{{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</b> · {{ now()->format('d M Y') }}</div>
                @if($roleChips->isNotEmpty())
                <div class="sa-chips">
                    @foreach($roleChips as $r)
                    <span class="sa-chip"><span class="sa-dot" style="background:#0f172a;"></span>{{ $r }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="sa-actions">
                @can('buka-menu-kelola-akun')
                <a href="{{ route('kelola-akun.index') }}" class="sa-btn sa-btn-primary">🛡️ Kelola Akun</a>
                @endcan
                <a href="{{ route('sr.dashboard') }}" class="sa-btn sa-btn-ghost">📊 Dashboard Karakter</a>
            </div>
        </div>

        <!-- METRIK -->
        <div class="sa-grid-metrics">
            <div class="sa-metric"><div class="lbl">Akun Terdaftar</div><div class="val">{{ $totalAkun }} <small>seluruh peran</small></div></div>
            <div class="sa-metric"><div class="lbl">Siswa Aktif</div><div class="val">{{ $siswa['aktif'] }}</div></div>
            <div class="sa-metric"><div class="lbl">Guru</div><div class="val">{{ $roleRows['Guru'] ?? 0 }} <small>akun</small></div></div>
            <div class="sa-metric"><div class="lbl">Dokumen E-Arsip</div><div class="val">{{ $arsip['total'] }}</div></div>
        </div>

        <div class="sa-grid2">
            <!-- KIRI: AKTIVITAS POIN -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>⚡ Aktivitas Poin Terbaru</h2>
                    <a class="lnk" href="{{ route('sr.dashboard') }}">Lihat rekap →</a>
                </div>
                <div class="sa-card-b">
                    @forelse($aktivitas as $a)
                    <div class="sa-row">
                        <div class="sa-ava">{{ mb_substr(($a->nama_lengkap ?: '?'), 0, 1) }}</div>
                        <div class="t">
                            <div class="nm">{{ $a->nama_lengkap ?? 'Siswa terhapus' }}</div>
                            <div class="ds">{{ \Illuminate\Support\Str::limit($a->catatan ?? '-', 60) }}{{ $a->guru ? ' · oleh ' . $a->guru : '' }}</div>
                        </div>
                        @if(($a->poin ?? 0) >= 0)
                        <span class="sa-pill sa-pill-pos">+{{ $a->poin }}</span>
                        @else
                        <span class="sa-pill sa-pill-neg">{{ $a->poin }}</span>
                        @endif
                        <span class="sa-when">{{ \Carbon\Carbon::parse($a->created_at)->format('d M') }}</span>
                    </div>
                    @empty
                    <div class="sa-empty"><span class="big">🌱</span>Belum ada aktivitas poin tercatat.</div>
                    @endforelse
                </div>
            </div>

            <!-- KANAN: TOP GRUP + SISWA -->
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <div class="sa-card">
                    <div class="sa-card-h">
                        <h2>🏆 Top Grup Karakter</h2>
                    </div>
                    <div class="sa-card-b">
                        @forelse($topGrup as $g)
                        <div class="sa-row">
                            <span class="sa-dot" style="background:{{ $g->warna_grup }};"></span>
                            <div class="t">
                                <div class="nm">{{ $g->nama_grup }}</div>
                                <div class="ds">{{ $g->mentor ?? 'Tanpa mentor' }}</div>
                            </div>
                            <span class="sa-pill sa-pill-navy">+{{ $g->poin }}</span>
                        </div>
                        @empty
                        <div class="sa-empty"><span class="big">🏕️</span>Belum ada grup binaan aktif.</div>
                        @endforelse
                    </div>
                </div>

                <div class="sa-card">
                    <div class="sa-card-h"><h2>🎓 Siswa &amp; Dokumen</h2></div>
                    <div class="sa-card-b">
                        <div class="sa-row">
                            <div class="t"><div class="nm">Siswa Aktif</div></div>
                            <span class="sa-num-mini">{{ $siswa['aktif'] }}</span>
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Alumni</div></div>
                            <span class="sa-num-mini">{{ $siswa['alumni'] }}</span>
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Surat Masuk</div></div>
                            <span class="sa-num-mini">{{ $arsip['masuk'] }}</span>
                        </div>
                        <div class="sa-row">
                            <div class="t"><div class="nm">Surat Keluar</div></div>
                            <span class="sa-num-mini">{{ $arsip['keluar'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOMPOSISI AKUN -->
        <div class="sa-card">
            <div class="sa-card-h">
                <h2>👥 Komposisi Akun &amp; Peran</h2>
                @can('buka-menu-kelola-akun')
                <a class="lnk" href="{{ route('kelola-akun.index') }}">Atur akun →</a>
                @endcan
            </div>
            <div class="sa-card-b" style="padding:.9rem 1rem 1.1rem;">
                <div style="display:flex; flex-wrap:wrap; gap:.5rem;">
                    @foreach($roleRows as $nama => $jumlah)
                    <span class="sa-chip"><span class="sa-dot" style="background:#6366f1;"></span>{{ $nama }} · {{ $jumlah }}</span>
                    @endforeach
                    <span class="sa-chip"><span class="sa-dot" style="background:#1e3a8a;"></span>{{ $peranTerisi }} peran terisi dari 6</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
