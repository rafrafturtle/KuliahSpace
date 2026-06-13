<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseClassLeader;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedRolesAndAbilities();

            $admin = User::updateOrCreate(
                ['email' => 'admin@ulm.ac.id'],
                ['name' => 'Admin Akademik', 'password' => Hash::make('password')]
            );

            $dosenA = User::updateOrCreate(
                ['email' => 'dosen@ulm.ac.id'],
                ['name' => 'Dr. Arif Wibowo', 'password' => Hash::make('password')]
            );

            $dosenB = User::updateOrCreate(
                ['email' => 'nabila@kuliahspace.test'],
                ['name' => 'Dr. Nabila Putri', 'password' => Hash::make('password')]
            );

            $students = collect([
                ['name' => 'Raka Pratama', 'email' => 'mahasiswa@ulm.ac.id'],
                ['name' => 'Sinta Lestari', 'email' => 'ketuakelas@ulm.ac.id'],
                ['name' => 'Bagus Nugroho', 'email' => 'bagus@kuliahspace.test'],
                ['name' => 'Maya Kirana', 'email' => 'maya@kuliahspace.test'],
                ['name' => 'Dewi Safitri', 'email' => 'dewi@kuliahspace.test'],
            ])->map(fn (array $student) => User::updateOrCreate(
                ['email' => $student['email']],
                ['name' => $student['name'], 'password' => Hash::make('password')]
            ));

            Bouncer::assign('admin')->to($admin);
            Bouncer::assign('dosen')->to([$dosenA, $dosenB]);
            Bouncer::assign('mahasiswa')->to($students->all());

            $courses = collect([
                ['code' => 'IF101', 'name' => 'Algoritma dan Pemrograman', 'credits' => 3],
                ['code' => 'IF203', 'name' => 'Basis Data', 'credits' => 3],
                ['code' => 'IF305', 'name' => 'Rekayasa Perangkat Lunak', 'credits' => 3],
                ['code' => 'SI210', 'name' => 'Sistem Informasi Manajemen', 'credits' => 2],
                ['code' => 'MKU110', 'name' => 'Bahasa Indonesia Akademik', 'credits' => 2],
            ])->map(fn (array $course) => Course::updateOrCreate(
                ['code' => $course['code']],
                $course
            ));

            $semesterGanjil = Semester::updateOrCreate(['name' => 'Ganjil'], ['is_active' => true]);
            Semester::updateOrCreate(['name' => 'Genap'], ['is_active' => false]);
            $academicYear = AcademicYear::updateOrCreate(['name' => '2026/2027'], ['is_active' => true]);

            $this->call([
                RoomSeeder::class,
                ClassScheduleSeeder::class,
            ]);

            $tomorrow = Carbon::now()->addDay();
            $nextWeek = Carbon::now()->addWeek();
            $threeDaysLater = Carbon::now()->addDays(3);

            RoomRequest::updateOrCreate(
                [
                    'requester_id' => $students[0]->id,
                    'room_id' => Room::where('code', 'R-2002')->firstOrFail()->id,
                    'request_date' => $tomorrow->toDateString(),
                    'start_time' => '09:00',
                    'end_time' => '10:30',
                ],
                [
                    'day_of_week' => $tomorrow->format('l'),
                    'purpose' => 'Diskusi kelompok proyek basis data.',
                    'status' => RoomRequest::STATUS_PENDING,
                    'admin_note' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                ]
            );

            RoomRequest::updateOrCreate(
                [
                    'requester_id' => $dosenA->id,
                    'room_id' => Room::where('code', 'R-9001')->firstOrFail()->id,
                    'request_date' => $nextWeek->toDateString(),
                    'start_time' => '13:00',
                    'end_time' => '15:00',
                ],
                [
                    'day_of_week' => $nextWeek->format('l'),
                    'purpose' => 'Kuliah tamu program studi.',
                    'status' => RoomRequest::STATUS_APPROVED,
                    'admin_note' => 'Disetujui untuk kegiatan akademik.',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]
            );

            RoomRequest::updateOrCreate(
                [
                    'requester_id' => $students[2]->id,
                    'room_id' => Room::where('code', 'R-1007')->firstOrFail()->id,
                    'request_date' => $threeDaysLater->toDateString(),
                    'start_time' => '10:00',
                    'end_time' => '11:00',
                ],
                [
                    'day_of_week' => $threeDaysLater->format('l'),
                    'purpose' => 'Rapat persiapan praktikum.',
                    'status' => RoomRequest::STATUS_REJECTED,
                    'admin_note' => 'Ruangan diprioritaskan untuk kuliah pengganti.',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]
            );

            CourseClassLeader::updateOrCreate(
                [
                    'course_id' => $courses[0]->id,
                    'lecturer_id' => $dosenA->id,
                    'semester_id' => $semesterGanjil->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'student_id' => $students[1]->id,
                    'assigned_at' => now(),
                ]
            );

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
