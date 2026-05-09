@extends('layouts.app')

@section('title', 'Tambah Semester')
@section('subtitle', 'Buat data semester baru.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('semesters.store') }}">
                @include('semesters._form', ['submitLabel' => 'Simpan Semester'])
            </form>
        </div>
    </div>
@endsection
