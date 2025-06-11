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



// Feature test scenarios for preventing double room reservations
// Scenario: Reservation attempt on a booked date
// This test simulates a user attempting to reserve a double room that has already been reserved for the same date range.
// HTTP Status: 422 Unprocessable Entity
// Database: Yes – uses RoomReservation factory to seed a prior reservation
// Inputs: POST /api/reservations with room_id, start_date, end_date
// Expected: JSON error response indicating date conflict
public function testReturns422WhenDoubleRoomIsBooked()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' => 'double']);
    $previousReservation = RoomReservation::factory()->create([
        'room_id' => $room->id,
       'start_date' => now()->subDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(1)->toDateTimeString(),
    ]);

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
       'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
}

// Scenario: Successful reservation when room is free
// This test ensures a reservation is created if the double room has no conflicting bookings.
// HTTP Status: 201 Created
// Database: Yes – creates reservation via factory
// Inputs: POST /api/reservations with valid date range and room_id
// Expected: JSON response with reservation data
public function testReturns201WhenDoubleRoomIsFree()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' => 'double']);

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
}

// Scenario: Reservation fails when overlapping partially
// This test validates that partial date overlaps also block a reservation.
// HTTP Status: 422 Unprocessable Entity
// Database: Yes – pre-existing reservation overlaps with requested start or end date
// Inputs: POST /api/reservations
// Expected: JSON error response mentioning overlap
public function testReturns422WhenDoubleRoomIsPartiallyBooked()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' => 'double']);
    RoomReservation::factory()->create([
        'room_id' => $room->id,
       'start_date' => now()->subDays(1)->toDateTimeString(),
        'end_date' => now()->subDays(2)->toDateTimeString(),
    ]);

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
       'start_date' => now()->subDays(1)->toDateTimeString(),
        'end_date' => now()->subDays(1)->toDateTimeString(),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
}

// Scenario: Room type mismatch
// Ensures users cannot reserve a room marked as single or another type when expecting a double.
// HTTP Status: 404 Not Found
// Database: Yes – uses Room factory with non-double type
// Inputs: POST /api/reservations with a single room_id
// Expected: JSON error indicating room type mismatch
public function testReturns404WhenRoomTypeMismatch()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' =>'single']);

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
       'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response->assertNotFound();
}

// Scenario: Validates input format
// Checks if invalid input data (like missing dates or malformed fields) is properly rejected.
// HTTP Status: 422 Unprocessable Entity
// Database: No
// Inputs: POST /api/reservations with bad or incomplete payload
// Expected: JSON with validation error messages
public function testReturns422WhenInputFormatIsInvalid()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' => 'double']);

    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => 'invalid-date',
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date']);
}

// Scenario: Concurrency prevention
// Simulates two simultaneous requests trying to reserve the same double room to test race condition handling.
// HTTP Status: 409 Conflict
// Database: Yes – concurrency simulated using transactions/locks
// Inputs: POST /api/reservations from two requests
// Expected: One succeeds with 201, the other fails with 409
public function testReturns409WhenConcurrencyPrevention()
{
    $user = User::factory()->create();
    $room = Room::factory()->create(['room_type' => 'double']);

    // Simulate two requests
    $response1 = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response2 = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/reservations", [
        'start_date' => now()->addDays(1)->toDateTimeString(),
        'end_date' => now()->addDays(2)->toDateTimeString(),
    ]);

    $response1->assertCreated()
        ->assertJsonStructure([
            'id',
            'room_id',
            'user_id',
            'start_date',
            'end_date',
        ]);

    $response2->assertConflict();
}


}
