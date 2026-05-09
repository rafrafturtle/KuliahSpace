@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')
@section('subtitle', 'Buat data mata kuliah baru.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('courses.store') }}">
                @include('courses._form', ['submitLabel' => 'Simpan Mata Kuliah'])
            </form>
        </div>
    </div>
@endsection
