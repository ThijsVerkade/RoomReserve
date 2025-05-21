<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, Room $room): JsonResponse
    {
        $reservation = new Reservation([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'purpose' => $request->purpose ?? '',
            'user_id' => 1,
            'room_id' => $room->id,
        ]);

        $reservation->room()->associate($room);
        $reservation->save();

        return response()->json($reservation, 201);
    }
}
