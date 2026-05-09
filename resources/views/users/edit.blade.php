@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('subtitle', $user->name)

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @method('PUT')
                @include('users._form', ['submitLabel' => 'Perbarui Pengguna'])
            </form>
        </div>
    </div>
@endsection
