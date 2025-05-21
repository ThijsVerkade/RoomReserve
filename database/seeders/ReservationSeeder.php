<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all rooms and users
        $rooms = Room::all();
        $users = User::all();

        if ($rooms->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Today's date
        $today = Carbon::today();

        // Sample reservations for today
        $reservations = [
            [
                'room_id' => $rooms[0]->id,
                'user_id' => $users[0]->id,
                'start_date' => $today->copy()->setTimeFromTimeString('09:00:00'),
                'end_date' => $today->copy()->setTimeFromTimeString('10:30:00'),
                'purpose' => 'Weekly Team Standup',
            ],
            [
                'room_id' => $rooms[1]->id,
                'user_id' => $users[0]->id,
                'start_date' => $today->copy()->setTimeFromTimeString('11:00:00'),
                'end_date' => $today->copy()->setTimeFromTimeString('12:00:00'),
                'purpose' => 'Client Presentation',
            ],
            [
                'room_id' => $rooms[2]->id,
                'user_id' => $users[0]->id,
                'start_date' => $today->copy()->setTimeFromTimeString('13:30:00'),
                'end_date' => $today->copy()->setTimeFromTimeString('15:00:00'),
                'purpose' => 'Quarterly Budget Review',
            ],
            [
                'room_id' => $rooms[3]->id,
                'user_id' => $users[0]->id,
                'start_date' => $today->copy()->setTimeFromTimeString('14:00:00'),
                'end_date' => $today->copy()->setTimeFromTimeString('16:30:00'),
                'purpose' => 'New Employee Orientation',
            ],
        ];

        // Tomorrow's reservations
        $tomorrow = $today->copy()->addDay();
        $tomorrowReservations = [
            [
                'room_id' => $rooms[0]->id,
                'user_id' => $users[0]->id,
                'start_date' => $tomorrow->copy()->setTimeFromTimeString('10:00:00'),
                'end_date' => $tomorrow->copy()->setTimeFromTimeString('11:30:00'),
                'purpose' => 'Product Planning',
            ],
            [
                'room_id' => $rooms[2]->id,
                'user_id' => $users[0]->id,
                'start_date' => $tomorrow->copy()->setTimeFromTimeString('14:00:00'),
                'end_date' => $tomorrow->copy()->setTimeFromTimeString('15:30:00'),
                'purpose' => 'Strategy Meeting',
            ],
        ];

        // Create all reservations
        foreach (array_merge($reservations, $tomorrowReservations) as $reservation) {
            Reservation::create($reservation);
        }
    }
}
