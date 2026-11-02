<?php
require 'db.php';
must_login();

$pageTitle = 'Home';
$me = $_SESSION['member'];

$counts = [];
if (in_array($me['position'], ['Admin', 'Registrar'], true)) {
    $counts = $pdo->query('SELECT position, COUNT(*) AS n FROM portal_members GROUP BY position')->fetchAll();
}

require 'partials/layout_top.php';
?>
<div class="panel hello">
    <img class="big-photo" src="<?= h(photo_of($me['username'])) ?>" alt="">
    <div>
        <h2>Hello, <?= h($me['full_name']) ?></h2>
        <p>You are signed in as <b><?= h($me['username']) ?></b> <span class="tag"><?= h($me['position']) ?></span></p>
    </div>
</div>

<?php if ($counts): ?>
<div class="stats">
    <?php foreach ($counts as $c): ?>
        <div class="stat"><div class="num"><?= (int)$c['n'] ?></div><div><?= h($c['position']) ?></div></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require 'partials/layout_bottom.php'; ?>
