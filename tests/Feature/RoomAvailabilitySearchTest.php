<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\RoomRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilitySearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->actingAs(User::where('email', 'admin@ulm.ac.id')->firstOrFail());
    }

    public function test_availability_page_defaults_to_clean_calendar_tab(): void
    {
        $this->get(route('room-availability.index'))
            ->assertOk()
            ->assertSee('Ketersediaan Ruang')
            ->assertSee('Sebelumnya')
            ->assertSee('Berikutnya')
            ->assertSee('Klik tanggal untuk melihat detail ketersediaan.')
            ->assertDontSee('Filter Pencarian')
            ->assertDontSee('Pencarian</a>', false)
            ->assertDontSee('Jadwal tetap dan pengajuan ruang yang sudah disetujui.')
            ->assertDontSee('Jadwal Kuliah');
    }

    public function test_room_search_page_shows_search_form_before_results(): void
    {
        $this->get(route('room-search.index'))
            ->assertOk()
            ->assertSee('Pencarian Ruangan')
            ->assertSee('Filter Pencarian')
            ->assertSee('Isi filter pencarian')
            ->assertDontSee('Klik tanggal untuk melihat detail ketersediaan.');
    }

    public function test_calendar_date_opens_separate_detail_page(): void
    {
        $request = RoomRequest::where('status', RoomRequest::STATUS_APPROVED)->firstOrFail();

        $this->get(route('room-availability.date', [
            'date' => $request->request_date->toDateString(),
            'month' => $request->request_date->month,
            'year' => $request->request_date->year,
        ]))
            ->assertOk()
            ->assertSee('Detail Ketersediaan Ruang')
            ->assertSee('Kembali ke Kalender')
            ->assertSee('Total Ruangan')
            ->assertSee('Terpakai')
            ->assertSee('Tidak Dipakai')
            ->assertSee('Sedang Dalam Pengajuan')
            ->assertSee($request->purpose);
    }

    public function test_class_schedule_usage_appears_as_used_room(): void
    {
        $schedule = ClassSchedule::with(['course'])->where('day_of_week', 'Monday')->firstOrFail();
        $date = Carbon::now()->next($schedule->day_of_week)->toDateString();

        $this->get(route('room-search.index', [
            'date' => $date,
            'start_time' => substr($schedule->start_time, 0, 5),
            'end_time' => substr($schedule->end_time, 0, 5),
            'semester_id' => $schedule->semester_id,
            'academic_year_id' => $schedule->academic_year_id,
        ]))
            ->assertOk()
            ->assertSee('Terpakai')
            ->assertSee('Jadwal Tetap')
            ->assertSee($schedule->course->name)
            ->assertSee($schedule->room?->code);
    }

    public function test_approved_and_pending_requests_are_classified_separately(): void
    {
        $approved = RoomRequest::where('status', RoomRequest::STATUS_APPROVED)->firstOrFail();
        $pending = RoomRequest::where('status', RoomRequest::STATUS_PENDING)->firstOrFail();

        $this->get(route('room-search.index', [
            'date' => $approved->request_date->toDateString(),
            'start_time' => substr($approved->start_time, 0, 5),
            'end_time' => substr($approved->end_time, 0, 5),
        ]))
            ->assertOk()
            ->assertSee('Filter Pencarian')
            ->assertSee('Pengajuan Disetujui')
            ->assertSee($approved->purpose)
            ->assertSee('Terpakai');

        $this->get(route('room-search.index', [
            'date' => $pending->request_date->toDateString(),
            'start_time' => substr($pending->start_time, 0, 5),
            'end_time' => substr($pending->end_time, 0, 5),
        ]))
            ->assertOk()
            ->assertSee('Sedang Dalam Pengajuan')
            ->assertSee($pending->purpose)
            ->assertSee($pending->requester?->name);
    }

    public function test_rejected_requests_do_not_block_availability(): void
    {
        $rejected = RoomRequest::where('status', RoomRequest::STATUS_REJECTED)->firstOrFail();

        $this->get(route('room-search.index', [
            'date' => $rejected->request_date->toDateString(),
            'start_time' => substr($rejected->start_time, 0, 5),
            'end_time' => substr($rejected->end_time, 0, 5),
        ]))
            ->assertOk()
            ->assertSee('Tidak Dipakai')
            ->assertSee($rejected->room?->code)
            ->assertDontSee($rejected->purpose);
    }

    public function test_time_range_requires_both_start_and_end_time(): void
    {
        $this->from(route('room-search.index'))
            ->get(route('room-search.index', [
                'date' => now()->toDateString(),
                'start_time' => '08:00',
            ]))
            ->assertRedirect(route('room-search.index'))
            ->assertSessionHasErrors('end_time');
    }
}
