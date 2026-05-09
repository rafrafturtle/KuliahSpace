@extends('layouts.app')

@section('title', 'Pengguna & Role')
@section('subtitle', 'Kelola pengguna demo dan struktur role Bouncer.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('users.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('users.create') }}">Tambah Pengguna</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->roles as $role)
                                @include('partials.status-badge', ['status' => 'active', 'label' => $role->title ?? str($role->name)->headline()])
                            @empty
                                <span class="muted">Belum ada role</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small" href="{{ route('users.show', $user) }}">Detail</a>
                                <a class="btn btn-small btn-soft" href="{{ route('users.edit', $user) }}">Edit</a>
                                <a class="btn btn-small" href="{{ route('users.roles.edit', $user) }}">Role</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">Belum ada data pengguna.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $users->links() }}</div>
@endsection
