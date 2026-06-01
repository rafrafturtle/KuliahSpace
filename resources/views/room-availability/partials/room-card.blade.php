@php
    $type = $type ?? 'available';
@endphp

@if ($type === 'used')
    <div class="availability-card availability-card-used">
        <div class="actions">
            @include('partials.status-badge', ['status' => 'rejected', 'badgeLabel' => 'Terpakai'])
            <span class="muted">{{ $item['source'] }}</span>
        </div>
        <div>
            <h3>{{ $item['room']?->code ?? '-' }} - {{ $item['room']?->name ?? '-' }}</h3>
            <div class="availability-card-meta">
                <span>{{ substr((string) $item['start_time'], 0, 5) }}-{{ substr((string) $item['end_time'], 0, 5) }}</span>
                @if ($item['course'])
                    <span>{{ $item['course']->name }}{{ $item['lecturer'] ? ' - '.$item['lecturer']->name : '' }}</span>
                @endif
                @if ($item['requester'])
                    <span>Pengaju: {{ $item['requester']->name }}</span>
                @endif
            </div>
        </div>
        @if ($item['purpose'])
            <div class="availability-card-body">{{ $item['purpose'] }}</div>
        @endif
    </div>
@elseif ($type === 'pending')
    <div class="availability-card availability-card-pending">
        <div class="actions">@include('partials.status-badge', ['status' => 'pending', 'badgeLabel' => 'Sedang Dalam Pengajuan'])</div>
        <div>
            <h3>{{ $item['room']?->code ?? '-' }} - {{ $item['room']?->name ?? '-' }}</h3>
            <div class="availability-card-meta">
                <span>Pengaju: {{ $item['requester']?->name ?? '-' }}</span>
                <span>{{ $item['request_date']?->format('d M Y') ?? '-' }} - {{ substr((string) $item['start_time'], 0, 5) }}-{{ substr((string) $item['end_time'], 0, 5) }}</span>
            </div>
        </div>
        <div class="availability-card-body">{{ $item['purpose'] }}</div>
    </div>
@else
    <div class="availability-card availability-card-available">
        <div class="actions">@include('partials.status-badge', ['status' => 'available', 'badgeLabel' => 'Tersedia'])</div>
        <div>
            <h3>{{ $room->code }} - {{ $room->name }}</h3>
            <div class="availability-card-meta">
                <span>{{ $room->building ?? 'Gedung belum diisi' }}{{ $room->floor ? ' - Lantai '.$room->floor : '' }}</span>
                <span>Kapasitas {{ $room->capacity }}</span>
            </div>
        </div>
        @if ($room->facilities)
            <div class="availability-card-body">{{ $room->facilities }}</div>
        @endif
    </div>
@endif
