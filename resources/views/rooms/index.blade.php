@extends('layouts.app')

@section('title', 'Ruangan')
@section('subtitle', 'Kelola ruang kuliah, kapasitas, fasilitas, dan status aktif.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('rooms.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama, gedung">
                <button class="btn btn-soft" type="submit">Cari</button>
            </form>
            <a class="btn btn-primary" href="{{ route('rooms.create') }}">Tambah Ruangan</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Lokasi</th>
                    <th>Kapasitas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td><strong>{{ $room->code }}</strong></td>
                        <td>{{ $room->name }}</td>
                        <td>{{ trim(($room->building ?? '-') . ' ' . ($room->floor ? 'Lt. '.$room->floor : '')) }}</td>
                        <td>{{ $room->capacity }}</td>
                        <td>@include('partials.status-badge', ['status' => $room->is_active])</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small" href="{{ route('rooms.show', $room) }}">Detail</a>
                                <a class="btn btn-small btn-soft" href="{{ route('rooms.edit', $room) }}">Edit</a>
                                <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Belum ada data ruangan.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $rooms->links() }}</div>
@endsection
