<?php
$pageTitle='Overview';
require __DIR__.'/_header.php';
$projectCount=(int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$postCount=(int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$messageCount=(int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read=0')->fetchColumn();
?>
<div class="stats"><div class="stat"><span class="muted">Total project</span><strong><?= $projectCount ?></strong><span class="muted">Karya yang ditampilkan</span></div><div class="stat"><span class="muted">Artikel published</span><strong><?= $postCount ?></strong><span class="muted">Tulisan di halaman blog</span></div><div class="stat"><span class="muted">Pesan belum dibaca</span><strong><?= $messageCount ?></strong><span class="muted">Dari pengunjung website</span></div></div>
<div class="panel" style="margin-top:20px"><h2>Mulai dari sini</h2><p class="muted">Perbarui foto profil, penjelasan tentang diri saya, riwayat pendidikan/pengalaman, serta karya agar website terasa personal. Semua perubahan langsung tersimpan di database.</p><p style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;"><a class="admin-btn" href="settings.php">Kelola Profil, Tentang Saya &amp; Riwayat →</a> <a class="admin-btn secondary" href="projects.php">Kelola Project</a> <a class="admin-btn secondary" href="skills.php">Kelola Skills</a></p></div>
<div class="panel"><h2>Tips singkat</h2><ul class="muted"><li>Unggah foto terbaikmu di menu <strong>Profil, Tentang &amp; Riwayat</strong> untuk ditampilkan pada kartu 3D section <em>Tentang Saya</em> dan modal <em>Profil Saya</em>.</li><li>Tambahkan jejak pendidikan, pengalaman kerja/proyek, organisasi, atau pencapaian pada tabel dinamis <strong>Riwayat &amp; Perjalanan</strong>.</li><li>Isi deskripsi project dengan masalah, solusi, dan teknologi yang digunakan.</li></ul></div>
<?php require __DIR__.'/_footer.php'; ?>
