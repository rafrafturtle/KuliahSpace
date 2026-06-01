<?php

namespace Tests\Feature;

use App\Models\RoomRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomRequestHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_active_room_request_page_only_shows_pending_requests(): void
    {
        $this->actingAs(User::where('email', 'admin@ulm.ac.id')->firstOrFail());

        $this->get(route('room-requests.index'))
            ->assertOk()
            ->assertSee('Diskusi kelompok proyek basis data.')
            ->assertDontSee('Kuliah tamu program studi.')
            ->assertDontSee('Rapat persiapan praktikum.')
            ->assertSee('Riwayat Pengajuan Ruang');
    }

    public function test_history_page_shows_completed_requests_without_pending_requests(): void
    {
        $this->actingAs(User::where('email', 'admin@ulm.ac.id')->firstOrFail());

        $this->get(route('room-request-history.index'))
            ->assertOk()
            ->assertSee('historyTable')
            ->assertSee('Kuliah tamu program studi.')
            ->assertSee('Rapat persiapan praktikum.')
            ->assertSee('Disetujui')
            ->assertSee('Ditolak')
            ->assertDontSee('Diskusi kelompok proyek basis data.');
    }

    public function test_history_date_filter_uses_request_date(): void
    {
        $this->actingAs(User::where('email', 'admin@ulm.ac.id')->firstOrFail());

        $approvedRequest = RoomRequest::where('status', RoomRequest::STATUS_APPROVED)->firstOrFail();
        $date = $approvedRequest->request_date->toDateString();

        $this->get(route('room-request-history.index', [
            'start_date' => $date,
            'end_date' => $date,
        ]))
            ->assertOk()
            ->assertSee('Kuliah tamu program studi.')
            ->assertDontSee('Rapat persiapan praktikum.')
            ->assertSee('value="'.$date.'"', false);
    }

    public function test_non_admins_cannot_access_room_request_history(): void
    {
        $this->actingAs(User::where('email', 'dosen@ulm.ac.id')->firstOrFail());

        $this->get(route('room-request-history.index'))->assertForbidden();
        $this->get(route('room-request-history.export-excel'))->assertForbidden();
        $this->get(route('room-request-history.export-pdf'))->assertForbidden();
    }

    public function test_mahasiswa_cannot_access_room_request_history(): void
    {
        $this->actingAs(User::where('email', 'bagus@kuliahspace.test')->firstOrFail());

        $this->get(route('room-request-history.index'))->assertForbidden();
        $this->get(route('room-requests.index'))->assertForbidden();
        $this->get(route('room-requests.create'))->assertForbidden();
    }

    public function test_non_admin_room_request_page_does_not_show_history_link(): void
    {
        $this->actingAs(User::where('email', 'dosen@ulm.ac.id')->firstOrFail());

        $this->get(route('room-requests.index'))
            ->assertOk()
            ->assertDontSee('Riwayat Pengajuan Ruang');
    }

    public function test_history_exports_are_downloadable(): void
    {
        $this->actingAs(User::where('email', 'admin@ulm.ac.id')->firstOrFail());

        $this->get(route('room-request-history.export-excel'))
            ->assertOk()
            ->assertDownload('riwayat-pengajuan-ruang.xlsx');

        $this->get(route('room-request-history.export-pdf'))
            ->assertOk()
            ->assertDownload('riwayat-pengajuan-ruang.pdf');
    }
}
