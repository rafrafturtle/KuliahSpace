@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('subtitle', 'Buat pengguna demo untuk role akademik.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('users.store') }}">
                @include('users._form', ['submitLabel' => 'Simpan Pengguna'])
            </form>
        </div>
    </div>
@endsection
