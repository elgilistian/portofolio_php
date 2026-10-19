<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

// Fetch database records
$projects = $pdo->query('SELECT * FROM projects ORDER BY featured DESC, created_at DESC')->fetchAll();
$skills = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, name ASC')->fetchAll();
$posts = $pdo->query("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC, created_at DESC LIMIT 3")->fetchAll();

// Handle Contact Form Submission
$sent = false;
$formError = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        $formError = 'Mohon isi nama, email yang valid, dan pesan Anda.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);
        $sent = true;
    }
}

// Site settings with defaults
$name = setting($pdo, 'name', 'Elgi Listian');
$role = setting($pdo, 'role', 'Informatics Engineering Student · Web Developer');
$profile = setting($pdo, 'profile_image', '');
$accent = setting($pdo, 'accent_color', '#5b6cff');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
    $accent = '#5b6cff';
}

$heroTitle = setting($pdo, 'hero_title', 'Building useful things for the web.');
$heroDesc = setting($pdo, 'hero_description', 'Saya membangun pengalaman digital yang sederhana, fungsional, dan mudah digunakan.');
$aboutTitle = setting($pdo, 'about_title', 'Tentang saya');
$aboutText = setting($pdo, 'about_text', 'Saya adalah mahasiswa Teknik Informatika yang tertarik pada pengembangan web, pemecahan masalah, dan membangun produk digital yang bermanfaat.');
$email = setting($pdo, 'email', 'hello@example.com');
$location = setting($pdo, 'location', 'Indonesia');
$github = setting($pdo, 'github', 'https://github.com/');
$instagram = setting($pdo, 'instagram', 'https://instagram.com/');
$linkedin = setting($pdo, 'linkedin', 'https://linkedin.com/');
$availability = setting($pdo, 'availability', 'Terbuka untuk kolaborasi');
$footerText = setting($pdo, 'footer_text', 'Dibuat dengan dedikasi dan rasa ingin tahu.');

// Hex to RGB conversion for CSS alpha variables
function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
$accentRgb = hexToRgb($accent);

// Project categories
$categories = [];
foreach ($projects as $p) {
    $cat = trim($p['category'] ?? '');
    if ($cat !== '' && !in_array($cat, $categories, true)) {
        $categories[] = $cat;
    }
}

// Typewriter words
$roleParts = array_filter(array_map('trim', preg_split('/[·,|]/', $role) ?: []));
if (empty($roleParts)) {
    $roleParts = ['Web Developer', 'Teknik Informatika', 'Full-Stack Explorer'];
}
$typewriterJson = json_encode(array_values($roleParts), JSON_UNESCAPED_UNICODE);

// SVG Helper with strict dimensions to prevent icon blowout
function getSkillIcon(string $name): string {
    $n = strtolower(trim($name));
    if (str_contains($n, 'php')) {
        return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 9a3 3 0 0 1 3 3v0a3 3 0 0 1-3 3H7V9h3z"/><path d="M7 12h3"/><path d="M19 9a3 3 0 0 1 3 3v0a3 3 0 0 1-3 3h-3V9h3z"/><path d="M16 12h3"/><path d="M2 12h2"/><path d="M5 12h2"/></svg>';
    }
    if (str_contains($n, 'sql') || str_contains($n, 'data') || str_contains($n, 'base')) {
        return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>';
    }
    if (str_contains($n, 'html') || str_contains($n, 'css')) {
        return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
    }
    if (str_contains($n, 'js') || str_contains($n, 'javascript') || str_contains($n, 'script')) {
        return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M16 8v8a2 2 0 0 1-2 2h-1"/><path d="M8 14c.5 1 1.5 1.5 2.5 1.5s2-.5 2-1.5-1-1.5-2-1.5-2-.5-2-1.5 1-1.5 2-1.5 2 .5 2.5 1.5"/></svg>';
    }
    if (str_contains($n, 'git')) {
        return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="3" x2="6" y2="15"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 0 1-9 9"/></svg>';
    }
    return '<svg width="20" height="20" style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>';
}

$cssVer = file_exists(__DIR__ . '/assets/style.css') ? filemtime(__DIR__ . '/assets/style.css') : time();
$jsVer = file_exists(__DIR__ . '/assets/main.js') ? filemtime(__DIR__ . '/assets/main.js') : time();
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($name) ?> — Portfolio Personal</title>
  <meta name="description" content="<?= e($heroDesc) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="assets/style.css?v=<?= $cssVer ?>">
  <style>
    :root {
      --accent: <?= e($accent) ?>;
      --accent-rgb: <?= $accentRgb ?>;
      --accent-gradient: linear-gradient(135deg, <?= e($accent) ?> 0%, #8b5cf6 100%);
      --accent-glow: rgba(<?= $accentRgb ?>, 0.25);
    }
    /* Critical Inline Protection: Ensures zero layout collision & zero icon expansion */
    .mobile-nav-drawer { display: none; }
    .mobile-nav-backdrop { display: none; }
    .social-pills { display: flex; align-items: center; gap: 10px; }
    .social-pill { width: 40px !important; height: 40px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; }
    .social-pill svg { width: 18px !important; height: 18px !important; }
    .bento-icon svg { width: 22px !important; height: 22px !important; }
    .contact-card-icon svg { width: 18px !important; height: 18px !important; }
    .btn-icon svg { width: 18px !important; height: 18px !important; }
    .btn svg { width: 15px !important; height: 15px !important; }
  </style>
</head>
<body>

<!-- Scroll Progress Bar -->
<div id="scroll-progress"></div>

<!-- Ambient Soft Lighting -->
<div class="ambient-glow" aria-hidden="true">
  <div class="glow-orb-1"></div>
  <div class="glow-orb-2"></div>
  <div class="bg-grid-overlay"></div>
</div>

<!-- ==========================================================================
     HEADER & NAVIGATION
     ========================================================================== -->
<header class="site-header">
  <div class="wrap site-nav">
    <!-- Brand -->
    <a class="brand" href="#home">
      <span class="brand-badge"><?= e(mb_substr($name, 0, 1)) ?></span>
      <span><?= e($name) ?><span class="dot">.</span></span>
    </a>

    <!-- Desktop Navigation Links -->
    <nav class="nav-links">
      <a class="nav-link active" href="#home">Beranda</a>
      <a class="nav-link" href="#about">Tentang</a>
      <a class="nav-link" href="#projects">Project</a>
      <a class="nav-link" href="#skills">Skills</a>
      <a class="nav-link" href="#blog">Blog</a>
      <a class="nav-link" href="#contact">Kontak</a>
    </nav>

    <!-- Header Actions -->
    <div class="nav-actions">
      <!-- Dark/Light Theme Switcher -->
      <button id="theme-toggle" class="btn-icon" aria-label="Ganti mode tema">
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
        <svg class="icon-moon" width="18" height="18" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>

      <!-- CTA -->
      <a class="btn primary sm" href="#contact">Hubungi Saya</a>

      <!-- Mobile Hamburger Toggle -->
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
<div class="mobile-nav-drawer" id="mobile-drawer">
  <div class="mobile-links">
    <a class="mobile-link" href="#home">Beranda <span>→</span></a>
    <a class="mobile-link" href="#about">Tentang <span>→</span></a>
    <a class="mobile-link" href="#projects">Project <span>→</span></a>
    <a class="mobile-link" href="#skills">Skills <span>→</span></a>
    <a class="mobile-link" href="#blog">Blog <span>→</span></a>
    <a class="mobile-link" href="#contact">Kontak <span>→</span></a>
  </div>
  <div>
    <a class="btn primary" href="#contact" style="width:100%;">Mari Berdiskusi 🚀</a>
  </div>
</div>

<main>
  <!-- ==========================================================================
       HERO SECTION
       ========================================================================== -->
  <section class="wrap hero" id="home">
    <div class="hero-grid">
      <!-- Left: Headline & Actions -->
      <div>
        <div class="status-pill">
          <span class="status-dot"></span>
          <span><?= e($availability) ?></span>
        </div>

        <h1><?= nl2br(e($heroTitle)) ?></h1>

        <div class="hero-subtitle">
          <span>Saya adalah</span>
          <span class="typewriter-word" id="typewriter-text" data-words="<?= e($typewriterJson) ?>">Web Developer</span>
        </div>

        <p class="hero-description"><?= e($heroDesc) ?></p>

        <div class="hero-actions">
          <a class="btn primary" href="#projects">
            Lihat Project
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="7" y1="17" x2="17" y2="7"></line>
              <polyline points="7 7 17 7 17 17"></polyline>
            </svg>
          </a>
          <button class="btn btn-copy-email" data-email="<?= e($email) ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            Salin Email
          </button>
        </div>

        <!-- Social Icons Row -->
        <div class="social-pills">
          <?php if ($github): ?>
          <a class="social-pill" href="<?= e($github) ?>" target="_blank" rel="noopener" aria-label="GitHub">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
            </svg>
          </a>
          <?php endif; ?>

          <?php if ($linkedin): ?>
          <a class="social-pill" href="<?= e($linkedin) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
              <rect x="2" y="9" width="4" height="12"></rect>
              <circle cx="4" cy="4" r="2"></circle>
            </svg>
          </a>
          <?php endif; ?>

          <?php if ($instagram): ?>
          <a class="social-pill" href="<?= e($instagram) ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
            </svg>
          </a>
          <?php endif; ?>

          <?php if ($email): ?>
          <a class="social-pill" href="mailto:<?= e($email) ?>" aria-label="Kirim Email">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Photo Card -->
      <div class="hero-visual">
        <div class="portrait-wrapper tilt-card">
          <div class="floating-chip chip-top-right">
            🎓 Teknik Informatika
          </div>

          <div class="portrait-card">
            <?php if ($profile && file_exists(__DIR__ . '/uploads/' . basename($profile))): ?>
              <img src="uploads/<?= e(basename($profile)) ?>" alt="Foto profil <?= e($name) ?>">
            <?php else: ?>
              <div class="portrait-fallback">
                <div class="portrait-fallback-avatar"><?= e(mb_substr($name, 0, 1)) ?></div>
                <strong style="color:var(--text-main);"><?= e($name) ?></strong>
                <p style="font-size:0.84rem;"><?= e($role) ?></p>
              </div>
            <?php endif; ?>
          </div>

          <div class="floating-chip chip-bottom-left">
            ⚡ Web & DB Dev
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Bar -->
    <div class="hero-stats">
      <div class="stat-item">
        <div class="stat-number"><?= count($projects) ?><span class="accent">+</span></div>
        <div class="stat-label">Project Selesai</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= count($skills) ?><span class="accent">+</span></div>
        <div class="stat-label">Keahlian & Tools</div>
      </div>
      <div class="stat-item">
        <div class="stat-number">100<span class="accent">%</span></div>
        <div class="stat-label">Dedikasi & Passion</div>
      </div>
      <div class="stat-item">
        <div class="stat-number" style="font-size:1.3rem;">🇮🇩 <span style="font-size:1rem; color:var(--text-main);"><?= e($location) ?></span></div>
        <div class="stat-label">Basis Lokasi</div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       ABOUT SECTION
       ========================================================================== -->
  <section class="section" id="about">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">01 / Profile</div>
        <h2 class="section-title"><?= e($aboutTitle) ?></h2>
        <p class="section-lead">Mengenal lebih dekat perjalanan saya di dunia teknologi dan rekayasa web.</p>
      </div>

      <div class="bento-grid">
        <div class="bento-card bento-main">
          <div class="bento-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <h3>Passion dalam Web Engineering</h3>
          <p><?= nl2br(e($aboutText)) ?></p>
          <ul class="bento-list">
            <li>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
              Fokus pada kode bersih (clean code), modularitas, dan efisiensi query database.
            </li>
            <li>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
              Pengalaman membangun arsitektur PHP native yang terstruktur, aman, dan responsif.
            </li>
          </ul>
        </div>

        <div class="bento-card bento-side">
          <div class="bento-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <h3>Status & Waktu Saat Ini</h3>
          <p>
            Berbasis di <strong><?= e($location) ?></strong>. Selalu terbuka untuk peluang kolaborasi proyek dan magang.
          </p>
          <div class="live-badge">
            <span class="status-dot"></span>
            <span id="live-clock">--:--:-- WIB</span>
          </div>
          <div style="margin-top:16px; font-size:0.86rem; color:var(--text-muted);">
            Email: <strong style="color:var(--text-main); font-family:var(--font-mono);"><?= e($email) ?></strong>
          </div>
        </div>

        <div class="bento-card bento-half">
          <div class="bento-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
              <polyline points="2 17 12 22 22 17"></polyline>
              <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
          </div>
          <h3>Arsitektur & Pendekatan</h3>
          <p>
            Memadukan pemahaman fundamental logika yang kuat dengan antarmuka yang ramah pengguna di berbagai perangkat.
          </p>
        </div>

        <div class="bento-card bento-half">
          <div class="bento-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </div>
          <h3>Evolusi Berkelanjutan</h3>
          <p>
            Terus memperdalam pemahaman teknologi web modern dan menerapkan standar industri terbaik.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       PROJECTS SECTION
       ========================================================================== -->
  <section class="section" id="projects">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">02 / Selected Works</div>
        <h2 class="section-title">Project & Karya Pilihan</h2>
        <p class="section-lead">Aplikasi dan solusi digital yang telah saya bangun.</p>
      </div>

      <div class="projects-filter-bar">
        <div class="filter-tabs">
          <button class="filter-tab active" data-filter="all">Semua (<?= count($projects) ?>)</button>
          <?php foreach ($categories as $cat): ?>
            <button class="filter-tab" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
          <?php endforeach; ?>
        </div>

        <div class="project-search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="project-search-input" placeholder="Cari project..." aria-label="Cari project">
        </div>
      </div>

      <?php if (!$projects): ?>
        <div class="empty">Belum ada project yang ditambahkan di admin.</div>
      <?php else: ?>
        <div class="projects-grid">
          <?php foreach ($projects as $project): 
            $projectJson = htmlspecialchars(json_encode([
                'title' => $project['title'],
                'category' => $project['category'],
                'description' => $project['description'],
                'image' => $project['image'] ? 'uploads/' . basename($project['image']) : '',
                'demo' => $project['demo_url'],
                'repo' => $project['repo_url'],
                'featured' => (bool)$project['featured']
            ]), ENT_QUOTES, 'UTF-8');
          ?>
          <article class="project-card <?= $project['featured'] ? 'featured' : '' ?>" data-category="<?= e($project['category']) ?>">
            <?php if ($project['featured']): ?>
              <div class="featured-badge">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                Unggulan
              </div>
            <?php endif; ?>

            <div class="project-preview">
              <?php if (!empty($project['image']) && file_exists(__DIR__ . '/uploads/' . basename($project['image']))): ?>
                <img src="uploads/<?= e(basename($project['image'])) ?>" alt="Preview <?= e($project['title']) ?>">
              <?php else: ?>
                <div class="project-mockup">
                  <div class="mockup-header">
                    <span class="mockup-dot red"></span>
                    <span class="mockup-dot yellow"></span>
                    <span class="mockup-dot green"></span>
                    <span class="mockup-url">https://<?= e(slugify($project['title'])) ?>.app</span>
                  </div>
                  <div class="mockup-canvas">
                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                      <line x1="8" y1="21" x2="16" y2="21"></line>
                      <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <span><?= e($project['category']) ?></span>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="project-body">
              <span class="project-category"><?= e($project['category']) ?></span>
              <h3 class="project-title"><?= e($project['title']) ?></h3>
              <p class="project-desc"><?= e($project['description']) ?></p>

              <div class="project-actions">
                <?php if (!empty($project['demo_url'])): ?>
                  <a class="btn primary sm" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">
                    Live Demo ↗
                  </a>
                <?php endif; ?>

                <?php if (!empty($project['repo_url'])): ?>
                  <a class="btn sm" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener">
                    Source Code ↗
                  </a>
                <?php endif; ?>

                <button type="button" class="btn sm btn-ghost btn-project-detail" data-project="<?= $projectJson ?>">
                  Detail 🔍
                </button>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <div id="projects-empty-filter" class="empty" style="display:none; margin-top:16px;">
          Tidak ada project yang sesuai dengan filter atau pencarian.
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       SKILLS SECTION
       ========================================================================== -->
  <section class="section" id="skills">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">03 / Toolkit</div>
        <h2 class="section-title">Keahlian & Teknologi</h2>
        <p class="section-lead">Teknologi dan instrumen yang saya pakai dalam mengembangkan produk digital.</p>
      </div>

      <?php if (!$skills): ?>
        <div class="empty">Skills belum ditambahkan di admin.</div>
      <?php else: ?>
        <div class="skills-grid">
          <?php foreach ($skills as $skill): ?>
          <div class="skill-card">
            <div class="skill-top">
              <div class="skill-meta">
                <div class="skill-icon-wrapper">
                  <?= getSkillIcon($skill['name']) ?>
                </div>
                <div>
                  <div class="skill-name"><?= e($skill['name']) ?></div>
                  <div class="skill-cat"><?= e($skill['category'] ?: 'Tools') ?></div>
                </div>
              </div>
              <div class="skill-pct"><?= (int)$skill['level'] ?>%</div>
            </div>

            <div class="skill-bar-bg">
              <div class="skill-bar-fill" data-level="<?= (int)$skill['level'] ?>" style="width: 0%;"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       BLOG SECTION
       ========================================================================== -->
  <section class="section" id="blog">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">04 / Blog & Notes</div>
        <h2 class="section-title">Catatan Terbaru</h2>
        <p class="section-lead">Wawasan, pembelajaran, dan dokumentasi teknis.</p>
      </div>

      <?php if (!$posts): ?>
        <div class="empty">Belum ada artikel yang dipublikasikan.</div>
      <?php else: ?>
        <div class="blog-grid">
          <?php foreach ($posts as $post): ?>
          <article class="post-card">
            <div class="post-image">
              <?php if (!empty($post['cover']) && file_exists(__DIR__ . '/uploads/' . basename($post['cover']))): ?>
                <img src="uploads/<?= e(basename($post['cover'])) ?>" alt="<?= e($post['title']) ?>">
              <?php else: ?>
                <div class="post-fallback">
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                  </svg>
                </div>
              <?php endif; ?>
            </div>

            <div class="post-body">
              <div class="post-date">
                <?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?>
              </div>
              <h3 class="post-title"><?= e($post['title']) ?></h3>
              <p class="post-excerpt"><?= e($post['excerpt']) ?></p>

              <a class="read-link" href="post.php?slug=<?= urlencode($post['slug']) ?>">
                Baca artikel ↗
              </a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       CONTACT SECTION
       ========================================================================== -->
  <section class="section" id="contact">
    <div class="wrap">
      <div class="contact-container">
        <!-- Left: Contact Details -->
        <div class="contact-info">
          <div>
            <div class="section-kicker">05 / Kontak</div>
            <h2>Ada ide menarik?<br>Mari berkolaborasi.</h2>
          </div>
          <p>
            Untuk kolaborasi, project, atau sekadar bertukar pikiran seputar web development.
          </p>

          <div class="contact-card-list">
            <div class="contact-card">
              <div class="contact-card-left">
                <div class="contact-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                </div>
                <div>
                  <div class="contact-card-label">Email</div>
                  <div class="contact-card-val"><?= e($email) ?></div>
                </div>
              </div>
              <button class="btn-copy btn-copy-email" data-email="<?= e($email) ?>">Salin</button>
            </div>

            <div class="contact-card">
              <div class="contact-card-left">
                <div class="contact-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                </div>
                <div>
                  <div class="contact-card-label">Lokasi</div>
                  <div class="contact-card-val"><?= e($location) ?></div>
                </div>
              </div>
              <span class="live-badge" style="margin:0;">WIB (UTC+7)</span>
            </div>
          </div>
        </div>

        <!-- Right: Contact Form -->
        <div>
          <?php if ($sent): ?>
            <div class="notice">
              ✓ Pesan berhasil dikirim! Terima kasih banyak.
            </div>
          <?php elseif ($formError): ?>
            <div class="notice error">
              <?= e($formError) ?>
            </div>
          <?php endif; ?>

          <form class="contact-form" method="post" action="#contact">
            <input type="hidden" name="send_message" value="1">
            
            <div class="form-group">
              <label class="form-label">Nama Anda</label>
              <input class="form-input" name="name" placeholder="Nama lengkap" required maxlength="120">
            </div>

            <div class="form-group">
              <label class="form-label">Email</label>
              <input class="form-input" name="email" type="email" placeholder="email@contoh.com" required maxlength="190">
            </div>

            <div class="form-group">
              <label class="form-label">Subjek</label>
              <input class="form-input" name="subject" placeholder="Topik atau kebutuhan project" maxlength="200">
            </div>

            <div class="form-group">
              <label class="form-label">Pesan</label>
              <textarea class="form-textarea" name="message" placeholder="Ceritakan sedikit tentang kebutuhan Anda..." required></textarea>
              <span class="form-char-count" id="message-char-count">0 karakter</span>
            </div>

            <button class="btn primary" type="submit" style="width:100%; padding:12px;">
              Kirim Pesan ↗
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- ==========================================================================
     FOOTER
     ========================================================================== -->
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="brand" href="#home">
          <span class="brand-badge"><?= e(mb_substr($name, 0, 1)) ?></span>
          <span><?= e($name) ?><span class="dot">.</span></span>
        </a>
        <p><?= e($role) ?></p>
      </div>

      <nav class="footer-nav">
        <a href="#home">Beranda</a>
        <a href="#about">Tentang</a>
        <a href="#projects">Project</a>
        <a href="#skills">Skills</a>
        <a href="#blog">Blog</a>
        <a href="#contact">Kontak</a>
      </nav>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e($name) ?>. <?= e($footerText) ?></span>
      <a class="footer-admin-link" href="admin/login.php">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        Admin ↗
      </a>
    </div>
  </div>
</footer>

<!-- Project Detail Modal -->
<div class="modal-backdrop" id="project-modal">
  <div class="modal-card">
    <div class="modal-header">
      <div>
        <span class="project-category" id="modal-project-category">Kategori</span>
        <h3 class="project-title" id="modal-project-title" style="margin:0;">Judul Project</h3>
      </div>
      <button class="modal-close" aria-label="Tutup modal">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-image" style="display:none;">
        <img id="modal-project-img" src="" alt="Detail Project">
      </div>
      <p id="modal-project-desc" style="color:var(--text-muted); font-size:0.95rem; line-height:1.65;"></p>
      <div style="display:flex; gap:10px; margin-top:8px; flex-wrap:wrap;">
        <a id="modal-project-demo" class="btn primary sm" href="#" target="_blank" rel="noopener">Live Demo ↗</a>
        <a id="modal-project-repo" class="btn sm" href="#" target="_blank" rel="noopener">Source Code ↗</a>
      </div>
    </div>
  </div>
</div>

<!-- Back to Top Button -->
<button class="back-to-top" id="back-to-top" aria-label="Kembali ke atas">↑</button>

<!-- Toast Container -->
<div id="toast-container"></div>

<!-- JavaScript with Cache Buster -->
<script src="assets/main.js?v=<?= $jsVer ?>"></script>
</body>
</html>
