@extends('layouts.app')

@section('title', 'Pencarian Ruangan')
@section('subtitle', 'Cari status ruangan berdasarkan tanggal, waktu, kapasitas, dan lokasi.')

@push('styles')
    <style>
        .availability-form {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }
        .availability-form .actions {
            grid-column: 1 / -1;
            margin-top: 4px;
        }
        .result-section .panel-header {
            align-items: flex-start;
        }
        .section-count {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .availability-card {
            display: grid;
            gap: 10px;
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
        }
        .availability-card-available {
            background: #f3fbf5;
            border-color: #bfe6cc;
        }
        .availability-card-used {
            background: #fff5f5;
            border-color: #f2c3c3;
        }
        .availability-card-pending {
            background: #fff9e8;
            border-color: #efd58a;
        }
        .availability-card h3 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 0;
        }
        .availability-card-meta {
            display: grid;
            gap: 4px;
            color: var(--muted);
            font-size: 13px;
        }
        .availability-card-body {
            color: #27364a;
            font-size: 14px;
        }

        @media (max-width: 1180px) {
            .availability-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .availability-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Filter Pencarian</h2>
                    <div class="muted">Tanggal wajib diisi saat pencarian. Jam mulai dan selesai dapat dikosongkan untuk melihat semua penggunaan pada hari itu.</div>
                </div>
            </div>
            <div class="panel-body">
                <form class="availability-form" method="GET" action="{{ route('room-search.index') }}">
                    <div class="form-field">
                        <label for="date">Tanggal</label>
                        <input id="date" name="date" type="date" value="{{ old('date', $criteria['date'] ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label for="start_time">Mulai</label>
                        <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $criteria['start_time'] ?? '') }}">
                    </div>
                    <div class="form-field">
                        <label for="end_time">Selesai</label>
                        <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $criteria['end_time'] ?? '') }}">
                    </div>
                    <div class="form-field">
                        <label for="capacity">Kapasitas Minimum</label>
                        <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $criteria['capacity'] ?? '') }}">
                    </div>
                    <div class="form-field">
                        <label for="semester_id">Semester</label>
                        <select id="semester_id" name="semester_id">
                            <option value="">Semua semester</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" @selected(old('semester_id', $criteria['semester_id'] ?? '') === $semester->id)>
                                    {{ $semester->name }}{{ $semester->is_active ? ' (aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="academic_year_id">Tahun Akademik</label>
                        <select id="academic_year_id" name="academic_year_id">
                            <option value="">Semua tahun akademik</option>
                            @foreach ($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $criteria['academic_year_id'] ?? '') === $academicYear->id)>
                                    {{ $academicYear->name }}{{ $academicYear->is_active ? ' (aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="building">Gedung</label>
                        <select id="building" name="building">
                            <option value="">Semua gedung</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building }}" @selected(old('building', $criteria['building'] ?? '') === $building)>{{ $building }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Cari</button>
                        <a class="btn" href="{{ route('room-search.index') }}">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($searchSubmitted && $availability)
            @include('room-availability.partials.status-sections', ['availability' => $availability])
        @else
            <div class="empty-state">Isi filter pencarian untuk melihat Terpakai, Tidak Dipakai, dan Sedang Dalam Pengajuan.</div>
        @endif
    </div>
@endsection
