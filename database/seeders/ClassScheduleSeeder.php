<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Room;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class ClassScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoomSeeder::class);

        $semester = $this->activeSemester();
        $academicYear = $this->activeAcademicYear();
        $courses = $this->courses();
        $lecturers = $this->lecturers();

        $schedules = [
            ['room_code' => 'R-1', 'course' => 'Pemrograman Web', 'lecturer_index' => 0, 'class_name' => '3A Pendidikan Komputer', 'day_of_week' => 'Monday', 'start_time' => '08:00', 'end_time' => '10:00'],
            ['room_code' => 'R-2', 'course' => 'Basis Data', 'lecturer_index' => 1, 'class_name' => '3B Pendidikan Komputer', 'day_of_week' => 'Monday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ['room_code' => 'R-3', 'course' => 'Jaringan Komputer', 'lecturer_index' => 0, 'class_name' => '5A Pendidikan Komputer', 'day_of_week' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '10:00'],
            ['room_code' => 'R-4', 'course' => 'Sistem Operasi', 'lecturer_index' => 1, 'class_name' => '5B Pendidikan Komputer', 'day_of_week' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '12:00'],
            ['room_code' => 'R-101', 'course' => 'Interaksi Manusia dan Komputer', 'lecturer_index' => 0, 'class_name' => '3A Pendidikan Komputer', 'day_of_week' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '10:00'],
            ['room_code' => 'R-201', 'course' => 'Algoritma dan Struktur Data', 'lecturer_index' => 1, 'class_name' => '1A Pendidikan Komputer', 'day_of_week' => 'Thursday', 'start_time' => '08:00', 'end_time' => '10:00'],
            ['room_code' => 'R-202', 'course' => 'Media Pembelajaran Berbasis Web', 'lecturer_index' => 0, 'class_name' => '1A PGSD', 'day_of_week' => 'Friday', 'start_time' => '08:00', 'end_time' => '10:00'],
        ];

        foreach ($schedules as $schedule) {
            $room = Room::where('code', $schedule['room_code'])->first();

            if (! $room) {
                continue;
            }

            $course = $courses->get($schedule['course']);
            $lecturer = $lecturers[$schedule['lecturer_index'] % $lecturers->count()];

            $identity = [
                'room_id' => $room->id,
                'day_of_week' => $schedule['day_of_week'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'semester_id' => $semester->id,
                'academic_year_id' => $academicYear->id,
            ];

            $existingSchedule = ClassSchedule::where($identity)->first();
            $hasConflict = ClassSchedule::query()
                ->where('room_id', $room->id)
                ->where('day_of_week', $schedule['day_of_week'])
                ->where('semester_id', $semester->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('is_active', true)
                ->when($existingSchedule, fn ($query) => $query->whereKeyNot($existingSchedule->id))
                ->where('start_time', '<', $schedule['end_time'])
                ->where('end_time', '>', $schedule['start_time'])
                ->exists();

            if ($hasConflict) {
                continue;
            }

            ClassSchedule::updateOrCreate($identity, [
                'course_id' => $course->id,
                'lecturer_id' => $lecturer->id,
                'class_name' => $schedule['class_name'],
                'week_number' => null,
                'is_active' => true,
            ]);
        }
    }

    private function activeSemester(): Semester
    {
        return Semester::where('is_active', true)->first()
            ?? Semester::updateOrCreate(['name' => 'Ganjil'], ['is_active' => true]);
    }

    private function activeAcademicYear(): AcademicYear
    {
        return AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::updateOrCreate(['name' => '2026/2027'], ['is_active' => true]);
    }

    /**
     * @return Collection<string, Course>
     */
    private function courses(): Collection
    {
        return collect([
            ['code' => 'KS101', 'name' => 'Pemrograman Web', 'credits' => 3],
            ['code' => 'KS102', 'name' => 'Basis Data', 'credits' => 3],
            ['code' => 'KS103', 'name' => 'Jaringan Komputer', 'credits' => 3],
            ['code' => 'KS104', 'name' => 'Sistem Operasi', 'credits' => 3],
            ['code' => 'KS105', 'name' => 'Interaksi Manusia dan Komputer', 'credits' => 3],
            ['code' => 'KS106', 'name' => 'Algoritma dan Struktur Data', 'credits' => 3],
            ['code' => 'KS107', 'name' => 'Media Pembelajaran Berbasis Web', 'credits' => 3],
        ])->mapWithKeys(function (array $course): array {
            $model = Course::where('name', $course['name'])->first()
                ?? Course::updateOrCreate(['code' => $course['code']], $course);

            return [$course['name'] => $model];
        });
    }

    /**
     * @return Collection<int, User>
     */
    private function lecturers(): Collection
    {
        $lecturers = User::whereHas('roles', fn ($query) => $query->where('name', 'dosen'))
            ->orderBy('name')
            ->get();

        if ($lecturers->isNotEmpty()) {
            return $lecturers;
        }

        $lecturer = User::updateOrCreate(
            ['email' => 'dosen.demo@kuliahspace.test'],
            ['name' => 'Dosen Demo KuliahSpace', 'password' => Hash::make('password')]
        );

        Bouncer::role()->firstOrCreate(['name' => 'dosen'], ['title' => 'Dosen']);
        Bouncer::assign('dosen')->to($lecturer);
        Bouncer::refresh();

        return collect([$lecturer]);
    }
}
