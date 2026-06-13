<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_room_detail_shows_local_room_image_for_non_admin(): void
    {
        $room = Room::where('code', 'R-1001')->firstOrFail();

        $this->post(route('login.store'), [
            'email' => 'dosen@ulm.ac.id',
            'password' => 'password',
        ]);

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('images/rooms/r-1001.png')
            ->assertDontSee('rooms/'.$room->id.'/edit')
            ->assertDontSee('Edit');
    }

    public function test_seeded_rooms_have_existing_local_svg_images(): void
    {
        Room::query()
            ->where('is_active', true)
            ->get()
            ->each(function (Room $room): void {
                $this->assertNotEmpty($room->image_path, $room->code.' has no image_path.');
                $this->assertStringStartsWith('images/rooms/', $room->image_path);
                $this->assertStringEndsWith('.png', $room->image_path);
                $this->assertFileExists(public_path($room->image_path));
            });
    }
}
