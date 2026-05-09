@extends('layouts.app')

@section('title', 'Detail Ruangan')
@section('subtitle', $room->code . ' - ' . $room->name)

@section('content')
    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Informasi Ruangan</h2>
                <a class="btn btn-small btn-soft" href="{{ route('rooms.edit', $room) }}">Edit</a>
            </div>
            <div class="panel-body">
                <dl class="detail-list">
                    <dt>Kode</dt><dd>{{ $room->code }}</dd>
                    <dt>Nama</dt><dd>{{ $room->name }}</dd>
                    <dt>Gedung</dt><dd>{{ $room->building ?? '-' }}</dd>
                    <dt>Lantai</dt><dd>{{ $room->floor ?? '-' }}</dd>
                    <dt>Kapasitas</dt><dd>{{ $room->capacity }}</dd>
                    <dt>Fasilitas</dt><dd>{{ $room->facilities ?? '-' }}</dd>
                    <dt>Status</dt><dd>@include('partials.status-badge', ['status' => $room->is_active])</dd>
                </dl>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><h2>Jadwal Terkait</h2></div>
            <div class="panel-body stack">
                @forelse ($room->classSchedules->take(6) as $schedule)
                    <div>
                        <strong>{{ $schedule->course?->code }} - {{ $schedule->class_name }}</strong>
                        <div class="muted">{{ $schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }}</div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada jadwal untuk ruangan ini.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
