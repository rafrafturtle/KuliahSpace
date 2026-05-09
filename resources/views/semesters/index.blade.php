@extends('layouts.app')

@section('title', 'Semester')
@section('subtitle', 'Kelola semester aktif untuk jadwal kuliah.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('semesters.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari semester">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('semesters.create') }}">Tambah Semester</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($semesters as $semester)
                    <tr>
                        <td><strong>{{ $semester->name }}</strong></td>
                        <td>@include('partials.status-badge', ['status' => $semester->is_active])</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small btn-soft" href="{{ route('semesters.edit', $semester) }}">Edit</a>
                                <form method="POST" action="{{ route('semesters.destroy', $semester) }}" onsubmit="return confirm('Hapus semester ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty-state">Belum ada data semester.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $semesters->links() }}</div>
@endsection
