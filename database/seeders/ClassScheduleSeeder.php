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
use RuntimeException;
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
        $schedules = $this->schedules();

        $this->validateScheduleData($schedules);
        $this->deactivateLegacySchedules();
        $this->deactivateManagedSchedules($courses->keys()->all());

        foreach ($schedules as $schedule) {
            $room = Room::where('code', $schedule['room_code'])->firstOrFail();
            $course = $courses->get($schedule['course_code'])
                ?? throw new RuntimeException("Course {$schedule['course_code']} belum tersedia.");
            $lecturer = $lecturers->get($schedule['lecturer_email'])
                ?? throw new RuntimeException("Dosen {$schedule['lecturer_email']} belum tersedia.");

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
                throw new RuntimeException("Bentrok ruangan pada {$schedule['room_code']} {$schedule['day_of_week']} {$schedule['start_time']}-{$schedule['end_time']}.");
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
        return collect($this->courseData())->mapWithKeys(function (array $course): array {
            $model = Course::updateOrCreate(
                ['code' => $course['code']],
                ['name' => $course['name'], 'credits' => $course['credits']]
            );

            return [$course['code'] => $model];
        });
    }

    /**
     * @return Collection<string, User>
     */
    private function lecturers(): Collection
    {
        Bouncer::role()->firstOrCreate(['name' => 'dosen'], ['title' => 'Dosen']);

        $lecturers = collect($this->lecturerData())->mapWithKeys(function (array $lecturer): array {
            $model = User::updateOrCreate(
                ['email' => $lecturer['email']],
                ['name' => $lecturer['name'], 'password' => Hash::make('password')]
            );

            Bouncer::assign('dosen')->to($model);

            return [$lecturer['email'] => $model];
        });

        Bouncer::refresh();

        return $lecturers;
    }

    private function courseData(): array
    {
        return [
            ['code' => 'PBI101', 'name' => 'Reading for General Communication', 'credits' => 3],
            ['code' => 'PBI102', 'name' => 'Basic English Grammar', 'credits' => 3],
            ['code' => 'PBI103', 'name' => 'Speaking for Daily Communication', 'credits' => 3],
            ['code' => 'PBI201', 'name' => 'English Phonology', 'credits' => 3],
            ['code' => 'PBI202', 'name' => 'Academic Writing', 'credits' => 3],
            ['code' => 'PBI203', 'name' => 'English Language Teaching Method', 'credits' => 3],
            ['code' => 'PBI301', 'name' => 'Translation Practice', 'credits' => 3],
            ['code' => 'PBI302', 'name' => 'Curriculum and Material Development', 'credits' => 3],
            ['code' => 'PBSI101', 'name' => 'Fonologi Bahasa Indonesia', 'credits' => 3],
            ['code' => 'PBSI102', 'name' => 'Morfologi Bahasa Indonesia', 'credits' => 3],
            ['code' => 'PBSI103', 'name' => 'Menulis Akademik', 'credits' => 3],
            ['code' => 'PBSI201', 'name' => 'Sintaksis Bahasa Indonesia', 'credits' => 3],
            ['code' => 'PBSI202', 'name' => 'Sastra Indonesia', 'credits' => 3],
            ['code' => 'PBSI203', 'name' => 'Pembelajaran Bahasa Indonesia', 'credits' => 3],
            ['code' => 'PBSI301', 'name' => 'Kritik Sastra', 'credits' => 3],
            ['code' => 'PBSI302', 'name' => 'Pengembangan Bahan Ajar Bahasa Indonesia', 'credits' => 3],
            ['code' => 'PSB101', 'name' => 'Dasar-Dasar Seni Budaya', 'credits' => 3],
            ['code' => 'PSB102', 'name' => 'Seni Tari Dasar', 'credits' => 3],
            ['code' => 'PSB103', 'name' => 'Seni Musik Dasar', 'credits' => 3],
            ['code' => 'PSB201', 'name' => 'Praktik Tari Tradisional', 'credits' => 3],
            ['code' => 'PSB202', 'name' => 'Apresiasi Seni', 'credits' => 3],
            ['code' => 'PSB203', 'name' => 'Manajemen Pertunjukan', 'credits' => 3],
            ['code' => 'PSB301', 'name' => 'Studio Kreatif Seni', 'credits' => 3],
            ['code' => 'PSB302', 'name' => 'Pembelajaran Seni Budaya', 'credits' => 3],
            ['code' => 'PTI101', 'name' => 'Pengantar Teknologi Informasi', 'credits' => 3],
            ['code' => 'PTI102', 'name' => 'Dasar Pemrograman', 'credits' => 3],
            ['code' => 'PTI103', 'name' => 'Algoritma dan Struktur Data', 'credits' => 3],
            ['code' => 'PTI201', 'name' => 'Basis Data', 'credits' => 3],
            ['code' => 'PTI202', 'name' => 'Pemrograman Web', 'credits' => 3],
            ['code' => 'PTI203', 'name' => 'Jaringan Komputer', 'credits' => 3],
            ['code' => 'PTI301', 'name' => 'Rekayasa Perangkat Lunak', 'credits' => 3],
            ['code' => 'PTI302', 'name' => 'Sistem Cerdas', 'credits' => 3],
            ['code' => 'PTI303', 'name' => 'Multimedia Pembelajaran', 'credits' => 3],
            ['code' => 'PTI304', 'name' => 'Open Source', 'credits' => 3],
            ['code' => 'KPD101', 'name' => 'Pengantar Pendidikan', 'credits' => 2],
            ['code' => 'KPD102', 'name' => 'Psikologi Pendidikan', 'credits' => 2],
            ['code' => 'KPD103', 'name' => 'Profesi Kependidikan', 'credits' => 2],
            ['code' => 'KPD201', 'name' => 'Strategi Pembelajaran', 'credits' => 3],
            ['code' => 'KPD202', 'name' => 'Evaluasi Pembelajaran', 'credits' => 3],
            ['code' => 'KPD203', 'name' => 'Kurikulum dan Pembelajaran', 'credits' => 3],
            ['code' => 'KPD301', 'name' => 'Microteaching', 'credits' => 3],
            ['code' => 'KPD302', 'name' => 'Manajemen Pendidikan', 'credits' => 3],
            ['code' => 'PIPA101', 'name' => 'Konsep Dasar IPA', 'credits' => 3],
            ['code' => 'PIPA102', 'name' => 'Biologi Umum', 'credits' => 3],
            ['code' => 'PIPA103', 'name' => 'Fisika Dasar', 'credits' => 3],
            ['code' => 'PIPA104', 'name' => 'Kimia Dasar', 'credits' => 3],
            ['code' => 'PIPA201', 'name' => 'Pembelajaran IPA Terpadu', 'credits' => 3],
            ['code' => 'PIPA202', 'name' => 'Praktikum IPA', 'credits' => 3],
            ['code' => 'PIPA301', 'name' => 'Evaluasi Pembelajaran IPA', 'credits' => 3],
            ['code' => 'PIPA302', 'name' => 'Media Pembelajaran IPA', 'credits' => 3],
        ];
    }

    private function lecturerData(): array
    {
        return [
            ['name' => 'Dr. Amelia Hartono', 'email' => 'dosen.inggris1@kuliahspace.test'],
            ['name' => 'Dr. Yusuf Mahendra', 'email' => 'dosen.inggris2@kuliahspace.test'],
            ['name' => 'Dr. Ratih Purnamasari', 'email' => 'dosen.indonesia1@kuliahspace.test'],
            ['name' => 'Dr. Bima Prasetyo', 'email' => 'dosen.indonesia2@kuliahspace.test'],
            ['name' => 'Dra. Laras Wulandari, M.Pd.', 'email' => 'dosen.seni1@kuliahspace.test'],
            ['name' => 'Dr. Aditya Suryana', 'email' => 'dosen.seni2@kuliahspace.test'],
            ['name' => 'Dr. Rendra Saputra', 'email' => 'dosen.komputer1@kuliahspace.test'],
            ['name' => 'Dr. Meilani Putri', 'email' => 'dosen.komputer2@kuliahspace.test'],
            ['name' => 'Ahmad Fadhil, M.Kom.', 'email' => 'dosen.komputer3@kuliahspace.test'],
            ['name' => 'Dr. Nanda Kusuma', 'email' => 'dosen.kependidikan1@kuliahspace.test'],
            ['name' => 'Dr. Sri Wahyuni', 'email' => 'dosen.kependidikan2@kuliahspace.test'],
            ['name' => 'Dr. Dimas Ardiansyah', 'email' => 'dosen.ipa1@kuliahspace.test'],
            ['name' => 'Dr. Mutiara Lestari', 'email' => 'dosen.ipa2@kuliahspace.test'],
        ];
    }

    private function schedules(): array
    {
        return [
            ['course_code' => 'PBI101', 'lecturer_email' => 'dosen.inggris1@kuliahspace.test', 'class_name' => 'PBI 1A', 'day_of_week' => 'Monday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1007'],
            ['course_code' => 'PBSI101', 'lecturer_email' => 'dosen.indonesia1@kuliahspace.test', 'class_name' => 'PBSI 1A', 'day_of_week' => 'Monday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1008'],
            ['course_code' => 'PTI102', 'lecturer_email' => 'dosen.komputer1@kuliahspace.test', 'class_name' => 'PTI 1A', 'day_of_week' => 'Monday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1002'],
            ['course_code' => 'KPD101', 'lecturer_email' => 'dosen.kependidikan1@kuliahspace.test', 'class_name' => 'KPD 1A', 'day_of_week' => 'Monday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2001'],
            ['course_code' => 'PIPA101', 'lecturer_email' => 'dosen.ipa1@kuliahspace.test', 'class_name' => 'PIPA 1A', 'day_of_week' => 'Monday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2002'],
            ['course_code' => 'PTI202', 'lecturer_email' => 'dosen.komputer2@kuliahspace.test', 'class_name' => 'PTI 3A', 'day_of_week' => 'Monday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1001'],
            ['course_code' => 'PBI102', 'lecturer_email' => 'dosen.inggris2@kuliahspace.test', 'class_name' => 'PBI 1B', 'day_of_week' => 'Monday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1007'],
            ['course_code' => 'PSB101', 'lecturer_email' => 'dosen.seni1@kuliahspace.test', 'class_name' => 'PSB 1A', 'day_of_week' => 'Monday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1004'],
            ['course_code' => 'PBSI102', 'lecturer_email' => 'dosen.indonesia2@kuliahspace.test', 'class_name' => 'PBSI 1B', 'day_of_week' => 'Monday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1008'],
            ['course_code' => 'KPD102', 'lecturer_email' => 'dosen.kependidikan2@kuliahspace.test', 'class_name' => 'KPD 1A', 'day_of_week' => 'Monday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-2001'],
            ['course_code' => 'PIPA102', 'lecturer_email' => 'dosen.ipa2@kuliahspace.test', 'class_name' => 'PIPA 1B', 'day_of_week' => 'Tuesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2002'],
            ['course_code' => 'PTI101', 'lecturer_email' => 'dosen.komputer1@kuliahspace.test', 'class_name' => 'PTI 1B', 'day_of_week' => 'Tuesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2010'],
            ['course_code' => 'PBI103', 'lecturer_email' => 'dosen.inggris1@kuliahspace.test', 'class_name' => 'PBI 1A', 'day_of_week' => 'Tuesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1003'],
            ['course_code' => 'PBSI103', 'lecturer_email' => 'dosen.indonesia1@kuliahspace.test', 'class_name' => 'PBSI 1A', 'day_of_week' => 'Tuesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1005'],
            ['course_code' => 'PSB102', 'lecturer_email' => 'dosen.seni2@kuliahspace.test', 'class_name' => 'PSB 1A', 'day_of_week' => 'Tuesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1010'],
            ['course_code' => 'PTI103', 'lecturer_email' => 'dosen.komputer2@kuliahspace.test', 'class_name' => 'PTI 1A', 'day_of_week' => 'Tuesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2011'],
            ['course_code' => 'PBI201', 'lecturer_email' => 'dosen.inggris2@kuliahspace.test', 'class_name' => 'PBI 3A', 'day_of_week' => 'Tuesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1007'],
            ['course_code' => 'KPD103', 'lecturer_email' => 'dosen.kependidikan1@kuliahspace.test', 'class_name' => 'KPD 1A', 'day_of_week' => 'Tuesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2003'],
            ['course_code' => 'PIPA103', 'lecturer_email' => 'dosen.ipa1@kuliahspace.test', 'class_name' => 'PIPA 1A', 'day_of_week' => 'Tuesday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-2004'],
            ['course_code' => 'PBSI201', 'lecturer_email' => 'dosen.indonesia2@kuliahspace.test', 'class_name' => 'PBSI 3A', 'day_of_week' => 'Wednesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1008'],
            ['course_code' => 'PTI201', 'lecturer_email' => 'dosen.komputer3@kuliahspace.test', 'class_name' => 'PTI 3A', 'day_of_week' => 'Wednesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1002'],
            ['course_code' => 'PBI202', 'lecturer_email' => 'dosen.inggris1@kuliahspace.test', 'class_name' => 'PBI 3A', 'day_of_week' => 'Wednesday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1007'],
            ['course_code' => 'KPD201', 'lecturer_email' => 'dosen.kependidikan2@kuliahspace.test', 'class_name' => 'KPD 3A', 'day_of_week' => 'Wednesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2001'],
            ['course_code' => 'PSB103', 'lecturer_email' => 'dosen.seni1@kuliahspace.test', 'class_name' => 'PSB 1B', 'day_of_week' => 'Wednesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1009'],
            ['course_code' => 'PIPA104', 'lecturer_email' => 'dosen.ipa2@kuliahspace.test', 'class_name' => 'PIPA 1B', 'day_of_week' => 'Wednesday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2005'],
            ['course_code' => 'PTI203', 'lecturer_email' => 'dosen.komputer1@kuliahspace.test', 'class_name' => 'PTI 3A', 'day_of_week' => 'Wednesday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-1001'],
            ['course_code' => 'PBSI202', 'lecturer_email' => 'dosen.indonesia1@kuliahspace.test', 'class_name' => 'PBSI 3A', 'day_of_week' => 'Wednesday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-1008'],
            ['course_code' => 'PBI203', 'lecturer_email' => 'dosen.inggris2@kuliahspace.test', 'class_name' => 'PBI 3B', 'day_of_week' => 'Wednesday', 'start_time' => '13:30', 'end_time' => '15:10', 'room_code' => 'R-1007'],
            ['course_code' => 'PIPA201', 'lecturer_email' => 'dosen.ipa1@kuliahspace.test', 'class_name' => 'PIPA 3A', 'day_of_week' => 'Thursday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2007'],
            ['course_code' => 'PTI301', 'lecturer_email' => 'dosen.komputer2@kuliahspace.test', 'class_name' => 'PTI 5A', 'day_of_week' => 'Thursday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2010'],
            ['course_code' => 'PBSI203', 'lecturer_email' => 'dosen.indonesia2@kuliahspace.test', 'class_name' => 'PBSI 3B', 'day_of_week' => 'Thursday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1008'],
            ['course_code' => 'PSB201', 'lecturer_email' => 'dosen.seni2@kuliahspace.test', 'class_name' => 'PSB 3A', 'day_of_week' => 'Thursday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1010'],
            ['course_code' => 'KPD202', 'lecturer_email' => 'dosen.kependidikan1@kuliahspace.test', 'class_name' => 'KPD 3A', 'day_of_week' => 'Thursday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2001'],
            ['course_code' => 'PBI301', 'lecturer_email' => 'dosen.inggris1@kuliahspace.test', 'class_name' => 'PBI 5A', 'day_of_week' => 'Thursday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1003'],
            ['course_code' => 'PTI302', 'lecturer_email' => 'dosen.komputer3@kuliahspace.test', 'class_name' => 'PTI 5A', 'day_of_week' => 'Thursday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-2011'],
            ['course_code' => 'PIPA202', 'lecturer_email' => 'dosen.ipa2@kuliahspace.test', 'class_name' => 'PIPA 3A', 'day_of_week' => 'Thursday', 'start_time' => '13:30', 'end_time' => '15:10', 'room_code' => 'R-2007'],
            ['course_code' => 'PSB202', 'lecturer_email' => 'dosen.seni1@kuliahspace.test', 'class_name' => 'PSB 3A', 'day_of_week' => 'Thursday', 'start_time' => '13:30', 'end_time' => '15:10', 'room_code' => 'R-1004'],
            ['course_code' => 'KPD203', 'lecturer_email' => 'dosen.kependidikan2@kuliahspace.test', 'class_name' => 'KPD 3A', 'day_of_week' => 'Friday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-2001'],
            ['course_code' => 'PBSI301', 'lecturer_email' => 'dosen.indonesia1@kuliahspace.test', 'class_name' => 'PBSI 5A', 'day_of_week' => 'Friday', 'start_time' => '07:30', 'end_time' => '09:10', 'room_code' => 'R-1005'],
            ['course_code' => 'PTI303', 'lecturer_email' => 'dosen.komputer1@kuliahspace.test', 'class_name' => 'PTI 5A', 'day_of_week' => 'Friday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2012'],
            ['course_code' => 'PSB203', 'lecturer_email' => 'dosen.seni2@kuliahspace.test', 'class_name' => 'PSB 3B', 'day_of_week' => 'Friday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1009'],
            ['course_code' => 'PBI302', 'lecturer_email' => 'dosen.inggris2@kuliahspace.test', 'class_name' => 'PBI 5A', 'day_of_week' => 'Friday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1007'],
            ['course_code' => 'PIPA301', 'lecturer_email' => 'dosen.ipa1@kuliahspace.test', 'class_name' => 'PIPA 3A', 'day_of_week' => 'Friday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-2008'],
            ['course_code' => 'PTI304', 'lecturer_email' => 'dosen.komputer2@kuliahspace.test', 'class_name' => 'PTI 5B', 'day_of_week' => 'Friday', 'start_time' => '13:30', 'end_time' => '15:10', 'room_code' => 'R-2013'],
            ['course_code' => 'PBSI302', 'lecturer_email' => 'dosen.indonesia2@kuliahspace.test', 'class_name' => 'PBSI 5A', 'day_of_week' => 'Friday', 'start_time' => '13:30', 'end_time' => '15:10', 'room_code' => 'R-1008'],
            ['course_code' => 'KPD301', 'lecturer_email' => 'dosen.kependidikan1@kuliahspace.test', 'class_name' => 'KPD 5A', 'day_of_week' => 'Saturday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-9001'],
            ['course_code' => 'PSB301', 'lecturer_email' => 'dosen.seni1@kuliahspace.test', 'class_name' => 'PSB 5A', 'day_of_week' => 'Saturday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-1009'],
            ['course_code' => 'PIPA302', 'lecturer_email' => 'dosen.ipa2@kuliahspace.test', 'class_name' => 'PIPA 5A', 'day_of_week' => 'Saturday', 'start_time' => '09:20', 'end_time' => '11:00', 'room_code' => 'R-2009'],
            ['course_code' => 'KPD302', 'lecturer_email' => 'dosen.kependidikan2@kuliahspace.test', 'class_name' => 'KPD 5A', 'day_of_week' => 'Saturday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-2001'],
            ['course_code' => 'PSB302', 'lecturer_email' => 'dosen.seni2@kuliahspace.test', 'class_name' => 'PSB 3A', 'day_of_week' => 'Saturday', 'start_time' => '11:10', 'end_time' => '12:50', 'room_code' => 'R-1012'],
        ];
    }

    private function validateScheduleData(array $schedules): void
    {
        $checks = [
            'ruangan' => fn (array $schedule): string => $schedule['room_code'],
            'dosen' => fn (array $schedule): string => $schedule['lecturer_email'],
            'kelas' => fn (array $schedule): string => $schedule['class_name'],
        ];

        foreach ($schedules as $index => $schedule) {
            if ($this->minutes($schedule['end_time']) <= $this->minutes($schedule['start_time'])) {
                throw new RuntimeException("Jam selesai harus setelah jam mulai pada jadwal index {$index}.");
            }

            if ($this->minutes($schedule['end_time']) > $this->minutes('18:00')) {
                throw new RuntimeException("Jadwal {$schedule['course_code']} melewati jam 18:00.");
            }
        }

        foreach ($checks as $label => $keyResolver) {
            foreach ($schedules as $leftIndex => $left) {
                foreach (array_slice($schedules, $leftIndex + 1) as $right) {
                    if ($left['day_of_week'] !== $right['day_of_week']) {
                        continue;
                    }

                    if ($keyResolver($left) !== $keyResolver($right)) {
                        continue;
                    }

                    if (! $this->overlaps($left, $right)) {
                        continue;
                    }

                    throw new RuntimeException("Bentrok {$label}: {$keyResolver($left)} {$left['day_of_week']} {$left['start_time']}-{$left['end_time']}.");
                }
            }
        }
    }

    private function overlaps(array $left, array $right): bool
    {
        return $this->minutes($left['start_time']) < $this->minutes($right['end_time'])
            && $this->minutes($left['end_time']) > $this->minutes($right['start_time']);
    }

    private function minutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }

    private function deactivateManagedSchedules(array $courseCodes): void
    {
        ClassSchedule::whereHas('course', fn ($query) => $query->whereIn('code', $courseCodes))
            ->update(['is_active' => false]);
    }

    private function deactivateLegacySchedules(): void
    {
        $legacyRoomIds = Room::whereIn('code', $this->legacyRoomCodes())->pluck('id');

        if ($legacyRoomIds->isNotEmpty()) {
            ClassSchedule::whereIn('room_id', $legacyRoomIds)
                ->update(['is_active' => false]);
        }

        ClassSchedule::whereHas('course', fn ($query) => $query->whereIn('code', $this->legacyCourseCodes()))
            ->update(['is_active' => false]);

        ClassSchedule::whereIn('class_name', $this->legacyClassNames())
            ->update(['is_active' => false]);
    }

    private function legacyRoomCodes(): array
    {
        return ['R-1', 'R-2', 'R-3', 'R-4', 'R-101', 'R-201', 'R-202', 'LAB-201', 'AUD-301'];
    }

    private function legacyCourseCodes(): array
    {
        return ['KS101', 'KS102', 'KS103', 'KS104', 'KS105', 'KS106', 'KS107', 'KS108', 'KS109', 'KS110'];
    }

    private function legacyClassNames(): array
    {
        return [
            '3A Pendidikan Komputer',
            '3B Pendidikan Komputer',
            '5A Pendidikan Komputer',
            '5B Pendidikan Komputer',
            '1A Pendidikan Komputer',
            '1A PGSD',
            'Umum FKIP',
            '2A Pendidikan Komputer',
            '2A PGSD',
        ];
    }
}
