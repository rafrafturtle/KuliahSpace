@extends('layouts.app')

@section('title', 'Ketersediaan Ruang')
@section('subtitle', 'Cari ruangan yang kosong berdasarkan jadwal dan request ruangan.')

@section('content')
    <div class="grid grid-2">
        <div class="panel">
            <div class="panel-header"><h2>Parameter Pencarian</h2></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('room-availability.check') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="day_of_week">Hari</label>
                            <select id="day_of_week" name="day_of_week" required>
                                <option value="">Pilih hari</option>
                                @foreach ($days as $day)
                                    <option value="{{ $day }}" @selected(old('day_of_week', $criteria['day_of_week'] ?? '') === $day)>{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="date">Tanggal</label>
                            <input id="date" name="date" type="date" value="{{ old('date', $criteria['date'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label for="start_time">Mulai</label>
                            <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $criteria['start_time'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label for="end_time">Selesai</label>
                            <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $criteria['end_time'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label for="semester_id">Semester</label>
                            <select id="semester_id" name="semester_id" required>
                                <option value="">Pilih semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected(old('semester_id', $criteria['semester_id'] ?? '') === $semester->id)>
                                        {{ $semester->name }}{{ $semester->is_active ? ' (aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="academic_year_id">Tahun Akademik</label>
                            <select id="academic_year_id" name="academic_year_id" required>
                                <option value="">Pilih tahun akademik</option>
                                @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $criteria['academic_year_id'] ?? '') === $academicYear->id)>
                                        {{ $academicYear->name }}{{ $academicYear->is_active ? ' (aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="capacity">Kapasitas Minimum</label>
                            <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $criteria['capacity'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label>Request Pending</label>
                            <input type="hidden" name="include_pending" value="0">
                            <label class="checkbox-row">
                                <input type="checkbox" name="include_pending" value="1" @checked((bool) old('include_pending', $includePending))>
                                Anggap pending sebagai terpakai
                            </label>
                        </div>
                    </div>
                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Cek Ruangan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Hasil</h2>
                @if ($availableRooms !== null)
                    <span class="muted">{{ $availableRooms->count() }} ruangan tersedia</span>
                @endif
            </div>
            <div class="panel-body stack">
                @if ($availableRooms === null)
                    <div class="empty-state">Isi parameter untuk melihat ruangan yang tersedia.</div>
                @else
                    @forelse ($availableRooms as $room)
                        <div class="bento-card">
                            <h2>{{ $room->code }} - {{ $room->name }}</h2>
                            <div class="muted">{{ $room->building ?? 'Gedung belum diisi' }} · Kapasitas {{ $room->capacity }}</div>
                            @if ($room->facilities)
                                <div style="margin-top: 8px;">{{ $room->facilities }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">Tidak ada ruangan yang tersedia untuk parameter ini.</div>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
@endsection
