@extends('layouts.app')

@section('title', 'Detail Mata Kuliah')
@section('subtitle', $course->code . ' - ' . $course->name)

@section('content')
    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Informasi Mata Kuliah</h2>
                <a class="btn btn-small btn-soft" href="{{ route('courses.edit', $course) }}">Edit</a>
            </div>
            <div class="panel-body">
                <dl class="detail-list">
                    <dt>Kode</dt><dd>{{ $course->code }}</dd>
                    <dt>Nama</dt><dd>{{ $course->name }}</dd>
                    <dt>SKS</dt><dd>{{ $course->credits ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><h2>Jadwal Kuliah</h2></div>
            <div class="panel-body stack">
                @forelse ($course->classSchedules->take(6) as $schedule)
                    <div>
                        <strong>{{ $schedule->class_name }} - {{ $schedule->room?->code }}</strong>
                        <div class="muted">{{ $schedule->lecturer?->name }} · {{ $schedule->day_of_week }} {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }}</div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada jadwal untuk mata kuliah ini.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
