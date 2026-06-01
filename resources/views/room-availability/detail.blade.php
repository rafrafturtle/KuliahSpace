@extends('layouts.app')

@section('title', 'Detail Ketersediaan Ruang')
@section('subtitle', $selectedDayLabel.' - '.$selectedDate->format('d M Y'))

@push('styles')
    <style>
        .detail-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .summary-card {
            display: grid;
            gap: 8px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow);
        }
        .summary-card h2 {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            letter-spacing: 0;
        }
        .summary-card .metric {
            margin: 0;
            font-size: 30px;
        }
        .summary-card-total { border-color: #cae0f3; background: #f4f9fd; }
        .summary-card-used { border-color: #f2c3c3; background: #fff5f5; }
        .summary-card-available { border-color: #bfe6cc; background: #f3fbf5; }
        .summary-card-pending { border-color: #efd58a; background: #fff9e8; }
        .timeline-context {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .timeline-context span {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #ffffff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .timeline-section .panel-header {
            align-items: flex-start;
        }
        .timeline-room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 14px;
        }
        .timeline-room-card {
            display: grid;
            gap: 14px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #ffffff;
            box-shadow: 0 12px 24px rgba(23, 32, 51, .04);
        }
        .timeline-room-card-available { border-color: #bfe6cc; background: #f9fdfb; }
        .timeline-room-card-used { border-color: #f2c3c3; background: #fff8f8; }
        .timeline-room-card-pending { border-color: #efd58a; background: #fffdf5; }
        .timeline-room-card-mixed { border-color: #d7e4ee; background: #fbfdff; }
        .timeline-room-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .timeline-room-card h3 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 0;
        }
        .timeline-room-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 5px;
            color: var(--muted);
            font-size: 13px;
        }
        .timeline-facilities {
            color: #3f4f63;
            font-size: 13px;
            line-height: 1.55;
        }
        .timeline-status-badge,
        .timeline-mini-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .timeline-status-badge {
            padding: 6px 10px;
        }
        .timeline-status-badge-available,
        .timeline-mini-badge-available {
            color: #206f3d;
            background: #e8f7ee;
            border: 1px solid #bfe6cc;
        }
        .timeline-status-badge-used,
        .timeline-mini-badge-used {
            color: #9f3445;
            background: #fff1f2;
            border: 1px solid #f2c3c3;
        }
        .timeline-status-badge-pending,
        .timeline-mini-badge-pending {
            color: #806019;
            background: #fff8e5;
            border: 1px solid #efd58a;
        }
        .timeline-status-badge-mixed {
            color: #275371;
            background: #eef6fb;
            border: 1px solid #c6dceb;
        }
        .timeline-slot-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .timeline-mini-badge {
            padding: 4px 8px;
        }
        .timeline-slot-groups {
            display: grid;
            gap: 12px;
        }
        .timeline-slot-group {
            display: grid;
            gap: 7px;
        }
        .timeline-slot-group h4 {
            margin: 0;
            color: #26384e;
            font-size: 13px;
            font-weight: 900;
        }
        .timeline-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }
        .time-chip {
            display: inline-grid;
            gap: 2px;
            max-width: 100%;
            padding: 7px 9px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            line-height: 1.35;
        }
        .time-chip strong {
            font-size: 12px;
        }
        .time-chip span,
        .time-chip small {
            color: var(--muted);
            font-size: 12px;
        }
        .time-chip-used {
            color: #9f3445;
            border-color: #f2c3c3;
            background: #fff1f2;
        }
        .time-chip-pending {
            color: #806019;
            border-color: #efd58a;
            background: #fff8e5;
        }
        .time-chip-available {
            color: #206f3d;
            border-color: #bfe6cc;
            background: #e8f7ee;
        }
        .timeline-full-available {
            width: fit-content;
            padding: 8px 10px;
            border: 1px solid #bfe6cc;
            border-radius: 8px;
            background: #e8f7ee;
            color: #206f3d;
            font-size: 13px;
            font-weight: 800;
        }
        .timeline-empty-text {
            color: var(--muted);
            font-size: 12px;
            font-style: italic;
        }

        @media (max-width: 760px) {
            .timeline-room-grid {
                grid-template-columns: 1fr;
            }
            .timeline-room-header {
                display: grid;
            }
        }
    </style>
@endpush

@section('content')
    <div class="detail-toolbar">
        <div>
            <h2 style="margin: 0;">{{ $selectedDate->format('d M Y') }}</h2>
            <div class="muted">{{ $selectedDayLabel }}</div>
            <div class="timeline-context">
                <span>{{ $timeline['hasTimeFilter'] ? 'Rentang dipilih' : 'Jam operasional' }}: {{ $timeline['rangeStartTime'] }} - {{ $timeline['rangeEndTime'] }}</span>
                @if (! empty($criteria['capacity']))
                    <span>Kapasitas min. {{ $criteria['capacity'] }}</span>
                @endif
                @if (! empty($criteria['building']))
                    <span>{{ $criteria['building'] }}</span>
                @endif
            </div>
        </div>
        <a class="btn btn-soft" href="{{ route('room-availability.index', $backQuery) }}">Kembali ke Kalender</a>
    </div>

    <div class="grid grid-4" style="margin-bottom: 16px;">
        <div class="summary-card summary-card-total">
            <h2>Total Ruangan</h2>
            <div class="metric">{{ $totalRooms }}</div>
        </div>
        <div class="summary-card summary-card-used">
            <h2>Ruangan dengan Jadwal Terpakai</h2>
            <div class="metric">{{ $timeline['usedRoomCount'] }}</div>
        </div>
        <div class="summary-card summary-card-available">
            <h2>Ruangan Sepenuhnya Tersedia</h2>
            <div class="metric">{{ $timeline['fullyAvailableRoomCount'] }}</div>
        </div>
        <div class="summary-card summary-card-pending">
            <h2>Ruangan dengan Pengajuan Pending</h2>
            <div class="metric">{{ $timeline['pendingRoomCount'] }}</div>
        </div>
    </div>

    <div class="panel timeline-section">
        <div class="panel-header">
            <div>
                <h2>Status Ruangan per Waktu</h2>
            </div>
            <span class="muted">{{ $timeline['totalRooms'] }} ruangan</span>
        </div>
        <div class="panel-body">
            <div class="timeline-room-grid">
                @forelse ($timeline['rooms'] as $timelineItem)
                    @include('room-availability.partials.timeline-room-card', ['timelineItem' => $timelineItem])
                @empty
                    <div class="empty-state">Tidak ada ruangan aktif yang sesuai dengan filter ini.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
