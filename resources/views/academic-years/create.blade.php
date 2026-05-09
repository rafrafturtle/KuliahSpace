@extends('layouts.app')

@section('title', 'Tambah Tahun Akademik')
@section('subtitle', 'Buat periode akademik baru.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('academic-years.store') }}">
                @include('academic-years._form', ['submitLabel' => 'Simpan Tahun Akademik'])
            </form>
        </div>
    </div>
@endsection
