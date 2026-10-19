<?php
$pageTitle = 'Kelola blog';
require __DIR__ . '/_header.php';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int)$_POST['id'];
        $st = $pdo->prepare('SELECT cover FROM posts WHERE id = ?');
        $st->execute([$id]);
        $cover = $st->fetchColumn();
        if ($cover && file_exists(__DIR__ . '/../uploads/' . $cover)) {
            @unlink(__DIR__ . '/../uploads/' . $cover);
        }
        $st = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $st->execute([$id]);
        $notice = 'Artikel berhasil dihapus.';
    }
}

$items = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll();
?>

<?php if ($notice): ?>
  <div class="notice"><?= e($notice) ?></div>
<?php endif; ?>

<div class="panel">
  <div class="admin-top" style="margin:0 0 20px;">
    <div>
      <h2 style="margin:0;">Artikel Blog (<?= count($items) ?>)</h2>
      <p class="muted" style="margin-top:4px;">Tulis catatan belajar, insight teknis, atau dokumentasi proyek.</p>
    </div>
    <a class="admin-btn" href="post_edit.php">+ Tulis Artikel Baru</a>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:70px;">Cover</th>
          <th>Judul & Ringkasan</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$items): ?>
          <tr><td colspan="5" style="text-align:center; color:#8b949e; padding:30px;">Belum ada artikel. Klik "Tulis Artikel Baru" untuk mulai menulis.</td></tr>
        <?php else: ?>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <?php if (!empty($it['cover']) && file_exists(__DIR__ . '/../uploads/' . basename($it['cover']))): ?>
                  <img src="../uploads/<?= e(basename($it['cover'])) ?>" alt="" style="width:60px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #30363d;">
                <?php else: ?>
                  <div style="width:60px; height:40px; border-radius:6px; background:#21262d; border:1px dashed #30363d; display:flex; align-items:center; justify-content:center; color:#8b949e; font-size:0.65rem;">No Cover</div>
                <?php endif; ?>
              </td>
              <td>
                <strong style="color:#ffffff;"><?= e($it['title']) ?></strong>
                <?php if ($it['status'] === 'published'): ?>
                  <a href="../post.php?slug=<?= urlencode($it['slug']) ?>" target="_blank" style="color:#58a6ff; font-size:0.75rem; margin-left:8px;">Buka ↗</a>
                <?php endif; ?>
                <br>
                <span class="muted" style="font-size:0.84rem;"><?= e(mb_strimwidth($it['excerpt'] ?? '', 0, 90, '...')) ?></span>
              </td>
              <td>
                <?php if ($it['status'] === 'published'): ?>
                  <span style="font-size:0.8rem; padding:3px 8px; background:rgba(16,185,129,0.15); color:#34d399; border-radius:6px; font-weight:600;">Published</span>
                <?php else: ?>
                  <span style="font-size:0.8rem; padding:3px 8px; background:rgba(245,158,11,0.15); color:#fbbf24; border-radius:6px; font-weight:600;">Draft</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap; font-size:0.84rem; color:#8b949e;">
                <?= e(date('d M Y', strtotime($it['published_at'] ?: $it['created_at']))) ?>
              </td>
              <td>
                <div style="display:flex; gap:6px;">
                  <a class="admin-btn secondary" style="padding:6px 12px; font-size:0.82rem;" href="post_edit.php?id=<?= (int)$it['id'] ?>">Edit</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Hapus artikel ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <button class="admin-btn danger" style="padding:6px 12px; font-size:0.82rem;">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
