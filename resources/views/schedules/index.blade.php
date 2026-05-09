@extends('layouts.app')

@section('title', 'Jadwal Kuliah Mingguan')
@section('subtitle', 'Lihat ruangan terpakai dan tersedia dari Senin sampai Minggu.')

@section('content')
    @php($canManageSchedules = auth()->user()->isAn('admin'))

    <div class="panel" style="margin-bottom: 18px;">
        <div class="panel-header">
            <h2>Filter Jadwal</h2>
            @if ($canManageSchedules)
                <a class="btn btn-primary" href="{{ route('schedules.create') }}">Tambah Jadwal</a>
            @endif
        </div>
        <div class="panel-body">
            <form class="form-grid" method="GET" action="{{ route('schedules.index') }}">
                <div class="form-field">
                    <label for="search">Cari</label>
                    <input id="search" type="text" name="search" value="{{ $search }}" placeholder="Kelas, mata kuliah, ruang">
                </div>
                <div class="form-field">
                    <label for="day_of_week">Hari</label>
                    <select id="day_of_week" name="day_of_week">
                        <option value="">Semua hari</option>
                        @foreach ($dayLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['day_of_week'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="start_time">Mulai</label>
                    <input id="start_time" type="time" name="start_time" value="{{ $filters['start_time'] ?? '' }}">
                </div>
                <div class="form-field">
                    <label for="end_time">Selesai</label>
                    <input id="end_time" type="time" name="end_time" value="{{ $filters['end_time'] ?? '' }}">
                </div>
                <div class="form-field">
                    <label for="semester_id">Semester</label>
                    <select id="semester_id" name="semester_id">
                        <option value="">Semua semester</option>
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}" @selected(($filters['semester_id'] ?? '') === $semester->id)>
                                {{ $semester->name }}{{ $semester->is_active ? ' (aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="academic_year_id">Tahun Akademik</label>
                    <select id="academic_year_id" name="academic_year_id">
                        <option value="">Semua tahun</option>
                        @foreach ($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" @selected(($filters['academic_year_id'] ?? '') === $academicYear->id)>
                                {{ $academicYear->name }}{{ $academicYear->is_active ? ' (aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field full">
                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                        <a class="btn" href="{{ route('schedules.index') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach ($weeklySchedule as $dayData)
        <section class="panel day-section">
            <div class="panel-header">
                <div>
                    <h2>{{ $dayData['label'] }}</h2>
                    <div class="day-summary">
                        <span>{{ $dayData['usedSchedules']->count() }} jadwal aktif</span>
                        <span>{{ $dayData['availableRooms']->count() }} ruangan tersedia</span>
                        @if ($hasTimeRange)
                            <span>{{ $filters['start_time'] }}-{{ $filters['end_time'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="panel-body stack">
                <div>
                    <div class="panel-header" style="padding: 0 0 12px; border-bottom: 0;">
                        <h2>Ruangan Terpakai</h2>
                    </div>
                    @if ($dayData['usedSchedules']->isEmpty())
                        <div class="empty-state">Tidak ada ruangan terpakai pada hari ini.</div>
                    @else
                        <div class="room-card-grid">
                            @foreach ($dayData['usedSchedules'] as $schedule)
                                <article class="room-card">
                                    <div class="actions" style="justify-content: space-between;">
                                        <h3>{{ $schedule->room?->code }} - {{ $schedule->room?->name }}</h3>
                                        @include('partials.status-badge', ['status' => 'used'])
                                    </div>
                                    <div>
                                        <strong>{{ $schedule->course?->name }}</strong>
                                        <div class="muted">{{ $schedule->course?->code }}</div>
                                    </div>
                                    <div class="room-card-meta">
                                        <span>Dosen: {{ $schedule->lecturer?->name }}</span>
                                        <span>Kelas: {{ $schedule->class_name }}</span>
                                        <span>Waktu: {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }}</span>
                                    </div>
                                    <div class="actions">
                                        <a class="btn btn-small" href="{{ route('schedules.show', $schedule) }}">Detail</a>
                                        @if ($canManageSchedules)
                                            <a class="btn btn-small btn-soft" href="{{ route('schedules.edit', $schedule) }}">Edit</a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <div class="panel-header" style="padding: 4px 0 12px; border-bottom: 0;">
                        <h2>Ruangan Tersedia</h2>
                    </div>
                    @if ($dayData['availableRooms']->isEmpty())
                        <div class="empty-state">Semua ruangan aktif sedang terpakai untuk filter ini.</div>
                    @else
                        <div class="room-card-grid">
                            @foreach ($dayData['availableRooms'] as $room)
                                <article class="room-card">
                                    <div class="actions" style="justify-content: space-between;">
                                        <h3>{{ $room->code }} - {{ $room->name }}</h3>
                                        @include('partials.status-badge', ['status' => 'available'])
                                    </div>
                                    <div class="room-card-meta">
                                        <span>Gedung: {{ $room->building ?? '-' }}</span>
                                        <span>Kapasitas: {{ $room->capacity }}</span>
                                        <span>Fasilitas: {{ $room->facilities ?? '-' }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endforeach
@endsection
