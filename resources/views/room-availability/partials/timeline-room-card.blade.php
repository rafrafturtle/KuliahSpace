@php
    $room = $timelineItem['room'];
    $status = $timelineItem['summary_status'];
    $statusMeta = [
        'fully_available' => ['label' => 'Tersedia', 'class' => 'available'],
        'partially_available' => ['label' => 'Sebagian Tersedia', 'class' => 'mixed'],
        'fully_used' => ['label' => 'Terpakai', 'class' => 'used'],
        'has_pending' => ['label' => 'Pending', 'class' => 'pending'],
    ][$status] ?? ['label' => 'Tersedia', 'class' => 'available'];
    $availableLabel = $timelineItem['has_time_filter'] ? 'Tersedia pada rentang waktu dipilih' : 'Tersedia sepanjang hari';
@endphp

<article class="timeline-room-card timeline-room-card-{{ $statusMeta['class'] }}">
    <div class="timeline-room-header">
        <div>
            <h3>{{ $room->code }} - {{ $room->name }}</h3>
            <div class="timeline-room-meta">
                <span>{{ $room->building ?? 'Gedung belum diisi' }}{{ $room->floor ? ' | Lantai '.$room->floor : '' }}</span>
                <span>Kapasitas {{ $room->capacity }}</span>
            </div>
        </div>
        <span class="timeline-status-badge timeline-status-badge-{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
    </div>

    @if ($room->facilities)
        <div class="timeline-facilities">{{ $room->facilities }}</div>
    @endif

    <div class="timeline-slot-summary">
        <span class="timeline-mini-badge timeline-mini-badge-used">Terpakai: {{ count($timelineItem['used_slots']) }}</span>
        <span class="timeline-mini-badge timeline-mini-badge-pending">Pending: {{ count($timelineItem['pending_slots']) }}</span>
        <span class="timeline-mini-badge timeline-mini-badge-available">Tersedia: {{ count($timelineItem['available_slots']) }}</span>
    </div>

    <div class="timeline-slot-groups">
        <div class="timeline-slot-group">
            <h4>Terpakai</h4>
            <div class="timeline-chip-list">
                @forelse ($timelineItem['used_slots'] as $slot)
                    <span class="time-chip time-chip-used">
                        <strong>{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</strong>
                        <span>{{ $slot['label'] ?: '-' }} | {{ $slot['source'] }}</span>
                        @if (! empty($slot['meta']))
                            <small>{{ $slot['meta'] }}</small>
                        @endif
                    </span>
                @empty
                    <span class="timeline-empty-text">Tidak ada slot terpakai.</span>
                @endforelse
            </div>
        </div>

        <div class="timeline-slot-group">
            <h4>Sedang Dalam Pengajuan</h4>
            <div class="timeline-chip-list">
                @forelse ($timelineItem['pending_slots'] as $slot)
                    <span class="time-chip time-chip-pending">
                        <strong>{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</strong>
                        <span>{{ $slot['label'] ?: '-' }} | {{ $slot['source'] }}</span>
                        @if (! empty($slot['meta']))
                            <small>{{ $slot['meta'] }}</small>
                        @endif
                    </span>
                @empty
                    <span class="timeline-empty-text">Tidak ada pengajuan pending.</span>
                @endforelse
            </div>
        </div>

        <div class="timeline-slot-group">
            <h4>Tersedia</h4>
            @if (count($timelineItem['used_slots']) === 0 && count($timelineItem['pending_slots']) === 0)
                <div class="timeline-full-available">{{ $availableLabel }}</div>
            @else
                <div class="timeline-chip-list">
                    @forelse ($timelineItem['available_slots'] as $slot)
                        <span class="time-chip time-chip-available">
                            <strong>{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</strong>
                        </span>
                    @empty
                        <span class="timeline-empty-text">Tidak ada slot tersedia pada rentang ini.</span>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</article>
