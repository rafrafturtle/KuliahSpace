@extends('layouts.app')

@section('title', 'Tetapkan Ketua Kelas')
@section('subtitle', 'Mahasiswa akan otomatis menerima role ketua_kelas.')

@section('content')
    <div class="panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('class-leaders.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field">
                        <label for="student_id">Mahasiswa</label>
                        <select id="student_id" name="student_id" required>
                            <option value="">Pilih mahasiswa</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') === $student->id)>{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="lecturer_id">Dosen</label>
                        @if ($canChooseLecturer)
                            <select id="lecturer_id" name="lecturer_id" required>
                                <option value="">Pilih dosen</option>
                                @foreach ($lecturers as $lecturer)
                                    <option value="{{ $lecturer->id }}" @selected(old('lecturer_id') === $lecturer->id)>{{ $lecturer->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="lecturer_id" value="{{ auth()->id() }}">
                            <input id="lecturer_id" type="text" value="{{ auth()->user()->name }}" disabled>
                        @endif
                    </div>
                    <div class="form-field">
                        <label for="course_id">Mata Kuliah</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">Pilih mata kuliah</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id') === $course->id)>{{ $course->code }} - {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="semester_id">Semester</label>
                        <select id="semester_id" name="semester_id" required>
                            <option value="">Pilih semester</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}" @selected(old('semester_id') === $semester->id)>
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
                                <option value="{{ $academicYear->id }}" @selected(old('academic_year_id') === $academicYear->id)>
                                    {{ $academicYear->name }}{{ $academicYear->is_active ? ' (aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Tetapkan Ketua</button>
                    <a class="btn" href="{{ route('class-leaders.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
