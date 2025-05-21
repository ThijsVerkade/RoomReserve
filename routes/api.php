<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::post('/rooms/{room}/reservations', [ReservationController::class, 'store']);

