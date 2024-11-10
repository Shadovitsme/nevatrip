<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class TicketController extends Controller
{

    public function chooseAction(Request $req)
    {
        $book = $this->orderTickets($req);
        if ($book->getStatusCode() != 200) {
            return $book;
        }
        $barcodes = (json_decode($book->content()))->tickets;
        $approve = $this->approve();
        if ($approve->getStatusCode() != 200) {
            return $approve;
        }
        return $this->addOrderToDatabase($req, $barcodes);
    }


    public function orderTickets($req)
    {
        if (empty($req->json('tickets'))) {
            return response(['error' => 'no data provided'], 400, ['Content-type' => 'Application/json']);
        }

        $arBarcodes = [];

        foreach ($req->json('tickets') as $tickets) {

            $arBarcodes[$tickets['type']] = [];

            for ($i = $tickets['quantity']; $i > 0; $i--) {
                $lim = 0;

                while (true) {
                    $barcode = $this->generateBarcode();

                    while (in_array($barcode, $arBarcodes[$tickets['type']]) || $this->findBarcodeIbTable($barcode)) {
                        $barcode = $this->generateBarcode();
                    }

                    $try = $this->book(barcode: $barcode);

                    if ($try->getStatusCode() == 200) {
                        $arBarcodes[$tickets['type']][] = $barcode;
                        break;
                    }

                    if ($lim > 100) {
                        return response(['error' => 'external error'], 500, ['Content-type' => 'Application/json']);
                    }

                    $lim++;
                }
            }
        }

        $bookState = $this->book();
        if ($bookState->getStatusCode() == 200) {
            return response([
                'tickets' => $arBarcodes,
            ], 200, ['Content-type' => 'Application/json']);
        } else {
            return $bookState;
        }
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

    public function approve($barcode = null)
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

    public function book($event_id = null, $event_date = null, $ticket_adult_price = null, $ticket_adult_quantity = null, $ticket_kid_price = null, $ticket_kid_quantity = null, $barcode = null): Response
    {
        if (rand(0, 1)) {
            return response(['message' => 'order successfully booked'], 200, ['Content-type' => 'Application/json']);
        } else {
            return response(['error' => 'barcode already exists'], 400, ['Content-type' => 'Application/json']);
        }
    }

    private function generateBarcode()
    {
        $noFinishedBarcode = rand(0, 9999999);
        if (strlen($noFinishedBarcode) < 7) {
            for ($i = 0; $i < (8 - strlen($noFinishedBarcode)); $i++) {
                $noFinishedBarcode = 0 . $noFinishedBarcode;
            }
        }
        $arNotFinishedBarcode = str_split($noFinishedBarcode);

        $sumNotChetNum = $arNotFinishedBarcode[0] + $arNotFinishedBarcode[2] + $arNotFinishedBarcode[4] + $arNotFinishedBarcode[6];
        $sumNotChetNum = $sumNotChetNum * 3;
        $sumChetNum = $arNotFinishedBarcode[1] + $arNotFinishedBarcode[3] + $arNotFinishedBarcode[5];
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
    public function addOrderToDatabase(Request $req, $barcodes)
    {
        $event_id = 1;
        $equal_price = 0;

        $booking_id = DB::table('bookings')->insertGetId([
            'event_id' => $event_id,
            'equal_price' => 0,
        ]);

        foreach ($req->json('tickets') as $tickets) {
            $type_id = $tickets['type'];
            $nowArBarcodes = $barcodes->$type_id;
            $quantity = $tickets['quantity'];

            $sell_price = (DB::table('ticket_types_events')
            ->where('ticket_type_id', $type_id)
            ->where('event_id', $event_id)
            ->get('price'))[0]->price;

            $ticketId = DB::table('tickets')->insertGetId([
                'type_id' => $type_id,
                'booking_id' => $booking_id,
                'sell_price' => $sell_price,
                'quantity' => $quantity
            ]);

            foreach ($nowArBarcodes as $code) {
                DB::table('tickets_barcodes')->insert([
                    'ticket_id' => $ticketId,
                    'barcode' => $code
                ]);
            }

            $equal_price = $equal_price + $sell_price * $quantity;
        }

        DB::table('bookings')->where('id', $booking_id)
        ->update(
            ['equal_price' => $equal_price]
        );

        return  response(['message' => 'order added'], 200, ['Content-type' => 'Application/json']);
    }
}
