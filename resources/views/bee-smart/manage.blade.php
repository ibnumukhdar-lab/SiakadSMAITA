<x-app-layout>
    

    <div class="bee-container">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-xl font-extrabold tracking-tight text-slate-900">Kelola Modul</h3>
                <p class="mt-0.5 text-sm text-slate-500">
                    <a href="{{ route('bee.index') }}" class="font-semibold text-blue-900 hover:underline">🐝 BEE Smart</a>
                    <span class="mx-1.5 text-slate-300">/</span>
                    <span class="font-semibold text-slate-700">{{ $week->judul }}</span>
                </p>
            </div>
        </div>
        @if(session('success'))
            <div class="bee-alert-success">{{ session('success') }}</div>
        @endif
        
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                ❌ Gagal menyimpan! Pastikan format file sesuai dan ukuran audio tidak lebih dari 5MB.
            </div>
        @endif

        <!-- AREA FORM INPUT (ATAS) -->
        <div class="bee-card no-print">
            <div class="bee-card-header">
                <div class="bee-header-text">
                    <h3>Tambah Kosakata Baru</h3>
                    <p>Rekam suara secara langsung atau pilih file audio dari perangkat Anda.</p>
                </div>
            </div>
            
            <div class="p-5 sm:p-6">
                <form action="{{ route('bee.storeVocab', $week->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- 1. Bahasa Indonesia -->
                    <div style="margin-bottom: 2rem;">
                        <label style="font-weight: 800; color: #374151; display: block; margin-bottom: 8px; font-size: 1.1rem;">🇮🇩 Bahasa Indonesia (Kata Dasar)</label>
                        <input type="text" name="kosakata_id" required placeholder="Contoh: Sekolah" class="bee-input-full">
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                        
                        <!-- 2. Bahasa Inggris -->
                        <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 12px; border: 1px solid #bbf7d0;">
                            <h4 style="margin: 0 0 1.2rem 0; color: #166534; font-weight: 900; font-size: 1.2rem;">🇬🇧 English</h4>
                            
                            <!-- Vocab EN -->
                            <label style="font-size: 0.9rem; font-weight: 800; color: #15803d; display: block; margin-bottom: 6px;">Vocabulary</label>
                            <input type="text" name="vocab_en" placeholder="School" class="bee-input-full" style="margin-bottom: 8px;">
                            
                            <div class="bee-recorder-area">
                                <button type="button" id="btn_record_vocab_en" onclick="startRecording('vocab_en')" class="bee-btn-record">🎤 Rekam Suara</button>
                                <button type="button" id="btn_stop_vocab_en" onclick="stopRecording('vocab_en')" class="bee-btn-stop" style="display:none;">⏹ Berhenti & Simpan</button>
                                <audio id="preview_vocab_en" controls class="bee-audio-preview" style="display:none;"></audio>
                                <input type="file" name="audio_vocab_en" id="file_vocab_en" accept="audio/*" class="bee-input-file">
                                <input type="hidden" name="generated_audio_vocab_en" id="gen_vocab_en">
                                <button type="button" id="btn_gen_vocab_en" onclick="generateAdd('vocab_en')" class="bee-btn-gen">✨ Generate Suara</button>
                                <span id="status_gen_vocab_en" class="bee-gen-status"></span>
                            </div>
                            
                            <!-- Sentence EN -->
                            <label style="font-size: 0.9rem; font-weight: 800; color: #15803d; display: block; margin-bottom: 6px; margin-top: 1.5rem;">Sentence (Kalimat)</label>
                            <textarea name="sentence_en" rows="2" placeholder="I go to school everyday." class="bee-input-full" style="margin-bottom: 8px;"></textarea>
                            
                            <div class="bee-recorder-area">
                                <button type="button" id="btn_record_sentence_en" onclick="startRecording('sentence_en')" class="bee-btn-record">🎤 Rekam Suara</button>
                                <button type="button" id="btn_stop_sentence_en" onclick="stopRecording('sentence_en')" class="bee-btn-stop" style="display:none;">⏹ Berhenti & Simpan</button>
                                <audio id="preview_sentence_en" controls class="bee-audio-preview" style="display:none;"></audio>
                                <input type="file" name="audio_sentence_en" id="file_sentence_en" accept="audio/*" class="bee-input-file">
                                <input type="hidden" name="generated_audio_sentence_en" id="gen_sentence_en">
                                <button type="button" id="btn_gen_sentence_en" onclick="generateAdd('sentence_en')" class="bee-btn-gen">✨ Generate Suara</button>
                                <span id="status_gen_sentence_en" class="bee-gen-status"></span>
                            </div>
                        </div>

                        <!-- 3. Bahasa Arab -->
                        <div style="background: #eff6ff; padding: 1.5rem; border-radius: 12px; border: 1px solid #bfdbfe;">
                            <h4 style="margin: 0 0 1.2rem 0; color: #1e40af; font-weight: 900; font-size: 1.2rem;">🇸🇦 العربية (Arabic)</h4>
                            
                            <!-- Mufrodat AR -->
                            <label style="font-size: 0.9rem; font-weight: 800; color: #1d4ed8; display: block; margin-bottom: 6px;">Mufrodat</label>
                            <input type="text" name="mufrodat_ar" placeholder="مَدْرَسَةٌ" class="bee-input-full text-right" style="margin-bottom: 8px; font-size: 1.2rem;" dir="rtl">
                            
                            <div class="bee-recorder-area">
                                <button type="button" id="btn_record_mufrodat_ar" onclick="startRecording('mufrodat_ar')" class="bee-btn-record">🎤 Rekam Suara</button>
                                <button type="button" id="btn_stop_mufrodat_ar" onclick="stopRecording('mufrodat_ar')" class="bee-btn-stop" style="display:none;">⏹ Berhenti & Simpan</button>
                                <audio id="preview_mufrodat_ar" controls class="bee-audio-preview" style="display:none;"></audio>
                                <input type="file" name="audio_mufrodat_ar" id="file_mufrodat_ar" accept="audio/*" class="bee-input-file">
                                <input type="hidden" name="generated_audio_mufrodat_ar" id="gen_mufrodat_ar">
                                <button type="button" id="btn_gen_mufrodat_ar" onclick="generateAdd('mufrodat_ar')" class="bee-btn-gen">✨ Generate Suara</button>
                                <span id="status_gen_mufrodat_ar" class="bee-gen-status"></span>
                            </div>
                            
                            <!-- Jumlah AR -->
                            <label style="font-size: 0.9rem; font-weight: 800; color: #1d4ed8; display: block; margin-bottom: 6px; margin-top: 1.5rem;">Al Jumlah (Kalimat)</label>
                            <textarea name="jumlah_ar" rows="2" placeholder="أَذْهَبُ إِلَى الْمَدْرَسَةِ كُلَّ يَوْمٍ" class="bee-input-full text-right" style="margin-bottom: 8px; font-size: 1.2rem;" dir="rtl"></textarea>
                            
                            <div class="bee-recorder-area">
                                <button type="button" id="btn_record_jumlah_ar" onclick="startRecording('jumlah_ar')" class="bee-btn-record">🎤 Rekam Suara</button>
                                <button type="button" id="btn_stop_jumlah_ar" onclick="stopRecording('jumlah_ar')" class="bee-btn-stop" style="display:none;">⏹ Berhenti & Simpan</button>
                                <audio id="preview_jumlah_ar" controls class="bee-audio-preview" style="display:none;"></audio>
                                <input type="file" name="audio_jumlah_ar" id="file_jumlah_ar" accept="audio/*" class="bee-input-file">
                                <input type="hidden" name="generated_audio_jumlah_ar" id="gen_jumlah_ar">
                                <button type="button" id="btn_gen_jumlah_ar" onclick="generateAdd('jumlah_ar')" class="bee-btn-gen">✨ Generate Suara</button>
                                <span id="status_gen_jumlah_ar" class="bee-gen-status"></span>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="bee-btn-submit" style="width: 100%; text-align: center;">💾 Simpan Kosakata & Audio</button>
                </form>
            </div>
        </div>

        <!-- PENGATURAN TARGET JUMLAH KATA (bebas kustom 1-500) -->
        <div class="bee-card no-print flex flex-wrap items-center justify-between gap-4 p-5">
            <div>
                <strong style="font-size: 1.05rem; color: #111827;">🎯 Target Jumlah Kata Modul Ini</strong>
                <div style="color: #6b7280; font-size: 0.85rem; margin-top: 2px;">
                    Bebas diubah 1–500, hanya informasi (tidak memblokir). Mau 20 kata? Set Maks = 20.
                </div>
            </div>
            <form method="POST" action="{{ route('bee.updateBatas', $week->id) }}" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                @csrf @method('PUT')
                <label style="font-size: 0.8rem; font-weight: 800; color: #374151;">Min</label>
                <input type="number" name="batas_min" min="1" max="500" value="{{ $week->batas_min }}" style="width: 80px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                <label style="font-size: 0.8rem; font-weight: 800; color: #374151;">Maks</label>
                <input type="number" name="batas_maks" min="1" max="500" value="{{ $week->batas_maks }}" style="width: 80px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                <button class="bee-btn-submit" style="padding: 7px 16px; font-size: 0.85rem;">💾 Simpan Target</button>
            </form>
        </div>

        <!-- AREA TABEL KOSAKATA (BAWAH) -->
        <div class="bee-card" id="print-area">
            <div class="bee-card-header">
                <div class="bee-header-text">
                    <h3>Daftar Kosakata: {{ $week->judul }}</h3>
                    <p>Total: {{ $week->vocabs->count() }} Kata · Target modul: {{ $week->batas_min }} – {{ $week->batas_maks }}</p>
                </div>
                
                <!-- TOMBOL CETAK PDF -->
                <button type="button" class="no-print inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap" onclick="window.print()">🖨️ Cetak Modul PDF</button>
                <button type="button" id="btnGenMissing" class="no-print inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap" onclick="generateMissingAll()">⚡ Generate Audio yang Belum Ada</button>
            </div>
            
            <div class="bee-table-responsive">
                <table class="bee-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th class="text-left">🇮🇩 Indonesia</th>
                            <th class="text-left">🇬🇧 English</th>
                            <th class="text-right">🇸🇦 Arabic</th>
                            <th class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($week->vocabs as $index => $v)
                        <tr>
                            <td style="font-weight: bold; color: #6b7280; vertical-align: middle; text-align: center;">{{ $index + 1 }}</td>
                            
                            <td class="text-left" style="vertical-align: middle;">
                                <div style="font-weight: 900; font-size: 1.2rem; color: #1f2937;">{{ $v->kosakata_id }}</div>
                            </td>
                            
                            <td class="text-left" style="vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-weight: 900; color: #166534; font-size: 1.1rem;">{{ $v->vocab_en }}</span>
                                    @if($v->audio_vocab_en)
                                        <button type="button" onclick="document.getElementById('audio_{{ $v->id }}_ve').play()" class="bee-btn-play no-print">🔊</button>
                                        <audio id="audio_{{ $v->id }}_ve" src="{{ url('berkas/' . $v->audio_vocab_en) }}"></audio>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                    <span style="font-size: 0.9rem; color: #4b5563; font-style: italic;">"{{ $v->sentence_en }}"</span>
                                    @if($v->audio_sentence_en)
                                        <button type="button" onclick="document.getElementById('audio_{{ $v->id }}_se').play()" class="bee-btn-play no-print">🔊</button>
                                        <audio id="audio_{{ $v->id }}_se" src="{{ url('berkas/' . $v->audio_sentence_en) }}"></audio>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="text-right" dir="rtl" style="vertical-align: middle;">
                                <div style="display: flex; align-items: center; justify-content: flex-start; flex-direction: row-reverse; gap: 8px;">
                                    <span style="font-weight: 900; color: #1e40af; font-size: 1.4rem; font-family: 'Amiri', 'Traditional Arabic', serif;">{{ $v->mufrodat_ar }}</span>
                                    @if($v->audio_mufrodat_ar)
                                        <button type="button" onclick="document.getElementById('audio_{{ $v->id }}_ma').play()" class="bee-btn-play no-print">🔊</button>
                                        <audio id="audio_{{ $v->id }}_ma" src="{{ url('berkas/' . $v->audio_mufrodat_ar) }}"></audio>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; justify-content: flex-start; flex-direction: row-reverse; gap: 8px; margin-top: 4px;">
                                    <span style="font-size: 1.1rem; color: #4b5563; font-family: 'Amiri', 'Traditional Arabic', serif;">{{ $v->jumlah_ar }}</span>
                                    @if($v->audio_jumlah_ar)
                                        <button type="button" onclick="document.getElementById('audio_{{ $v->id }}_ja').play()" class="bee-btn-play no-print">🔊</button>
                                        <audio id="audio_{{ $v->id }}_ja" src="{{ url('berkas/' . $v->audio_jumlah_ar) }}"></audio>
                                    @endif
                                </div>
                            </td>
                            
                            <td style="vertical-align: middle;" class="no-print">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit -->
                                    <button type="button" onclick="openEditModal({{ $v->id }}, '{{ addslashes($v->kosakata_id) }}', '{{ addslashes($v->vocab_en) }}', '{{ addslashes($v->sentence_en) }}', '{{ addslashes($v->mufrodat_ar) }}', '{{ addslashes($v->jumlah_ar) }}')" title="Edit Kosakata &amp; Audio" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</button>
                                    
                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('bee.destroyVocab', $v->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kata beserta audionya?');" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus Kosakata" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($week->vocabs->isEmpty())
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center font-semibold text-slate-400">
                                📭 Belum ada kosakata. Silakan input kata pertama di form atas!
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= MODAL EDIT TEKS & AUDIO ================= -->
        <div id="modalEdit" class="no-print" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
            <div style="background: white; width: 90%; max-width: 600px; border-radius: 16px; padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem;">
                    <h3 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: #1f2937;">✏️ Edit Kosakata & Audio</h3>
                    <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">✖</button>
                </div>

                <div style="background: #e0f2fe; border-left: 4px solid #3b82f6; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0 8px 8px 0;">
                    <p style="margin: 0; color: #1e40af; font-size: 0.85rem; font-weight: bold;">Anda dapat mengubah teks atau merekam ulang audio. Kosongkan rekaman jika tidak ingin mengubah audio yang sudah ada.</p>
                </div>

                <form id="formEditVocab" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    
                    <label style="font-weight: 800; color: #374151; display: block; margin-bottom: 6px;">🇮🇩 Bahasa Indonesia</label>
                    <input type="text" name="kosakata_id" id="edit_kosakata_id" required class="bee-input-full" style="margin-bottom: 1.5rem;">

                    <!-- Bahasa Inggris: Vocab -->
                    <label style="font-weight: 800; color: #15803d; display: block; margin-bottom: 6px;">🇬🇧 English (Vocabulary)</label>
                    <input type="text" name="vocab_en" id="edit_vocab_en" required class="bee-input-full" style="margin-bottom: 8px;">
                    <div class="bee-recorder-area" style="margin-bottom: 1.5rem; background: #f0fdf4;">
                        <button type="button" id="btn_record_edit_audio_vocab_en" onclick="startRecording('edit_audio_vocab_en')" class="bee-btn-record text-xs">🎤 Rekam Audio Baru</button>
                        <button type="button" id="btn_stop_edit_audio_vocab_en" onclick="stopRecording('edit_audio_vocab_en')" class="bee-btn-stop text-xs" style="display:none;">⏹ Berhenti & Simpan</button>
                        <audio id="preview_edit_audio_vocab_en" controls class="bee-audio-preview" style="display:none;"></audio>
                        <input type="file" name="audio_vocab_en" id="file_edit_audio_vocab_en" accept="audio/*" class="bee-input-file">
                        <input type="hidden" name="generated_audio_vocab_en" id="gen_edit_vocab_en">
                        <button type="button" id="btn_gen_edit_vocab_en" onclick="generateEdit('vocab_en')" class="bee-btn-gen text-xs">✨ Generate Suara</button>
                        <span id="status_gen_edit_vocab_en" class="bee-gen-status"></span>
                    </div>

                    <!-- Bahasa Inggris: Sentence -->
                    <label style="font-weight: 800; color: #15803d; display: block; margin-bottom: 6px;">🇬🇧 English (Sentence)</label>
                    <textarea name="sentence_en" id="edit_sentence_en" rows="2" class="bee-input-full" style="margin-bottom: 8px;"></textarea>
                    <div class="bee-recorder-area" style="margin-bottom: 1.5rem; background: #f0fdf4;">
                        <button type="button" id="btn_record_edit_audio_sentence_en" onclick="startRecording('edit_audio_sentence_en')" class="bee-btn-record text-xs">🎤 Rekam Audio Baru</button>
                        <button type="button" id="btn_stop_edit_audio_sentence_en" onclick="stopRecording('edit_audio_sentence_en')" class="bee-btn-stop text-xs" style="display:none;">⏹ Berhenti & Simpan</button>
                        <audio id="preview_edit_audio_sentence_en" controls class="bee-audio-preview" style="display:none;"></audio>
                        <input type="file" name="audio_sentence_en" id="file_edit_audio_sentence_en" accept="audio/*" class="bee-input-file">
                        <input type="hidden" name="generated_audio_sentence_en" id="gen_edit_sentence_en">
                        <button type="button" id="btn_gen_edit_sentence_en" onclick="generateEdit('sentence_en')" class="bee-btn-gen text-xs">✨ Generate Suara</button>
                        <span id="status_gen_edit_sentence_en" class="bee-gen-status"></span>
                    </div>

                    <!-- Bahasa Arab: Mufrodat -->
                    <label style="font-weight: 800; color: #1d4ed8; display: block; margin-bottom: 6px; text-align: right;">🇸🇦 العربية (Mufrodat)</label>
                    <input type="text" name="mufrodat_ar" id="edit_mufrodat_ar" dir="rtl" required class="bee-input-full text-right" style="margin-bottom: 8px; font-family: 'Amiri', serif; font-size: 1.2rem;">
                    <div class="bee-recorder-area" style="margin-bottom: 1.5rem; background: #eff6ff;">
                        <button type="button" id="btn_record_edit_audio_mufrodat_ar" onclick="startRecording('edit_audio_mufrodat_ar')" class="bee-btn-record text-xs">🎤 Rekam Audio Baru</button>
                        <button type="button" id="btn_stop_edit_audio_mufrodat_ar" onclick="stopRecording('edit_audio_mufrodat_ar')" class="bee-btn-stop text-xs" style="display:none;">⏹ Berhenti & Simpan</button>
                        <audio id="preview_edit_audio_mufrodat_ar" controls class="bee-audio-preview" style="display:none;"></audio>
                        <input type="file" name="audio_mufrodat_ar" id="file_edit_audio_mufrodat_ar" accept="audio/*" class="bee-input-file">
                        <input type="hidden" name="generated_audio_mufrodat_ar" id="gen_edit_mufrodat_ar">
                        <button type="button" id="btn_gen_edit_mufrodat_ar" onclick="generateEdit('mufrodat_ar')" class="bee-btn-gen text-xs">✨ Generate Suara</button>
                        <span id="status_gen_edit_mufrodat_ar" class="bee-gen-status"></span>
                    </div>

                    <!-- Bahasa Arab: Jumlah -->
                    <label style="font-weight: 800; color: #1d4ed8; display: block; margin-bottom: 6px; text-align: right;">🇸🇦 العربية (Jumlah)</label>
                    <textarea name="jumlah_ar" id="edit_jumlah_ar" dir="rtl" rows="2" class="bee-input-full text-right" style="margin-bottom: 8px; font-family: 'Amiri', serif; font-size: 1.2rem;"></textarea>
                    <div class="bee-recorder-area" style="margin-bottom: 2rem; background: #eff6ff;">
                        <button type="button" id="btn_record_edit_audio_jumlah_ar" onclick="startRecording('edit_audio_jumlah_ar')" class="bee-btn-record text-xs">🎤 Rekam Audio Baru</button>
                        <button type="button" id="btn_stop_edit_audio_jumlah_ar" onclick="stopRecording('edit_audio_jumlah_ar')" class="bee-btn-stop text-xs" style="display:none;">⏹ Berhenti & Simpan</button>
                        <audio id="preview_edit_audio_jumlah_ar" controls class="bee-audio-preview" style="display:none;"></audio>
                        <input type="file" name="audio_jumlah_ar" id="file_edit_audio_jumlah_ar" accept="audio/*" class="bee-input-file">
                        <input type="hidden" name="generated_audio_jumlah_ar" id="gen_edit_jumlah_ar">
                        <button type="button" id="btn_gen_edit_jumlah_ar" onclick="generateEdit('jumlah_ar')" class="bee-btn-gen text-xs">✨ Generate Suara</button>
                        <span id="status_gen_edit_jumlah_ar" class="bee-gen-status"></span>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeEditModal()" style="background: #f3f4f6; color: #4b5563; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; border: none;">Batal</button>
                        <button type="submit" class="bee-btn-submit" style="padding: 10px 20px;">💾 Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CSS EKSKLUSIF & PENGATURAN CETAK PDF       -->
    <!-- ========================================== -->
    <style>
        .bee-container { max-width: 1280px; margin: 0 auto; padding: 1.5rem 1rem; }
        .bee-alert-success { background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; border-radius: 12px; font-weight: 600; font-size: 0.9rem; }
        
        .bee-card { background-color: #ffffff; border-radius: 1rem; box-shadow: 0 1px 2px rgba(15,23,42,0.05); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 1.5rem; }
        .bee-card-header { background: #ffffff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap; border-bottom: 1px solid #f1f5f9; }
        .bee-header-text h3 { margin: 0; font-size: 1rem; font-weight: 800; color: #1e293b; letter-spacing: -0.01em; }
        .bee-header-text p { margin: 3px 0 0 0; color: #64748b; font-weight: 500; font-size: 0.85rem; }
        
        .bee-input-full { width: 100%; padding: 0.55rem 0.85rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; background: #ffffff; font-weight: 500; font-size: 0.9rem; color: #334155; box-sizing: border-box; outline: none; transition: all 0.15s; }
        .bee-input-full:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        
        .bee-recorder-area { background: rgba(255,255,255,0.75); padding: 10px 12px; border-radius: 10px; border: 1px dashed #cbd5e1; display: flex; flex-direction: column; gap: 8px; }
        .bee-btn-record { background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: 0.15s; align-self: flex-start; display: inline-flex; align-items: center; gap: 4px; }
        .bee-btn-record:hover { background: #f1f5f9; border-color: #94a3b8; }
        .bee-btn-stop { background: #fff1f2; border: 1px solid #fda4af; color: #e11d48; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; cursor: pointer; animation: pulse 1.5s infinite; align-self: flex-start; display: inline-flex; align-items: center; gap: 4px; }
        .bee-btn-gen { background: #ecfdf5; border: 1px solid #6ee7b7; color: #047857; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: 0.15s; align-self: flex-start; display: inline-flex; align-items: center; gap: 4px; }
        .bee-btn-gen:hover:not(:disabled) { background: #d1fae5; }
        .bee-btn-gen:disabled { opacity: .6; cursor: wait; }
        .bee-gen-status { font-size: .75rem; font-weight: 700; color: #047857; }
        .bee-audio-preview { width: 100%; height: 35px; border-radius: 6px; }
        .bee-input-file { width: 100%; font-size: 0.75rem; color: #6b7280; }
        .bee-input-file::file-selector-button { background: #e5e7eb; color: #374151; font-weight: bold; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; margin-right: 10px; transition: 0.2s; font-size: 0.75rem; }

        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        .bee-btn-submit { background-color: #1e3a8a; color: #ffffff; border: none; padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(15,23,42,0.08); display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .bee-btn-submit:hover { background-color: #1e40af; }

        .bee-btn-play { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; cursor: pointer; transition: 0.2s; }
        .bee-btn-play:hover { background: #e2e8f0; transform: scale(1.05); }

        .bee-table-responsive { overflow-x: auto; width: 100%; }
        .bee-table { width: 100%; border-collapse: collapse; min-width: 750px; }
        .bee-table th { background-color: rgba(248,250,252,0.8); padding: 0.9rem 1rem; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; text-align: center; }
        .bee-table th.text-left { text-align: left; }
        .bee-table th.text-right { text-align: right; }
        .bee-table td { padding: 1.1rem 1rem; border-bottom: 1px solid #f1f5f9; text-align: center; vertical-align: top; }
        .bee-table td.text-left { text-align: left; }
        .bee-table td.text-right { text-align: right; }
        .bee-table tbody tr:hover { background-color: #f8fafc; }

        /* =======================================================
           STYLE KHUSUS CETAK PDF (HANYA AKTIF SAAT PRINT / SAVE PDF)
           ======================================================= */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm; /* Margin keliling standar buku */
            }

            /* 1. Mencegah kotak & form mempengaruhi layout */
            *, *:before, *:after { box-sizing: border-box !important; }

            /* 2. Sembunyikan elemen Navigasi Laravel dan Komponen yang tidak relevan */
            nav, header, footer, aside, .no-print, .bee-card:not(#print-area) { 
                display: none !important; 
            }
            
            /* 3. Menghapus limit lebar layar agar tidak membatasi ukuran PDF */
            html, body, main, .min-h-screen, .py-12, .px-4, .sm\:px-6, .lg\:px-8, .bee-container {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important; /* HAPUS MIN-WIDTH LAYAR MONITOR */
            }
            
            /* 4. Membebaskan pembungkus tabel (print-area) */
            #print-area { 
                display: block !important; 
                position: relative !important;
                width: 100% !important; 
                max-width: 100% !important;
                box-shadow: none !important; 
                border: none !important; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            
            /* 5. Hapus kolom Aksi dan tombol Audio */
            .bee-btn-play, th.no-print, td.no-print { 
                display: none !important; 
            }
            
            /* 6. Desain Ulang Header Khusus Print */
            .bee-card-header { 
                background: transparent !important; 
                padding: 0 0 10px 0 !important; 
                border-bottom: 2px solid #000 !important; 
                margin-bottom: 15px !important;
            }
            .bee-header-text h3 { color: #000 !important; font-size: 16pt !important; margin: 0 !important; }
            .bee-header-text p { color: #555 !important; font-size: 10pt !important; margin-top: 4px !important; }
            
            /* 7. KUNCI PERBAIKAN: PAKSA TABEL BERUKURAN KERTAS DAN PROPORSIONAL */
            .bee-table-responsive { 
                overflow: visible !important; 
                width: 100% !important; 
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .bee-table { 
                border-collapse: collapse !important; 
                width: 100% !important; 
                max-width: 100% !important;
                min-width: 0 !important; /* HAPUS 750PX MIN-WIDTH AGAR BISA MENGECIL */
                table-layout: fixed !important; /* Paksa lebar diatur oleh persentase bawah */
                word-wrap: break-word !important;
            }
            
            /* PEMBAGIAN PROPORSI KOLOM YANG AKURAT (Hanya 4 Kolom) */
            .bee-table th:nth-child(1), .bee-table td:nth-child(1) { width: 5% !important; text-align: center !important; }
            .bee-table th:nth-child(2), .bee-table td:nth-child(2) { width: 25% !important; }
            .bee-table th:nth-child(3), .bee-table td:nth-child(3) { width: 35% !important; }
            .bee-table th:nth-child(4), .bee-table td:nth-child(4) { width: 35% !important; }

            .bee-table th, .bee-table td { 
                color: #000 !important; 
                border: 1px solid #000 !important; 
                padding: 8px 6px !important; /* Padding disesuaikan */
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }
            
            /* Mencegah baris tabel terpotong di tengah halaman */
            .bee-table tr {
                page-break-inside: avoid !important;
            }

            .bee-table th {
                font-size: 10pt !important;
                background-color: #f3f4f6 !important; 
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* 8. PENGECILAN UKURAN FONT MODUL */
            .bee-table td, 
            .bee-table td div, 
            .bee-table td span { 
                font-size: 9pt !important; 
                line-height: 1.3 !important;
                white-space: normal !important;
            }
            
            /* Teks Indonesia & English */
            td.text-left div, 
            td.text-left span { 
                font-size: 10pt !important; 
            }
            
            /* Teks English Sentence (lebih kecil sedikit) */
            td.text-left div[style*="margin-top"] span { 
                font-size: 9pt !important; 
            }

            /* Teks Arab (Mufrodat) */
            td.text-right span { 
                font-size: 12pt !important; /* Dikecilkan */
            }
            
            /* Teks Arab (Jumlah/Kalimat) */
            td.text-right div[style*="margin-top"] span { 
                font-size: 10pt !important; /* Dikecilkan */
            }
        }
    </style>

    <!-- ========================================== -->
    <!-- SCRIPT JAVASCRIPT GABUNGAN                 -->
    <!-- ========================================== -->
    <script>
        // --- LOGIKA MODAL EDIT ---
        function openEditModal(id, idText, enVocab, enSentence, arMufrodat, arJumlah) {
            document.getElementById('edit_kosakata_id').value = idText;
            document.getElementById('edit_vocab_en').value = enVocab;
            document.getElementById('edit_sentence_en').value = enSentence;
            document.getElementById('edit_mufrodat_ar').value = arMufrodat;
            document.getElementById('edit_jumlah_ar').value = arJumlah;
            
            // Bersihkan sisa rekaman/file sebelumnya saat modal dibuka
            ['edit_audio_vocab_en', 'edit_audio_sentence_en', 'edit_audio_mufrodat_ar', 'edit_audio_jumlah_ar'].forEach(field => {
                document.getElementById('preview_' + field).style.display = 'none';
                document.getElementById('preview_' + field).src = '';
                document.getElementById('file_' + field).value = '';
                document.getElementById('btn_record_' + field).style.display = 'inline-block';
                document.getElementById('btn_record_' + field).innerText = '🎤 Rekam Audio Baru';
                document.getElementById('btn_stop_' + field).style.display = 'none';
            });
            
            // Bersihkan juga hasil "Generate Suara" dari sesi modal sebelumnya
            ['gen_edit_vocab_en', 'gen_edit_sentence_en', 'gen_edit_mufrodat_ar', 'gen_edit_jumlah_ar'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            ['status_gen_edit_vocab_en', 'status_gen_edit_sentence_en', 'status_gen_edit_mufrodat_ar', 'status_gen_edit_jumlah_ar'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = '';
            });
            
            // Atur URL tujuan form submit
            document.getElementById('formEditVocab').action = "{{ url('bee-smart/kosakata') }}/" + id;
            
            // Tampilkan Modal
            document.getElementById('modalEdit').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('modalEdit').style.display = 'none';
            ['vocab_en', 'sentence_en', 'mufrodat_ar', 'jumlah_ar'].forEach(slot => {
                const h = document.getElementById('gen_edit_' + slot);
                if (h) h.value = '';
                const s = document.getElementById('status_gen_edit_' + slot);
                if (s) s.textContent = '';
            });
        }

        // --- LOGIKA REKAM SUARA ---
        let mediaRecorder;
        let audioChunks = [];
        let currentField = '';

        async function startRecording(fieldId) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                currentField = fieldId;
                audioChunks = [];

                mediaRecorder.ondataavailable = event => {
                    audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    const preview = document.getElementById('preview_' + currentField);
                    preview.src = audioUrl;
                    preview.style.display = 'block';

                    const fileInput = document.getElementById('file_' + currentField);
                    const dataTransfer = new DataTransfer();
                    
                    const file = new File([audioBlob], "rekaman_" + currentField + ".webm", { type: 'audio/webm' });
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    stream.getTracks().forEach(track => track.stop());
                };

                mediaRecorder.start();
                
                document.getElementById('btn_record_' + fieldId).style.display = 'none';
                document.getElementById('btn_stop_' + fieldId).style.display = 'inline-block';
            } catch (err) {
                alert('Gagal mengakses mikrofon. Pastikan Anda memberikan izin (Allow) pada browser untuk menggunakan mikrofon.');
            }
        }

        function stopRecording(fieldId) {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                
                document.getElementById('btn_stop_' + fieldId).style.display = 'none';
                const recordBtn = document.getElementById('btn_record_' + fieldId);
                recordBtn.style.display = 'inline-block';
                recordBtn.innerText = '🔄 Rekam Ulang'; 
            }
        }
    // ======================================================
    // GENERATE AUDIO (TTS) — 2026-09
    // ======================================================
    function beeLang(slot) { return slot.endsWith('_ar') ? 'ar' : 'en'; }

    async function beeGenerate(payload, btn, statusEl) {
        const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
        const labelLama = btn.innerText;
        btn.disabled = true;
        btn.innerText = '⏳ Membuat suara...';
        if (statusEl) statusEl.textContent = '';
        try {
            const r = await fetch('{{ url('bee-smart/generate-audio') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const j = await r.json();
            if (!j.ok) throw new Error(j.message || 'Gagal membuat audio');
            return j;
        } finally {
            btn.disabled = false;
            btn.innerText = labelLama;
        }
    }

    // Form TAMBAH kosakata: slot = vocab_en / sentence_en / mufrodat_ar / jumlah_ar
    async function generateAdd(slot) {
        const teks = document.getElementById(slot).value.trim();
        const btn = document.getElementById('btn_gen_' + slot);
        const status = document.getElementById('status_gen_' + slot);
        if (!teks) { alert('Isi dulu teksnya di kolom atas, lalu tekan Generate Suara.'); return; }
        try {
            const j = await beeGenerate({ teks: teks, lang: beeLang(slot) }, btn, status);
            document.getElementById('gen_' + slot).value = j.path;
            const pv = document.getElementById('preview_' + slot);
            if (pv) { pv.src = j.url; pv.style.display = 'block'; }
            if (status) status.textContent = '✨ Suara dibuat ✓ (bisa diganti lewat Rekam)';
        } catch (e) {
            alert('Gagal membuat audio: ' + e.message + '\nCoba lagi beberapa saat, atau pakai 🎤 Rekam Suara / pilih file audio.');
        }
    }

    // Modal EDIT kosakata: slot sama seperti di atas
    async function generateEdit(slot) {
        const teks = document.getElementById('edit_' + slot).value.trim();
        const btn = document.getElementById('btn_gen_edit_' + slot);
        const status = document.getElementById('status_gen_edit_' + slot);
        if (!teks) { alert('Isi dulu teksnya di kolom atas, lalu tekan Generate Suara.'); return; }
        try {
            const j = await beeGenerate({ teks: teks, lang: beeLang(slot) }, btn, status);
            document.getElementById('gen_edit_' + slot).value = j.path;
            const pv = document.getElementById('preview_edit_audio_' + slot);
            if (pv) { pv.src = j.url; pv.style.display = 'block'; }
            if (status) status.textContent = '✨ Suara dibuat ✓';
        } catch (e) {
            alert('Gagal membuat audio: ' + e.message + '\nCoba lagi beberapa saat, atau pakai 🎤 Rekam Suara / pilih file audio.');
        }
    }

    // Isi otomatis semua audio yang belum ada pada modul ini (12 audio per panggilan)
    async function generateMissingAll() {
        const btn = document.getElementById('btnGenMissing');
        const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
        btn.disabled = true;
        btn.innerText = '⏳ Mengisi audio (bisa 1–2 menit)...';
        try {
            for (let i = 0; i < 40; i++) {
                const r = await fetch('{{ url('bee-smart/minggu') }}/{{ $week->id }}/generate-missing', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: '{}',
                });
                const j = await r.json();
                if (!j.ok) { alert('Gagal: ' + (j.message || 'coba lagi nanti')); return; }
                if (j.gagal > 0) {
                    alert('Berhenti sementara: ' + j.gagal + ' audio gagal (kemungkinan batas Google). Sisa ' + j.sisa + '. Tekan lagi beberapa saat lagi.');
                    return;
                }
                if (j.sisa <= 0) {
                    alert('Selesai! ' + j.berhasil + ' audio berhasil dibuat.');
                    location.reload();
                    return;
                }
            }
            alert('Masih ada sisa audio. Tekan tombol ini lagi untuk melanjutkan.');
        } finally {
            btn.disabled = false;
            btn.innerText = '⚡ Generate Audio yang Belum Ada';
        }
    }
    </script>
</x-app-layout>