<?php
require 'config.php';
header('Content-Type: application/json');
$s = db()->prepare("SELECT TIME_FORMAT(appt_time, '%H:%i') FROM appointments WHERE appt_date = ? AND status <> 'CANCELLED'");
$s->execute([$_GET['date'] ?? '']);
echo json_encode($s->fetchAll(PDO::FETCH_COLUMN));
