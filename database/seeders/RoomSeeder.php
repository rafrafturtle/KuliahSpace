<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = collect([
            ['name' => 'Gedung FKIP 1', 'code' => 'FKIP-1', 'floor' => '1, 2, 3', 'description' => null, 'is_active' => true],
            ['name' => 'Gedung FKIP 2', 'code' => 'FKIP-2', 'floor' => '1', 'description' => null, 'is_active' => true],
            ['name' => 'Gedung Aula', 'code' => 'AULA', 'floor' => '1', 'description' => null, 'is_active' => true],
        ])->mapWithKeys(function (array $building): array {
            $model = Building::updateOrCreate(
                ['name' => $building['name']],
                $building
            );

            return [$building['name'] => $model];
        });

        $rooms = [
            ['code' => 'R-1', 'name' => 'Ruang Fuad Hasan', 'building_name' => 'Gedung FKIP 1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-2', 'name' => 'Ruang Ahmad Dahlan', 'building_name' => 'Gedung FKIP 1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-3', 'name' => 'R.A Kartini', 'building_name' => 'Gedung FKIP 1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, kipas angin, proyektor', 'is_active' => true],
            ['code' => 'R-4', 'name' => 'Ruang Ki Hajar Dewantara', 'building_name' => 'Gedung FKIP 1', 'capacity' => 120, 'facilities' => 'papan tulis, AC, kipas angin, proyektor, mikrofon, speaker', 'is_active' => true],
            ['code' => 'R-101', 'name' => 'Aula Hasan Bondan', 'building_name' => 'Gedung Aula', 'capacity' => 200, 'facilities' => 'papan tulis, AC, kipas angin, proyektor, podium', 'is_active' => true],
            ['code' => 'R-201', 'name' => 'Lab Pendidikan Komputer', 'building_name' => 'Gedung FKIP 1', 'capacity' => 60, 'facilities' => 'papan tulis, AC, proyektor, komputer', 'is_active' => true],
            ['code' => 'R-202', 'name' => 'Lab Komputer PGSD', 'building_name' => 'Gedung FKIP 2', 'capacity' => 60, 'facilities' => 'papan tulis, AC, proyektor, komputer', 'is_active' => true],
        ];

        foreach ($rooms as $room) {
            $building = $buildings->get($room['building_name']);
            unset($room['building_name']);

            Room::updateOrCreate(
                ['code' => $room['code']],
                $room + [
                    'building_id' => $building?->id,
                    'building' => $building?->name,
                    'floor' => null,
                ]
            );
        }
    }
}
