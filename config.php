<?php
date_default_timezone_set("Africa/Nairobi");
// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'cjm_cosmetic');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Daraja (get these at https://developer.safaricom.co.ke) ----
define('MPESA_ENV', 'sandbox'); // change to 'live' for production
define('CONSUMER_KEY', 'z7VKzHDYhRsCV9H7ch9G3SQ8eR0A7AA11ozvKlGE7afFF5TI');
define('CONSUMER_SECRET', 'a6M8U8jCmMBTQZE7UxoAIGwifYbKjC3GzzrSim27nJ2i0I7oreN0cAcUwiXay6Bl');
define('SHORTCODE', '174379'); // sandbox shortcode
define('PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'); // sandbox passkey
// Must be a PUBLIC https URL (use ngrok while on localhost)
define('CALLBACK_URL', 'https://levers-trophy-partner.ngrok-free.dev/cjm-cosmetic/callback.php');

define('MPESA_BASE', MPESA_ENV === 'sandbox' ? 'https://sandbox.safaricom.co.ke' : 'https://api.safaricom.co.ke');

function db() {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
