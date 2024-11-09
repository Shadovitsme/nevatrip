<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TicketController extends Controller
{
    public function book(Request $req)
    {
        $arBarcode = array();
        for ($i = 0; $i <= $req->json('quantity'); $i++) {
        $barcode = $this->generateBarcode();
            if ($this->findBarcodeIbTable($barcode) || in_array($barcode, $arBarcode)) {
            return response(['error' => 'barcode already exists'], 401, ['Content-type' => 'Application/json']);
            } else {
                $arBarcode[] = $barcode;
            }
        }
        return response()->json(
            [
                'barcodes' => $arBarcode,
                'message' => 'order successfully booked'
            ]
        );
    }

    private function findBarcodeIbTable($barcode)
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

    private function generateBarcode()
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
        if ($controlNum > 9) {
            $controlNum = 0;
        }
        $barcode = $noFinishedBarcode . $controlNum;
        return $barcode;
    }


    // TODO переделать на добавление данных в кучу других таблиц
    // TODO пробежаться по своей бд и подбить эрайзер под данную версию
    // TODO сделать бронь разных типов билетов
    public function addOrderToDatabase(Request $req)
    {
        $event_id = 1;
        $event = DB::table('events')->find($event_id);
        $event_date = $event->event_date;
        $barcodes = $req->json('barcodes');
        $type_id = 1;
        $quantity = $req->json('quantity');
        $sell_price = (DB::table('ticket_types_events')
        ->where('ticket_type_id', $type_id)
            ->where('event_id', $event_id)
            ->get('price'))[0]->price;
        $equal_price = $sell_price * $quantity;

        $booking_id = DB::table('bookings')->insertGetId([
            'event_id' => $event_id,
            'equal_price' => $equal_price,
        ]);


        $ticketId = DB::table('tickets')->insertGetId([
            'type_id' => $type_id,
            'booking_id' => $booking_id,
            'sell_price' => $sell_price,
            'quantity' => $quantity
        ]);
        foreach ($barcodes as $code) {
            echo $code . '<br ?>';
            DB::table('tickets_barcodes')->insert([
                'ticket_id' => $ticketId,
                'barcode' => $code
            ]);
        }
    }
}
