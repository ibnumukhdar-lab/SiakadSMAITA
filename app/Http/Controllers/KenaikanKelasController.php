<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kenaikan Kelas / Pindah Kelas massal.
 *
 * Memindahkan SELURUH siswa dari kelas sumber ke kelas tujuan sekaligus (bukan satu-satu),
 * plus mengisi tahun ajaran baru. Hanya menyentuh kolom `kelas`, `status`, dan `tahun_ajaran`
 * milik siswa — tidak menyentuh kamar Asrama maupun grup Student Root.
 */
class KenaikanKelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::terurut()->get();
        $jumlahPerKelas = Kelas::jumlahSiswaPerKelas();   // siswa aktif saja

        $tahunAktif = TahunAjaran::aktifSekarang();
        $daftarTahun = TahunAjaran::daftarNama();

        return view('kenaikan.index', [
            'kelas' => $kelas,
            'jumlahPerKelas' => $jumlahPerKelas,
            'tahunAktif' => $tahunAktif,
            'daftarTahun' => $daftarTahun,
            'totalSiswa' => array_sum($jumlahPerKelas),
        ]);
    }

    public function proses(Request $request)
    {
        $data = $request->validate([
            'dari' => ['required', 'array', 'min:1'],
            'dari.*' => ['string', 'max:60'],
            'ke' => ['required', 'string', 'max:60'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'jadikan_aktif' => ['nullable', 'boolean'],
        ], [
            'dari.required' => 'Pilih dulu kelas asal yang mau dinaikkan.',
            'ke.required' => 'Pilih kelas tujuan (atau Lulus/Alumni).',
        ]);

        $kelasSah = Kelas::pluck('nama')->all();
        $dari = array_values(array_intersect($data['dari'], $kelasSah));

        if (empty($dari)) {
            return back()->with('error', 'Kelas asal yang dipilih tidak dikenali. Muat ulang halaman lalu coba lagi.');
        }

        $tujuanLulus = ($data['ke'] === '__lulus__');
        if (! $tujuanLulus && ! in_array($data['ke'], $kelasSah, true)) {
            return back()->with('error', 'Kelas tujuan tidak dikenali.');
        }

        $bentrok = array_values(array_intersect($dari, [$data['ke']]));
        if (! empty($bentrok) && count($dari) === 1) {
            return back()->with('error', 'Kelas asal dan kelas tujuan tidak boleh sama.');
        }
        if (! empty($bentrok)) {
            // Kelas tujuan termasuk salah satu sumber: keluarkan dari daftar sumber supaya tidak berputar.
            $dari = array_values(array_diff($dari, [$data['ke']]));
            if (empty($dari)) {
                return back()->with('error', 'Kelas asal dan kelas tujuan tidak boleh sama.');
            }
        }

        $tahunBaru = $data['tahun_ajaran'] ?: TahunAjaran::namaAktif();
        $jadikanAktif = $request->boolean('jadikan_aktif');

        $rincian = [];
        $total = 0;

        DB::transaction(function () use ($dari, $tujuanLulus, $data, $tahunBaru, $jadikanAktif, &$rincian, &$total) {
            foreach ($dari as $kelasAsal) {
                $perubahan = $tujuanLulus
                    ? ['kelas' => 'Lulus', 'status' => 'Alumni']
                    : ['kelas' => $data['ke']];

                if (! $tujuanLulus && $jadikanAktif) {
                    $perubahan['status'] = 'Aktif';
                }
                if ($tahunBaru) {
                    $perubahan['tahun_ajaran'] = $tahunBaru;
                }

                $jumlah = Siswa::where('kelas', $kelasAsal)->update($perubahan);

                $rincian[] = $kelasAsal . ' → ' . ($tujuanLulus ? 'Lulus/Alumni' : $data['ke']) . ' (' . $jumlah . ' siswa)';
                $total += $jumlah;
            }
        });

        $pesan = $total . ' siswa dipindahkan: ' . implode(', ', $rincian) . '.';
        if ($tahunBaru) {
            $pesan .= ' Tahun ajaran diisi ' . $tahunBaru . '.';
        }

        return redirect()->route('kenaikan.index')->with('success', $pesan);
    }
}
