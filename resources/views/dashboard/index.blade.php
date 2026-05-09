@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan cepat ruangan, jadwal, dan permintaan terbaru.')

@section('content')
    <div class="grid grid-4">
        <div class="bento-card">
            <h2>Ruang Aktif</h2>
            <div class="metric">{{ $totalActiveRooms }}</div>
            <div class="muted">Ruangan siap digunakan</div>
        </div>
        <div class="bento-card">
            <h2>Jadwal Aktif</h2>
            <div class="metric">{{ $totalActiveSchedules }}</div>
            <div class="muted">Perkuliahan terjadwal</div>
        </div>
        <div class="bento-card">
            <h2>Request Pending</h2>
            <div class="metric">{{ $pendingRoomRequests }}</div>
            <div class="muted">Menunggu keputusan admin</div>
        </div>
        <div class="bento-card">
            <h2>Request Disetujui</h2>
            <div class="metric">{{ $approvedRoomRequests }}</div>
            <div class="muted">Permintaan yang sudah final</div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-top: 16px;">
        <div class="bento-card">
            <h2>Ruang Tersedia Hari Ini</h2>
            <div class="metric">{{ $availableRoomsToday }}</div>
            <div class="muted">
                Berdasarkan {{ $activeSemester?->name ?? 'semester aktif belum ada' }} dan
                {{ $activeAcademicYear?->name ?? 'tahun akademik aktif belum ada' }}.
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Permintaan Terbaru</h2>
                <a class="btn btn-small btn-soft" href="{{ route('room-requests.index') }}">Lihat semua</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Ruang</th>
                        <th>Pemohon</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($recentRoomRequests as $request)
                        <tr>
                            <td>{{ $request->room?->code }}</td>
                            <td>{{ $request->requester?->name }}</td>
                            <td>{{ $request->request_date->format('d M Y') }}</td>
                            <td>@include('partials.status-badge', ['status' => $request->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">Belum ada permintaan ruangan.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
