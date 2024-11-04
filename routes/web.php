<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('buttonPlaceholder');
});

Route::controller(TicketController::class)->group(function () {
    Route::get('/book', 'chooseAction');
    Route::post('/approve', 'approve');
    Route::post('/external', function () {
        return response(['message' => 'good!'], 200);
    });
});