<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TicketController extends Controller
{

    public function book(Request $req)
    {
        $barcode = $this->generateBarcode();
        if ($this->checkBarcodeUniqInOrderTable($barcode) || $this->findBarcodeInBooking($barcode)) {
            return response(['error' => 'barcode already exists'], 401, ['Content-type' => 'Application/json']);
        } else {
            DB::table('booking')->insert(['barcode' => $barcode]);
            return response()->json(
                [
                    'barcode' => $barcode,
                    'message' => 'order successfully booked'
                ]
            );
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
    {
        $noFinishedBarcode = rand(0, 9999999);
        if (strlen($noFinishedBarcode) < 7) {
            for ($i = 0; $i <= 8 - strlen($noFinishedBarcode); $i++) {
                $noFinishedBarcode = 0 . $noFinishedBarcode;
            }
        }
        $arNotFinishedBarcode = str_split($noFinishedBarcode);
        $sumNotChetNum = $arNotFinishedBarcode[0] + $arNotFinishedBarcode[2] + $arNotFinishedBarcode[4] + $arNotFinishedBarcode[6];
        $sumNotChetNum = $sumNotChetNum * 3;
        $sumChetNum = $arNotFinishedBarcode[0] + $arNotFinishedBarcode[2] + $arNotFinishedBarcode[4] + $arNotFinishedBarcode[6];
        $numSum = $sumChetNum + $sumNotChetNum;
        $oneNum = $numSum % 10;
        $controlNum = 10 - $oneNum;
        $barcode = $noFinishedBarcode . $controlNum;
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

    function addOrderToDatabase(Request $req)
    {
        $ticket_adult_price = $req->query('ticket_adult_price');
        $ticket_adult_quantity = $req->query('ticket_adult_quantity');
        $ticket_kid_price = $req->query('ticket_kid_price');
        $ticket_kid_quantity = $req->query('ticket_kid_quantity');
        $event_id = $req->query('event_id');
        $event_date = $req->query('event_date');
        $barcode = $req->query('barcode');

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
        $queryResult = DB::table('order_list')->where('barcode', $barcode)->get();
        foreach ($queryResult as $result) {
            return 'Аргументы которые функция получает на входе: event_id - ' . $result->event_id . ', event_date - ' . $result->event_date . ', ticket_adult_price - ' . $result->ticket_adult_price . ', ticket_adult_quantity - ' . $result->ticket_adult_quantity . ', ticket_kid_price - ' . $result->ticket_kid_price . ', ticket_kid_quantity - ' . $result->ticket_kid_quantity . ' barcode - ' . $result->barcode . '<b> Итог: </b>' . $result->equal_price . '<br />';
        }
    }
}
