@extends('layouts.app')

@section('title', 'Tambah Ruangan')
@section('subtitle', 'Masukkan informasi dasar ruangan.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('rooms.store') }}">
                @include('rooms._form', ['submitLabel' => 'Simpan Ruangan'])
            </form>
        </div>
    </div>
@endsection
