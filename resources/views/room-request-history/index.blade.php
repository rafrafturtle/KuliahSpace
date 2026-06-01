@extends('layouts.app')

@section('title', 'Riwayat Pengajuan Ruang')
@section('subtitle', 'Arsip pengajuan ruang yang sudah disetujui, ditolak, atau dibatalkan.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.dataTables.min.css') }}">
    <style>
        .history-filter {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 260px)) minmax(0, 1fr);
            gap: 12px;
            align-items: end;
        }
        .history-filter .actions { justify-content: flex-end; }
        .history-summary {
            margin-top: 10px;
            color: var(--muted);
            font-size: 13px;
        }
        #historyTable { min-width: 1180px; }
        #historyTable td { font-size: 14px; }
        #historyTable .purpose-cell,
        #historyTable .note-cell {
            max-width: 240px;
        }
        .dataTables_wrapper {
            padding: 16px 20px 20px;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--muted);
            font-size: 13px;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 7px 9px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--blue-soft);
            border-color: #cae0f3;
            color: #194f75 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--surface-muted);
            border-color: var(--line);
            color: var(--text) !important;
        }

        @media (max-width: 980px) {
            .history-filter {
                grid-template-columns: 1fr;
            }
            .history-filter .actions {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Riwayat Pengajuan Ruang</h2>
                    <div class="muted">Pending tetap berada di halaman Permintaan Ruang.</div>
                </div>
                <a class="btn btn-soft" href="{{ route('room-requests.index') }}">Permintaan Aktif</a>
            </div>
            <div class="panel-body">
                <form class="history-filter" method="GET" action="{{ route('room-request-history.index') }}">
                    <div class="form-field">
                        <label for="start_date">Tanggal Mulai</label>
                        <input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div class="form-field">
                        <label for="end_date">Tanggal Akhir</label>
                        <input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    <div class="actions">
                        <button class="btn btn-soft" type="submit">Filter</button>
                        <a class="btn" href="{{ route('room-request-history.index') }}">Reset</a>
                        <a class="btn btn-soft" href="{{ route('room-request-history.export-pdf', $filters) }}">Export PDF</a>
                        <a class="btn btn-primary" href="{{ route('room-request-history.export-excel', $filters) }}">Export Excel</a>
                    </div>
                </form>
                @if ($filterSummary)
                    <div class="history-summary">Filter tanggal: {{ $filterSummary }}</div>
                @endif
            </div>
        </div>

        <div class="panel">
            <div class="table-wrap">
                <table id="historyTable" class="display nowrap">
                    <thead>
                    <tr>
                        <th>Nama Pengaju</th>
                        <th>Role</th>
                        <th>Ruangan</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Hari</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Keperluan</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                        <th>Diproses Oleh</th>
                        <th>Waktu Diproses</th>
                        <th>Dibuat Pada</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($roomRequests as $roomRequest)
                        @php
                            $roles = $roomRequest->requester?->roles
                                ?->map(fn ($role) => $role->title ?: str($role->name)->headline()->toString())
                                ->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $roomRequest->requester?->name ?? '-' }}</td>
                            <td>{{ $roles ?: '-' }}</td>
                            <td><strong>{{ $roomRequest->room?->code ?? '-' }}</strong><br>{{ $roomRequest->room?->name ?? '-' }}</td>
                            <td data-order="{{ $roomRequest->request_date?->format('Y-m-d') }}">{{ $roomRequest->request_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ \App\Models\RoomRequest::dayLabel($roomRequest->day_of_week) }}</td>
                            <td>{{ substr((string) $roomRequest->start_time, 0, 5) }}</td>
                            <td>{{ substr((string) $roomRequest->end_time, 0, 5) }}</td>
                            <td class="purpose-cell">{{ $roomRequest->purpose }}</td>
                            <td data-order="{{ $roomRequest->status }}">@include('partials.status-badge', ['status' => $roomRequest->status])</td>
                            <td class="note-cell">{{ $roomRequest->admin_note ?? '-' }}</td>
                            <td>{{ $roomRequest->approver?->name ?? '-' }}</td>
                            <td data-order="{{ $roomRequest->approved_at?->timestamp ?? 0 }}">{{ $roomRequest->approved_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td data-order="{{ $roomRequest->created_at?->timestamp ?? 0 }}">{{ $roomRequest->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/datatables/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.responsive.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (! window.jQuery || ! jQuery.fn.DataTable) {
                return;
            }

            jQuery('#historyTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada riwayat pengajuan ruang.',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });
        });
    </script>
@endpush
