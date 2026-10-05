<?php
require __DIR__ . '/../config.php';
session_start();
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

function require_admin() { if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; } }
function csrf() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function check_csrf() { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('Invalid request'); }
function e(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Validates and stores one uploaded image, records it in the media table, returns the filename.
function save_upload(array $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload failed (error ' . $file['error'] . ').');
    if ($file['size'] > 3 * 1024 * 1024) throw new Exception('Each image must be under 3MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if (!$ext) throw new Exception('Only JPG, PNG, WEBP or GIF images are allowed.');
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) throw new Exception('Could not save the file.');
    db()->prepare("INSERT INTO media (filename) VALUES (?)")->execute([$name]);
    return $name;
}
