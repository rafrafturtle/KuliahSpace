@extends('layouts.app')

@section('title', 'Detail Permintaan')
@section('subtitle', $roomRequest->room?->code . ' · ' . $roomRequest->request_date->format('d M Y'))

@section('content')
    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header">
                <h2>Informasi Permintaan</h2>
                <a class="btn btn-small btn-soft" href="{{ route('room-requests.edit', $roomRequest) }}">Edit</a>
            </div>
            <div class="panel-body">
                <dl class="detail-list">
                    <dt>Pemohon</dt><dd>{{ $roomRequest->requester?->name }}</dd>
                    <dt>Ruangan</dt><dd>{{ $roomRequest->room?->code }} - {{ $roomRequest->room?->name }}</dd>
                    <dt>Tanggal</dt><dd>{{ $roomRequest->request_date->format('d M Y') }}</dd>
                    <dt>Hari</dt><dd>{{ $roomRequest->day_of_week }}</dd>
                    <dt>Waktu</dt><dd>{{ substr($roomRequest->start_time, 0, 5) }}-{{ substr($roomRequest->end_time, 0, 5) }}</dd>
                    <dt>Status</dt><dd>@include('partials.status-badge', ['status' => $roomRequest->status])</dd>
                    <dt>Disetujui Oleh</dt><dd>{{ $roomRequest->approver?->name ?? '-' }}</dd>
                    <dt>Disetujui Pada</dt><dd>{{ $roomRequest->approved_at?->format('d M Y H:i') ?? '-' }}</dd>
                    <dt>Tujuan</dt><dd>{{ $roomRequest->purpose }}</dd>
                    <dt>Catatan Admin</dt><dd>{{ $roomRequest->admin_note ?? '-' }}</dd>
                </dl>
            </div>
        </div>

        @if ($canApproveRequests)
            <div class="panel">
                <div class="panel-header"><h2>Keputusan Admin</h2></div>
                <div class="panel-body stack">
                    <form method="POST" action="{{ route('room-requests.approve', $roomRequest) }}" class="stack">
                        @csrf
                        @method('PATCH')
                        <div class="form-field">
                            <label for="approve_note">Catatan</label>
                            <textarea id="approve_note" name="admin_note">{{ old('admin_note', $roomRequest->admin_note) }}</textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Setujui</button>
                    </form>

                    <form method="POST" action="{{ route('room-requests.reject', $roomRequest) }}" class="stack">
                        @csrf
                        @method('PATCH')
                        <div class="form-field">
                            <label for="reject_note">Catatan Penolakan</label>
                            <textarea id="reject_note" name="admin_note">{{ old('admin_note') }}</textarea>
                        </div>
                        <button class="btn btn-danger" type="submit">Tolak</button>
                    </form>

                    <form method="POST" action="{{ route('room-requests.cancel', $roomRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn" type="submit">Batalkan Permintaan</button>
                    </form>
                </div>
            </div>
        @else
            <div class="panel">
                <div class="panel-header"><h2>Aksi Permintaan</h2></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('room-requests.cancel', $roomRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn" type="submit">Batalkan Permintaan</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
