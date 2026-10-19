<?php
$pageTitle = 'Kelola project';
require __DIR__ . '/_header.php';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Delete project
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?');
        $st->execute([$id]);
        $oldImg = $st->fetchColumn();
        if ($oldImg && file_exists(__DIR__ . '/../uploads/' . $oldImg)) {
            @unlink(__DIR__ . '/../uploads/' . $oldImg);
        }
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Project berhasil dihapus.';
    }
    
    // Save (Create / Update) project
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $demo = trim($_POST['demo_url'] ?? '');
        $repo = trim($_POST['repo_url'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $removeImage = isset($_POST['remove_image']) ? 1 : 0;

        if ($title === '') {
            $error = 'Nama project wajib diisi.';
        } else {
            // Retrieve existing image if editing
            $imageName = '';
            if ($id) {
                $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?');
                $st->execute([$id]);
                $imageName = (string)($st->fetchColumn() ?: '');
            }

            // If user checked remove image
            if ($removeImage && $imageName) {
                if (file_exists(__DIR__ . '/../uploads/' . $imageName)) {
                    @unlink(__DIR__ . '/../uploads/' . $imageName);
                }
                $imageName = '';
            }

            // Handle new file upload
            if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $f = $_FILES['image'];
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($f['tmp_name']);

                if (isset($allowed[$mime]) && $f['size'] <= 5 * 1024 * 1024) {
                    if (!is_dir(__DIR__ . '/../uploads')) {
                        mkdir(__DIR__ . '/../uploads', 0755, true);
                    }
                    // Delete old image if replacing
                    if ($imageName && file_exists(__DIR__ . '/../uploads/' . $imageName)) {
                        @unlink(__DIR__ . '/../uploads/' . $imageName);
                    }
                    $newFilename = 'project_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
                    if (move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $newFilename)) {
                        $imageName = $newFilename;
                    }
                } else {
                    $error = 'Format gambar tidak valid atau ukuran melebihi 5MB (Gunakan JPG, PNG, atau WEBP).';
                }
            }

            if (!$error) {
                if ($id) {
                    $stmt = $pdo->prepare('UPDATE projects SET title=?, category=?, description=?, image=?, demo_url=?, repo_url=?, featured=? WHERE id=?');
                    $stmt->execute([$title, $category, $description, $imageName, $demo, $repo, $featured, $id]);
                    $notice = 'Project berhasil diperbarui.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO projects (title, category, description, image, demo_url, repo_url, featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$title, $category, $description, $imageName, $demo, $repo, $featured]);
                    $notice = 'Project baru berhasil ditambahkan.';
                }
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

$items = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
?>

<?php if ($notice): ?>
  <div class="notice"><?= e($notice) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="notice error"><?= e($error) ?></div>
<?php endif; ?>

<div class="panel">
  <h2><?= $edit ? 'Edit Project: ' . e($edit['title']) : 'Tambah Project Baru' ?></h2>
  <form class="admin-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">

    <div class="form-grid">
      <label>
        Nama Project *
        <input class="admin-input" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="Misal: Parking Management System">
      </label>

      <label>
        Kategori / Teknologi
        <input class="admin-input" name="category" value="<?= e($edit['category'] ?? 'Web Development') ?>" placeholder="Misal: PHP · MySQL">
      </label>

      <label class="full">
        Deskripsi Singkat
        <textarea class="admin-input" name="description" placeholder="Ceritakan fitur atau tujuan dari project ini..."><?= e($edit['description'] ?? '') ?></textarea>
      </label>

      <label>
        URL Demo / Live Preview
        <input class="admin-input" type="url" name="demo_url" value="<?= e($edit['demo_url'] ?? '') ?>" placeholder="https://demo-project.com">
      </label>

      <label>
        URL Source Code / GitHub
        <input class="admin-input" type="url" name="repo_url" value="<?= e($edit['repo_url'] ?? '') ?>" placeholder="https://github.com/username/project">
      </label>

      <!-- Image Upload Field -->
      <label class="full">
        Gambar / Screenshot Project (JPG, PNG, WEBP · Maks 5MB)
        <?php if (!empty($edit['image']) && file_exists(__DIR__ . '/../uploads/' . basename($edit['image']))): ?>
          <div style="display:flex; align-items:center; gap:16px; margin:10px 0; padding:12px; background:rgba(255,255,255,0.03); border-radius:10px; border:1px solid #30363d;">
            <img src="../uploads/<?= e(basename($edit['image'])) ?>" alt="Preview" style="width:120px; height:75px; object-fit:cover; border-radius:8px; border:1px solid #484f58;">
            <div>
              <div style="font-size:0.85rem; color:#c9d1d9; margin-bottom:6px;">Gambar saat ini: <code><?= e($edit['image']) ?></code></div>
              <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.82rem; color:#f87171; cursor:pointer;">
                <input type="checkbox" name="remove_image" value="1"> Hapus gambar ini
              </label>
            </div>
          </div>
        <?php endif; ?>
        <input class="admin-input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </label>

      <label class="full">
        <span>
          <input type="checkbox" name="featured" <?= !empty($edit['featured']) ? 'checked' : '' ?>>
          Tandai sebagai <strong>Project Pilihan / Unggulan (Featured)</strong>
        </span>
      </label>
    </div>

    <div style="display:flex; gap:10px; margin-top:8px;">
      <button class="admin-btn" type="submit"><?= $edit ? 'Simpan Perubahan' : 'Tambah Project' ?></button>
      <?php if ($edit): ?>
        <a class="admin-btn secondary" href="projects.php">Batal Edit</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="panel">
  <h2>Daftar Project (<?= count($items) ?>)</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:70px;">Preview</th>
          <th>Nama & Deskripsi</th>
          <th>Kategori</th>
          <th>Unggulan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$items): ?>
          <tr><td colspan="5" style="text-align:center; color:#8b949e; padding:30px;">Belum ada project yang dibuat.</td></tr>
        <?php else: ?>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <?php if (!empty($it['image']) && file_exists(__DIR__ . '/../uploads/' . basename($it['image']))): ?>
                  <img src="../uploads/<?= e(basename($it['image'])) ?>" alt="" style="width:60px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #30363d;">
                <?php else: ?>
                  <div style="width:60px; height:40px; border-radius:6px; background:#21262d; border:1px dashed #30363d; display:flex; align-items:center; justify-content:center; color:#8b949e; font-size:0.65rem;">No Image</div>
                <?php endif; ?>
              </td>
              <td>
                <strong style="color:#ffffff;"><?= e($it['title']) ?></strong>
                <?php if ($it['demo_url']): ?>
                  <a href="<?= e($it['demo_url']) ?>" target="_blank" style="color:#58a6ff; font-size:0.75rem; margin-left:8px;">Live ↗</a>
                <?php endif; ?>
                <br>
                <span class="muted" style="font-size:0.84rem;"><?= e(mb_strimwidth($it['description'] ?? '', 0, 90, '...')) ?></span>
              </td>
              <td><span style="font-size:0.82rem; padding:3px 8px; background:rgba(91,108,255,0.15); color:#7988ff; border-radius:6px;"><?= e($it['category']) ?></span></td>
              <td><?= $it['featured'] ? '<span style="color:#fbbf24; font-weight:700;">★ Ya</span>' : '<span class="muted">—</span>' ?></td>
              <td>
                <div style="display:flex; gap:6px;">
                  <a class="admin-btn secondary" style="padding:6px 12px; font-size:0.82rem;" href="?edit=<?= (int)$it['id'] ?>">Edit</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Hapus project ini secara permanen?')">
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
