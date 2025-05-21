Write Laravel 12 feature test scenarios for creating a reservation on a room.
Use the method name format:
testReturns[StatusText]When [Condition]
Each scenario should include:
- The expected HTTP status code (e.g., 200, 404, 201)
- Whether the database is used or changed
- Make use of factory for models
- Relevant inputs (route, request data)
- Expected response structure if applicable
  Output just the scenario names + a brief explanation of what each test does in text docs for php.


// Feature test scenarios for creating a reservation on a room using Laravel 12

// 1. Successful reservation
// testReturnsCreatedWhenReservationIsSuccessfullyCreated
// - Status: 201
// - DB: Inserts new reservation
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Valid start_date, end_date
// - Response: JSON with id, room_id, user_id, start_date, end_date

// 2. End date before start date
// testReturnsUnprocessableEntityWhenEndDateIsBeforeStartDate
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: end_date before start_date
// - Response: Validation error structure

// 3. Room not found
// testReturnsNotFoundWhenRoomDoesNotExist
// - Status: 404
// - DB: No change
// - Factory: User::factory()
// - Route: POST /api/rooms/99999/reservations
// - Data: Valid reservation payload
// - Response: Not Found error

// 4. Double booking
// testReturnsConflictWhenRoomIsAlreadyReservedForGivenDates
// - Status: 409
// - DB: Prevents overlapping reservation insert
// - Factory: User::factory(), Room::factory(), Reservation::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Dates overlap existing reservation
// - Response: Conflict error

// 5. Unauthorized request
// testReturnsUnauthorizedWhenUserIsNotAuthenticated
// - Status: 401
// - DB: No change
// - Factory: Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Valid payload, no auth
// - Response: Unauthorized error

// 6. Forbidden access
// testReturnsForbiddenWhenUserLacksPermissionToReserveRoom
// - Status: 403
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Valid, user lacks role
// - Response: Forbidden error

// 7. Missing start_date
// testReturnsValidationErrorWhenStartDateIsMissing
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Missing start_date
// - Response: Validation error

// 8. Missing end_date
// testReturnsValidationErrorWhenEndDateIsMissing
// - Status: 422
// - DB: No change
// - Factory: User::factory(), Room::factory()
// - Route: POST /api/rooms/{room}/reservations
// - Data: Missing end_date
// - Response: Validation error

// 9. Invalid room ID format
// testReturnsNotFoundWhenRoomIdIsNonNumeric
// - Status: 404
// - DB: No change
// - Factory: User::factory()
// - Route: POST /api/rooms/abc/reservations
// - Data: Valid payload
// - Response: Not Found error
