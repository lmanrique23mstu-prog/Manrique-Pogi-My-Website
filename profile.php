<?php
require 'db.php';
must_login();

$pageTitle = 'My Profile';
$me = $_SESSION['member'];
$note = '';
$noteType = '';

// Change own photo (saved over images/users/<Username>.JPG)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $msg] = save_photo($_FILES['photo'] ?? [], $me['username']);
    if ($ok) {
        $pdo->prepare('UPDATE portal_members SET photo = ? WHERE member_id = ?')
            ->execute(['images/users/' . $me['username'] . '.JPG', $me['member_id']]);
    }
    $note = $msg;
    $noteType = $ok ? 'good' : 'bad';
}

$st = $pdo->prepare('SELECT username, full_name, email, position, created_at FROM portal_members WHERE member_id = ?');
$st->execute([$me['member_id']]);
$info = $st->fetch();

require 'partials/layout_top.php';
?>
<div class="panel hello">
    <img class="big-photo" src="<?= h(photo_of($info['username'])) ?>" alt="">
    <div>
        <h2><?= h($info['full_name']) ?></h2>
        <p>Username: <b><?= h($info['username']) ?></b></p>
        <p>Position: <span class="tag"><?= h($info['position']) ?></span></p>
        <p>Email: <?= h($info['email'] ?: '-') ?></p>
        <p class="muted">Member since <?= h($info['created_at']) ?></p>
    </div>
</div>

<div class="panel form-panel">
    <h3>Change my photo</h3>
    <?php if ($note): ?><div class="note <?= h($noteType) ?>"><?= h($note) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="photo" accept="image/jpeg,image/png,image/gif" required>
        <button class="btn" type="submit">Upload</button>
    </form>
</div>
<?php require 'partials/layout_bottom.php'; ?>
