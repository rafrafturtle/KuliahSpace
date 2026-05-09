@extends('layouts.app')

@section('title', 'Tambah Jadwal')
@section('subtitle', 'Jadwal aktif akan dicek agar tidak bentrok ruangan.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('schedules.store') }}">
                @include('schedules._form', ['submitLabel' => 'Simpan Jadwal'])
            </form>
        </div>
    </div>
@endsection
