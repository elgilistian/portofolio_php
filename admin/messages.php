<?php
$pageTitle='Pesan kontak';
require __DIR__.'/_header.php';
if(isset($_GET['read'])){$st=$pdo->prepare('UPDATE messages SET is_read=1 WHERE id=?');$st->execute([(int)$_GET['read']]);}
if(isset($_GET['delete'])){$st=$pdo->prepare('DELETE FROM messages WHERE id=?');$st->execute([(int)$_GET['delete']]);redirect('messages.php');}
$items=$pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="panel"><h2>Inbox pengunjung</h2><?php if(!$items): ?><p class="muted">Belum ada pesan.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Pengirim</th><th>Pesan</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody><?php foreach($items as $it): ?><tr><td><strong><?= e($it['name']) ?></strong><?= !$it['is_read']?' <span class="meta">• Baru</span>':'' ?><br><a class="text-link" href="mailto:<?= e($it['email']) ?>"><?= e($it['email']) ?></a></td><td><strong><?= e($it['subject']?:'Tanpa subjek') ?></strong><br><?= nl2br(e($it['message'])) ?></td><td><?= e(date('d M Y H:i',strtotime($it['created_at']))) ?></td><td><?php if(!$it['is_read']): ?><a class="admin-btn secondary" href="?read=<?= (int)$it['id'] ?>">Tandai dibaca</a><?php endif; ?> <a class="admin-btn danger" onclick="return confirm('Hapus pesan?')" href="?delete=<?= (int)$it['id'] ?>">Hapus</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__.'/_footer.php'; ?>
