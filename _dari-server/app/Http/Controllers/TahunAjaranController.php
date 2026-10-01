<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tahun Ajaran — satu tahun yang AKTIF pada satu waktu.
 * Nilai aktif dipakai sebagai bawaan kolom `siswas.tahun_ajaran`, pada kenaikan kelas,
 * dan sebagai penanda tahun berjalan (tanpa mengubah struktur data siswa).
 */
class TahunAjaranController extends Controller
{
    public function index()
    {
        $daftar = TahunAjaran::orderByDesc('nama')->get();

        // Berapa siswa yang datanya memakai tahun ajaran ini (kolom teks `siswas.tahun_ajaran`).
        $jumlahSiswa = Siswa::query()
            ->whereNotNull('tahun_ajaran')
            ->where('tahun_ajaran', '!=', '')
            ->selectRaw('tahun_ajaran, COUNT(*) as jumlah')
            ->groupBy('tahun_ajaran')
            ->pluck('jumlah', 'tahun_ajaran')
            ->toArray();

        return view('tahun-ajaran.index', compact('daftar', 'jumlahSiswa'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $tahun = TahunAjaran::create($data);

        if ($data['aktif']) {
            $tahun->jadikanAktif();
        }

        return redirect()->route('tahun-ajaran.index')
            ->with('success', 'Tahun ajaran ' . $tahun->nama . ' berhasil ditambahkan' . ($data['aktif'] ? ' dan dijadikan tahun ajaran aktif.' : '.'));
    }

    public function edit($id)
    {
        $tahun = TahunAjaran::findOrFail($id);
        $dipakai = Siswa::where('tahun_ajaran', $tahun->nama)->count();

        return view('tahun-ajaran.edit', compact('tahun', 'dipakai'));
    }

    public function update(Request $request, $id)
    {
        $tahun = TahunAjaran::findOrFail($id);
        $namaLama = $tahun->nama;

        $data = $this->validasi($request, $tahun);
        $gantiNama = $data['nama'] !== $namaLama;
        $ikutPerbarui = $request->boolean('perbarui_siswa');
        $dipakai = Siswa::withTrashed()->where('tahun_ajaran', $namaLama)->count();

        if ($gantiNama && $dipakai > 0 && ! $ikutPerbarui) {
            return back()->withInput()->withErrors([
                'nama' => "Tahun ajaran ini masih dipakai $dipakai data siswa. Centang \"Ikut perbarui data siswa\" supaya data siswa tidak menunjuk tahun ajaran yang tidak ada.",
            ]);
        }

        $tahun->update($data);

        if ($data['aktif']) {
            $tahun->jadikanAktif();
        }

        if ($gantiNama && $ikutPerbarui) {
            $jumlah = Siswa::withTrashed()->where('tahun_ajaran', $namaLama)->update(['tahun_ajaran' => $data['nama']]);

            return redirect()->route('tahun-ajaran.index')
                ->with('success', 'Tahun ajaran diubah menjadi ' . $data['nama'] . ' dan ' . $jumlah . ' data siswa ikut diperbarui.');
        }

        return redirect()->route('tahun-ajaran.index')
            ->with('success', 'Tahun ajaran ' . $tahun->nama . ' berhasil diperbarui.');
    }

    public function aktifkan($id)
    {
        $tahun = TahunAjaran::findOrFail($id);
        $tahun->jadikanAktif();

        return redirect()->route('tahun-ajaran.index')
            ->with('success', 'Tahun ajaran aktif sekarang: ' . $tahun->nama . '.');
    }

    public function destroy($id)
    {
        $tahun = TahunAjaran::findOrFail($id);

        if ($tahun->aktif) {
            return redirect()->route('tahun-ajaran.index')
                ->with('error', 'Tahun ajaran ' . $tahun->nama . ' sedang AKTIF, jadi belum bisa dihapus. Aktifkan tahun ajaran lain dulu.');
        }

        $jumlahSiswa = Siswa::where('tahun_ajaran', $tahun->nama)->count();
        if ($jumlahSiswa > 0) {
            return redirect()->route('tahun-ajaran.index')
                ->with('error', 'Tahun ajaran ' . $tahun->nama . ' masih dipakai ' . $jumlahSiswa . ' data siswa, jadi belum bisa dihapus.');
        }

        $nama = $tahun->nama;
        $tahun->delete();

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran ' . $nama . ' berhasil dihapus.');
    }

    private function validasi(Request $request, ?TahunAjaran $tahun = null): array
    {
        $request->merge([
            'nama' => $this->rapikanNama($request->input('nama')),
            'awal' => $request->filled('awal') ? $request->input('awal') : null,
            'akhir' => $request->filled('akhir') ? $request->input('akhir') : null,
            'keterangan' => $this->rapikanTeks($request->input('keterangan')),
            'aktif' => $request->boolean('aktif'),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:20', Rule::unique('tahun_ajaran', 'nama')->ignore($tahun?->id)],
            'awal' => ['nullable', 'date'],
            'akhir' => ['nullable', 'date', 'after_or_equal:awal'],
            'keterangan' => ['nullable', 'string', 'max:150'],
            'aktif' => ['boolean'],
        ], [
            'nama.required' => 'Nama tahun ajaran wajib diisi (contoh: 2026/2027).',
            'nama.unique' => 'Tahun ajaran itu sudah ada di daftar.',
            'akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        // Bila belum ada satu pun tahun aktif, yang pertama dibuat otomatis aktif.
        if (! $data['aktif'] && TahunAjaran::where('aktif', true)->count() === 0) {
            $data['aktif'] = true;
        }

        return $data;
    }

    private function rapikanNama($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);

        return $teks === null ? null : mb_substr(str_replace(' ', '', $teks), 0, 20);
    }

    private function rapikanTeks($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $teks = str_replace(["\xC2\xA0", "\xE2\x80\xAF"], ' ', (string) $nilai);
        $teks = preg_replace('/\s+/u', ' ', $teks);

        return trim((string) $teks);
    }
}
