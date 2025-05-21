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
}
