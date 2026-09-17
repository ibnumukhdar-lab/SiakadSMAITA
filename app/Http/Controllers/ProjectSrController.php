<?php

namespace App\Http\Controllers;

use App\Models\PenilaianPengaturan;
use App\Models\Siswa;
use App\Models\SrGroup;
use App\Models\SrProject;
use App\Models\SrProjectDokumen;
use App\Models\SrProjectNilai;
use App\Models\SrProjectPortofolio;
use App\Models\SrProjectTahap;
use App\Models\SrProjectTahapStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Project Student Root — penilaian 5 tahap per siswa.
 *
 * Jumlah project bebas per grup: mentor menambah project sesuai kebutuhan grupnya.
 * Status tahap diatur di level project (belum/berjalan/selesai/tidak dipakai),
 * nilai diisi per siswa per tahap (0-100) lalu dirata-ratakan berbobot.
 */
class ProjectSrController extends Controller
{
    // ---------------- Daftar project ----------------

    public function index(Request $request)
    {
        $user = Auth::user();
        $bolehSemua = $this->bolehLihatSemua();

        $filter = [
            'grup_id' => trim((string) $request->input('grup_id', '')),
            'status' => in_array($request->input('status'), array_keys(SrProject::STATUS), true) ? $request->input('status') : '',
            'q' => trim((string) $request->input('q', '')),
        ];

        $query = SrProject::with(['grup', 'mentor'])->aktif();

        if (! $bolehSemua) {
            $query->milikMentor($user->id);
        } elseif ($filter['grup_id'] !== '') {
            $query->where('grup_id', $filter['grup_id']);
        }

        if ($filter['status'] !== '') {
            $query->where('status', $filter['status']);
        }
        if ($filter['q'] !== '') {
            $query->where('nama', 'like', '%' . $filter['q'] . '%');
        }

        $projects = $query->orderByDesc('id')->paginate(12)->withQueryString();

        // Ringkasan nilai tiap project (hanya yang lengkap dihitung sebagai nilai akhir)
        $ringkas = [];
        foreach ($projects as $p) {
            $nilai = $p->nilaiPerSiswa();
            $lengkap = array_filter($nilai, fn ($n) => ($n['rata'] ?? null) !== null && ($n['lengkap'] ?? false));
            $sebagian = array_filter($nilai, fn ($n) => ($n['rata'] ?? null) !== null && ! ($n['lengkap'] ?? false));
            $angka = array_column($lengkap, 'rata');
            $ringkas[$p->id] = [
                'dinilai' => count($lengkap),
                'sebagian' => count($sebagian),
                'anggota' => $p->anggota()->count(),
                'rata' => $angka ? round(array_sum($angka) / count($angka), 2) : null,
                'progres' => $p->progresTahap(),
                'tuntas' => $p->tuntas(),
                'dokumen' => $p->jumlahDokumen(),
                'portofolio' => (bool) $p->portofolio,
            ];
        }

        $grupQuery = SrGroup::with('mentor')->orderBy('nama_grup');
        if (! $bolehSemua) {
            $grupQuery->where('mentor_id', $user->id);
        }

        return view('project-sr.index', [
            'projects' => $projects,
            'ringkas' => $ringkas,
            'filter' => $filter,
            'daftarGrup' => $grupQuery->get(),
            'bolehSemua' => $bolehSemua,
            'bolehTambah' => $user->can('kelola-project-sr') || $user->hasRole('Super Admin'),
            'bolehMaster' => $user->can('kelola-master-project-sr') || $user->hasRole('Super Admin'),
            'grupSaya' => SrGroup::where('mentor_id', $user->id)->count(),
            'jumlahTahap' => SrProjectTahap::daftarAktif()->count(),
            'bobotTotal' => SrProjectTahap::bobotTotal(),
        ]);
    }

    // ---------------- Tambah / ubah / hapus project ----------------

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin'), 403, 'Hanya mentor grup (Guru) yang boleh membuat project.');
        $data = $this->validasiProject($request);

        if (! $this->bolehKelolaGrup($data['grup_id'])) {
            return redirect()->route('project-sr.index')
                ->with('error', 'Project hanya boleh dibuat oleh mentor grup tersebut. Pilih grup binaan Anda sendiri.');
        }

        $grup = SrGroup::find($data['grup_id']);

        $project = SrProject::create($data + [
            'mentor_id' => $grup?->mentor_id,
            'dibuat_oleh' => Auth::id(),
            'aktif' => true,
        ]);

        return redirect()->route('project-sr.show', $project->id)
            ->with('success', 'Project "' . $project->nama . '" ditambahkan. Silakan isi status tahap dan nilainya.');
    }

    public function update(Request $request, $id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless(Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin'), 403);

        if (! $this->mentorGrup($project)) {
            return redirect()->route('project-sr.show', $project->id)
                ->with('error', 'Project ini milik grup lain — hanya mentornya yang boleh mengubah.');
        }

        $data = $this->validasiProject($request, $project);

        if (! $this->bolehKelolaGrup($data['grup_id'])) {
            return redirect()->route('project-sr.show', $project->id)
                ->with('error', 'Tidak bisa memindahkan project ke grup yang bukan binaan Anda.');
        }

        // Mentor project SELALU mentor grupnya (tidak bisa diisi/diganti orang lain).
        $data['mentor_id'] = SrGroup::find($data['grup_id'])?->mentor_id;

        $project->update($data);

        return redirect()->route('project-sr.show', $project->id)->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless(Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin'), 403);

        if (! $this->mentorGrup($project)) {
            return redirect()->route('project-sr.show', $project->id)
                ->with('error', 'Project ini milik grup lain — hanya mentornya yang boleh menghapus.');
        }

        $nama = $project->nama;
        $jumlahNilai = $project->nilai()->whereNotNull('skor')->count();

        DB::transaction(function () use ($project) {
            // Hapus berkas foto/dokumentasi dari storage
            foreach ($project->dokumen()->get() as $dokumen) {
                if ($dokumen->path && Storage::disk('public')->exists($dokumen->path)) {
                    Storage::disk('public')->delete($dokumen->path);
                }
                $dokumen->delete();
            }

            $project->portofolio()->delete();
            $project->statusTahap()->delete();
            $project->nilai()->delete();
            $project->delete();
        });

        return redirect()->route('project-sr.index')
            ->with('success', 'Project "' . $nama . '" dihapus' . ($jumlahNilai > 0 ? ' beserta ' . $jumlahNilai . ' nilainya' : '') . '.');
    }

    // ---------------- Portofolio (setelah 5 tahap tuntas) ----------------

    /** Halaman penyusunan portofolio: daftar project yang sudah tuntas tahapannya. */
    public function portofolioIndex()
    {
        $bolehSemua = $this->bolehLihatSemua();

        $query = SrProject::with(['grup', 'mentor', 'portofolio'])->aktif();
        if (! $bolehSemua) {
            $query->milikMentor(Auth::id());
        }

        $siap = $query->orderByDesc('id')->get()
            ->filter(fn ($p) => $p->tuntas())
            ->map(function ($p) {
                return [
                    'project' => $p,
                    'dokumen' => $p->jumlahDokumen(),
                    'narasi' => $p->portofolio?->bagianTerisi() ?? 0,
                    'rata' => $p->rataProject(),
                ];
            });

        return view('project-sr.portofolio-index', [
            'daftar' => $siap,
            'bolehSemua' => $bolehSemua,
            'bolehSusun' => Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin'),
        ]);
    }

    /** Halaman penyusunan portofolio satu project. */
    public function portofolio($id)
    {
        $project = SrProject::with(['grup', 'mentor'])->findOrFail($id);
        abort_unless($this->bolehLihatProject($project), 403);

        $tuntas = $project->tuntas();
        abort_unless($tuntas || Auth::user()->hasRole('Super Admin'), 403, 'Portofolio disusun setelah seluruh tahap project selesai.');

        return view('project-sr.portofolio', [
            'project' => $project,
            'tahap' => SrProjectTahap::daftarAktif(),
            'status' => $project->statusTahap()->get()->keyBy('tahap_id'),
            'anggota' => $project->anggota(),
            'nilaiSiswa' => $project->nilaiPerSiswa(),
            'tahapRata' => $this->rataPerTahap($project),
            'portofolio' => $project->portofolio ?? new SrProjectPortofolio(['project_id' => $project->id]),
            'dokumen' => $project->dokumen()->get(),
            'rataProject' => $project->rataProject(),
            'bolehSusun' => $this->bolehSusunPortofolio($project),
            'ambang' => PenilaianPengaturan::ambang(),
        ]);
    }

    /** Simpan narasi + unggahan foto portofolio. */
    public function portofolioSimpan(Request $request, $id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless($this->bolehSusunPortofolio($project), 403, 'Hanya mentor grup ini yang boleh menyusun portofolio.');

        $data = $request->validate([
            'ringkasan' => ['nullable', 'string', 'max:3000'],
            'latar_belakang' => ['nullable', 'string', 'max:3000'],
            'tujuan' => ['nullable', 'string', 'max:3000'],
            'pelaksanaan' => ['nullable', 'string', 'max:3000'],
            'hasil' => ['nullable', 'string', 'max:3000'],
            'refleksi' => ['nullable', 'string', 'max:3000'],
            'tempat' => ['nullable', 'string', 'max:100'],
            'tanggal_presentasi' => ['nullable', 'date'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,heic,heif,pdf', 'max:5120'],
            'keterangan_foto' => ['nullable', 'array'],
            'keterangan_foto.*' => ['nullable', 'string', 'max:255'],
        ], [
            'foto.*.mimes' => 'Berkas harus berupa gambar (jpg, png, webp, gif) atau PDF.',
            'foto.*.max' => 'Ukuran tiap berkas maksimal 5 MB.',
        ]);

        $portofolio = SrProjectPortofolio::firstOrNew(['project_id' => $project->id]);

        $portofolio->fill([
            'ringkasan' => $data['ringkasan'] ?? null,
            'latar_belakang' => $data['latar_belakang'] ?? null,
            'tujuan' => $data['tujuan'] ?? null,
            'pelaksanaan' => $data['pelaksanaan'] ?? null,
            'hasil' => $data['hasil'] ?? null,
            'refleksi' => $data['refleksi'] ?? null,
            'tempat' => $data['tempat'] ?? null,
            'tanggal_presentasi' => $data['tanggal_presentasi'] ?? null,
            'disusun_oleh' => Auth::id(),
        ]);
        $portofolio->save();

        $jumlahFoto = 0;
        $berkas = $request->file('foto') ?? [];
        $keterangan = $request->input('keterangan_foto') ?? [];
        $urutan = (int) $project->dokumen()->max('urutan');

        foreach ($berkas as $i => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('project_sr/' . now()->format('Y/m'), 'public');

            SrProjectDokumen::create([
                'project_id' => $project->id,
                'path' => $path,
                'nama_asli' => $file->getClientOriginalName(),
                'keterangan' => trim((string) ($keterangan[$i] ?? '')) ?: null,
                'urutan' => ++$urutan,
                'diunggah_oleh' => Auth::id(),
            ]);
            $jumlahFoto++;
        }

        // Tandai selesai bila narasi inti terisi dan sudah ada foto
        $punyaFoto = $project->jumlahDokumen() > 0;
        $portofolio->diselesaikan_pada = ($punyaFoto && $portofolio->bagianTerisi() >= 4)
            ? ($portofolio->diselesaikan_pada ?? now())
            : null;
        $portofolio->save();

        return redirect()->route('project-sr.portofolio', $project->id)
            ->with('success', 'Portofolio tersimpan' . ($jumlahFoto > 0 ? ' + ' . $jumlahFoto . ' berkas diunggah' : '') . '.');
    }

    public function dokumenHapus($id, $dokumenId)
    {
        $project = SrProject::findOrFail($id);
        abort_unless($this->bolehSusunPortofolio($project), 403);

        $dokumen = SrProjectDokumen::where('project_id', $project->id)->findOrFail($dokumenId);

        if ($dokumen->path && Storage::disk('public')->exists($dokumen->path)) {
            Storage::disk('public')->delete($dokumen->path);
        }
        $dokumen->delete();

        $portofolio = $project->portofolio;
        if ($portofolio && $project->jumlahDokumen() === 0) {
            $portofolio->update(['diselesaikan_pada' => null]);
        }

        return redirect()->route('project-sr.portofolio', $project->id)->with('success', 'Foto/dokumentasi dihapus.');
    }

    /** Halaman cetak portofolio (siap Ctrl+P / PDF). */
    public function portofolioCetak($id)
    {
        $project = SrProject::with(['grup', 'mentor'])->findOrFail($id);
        abort_unless($this->bolehLihatProject($project), 403);
        abort_unless($project->jumlahDokumen() > 0, 403, 'Unggah foto/dokumentasi terlebih dahulu sebelum mencetak portofolio.');

        return view('project-sr.portofolio-cetak', [
            'project' => $project,
            'tahap' => SrProjectTahap::daftarAktif(),
            'status' => $project->statusTahap()->get()->keyBy('tahap_id'),
            'anggota' => $project->anggota(),
            'nilaiSiswa' => $project->nilaiPerSiswa(),
            'tahapRata' => $this->rataPerTahap($project),
            'portofolio' => $project->portofolio,
            'dokumen' => $project->dokumen()->get(),
            'rataProject' => $project->rataProject(),
            'jumlahTuntas' => $project->nilaiPerSiswa() ? count(array_filter($project->nilaiPerSiswa(), fn ($n) => ($n['lengkap'] ?? false))) : 0,
            'pengaturan' => \App\Models\Pengaturan::first(),
        ]);
    }

    /** Rata-rata tiap tahap untuk satu project: [tahap_id => rata]. */
    private function rataPerTahap(SrProject $project): array
    {
        $hasil = [];
        $nilai = $project->nilaiPerSiswa();

        foreach (SrProjectTahap::daftarAktif() as $t) {
            $kumpulan = [];
            foreach ($nilai as $perSiswa) {
                $skor = $perSiswa['per_tahap'][$t->id] ?? null;
                if ($skor !== null) {
                    $kumpulan[] = $skor;
                }
            }
            $hasil[$t->id] = $kumpulan ? round(array_sum($kumpulan) / count($kumpulan), 2) : null;
        }

        return $hasil;
    }

    private function bolehSusunPortofolio(SrProject $project): bool
    {
        return (Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin')) && $this->mentorGrup($project);
    }

    // ---------------- Detail project + penilaian ----------------

    public function show($id)
    {
        $project = SrProject::with(['grup', 'mentor', 'pembuat'])->findOrFail($id);
        abort_unless($this->bolehLihatProject($project), 403);

        $tahap = SrProjectTahap::daftarAktif();
        $status = $project->statusTahap()->get()->keyBy('tahap_id');
        $nilaiSiswa = $project->nilaiPerSiswa();
        $anggota = $project->anggota();
        $bolehNilai = (Auth::user()->can('nilai-project-sr') || Auth::user()->hasRole('Super Admin')) && $this->mentorGrup($project);

        // Peta nilai mentah untuk prefill grid: [siswa_id][tahap_id] => skor/catatan
        $grid = [];
        foreach ($project->nilai()->get() as $n) {
            $grid[$n->siswa_id][$n->tahap_id] = ['skor' => $n->skor, 'catatan' => $n->catatan];
        }

        $angka = array_filter(array_column($nilaiSiswa, 'rata'), fn ($v) => $v !== null);

        return view('project-sr.show', [
            'project' => $project,
            'tahap' => $tahap,
            'status' => $status,
            'anggota' => $anggota,
            'nilaiSiswa' => $nilaiSiswa,
            'grid' => $grid,
            'bolehNilai' => $bolehNilai,
            'bolehUbah' => Auth::user()->can('kelola-project-sr') || Auth::user()->hasRole('Super Admin'),
            'rataProject' => $angka ? round(array_sum($angka) / count($angka), 2) : null,
            'jumlahDinilai' => count($angka),
            'bobotTerpakai' => $project->bobotTerpakai(),
            'tuntas' => $project->tuntas(),
            'jumlahDokumen' => $project->jumlahDokumen(),
            'jumlahNilai' => $project->nilai()->whereNotNull('skor')->count(),
            'ambang' => PenilaianPengaturan::ambang(),
        ]);
    }

    /** Simpan nilai grid siswa x tahap. */
    public function simpanNilai(Request $request, $id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless(Auth::user()->can('nilai-project-sr') || Auth::user()->hasRole('Super Admin'), 403, 'Anda tidak punya izin menilai project.');
        abort_unless($this->mentorGrup($project), 403, 'Project ini bukan binaan Anda.');

        $tahap = SrProjectTahap::daftarAktif();
        $anggotaIds = $project->anggota()->pluck('id')->all();

        $aturan = ['skor' => ['array']];
        foreach ($anggotaIds as $siswaId) {
            foreach ($tahap as $t) {
                $aturan["skor.$siswaId.{$t->id}"] = ['nullable', 'integer', 'min:0', 'max:100'];
            }
        }
        $aturan['catatan'] = ['array'];
        $aturan['catatan.*'] = ['nullable', 'string', 'max:255'];

        $request->validate($aturan, [], []);

        $jumlah = 0;
        DB::transaction(function () use ($request, $project, $tahap, $anggotaIds, &$jumlah) {
            foreach ($anggotaIds as $siswaId) {
                $kunciCatatan = "catatan.$siswaId";
                $adaCatatan = $request->has($kunciCatatan);
                $catatanSiswa = $request->input($kunciCatatan);
                $adaIsian = false;

                foreach ($tahap as $t) {
                    $kunci = "skor.$siswaId.{$t->id}";

                    // Penting: field yang TIDAK dikirim pada request ini jangan diubah,
                    // supaya menyimpan satu siswa tidak menghapus nilai siswa lain.
                    if ($request->has($kunci)) {
                        $adaIsian = true;
                    }
                }

                if (! $adaIsian && ! $adaCatatan) {
                    continue;
                }

                foreach ($tahap as $t) {
                    $kunci = "skor.$siswaId.{$t->id}";
                    $baris = SrProjectNilai::firstOrNew([
                        'project_id' => $project->id,
                        'siswa_id' => $siswaId,
                        'tahap_id' => $t->id,
                    ]);

                    if ($request->has($kunci)) {
                        $skor = $request->input($kunci);
                        $baris->skor = ($skor === null || $skor === '') ? null : (int) $skor;
                        if ($baris->skor !== null) {
                            $baris->dinilai_oleh = Auth::id();
                        }
                    }

                    if ($adaCatatan) {
                        $baris->catatan = $catatanSiswa;
                    }

                    if (! $baris->exists && $baris->skor === null && ! $baris->catatan) {
                        continue;   // jangan simpan baris kosong
                    }

                    $baris->save();
                    $jumlah++;
                }
            }
        });

        $this->selaraskanStatusProject($project);

        return redirect()->route('project-sr.show', $project->id)
            ->with('success', 'Nilai project "' . $project->nama . '" tersimpan (' . $jumlah . ' baris).');
    }

    /** Ubah status satu tahap (progres project). */
    public function simpanTahap(Request $request, $id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless(Auth::user()->hasRole('Super Admin') || $this->mentorGrup($project), 403, 'Project ini bukan binaan Anda.');

        $data = $request->validate([
            'tahap_id' => ['required', 'integer', 'exists:sr_project_tahap,id'],
            'status' => ['required', Rule::in(array_keys(SrProjectTahap::STATUS_TAHAP))],
            'tanggal' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        SrProjectTahapStatus::updateOrCreate(
            ['project_id' => $project->id, 'tahap_id' => $data['tahap_id']],
            ['status' => $data['status'], 'tanggal' => $data['tanggal'] ?? null, 'catatan' => $data['catatan'] ?? null]
        );

        $this->selaraskanStatusProject($project);

        return redirect()->route('project-sr.show', $project->id)->with('success', 'Status tahap diperbarui.');
    }

    /** Isi satu nilai tahap untuk semua anggota sekaligus (bantuan cepat). */
    public function nilaiRata(Request $request, $id)
    {
        $project = SrProject::findOrFail($id);
        abort_unless(Auth::user()->can('nilai-project-sr') || Auth::user()->hasRole('Super Admin'), 403);
        abort_unless($this->mentorGrup($project), 403);

        $data = $request->validate([
            'tahap_id' => ['required', 'integer', 'exists:sr_project_tahap,id'],
            'skor' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $anggotaIds = $project->anggota()->pluck('id')->all();

        DB::transaction(function () use ($project, $data, $anggotaIds) {
            foreach ($anggotaIds as $siswaId) {
                $baris = SrProjectNilai::firstOrNew([
                    'project_id' => $project->id,
                    'siswa_id' => $siswaId,
                    'tahap_id' => $data['tahap_id'],
                ]);
                $baris->skor = (int) $data['skor'];
                $baris->dinilai_oleh = Auth::id();
                $baris->save();
            }
        });

        return redirect()->route('project-sr.show', $project->id)
            ->with('success', 'Nilai tahap diisi ' . $data['skor'] . ' untuk ' . count($anggotaIds) . ' anggota. Silakan koreksi per siswa bila berbeda.');
    }

    // ---------------- Rekap ----------------

    public function rekap(Request $request)
    {
        $bolehSemua = $this->bolehLihatSemua();
        $filter = [
            'grup_id' => trim((string) $request->input('grup_id', '')),
            'q' => trim((string) $request->input('q', '')),
        ];

        $grupQuery = SrGroup::with('mentor')->orderBy('nama_grup');
        if (! $bolehSemua) {
            $grupQuery->where('mentor_id', Auth::id());
        }
        $daftarGrup = $grupQuery->get();

        $grupTerpilih = $filter['grup_id'] !== ''
            ? $daftarGrup->firstWhere('id', $filter['grup_id'])
            : $daftarGrup->first();

        $projects = collect();
        if ($grupTerpilih) {
            $projects = SrProject::aktif()->where('grup_id', $grupTerpilih->id)->orderBy('id')->get();
        }

        // Data per siswa: nilai per project + nilai akhir (rata-rata seluruh project)
        $baris = collect();
        if ($grupTerpilih) {
            $anggota = DB::table('sr_group_members as m')
                ->join('siswas as s', 's.id', '=', 'm.student_id')
                ->where('m.group_id', $grupTerpilih->id)
                ->whereNull('m.tanggal_keluar')
                ->whereNull('s.deleted_at')
                ->orderBy('s.nama_lengkap')
                ->select('s.id', 's.nama_lengkap', 's.nisn', 's.kelas')
                ->get();

            $nilaiPerProject = [];
            $tahapPerProject = [];
            foreach ($projects as $p) {
                $nilaiPerProject[$p->id] = $p->nilaiPerSiswa();
                $tahapPerProject[$p->id] = $p->bobotTerpakai();
            }

            $tahap = SrProjectTahap::daftarAktif();

            foreach ($anggota as $s) {
                if ($filter['q'] !== '' && stripos($s->nama_lengkap, $filter['q']) === false && stripos((string) $s->nisn, $filter['q']) === false) {
                    continue;
                }

                $perProject = [];
                $angka = [];
                $projectDilaksanakan = 0;
                $projectSebagian = 0;

                foreach ($projects as $p) {
                    $n = $nilaiPerProject[$p->id][$s->id] ?? null;
                    $perProject[$p->id] = $n;
                    if ($n && $n['rata'] !== null) {
                        if ($n['lengkap'] ?? false) {
                            $angka[] = $n['rata'];
                            $projectDilaksanakan++;
                        } else {
                            $projectSebagian++;
                        }
                    }
                }

                // rata-rata per tahap (untuk melihat tahap mana yang kuat/lemah)
                $perTahap = [];
                foreach ($tahap as $t) {
                    $kumpulan = [];
                    foreach ($projects as $p) {
                        $skor = $nilaiPerProject[$p->id][$s->id]['per_tahap'][$t->id] ?? null;
                        if ($skor !== null) {
                            $kumpulan[] = $skor;
                        }
                    }
                    $perTahap[$t->id] = $kumpulan ? round(array_sum($kumpulan) / count($kumpulan), 2) : null;
                }

                $rata = $angka ? round(array_sum($angka) / count($angka), 2) : null;

                $baris->push([
                    'id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'nisn' => $s->nisn,
                    'kelas' => $s->kelas,
                    'per_project' => $perProject,
                    'per_tahap' => $perTahap,
                    'jumlah_project' => count($projects),
                    'project_dilaksanakan' => $projectDilaksanakan,
                    'project_sebagian' => $projectSebagian,
                    'rata' => $rata,
                    'predikat' => PenilaianPengaturan::predikat($rata),
                ]);
            }
        }

        return view('project-sr.rekap', [
            'daftarGrup' => $daftarGrup,
            'grupTerpilih' => $grupTerpilih,
            'projects' => $projects,
            'baris' => $baris,
            'filter' => $filter,
            'tahap' => SrProjectTahap::daftarAktif(),
            'bolehSemua' => $bolehSemua,
            'ambang' => PenilaianPengaturan::ambang(),
            'bobotTotal' => SrProjectTahap::bobotTotal(),
        ]);
    }

    public function ekspor(Request $request)
    {
        $rekap = $this->rekap($request);
        $grup = $rekap['grupTerpilih'];
        $projects = $rekap['projects'];
        $tahap = $rekap['tahap'];
        $baris = $rekap['baris'];

        $namaBerkas = 'rekap-project-' . preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($grup->nama_grup ?? 'grup')) . '.csv';

        return response()->streamDownload(function () use ($projects, $tahap, $baris) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");

            $header = ['NISN', 'Nama Siswa', 'Kelas'];
            foreach ($projects as $p) {
                $header[] = 'Project: ' . $p->nama . ' (%)';
            }
            foreach ($tahap as $t) {
                $header[] = 'Rata ' . $t->nama . ' (%)';
            }
            $header[] = 'Nilai Akhir (%)';
            $header[] = 'Predikat';
            $header[] = 'Project Dilaksanakan';
            $header[] = 'Project Belum Lengkap';
            fputcsv($keluaran, $header);

            foreach ($baris as $r) {
                $kolom = [$r['nisn'], $r['nama'], $r['kelas']];
                foreach ($projects as $p) {
                    $kolom[] = $r['per_project'][$p->id]['rata'] ?? '';
                }
                foreach ($tahap as $t) {
                    $kolom[] = $r['per_tahap'][$t->id] ?? '';
                }
                $kolom[] = $r['rata'] ?? '';
                $kolom[] = $r['predikat'] ?? '';
                $kolom[] = $r['project_dilaksanakan'] . '/' . $r['jumlah_project'];
                $kolom[] = $r['project_sebagian'] ?? 0;
                fputcsv($keluaran, $kolom);
            }

            fclose($keluaran);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------------- Master tahap ----------------

    public function master()
    {
        abort_unless(Auth::user()->can('kelola-master-project-sr') || Auth::user()->hasRole('Super Admin'), 403);

        $tahap = SrProjectTahap::terurut()->get();
        $jumlahProject = SrProject::aktif()->count();
        $jumlahNilai = SrProjectNilai::whereNotNull('skor')->count();

        return view('project-sr.master', compact('tahap', 'jumlahProject', 'jumlahNilai'));
    }

    public function masterUpdate(Request $request, $id)
    {
        abort_unless(Auth::user()->can('kelola-master-project-sr') || Auth::user()->hasRole('Super Admin'), 403);

        $tahap = SrProjectTahap::findOrFail($id);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'panduan' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'aktif' => ['boolean'],
        ]);

        $data['aktif'] = $request->boolean('aktif');
        $tahap->update($data);

        return redirect()->route('project-sr.master')->with('success', 'Tahap "' . $tahap->nama . '" diperbarui.');
    }

    public function masterSimpanSemua(Request $request)
    {
        abort_unless(Auth::user()->can('kelola-master-project-sr') || Auth::user()->hasRole('Super Admin'), 403);

        $data = $request->validate([
            'bobot' => ['required', 'array'],
            'bobot.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['bobot'] as $id => $bobot) {
                if ($bobot === null || $bobot === '') {
                    continue;
                }
                SrProjectTahap::where('id', (int) $id)->update(['bobot' => (float) $bobot]);
            }
        });

        return redirect()->route('project-sr.master')
            ->with('success', 'Bobot tahap diperbarui (total sekarang ' . SrProjectTahap::bobotTotal() . '%).');
    }

    // ---------------- Bantuan ----------------

    private function bolehLihatSemua(): bool
    {
        $user = Auth::user();

        return $user->hasRole('Super Admin') || $user->can('buka-menu-master-student-root') || $user->hasRole('Tata Usaha');
    }

    private function mentorGrup(SrProject $project): bool
    {
        $user = Auth::user();

        // Hanya Super Admin yang boleh bertindak pada project grup mana pun (jaring pengaman teknis).
        // Pembuatan & penilaian project adalah tugas MENTOR grup (Guru pengampu grup itu).
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return (int) ($project->grup->mentor_id ?? 0) === (int) $user->id;
    }

    private function bolehLihatProject(SrProject $project): bool
    {
        $user = Auth::user();

        if (! $user->can('buka-menu-project-sr') && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $this->mentorGrup($project) || $this->bolehLihatSemua();
    }

    private function bolehKelolaGrup($grupId): bool
    {
        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        $grup = SrGroup::find($grupId);

        return $grup && (int) $grup->mentor_id === (int) $user->id;
    }

    /** Kalau seluruh tahap yang dipakai sudah selesai → project otomatis berstatus selesai. */
    private function selaraskanStatusProject(SrProject $project): void
    {
        $status = $project->statusTahap()->get()->keyBy('tahap_id');
        $dipakai = 0;
        $selesai = 0;

        foreach (SrProjectTahap::daftarAktif() as $tahap) {
            if (($status[$tahap->id]->status ?? 'belum') === 'tidak_dipakai') {
                continue;
            }
            $dipakai++;
            if (($status[$tahap->id]->status ?? 'belum') === 'selesai') {
                $selesai++;
            }
        }

        $baru = $project->status;
        if ($dipakai > 0 && $selesai >= $dipakai) {
            $baru = 'selesai';
        } elseif ($selesai > 0 || $project->nilai()->whereNotNull('skor')->exists()) {
            $baru = 'berjalan';
        }

        if ($baru !== $project->status) {
            $project->update(['status' => $baru]);
        }
    }

    private function validasiProject(Request $request, ?SrProject $project = null): array
    {
        $request->merge([
            'nama' => trim((string) $request->input('nama')),
            'deskripsi' => trim((string) $request->input('deskripsi')) ?: null,
        ]);

        $data = $request->validate([
            'grup_id' => ['required', 'string', 'exists:sr_groups,id'],
            'nama' => ['required', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(array_keys(SrProject::STATUS))],
        ], [
            'nama.required' => 'Nama project wajib diisi.',
            'grup_id.required' => 'Grup Student Root wajib dipilih.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        return $data;
    }
}
