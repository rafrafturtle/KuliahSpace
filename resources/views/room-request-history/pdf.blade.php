<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Pengajuan Ruang</title>
    <style>
        @page { margin: 18px; }
        body {
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
        }
        h1 {
            margin: 0 0 5px;
            font-size: 18px;
        }
        .meta {
            margin: 0 0 12px;
            color: #667085;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            padding: 6px;
            border: 1px solid #dbe3ea;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f8fafc;
            color: #344054;
            font-size: 9px;
            text-transform: uppercase;
        }
        .status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: 700;
        }
        .status-approved {
            background: #edf7f0;
            color: #296246;
        }
        .status-rejected {
            background: #fff0f0;
            color: #b13a3a;
        }
        .status-cancelled {
            background: #eef2f7;
            color: #536172;
        }
        .empty {
            color: #667085;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Riwayat Pengajuan Ruang</h1>
    <p class="meta">
        @if ($filterSummary)
            Filter tanggal: {{ $filterSummary }}
        @else
            Semua riwayat pengajuan ruang
        @endif
    </p>

    <table>
        <thead>
        <tr>
            <th>Nama Pengaju</th>
            <th>Ruangan</th>
            <th>Tanggal Pengajuan</th>
            <th>Hari</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Keperluan</th>
            <th>Status</th>
            <th>Catatan Admin</th>
            <th>Disetujui/Ditolak Oleh</th>
            <th>Waktu Diproses</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($roomRequests as $roomRequest)
            <tr>
                <td>{{ $roomRequest->requester?->name ?? '-' }}</td>
                <td>{{ $roomRequest->room?->code ?? '-' }} - {{ $roomRequest->room?->name ?? '-' }}</td>
                <td>{{ $roomRequest->request_date?->format('d M Y') ?? '-' }}</td>
                <td>{{ \App\Models\RoomRequest::dayLabel($roomRequest->day_of_week) }}</td>
                <td>{{ substr((string) $roomRequest->start_time, 0, 5) }}</td>
                <td>{{ substr((string) $roomRequest->end_time, 0, 5) }}</td>
                <td>{{ $roomRequest->purpose }}</td>
                <td>
                    <span class="status status-{{ $roomRequest->status }}">
                        {{ \App\Models\RoomRequest::statusLabel($roomRequest->status) }}
                    </span>
                </td>
                <td>{{ $roomRequest->admin_note ?? '-' }}</td>
                <td>{{ $roomRequest->approver?->name ?? '-' }}</td>
                <td>{{ $roomRequest->approved_at?->format('d M Y H:i') ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td class="empty" colspan="11">Belum ada riwayat pengajuan ruang.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
