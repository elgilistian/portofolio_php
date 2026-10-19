<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
admin_required();
$pageTitle = $pageTitle ?? 'Dashboard';
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($pageTitle) ?> · Admin</title><link rel="stylesheet" href="../assets/style.css"></head><body class="admin-body">
<div class="admin-layout"><aside class="sidebar"><a class="brand" href="../index.php" target="_blank"><?= e(setting($pdo,'name','Portfolio')) ?><span style="color:#aeb3ff">.</span></a><div class="side-label">Workspace</div><nav>
<a class="side-link <?= $current==='index.php'?'active':'' ?>" href="index.php">Overview</a>
<a class="side-link <?= $current==='settings.php'?'active':'' ?>" href="settings.php">Profil, Tentang &amp; Riwayat</a>
<a class="side-link <?= $current==='projects.php'?'active':'' ?>" href="projects.php">Project</a>
<a class="side-link <?= $current==='skills.php'?'active':'' ?>" href="skills.php">Skills</a>
<a class="side-link <?= in_array($current,['posts.php','post_edit.php'])?'active':'' ?>" href="posts.php">Blog</a>
<a class="side-link <?= $current==='messages.php'?'active':'' ?>" href="messages.php">Pesan kontak</a>
</nav><div class="side-label">Website</div><a class="side-link" href="../index.php" target="_blank">Lihat website ↗</a><a class="side-link" href="logout.php">Keluar</a></aside>
<main class="admin-main"><div class="admin-top"><div><div class="muted" style="font-size:.8rem">PORTFOLIO CMS</div><h1><?= e($pageTitle) ?></h1></div><a class="admin-btn secondary" href="../index.php" target="_blank">Lihat website ↗</a></div>
