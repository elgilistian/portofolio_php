<?php
$pageTitle='Kelola blog';
require __DIR__.'/_header.php';$notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_POST['action']??'')==='delete'){$st=$pdo->prepare('DELETE FROM posts WHERE id=?');$st->execute([(int)$_POST['id']]);$notice='Artikel dihapus.';}
}
$items=$pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll();
?>
<?php if($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
<div class="panel"><div class="admin-top" style="margin:0"><div><h2 style="margin:0">Artikel</h2><p class="muted">Tulis catatan belajar atau cerita di balik project.</p></div><a class="admin-btn" href="post_edit.php">+ Tulis artikel</a></div><div class="table-wrap"><table><thead><tr><th>Judul</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody><?php foreach($items as $it): ?><tr><td><strong><?= e($it['title']) ?></strong><br><span class="muted"><?= e($it['excerpt']) ?></span></td><td><?= e($it['status']) ?></td><td><?= e(date('d M Y',strtotime($it['created_at']))) ?></td><td><a class="admin-btn secondary" href="post_edit.php?id=<?= (int)$it['id'] ?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Hapus artikel ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>"><button class="admin-btn danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/_footer.php'; ?>
