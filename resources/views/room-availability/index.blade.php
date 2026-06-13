@extends('layouts.app')

@section('title', 'Ketersediaan Ruang')
@section('subtitle', 'Lihat kalender ketersediaan ruang per tanggal.')

@push('styles')
    <style>
        .calendar-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .calendar-title {
            font-size: 18px;
            font-weight: 800;
        }
        .calendar-weekdays,
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }
        .calendar-weekdays {
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .calendar-weekday {
            padding: 0 6px;
        }
        .calendar-weekday.weekend {
            color: #b13a3a;
        }
        .calendar-day {
            position: relative;
            display: grid;
            align-content: start;
            min-height: 96px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #ffffff;
            cursor: pointer;
            overflow: hidden;
            transition: background-color .16s ease, border-color .16s ease, box-shadow .16s ease;
        }
        .calendar-day:hover,
        .calendar-day:focus-visible {
            border-color: #afbfce;
            background: #fbfdff;
            box-shadow: 0 12px 28px rgba(23, 32, 51, .06);
        }
        .calendar-day.outside-month {
            opacity: .45;
            background: var(--surface-muted);
        }
        .calendar-day.today {
            border-color: #cae0f3;
            box-shadow: inset 0 0 0 1px #cae0f3;
        }
        .calendar-date-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 850;
            line-height: 1;
        }
        .calendar-day.weekend .calendar-date-number {
            color: #b13a3a;
        }
        .calendar-day.today .calendar-date-number {
            background: var(--blue-soft);
            color: #194f75;
        }
        .calendar-summary {
            position: absolute;
            right: 10px;
            bottom: 10px;
            left: 10px;
            display: grid;
            gap: 2px;
            padding: 8px 9px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 10px 24px rgba(23, 32, 51, .08);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
            opacity: 0;
            pointer-events: none;
            transform: translateY(6px);
            visibility: hidden;
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
        }

        


        .calendar-day:hover .calendar-summary,
        .calendar-day:focus-visible .calendar-summary {
            opacity: 1;
            transform: translateY(0);
            visibility: visible;
        }

        html.dark .calendar-day {
    background: #1e293b;
    border-color: #475569;
    color: #f8fafc;
}

html.dark .calendar-day:hover,
html.dark .calendar-day:focus-visible {
    background: #263449;
    border-color: #60a5fa;
    box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
}

html.dark .calendar-day.outside-month {
    background: #111827;
    opacity: .55;
}

html.dark .calendar-date-number {
    color: #f8fafc;
}

html.dark .calendar-day.weekend .calendar-date-number {
    color: #f87171;
}

html.dark .calendar-day.today {
    border-color: #60a5fa;
    box-shadow: inset 0 0 0 1px #60a5fa;
}

html.dark .calendar-day.today .calendar-date-number {
    background: #172554;
    color: #93c5fd;
}

html.dark .calendar-summary {
    background: #0f172a;
    border-color: #475569;
    color: #cbd5e1;
}

        @media (max-width: 760px) {
            .calendar-weekdays,
            .calendar-grid {
                grid-template-columns: 1fr;
            }
            .calendar-weekdays {
                display: none;
            }
            .calendar-day {
                min-height: auto;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $monthQuery = fn ($month) => ['month' => $month->month, 'year' => $month->year];
        $dateQuery = fn ($date) => ['date' => $date->toDateString(), 'month' => $calendarMonth->month, 'year' => $calendarMonth->year];
        $weekdayLabels = [
            ['label' => 'SEN', 'weekend' => false],
            ['label' => 'SEL', 'weekend' => false],
            ['label' => 'RAB', 'weekend' => false],
            ['label' => 'KAM', 'weekend' => false],
            ['label' => 'JUM', 'weekend' => false],
            ['label' => 'SAB', 'weekend' => true],
            ['label' => 'MIN', 'weekend' => true],
        ];
    @endphp

    <div class="panel">
        <div class="panel-header">
            <div class="calendar-nav">
                <a class="btn btn-small btn-soft" href="{{ route('room-availability.index', $monthQuery($previousMonth)) }}">Sebelumnya</a>
                <div class="calendar-title">{{ $calendarMonth->translatedFormat('F Y') }}</div>
                <a class="btn btn-small btn-soft" href="{{ route('room-availability.index', $monthQuery($nextMonth)) }}">Berikutnya</a>
            </div>
            <div class="muted">Klik tanggal untuk melihat detail ketersediaan.</div>
        </div>
        <div class="panel-body">
            <div class="calendar-weekdays">
                @foreach ($weekdayLabels as $weekday)
                    <div class="calendar-weekday {{ $weekday['weekend'] ? 'weekend' : '' }}">{{ $weekday['label'] }}</div>
                @endforeach
            </div>
            <div class="calendar-grid">
                @foreach ($calendarWeeks as $week)
                    @foreach ($week as $day)
                        @php
                            $date = $day['date'];
                            $dateString = $date->toDateString();
                            $summary = $calendarSummary[$dateString] ?? ['used' => 0, 'available' => 0, 'pending' => 0];
                            $isWeekend = $date->isWeekend();
                        @endphp
                        <a
                            class="calendar-day {{ $day['inMonth'] ? '' : 'outside-month' }} {{ $day['isToday'] ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                            href="{{ route('room-availability.date', $dateQuery($date)) }}"
                        >
                            <span class="calendar-date-number">{{ $date->day }}</span>
                            <span class="calendar-summary">
                                <span>Tersedia: {{ $summary['available'] }}</span>
                                <span>Terpakai: {{ $summary['used'] }}</span>
                                <span>Pending: {{ $summary['pending'] }}</span>
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
@endsection
