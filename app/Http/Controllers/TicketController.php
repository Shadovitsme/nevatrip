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
        if (empty($req->json('tickets'))) {
            return response(['error' => 'no data provided'], 400, ['Content-type' => 'Application/json']);
        }
        $arBarcodes = $this->orderTickets($req->json('tickets'), $req->json('event'));

        if (empty($req->json('tickets'))) {
            return response(['error' => 'external API error'], 500, ['Content-type' => 'Application/json']);
        }

        // Логика внешнего API не поменялась и вместо одного заказа на множество билетов каждый билет становится отдельным заказом для внешнего API.
        foreach ($arBarcodes as $barcodes) {
            foreach ($barcodes as $barcode) {
                $approve = $this->approve($barcode);
                if ($approve->getStatusCode() != 200) {
                    // По хорошему на клиент не надо передавать сырые ошибки от внешнего API, но в рамках задания так проще.
                    return $approve;
                }
            }
        }

        if (!$this->addOrderToDatabase($req->json('event'), $req->json('tickets'), $arBarcodes)) {
            return response(['error' => 'order not saved'], 500, ['Content-type' => 'Application/json']);
        }

        return response(['message' => 'order added'], 200, ['Content-type' => 'Application/json']);
    }


    public function orderTickets($arTickets, $event): null|array
    {
        $arBarcodes = [];

        foreach ($arTickets as $tickets) {

            $arBarcodes[$tickets['type']] = [];

            for ($i = $tickets['quantity']; $i > 0; $i--) {
                // Лимит повтора запроса к стороннему API на всякий случай.
                $lim = 0;

                while (true) {
                    $barcode = $this->generateBarcode();

                    while (in_array($barcode, $arBarcodes[$tickets['type']]) || $this->findBarcodeIbTable($barcode)) {
                        $barcode = $this->generateBarcode();
                    }

                    $try = $this->book(event_id: $event, barcode: $barcode);

                    if ($try->getStatusCode() == 200) {
                        $arBarcodes[$tickets['type']][] = $barcode;
                        break;
                    }

                    if ($lim > 100) {
                        return null;
                    }

                    $lim++;
                }
            }
        }
        return $arBarcodes;
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
        $answerChoose = rand(0, 10);
        if ($answerChoose > 0) {
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

    private function generateBarcode(): string
    {
        $noFinishedBarcode = rand(0, 9999999);
        $len = strlen($noFinishedBarcode);
        if ($len < 7) {
            for ($i = $len; $i < 7; $i++) {
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

    public function addOrderToDatabase($event_id, $arTickets, $barcodes): bool
    {
        // TODO теоретически по заданию это должно быть вычислено из стоимости каждой категории билетов, которое передается в первом запросе... что глупо, но задание есть задание, поэтому пояснить в ридми почему мы отказались от изначального задания после нормализации.
        $equal_price = 0;

        $booking_id = DB::table('bookings')->insertGetId([
            'event_id' => $event_id,
            'equal_price' => 0,
        ]);

        foreach ($arTickets as $tickets) {
            $type_id = $tickets['type'];
            $arBarcodes = $barcodes[$type_id];
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

            foreach ($arBarcodes as $code) {
                DB::table('tickets_barcodes')->insert([
                    'ticket_id' => $ticketId,
                    'barcode' => $code
                ]);
            }

            $equal_price += $sell_price * $quantity;
        }

        DB::table('bookings')->where('id', $booking_id)
        ->update(
            ['equal_price' => $equal_price]
        );

        return true;
    }
}
