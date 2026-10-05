<?php
// Safaricom POSTs the payment result here.
require 'config.php';

$raw = file_get_contents('php://input');
file_put_contents('mpesa_log.txt', date('c') . " $raw\n", FILE_APPEND); // for debugging

$cb = json_decode($raw, true)['Body']['stkCallback'] ?? null;
if ($cb) {
    $checkoutId = $cb['CheckoutRequestID'];
    if ($cb['ResultCode'] == 0) {
        $receipt = null;
        foreach ($cb['CallbackMetadata']['Item'] as $item) {
            if ($item['Name'] === 'MpesaReceiptNumber') $receipt = $item['Value'];
        }
        db()->prepare("UPDATE orders SET status='PAID', mpesa_receipt=?, result_desc=? WHERE checkout_request_id=?")
            ->execute([$receipt, $cb['ResultDesc'], $checkoutId]);
    } else {
        db()->prepare("UPDATE orders SET status='FAILED', result_desc=? WHERE checkout_request_id=?")
            ->execute([$cb['ResultDesc'], $checkoutId]);
    }
}
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
