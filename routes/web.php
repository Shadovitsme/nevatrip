<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('refactor_after');
});

Route::get('api.site.com/book', [TicketController::class, 'book']);

Route::get('api.site.com/approve)', [TicketController::class, 'approve']);