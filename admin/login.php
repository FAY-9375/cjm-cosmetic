<?php
require 'auth.php';
$setup = (int)db()->query("SELECT COUNT(*) FROM admins")->fetchColumn() === 0; // first run: create admin
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    if ($setup) {
        if (strlen($u) < 3 || strlen($p) < 8) $msg = 'Username needs 3+ characters and password 8+ characters.';
        else {
            db()->prepare("INSERT INTO admins (username, password_hash) VALUES (?,?)")->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
            $setup = false; $msg = 'Admin created. Please log in.';
        }
    } else {
        $s = db()->prepare("SELECT * FROM admins WHERE username = ?"); $s->execute([$u]); $a = $s->fetch();
        if ($a && password_verify($p, $a['password_hash'])) {
            session_regenerate_id(true); $_SESSION['admin'] = $a['id'];
            header('Location: index.php'); exit;
        }
        $msg = 'Wrong username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login">
  <form method="post" class="box">
    <h2><?= $setup ? 'Create admin account' : 'Admin login' ?></h2>
    <?php if ($msg): ?><p class="notice"><?= e($msg) ?></p><?php endif; ?>
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <button><?= $setup ? 'Create account' : 'Log in' ?></button>
  </form>
</body>
</html>
