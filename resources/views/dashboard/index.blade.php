@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan cepat ruangan, jadwal, dan permintaan terbaru.')

@section('content')
    <div class="grid grid-4">
        <div class="bento-card">
            <div class="actions" style="justify-content: space-between;">
                <h2>Ruang Aktif</h2>
                <span class="nav-icon material-symbols-rounded" style="background:#e8f3ff;color:#0b60eb;">meeting_room</span>
            </div>
            <div class="metric">{{ $totalActiveRooms }}</div>
            <div class="muted">Ruangan siap digunakan</div>
        </div>

        <div class="bento-card">
            <div class="actions" style="justify-content: space-between;">
                <h2>Jadwal Aktif</h2>
                <span class="nav-icon material-symbols-rounded" style="background:#e8f3ff;color:#0b60eb;">calendar_month</span>
            </div>
            <div class="metric">{{ $totalActiveSchedules }}</div>
            <div class="muted">Perkuliahan terjadwal</div>
        </div>

        <div class="bento-card">
            <div class="actions" style="justify-content: space-between;">
                <h2>Request Pending</h2>
                <span class="nav-icon material-symbols-rounded" style="background:#fff7df;color:#b77905;">pending_actions</span>
            </div>
            <div class="metric">{{ $pendingRoomRequests }}</div>
            <div class="muted">Menunggu keputusan admin</div>
        </div>

        <div class="bento-card">
            <div class="actions" style="justify-content: space-between;">
                <h2>Request Disetujui</h2>
                <span class="nav-icon material-symbols-rounded" style="background:#e8f8f1;color:#0f8f65;">task_alt</span>
            </div>
            <div class="metric">{{ $approvedRoomRequests }}</div>
            <div class="muted">Permintaan yang sudah final</div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-top: 18px;">
        <div class="bento-card">
            <div class="actions" style="justify-content: space-between;">
                <h2>Ruang Tersedia Hari Ini</h2>
                <span class="nav-icon material-symbols-rounded" style="background:#e8f8f1;color:#0f8f65;">event_seat</span>
            </div>
            <div class="metric">{{ $availableRoomsToday }}</div>
            <div class="muted">
                Berdasarkan {{ $activeSemester?->name ?? 'semester aktif belum ada' }} dan
                {{ $activeAcademicYear?->name ?? 'tahun akademik aktif belum ada' }}.
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Permintaan Terbaru</h2>
                <a class="btn btn-small btn-soft" href="{{ route('room-requests.index') }}">
                    <span class="material-symbols-rounded" style="font-size: 18px;">visibility</span>
                    Lihat semua
                </a>
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

    <div class="grid grid-2" style="margin-top: 18px;">
        <div class="panel chart-panel">
            <div class="panel-header">
                <div>
                    <h2>Statistik Penggunaan Ruang</h2>
                    <p class="muted">Jumlah permintaan ruang per bulan tahun ini</p>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="roomRequestChart"></canvas>
            </div>
        </div>

        <div class="panel chart-panel">
            <div class="panel-header">
                <div>
                    <h2>Status Permintaan</h2>
                    <p class="muted">Perbandingan status request ruangan</p>
                </div>
            </div>

            <div class="chart-box">
                <canvas id="statusRequestChart"></canvas>
            </div>
        </div>
    </div>

    <div class="panel top-room-panel" style="margin-top: 18px;">
        <div class="panel-header">
            <div>
                <h2>Top 5 Ruangan Paling Sering Diminta</h2>
                <p class="muted">Berdasarkan jumlah permintaan ruangan di database</p>
            </div>
        </div>

        <div class="top-room-list">
            @forelse ($topRooms as $index => $item)
                <div class="top-room-item">
                    <div class="top-room-left">
                        <div class="top-room-rank">{{ $index + 1 }}</div>
                        <div>
                            <strong>{{ $item->room?->name ?? 'Ruangan tidak ditemukan' }}</strong>
                            <div class="muted">{{ $item->room?->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="top-room-right">
                        <span>{{ $item->total }}x diminta</span>
                        <div class="top-room-bar">
                            <div class="top-room-progress"
                                 style="width: {{ $topRooms->max('total') > 0 ? ($item->total / $topRooms->max('total')) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada data penggunaan ruangan.</div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
<style>
    .chart-panel {
        min-height: 390px;
    }

    .chart-box {
        position: relative;
        width: 100%;
        height: 300px;
        margin-top: 10px;
    }

    .chart-box canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .top-room-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 14px;
    }

    .top-room-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.95), rgba(239, 246, 255, 0.9));
        border: 1px solid rgba(37, 99, 235, 0.10);
    }

    .top-room-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .top-room-rank {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #e8f3ff;
        color: #0b60eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .top-room-right {
        min-width: 240px;
        text-align: right;
        font-weight: 700;
        color: #0f172a;
    }

    .top-room-bar {
        width: 240px;
        height: 9px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 8px;
    }

    .top-room-progress {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #2563eb, #38bdf8);
    }

    @media (max-width: 768px) {
        .top-room-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .top-room-right,
        .top-room-bar {
            width: 100%;
            min-width: 100%;
            text-align: left;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const roomRequestChart = document.getElementById('roomRequestChart');

if (roomRequestChart) {
    new Chart(roomRequestChart, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Permintaan Ruang',
                data: @json($monthlyRequestData),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.14)',
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 4,
                tension: 0.42,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(37, 99, 235, 0.08)'
                    },
                    ticks: {
                        color: '#47638f'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(37, 99, 235, 0.10)'
                    },
                    ticks: {
                        color: '#47638f',
                        precision: 0
                    }
                }
            }
        }
    });
}

    const statusRequestChart = document.getElementById('statusRequestChart');

    if (statusRequestChart) {
        new Chart(statusRequestChart, {
            type: 'doughnut',
            data: {
                labels: ['Disetujui', 'Pending', 'Ditolak', 'Dibatalkan'],
                datasets: [{
                    data: @json($statusRequestData),
                    backgroundColor: [
                        '#22c55e',
                        '#f59e0b',
                        '#ef4444',
                        '#64748b'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 5,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#1e3a8a',
                            padding: 18,
                            usePointStyle: true,
                            font: {
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush