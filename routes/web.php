<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('buttonPlaceholder');
});

Route::controller(TicketController::class)->group(function () {
    Route::post('/chooseAction', 'chooseAction');
    Route::post('/approve', 'approve');
    Route::post('/addToDatabase', 'addOrderToDatabase');
});