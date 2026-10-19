<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
if (!empty($_SESSION['admin_id'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1'); $stmt->execute([$email]); $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true); $_SESSION['admin_id'] = $admin['id']; $_SESSION['admin_name'] = $admin['name']; redirect('index.php');
    }
    $error = 'Email atau password tidak sesuai.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Admin</title><link rel="stylesheet" href="../assets/style.css"></head><body class="admin-body"><div class="login-shell"><div class="login-card"><a class="brand" href="../index.php">Portfolio<span>.</span></a><h1>Welcome back.</h1><p class="muted">Masuk untuk mengelola website portofolio.</p><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post" class="admin-form"><label>Email<input class="admin-input" type="email" name="email" required autocomplete="username"></label><label>Password<input class="admin-input" type="password" name="password" required autocomplete="current-password"></label><button class="admin-btn" type="submit">Masuk ke admin →</button></form><p class="muted" style="font-size:.8rem;margin-top:22px"><a href="../index.php">← Kembali ke website</a></p></div></div></body></html>
