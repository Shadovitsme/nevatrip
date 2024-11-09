<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TicketController extends Controller
{
// TODO сделать тут генерацию для каждого билета и проверку этих баркодов не по таблице букингс
    public function book(Request $req)
    {
        for ($i = 1; $i <= $req->json('quantity'); $i++) {
        $barcode = $this->generateBarcode();
            if ($this->findBarcode($barcode)) {
            return response(['error' => 'barcode already exists'], 401, ['Content-type' => 'Application/json']);
            } else {
                DB::table('tickets_barcodes')->insert(['barcode' => $barcode]);
            }
        }
        return response()->json(
            [
                'message' => 'order successfully booked'
            ]
        );
    }

    public function findBarcode($barcode)
    {
        $queryResult = DB::table('tickets_barcodes')->where('barcode', $barcode)->get();
        if (empty($queryResult)) {
            return true;
        } else {
            return false;
        }
    }

    public function approve(Request $req)
    {
        // TODO либо сделать удаление баркодов из бд при неуспехе этого этапа либо хранить баркоды в отдельном массиве
        $answerChoose = rand(0, 1);
        if ($answerChoose == 0) {
            return response(['message' => 'order successfully aproved'], 200, ['Content-type' => 'Application/json']);
        } else {
            switch (rand(0, 3)) {
                case 0:
                    return response(['error' => 'event cancelled'], 400, ['Content-type' => 'Application/json']);
                    break;
                case 1:
                    return response(['error' => 'no tickets'], 400, ['Content-type' => 'Application/json']);
                    break;
                case 2:
                    return response(['error' => 'no seats'], 400, ['Content-type' => 'Application/json']);
                    break;
                case 3:
                    return response(['error' => 'fan removed'], 400, ['Content-type' => 'Application/json']);
                    break;
            }
        }
    }

    function generateBarcode()
    {
        // TODO понять что делать если контрольная сумма =10
        $noFinishedBarcode = rand(0, 9999999);
        if (strlen($noFinishedBarcode) < 7) {
            for ($i = 0; $i < (8 - strlen($noFinishedBarcode)); $i++) {
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

    // TODO переделать на добавление данных в кучу других таблиц
    function addOrderToDatabase(Request $req)
    {
        $ticket_adult_price = $req->json('ticket_adult_price');
        $ticket_adult_quantity = $req->json('ticket_adult_quantity');
        $ticket_kid_price = $req->json('ticket_kid_price');
        $ticket_kid_quantity = $req->json('ticket_kid_quantity');
        $event_id = $req->json('event_id');
        $event_date = $req->json('event_date');
        $barcode = $req->json('barcode');
        $extraTypes = $req->json('extraTypes');

        $equal_price = $ticket_adult_price * $ticket_adult_quantity + $ticket_kid_price * $ticket_kid_quantity;
        DB::table('order_list')->insert([
            'event_id' => $event_id,
            'event_date' => $event_date,
            'ticket_adult_price' => $ticket_adult_price,
            'ticket_adult_quantity' => $ticket_adult_quantity,
            'ticket_kid_price' => $ticket_kid_price,
            'ticket_kid_quantity' => $ticket_kid_quantity,
            'barcode' => $barcode,
            'equal_price' => $equal_price,
            'other_types' => $extraTypes,
        ]);
        if ($extraTypes) {
            $ticketTypeId = DB::table('ticket_types')->where('name', $req->json('ticket_type'))->get('id');
            $orderId = DB::table('order_list')->where('barcode', $barcode)->get('id');
            DB::table('connect_types_orders')->insert(
                [
                    'order_id' => $orderId[0]->id,
                    'type_id' => $ticketTypeId[0]->id,
                    'count' => $req->json('ticket_type_count')
                ]
            );
        }
        $queryResult = DB::table('order_list')->where('barcode', $barcode)->get();
        foreach ($queryResult as $result) {
            return 'Аргументы которые функция получает на входе: event_id - ' . $result->event_id . ', event_date - ' . $result->event_date . ', ticket_adult_price - ' . $result->ticket_adult_price . ', ticket_adult_quantity - ' . $result->ticket_adult_quantity . ', ticket_kid_price - ' . $result->ticket_kid_price . ', ticket_kid_quantity - ' . $result->ticket_kid_quantity . ' barcode - ' . $result->barcode . '<b> Итог: </b>' . $result->equal_price . '<br />';
        }
    }
}
