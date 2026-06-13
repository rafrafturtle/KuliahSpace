<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\ClassScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassScheduleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_schedule_seeder_creates_realistic_active_schedules_without_conflicts(): void
    {
        $this->seed(ClassScheduleSeeder::class);

        $schedules = ClassSchedule::with(['course', 'lecturer', 'room'])
            ->where('is_active', true)
            ->get();

        $this->assertCount(50, $schedules);
        $this->assertSame(50, Course::whereIn('code', $this->managedCourseCodes())->count());
        $this->assertSame(13, User::whereIn('email', $this->managedLecturerEmails())->count());

        $expectedCounts = [
            'PBI' => 8,
            'PBSI' => 8,
            'PSB' => 8,
            'PTI' => 10,
            'KPD' => 8,
            'PIPA' => 8,
        ];

        foreach ($expectedCounts as $prefix => $count) {
            $this->assertSame(
                $count,
                $schedules->filter(fn (ClassSchedule $schedule) => str_starts_with($schedule->course->code, $prefix))->count(),
                "Unexpected schedule count for {$prefix}."
            );
        }

        $this->assertNoOverlap($schedules, 'room_id');
        $this->assertNoOverlap($schedules, 'lecturer_id');
        $this->assertNoOverlap($schedules, 'class_name');

        $labRooms = ['R-1001', 'R-1002', 'R-2010', 'R-2011', 'R-2012', 'R-2013'];

        $schedules
            ->filter(fn (ClassSchedule $schedule) => str_starts_with($schedule->course->code, 'PTI'))
            ->each(fn (ClassSchedule $schedule) => $this->assertContains($schedule->room->code, $labRooms));

        $this->assertSame('R-1010', $schedules->firstWhere('course.code', 'PSB201')->room->code);
        $this->assertSame('R-1009', $schedules->firstWhere('course.code', 'PSB301')->room->code);
        $this->assertSame('R-9001', $schedules->firstWhere('course.code', 'KPD301')->room->code);

        $schedules->each(function (ClassSchedule $schedule): void {
            $this->assertLessThanOrEqual($this->minutes('18:00'), $this->minutes(substr($schedule->end_time, 0, 5)));
            $this->assertGreaterThan($this->minutes(substr($schedule->start_time, 0, 5)), $this->minutes(substr($schedule->end_time, 0, 5)));
            $this->assertNotContains($schedule->room->code, ['R-1', 'R-2', 'R-3', 'R-4', 'R-101', 'R-201', 'R-202']);
        });
    }

    private function assertNoOverlap($schedules, string $attribute): void
    {
        $items = $schedules->values();

        for ($left = 0; $left < $items->count(); $left++) {
            for ($right = $left + 1; $right < $items->count(); $right++) {
                $leftSchedule = $items[$left];
                $rightSchedule = $items[$right];

                if ($leftSchedule->day_of_week !== $rightSchedule->day_of_week) {
                    continue;
                }

                if ($leftSchedule->{$attribute} !== $rightSchedule->{$attribute}) {
                    continue;
                }

                $this->assertFalse(
                    $this->overlaps($leftSchedule, $rightSchedule),
                    "{$attribute} conflict on {$leftSchedule->day_of_week} {$leftSchedule->start_time}-{$leftSchedule->end_time}."
                );
            }
        }
    }

    private function overlaps(ClassSchedule $left, ClassSchedule $right): bool
    {
        return $this->minutes(substr($left->start_time, 0, 5)) < $this->minutes(substr($right->end_time, 0, 5))
            && $this->minutes(substr($left->end_time, 0, 5)) > $this->minutes(substr($right->start_time, 0, 5));
    }

    private function minutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }

    private function managedCourseCodes(): array
    {
        return [
            'PBI101', 'PBI102', 'PBI103', 'PBI201', 'PBI202', 'PBI203', 'PBI301', 'PBI302',
            'PBSI101', 'PBSI102', 'PBSI103', 'PBSI201', 'PBSI202', 'PBSI203', 'PBSI301', 'PBSI302',
            'PSB101', 'PSB102', 'PSB103', 'PSB201', 'PSB202', 'PSB203', 'PSB301', 'PSB302',
            'PTI101', 'PTI102', 'PTI103', 'PTI201', 'PTI202', 'PTI203', 'PTI301', 'PTI302', 'PTI303', 'PTI304',
            'KPD101', 'KPD102', 'KPD103', 'KPD201', 'KPD202', 'KPD203', 'KPD301', 'KPD302',
            'PIPA101', 'PIPA102', 'PIPA103', 'PIPA104', 'PIPA201', 'PIPA202', 'PIPA301', 'PIPA302',
        ];
    }

    private function managedLecturerEmails(): array
    {
        return [
            'dosen.inggris1@kuliahspace.test',
            'dosen.inggris2@kuliahspace.test',
            'dosen.indonesia1@kuliahspace.test',
            'dosen.indonesia2@kuliahspace.test',
            'dosen.seni1@kuliahspace.test',
            'dosen.seni2@kuliahspace.test',
            'dosen.komputer1@kuliahspace.test',
            'dosen.komputer2@kuliahspace.test',
            'dosen.komputer3@kuliahspace.test',
            'dosen.kependidikan1@kuliahspace.test',
            'dosen.kependidikan2@kuliahspace.test',
            'dosen.ipa1@kuliahspace.test',
            'dosen.ipa2@kuliahspace.test',
        ];
    }
}
