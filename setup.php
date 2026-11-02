<?php
// Run ONCE: http://localhost/campus_portal/setup.php
// Creates its OWN database "campus_portal_db" - your other databases are not touched.

$note = '';
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `campus_portal_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `campus_portal_db`');
    $pdo->exec("CREATE TABLE IF NOT EXISTS portal_members (
        member_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        pass_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) DEFAULT NULL,
        position ENUM('Student','Instructor','Department Head','Registrar','Admin') NOT NULL,
        photo VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $exists = $pdo->query("SELECT COUNT(*) FROM portal_members WHERE username='campus_admin'")->fetchColumn();
    if (!$exists) {
        $st = $pdo->prepare("INSERT INTO portal_members (username, pass_hash, full_name, position) VALUES ('campus_admin', ?, 'Campus Administrator', 'Admin')");
        $st->execute([password_hash('Admin@2026', PASSWORD_DEFAULT)]);
        $note = 'Default admin created: <b>campus_admin</b> / <b>Admin@2026</b>';
    } else {
        $note = 'Admin account already exists.';
    }

    $dir = __DIR__ . '/images/users/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ok = true;
} catch (PDOException $e) {
    $ok = false;
    $note = 'Could not connect to MySQL. Start MySQL in XAMPP, then reload. (' . htmlspecialchars($e->getMessage()) . ')';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Setup</title>
<link rel="stylesheet" href="assets/app.css"></head>
<body class="plain"><div class="panel center">
<h2><?= $ok ? 'Setup complete' : 'Setup failed' ?></h2>
<p><?= $note ?></p>
<?php if ($ok): ?>
<p>Database: <b>campus_portal_db</b> &middot; Table: <b>portal_members</b></p>
<a class="btn" href="signin.php">Go to Sign in</a>
<?php endif; ?>
</div></body></html>
