<?php

namespace App\Helpers;

use Midtrans\Snap;
use Midtrans\Config;

class MidtransSnap
{
    public static function generateSnapToken($order_id, $gross_amount, $customerDetails, $itemDetails)
    {
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        $transactionDetails = [
            'order_id' => $order_id,
            'gross_amount' => (int)$gross_amount,
        ];

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        return Snap::getSnapToken($params);
    }
}
