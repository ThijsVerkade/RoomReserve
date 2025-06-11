<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, Room $room): JsonResponse
    {
        // Check if user has permission to reserve rooms
        if ($request->user()->role === 'guest') {
            return response()->json(['message' => 'You do not have permission to reserve rooms'], 403);
        }

        try {
            // Use a transaction to handle concurrency
            $reservation = DB::transaction(function () use ($request, $room) {
                // Lock the room for update to prevent race conditions
                $room = Room::lockForUpdate()->findOrFail($room->id);

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
                    throw new \Exception('Room is already booked for the selected dates');
                }

                // Create the reservation
                return Reservation::create([
                    'room_id' => $room->id,
                    'user_id' => $request->user()->id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);
            });

            return response()->json($reservation, 201);
        } catch (QueryException $e) {
            // Handle database-level concurrency issues
            return response()->json(['message' => 'Reservation conflict detected'], 409);
        } catch (\Exception $e) {
            // Handle validation errors from the transaction
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'start_date' => ['The selected dates are not available'],
                    'end_date' => ['The selected dates are not available'],
                ]
            ], 422);
        }
    }
}
