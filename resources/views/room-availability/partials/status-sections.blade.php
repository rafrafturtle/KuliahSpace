@php
    $sections = [
        [
            'key' => 'used',
            'title' => 'Terpakai',
            'description' => 'Jadwal tetap dan pengajuan ruang yang sudah disetujui.',
            'count' => $availability['used']->count().' data',
            'empty' => 'Tidak ada ruangan terpakai untuk filter ini.',
            'items' => $availability['used'],
        ],
        [
            'key' => 'available',
            'title' => 'Tidak Dipakai',
            'description' => 'Ruangan aktif yang tidak terpakai dan tidak sedang diajukan.',
            'count' => $availability['available']->count().' ruangan',
            'empty' => 'Tidak ada ruangan kosong untuk filter ini.',
            'items' => $availability['available'],
        ],
        [
            'key' => 'pending',
            'title' => 'Sedang Dalam Pengajuan',
            'description' => 'Pengajuan pending pada tanggal dan waktu yang dipilih.',
            'count' => $availability['pending']->count().' data',
            'empty' => 'Tidak ada pengajuan pending untuk filter ini.',
            'items' => $availability['pending'],
        ],
    ];
@endphp

@foreach ($sections as $section)
    <div class="panel result-section result-section-{{ $section['key'] }}">
        <div class="panel-header">
            <div>
                <h2>{{ $section['title'] }}</h2>
                <div class="muted">{{ $section['description'] }}</div>
            </div>
            <span class="section-count">{{ $section['count'] }}</span>
        </div>
        <div class="panel-body">
            <div class="room-card-grid">
                @forelse ($section['items'] as $item)
                    @if ($section['key'] === 'available')
                        @include('room-availability.partials.room-card', ['type' => 'available', 'room' => $item])
                    @else
                        @include('room-availability.partials.room-card', ['type' => $section['key'], 'item' => $item])
                    @endif
                @empty
                    <div class="empty-state">{{ $section['empty'] }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endforeach
