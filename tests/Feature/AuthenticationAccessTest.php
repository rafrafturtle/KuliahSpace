<?php

namespace Tests\Feature;

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
        ])->assertRedirect(route('schedules.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
        $this->get(route('class-leaders.index'))->assertOk();
        $this->get(route('room-requests.create'))->assertOk();
        $this->get(route('rooms.index'))->assertForbidden();
    }

    public function test_ketua_kelas_access_is_limited_to_schedules_and_room_requests(): void
    {
        $this->post(route('login.store'), [
            'email' => 'ketuakelas@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('schedules.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('room-requests.index'))->assertOk();
        $this->get(route('room-requests.create'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
    }

    public function test_mahasiswa_access_is_limited_to_schedules_and_room_availability(): void
    {
        $this->post(route('login.store'), [
            'email' => 'mahasiswa@ulm.ac.id',
            'password' => 'password',
        ])->assertRedirect(route('schedules.index'));

        $this->get(route('schedules.index'))->assertOk();
        $this->get(route('room-availability.index'))->assertOk();
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
}
