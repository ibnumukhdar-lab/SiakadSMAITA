<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola Kelas — daftar/pengelompokan kelas.
 *
 * PENTING: modul ini TIDAK mengubah struktur data siswa. Nama kelas hanya disimpan
 * di tabel `kelas`, lalu dipakai sebagai pilihan di Data Siswa (form & filter).
 * Pengelompokan yang sudah jalan — Asrama (kamar/penilaian) dan Student Root
 * (grup binaan/poin) — membaca `siswas.id`, jadi tidak terpengaruh.
 */
class KelasController extends Controller
{
    public function index()
    {
        $daftar = Kelas::terurut()->get();
        $jumlah = Kelas::jumlahSiswaPerKelas();              // siswa aktif
        $jumlahSemua = Kelas::jumlahSiswaPerKelas(true);     // termasuk tong sampah

        $namaTerdaftar = $daftar->pluck('nama')->all();

        // Nilai kelas yang masih dipakai data siswa tapi belum terdaftar di daftar kelas.
        $belumTerdaftar = collect($jumlah)
            ->reject(fn ($n, $nama) => in_array($nama, $namaTerdaftar, true))
            ->sortKeys()
            ->all();

        return view('kelas.index', compact('daftar', 'jumlah', 'jumlahSemua', 'belumTerdaftar'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        Kelas::create($data);

        return redirect()->route('kelas.index')
            ->with('success', 'Kelas "' . $data['nama'] . '" berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $dipakai = (int) (Kelas::jumlahSiswaPerKelas(true)[$kelas->nama] ?? 0);

        return view('kelas.edit', compact('kelas', 'dipakai'));
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $namaLama = $kelas->nama;

        $data = $this->validasi($request, $kelas);
        $namaBaru = $data['nama'];
        $gantiNama = $namaBaru !== $namaLama;
        $ikutPindah = $request->boolean('pindahkan_siswa');

        $dipakaiAktif = (int) (Kelas::jumlahSiswaPerKelas()[$namaLama] ?? 0);

        if ($gantiNama && $dipakaiAktif > 0 && ! $ikutPindah) {
            return back()->withInput()->withErrors([
                'nama' => "Kelas ini sedang dipakai $dipakaiAktif siswa. Centang \"Ikut pindahkan siswa\" supaya data siswa tetap terkelompok ke nama kelas baru.",
            ]);
        }

        $kelas->update($data);

        if ($gantiNama && $ikutPindah) {
            // Hanya kolom `kelas` pada siswa yang bernama kelas lama — dipicu admin lewat centang.
            $jumlahPindah = Siswa::withTrashed()->where('kelas', $namaLama)->update(['kelas' => $namaBaru]);

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas diubah menjadi "' . $namaBaru . '" dan ' . $jumlahPindah . ' data siswa ikut dipindahkan.');
        }

        return redirect()->route('kelas.index')->with('success', 'Kelas "' . $namaBaru . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);

        $jumlahSemua = (int) (Kelas::jumlahSiswaPerKelas(true)[$kelas->nama] ?? 0);

        if ($jumlahSemua > 0) {
            return redirect()->route('kelas.index')->with('error', 'Kelas "' . $kelas->nama . '" tidak dihapus karena masih dipakai ' . $jumlahSemua . ' data siswa. Pindahkan dulu siswa ke kelas lain lewat Data Siswa.');
        }

        $nama = $kelas->nama;
        $kelas->delete();

        return redirect()->route('kelas.index')->with('success', 'Kelas "' . $nama . '" berhasil dihapus.');
    }

    private function validasi(Request $request, ?Kelas $kelas = null): array
    {
        $request->merge([
            'nama' => $this->rapikanNama($request->input('nama')),
            'tingkat' => $request->filled('tingkat') ? $request->input('tingkat') : null,
            'keterangan' => $this->rapikanTeks($request->input('keterangan')),
            'urutan' => $request->filled('urutan') ? (int) $request->input('urutan') : 0,
            'aktif' => $request->boolean('aktif'),
        ]);

        $aturan = [
            'nama' => ['required', 'string', 'max:60', Rule::unique('kelas', 'nama')->ignore($kelas?->id)],
            'tingkat' => ['nullable', Rule::in(Kelas::TINGKAT)],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'keterangan' => ['nullable', 'string', 'max:150'],
            'aktif' => ['boolean'],
        ];

        $pesan = [
            'nama.required' => 'Nama kelas wajib diisi.',
            'nama.max' => 'Nama kelas maksimal 60 karakter.',
            'nama.unique' => 'Nama kelas itu sudah ada di daftar.',
            'tingkat.in' => 'Tingkat harus X, XI, XII, atau Lulus.',
        ];

        $data = $request->validate($aturan, $pesan);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }

    private function rapikanNama($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);

        return $teks === null ? null : mb_substr($teks, 0, 60);
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
