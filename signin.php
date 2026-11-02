<?php
require 'db.php';

if (!empty($_SESSION['member'])) {
    header('Location: home.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare('SELECT member_id, username, pass_hash, full_name, position FROM portal_members WHERE username = ?');
    $st->execute([trim($_POST['username'] ?? '')]);
    $m = $st->fetch();

    if ($m && password_verify($_POST['password'] ?? '', $m['pass_hash'])) {
        session_regenerate_id(true);
        unset($m['pass_hash']);
        $_SESSION['member'] = $m;
        header('Location: home.php');
        exit;
    }
    $error = 'Wrong username or password.';
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in - Campus Portal</title>
<link rel="stylesheet" href="assets/app.css">
</head>
<body class="plain">
<div class="panel center">
    <h2>Campus Portal</h2>
    <p class="muted">Sign in to continue</p>
    <?php if ($error): ?><div class="note bad"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <button class="btn block" type="submit">Sign in</button>
    </form>
</div>
</body></html>
