<?php
require 'config.php';
header('Content-Type: application/json');
$s = db()->prepare("SELECT status, mpesa_receipt, result_desc FROM orders WHERE id = ?");
$s->execute([(int)($_GET['id'] ?? 0)]);
echo json_encode($s->fetch() ?: ['status' => 'FAILED']);
