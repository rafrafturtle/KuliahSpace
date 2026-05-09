@extends('layouts.app')

@section('title', 'Detail Jadwal')
@section('subtitle', $schedule->course?->code . ' - ' . $schedule->class_name)

@section('content')
    <div class="panel">
        <div class="panel-header">
            <h2>Informasi Jadwal</h2>
            @if (auth()->user()->isAn('admin'))
                <a class="btn btn-small btn-soft" href="{{ route('schedules.edit', $schedule) }}">Edit</a>
            @endif
        </div>
        <div class="panel-body">
            <dl class="detail-list">
                <dt>Mata Kuliah</dt><dd>{{ $schedule->course?->code }} - {{ $schedule->course?->name }}</dd>
                <dt>Kelas</dt><dd>{{ $schedule->class_name }}</dd>
                <dt>Dosen</dt><dd>{{ $schedule->lecturer?->name }}</dd>
                <dt>Ruangan</dt><dd>{{ $schedule->room?->code }} - {{ $schedule->room?->name }}</dd>
                <dt>Hari</dt><dd>{{ $schedule->day_of_week }}</dd>
                <dt>Waktu</dt><dd>{{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }}</dd>
                <dt>Minggu Ke</dt><dd>{{ $schedule->week_number ?? '-' }}</dd>
                <dt>Semester</dt><dd>{{ $schedule->semester?->name }}</dd>
                <dt>Tahun Akademik</dt><dd>{{ $schedule->academicYear?->name }}</dd>
                <dt>Status</dt><dd>@include('partials.status-badge', ['status' => $schedule->is_active])</dd>
            </dl>
        </div>
    </div>
@endsection
