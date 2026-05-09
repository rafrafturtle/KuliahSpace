@extends('layouts.app')

@section('title', 'Kelola Role')
@section('subtitle', $user->name)

@section('content')
    <div class="panel">
        <div class="panel-header"><h2>Role Pengguna</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('users.roles.update', $user) }}" class="stack">
                @csrf
                @foreach ($roles as $name => $title)
                    <label class="checkbox-row">
                        <input type="checkbox" name="roles[]" value="{{ $name }}" @checked($user->roles->contains('name', $name))>
                        {{ $title }}
                    </label>
                @endforeach
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Simpan Role</button>
                    <a class="btn" href="{{ route('users.show', $user) }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
