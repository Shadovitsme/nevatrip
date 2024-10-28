<?php

use Illuminate\Support\Facades\DB;
// Задача: написать функцию, которая будет добавлять заказы в эту таблицу.

// Аргументы которые функция получает на входе: event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity
// $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity

function generateBarcode()
{
    $barcode = '';
    for ($i = 1; $i <= 120; $i++) {
        $barcode .= rand(0, 9);
    }
    return $barcode;
}

// TODO добавить документационную письменность касательно того почему тут так стоят тру фолс
function checkBarcodeUniq($barcode)
{
    $queryResult = DB::table('order_list')->where('barcode', $barcode)->get();
    if (empty($queryResult)) {
        return true;
    } else {
        return false;
    }
}
function addOrderToDatabase()
{
    $event_id = rand(1, 100);
    $event_date = '2021-08-21 13:00:00';
    $ticket_adult_price = rand(1, 1000);
    $ticket_adult_quantity = rand(1, 10);
    $ticket_kid_price = rand(1, 1000);
    $ticket_kid_quantity = rand(1, 10);

    $barcode = generateBarcode();
    while (checkBarcodeUniq($barcode)) {
        $barcode = generateBarcode();
    }

    // TODO сделать расчет итоговой цены в самой таблице
    $equal_price = $ticket_adult_price * $ticket_adult_quantity + $ticket_kid_price * $ticket_kid_quantity;
    DB::table('order_list')->insert([
        'event_id' => $event_id,
        'event_date' => $event_date,
        'ticket_adult_price' => $ticket_adult_price,
        'ticket_adult_quantity' => $ticket_adult_quantity,
        'ticket_kid_price' => $ticket_kid_price,
        'ticket_kid_quantity' => $ticket_kid_quantity,
        'barcode' => $barcode,
        'equal_price' => $equal_price
    ]);
    $queryResult = DB::table('order_list')->get();
    foreach ($queryResult as $result) {
        echo 'Аргументы которые функция получает на входе: event_id - ' . $result->event_id . ', event_date - ' . $result->event_date . ', ticket_adult_price - ' . $result->ticket_adult_price . ', ticket_adult_quantity - ' . $result->ticket_adult_quantity . ', ticket_kid_price - ' . $result->ticket_kid_price . ', ticket_kid_quantity - ' . $result->ticket_kid_quantity . ' barcode - ' . $result->barcode . '<b> Итог: </b>' . $result->equal_price . '<br />';
    }
}
addOrderToDatabase();