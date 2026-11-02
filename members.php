<?php
require 'db.php';
must_be(['Admin', 'Registrar']);

$pageTitle = 'All Members';
$me = $_SESSION['member'];
$note = '';

// Delete (Admin only, never yourself)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if ($me['position'] !== 'Admin') {
        $note = 'Only an Admin can delete members.';
    } elseif ((int)$_POST['delete_id'] === (int)$me['member_id']) {
        $note = 'You cannot delete your own account.';
    } else {
        $g = $pdo->prepare('SELECT username FROM portal_members WHERE member_id = ?');
        $g->execute([(int)$_POST['delete_id']]);
        if ($row = $g->fetch()) {
            $pdo->prepare('DELETE FROM portal_members WHERE member_id = ?')->execute([(int)$_POST['delete_id']]);
            $img = __DIR__ . '/images/users/' . $row['username'] . '.JPG';
            if (is_file($img)) unlink($img);
            $note = "Deleted '{$row['username']}'.";
        }
    }
}

$members = $pdo->query('SELECT member_id, username, full_name, email, position, created_at FROM portal_members ORDER BY position, username')->fetchAll();

require 'partials/layout_top.php';
?>
<div class="panel">
    <?php if ($note): ?><div class="note good"><?= h($note) ?></div><?php endif; ?>
    <table>
        <thead><tr><th>Photo</th><th>Username</th><th>Name</th><th>Email</th><th>Position</th><th>Added</th><?php if ($me['position'] === 'Admin'): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><img class="thumb" src="<?= h(photo_of($m['username'])) ?>" alt=""></td>
                <td><?= h($m['username']) ?></td>
                <td><?= h($m['full_name']) ?></td>
                <td><?= h($m['email']) ?></td>
                <td><span class="tag"><?= h($m['position']) ?></span></td>
                <td><?= h($m['created_at']) ?></td>
                <?php if ($me['position'] === 'Admin'): ?>
                <td>
                    <?php if ((int)$m['member_id'] !== (int)$me['member_id']): ?>
                    <form method="post" onsubmit="return confirm('Delete <?= h($m['username']) ?>?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$m['member_id'] ?>">
                        <button class="btn danger sm" type="submit">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require 'partials/layout_bottom.php'; ?>
