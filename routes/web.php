<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('buttonPlaceholder');
});

Route::controller(TicketController::class)->group(function () {
    Route::get('/book', 'book');
    Route::post('/approve', 'approve');
    Route::get('/addToDataBase/{barcode}', 'addOrderToDatabase');
});