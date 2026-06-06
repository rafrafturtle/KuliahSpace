@extends('layouts.app')

@section('title', 'Detail Ketersediaan Ruang')
@section('subtitle', $selectedDayLabel.' - '.$selectedDate->format('d M Y'))

@push('styles')
    <style>
        .availability-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .availability-title {
            display: grid;
            gap: 8px;
        }
        .availability-title h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 0;
        }
        .availability-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }
        .availability-breadcrumb a {
            color: var(--blue);
        }
        .availability-breadcrumb span:not(:last-child)::after {
            content: "/";
            margin-left: 8px;
            color: #9aabc1;
        }
        .availability-context {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .context-pill,
        .flow-badge,
        .timeline-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            line-height: 1;
            white-space: nowrap;
        }
        .context-pill {
            padding: 7px 10px;
            border: 1px solid var(--line);
            background: #ffffff;
            color: var(--muted);
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }
        .summary-card {
            display: grid;
            gap: 8px;
            min-height: 112px;
            padding: 17px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: var(--shadow-soft);
        }
        .summary-card h2 {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: 0;
        }
        .summary-card .metric {
            margin: 0;
            font-size: 32px;
            letter-spacing: 0;
        }
        .flow-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
        }
        .flow-card {
            display: grid;
            gap: 14px;
            min-height: 100%;
            padding: 17px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 12px 28px rgba(5, 38, 109, .06);
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }
        .flow-card:hover,
        .flow-card:focus-visible {
            border-color: #a9c9f3;
            box-shadow: 0 16px 34px rgba(5, 38, 109, .1);
            transform: translateY(-2px);
        }
        .flow-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .flow-card h3 {
            margin: 0;
            font-size: 17px;
            letter-spacing: 0;
        }
        .flow-meta {
            display: grid;
            gap: 5px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .flow-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .flow-stat {
            display: grid;
            gap: 2px;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(255, 255, 255, .78);
        }
        .flow-stat strong {
            font-size: 18px;
            line-height: 1;
        }
        .flow-stat span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }
        .flow-badge {
            padding: 7px 10px;
        }
        .flow-badge-neutral {
            color: #31516f;
            background: #f1f5f9;
            border: 1px solid #d7e2ee;
        }
        .flow-badge-available,
        .timeline-badge-available {
            color: #0f7a4f;
            background: #e8f8f1;
            border: 1px solid #bcebd9;
        }
        .flow-badge-used,
        .timeline-badge-used {
            color: #be314a;
            background: #fff0f3;
            border: 1px solid #ffd1da;
        }
        .flow-badge-pending,
        .timeline-badge-pending {
            color: #9a6500;
            background: #fff7df;
            border: 1px solid #efd58a;
        }
        .flow-badge-mixed {
            color: #0b60eb;
            background: #e8f3ff;
            border: 1px solid #cfe3ff;
        }
        .timeline-badge {
            padding: 8px 10px;
        }
        .timeline-room-summary {
            display: grid;
            gap: 6px;
            margin-bottom: 16px;
        }
        .timeline-room-summary h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 0;
        }
        .timeline-table td:first-child {
            white-space: nowrap;
            font-weight: 900;
        }
        .timeline-label {
            display: grid;
            gap: 3px;
        }
        .timeline-label strong {
            color: #17356d;
        }
        .timeline-label small {
            color: var(--muted);
            font-weight: 700;
        }

        @media (max-width: 1180px) {
            .summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 760px) {
            .summary-grid,
            .flow-stats {
                grid-template-columns: 1fr;
            }
            .timeline-table td:first-child {
                white-space: normal;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $baseQuery = collect([
            'start_time' => $criteria['start_time'] ?? null,
            'end_time' => $criteria['end_time'] ?? null,
            'semester_id' => $criteria['semester_id'] ?? null,
            'academic_year_id' => $criteria['academic_year_id'] ?? null,
            'capacity' => $criteria['capacity'] ?? null,
            'month' => $backQuery['month'] ?? null,
            'year' => $backQuery['year'] ?? null,
        ])->filter(fn ($value) => filled($value))->all();
        $dateRoute = fn (array $extra = []) => route('room-availability.date', array_merge(
            ['date' => $selectedDate->toDateString()],
            array_filter(array_merge($baseQuery, $extra), fn ($value) => filled($value))
        ));
        $statusMeta = [
            'fully_available' => ['label' => 'Tersedia Sepanjang Hari', 'class' => 'available'],
            'partially_available' => ['label' => 'Terpakai Sebagian', 'class' => 'mixed'],
            'fully_used' => ['label' => 'Terpakai Penuh', 'class' => 'used'],
            'has_pending' => ['label' => 'Ada Pengajuan Pending', 'class' => 'pending'],
        ];
    @endphp

    <div class="availability-toolbar">
        <div class="availability-title">
            <div class="availability-breadcrumb">
                <span><a href="{{ route('room-availability.index', $backQuery) }}">Ketersediaan Ruang</a></span>
                <span>{{ $selectedDate->format('d M Y') }}</span>
                @if ($selectedBuilding)
                    <span>{{ $selectedBuilding->name }}</span>
                @endif
                @if ($selectedRoom)
                    <span>{{ $selectedRoom->code }}</span>
                @endif
            </div>
            <div>
                <h2>{{ $selectedDate->format('d M Y') }}</h2>
                <div class="muted">{{ $selectedDayLabel }}</div>
            </div>
            <div class="availability-context">
                <span class="context-pill">{{ $buildingAvailability['hasTimeFilter'] ? 'Rentang dipilih' : 'Jam operasional' }}: {{ $buildingAvailability['rangeStartTime'] }} - {{ $buildingAvailability['rangeEndTime'] }}</span>
                @if (! empty($criteria['capacity']))
                    <span class="context-pill">Kapasitas min. {{ $criteria['capacity'] }}</span>
                @endif
            </div>
        </div>
        <div class="actions">
            @if ($selectedRoom && $selectedBuilding)
                <a class="btn btn-soft" href="{{ $dateRoute(['building_id' => $selectedBuilding->id]) }}">Kembali ke Daftar Ruangan</a>
            @endif
            @if ($selectedBuilding)
                <a class="btn btn-soft" href="{{ $dateRoute() }}">Kembali ke Daftar Gedung</a>
            @endif
            <a class="btn" href="{{ route('room-availability.index', $backQuery) }}">Kembali ke Kalender</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h2>Total Gedung</h2>
            <div class="metric">{{ $buildingAvailability['totalBuildings'] }}</div>
        </div>
        <div class="summary-card">
            <h2>Total Ruangan</h2>
            <div class="metric">{{ $buildingAvailability['totalRooms'] }}</div>
        </div>
        <div class="summary-card">
            <h2>Ruangan Terpakai</h2>
            <div class="metric">{{ $buildingAvailability['usedRoomCount'] }}</div>
        </div>
        <div class="summary-card">
            <h2>Ruangan Tersedia</h2>
            <div class="metric">{{ $buildingAvailability['availableRoomCount'] }}</div>
        </div>
        <div class="summary-card">
            <h2>Pengajuan Pending</h2>
            <div class="metric">{{ $buildingAvailability['pendingRoomCount'] }}</div>
        </div>
    </div>

    @if ($selectedRoom && $roomTimeline)
        @php
            $timelineItem = $roomTimeline['timeline'];
            $roomStatus = $statusMeta[$timelineItem['summary_status']] ?? $statusMeta['fully_available'];
        @endphp
        <div class="panel">
            <div class="panel-header">
                <div class="timeline-room-summary">
                    <h2>{{ $selectedRoom->code }} - {{ $selectedRoom->name }}</h2>
                    <div class="muted">
                        {{ $selectedRoom->buildingRecord?->name ?? $selectedRoom->building ?? '-' }}
                        @if ($selectedRoom->buildingRecord?->floor)
                            | Lantai {{ $selectedRoom->buildingRecord->floor }}
                        @endif
                        | Kapasitas {{ $selectedRoom->capacity }}
                    </div>
                </div>
                <span class="flow-badge flow-badge-{{ $roomStatus['class'] }}">{{ $roomStatus['label'] }}</span>
            </div>
            <div class="table-wrap">
                <table class="timeline-table">
                    <thead>
                    <tr>
                        <th>Jam</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($timelineItem['timeline_rows'] as $row)
                        @php
                            $rowClass = 'available';

                            if ($row['status'] === 'Terpakai') {
                                $rowClass = 'used';
                            } elseif ($row['status'] === 'Pending') {
                                $rowClass = 'pending';
                            }
                        @endphp
                        <tr>
                            <td>{{ $row['start_time'] }} - {{ $row['end_time'] }}</td>
                            <td>
                                <span class="timeline-label">
                                    <strong>{{ $row['label'] ?: $row['status'] }}</strong>
                                    @if (! empty($row['meta']))
                                        <small>{{ $row['meta'] }}</small>
                                    @endif
                                </span>
                            </td>
                            <td><span class="timeline-badge timeline-badge-{{ $rowClass }}">{{ $row['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3"><div class="empty-state">Tidak ada timeline untuk ruangan ini.</div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($selectedBuilding && $roomsAvailability)
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Ruangan di {{ $selectedBuilding->name }}</h2>
                    <div class="muted">{{ $selectedBuilding->floor ? 'Lantai '.$selectedBuilding->floor.' | ' : '' }}{{ $roomsAvailability['totalRooms'] }} ruangan</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="flow-grid">
                    @forelse ($roomsAvailability['rooms'] as $timelineItem)
                        @php
                            $room = $timelineItem['room'];
                            $roomStatus = $statusMeta[$timelineItem['summary_status']] ?? $statusMeta['fully_available'];
                        @endphp
                        <a class="flow-card" href="{{ $dateRoute(['building_id' => $selectedBuilding->id, 'room_id' => $room->id]) }}">
                            <div class="flow-card-header">
                                <div>
                                    <h3>{{ $room->code }} - {{ $room->name }}</h3>
                                    <div class="flow-meta">
                                        <span>Kapasitas {{ $room->capacity }}</span>
                                    </div>
                                </div>
                                <span class="flow-badge flow-badge-{{ $roomStatus['class'] }}">{{ $roomStatus['label'] }}</span>
                            </div>
                            <div class="flow-meta">
                                <span>{{ $room->facilities ?? 'Fasilitas belum diisi' }}</span>
                            </div>
                            <span class="btn btn-small btn-soft">Lihat Timeline</span>
                        </a>
                    @empty
                        <div class="empty-state">Tidak ada ruangan aktif pada gedung ini untuk filter yang dipilih.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Daftar Gedung</h2>
                    <div class="muted">Pilih gedung untuk melihat ruangan di dalamnya.</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="flow-grid">
                    @forelse ($buildingAvailability['buildings'] as $buildingSummary)
                        @php($building = $buildingSummary['building'])
                        <a class="flow-card" href="{{ $dateRoute(['building_id' => $building->id]) }}">
                            <div class="flow-card-header">
                                <div>
                                    <h3>{{ $building->name }}</h3>
                                    <div class="flow-meta">
                                        <span>{{ $building->floor ? 'Lantai '.$building->floor : 'Lantai belum diisi' }}</span>
                                        @if ($building->code)
                                            <span>Kode {{ $building->code }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="flow-badge flow-badge-neutral">Lihat Ruangan</span>
                            </div>
                            <div class="flow-stats">
                                <span class="flow-stat"><strong>{{ $buildingSummary['total_rooms'] }}</strong><span>Total Ruangan</span></span>
                                <span class="flow-stat"><strong>{{ $buildingSummary['used_rooms_count'] }}</strong><span>Terpakai</span></span>
                                <span class="flow-stat"><strong>{{ $buildingSummary['available_rooms_count'] }}</strong><span>Tersedia</span></span>
                                <span class="flow-stat"><strong>{{ $buildingSummary['pending_rooms_count'] }}</strong><span>Pending</span></span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">Tidak ada gedung aktif dengan ruangan yang sesuai filter.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
