@extends('layouts.app')

@section('title', 'Edit Jadwal')
@section('subtitle', $schedule->course?->code . ' - ' . $schedule->class_name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('schedules.update', $schedule) }}">
                @method('PUT')
                @include('schedules._form', ['submitLabel' => 'Perbarui Jadwal'])
            </form>
        </div>
    </div>
@endsection
