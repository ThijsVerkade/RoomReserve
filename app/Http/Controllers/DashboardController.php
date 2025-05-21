<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->query('date', now()->toDateString());

        $rooms = Room::all();

        $reservations = Reservation::with(['room', 'user'])
//            ->whereDate('date', $date)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'room_name' => $reservation->room->name,
                    'date' => $reservation->date,
                    'start_date' => $reservation->start_time,
                    'end_date' => $reservation->end_time,
                    'purpose' => $reservation->purpose ?? '',
                    'user_name' => $reservation->user->name,
                ];
            });

        return Inertia::render('dashboard', [
            'rooms' => $rooms,
            'reservations' => $reservations,
        ]);
    }
}
