@extends('layouts.app')

@section('title', 'Permintaan Ruangan')
@section('subtitle', 'Ajukan, tinjau, setujui, atau tolak penggunaan ruangan.')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="search-form" method="GET" action="{{ route('room-requests.index') }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari pemohon, ruangan, tujuan">
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\RoomRequest::STATUSES as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->headline() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-soft" type="submit">Filter</button>
            </form>
            @if ($canCreateRoomRequest)
                <a class="btn btn-primary" href="{{ route('room-requests.create') }}">Ajukan Ruangan</a>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Ruang</th>
                    <th>Pemohon</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Tujuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($roomRequests as $request)
                    <tr>
                        <td><strong>{{ $request->room?->code }}</strong><br>{{ $request->room?->name }}</td>
                        <td>{{ $request->requester?->name }}</td>
                        <td>{{ $request->request_date->format('d M Y') }}<br><span class="muted">{{ $request->day_of_week }}</span></td>
                        <td>{{ substr($request->start_time, 0, 5) }}-{{ substr($request->end_time, 0, 5) }}</td>
                        <td>{{ str($request->purpose)->limit(60) }}</td>
                        <td>@include('partials.status-badge', ['status' => $request->status])</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-small" href="{{ route('room-requests.show', $request) }}">Detail</a>
                                <a class="btn btn-small btn-soft" href="{{ route('room-requests.edit', $request) }}">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">Belum ada permintaan ruangan.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{{ $roomRequests->links() }}</div>
@endsection
