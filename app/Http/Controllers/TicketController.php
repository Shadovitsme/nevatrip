<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TicketController extends Controller
{
    public function chooseAction(Request $req)
    {
        $barcode = $this->generateBarcode();
        $book = $this->book($barcode);
        while ($book == json_encode(['error' => 'barcode already exists'])) {
            $barcode = generateBarcode();
            $book = $this->book($barcode);
        }
        $approve = $this->approve($barcode);
        if ($approve !== json_encode(['message' => 'order successfully aproved'])) {
            return $approve;
        }
        
        return $this->addOrderToDatabase($req->query('ticket_adult_price'), $req->query('ticket_adult_quantity'), $req->query('ticket_kid_price'), $req->query('ticket_kid_quantity'), $req->query('event_id'), $req->query('event_date'), $barcode);
    }

    public function book($barcode)
    {
        if ($this->checkBarcodeUniqInOrderTable($barcode) || $this->findBarcodeInBooking($barcode)) {
            // TODO везде переписать на вот такой ответ
            return response(['error' => 'barcode already exists'], 401, ['Content-type' => 'Application/json'])->json(['error' => 'barcode already exists']);
        } else {
            DB::table('booking')->insert(['barcode' => $barcode]);
            return response(['message' => 'order successfully booked'], 200, ['Content-type' => 'Application/json']);
        }
    }

    public function findBarcodeInBooking($barcode)
    {
        $queryResult = DB::table('booking')->where('barcode', $barcode)->get();
        if (empty($queryResult)) {
            return true;
        } else {
            return false;
        }
    }


    public function approve($barcode)
    {
        $answerChoose = rand(0, 1);
        if ($answerChoose == 0) {
            return response(['message' => 'order successfully aproved'], 200, ['Content-type' => 'Application/json']);
        } else {
            switch (rand(0, 4)) {
                case 0:
                    return response(['error' => 'event cancelled'], 401, ['Content-type' => 'Application/json']);
                    break;
                case 1:
                    return response(['error' => 'no tickets'], 401, ['Content-type' => 'Application/json']);
                    break;
                case 2:
                    return response(['error' => 'no seats'], 401, ['Content-type' => 'Application/json']);
                    break;
                case 3:
                    return response(['error' => 'fan removed'], 401, ['Content-type' => 'Application/json']);
                    break;
            }
        }
    }

    function generateBarcode()
    { // TODO убрать то же самое из AJAX
      // TODO Генерировать EAN-8 barcode
        $barcode = '';
        for ($i = 1; $i <= 120; $i++) {
            $barcode .= rand(0, 9);
        }
        return $barcode;
    }

    function checkBarcodeUniqInOrderTable($barcode)
    {
        $queryResult = DB::table('order_list')->where('barcode', $barcode)->get();
        if (empty($queryResult)) {
            return true;
        } else {
            return false;
        }
    }

    function addOrderToDatabase($ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $event_id, $event_date, $barcode)
    {
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
            return 'Аргументы которые функция получает на входе: event_id - ' . $result->event_id . ', event_date - ' . $result->event_date . ', ticket_adult_price - ' . $result->ticket_adult_price . ', ticket_adult_quantity - ' . $result->ticket_adult_quantity . ', ticket_kid_price - ' . $result->ticket_kid_price . ', ticket_kid_quantity - ' . $result->ticket_kid_quantity . ' barcode - ' . $result->barcode . '<b> Итог: </b>' . $result->equal_price . '<br />';
        }
    }
}
