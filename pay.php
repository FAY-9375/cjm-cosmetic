<?php
require 'config.php';
header('Content-Type: application/json');

function fail( string$m) { echo json_encode(['success' => false, 'message' => $m]); exit; }

$in = json_decode(file_get_contents('php://input'), true);
$name = trim($in['name'] ?? '');
$cart = $in['cart'] ?? [];
$address = trim($in['address'] ?? '');
if (!$name || !$cart || !$address) fail('Invalid request.');

// Normalise phone to 2547XXXXXXXX / 2541XXXXXXXX
$phone = preg_replace('/\D/', '', $in['phone'] ?? '');
if (preg_match('/^0([71]\d{8})$/', $phone, $m)) $phone = '254' . $m[1];
elseif (preg_match('/^([71]\d{8})$/', $phone, $m)) $phone = '254' . $m[1];
if (!preg_match('/^254[71]\d{8}$/', $phone)) fail('Invalid phone number.');

// Calculate total on the SERVER from DB prices (never trust the browser)
$pdo = db();
$total = 0; $lines = [];
$stmt = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");
foreach ($cart as $c) {
    $stmt->execute([(int)$c['id']]);
    $p = $stmt->fetch();
    $qty = max(1, (int)$c['qty']);
    if (!$p) fail('Product not found.');
    $total += $p['price'] * $qty;
    $lines[] = [$p['id'], $qty, $p['price']];
}

// Save order
$pdo->prepare("INSERT INTO orders (customer_name, phone, address, total) VALUES (?,?,?,?)")->execute([$name, $phone, $address, $total]);
$orderId = $pdo->lastInsertId();
$ins = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?,?,?,?)");
foreach ($lines as $l) $ins->execute([$orderId, $l[0], $l[1], $l[2]]);

// 1. Get access token
$ch = curl_init(MPESA_BASE . '/oauth/v1/generate?grant_type=client_credentials');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => CONSUMER_KEY . ':' . CONSUMER_SECRET]);
$token = json_decode(curl_exec($ch), true)['access_token'] ?? null;
curl_close($ch);
if (!$token) fail('Could not authenticate with M-Pesa. Check your keys.');

// 2. STK Push
$timestamp = date('YmdHis');
$payload = [
    'BusinessShortCode' => SHORTCODE,
    'Password' => base64_encode(SHORTCODE . PASSKEY . $timestamp),
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => (int)ceil($total),
    'PartyA' => $phone,
    'PartyB' => SHORTCODE,
    'PhoneNumber' => $phone,
    'CallBackURL' => CALLBACK_URL,
    'AccountReference' => 'Order' . $orderId,
    'TransactionDesc' => 'Shop payment',
];
$ch = curl_init(MPESA_BASE . '/mpesa/stkpush/v1/processrequest');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

if (($res['ResponseCode'] ?? '') === '0') {
    $pdo->prepare("UPDATE orders SET checkout_request_id = ? WHERE id = ?")->execute([$res['CheckoutRequestID'], $orderId]);
    echo json_encode(['success' => true, 'order_id' => $orderId]);
} else {
    $pdo->prepare("UPDATE orders SET status='FAILED', result_desc=? WHERE id=?")->execute([$res['errorMessage'] ?? 'STK push failed', $orderId]);
    fail($res['errorMessage'] ?? 'STK push failed.');
}
