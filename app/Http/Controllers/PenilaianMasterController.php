<?php

namespace App\Http\Controllers;

use App\Models\PenilaianJawaban;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPengaturan;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Master Penilaian: pertanyaan (kriteria) Adab & Keasramaan, periode penilaian,
 * dan ambang predikat A/B/C/D.
 */
class PenilaianMasterController extends Controller
{
    public function index()
    {
        $kriteria = PenilaianKriteria::terurut()->get()->groupBy('jenis');
        $periode = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $ambang = PenilaianPengaturan::ambang();

        $jumlahJawaban = PenilaianJawaban::query()
            ->selectRaw('kriteria_id, COUNT(*) as jumlah')
            ->groupBy('kriteria_id')
            ->pluck('jumlah', 'kriteria_id')
            ->toArray();

        return view('penilaian.master', compact('kriteria', 'periode', 'ambang', 'jumlahJawaban'));
    }

    // ---------------- Pertanyaan ----------------

    public function kriteriaStore(Request $request)
    {
        $data = $this->validasiKriteria($request);

        PenilaianKriteria::create($data);

        return redirect()->route('penilaian.master')->with('success', 'Pertanyaan ' . PenilaianKriteria::JENIS[$data['jenis']] . ' berhasil ditambahkan.');
    }

    public function kriteriaUpdate(Request $request, $id)
    {
        $kriteria = PenilaianKriteria::findOrFail($id);
        $data = $this->validasiKriteria($request, $kriteria);

        $kriteria->update($data);

        return redirect()->route('penilaian.master')->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function kriteriaDestroy($id)
    {
        $kriteria = PenilaianKriteria::findOrFail($id);

        $dipakai = PenilaianJawaban::where('kriteria_id', $kriteria->id)->count();
        if ($dipakai > 0) {
            return redirect()->route('penilaian.master')
                ->with('error', 'Pertanyaan ini sudah dipakai di ' . $dipakai . ' penilaian, jadi belum bisa dihapus. Nonaktifkan saja supaya tidak muncul lagi.');
        }

        $kriteria->delete();

        return redirect()->route('penilaian.master')->with('success', 'Pertanyaan berhasil dihapus.');
    }

    private function validasiKriteria(Request $request, ?PenilaianKriteria $kriteria = null): array
    {
        $request->merge([
            'pertanyaan' => $this->rapikan($request->input('pertanyaan')),
            'keterangan' => $this->rapikan($request->input('keterangan')),
            'urutan' => $request->filled('urutan') ? (int) $request->input('urutan') : 0,
            'aktif' => $request->boolean('aktif'),
        ]);

        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(PenilaianKriteria::JENIS))],
            'pertanyaan' => [
                'required', 'string', 'max:255',
                Rule::unique('penilaian_kriteria', 'pertanyaan')
                    ->where(fn ($q) => $q->where('jenis', $request->input('jenis')))
                    ->ignore($kriteria?->id),
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'aktif' => ['boolean'],
        ], [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'pertanyaan.unique' => 'Pertanyaan itu sudah ada di daftar.',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }

    // ---------------- Periode ----------------

    public function periodeStore(Request $request)
    {
        $data = $this->validasiPeriode($request);
        $periode = PenilaianPeriode::create($data);

        if ($data['aktif']) {
            $periode->jadikanAktif();
        }

        return redirect()->route('penilaian.master')->with('success', 'Periode ' . $periode->nama . ' berhasil ditambahkan.');
    }

    public function periodeUpdate(Request $request, $id)
    {
        $periode = PenilaianPeriode::findOrFail($id);
        $data = $this->validasiPeriode($request, $periode);

        $periode->update($data);

        if ($data['aktif']) {
            $periode->jadikanAktif();
        }

        return redirect()->route('penilaian.master')->with('success', 'Periode ' . $periode->nama . ' berhasil diperbarui.');
    }

    public function periodeAktifkan($id)
    {
        $periode = PenilaianPeriode::findOrFail($id);
        $periode->jadikanAktif();

        return redirect()->route('penilaian.master')->with('success', 'Periode aktif sekarang: ' . $periode->nama . '.');
    }

    public function periodeDestroy($id)
    {
        $periode = PenilaianPeriode::findOrFail($id);

        if ($periode->aktif) {
            return redirect()->route('penilaian.master')
                ->with('error', 'Periode ini sedang AKTIF. Jadikan periode lain aktif dulu sebelum menghapusnya.');
        }

        $sesi = PenilaianSesi::where('periode_id', $periode->id)->count();
        if ($sesi > 0) {
            return redirect()->route('penilaian.master')
                ->with('error', 'Periode ini sudah punya ' . $sesi . ' lembar penilaian, jadi belum bisa dihapus.');
        }

        $nama = $periode->nama;
        $periode->delete();

        // Pengaman: kalau karena suatu hal tidak ada periode aktif lagi, aktifkan yang terbaru.
        if (PenilaianPeriode::where('aktif', true)->count() === 0) {
            PenilaianPeriode::orderByDesc('id')->first()?->jadikanAktif();
        }

        return redirect()->route('penilaian.master')->with('success', 'Periode ' . $nama . ' berhasil dihapus.');
    }

    private function validasiPeriode(Request $request, ?PenilaianPeriode $periode = null): array
    {
        $request->merge([
            'nama' => $this->rapikan($request->input('nama')),
            'tahun_ajaran' => $this->rapikan($request->input('tahun_ajaran')),
            'keterangan' => $this->rapikan($request->input('keterangan')),
            'aktif' => $request->boolean('aktif'),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60', Rule::unique('penilaian_periode', 'nama')->ignore($periode?->id)],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'keterangan' => ['nullable', 'string', 'max:150'],
            'aktif' => ['boolean'],
        ], [
            'nama.required' => 'Nama periode wajib diisi (contoh: Semester 1 2026/2027).',
            'nama.unique' => 'Nama periode itu sudah ada.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        if (! $data['aktif'] && PenilaianPeriode::where('aktif', true)->count() === 0) {
            $data['aktif'] = true;
        }

        return $data;
    }

    // ---------------- Ambang predikat ----------------

    public function ambangStore(Request $request)
    {
        $data = $request->validate([
            'predikat_a' => ['required', 'numeric', 'min:1', 'max:100'],
            'predikat_b' => ['required', 'numeric', 'min:1', 'max:100', 'lt:predikat_a'],
            'predikat_c' => ['required', 'numeric', 'min:1', 'max:100', 'lt:predikat_b'],
        ], [
            'predikat_b.lt' => 'Ambang B harus lebih kecil dari ambang A.',
            'predikat_c.lt' => 'Ambang C harus lebih kecil dari ambang B.',
        ]);

        PenilaianPengaturan::simpanAmbang((float) $data['predikat_a'], (float) $data['predikat_b'], (float) $data['predikat_c']);

        return redirect()->route('penilaian.master')->with('success', 'Ambang predikat diperbarui: A ≥ ' . $data['predikat_a'] . ', B ≥ ' . $data['predikat_b'] . ', C ≥ ' . $data['predikat_c'] . '.');
    }

    private function rapikan($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $teks = str_replace(["\xC2\xA0", "\xE2\x80\xAF"], ' ', (string) $nilai);
        $teks = preg_replace('/\s+/u', ' ', $teks);

        return trim((string) $teks);
    }
}
