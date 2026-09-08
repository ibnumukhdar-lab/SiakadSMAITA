@if(!$sudah)
<div x-show="openModal" style="display: none;" class="k-modal-overlay">
    <div class="k-modal-box" @click.away="openModal = false">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
            <h3 style="font-size: 18px; font-weight: 900; margin: 0;">Nilai: {{ $kamar->nama_kamar }}</h3>
            <button type="button" @click="openModal = false" style="background: none; border: none; font-size: 20px; font-weight: bold; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('asrama.penilaian.simpanSkor', $kamar->id) }}" method="POST">
            @csrf
            <!-- 5 Kriteria -->
            @php 
                $kriterias = [
                    '1' => 'Kerapian Tempat Tidur 🛏️', '2' => 'Kebersihan Lantai 🧹', 
                    '3' => 'Kerapian Lemari 🚪', '4' => 'Barang & Sepatu 👞', '5' => 'Aroma & Sirkulasi 🌬️'
                ];
            @endphp
            
            @foreach($kriterias as $key => $judul)
            <div>
                <div class="k-kriteria-title">{{ $key }}. {{ $judul }}</div>
                <div class="likert-group">
                    @for($i=1; $i<=5; $i++)
                    <div class="likert-item">
                        <input type="radio" id="k{{$key}}_{{$kamar->id}}_{{$i}}" name="skor_{{$key}}" value="{{$i}}" required>
                        <label for="k{{$key}}_{{$kamar->id}}_{{$i}}">{{$i}}</label>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach

            <div style="margin-top: 15px;">
                <textarea name="catatan" rows="2" style="width: 100%; border-radius: 8px; border: 2px solid #e2e8f0; padding: 10px;" placeholder="Catatan opsional..."></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #1e293b; color: white; padding: 12px; border-radius: 10px; font-weight: bold; border: none; cursor: pointer; margin-top: 15px;">
                💾 SIMPAN NILAI
            </button>
        </form>
    </div>
</div>
@endif