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
                <h1>Ikhtisar Tata Usaha</h1>
                <div class="sub">Administrasi <b>{{ $pengaturan->nama_sekolah ?? 'SMA IT Arafah' }}</b> · {{ now()->format('d M Y') }}</div>
                @if($roleChips->isNotEmpty())
                <div class="sa-chips">
                    @foreach($roleChips as $r)
                    <span class="sa-chip"><span class="sa-dot" style="background:#0ea5e9;"></span>{{ $r }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="sa-actions">
                @can('buka-menu-arsip')
                <a href="{{ route('arsip.index') }}" class="sa-btn sa-btn-primary">📁 Ruang E-Arsip</a>
                @endcan
                @can('buka-menu-siswa')
                <a href="{{ route('siswa.index') }}" class="sa-btn sa-btn-ghost">🎓 Database Siswa</a>
                @endcan
            </div>
        </div>

        <!-- METRIK -->
        <div class="sa-grid-metrics">
            <div class="sa-metric"><div class="lbl">Total Dokumen</div><div class="val">{{ $arsip['total'] }} <small>e-arsip</small></div></div>
            <div class="sa-metric"><div class="lbl">Surat Masuk</div><div class="val">{{ $arsip['masuk'] }}</div></div>
            <div class="sa-metric"><div class="lbl">Surat Keluar</div><div class="val">{{ $arsip['keluar'] }}</div></div>
            <div class="sa-metric"><div class="lbl">Surat Bulan Ini</div><div class="val">{{ $arsip['bulan_ini'] }} <small>{{ now()->format('M Y') }}</small></div></div>
        </div>

        <div class="sa-grid2">
            <!-- KIRI: KOMPOSISI SISWA -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>🎓 Komposisi Siswa</h2>
                    @can('buka-menu-siswa')
                    <a class="lnk" href="{{ route('siswa.index') }}">Database →</a>
                    @endcan
                </div>
                <div class="sa-card-b">
                    <div class="sa-row">
                        <span class="sa-ava" style="background:#eff6ff;color:#1e40af;">S</span>
                        <div class="t"><div class="nm">Total Terdaftar</div><div class="ds">semua status</div></div>
                        <span class="sa-num-mini">{{ $siswa['terdaftar'] }}</span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Siswa Aktif</div><div class="ds">status Aktif</div></div>
                        <span class="sa-num-mini">{{ $siswa['aktif'] }}</span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Kelas X</div></div>
                        <span class="sa-num-mini">{{ $siswa['x'] }}</span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Kelas XI</div></div>
                        <span class="sa-num-mini">{{ $siswa['xi'] }}</span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Kelas XII</div></div>
                        <span class="sa-num-mini">{{ $siswa['xii'] }}</span>
                    </div>
                    <div class="sa-row">
                        <div class="t"><div class="nm">Alumni</div><div class="ds">status Alumni / kelas Lulus</div></div>
                        <span class="sa-num-mini">{{ $siswa['alumni'] }}</span>
                    </div>
                </div>
            </div>

            <!-- KANAN: AKTIVITAS POIN -->
            <div class="sa-card">
                <div class="sa-card-h">
                    <h2>⚡ Aktivitas Poin Terbaru</h2>
                    <a class="lnk" href="{{ route('sr.dashboard') }}">Rekap →</a>
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
        </div>

        <!-- PINTASAN KELOLA -->
        <div class="sa-actions">
            @can('buka-menu-kelola-akun')
            <a href="{{ route('kelola-akun.index') }}" class="sa-btn sa-btn-ghost">🛡️ Kelola Akun</a>
            @endcan
            @can('buka-menu-pengaturan')
            <a href="{{ route('pengaturan.edit') }}" class="sa-btn sa-btn-ghost">⚙️ Pengaturan Lembaga</a>
            @endcan
            @can('buka-menu-master-student-root')
            <a href="{{ route('sr.kriteria.index') }}" class="sa-btn sa-btn-ghost">⚙️ Master Kriteria</a>
            <a href="{{ route('sr.display.setting') }}" class="sa-btn sa-btn-ghost">📺 Pengaturan Display TV</a>
            @endcan
        </div>
    </div>
</x-app-layout>
