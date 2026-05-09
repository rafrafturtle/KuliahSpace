@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('subtitle', $user->name)

@section('content')
    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Profil</h2>
                <div class="actions">
                    <a class="btn btn-small" href="{{ route('users.roles.edit', $user) }}">Kelola Role</a>
                    <a class="btn btn-small btn-soft" href="{{ route('users.edit', $user) }}">Edit</a>
                </div>
            </div>
            <div class="panel-body">
                <dl class="detail-list">
                    <dt>Nama</dt><dd>{{ $user->name }}</dd>
                    <dt>Email</dt><dd>{{ $user->email }}</dd>
                    <dt>Role</dt>
                    <dd>
                        @forelse ($user->roles as $role)
                            @include('partials.status-badge', ['status' => 'active', 'label' => $role->title ?? str($role->name)->headline()])
                        @empty
                            <span class="muted">Belum ada role</span>
                        @endforelse
                    </dd>
                </dl>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><h2>Aktivitas Akademik</h2></div>
            <div class="panel-body stack">
                <div>
                    <strong>Jadwal sebagai dosen</strong>
                    <div class="muted">{{ $user->classSchedules->count() }} jadwal</div>
                </div>
                <div>
                    <strong>Permintaan ruangan</strong>
                    <div class="muted">{{ $user->roomRequests->count() }} permintaan</div>
                </div>
            </div>
        </div>
    </div>
@endsection
