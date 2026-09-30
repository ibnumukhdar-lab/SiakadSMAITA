<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Services\RekamJejak;
use Illuminate\Http\Request;

/**
 * REKAM JEJEK SISWA — halaman untuk guru, TU, Kepala Sekolah, Super Admin.
 * (mulai 30 Sep 2026)
 *
 * Satu halaman merangkum seorang siswa dari seluruh program SIAKAD: poin
 * karakter Student Root, project & portofolio, kebersihan/absensi asrama,
 * penilaian Adab & Keasramaan, dan catatan rapot. Angka dihitung server-side
 * lewat App\Services\RekamJejak — pelayanan yang sama dipakai portal orang tua
 * supaya angkanya mustahil berbeda.
 *
 * Halaman ini TIDAK bisa menyunting apa pun (hanya baca); penyuntingan data
 * induk tetap di halaman siswa seperti biasa.
 */
class RekamJejakController extends Controller
{
    public function siswa(Request $request, $id, RekamJejak $layanan)
    {
        $id = (int) $id;

        $data = $layanan->susun($id);

        if ($data === null) {
            return redirect()->route('siswa.index')->with('error', 'Siswa tidak ditemukan atau sudah dihapus.');
        }

        $sandiOrtu = \App\Support\SandiOrtu::dariTtl($data['siswa']->ttl ?? null);

        return view('siswa.rekam-jejak', [
            'rj'          => $data,
            'subdomain'   => null, // halaman ini di domain utama, bukan subdomain
            'sandiSiap'   => $sandiOrtu !== null,
            'alamatOrtu'  => url('/ortu'),
        ]);
    }

    /** Unduhan teks ringkas (untuk ditempel ke WhatsApp orang tua). */
    public function ringkas($id, RekamJejak $layanan)
    {
        $data = $layanan->susun((int) $id);

        if ($data === null) {
            return redirect()->route('siswa.index')->with('error', 'Siswa tidak ditemukan.');
        }

        $s = $data['siswa'];
        $p = $data['poin'];
        $i = $data['asrama']['inspeksi'];

        $baris = [
            '*REKAM JEJEK SISWA*',
            '*'.$s->nama_lengkap.'* ('.($s->kelas ?? '-').')',
            'NISN: '.($s->nisn ?? '-'),
            'Kamar: '.($data['kamar']?->nama_kamar ?? '-').' · Grup SR: '.($data['grup']?->nama_grup ?? '-'),
            '',
            '*Poin Karakter (Student Root)*',
            'Total: '.($p['total'] > 0 ? '+' : '').$p['total'].' dari '.$p['entri'].' kejadian',
        ];

        foreach ($p['terbanyak']->take(4) as $t) {
            $baris[] = '• '.$t->nama_perilaku.' — '.$t->jumlah.'×';
        }

        $baris[] = '';
        $baris[] = '*Kebersihan Kamar*';
        $baris[] = 'Rata-rata: '.($i['rata'] !== null ? str_replace('.', ',', (string) $i['rata']).' / 25' : 'belum ada data');
        $baris[] = 'Jadi kamar terbersih: '.$i['terbersih'].'× · terkotor: '.$i['terkotor'].'×';

        if ($data['project'] !== []) {
            $baris[] = '';
            $baris[] = '*Project Student Root*';

            foreach ($data['project'] as $pr) {
                $baris[] = '• '.$pr['nama'].' ('.$pr['status'].')'.($pr['nilai_akhir'] !== null ? ' — nilai '.$pr['nilai_akhir'] : '');
            }
        }

        $baris[] = '';
        $baris[] = 'Rekam jejak lengkap: '.url('/ortu');

        return response(implode("\n", $baris), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
