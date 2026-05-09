<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\CourseClassLeader;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedRolesAndAbilities();

            $admin = User::create([
                'name' => 'Admin Akademik',
                'email' => 'admin@ulm.ac.id',
                'password' => Hash::make('password'),
            ]);

            $dosenA = User::create([
                'name' => 'Dr. Arif Wibowo',
                'email' => 'dosen@ulm.ac.id',
                'password' => Hash::make('password'),
            ]);

            $dosenB = User::create([
                'name' => 'Dr. Nabila Putri',
                'email' => 'nabila@kuliahspace.test',
                'password' => Hash::make('password'),
            ]);

            $students = collect([
                ['name' => 'Raka Pratama', 'email' => 'mahasiswa@ulm.ac.id'],
                ['name' => 'Sinta Lestari', 'email' => 'ketuakelas@ulm.ac.id'],
                ['name' => 'Bagus Nugroho', 'email' => 'bagus@kuliahspace.test'],
                ['name' => 'Maya Kirana', 'email' => 'maya@kuliahspace.test'],
                ['name' => 'Dewi Safitri', 'email' => 'dewi@kuliahspace.test'],
            ])->map(fn (array $student) => User::create($student + ['password' => Hash::make('password')]));

            Bouncer::assign('admin')->to($admin);
            Bouncer::assign('dosen')->to([$dosenA, $dosenB]);
            Bouncer::assign('mahasiswa')->to($students->all());

            $rooms = collect([
                ['code' => 'R-101', 'name' => 'Ruang Kuliah 101', 'building' => 'Gedung A', 'floor' => '1', 'capacity' => 40, 'facilities' => 'LCD, papan tulis, AC', 'is_active' => true],
                ['code' => 'R-102', 'name' => 'Ruang Kuliah 102', 'building' => 'Gedung A', 'floor' => '1', 'capacity' => 35, 'facilities' => 'LCD, papan tulis', 'is_active' => true],
                ['code' => 'LAB-201', 'name' => 'Laboratorium Komputer', 'building' => 'Gedung B', 'floor' => '2', 'capacity' => 30, 'facilities' => 'Komputer, proyektor, AC', 'is_active' => true],
                ['code' => 'AUD-301', 'name' => 'Aula Akademik', 'building' => 'Gedung C', 'floor' => '3', 'capacity' => 120, 'facilities' => 'Sound system, proyektor, podium', 'is_active' => true],
                ['code' => 'R-202', 'name' => 'Ruang Diskusi 202', 'building' => 'Gedung B', 'floor' => '2', 'capacity' => 24, 'facilities' => 'Smart TV, papan tulis', 'is_active' => true],
            ])->map(fn (array $room) => Room::create($room));

            $courses = collect([
                ['code' => 'IF101', 'name' => 'Algoritma dan Pemrograman', 'credits' => 3],
                ['code' => 'IF203', 'name' => 'Basis Data', 'credits' => 3],
                ['code' => 'IF305', 'name' => 'Rekayasa Perangkat Lunak', 'credits' => 3],
                ['code' => 'SI210', 'name' => 'Sistem Informasi Manajemen', 'credits' => 2],
                ['code' => 'MKU110', 'name' => 'Bahasa Indonesia Akademik', 'credits' => 2],
            ])->map(fn (array $course) => Course::create($course));

            $semesterGanjil = Semester::create(['name' => 'Ganjil', 'is_active' => true]);
            $semesterGenap = Semester::create(['name' => 'Genap', 'is_active' => false]);
            $academicYear = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);

            ClassSchedule::create([
                'course_id' => $courses[0]->id,
                'lecturer_id' => $dosenA->id,
                'room_id' => $rooms[0]->id,
                'class_name' => 'IF-1A',
                'day_of_week' => 'Monday',
                'start_time' => '08:00',
                'end_time' => '09:40',
                'week_number' => null,
                'semester_id' => $semesterGanjil->id,
                'academic_year_id' => $academicYear->id,
                'is_active' => true,
            ]);

            ClassSchedule::create([
                'course_id' => $courses[1]->id,
                'lecturer_id' => $dosenB->id,
                'room_id' => $rooms[2]->id,
                'class_name' => 'IF-2B',
                'day_of_week' => 'Tuesday',
                'start_time' => '10:00',
                'end_time' => '11:40',
                'week_number' => null,
                'semester_id' => $semesterGanjil->id,
                'academic_year_id' => $academicYear->id,
                'is_active' => true,
            ]);

            ClassSchedule::create([
                'course_id' => $courses[2]->id,
                'lecturer_id' => $dosenA->id,
                'room_id' => $rooms[1]->id,
                'class_name' => 'IF-3A',
                'day_of_week' => 'Wednesday',
                'start_time' => '13:00',
                'end_time' => '14:40',
                'week_number' => null,
                'semester_id' => $semesterGanjil->id,
                'academic_year_id' => $academicYear->id,
                'is_active' => true,
            ]);

            ClassSchedule::create([
                'course_id' => $courses[3]->id,
                'lecturer_id' => $dosenB->id,
                'room_id' => $rooms[3]->id,
                'class_name' => 'SI-2A',
                'day_of_week' => 'Thursday',
                'start_time' => '08:00',
                'end_time' => '09:40',
                'week_number' => null,
                'semester_id' => $semesterGanjil->id,
                'academic_year_id' => $academicYear->id,
                'is_active' => true,
            ]);

            $tomorrow = Carbon::now()->addDay();
            $nextWeek = Carbon::now()->addWeek();

            RoomRequest::create([
                'requester_id' => $students[0]->id,
                'room_id' => $rooms[4]->id,
                'request_date' => $tomorrow->toDateString(),
                'day_of_week' => $tomorrow->format('l'),
                'start_time' => '09:00',
                'end_time' => '10:30',
                'purpose' => 'Diskusi kelompok proyek basis data.',
                'status' => RoomRequest::STATUS_PENDING,
            ]);

            RoomRequest::create([
                'requester_id' => $dosenA->id,
                'room_id' => $rooms[3]->id,
                'request_date' => $nextWeek->toDateString(),
                'day_of_week' => $nextWeek->format('l'),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'purpose' => 'Kuliah tamu program studi.',
                'status' => RoomRequest::STATUS_APPROVED,
                'admin_note' => 'Disetujui untuk kegiatan akademik.',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            RoomRequest::create([
                'requester_id' => $students[2]->id,
                'room_id' => $rooms[1]->id,
                'request_date' => Carbon::now()->addDays(3)->toDateString(),
                'day_of_week' => Carbon::now()->addDays(3)->format('l'),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'purpose' => 'Rapat persiapan praktikum.',
                'status' => RoomRequest::STATUS_REJECTED,
                'admin_note' => 'Ruangan diprioritaskan untuk kuliah pengganti.',
            ]);

            CourseClassLeader::create([
                'student_id' => $students[1]->id,
                'lecturer_id' => $dosenA->id,
                'course_id' => $courses[0]->id,
                'semester_id' => $semesterGanjil->id,
                'academic_year_id' => $academicYear->id,
                'assigned_at' => now(),
            ]);

            Bouncer::assign('ketua_kelas')->to($students[1]);
            Bouncer::refresh();
        });
    }

    private function seedRolesAndAbilities(): void
    {
        $roles = [
            'admin' => 'Admin',
            'dosen' => 'Dosen',
            'ketua_kelas' => 'Ketua Kelas Mata Kuliah',
            'mahasiswa' => 'Mahasiswa',
        ];

        foreach ($roles as $name => $title) {
            Bouncer::role()->firstOrCreate(['name' => $name], ['title' => $title]);
        }

        $roleAbilities = [
            'admin' => ['manage-users', 'assign-roles', 'manage-rooms', 'manage-courses', 'manage-semesters', 'manage-academic-years', 'manage-schedules', 'approve-room-requests', 'reject-room-requests', 'view-all-data'],
            'dosen' => ['view-schedules', 'check-room-availability', 'submit-room-requests', 'assign-class-leaders'],
            'ketua_kelas' => ['view-schedules', 'check-room-availability', 'submit-room-requests'],
            'mahasiswa' => ['view-schedules', 'check-room-availability'],
        ];

        Bouncer::allow('admin')->everything();

        foreach ($roleAbilities as $role => $abilities) {
            foreach ($abilities as $ability) {
                Bouncer::ability()->firstOrCreate(['name' => $ability], ['title' => str($ability)->replace('-', ' ')->headline()]);
                Bouncer::allow($role)->to($ability);
            }
        }
    }
}
