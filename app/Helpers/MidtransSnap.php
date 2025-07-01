<?php

namespace App\Helpers;

use Midtrans\Snap;
use Midtrans\Config;

class MidtransSnap
{
    public static function generateSnapToken($order_id, $gross_amount, $customerDetails, $itemDetails, $customFields = []) // Tambahkan $customFields
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

        // Tambahkan custom_fields jika tidak kosong
        if (!empty($customFields)) {
            $params['custom_field1'] = json_encode($customFields); // Midtrans hanya support custom_field1, custom_field2, custom_field3
            // Anda bisa menggunakan salah satunya dan menyimpan semua data JSON di dalamnya.
        }

        return Snap::getSnapToken($params);
    }
}
