<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, Room $room): JsonResponse
    {
        // Check for overlapping reservations
        $overlapping = Reservation::where('room_id', $room->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json(['message' => 'Room is already reserved for the selected dates'], 409);
        }

        $reservation = new Reservation([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        $reservation->room()->associate($room);
        $reservation->user()->associate(Auth::user());
        $reservation->save();

        return response()->json($reservation, 201);
    }
}
