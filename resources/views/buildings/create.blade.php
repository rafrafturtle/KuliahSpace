@extends('layouts.app')

@section('title', 'Tambah Gedung')
@section('subtitle', 'Masukkan informasi dasar gedung.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('buildings.store') }}">
                @include('buildings._form', ['submitLabel' => 'Simpan Gedung'])
            </form>
        </div>
    </div>
@endsection
