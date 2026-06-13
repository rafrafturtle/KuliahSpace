<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    private const FACILITIES_DEFAULT = 'papan tulis, AC, kipas angin, proyektor, kursi';

    private const FACILITIES_LABKOM = 'papan tulis, AC, kipas angin, proyektor, komputer, kursi';

    private const FACILITIES_AULA = 'papan tulis, AC, kipas angin, proyektor, mikrofon, mimbar, kursi';

    private const FACILITIES_STUDIO = 'papan tulis, AC, kipas angin, proyektor, peralatan studio, kursi';

    public function run(): void
    {
        $buildings = $this->buildings();
        $roomCodes = collect($this->rooms())->pluck('code')->all();

        foreach ($this->rooms() as $room) {
            $building = $buildings->get($room['building_name']);
            $floor = $room['floor'] ?? null;
            unset($room['building_name']);

            Room::updateOrCreate(
                ['code' => $room['code']],
                $room + [
                    'building_id' => $building?->id,
                    'building' => $building?->name,
                    'floor' => $floor,
                    'image_path' => $this->imagePath($room['code']),
                    'is_active' => true,
                ]
            );
        }

        Room::whereIn('code', $this->legacyRoomCodes())
            ->whereNotIn('code', $roomCodes)
            ->update(['is_active' => false]);

        Building::whereIn('name', ['Gedung FKIP 2'])
            ->update(['is_active' => false]);
    }

    private function buildings()
    {
        return collect([
            ['name' => 'Gedung FKIP 1', 'code' => 'FKIP-1', 'floor' => '1, 2, 3', 'description' => 'Gedung utama FKIP lantai 1 sampai 3.', 'is_active' => true],
            ['name' => 'Gedung Aula', 'code' => 'AULA', 'floor' => '1', 'description' => 'Gedung aula untuk kegiatan besar dan pertemuan umum.', 'is_active' => true],
            ['name' => 'Gedung FKIP 2 A', 'code' => 'FKIP-2A', 'floor' => '1', 'description' => 'Area ruang kuliah FKIP 2 blok A.', 'is_active' => true],
            ['name' => 'Gedung FKIP 2 B', 'code' => 'FKIP-2B', 'floor' => '1', 'description' => 'Area ruang kuliah FKIP 2 blok B.', 'is_active' => true],
            ['name' => 'Gedung FKIP 2 E', 'code' => 'FKIP-2E', 'floor' => '1', 'description' => 'Area ruang kuliah FKIP 2 blok E.', 'is_active' => true],
            ['name' => 'Gedung FKIP 2 Labkom', 'code' => 'FKIP-2L', 'floor' => '1', 'description' => 'Area laboratorium komputer FKIP 2.', 'is_active' => true],
        ])->mapWithKeys(function (array $building): array {
            $model = Building::updateOrCreate(
                ['name' => $building['name']],
                $building
            );

            return [$building['name'] => $model];
        });
    }

    private function rooms(): array
    {
        return [
            ['code' => 'R-1001', 'name' => 'Labkom Teknologi Pendidikan', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
            ['code' => 'R-1002', 'name' => 'Labkom Pendidikan Komputer', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
            ['code' => 'R-1003', 'name' => 'Ruang 21', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 30, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1004', 'name' => 'Ruang 22', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1005', 'name' => 'Ruang 23', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 30, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1006', 'name' => 'Ruang 24', 'building_name' => 'Gedung FKIP 1', 'floor' => '1', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1007', 'name' => 'Ruang Ahmad Dahlan', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1008', 'name' => 'Ruang Fuad Hasan', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1009', 'name' => 'Ruang Studio', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 45, 'facilities' => self::FACILITIES_STUDIO],
            ['code' => 'R-1010', 'name' => 'Ruang Lab Tari', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1011', 'name' => 'Ruang R.A. Kartini', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 30, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1012', 'name' => 'Ruang 33', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1013', 'name' => 'Ruang 34', 'building_name' => 'Gedung FKIP 1', 'floor' => '2', 'capacity' => 30, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1014', 'name' => 'Ruang Ki Hajar Dewantara', 'building_name' => 'Gedung FKIP 1', 'floor' => '3', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-1015', 'name' => 'Ruang Pangeran Antasari', 'building_name' => 'Gedung FKIP 1', 'floor' => '3', 'capacity' => 45, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-9001', 'name' => 'Aula Hasan Bondan', 'building_name' => 'Gedung Aula', 'floor' => '1', 'capacity' => 150, 'facilities' => self::FACILITIES_AULA],
            ['code' => 'R-2001', 'name' => 'Ruang A 1.1', 'building_name' => 'Gedung FKIP 2 A', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2002', 'name' => 'Ruang A 1.2', 'building_name' => 'Gedung FKIP 2 A', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2003', 'name' => 'Ruang A 1.3', 'building_name' => 'Gedung FKIP 2 A', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2004', 'name' => 'Ruang B 1.1', 'building_name' => 'Gedung FKIP 2 B', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2005', 'name' => 'Ruang B 1.2', 'building_name' => 'Gedung FKIP 2 B', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2006', 'name' => 'Ruang B 1.3', 'building_name' => 'Gedung FKIP 2 B', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2007', 'name' => 'Ruang E 1.1', 'building_name' => 'Gedung FKIP 2 E', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2008', 'name' => 'Ruang E 1.2', 'building_name' => 'Gedung FKIP 2 E', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2009', 'name' => 'Ruang E 1.3', 'building_name' => 'Gedung FKIP 2 E', 'floor' => '1', 'capacity' => 80, 'facilities' => self::FACILITIES_DEFAULT],
            ['code' => 'R-2010', 'name' => 'Labkom PGSD 1', 'building_name' => 'Gedung FKIP 2 Labkom', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
            ['code' => 'R-2011', 'name' => 'Labkom PGSD 2', 'building_name' => 'Gedung FKIP 2 Labkom', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
            ['code' => 'R-2012', 'name' => 'Labkom PGPAUD 1', 'building_name' => 'Gedung FKIP 2 Labkom', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
            ['code' => 'R-2013', 'name' => 'Labkom PGPAUD 2', 'building_name' => 'Gedung FKIP 2 Labkom', 'floor' => '1', 'capacity' => 60, 'facilities' => self::FACILITIES_LABKOM],
        ];
    }

    private function legacyRoomCodes(): array
    {
        return ['R-1', 'R-2', 'R-3', 'R-4', 'R-101', 'R-201', 'R-202', 'LAB-201', 'AUD-301'];
    }

    private function imagePath(string $roomCode): string
    {
        return 'images/rooms/'.strtolower($roomCode).'.png';
    }
}
