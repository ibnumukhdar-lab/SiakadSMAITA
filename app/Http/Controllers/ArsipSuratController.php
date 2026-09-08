<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArsipSurat;

class ArsipSuratController extends Controller
{
    public function index()
    {
        $arsips = ArsipSurat::latest()->get();
        return view('arsip.index', compact('arsips'));
    }

    public function create()
    {
        return view('arsip.create');
    }

    public function store(Request $request)
    {
        // Whitelist kolom (PENGAMAN 2026-09: hindari mass-assignment dari request)
        $data = $request->only([
            'jenis_surat', 'nomor_surat', 'tanggal_surat',
            'pihak_terkait', 'perihal', 'link_drive',
        ]);
        if ($request->hasFile('file_surat')) {
            $data['file_surat'] = $request->file('file_surat')->store('arsip_dokumen', 'public');
        }
        ArsipSurat::create($data);
        return redirect()->route('arsip.index')->with('success', 'Data surat berhasil ditambahkan!');
    }

    public function show($id)
    {
        $arsip = ArsipSurat::findOrFail($id);
        return view('arsip.show', compact('arsip'));
    }

    // --- FITUR BARU: EDIT & HAPUS DI BAWAH INI ---

    public function edit($id)
    {
        // Cari data yang mau diedit, lalu bawa ke halaman form edit
        $arsip = ArsipSurat::findOrFail($id);
        return view('arsip.edit', compact('arsip'));
    }

    public function update(Request $request, $id)
    {
        // Cari data lamanya
        $arsip = ArsipSurat::findOrFail($id);
        // Whitelist kolom (PENGAMAN 2026-09)
        $data = $request->only([
            'jenis_surat', 'nomor_surat', 'tanggal_surat',
            'pihak_terkait', 'perihal', 'link_drive',
        ]);

        // Kalau user upload file baru, timpa file lamanya
        if ($request->hasFile('file_surat')) {
            if ($arsip->file_surat) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($arsip->file_surat);
            }
            $data['file_surat'] = $request->file('file_surat')->store('arsip_dokumen', 'public');
        }

        // Simpan pembaruan ke database
        $arsip->update($data);
        return redirect()->route('arsip.index')->with('success', 'Data surat berhasil diperbarui!');
    }

    public function destroy($id)
    {
        // Cari data yang mau dihapus, lalu hancurkan!
        $arsip = ArsipSurat::findOrFail($id);
        if ($arsip->file_surat) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($arsip->file_surat);
        }
        $arsip->delete();
        return redirect()->route('arsip.index')->with('success', 'Data surat berhasil dihapus!');
    }

    // --- AKSI MASSAL (2026-09): melengkapi tombol yang sudah ada di halaman index ---

    // Hapus massal (DELETE /arsip/bulk)
    public function destroyBulk(Request $request)
    {
        $ids = $request->input('arsip_ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Pilih minimal satu data arsip terlebih dahulu.');
        }

        $arsips = ArsipSurat::whereIn('id', $ids)->get();
        $jumlah = 0;
        foreach ($arsips as $arsip) {
            if ($arsip->file_surat) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($arsip->file_surat);
            }
            $arsip->delete();
            $jumlah++;
        }

        return back()->with('success', "🗑️ {$jumlah} arsip berhasil dihapus permanen.");
    }

    // Ekspor massal ke CSV (kompatibel Excel; POST /arsip/bulk-export)
    public function exportBulk(Request $request)
    {
        $ids = $request->input('arsip_ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Pilih minimal satu data arsip untuk diekspor.');
        }

        $arsips = ArsipSurat::whereIn('id', $ids)->orderBy('tanggal_surat', 'desc')->get();

        $callback = function () use ($arsips) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM UTF-8 agar terbaca rapi di Excel
            fputcsv($file, ['No', 'Kategori', 'Nomor Surat', 'Tanggal Surat', 'Pihak Terkait', 'Perihal', 'File', 'Link Drive']);
            $no = 1;
            foreach ($arsips as $a) {
                fputcsv($file, [
                    $no++,
                    $a->jenis_surat,
                    $a->nomor_surat,
                    $a->tanggal_surat,
                    $a->pihak_terkait,
                    $a->perihal,
                    $a->file_surat ?? '',
                    $a->link_drive ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload(
            $callback,
            'Ekspor-Arsip-' . now()->format('Ymd-His') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}