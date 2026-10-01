{{-- Token & komponen gaya "satu bahasa desain" (kalibrasi 2026-09, selaras Pusat Kendali). Dipakai seluruh dashboard peran. --}}
<style>
    /* ===== TOKEN (kalibrasi 2026-09) ===== */
    .sa-page { --navy:#1e3a8a; --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --bg:#f8fafc; }
    .sa-page {
        max-width: 1180px; margin: 0 auto; padding: 2rem 1.25rem 4rem;
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; color: var(--ink);
    }
    .sa-page * { box-sizing: border-box; }
    .sa-hero { display:flex; flex-wrap:wrap; gap:1rem; justify-content:space-between; align-items:flex-end; margin-bottom:1.75rem; }
    .sa-hero h1 { font-size:1.6rem; font-weight:800; letter-spacing:-0.02em; margin:0 0 .25rem; }
    .sa-hero .sub { color:var(--muted); font-size:.9rem; }
    .sa-hero .sub b { color:var(--ink); font-weight:700; }
    .sa-chips { display:flex; gap:.4rem; flex-wrap:wrap; margin-top:.6rem; }
    .sa-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
    .sa-btn { display:inline-flex; align-items:center; gap:.4rem; font-weight:600; font-size:.85rem;
              padding:.55rem 1rem; border-radius:9px; text-decoration:none; transition:.15s; border:1px solid transparent; }
    .sa-btn-primary { background:var(--navy); color:#fff; box-shadow:0 1px 2px rgba(15,23,42,.25); }
    .sa-btn-primary:hover { background:#1e40af; }
    .sa-btn-ghost { background:#fff; color:#334155; border-color:var(--line); }
    .sa-btn-ghost:hover { border-color:#cbd5e1; background:#f1f5f9; }
    .sa-grid-metrics { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:1rem; margin-bottom:1.5rem; }
    .sa-metric { background:#fff; border:1px solid var(--line); border-radius:14px; padding:1.1rem 1.25rem; }
    .sa-metric .lbl { font-size:.68rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:var(--muted); }
    .sa-metric .val { font-size:1.9rem; font-weight:800; letter-spacing:-0.02em; margin-top:.35rem; color:var(--ink); }
    .sa-metric .val small { font-size:.85rem; font-weight:600; color:var(--muted); }
    .sa-grid2 { display:grid; grid-template-columns: 1.45fr 1fr; gap:1rem; margin-bottom:1.5rem; }
    .sa-card { background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
    .sa-card-h { padding:1rem 1.25rem; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
    .sa-card-h h2 { font-size:.95rem; font-weight:700; margin:0; }
    .sa-card-h .lnk { font-size:.78rem; font-weight:600; color:var(--navy); text-decoration:none; }
    .sa-card-b { padding:.5rem .75rem; }
    .sa-row { display:flex; align-items:center; gap:.8rem; padding:.7rem .5rem; border-radius:10px; }
    .sa-row:hover { background:#f8fafc; }
    .sa-ava { width:36px; height:36px; border-radius:10px; background:#eef2ff; color:#3730a3; font-weight:800;
              display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
    .sa-row .t { flex:1; min-width:0; }
    .sa-row .t .nm { font-weight:700; font-size:.86rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sa-row .t .ds { font-size:.76rem; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sa-pill { font-size:.72rem; font-weight:800; padding:.22rem .55rem; border-radius:999px; flex-shrink:0; }
    .sa-pill-pos { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .sa-pill-neg { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .sa-pill-soft { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
    .sa-pill-blue { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .sa-pill-amber { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
    .sa-pill-navy { background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
    .sa-when { font-size:.7rem; color:#94a3b8; flex-shrink:0; }
    .sa-chip { display:inline-flex; align-items:center; gap:.45rem; background:#f8fafc; border:1px solid var(--line);
               border-radius:999px; padding:.35rem .8rem; font-size:.8rem; font-weight:600; color:#334155; }
    .sa-dot { width:8px; height:8px; border-radius:99px; background:#10b981; flex-shrink:0; }
    .sa-dot-ok { background:#10b981; }
    .sa-empty { text-align:center; color:var(--muted); font-size:.85rem; padding:1.6rem 1rem; }
    .sa-empty .big { font-size:1.5rem; display:block; margin-bottom:.35rem; }
    .sa-num-mini { font-weight:800; font-size:1.05rem; }
    .sa-num-mini small { font-size:.72rem; font-weight:700; color:var(--muted); margin-left:.15rem; }
    .sa-row-num { margin-left:auto; text-align:right; flex-shrink:0; }
    .sa-arrow { color:var(--navy); font-weight:800; text-decoration:none; }
    @media (max-width: 940px) { .sa-grid2 { grid-template-columns:1fr; } }
    @media (max-width: 640px) { .sa-page { padding:1.25rem 1rem 6rem; } .sa-hero h1 { font-size:1.35rem; } }
</style>
