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

$name = setting($pdo, 'name', 'Elgi Listian');
$role = setting($pdo, 'role', 'Informatics Engineering · Full-Stack Systems Developer');
$location = setting($pdo, 'location', 'Indonesia');
$accent = setting($pdo, 'accent_color', '#00f0ff');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
    $accent = '#00f0ff';
}

function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
$accentRgb = hexToRgb($accent);

$wordCount = $post ? str_word_count(strip_tags($post['content'] ?? '')) : 0;
$readMinutes = max(1, (int)ceil($wordCount / 180));

$cssVer = file_exists(__DIR__ . '/assets/style.css') ? filemtime(__DIR__ . '/assets/style.css') : time();
$jsVer = file_exists(__DIR__ . '/assets/main.js') ? filemtime(__DIR__ . '/assets/main.js') : time();
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $post ? e($post['title']) . ' — ' . e($name) : 'Dossier Tidak Ditemukan — ' . e($name) ?></title>
  <?php if ($post && !empty($post['excerpt'])): ?>
    <meta name="description" content="<?= e($post['excerpt']) ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/style.css?v=<?= $cssVer ?>">
  <style>
    :root {
      --accent: <?= e($accent) ?>;
      --accent-rgb: <?= $accentRgb ?>;
      --accent-gradient: linear-gradient(135deg, <?= e($accent) ?> 0%, #6366f1 55%, #a855f7 100%);
      --accent-glow: rgba(<?= $accentRgb ?>, 0.3);
    }
    .article-container {
      max-width: 840px;
      margin: 54px auto 90px;
      padding: 0 20px;
    }
    .article-dossier-card {
      padding: 38px;
      box-shadow: var(--shadow-3d-edge);
    }
    .article-meta-bar {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 16px;
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: var(--accent);
      flex-wrap: wrap;
    }
    .article-title {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: clamp(2rem, 4.5vw, 3.2rem);
      line-height: 1.12;
      letter-spacing: -0.035em;
      color: var(--text-main);
      margin-bottom: 16px;
    }
    .article-lead {
      font-size: 1.12rem;
      color: var(--text-muted);
      line-height: 1.68;
      padding-left: 14px;
      border-left: 3px solid var(--accent);
      margin-bottom: 26px;
    }
    .article-cover {
      width: 100%;
      border-radius: var(--radius-md);
      overflow: hidden;
      margin: 28px 0;
      border: 1px solid var(--border-card);
    }
    .article-cover img {
      width: 100%;
      max-height: 460px;
      object-fit: cover;
    }
    .article-content {
      font-size: 1.05rem;
      line-height: 1.82;
      color: var(--text-main);
      white-space: pre-line;
    }
    .article-share {
      margin-top: 44px;
      padding-top: 24px;
      border-top: 1px solid var(--border-subtle);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    @media (max-width: 768px) {
      .article-dossier-card { padding: 24px 18px; }
    }
  </style>
</head>
<body>

<div class="cursor-dot" id="cursor-dot"></div>
<div class="cursor-ring" id="cursor-ring"></div>
<div id="scroll-progress"></div>

<div class="ambient-glow" aria-hidden="true">
  <canvas id="cyber-canvas"></canvas>
  <div class="spatial-floor-grid"></div>
  <div class="glow-orb-1"></div>
  <div class="glow-orb-2"></div>
</div>

<header class="site-header">
  <div class="wrap site-nav">
    <a class="brand" href="index.php">
      <span class="brand-cube">3D</span>
      <span><?= e($name) ?><span class="dot">_OS</span></span>
    </a>

    <div class="hud-telemetry">
      <span class="hud-metric"><span class="status-dot"></span><span>DOSSIER_READER</span></span>
      <span class="hud-divider"></span>
      <span class="hud-metric">XY: <strong id="hud-coords">000, 000</strong></span>
      <span class="hud-divider"></span>
      <span class="hud-metric"><strong id="live-clock">--:--:--</strong></span>
    </div>

    <div class="nav-actions">
      <button type="button" id="sfx-toggle" class="btn-icon sfx-active" title="Toggle UI Sound Effects">SFX</button>
      <button type="button" id="theme-toggle" class="btn-icon" aria-label="Ganti mode tema">
        <svg class="icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
        </svg>
        <svg class="icon-moon" width="17" height="17" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
      <a class="btn primary sm" href="index.php#contact">TRANSMIT ↗</a>
    </div>
  </div>
</header>

<div class="spatial-dock-wrap">
  <nav class="spatial-dock">
    <a class="dock-item" href="index.php#home"><span>~/stage</span></a>
    <a class="dock-item" href="index.php#about"><span>./blueprint</span></a>
    <a class="dock-item" href="index.php#projects"><span>./works</span></a>
    <a class="dock-item" href="index.php#skills"><span>./keycaps</span></a>
    <a class="dock-item active" href="index.php#blog"><span>./logs</span></a>
    <a class="dock-item" href="index.php#contact"><span>./transmit</span></a>
  </nav>
</div>

<main class="article-container">
  <?php if (!$post): ?>
    <div class="empty" style="padding:60px 20px;">
      <h1 style="font-size:1.8rem; margin-bottom:12px; color:var(--text-main);">404 // DOSSIER_NOT_FOUND</h1>
      <p style="margin-bottom:24px;">Catatan teknis yang Anda cari tidak ditemukan atau telah diarsipkan.</p>
      <a class="btn primary" href="index.php#blog">← KEMBALI KE LOGS</a>
    </div>
  <?php else: ?>
    <article class="article-dossier-card spotlight-card">
      <span class="tech-corner tl"></span><span class="tech-corner br"></span>
      <a class="btn sm" href="index.php#blog" style="margin-bottom:24px;">← BACK_TO_LOGS</a>

      <div class="article-meta-bar">
        <span>DATE // <?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></span>
        <span>·</span>
        <span>EST_READ // <?= $readMinutes ?> MIN</span>
        <span>·</span>
        <span>AUTHOR // <?= e(strtoupper($name)) ?></span>
      </div>

      <h1 class="article-title"><?= e($post['title']) ?></h1>

      <?php if (!empty($post['excerpt'])): ?>
        <p class="article-lead"><?= e($post['excerpt']) ?></p>
      <?php endif; ?>

      <?php if (!empty($post['cover']) && file_exists(__DIR__ . '/uploads/' . basename($post['cover']))): ?>
        <div class="article-cover">
          <img src="uploads/<?= e(basename($post['cover'])) ?>" alt="<?= e($post['title']) ?>">
        </div>
      <?php endif; ?>

      <div class="article-content"><?= e($post['content']) ?></div>

      <div class="article-share">
        <a class="btn" href="index.php#blog">← INDEX_LOGS</a>
        <button type="button" class="btn btn-copy-email" data-email="<?= e((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '')) ?>">
          COPY_DOSSIER_URL 🔗
        </button>
      </div>
    </article>
  <?php endif; ?>
</main>

<div id="toast-container"></div>
<script src="assets/main.js?v=<?= $jsVer ?>"></script>
</body>
</html>
