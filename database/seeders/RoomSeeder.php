<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Conference Room A',
                'description' => 'Large conference room with projector',
                'capacity' => 20,
            ],
            [
                'name' => 'Meeting Room B',
                'description' => 'Medium-sized meeting room',
                'capacity' => 10,
            ],
            [
                'name' => 'Boardroom',
                'description' => 'Executive boardroom with video conferencing',
                'capacity' => 15,
            ],
            [
                'name' => 'Training Room',
                'description' => 'Training room with computers',
                'capacity' => 30,
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
