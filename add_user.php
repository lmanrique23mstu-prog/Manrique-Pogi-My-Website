<?php
require 'db.php';
must_be(['Admin', 'Registrar']);

$pageTitle = 'Add Member';
$note = '';
$noteType = '';
$f = ['username' => '', 'full_name' => '', 'email' => '', 'position' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['username']  = trim($_POST['username'] ?? '');
    $f['full_name'] = trim($_POST['full_name'] ?? '');
    $f['email']     = trim($_POST['email'] ?? '');
    $f['position']  = $_POST['position'] ?? '';
    $password       = $_POST['password'] ?? '';

    if ($f['username'] === '' || $f['full_name'] === '' || $password === '' || !in_array($f['position'], POSITIONS, true)) {
        $note = 'Please complete all required fields.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $f['username'])) {
        $note = 'Username: 3-50 characters, letters / numbers / underscore only.';
    } elseif (strlen($password) < 6) {
        $note = 'Password must be at least 6 characters.';
    } else {
        $chk = $pdo->prepare('SELECT 1 FROM portal_members WHERE username = ?');
        $chk->execute([$f['username']]);

        if ($chk->fetchColumn()) {
            $note = 'That username is already taken.';
        } else {
            // Save the image first (renamed to Username.JPG in images/users/)
            [$ok, $msg] = save_photo($_FILES['photo'] ?? [], $f['username']);

            if (!$ok) {
                $note = $msg;
            } else {
                try {
                    $ins = $pdo->prepare('INSERT INTO portal_members (username, pass_hash, full_name, email, position, photo) VALUES (?,?,?,?,?,?)');
                    $ins->execute([
                        $f['username'],
                        password_hash($password, PASSWORD_DEFAULT),
                        $f['full_name'],
                        $f['email'] !== '' ? $f['email'] : null,
                        $f['position'],
                        'images/users/' . $f['username'] . '.JPG',
                    ]);
                    $note = "Member '{$f['username']}' added. " . $msg;
                    $noteType = 'good';
                    $f = ['username' => '', 'full_name' => '', 'email' => '', 'position' => ''];
                } catch (PDOException $e) {
                    @unlink(__DIR__ . '/images/users/' . $f['username'] . '.JPG');
                    $note = 'Database error: member was not saved.';
                }
            }
        }
    }
    if ($noteType === '') $noteType = 'bad';
}

require 'partials/layout_top.php';
?>
<div class="panel form-panel">
    <?php if ($note): ?><div class="note <?= h($noteType) ?>"><?= h($note) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="grid2">
            <div>
                <label>Position *</label>
                <select name="position" required>
                    <option value="">-- choose --</option>
                    <?php foreach (POSITIONS as $p): ?>
                        <option <?= $f['position'] === $p ? 'selected' : '' ?>><?= h($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Username *</label>
                <input type="text" name="username" value="<?= h($f['username']) ?>" required>
            </div>
            <div>
                <label>Full name *</label>
                <input type="text" name="full_name" value="<?= h($f['full_name']) ?>" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= h($f['email']) ?>">
            </div>
            <div>
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Photo * (JPG / PNG / GIF)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/gif" required>
            </div>
        </div>
        <button class="btn" type="submit">Add Member</button>
    </form>
</div>
<?php require 'partials/layout_bottom.php'; ?>
