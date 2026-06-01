<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['code' => 'R-1', 'name' => 'Ruang Fuad Hasan', 'building' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-2', 'name' => 'Ruang Ahmad Dahlan', 'building' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-3', 'name' => 'R.A Kartini', 'building' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-4', 'name' => 'Ruang Ki Hajar Dewantara', 'building' => 'Gedung FKIP 1', 'floor' => '3', 'capacity' => 120, 'facilities' => 'papan tulis, AC, kipas angin, proyektor, mikrofon, speaker', 'is_active' => true],
            ['code' => 'R-101', 'name' => 'Aula Hasan Bondan', 'building' => 'Gedung Aula', 'floor' => '1', 'capacity' => 200, 'facilities' => 'papan tulis, AC, kipas angin, proyektor, podium', 'is_active' => true],
            ['code' => 'R-201', 'name' => 'Lab Pendidikan Komputer', 'building' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, proyektor, komputer', 'is_active' => true],
            ['code' => 'R-202', 'name' => 'Lab Komputer PGSD', 'building' => 'Gedung FKIP 2', 'floor' => '1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, proyektor, komputer', 'is_active' => true],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['code' => $room['code']],
                $room
            );
        }
    }
}
