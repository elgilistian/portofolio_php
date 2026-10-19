<?php
$pageTitle = 'Editor artikel';
require __DIR__ . '/_header.php';

$id = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch();
}

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    $slug = slugify($slugInput !== '' ? $slugInput : $title);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $removeCover = isset($_POST['remove_cover']) ? 1 : 0;

    if ($title === '') {
        $error = 'Judul artikel wajib diisi.';
    } else {
        // Current cover
        $coverName = (string)($post['cover'] ?? '');

        // Remove cover if requested
        if ($removeCover && $coverName) {
            if (file_exists(__DIR__ . '/../uploads/' . $coverName)) {
                @unlink(__DIR__ . '/../uploads/' . $coverName);
            }
            $coverName = '';
        }

        // Handle uploaded cover image
        if (!empty($_FILES['cover']['name']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
            $f = $_FILES['cover'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($f['tmp_name']);

            if (isset($allowed[$mime]) && $f['size'] <= 5 * 1024 * 1024) {
                if (!is_dir(__DIR__ . '/../uploads')) {
                    mkdir(__DIR__ . '/../uploads', 0755, true);
                }
                // Delete old cover if replacing
                if ($coverName && file_exists(__DIR__ . '/../uploads/' . $coverName)) {
                    @unlink(__DIR__ . '/../uploads/' . $coverName);
                }
                $newFilename = 'post_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
                if (move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $newFilename)) {
                    $coverName = $newFilename;
                }
            } else {
                $error = 'Format cover tidak valid atau ukuran melebihi 5MB (Gunakan JPG, PNG, atau WEBP).';
            }
        }

        if (!$error) {
            // Check for slug duplication
            $check = $pdo->prepare('SELECT id FROM posts WHERE slug = ? AND id != ? LIMIT 1');
            $check->execute([$slug, $id]);
            if ($check->fetch()) {
                $slug .= '-' . time();
            }

            if ($id) {
                $st = $pdo->prepare('UPDATE posts SET title=?, slug=?, excerpt=?, content=?, cover=?, status=?, published_at=IF(?="published", COALESCE(published_at, NOW()), NULL) WHERE id=?');
                $st->execute([$title, $slug, $excerpt, $content, $coverName, $status, $status, $id]);
                $notice = 'Artikel berhasil diperbarui.';
            } else {
                $st = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, cover, status, published_at) VALUES (?, ?, ?, ?, ?, ?, IF(?="published", NOW(), NULL))');
                $st->execute([$title, $slug, $excerpt, $content, $coverName, $status, $status]);
                $id = (int)$pdo->lastInsertId();
                $notice = 'Artikel baru berhasil dibuat.';
            }

            // Reload post
            $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
            $st->execute([$id]);
            $post = $st->fetch();
        }
    }
}
?>

<?php if ($notice): ?>
  <div class="notice"><?= e($notice) ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="notice error"><?= e($error) ?></div>
<?php endif; ?>

<div class="panel">
  <h2><?= $id ? 'Edit Artikel: ' . e($post['title']) : 'Tulis Artikel Baru' ?></h2>
  <form class="admin-form" method="post" enctype="multipart/form-data">
    <label>
      Judul Artikel *
      <input class="admin-input" name="title" required value="<?= e($post['title'] ?? '') ?>" placeholder="Misal: Membangun Arsitektur Web Modular">
    </label>

    <label>
      Slug URL
      <input class="admin-input" name="slug" value="<?= e($post['slug'] ?? '') ?>" placeholder="otomatis-dari-judul-jika-kosong">
    </label>

    <!-- Cover Image Upload Field -->
    <label>
      Gambar Sampul / Cover Artikel (JPG, PNG, WEBP · Maks 5MB)
      <?php if (!empty($post['cover']) && file_exists(__DIR__ . '/../uploads/' . basename($post['cover']))): ?>
        <div style="display:flex; align-items:center; gap:16px; margin:10px 0; padding:12px; background:rgba(255,255,255,0.03); border-radius:10px; border:1px solid #30363d;">
          <img src="../uploads/<?= e(basename($post['cover'])) ?>" alt="Cover" style="width:120px; height:75px; object-fit:cover; border-radius:8px; border:1px solid #484f58;">
          <div>
            <div style="font-size:0.85rem; color:#c9d1d9; margin-bottom:6px;">Cover saat ini: <code><?= e($post['cover']) ?></code></div>
            <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.82rem; color:#f87171; cursor:pointer;">
              <input type="checkbox" name="remove_cover" value="1"> Hapus cover ini
            </label>
          </div>
        </div>
      <?php endif; ?>
      <input class="admin-input" type="file" name="cover" accept="image/jpeg,image/png,image/webp">
    </label>

    <label>
      Ringkasan Singkat (Excerpt)
      <textarea class="admin-input" name="excerpt" placeholder="Deskripsi ringkas yang tampil di kartu blog..."><?= e($post['excerpt'] ?? '') ?></textarea>
    </label>

    <label>
      Konten / Isi Artikel
      <textarea class="admin-input" name="content" style="min-height:280px;" placeholder="Tuliskan isi artikel Anda di sini..."><?= e($post['content'] ?? '') ?></textarea>
    </label>

    <label>
      Status Publikasi
      <select class="admin-input" name="status">
        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (Hanya tersimpan di admin)</option>
        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Tampil di website publik)</option>
      </select>
    </label>

    <div style="display:flex; gap:10px; margin-top:8px;">
      <button class="admin-btn" type="submit"><?= $id ? 'Simpan Perubahan' : 'Publikasikan / Simpan' ?></button>
      <a class="admin-btn secondary" href="posts.php">Kembali ke Daftar Blog</a>
      <?php if ($id && ($post['status'] ?? '') === 'published'): ?>
        <a class="admin-btn secondary" href="../post.php?slug=<?= urlencode($post['slug']) ?>" target="_blank">Lihat di Website ↗</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
