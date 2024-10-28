<?php

use Illuminate\Support\Facades\DB;
// Задача: написать функцию, которая будет добавлять заказы в эту таблицу.

// Аргументы которые функция получает на входе: event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity
// $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity

function addOrderToDatabase()
{
    $event_id = rand(1, 100);
    $event_date = '2021-08-21 13:00:00';
    $ticket_adult_price = rand(1, 1000);
    $ticket_adult_quantity = rand(1, 10);
    $ticket_kid_price = rand(1, 1000);
    $ticket_kid_quantity = rand(1, 10);
    $barcode = rand(1, 10);
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
        echo 'Аргументы которые функция получает на входе: event_id - ' . $result->event_id . ', event_date - ' . $result->event_date . ', ticket_adult_price - ' . $result->ticket_adult_price . ', ticket_adult_quantity - ' . $result->ticket_adult_quantity . ', ticket_kid_price - ' . $result->ticket_kid_price . ', ticket_kid_quantity - ' . $result->ticket_kid_quantity . '<b> Итог: </b>' . $result->equal_price . '<br />';
    }
}
addOrderToDatabase();
