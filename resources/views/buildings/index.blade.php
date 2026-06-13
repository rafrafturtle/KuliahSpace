@extends('layouts.app')

@section('title', 'Gedung')
@section('subtitle', 'Kelola gedung, lantai, dan ruangan yang berada di dalamnya.')

@section('content')
    @php($canManageBuildings = auth()->user()->isAn('admin'))

    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('buildings.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, kode, lantai">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            @if ($canManageBuildings)
                <a class="btn btn-primary" href="{{ route('buildings.create') }}">Tambah Gedung</a>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kode</th>
                    <th>Lantai</th>
                    <th>Ruangan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($buildings as $building)
                    <tr>
                        <td><strong>{{ $building->name }}</strong></td>
                        <td>{{ $building->code ?? '-' }}</td>
                        <td>{{ $building->floor ?? '-' }}</td>
                        <td>{{ $building->rooms_count }}</td>
                        <td>@include('partials.status-badge', ['status' => $building->is_active])</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small" href="{{ route('buildings.show', $building) }}">Detail</a>
                                @if ($canManageBuildings)
                                    <a class="btn btn-small btn-soft" href="{{ route('buildings.edit', $building) }}">Edit</a>
                                    <form method="POST" action="{{ route('buildings.destroy', $building) }}" onsubmit="return confirm('Hapus gedung ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Belum ada data gedung.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $buildings->links() }}</div>
@endsection
