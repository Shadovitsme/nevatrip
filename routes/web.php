<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('buttonPlaceholder');
});

Route::controller(TicketController::class)->group(function () {
    Route::get('/book', 'book');
    Route::get('/approve/{barcode}', 'approve');
    Route::get('/addToDatabase', 'addOrderToDatabase');
});