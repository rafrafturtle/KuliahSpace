@csrf
<div class="form-grid">
    <div class="form-field">
        <label for="course_id">Mata Kuliah</label>
        <select id="course_id" name="course_id" required>
            <option value="">Pilih mata kuliah</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $schedule->course_id) === $course->id)>
                    {{ $course->code }} - {{ $course->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="class_name">Nama Kelas</label>
        <input id="class_name" name="class_name" type="text" value="{{ old('class_name', $schedule->class_name) }}" placeholder="A / IF-4A" required>
    </div>
    <div class="form-field">
        <label for="lecturer_id">Dosen</label>
        <select id="lecturer_id" name="lecturer_id" required>
            <option value="">Pilih dosen</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $schedule->lecturer_id) === $lecturer->id)>
                    {{ $lecturer->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="room_id">Ruangan</label>
        <select id="room_id" name="room_id" required>
            <option value="">Pilih ruangan</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected(old('room_id', $schedule->room_id) === $room->id)>
                    {{ $room->code }} - {{ $room->name }} ({{ $room->capacity }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="day_of_week">Hari</label>
        <select id="day_of_week" name="day_of_week" required>
            <option value="">Pilih hari</option>
            @foreach ($days as $day)
                <option value="{{ $day }}" @selected(old('day_of_week', $schedule->day_of_week) === $day)>{{ $day }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field">
        <label for="week_number">Minggu Ke</label>
        <input id="week_number" name="week_number" type="number" min="1" value="{{ old('week_number', $schedule->week_number) }}">
    </div>
    <div class="form-field">
        <label for="start_time">Mulai</label>
        <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $schedule->start_time ? substr($schedule->start_time, 0, 5) : '') }}" required>
    </div>
    <div class="form-field">
        <label for="end_time">Selesai</label>
        <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $schedule->end_time ? substr($schedule->end_time, 0, 5) : '') }}" required>
    </div>
    <div class="form-field">
        <label for="semester_id">Semester</label>
        <select id="semester_id" name="semester_id" required>
            <option value="">Pilih semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" @selected(old('semester_id', $schedule->semester_id) === $semester->id)>
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
                <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $schedule->academic_year_id) === $academicYear->id)>
                    {{ $academicYear->name }}{{ $academicYear->is_active ? ' (aktif)' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-field full">
        <label>Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $schedule->is_active ?? true))>
            Aktif
        </label>
    </div>
</div>
<div class="actions" style="margin-top: 18px;">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('schedules.index') }}">Batal</a>
</div>
