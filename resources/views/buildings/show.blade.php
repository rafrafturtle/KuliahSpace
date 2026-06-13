@extends('layouts.app')

@section('title', 'Detail Gedung')
@section('subtitle', $building->name)

@section('content')
    @php($canManageBuildings = auth()->user()->isAn('admin'))

    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Informasi Gedung</h2>
                @if ($canManageBuildings)
                    <a class="btn btn-small btn-soft" href="{{ route('buildings.edit', $building) }}">Edit</a>
                @endif
            </div>
            <div class="panel-body">
                <dl class="detail-list">
                    <dt>Nama</dt><dd>{{ $building->name }}</dd>
                    <dt>Kode</dt><dd>{{ $building->code ?? '-' }}</dd>
                    <dt>Lantai</dt><dd>{{ $building->floor ?? '-' }}</dd>
                    <dt>Deskripsi</dt><dd>{{ $building->description ?? '-' }}</dd>
                    <dt>Status</dt><dd>@include('partials.status-badge', ['status' => $building->is_active])</dd>
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Ruangan di Gedung Ini</h2>
                <span class="muted">{{ $building->rooms->count() }} ruangan</span>
            </div>
            <div class="panel-body stack">
                @forelse ($building->rooms as $room)
                    <div>
                        <strong>{{ $room->code }} - {{ $room->name }}</strong>
                        <div class="muted">Kapasitas {{ $room->capacity }} | {{ $room->facilities ?? 'Fasilitas belum diisi' }}</div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada ruangan untuk gedung ini.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
