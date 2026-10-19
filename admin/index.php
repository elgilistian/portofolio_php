<?php
$pageTitle='Overview';
require __DIR__.'/_header.php';
$projectCount=(int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$postCount=(int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$messageCount=(int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read=0')->fetchColumn();
?>
<div class="stats"><div class="stat"><span class="muted">Total project</span><strong><?= $projectCount ?></strong><span class="muted">Karya yang ditampilkan</span></div><div class="stat"><span class="muted">Artikel published</span><strong><?= $postCount ?></strong><span class="muted">Tulisan di halaman blog</span></div><div class="stat"><span class="muted">Pesan belum dibaca</span><strong><?= $messageCount ?></strong><span class="muted">Dari pengunjung website</span></div></div>
<div class="panel" style="margin-top:20px"><h2>Mulai dari sini</h2><p class="muted">Perbarui identitas dan konten agar website terasa personal. Semua perubahan tersimpan di database.</p><p><a class="admin-btn" href="settings.php">Edit tampilan & konten →</a> <a class="admin-btn secondary" href="projects.php">Kelola project</a></p></div>
<div class="panel"><h2>Tips singkat</h2><ul class="muted"><li>Gunakan foto profil asli atau foto yang memang ingin kamu tampilkan.</li><li>Isi deskripsi project dengan masalah, solusi, dan teknologi yang digunakan.</li><li>Publikasikan artikel blog setelah isi dan judul siap.</li><li>Jangan biarkan password admin bawaan saat website dipasang di hosting.</li></ul></div>
<?php require __DIR__.'/_footer.php'; ?>
