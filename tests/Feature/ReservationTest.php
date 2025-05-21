<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReservationTest extends TestCase
{
    use RefreshDatabase;
// 1. Successful reservation
// testReturnsCreatedWhenReservationIsSuccessfullyCreated
// - Status: 201
// - DB: Inserts new reservation
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Valid start_date, end_date
// - Response: JSON with id, room_id, user_id, start_date, end_date
public function testReturnsCreatedWhenReservationIsSuccessfullyCreated(): void
{
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'id',
            'room_id',
            'user_id',
            'start_date',
            'end_date',
        ]);

    $this->assertDatabaseHas('reservations', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);
}

// 2. End date before start date
// testReturnsUnprocessableEntityWhenEndDateIsBeforeStartDate
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: end_date before start_date
// - Response: Validation error structure
public function testReturnsUnprocessableEntityWhenEndDateIsBeforeStartDate(): void
{
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => now()->addDays(2)->toDateTimeString(),
        'end_date' => now()->addDays(1)->toDateTimeString(),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);

    $this->assertDatabaseMissing('reservations', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'start_date' => now()->addDays(2)->toDateTimeString(),
        'end_date' => now()->addDays(1)->toDateTimeString(),
    ]);
}

// 3. Room not found
// testReturnsNotFoundWhenRoomDoesNotExist
// - Status: 404
// - DB: No change
// - Factory: User::factory()
// - Route: POST /api/rooms/99999/reservations
// - Data: Valid reservation payload
// - Response: Not Found error
    public function testReturnsNotFoundWhenRoomDoesNotExist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/rooms/99999/reservations", [
            'start_date' => now()->addDays(1)->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('reservations');
    }

// 4. Double booking
// testReturnsConflictWhenRoomIsAlreadyReservedForGivenDates
// - Status: 409
// - DB: Prevents overlapping reservation insert
// - Factory: User::factory(), Room::factory(), Reservation::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Dates overlap existing reservation
// - Response: Conflict error
    public function testReturnsConflictWhenRoomIsAlreadyReservedForGivenDates(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $existingReservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'start_date' => now()->addDays(1)->toDateTimeString(),
            'end_date' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
            'start_date' => now()->addDays(2)->toDateTimeString(),
            'end_date' => now()->addDays(4)->toDateTimeString(),
        ]);

        $response->assertStatus(409);

        $this->assertDatabaseMissing('reservations', [
            'room_id' => $room->id,
            'start_date' => now()->addDays(2)->toDateTimeString(),
            'end_date' => now()->addDays(4)->toDateTimeString(),
        ]);
    }

// 5. Forbidden access
// testReturnsForbiddenWhenUserLacksPermissionToReserveRoom
// - Status: 403
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Valid, user lacks role
// - Response: Forbidden error
public function testReturnsForbiddenWhenUserLacksPermissionToReserveRoom(): void
{
    $user = User::factory()->create(['role' => 'guest']);
    $room = Room::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('reservations');
}

// 6. Missing start_date
// testReturnsValidationErrorWhenStartDateIsMissing
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Missing start_date
// - Response: Validation error
    public function testReturnsValidationErrorWhenStartDateIsMissing(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
            'end_date' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['start_date']);

        $this->assertDatabaseMissing('reservations');
    }

// 7. Missing end_date
// testReturnsValidationErrorWhenEndDateIsMissing
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Missing end_date
// - Response: Validation error
    public function testReturnsValidationErrorWhenEndDateIsMissing(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
           'start_date' => now()->addDays(1)->toDateTimeString(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);

        $this->assertDatabaseMissing('reservations');
    }

// 8. Invalid room ID format
// testReturnsNotFoundWhenRoomIdIsNonNumeric
// - Status: 404
// - DB: No change
// - Factory: User::factory()
// - Route: POST /api/rooms/abc/reservations
// - Data: Valid payload
// - Response: Not Found error
    public function testReturnsNotFoundWhenRoomIdIsNonNumeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/rooms/abc/reservations", [
            'start_date' => now()->addDays(1)->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('reservations');
    }
}
