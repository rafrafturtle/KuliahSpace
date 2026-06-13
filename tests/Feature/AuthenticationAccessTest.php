<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\ClassSchedule;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_login_page_is_available(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $this->post(route('login.store'), [
            'email' => 'admin@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('rooms.index'))->assertOk();
    }

    public function test_dosen_access_is_limited_to_allowed_modules(): void
    {
        $this->post(route('login.store'), [
            'email' => 'dosen@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('room-availability.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
        $this->get(route('room-search.index'))->assertOk();
        $this->get(route('class-leaders.index'))->assertOk();
        $this->get(route('room-requests.create'))->assertOk();
        $this->get(route('room-request-history.index'))->assertForbidden();
        $this->get(route('buildings.index'))->assertOk();
        $this->get(route('rooms.index'))->assertOk();
    }

    public function test_ketua_kelas_access_is_limited_to_schedules_and_room_requests(): void
    {
        $this->post(route('login.store'), [
            'email' => 'ketuakelas@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('room-availability.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('room-requests.index'))->assertOk();
        $this->get(route('room-requests.create'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
        $this->get(route('room-search.index'))->assertOk();
        $this->get(route('buildings.index'))->assertOk();
        $this->get(route('rooms.index'))->assertOk();
        $this->get(route('room-request-history.index'))->assertForbidden();
    }

    public function test_mahasiswa_access_is_limited_to_schedules_and_room_availability(): void
    {
        $this->post(route('login.store'), [
            'email' => 'mahasiswa@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('room-availability.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('buildings.index'))->assertOk();
        $this->get(route('rooms.index'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
        $this->get(route('room-search.index'))->assertOk();
        $this->get(route('room-request-history.index'))->assertForbidden();
        $this->get(route('room-requests.index'))->assertForbidden();
        $this->get(route('room-requests.create'))->assertForbidden();
        $this->post(route('room-requests.store'), [])->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_schedule_page_shows_weekly_sections(): void
    {
        $this->post(route('login.store'), [
            'email' => 'admin@ulm.ac.id',
            'password' => 'password',
        ]);

        $this->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('Senin')
            ->assertSee('Selasa')
            ->assertSee('Rabu')
            ->assertSee('Kamis')
            ->assertSee('Jumat')
            ->assertSee('Sabtu')
            ->assertSee('Minggu')
            ->assertSee('Ruangan Terpakai')
            ->assertSee('Ruangan Tersedia');
    }

    public function test_non_admins_can_view_academic_pages_read_only(): void
    {
        $building = Building::firstOrFail();
        $room = Room::firstOrFail();
        $schedule = ClassSchedule::firstOrFail();

        foreach (['dosen@ulm.ac.id', 'ketuakelas@ulm.ac.id', 'mahasiswa@ulm.ac.id'] as $email) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'password',
            ]);

            $this->get(route('buildings.index'))->assertOk();
            $this->get(route('buildings.show', $building))->assertOk();
            $this->get(route('rooms.index'))->assertOk();
            $this->get(route('rooms.show', $room))->assertOk();
            $this->get(route('schedules.index'))->assertOk();
            $this->get(route('schedules.show', $schedule))->assertOk();

            $this->post(route('logout'));
        }
    }

    public function test_non_admins_cannot_access_academic_mutation_routes(): void
    {
        $building = Building::firstOrFail();
        $room = Room::firstOrFail();
        $schedule = ClassSchedule::firstOrFail();

        foreach (['dosen@ulm.ac.id', 'ketuakelas@ulm.ac.id', 'mahasiswa@ulm.ac.id'] as $email) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'password',
            ]);

            $this->get(route('buildings.create'))->assertForbidden();
            $this->post(route('buildings.store'), [])->assertForbidden();
            $this->get(route('buildings.edit', $building))->assertForbidden();
            $this->put(route('buildings.update', $building), [])->assertForbidden();
            $this->delete(route('buildings.destroy', $building))->assertForbidden();

            $this->get(route('rooms.create'))->assertForbidden();
            $this->post(route('rooms.store'), [])->assertForbidden();
            $this->get(route('rooms.edit', $room))->assertForbidden();
            $this->put(route('rooms.update', $room), [])->assertForbidden();
            $this->delete(route('rooms.destroy', $room))->assertForbidden();

            $this->get(route('schedules.create'))->assertForbidden();
            $this->post(route('schedules.store'), [])->assertForbidden();
            $this->get(route('schedules.edit', $schedule))->assertForbidden();
            $this->put(route('schedules.update', $schedule), [])->assertForbidden();
            $this->delete(route('schedules.destroy', $schedule))->assertForbidden();

            $this->post(route('logout'));
        }
    }

    public function test_non_admin_room_index_only_shows_detail_action(): void
    {
        $this->post(route('login.store'), [
            'email' => 'dosen@ulm.ac.id',
            'password' => 'password',
        ]);

        $this->get(route('rooms.index'))
            ->assertOk()
            ->assertSee('Detail')
            ->assertDontSee('Tambah Ruangan')
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus');
    }
}
