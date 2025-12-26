<?php
$message = '';

$q = $_GET['q'] ?? '';
if ($q !== '') {
    $rows = $admin->search_login($q);
} else {
    $rows = $admin->read_all_login();
}
?>

<h2>Logins</h2>
<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="get" class="simple-form">
    <h3>Search logins</h3>
    <input type="hidden" name="page" value="logins">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>Login ID</th>
        <th>User ID</th>
        <th>Role</th>
        <!-- <th>password</th> -->
    </tr>
    <?php foreach ($rows as $l): ?>
    <tr>
        <td><?= htmlspecialchars($l['login_id']) ?></td>
        <td><?= htmlspecialchars($l['user_id']) ?></td>
        <td><?= htmlspecialchars($l['role']) ?></td>
        <!-- <td><?= htmlspecialchars($l['password_hash']) ?></td> -->
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No logins found.</p>
<?php endif; ?>