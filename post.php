<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
}

$name = setting($pdo, 'name', 'Portfolio');
$role = setting($pdo, 'role', 'Web Developer');
$accent = setting($pdo, 'accent_color', '#5b6cff');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
    $accent = '#5b6cff';
}

function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
$accentRgb = hexToRgb($accent);

// Estimated reading time calculation
$wordCount = $post ? str_word_count(strip_tags($post['content'] ?? '')) : 0;
$readMinutes = max(1, (int)ceil($wordCount / 180));
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $post ? e($post['title']) . ' — ' . e($name) : 'Artikel Tidak Ditemukan — ' . e($name) ?></title>
  <?php if ($post && !empty($post['excerpt'])): ?>
    <meta name="description" content="<?= e($post['excerpt']) ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/style.css?v=<?= file_exists(__DIR__ . '/assets/style.css') ? filemtime(__DIR__ . '/assets/style.css') : time() ?>">
  <style>
    :root {
      --accent: <?= e($accent) ?>;
      --accent-rgb: <?= $accentRgb ?>;
      --accent-gradient: linear-gradient(135deg, <?= e($accent) ?> 0%, #8b5cf6 100%);
      --accent-glow: rgba(<?= $accentRgb ?>, 0.28);
    }
    .mobile-nav-drawer { display: none; }
    .mobile-nav-backdrop { display: none; }
    .btn-icon svg { width: 18px !important; height: 18px !important; }
    .btn svg { width: 15px !important; height: 15px !important; }
    .article-container {
      max-width: 820px;
      margin: 60px auto 100px;
      padding: 0 20px;
    }
    .article-header {
      margin-bottom: 40px;
    }
    .article-meta-bar {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 18px;
      font-size: 0.88rem;
      color: var(--text-sub);
      flex-wrap: wrap;
    }
    .article-title {
      font-family: var(--font-heading);
      font-weight: 800;
      font-size: clamp(2.3rem, 5vw, 3.6rem);
      line-height: 1.15;
      letter-spacing: -0.04em;
      color: var(--text-main);
      margin-bottom: 18px;
    }
    .article-lead {
      font-size: 1.18rem;
      color: var(--text-muted);
      line-height: 1.65;
    }
    .article-cover {
      width: 100%;
      border-radius: var(--radius-lg);
      overflow: hidden;
      margin: 35px 0;
      border: 1px solid var(--border-card);
      box-shadow: var(--shadow-lg);
    }
    .article-cover img {
      width: 100%;
      max-height: 480px;
      object-fit: cover;
    }
    .article-content {
      font-size: 1.1rem;
      line-height: 1.8;
      color: var(--text-main);
      white-space: pre-line;
      letter-spacing: -0.01em;
    }
    .article-share {
      margin-top: 55px;
      padding-top: 30px;
      border-top: 1px solid var(--border-subtle);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>

<div id="scroll-progress"></div>

<div class="ambient-glow" aria-hidden="true">
  <div class="glow-orb-1"></div>
  <div class="glow-orb-2"></div>
  <div class="bg-grid-overlay"></div>
</div>

<!-- Header -->
<header class="site-header">
  <div class="wrap site-nav">
    <a class="brand" href="index.php">
      <span class="brand-badge"><?= e(mb_substr($name, 0, 1)) ?></span>
      <span><?= e($name) ?><span class="dot">.</span></span>
    </a>

    <nav class="nav-links">
      <a class="nav-link" href="index.php#home">Beranda</a>
      <a class="nav-link" href="index.php#about">Tentang</a>
      <a class="nav-link" href="index.php#projects">Project</a>
      <a class="nav-link" href="index.php#skills">Skills</a>
      <a class="nav-link active" href="index.php#blog">Blog</a>
      <a class="nav-link" href="index.php#contact">Kontak</a>
    </nav>

    <div class="nav-actions">
      <button id="theme-toggle" class="btn-icon" aria-label="Ganti mode tema">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <svg class="icon-moon" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>

      <a class="btn primary sm" href="index.php#contact">
        Hubungi Saya
      </a>

      <button class="nav-toggle" aria-label="Menu navigasi">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav-backdrop"></div>
<div class="mobile-nav-drawer">
  <div class="mobile-links">
    <a class="mobile-link" href="index.php#home">Beranda <span>→</span></a>
    <a class="mobile-link" href="index.php#about">Tentang <span>→</span></a>
    <a class="mobile-link" href="index.php#projects">Project <span>→</span></a>
    <a class="mobile-link" href="index.php#skills">Skills <span>→</span></a>
    <a class="mobile-link active" href="index.php#blog">Blog <span>→</span></a>
    <a class="mobile-link" href="index.php#contact">Kontak <span>→</span></a>
  </div>
</div>

<main class="article-container">
  <?php if (!$post): ?>
    <div class="empty" style="padding:60px 20px;">
      <h1 style="font-size:2rem; margin-bottom:12px; color:var(--text-main);">Artikel Tidak Ditemukan</h1>
      <p style="margin-bottom:24px;">Artikel yang Anda cari mungkin telah dihapus atau URL tidak valid.</p>
      <a class="btn primary" href="index.php#blog">← Kembali ke Halaman Blog</a>
    </div>
  <?php else: ?>
    <article>
      <div class="article-header">
        <a class="btn sm" href="index.php#blog" style="margin-bottom:24px; display:inline-flex;">
          ← Kembali ke Blog
        </a>

        <div class="article-meta-bar">
          <span>📅 <?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></span>
          <span>·</span>
          <span>⏱️ <?= $readMinutes ?> menit baca</span>
          <span>·</span>
          <span>✍️ <?= e($name) ?></span>
        </div>

        <h1 class="article-title"><?= e($post['title']) ?></h1>

        <?php if (!empty($post['excerpt'])): ?>
          <p class="article-lead"><?= e($post['excerpt']) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($post['cover']) && file_exists(__DIR__ . '/uploads/' . basename($post['cover']))): ?>
        <div class="article-cover">
          <img src="uploads/<?= e(basename($post['cover'])) ?>" alt="<?= e($post['title']) ?>">
        </div>
      <?php endif; ?>

      <div class="article-content">
        <?= e($post['content']) ?>
      </div>

      <div class="article-share">
        <a class="btn" href="index.php#blog">← Kembali ke Artikel Lainnya</a>
        <button class="btn btn-copy-email" data-email="<?= e($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
          🔗 Salin Tautan Artikel
        </button>
      </div>
    </article>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <div class="wrap footer-bottom" style="border:none; padding:0;">
    <span>© <?= date('Y') ?> <?= e($name) ?>. Hak cipta dilindungi.</span>
    <a class="footer-admin-link" href="admin/login.php">Panel Admin ↗</a>
  </div>
</footer>

<div id="toast-container"></div>
<script src="assets/main.js"></script>
</body>
</html>
