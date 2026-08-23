<?php
require_once __DIR__ . '/db.php';
if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $statement->execute([trim($_POST['username'] ?? '')]);
    $admin = $statement->fetch();
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php'); exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Admin login | <?= APP_NAME ?></title>
        <link rel="stylesheet" href="admin.css">
    </head>
    <body class="auth-page">
        <main class="auth-card">
            <p class="eyebrow">Private dashboard</p>
            <h1>Welcome back.</h1>
            <?php if ($error): ?>
                <p class="notice error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post">
                <label>Username<input name="username" required autocomplete="username"></label>
                <label>Password<input name="password" type="password" required autocomplete="current-password"></label>
                <button class="button" type="submit">Sign in</button>
            </form>
            <a class="muted" href="../index.html">Return to storefront</a>
        </main>
    </body>
</html>
