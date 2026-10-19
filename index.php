<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

// Generate CSRF Token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch database records
$projects = $pdo->query('SELECT * FROM projects ORDER BY featured DESC, created_at DESC')->fetchAll();
$skills = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, name ASC')->fetchAll();
$posts = $pdo->query("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC, created_at DESC LIMIT 3")->fetchAll();

// Handle Contact Form Submission with 7-Layer Anti-Spam Shield
$sent = false;
$formError = '';
$oldName = '';
$oldEmail = '';
$oldSubject = '';
$oldMessage = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['send_message'])) {
    $oldName = trim($_POST['name'] ?? '');
    $oldEmail = trim($_POST['email'] ?? '');
    $oldSubject = trim($_POST['subject'] ?? '');
    $oldMessage = trim($_POST['message'] ?? '');

    $csrf = $_POST['csrf_token'] ?? '';
    $honeypot = trim($_POST['website_url_hp'] ?? '');
    $formLoadedAt = (int)($_POST['form_loaded_at'] ?? 0);
    $captchaInput = trim($_POST['spam_answer'] ?? '');
    $expectedCaptcha = (string)($_SESSION['spam_expected'] ?? '');

    $now = time();
    $lastSent = (int)($_SESSION['last_msg_timestamp'] ?? 0);

    if ($honeypot !== '') {
        $formError = 'Bot terdeteksi oleh sistem Honeypot Shield.';
    } elseif (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $formError = 'Token keamanan sesi tidak valid. Silakan muat ulang halaman.';
    } elseif ($formLoadedAt === 0 || ($now - $formLoadedAt) < 2) {
        $formError = 'Pengiriman terlalu cepat (Time-Trap Protection).';
    } elseif (($now - $lastSent) < 60) {
        $waitSec = 60 - ($now - $lastSent);
        $formError = "Cooldown aktif: silakan tunggu {$waitSec} detik sebelum mengirim pesan lagi.";
    } elseif ($expectedCaptcha === '' || $captchaInput !== $expectedCaptcha) {
        $formError = 'Jawaban verifikasi matematika belum tepat.';
    } elseif ($oldName === '' || !filter_var($oldEmail, FILTER_VALIDATE_EMAIL) || $oldMessage === '') {
        $formError = 'Mohon lengkapi nama, email valid, dan isi pesan Anda.';
    } elseif (substr_count(strtolower($oldMessage), 'http') > 2 || preg_match('/(viagra|casino|crypto giveaway|betting|loan|seo service)/i', $oldMessage . ' ' . $oldSubject)) {
        $formError = 'Pesan ditolak oleh filter heuristik anti-spam.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$oldName, $oldEmail, $oldSubject, $oldMessage]);
        $sent = true;
        $_SESSION['last_msg_timestamp'] = $now;
        $oldName = $oldEmail = $oldSubject = $oldMessage = '';
    }
}

// Generate fresh Math Challenge
$mathA = random_int(4, 14);
$mathB = random_int(2, 9);
$_SESSION['spam_expected'] = (string)($mathA + $mathB);
$formRenderTimestamp = time();

// Site settings
$name = setting($pdo, 'name', 'Elgi Listian');
$role = setting($pdo, 'role', 'Informatics Engineering · Full-Stack Systems Developer');
$profile = setting($pdo, 'profile_image', '');
$accent = setting($pdo, 'accent_color', '#00f0ff');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
    $accent = '#00f0ff';
}

$heroTitle = setting($pdo, 'hero_title', 'Architecting spatial, resilient & high-velocity web systems.');
$heroDesc = setting($pdo, 'hero_description', 'Membangun aplikasi web generasi baru dengan perpaduan rekayasa backend yang presisi, struktur database optimal, dan interaksi antarmuka semi-3D yang imersif.');
$aboutTitle = setting($pdo, 'about_title', 'Tentang Diri Saya & Perjalanan Rekayasa');
$aboutSubtitle = setting($pdo, 'about_subtitle', 'Mengenal lebih dekat latar belakang, fokus keahlian, serta riwayat pendidikan dan pengalaman saya.');
$aboutText = setting($pdo, 'about_text', 'Saya adalah mahasiswa Teknik Informatika dan pengembang sistem web yang berfokus pada penulisan kode yang bersih (clean architecture), keamanan berlapis, dan pengalaman pengguna interaktif yang melampaui standar template konvensional.');
$aboutImageRaw = setting($pdo, 'about_image', '');
$aboutPhoto = '';
if ($aboutImageRaw !== '' && file_exists(__DIR__ . '/uploads/' . basename($aboutImageRaw))) {
    $aboutPhoto = 'uploads/' . basename($aboutImageRaw);
} elseif ($profile !== '' && file_exists(__DIR__ . '/uploads/' . basename($profile))) {
    $aboutPhoto = 'uploads/' . basename($profile);
}
$eduSummary = setting($pdo, 'education_summary', 'S1 Teknik Informatika');
$focusSummary = setting($pdo, 'focus_summary', 'Full-Stack Web & System Architecture');
$expYears = setting($pdo, 'experience_years', '3+ Tahun Eksplorasi & Proyek');

$defaultHistory = [
    [
        'period' => '2022 — Sekarang',
        'type' => 'Pendidikan',
        'title' => 'Mahasiswa S1 Teknik Informatika',
        'org' => 'Perguruan Tinggi / Universitas',
        'desc' => 'Mendalami rekayasa perangkat lunak, algoritma & struktur data, perancangan basis data relasional, serta pengembangan sistem web modern.'
    ],
    [
        'period' => '2023 — Sekarang',
        'type' => 'Pengalaman',
        'title' => 'Full-Stack Web Developer (Freelance & Academic Projects)',
        'org' => 'Independent Software Engineering',
        'desc' => 'Merancang dan membangun berbagai aplikasi web berbasis PHP 8, MySQL, dan JavaScript interaktif dengan fokus pada performa, keamanan, dan UI/UX modern.'
    ],
    [
        'period' => '2021 — 2022',
        'type' => 'Pendidikan',
        'title' => 'Eksplorasi Fundamental Pemrograman & Jaringan',
        'org' => 'Self-Taught & Bootcamp',
        'desc' => 'Mempelajari dasar-dasar HTML5, CSS3, JavaScript, serta pengelolaan server lokal dan manajemen kode menggunakan Git.'
    ]
];
$rawHistory = setting($pdo, 'history_json', '');
$historyItems = $rawHistory !== '' ? json_decode($rawHistory, true) : null;
if (!is_array($historyItems) || empty($historyItems)) {
    $historyItems = $defaultHistory;
}
$historyTypes = [];
foreach ($historyItems as $hItem) {
    $hType = trim((string)($hItem['type'] ?? ''));
    if ($hType !== '' && !in_array($hType, $historyTypes, true)) {
        $historyTypes[] = $hType;
    }
}

$email = setting($pdo, 'email', 'hello@example.com');
$location = setting($pdo, 'location', 'Indonesia');
$github = setting($pdo, 'github', 'https://github.com/');
$instagram = setting($pdo, 'instagram', 'https://instagram.com/');
$linkedin = setting($pdo, 'linkedin', 'https://linkedin.com/');
$availability = setting($pdo, 'availability', 'ONLINE // OPEN FOR COLLABORATION');
$footerText = setting($pdo, 'footer_text', 'Crafted with spatial 3D precision & clean code.');

function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
$accentRgb = hexToRgb($accent);

// Categories
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
    $roleParts = ['Full-Stack Engineer', 'Creative Technologist', 'System Architect', 'Database Designer'];
}
$typewriterJson = json_encode(array_values($roleParts), JSON_UNESCAPED_UNICODE);

// CLI Data
$cliSkillsList = array_map(fn($s) => $s['name'] . ' [' . $s['level'] . '%]', array_slice($skills, 0, 6));
$cliProjectsList = array_map(fn($p) => $p['title'] . ' (' . $p['category'] . ')', array_slice($projects, 0, 4));
$cliDataJson = htmlspecialchars(json_encode([
    'name' => $name,
    'role' => $role,
    'location' => $location,
    'email' => $email,
    'availability' => $availability,
    'skills' => $cliSkillsList ?: ['PHP 8+', 'MySQL', 'JavaScript ES6+', 'WebGL / 3D CSS', 'Git'],
    'projects' => $cliProjectsList ?: ['Spatial Portfolio OS', 'Full-Stack Web App']
], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

function getSkillIcon(string $name): string {
    $n = strtolower(trim($name));
    if (str_contains($n, 'php')) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="12" rx="10" ry="6"/><path d="M8 10v4"/><path d="M14 10v4"/></svg>';
    }
    if (str_contains($n, 'sql') || str_contains($n, 'data')) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>';
    }
    if (str_contains($n, 'html') || str_contains($n, 'css')) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
    }
    if (str_contains($n, 'js') || str_contains($n, 'javascript')) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M16 8v8a2 2 0 0 1-2 2h-1"/><path d="M8 14c.5 1 1.5 1.5 2.5 1.5s2-.5 2-1.5-1-1.5-2-1.5-2-.5-2-1.5 1-1.5 2-1.5 2 .5 2.5 1.5"/></svg>';
    }
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>';
}

$cssVer = file_exists(__DIR__ . '/assets/style.css') ? filemtime(__DIR__ . '/assets/style.css') : time();
$jsVer = file_exists(__DIR__ . '/assets/main.js') ? filemtime(__DIR__ . '/assets/main.js') : time();
?>
<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($name) ?> — Interactive 3D Systems Engineer Portfolio</title>
  <meta name="description" content="<?= e($heroDesc) ?>">
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
  </style>
</head>
<body>

<!-- Custom Interactive Precision Cursor -->
<div class="cursor-dot" id="cursor-dot"></div>
<div class="cursor-ring" id="cursor-ring"></div>

<!-- Top Scroll Progress Bar -->
<div id="scroll-progress"></div>

<!-- 3D Spatial Environment & Perspective Floor -->
<div class="ambient-glow" aria-hidden="true">
  <canvas id="cyber-canvas"></canvas>
  <div class="spatial-floor-grid"></div>
  <div class="glow-orb-1"></div>
  <div class="glow-orb-2"></div>
</div>

<!-- ==========================================================================
     TOP TELEMETRY HUD HEADER
     ========================================================================== -->
<header class="site-header">
  <div class="wrap site-nav">
    <!-- 3D Cube Brand -->
    <a class="brand" href="#home">
      <span class="brand-cube">3D</span>
      <span><?= e($name) ?><span class="dot">_OS</span></span>
    </a>

    <!-- Live System Telemetry Bar -->
    <div class="hud-telemetry">
      <span class="hud-metric">
        <span class="status-dot"></span>
        <span>SYS: <strong>ONLINE</strong></span>
      </span>
      <span class="hud-divider"></span>
      <span class="hud-metric">XY: <strong id="hud-coords">000, 000</strong></span>
      <span class="hud-divider"></span>
      <span class="hud-metric">LOC: <strong><?= e(strtoupper($location)) ?> (<span id="live-clock">--:--:--</span>)</strong></span>
    </div>

    <!-- Header Controls -->
    <div class="nav-actions">
      <!-- UI Synth Sound Toggle (Web Audio API) -->
      <button type="button" id="sfx-toggle" class="btn-icon sfx-active" title="Toggle UI Sound Effects" aria-label="Toggle Sound Effects">
        SFX
      </button>

      <!-- Theme Switcher -->
      <button type="button" id="theme-toggle" class="btn-icon" aria-label="Ganti mode tema">
        <svg class="icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
        <svg class="icon-moon" width="17" height="17" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>

      <a class="btn primary sm magnetic-btn" href="#contact">TRANSMIT ↗</a>

      <button class="nav-toggle" aria-label="Menu navigasi">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- FLOATING SPATIAL DOCK (Bottom Center Navigation) -->
<div class="spatial-dock-wrap">
  <nav class="spatial-dock" aria-label="Spatial Dock Navigation">
    <a class="dock-item active" href="#home">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
      <span>~/stage</span>
    </a>
    <a class="dock-item" href="#about">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      <span>./about</span>
    </a>
    <a class="dock-item" href="#projects">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
      <span>./works</span>
    </a>
    <a class="dock-item" href="#skills">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
      <span>./keycaps</span>
    </a>
    <a class="dock-item" href="#blog">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
      <span>./logs</span>
    </a>
    <a class="dock-item" href="#contact">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      <span>./transmit</span>
    </a>
  </nav>
</div>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav-backdrop"></div>
<div class="mobile-nav-drawer" id="mobile-drawer">
  <div class="mobile-links">
    <a class="mobile-link" href="#home">~/stage <span>→</span></a>
    <a class="mobile-link" href="#about">./about <span>→</span></a>
    <a class="mobile-link" href="#projects">./works <span>→</span></a>
    <a class="mobile-link" href="#skills">./keycaps <span>→</span></a>
    <a class="mobile-link" href="#blog">./logs <span>→</span></a>
    <a class="mobile-link" href="#contact">./transmit <span>→</span></a>
  </div>
  <div>
    <a class="btn primary" href="#contact" style="width:100%;">Initiate Transmission ⚡</a>
  </div>
</div>

<main>
  <!-- ==========================================================================
       HERO SECTION: SPATIAL 3D STAGE, INTERACTIVE WIREFRAME CORE & CLI
       ========================================================================== -->
  <section class="wrap hero" id="home">
    <div class="hero-grid">
      <!-- Left Column: Editorial & Technical Statement -->
      <div>
        <div class="hero-telemetry">
          <div class="status-pill">
            <span><?= e($availability) ?></span>
          </div>
        </div>

        <h1>
          <?= nl2br(e($heroTitle)) ?>
        </h1>

        <div class="hero-subtitle">
          <span class="typewriter-prefix">sys.role =&gt;</span>
          <span class="typewriter-word" id="typewriter-text" data-words="<?= e($typewriterJson) ?>">"Full-Stack Developer"</span>
        </div>

        <p class="hero-description"><?= e($heroDesc) ?></p>

        <div class="hero-actions">
          <a class="btn primary magnetic-btn" href="#projects">
            LAUNCH_WORKS()
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="7" y1="17" x2="17" y2="7"></line>
              <polyline points="7 7 17 7 17 17"></polyline>
            </svg>
          </a>
          <button type="button" class="btn magnetic-btn" id="btn-open-profile">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            PROFIL_SAYA()
          </button>
        </div>

        <!-- Social Tactile Buttons -->
        <div class="social-pills">
          <?php if ($github): ?>
          <a class="social-pill" href="<?= e($github) ?>" target="_blank" rel="noopener" aria-label="GitHub">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
            </svg>
          </a>
          <?php endif; ?>

          <?php if ($linkedin): ?>
          <a class="social-pill" href="<?= e($linkedin) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
              <rect x="2" y="9" width="4" height="12"></rect>
              <circle cx="4" cy="4" r="2"></circle>
            </svg>
          </a>
          <?php endif; ?>

          <?php if ($instagram): ?>
          <a class="social-pill" href="<?= e($instagram) ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
            </svg>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Column: Interactive Developer CLI Terminal -->
      <div class="hero-visual">
        <div class="dev-terminal tilt-card spotlight-card" id="hero-terminal" data-cli="<?= $cliDataJson ?>">
          <span class="tech-corner tl"></span><span class="tech-corner tr"></span>
          <span class="tech-corner bl"></span><span class="tech-corner br"></span>

          <div class="terminal-header">
            <div class="terminal-dots">
              <span class="rig-dot r"></span>
              <span class="rig-dot y"></span>
              <span class="rig-dot g"></span>
            </div>
            <div class="terminal-title">zsh — <?= e(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) ?: 'dev') ?>@workstation:~</div>
            <span class="terminal-badge">INTERACTIVE SHELL</span>
          </div>

          <div class="terminal-body" id="terminal-output" style="min-height:240px; max-height:290px;">
            <div class="term-line">
              <span class="term-prompt">➜</span>
              <span class="term-cmd">./boot_system.sh --env=production</span>
            </div>
            <div class="term-output">
              [OK] Kernel: PHP <?= PHP_VERSION ?> · Spatial UI Engine Ready<br>
              [OK] Developer: <strong><?= e($name) ?></strong> (<?= e($location) ?>)<br>
              [OK] Loaded <?= count($projects) ?> Projects &amp; <?= count($skills) ?> Tech Modules.<br>
              <span style="color:var(--accent)">Ketik perintah di bawah atau klik tombol pintasan:</span>
            </div>
          </div>

          <form class="term-input-row" id="terminal-form" autocomplete="off">
            <span class="term-prompt" style="padding-left:14px;">➜</span>
            <input type="text" class="term-input" id="terminal-input" placeholder="Ketik 'help', 'profile', 'skills', 'projects', 'clear'..." aria-label="Perintah Terminal">
          </form>

          <div class="term-quick-cmds">
            <button type="button" class="term-chip" data-cmd="help">help</button>
            <button type="button" class="term-chip" data-cmd="profile">profile</button>
            <button type="button" class="term-chip" data-cmd="skills">skills</button>
            <button type="button" class="term-chip" data-cmd="projects">projects</button>
            <button type="button" class="term-chip" data-cmd="theme">theme</button>
            <button type="button" class="term-chip" data-cmd="clear">clear</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       SLANTED 3D KINETIC MARQUEE RIBBON
       ========================================================================== -->
  <div class="kinetic-marquee-section" aria-hidden="true">
    <div class="marquee-band">
      <div class="marquee-track">
        <span class="marquee-item">FULL-STACK ARCHITECTURE <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">SEMI-3D SPATIAL INTERACTION <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">CLEAN &amp; MODULAR PHP 8 <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">7-LAYER ANTI-SPAM SECURITY <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">RELATIONAL DATABASE DESIGN <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">ZERO AI TEMPLATE CLICHÉS <span class="marquee-sep">✦</span></span>
        <!-- Duplicate for seamless loop -->
        <span class="marquee-item">FULL-STACK ARCHITECTURE <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">SEMI-3D SPATIAL INTERACTION <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">CLEAN &amp; MODULAR PHP 8 <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">7-LAYER ANTI-SPAM SECURITY <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">RELATIONAL DATABASE DESIGN <span class="marquee-sep">✦</span></span>
        <span class="marquee-item">ZERO AI TEMPLATE CLICHÉS <span class="marquee-sep">✦</span></span>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       SECTION 01: TENTANG SAYA, FOTO PROFIL & RIWAYAT PERJALANAN
       ========================================================================== -->
  <section class="section" id="about">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">// 01_TENTANG_SAYA_&amp;_RIWAYAT</div>
        <h2 class="section-title"><?= e($aboutTitle === 'System Blueprint & Engineering Philosophy' ? 'Tentang Diri Saya & Perjalanan Rekayasa' : $aboutTitle) ?></h2>
        <p class="section-lead"><?= e($aboutSubtitle) ?></p>
      </div>

      <div class="about-dossier-grid">
        <!-- Left Column: 3D Tactile Photo & Identity Dossier Card -->
        <aside class="about-photo-card spotlight-card tilt-card">
          <span class="tech-corner tl"></span><span class="tech-corner tr"></span>
          <span class="tech-corner bl"></span><span class="tech-corner br"></span>

          <div class="about-card-hud">
            <span class="about-hud-tag"><span class="status-dot"></span> ID_DOSSIER // VERIFIED</span>
            <span class="about-hud-code">LOC: <?= e(strtoupper($location)) ?></span>
          </div>

          <div class="about-photo-frame">
            <?php if ($aboutPhoto): ?>
              <img src="<?= e($aboutPhoto) ?>" alt="Foto <?= e($name) ?>" class="about-photo-img" loading="lazy">
            <?php else: ?>
              <div class="about-photo-fallback">
                <span><?= e(strtoupper(substr($name, 0, 2))) ?></span>
                <small>UPLOAD FOTO DI PANEL ADMIN</small>
              </div>
            <?php endif; ?>
            <div class="about-photo-scanline"></div>
            <div class="about-photo-badge">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              <span><?= e($expYears) ?></span>
            </div>
          </div>

          <div class="about-identity-block">
            <h3 class="about-identity-name"><?= e($name) ?></h3>
            <p class="about-identity-role"><?= e($role) ?></p>
          </div>

          <div class="about-spec-list">
            <div class="about-spec-item">
              <span class="spec-label">PENDIDIKAN</span>
              <strong class="spec-value"><?= e($eduSummary) ?></strong>
            </div>
            <div class="about-spec-item">
              <span class="spec-label">FOKUS KEAHLIAN</span>
              <strong class="spec-value"><?= e($focusSummary) ?></strong>
            </div>
            <div class="about-spec-item">
              <span class="spec-label">DOMISILI</span>
              <strong class="spec-value"><?= e($location) ?></strong>
            </div>
            <div class="about-spec-item">
              <span class="spec-label">KONTAK EMAIL</span>
              <strong class="spec-value"><?= e($email) ?></strong>
            </div>
          </div>

          <div class="about-card-actions">
            <a href="#contact" class="btn primary sm magnetic-btn" style="flex:1;">HUBUNGI_SAYA() ↗</a>
            <a href="#projects" class="btn sm magnetic-btn" style="flex:1;">LIHAT_KARYA()</a>
          </div>
        </aside>

        <!-- Right Column: Penjelasan Tentang Diri Saya & Riwayat Perjalanan -->
        <div class="about-content-col">
          <!-- Sub-panel A: Penjelasan Tentang Diri Saya -->
          <article class="about-bio-card spotlight-card">
            <span class="tech-corner tl"></span><span class="tech-corner br"></span>
            <div class="about-subpanel-head">
              <span class="subpanel-kicker">01A // PENJELASAN_TENTANG_DIRI_SAYA</span>
              <span class="subpanel-badge">BIO_NARRATIVE.MD</span>
            </div>

            <div class="about-bio-body">
              <?= nl2br(e($aboutText)) ?>
            </div>

            <div class="about-metrics-row">
              <div class="about-metric-box">
                <span class="metric-num"><?= count($projects) ?>+</span>
                <span class="metric-caption">Project &amp; Sistem</span>
              </div>
              <div class="about-metric-box">
                <span class="metric-num"><?= count($skills) ?>+</span>
                <span class="metric-caption">Teknologi &amp; Stack</span>
              </div>
              <div class="about-metric-box">
                <span class="metric-num"><?= count($historyItems) ?></span>
                <span class="metric-caption">Riwayat &amp; Fase</span>
              </div>
            </div>
          </article>

          <!-- Sub-panel B: Riwayat Pendidikan, Pengalaman & Perjalanan -->
          <div class="about-history-card spotlight-card">
            <span class="tech-corner tr"></span><span class="tech-corner bl"></span>
            <div class="about-subpanel-head history-head-wrap">
              <div>
                <span class="subpanel-kicker">01B // RIWAYAT_PENDIDIKAN_&amp;_PENGALAMAN</span>
                <h3 class="about-history-title">Jejak Akademik, Pengalaman &amp; Perjalanan</h3>
              </div>

              <?php if (count($historyTypes) > 1): ?>
              <div class="history-filter-tabs" id="history-filter-tabs" role="tablist" aria-label="Filter Riwayat">
                <button type="button" class="history-filter-btn active" data-hist-filter="all">Semua</button>
                <?php foreach ($historyTypes as $hType): ?>
                <button type="button" class="history-filter-btn" data-hist-filter="<?= e($hType) ?>"><?= e($hType) ?></button>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

            <div class="history-timeline" id="history-timeline">
              <?php foreach ($historyItems as $idx => $item):
                $itemType = trim((string)($item['type'] ?? 'Pendidikan'));
                $typeSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $itemType));
              ?>
              <div class="history-item" data-hist-type="<?= e($itemType) ?>">
                <div class="history-node-col">
                  <span class="history-node-dot"></span>
                  <span class="history-node-line"></span>
                </div>
                <div class="history-item-body">
                  <div class="history-item-meta">
                    <span class="history-period"><?= e($item['period'] ?? '') ?></span>
                    <span class="history-type-badge type-<?= e($typeSlug) ?>"><?= e($itemType) ?></span>
                  </div>
                  <h4 class="history-item-title"><?= e($item['title'] ?? '') ?></h4>
                  <?php if (!empty($item['org'])): ?>
                    <div class="history-item-org">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path>
                      </svg>
                      <span><?= e($item['org']) ?></span>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($item['desc'])): ?>
                    <p class="history-item-desc"><?= nl2br(e($item['desc'])) ?></p>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       SECTION 02: PROJECTS — DUAL-MODE 3D DECK & MATRIX SHOWCASE
       ========================================================================== -->
  <section class="section" id="projects">
    <div class="wrap">
      <div class="section-header projects-section-header">
        <div>
          <div class="section-kicker">// 02_DEPLOYED_WORKS</div>
          <h2 class="section-title">Eksplorasi Karya &amp; Sistem</h2>
          <p class="section-lead">Geser dek 3D secara horizontal atau beralih ke tampilan Matrix Grid untuk menginspeksi setiap project.</p>
        </div>

        <div class="slider-controls-wrap">
          <!-- Dual View Switcher -->
          <div class="view-mode-toggle" role="group" aria-label="Pilih Mode Tampilan">
            <button type="button" class="view-mode-btn active" id="view-rail-btn">3D_RAIL</button>
            <button type="button" class="view-mode-btn" id="view-matrix-btn">MATRIX_GRID</button>
          </div>

          <button type="button" class="slider-autoplay-badge" id="slider-autoplay-indicator">
            <span class="status-dot"></span>
            <span class="autoplay-text">AUTO_DECK</span>
          </button>

          <div class="slider-arrow-btns">
            <button type="button" class="slider-arrow-btn" id="slider-prev" aria-label="Sebelumnya">←</button>
            <button type="button" class="slider-arrow-btn" id="slider-next" aria-label="Selanjutnya">→</button>
          </div>
        </div>
      </div>

      <!-- Filter & Search -->
      <div class="projects-filter-bar">
        <div class="filter-tabs">
          <button type="button" class="filter-tab active" data-filter="all">ALL_SYSTEMS (<?= count($projects) ?>)</button>
          <?php foreach ($categories as $cat): ?>
            <button type="button" class="filter-tab" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
          <?php endforeach; ?>
        </div>

        <div class="project-search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="project-search-input" placeholder="Filter by keyword..." aria-label="Cari project">
        </div>
      </div>

      <?php if (!$projects): ?>
        <div class="empty">Belum ada project yang dipublikasikan di database.</div>
      <?php else: ?>
        <div class="projects-slider-viewport" id="projects-slider-viewport">
          <div class="projects-slider-track" id="projects-slider-track">
            <?php foreach ($projects as $idx => $project):
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
            <article class="project-card spotlight-card tilt-card <?= $project['featured'] ? 'featured' : '' ?>" data-category="<?= e($project['category']) ?>">
              <span class="tech-corner tl"></span><span class="tech-corner br"></span>
              <span class="project-index-num">SYS_<?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?></span>

              <?php if ($project['featured']): ?>
                <div class="featured-badge">★ FEATURED</div>
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
                      <span class="mockup-url">https://<?= e(slugify($project['title'])) ?>.sys</span>
                    </div>
                    <div class="mockup-canvas">
                      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                      </svg>
                      <span style="font-family:var(--font-mono); font-size:0.74rem;"><?= e($project['category']) ?></span>
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
                      LIVE_DEMO ↗
                    </a>
                  <?php endif; ?>

                  <?php if (!empty($project['repo_url'])): ?>
                    <a class="btn sm" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener">
                      REPO ↗
                    </a>
                  <?php endif; ?>

                  <button type="button" class="btn sm btn-ghost btn-project-detail" data-project="<?= $projectJson ?>">
                    INSPECT 🔍
                  </button>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="slider-footer" id="slider-footer">
          <div class="slider-dots" id="slider-dots"></div>
          <div class="slider-hint">[DRAG / SWIPE 3D DECK]</div>
        </div>

        <div id="projects-empty-filter" class="empty" style="display:none; margin-top:16px;">
          Tidak ada project yang cocok dengan filter pencarian.
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       SECTION 03: 3D TACTILE MECHANICAL KEYCAPS & RADIAL TELEMETRY
       ========================================================================== -->
  <section class="section" id="skills">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">// 03_TACTILE_TECH_MATRIX</div>
        <h2 class="section-title">3D Hardware Keycaps &amp; Stack</h2>
        <p class="section-lead">Tekan atau arahkan kursor pada tombol mekanikal 3D di bawah untuk menguji telemetri penguasaan instrumen teknis.</p>
      </div>

      <?php if (!$skills): ?>
        <div class="empty">Data keahlian belum ditambahkan di database.</div>
      <?php else: ?>
        <div class="skills-3d-layout">
          <!-- Left: 3D Mechanical Keycaps Board -->
          <div class="keycaps-board">
            <?php foreach ($skills as $idx => $skill): ?>
            <div class="keycap-card <?= $idx === 0 ? 'active-cap' : '' ?>"
                 data-skill-name="<?= e($skill['name']) ?>"
                 data-skill-cat="<?= e($skill['category'] ?: 'Core Stack') ?>"
                 data-skill-level="<?= (int)$skill['level'] ?>">
              <div class="keycap-top">
                <div class="keycap-icon">
                  <?= getSkillIcon($skill['name']) ?>
                </div>
                <span class="keycap-led"></span>
              </div>
              <div class="keycap-name"><?= e($skill['name']) ?></div>
              <div class="keycap-cat"><?= e($skill['category'] ?: 'Core Stack') ?></div>
              <div class="keycap-meter">
                <div class="skill-bar-bg">
                  <div class="skill-bar-fill" data-level="<?= (int)$skill['level'] ?>" style="width: 0%;"></div>
                </div>
                <span><?= (int)$skill['level'] ?>%</span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Right: Live Radial Telemetry Gauge -->
          <?php $firstSkill = $skills[0]; ?>
          <div class="skill-telemetry-pod spotlight-card">
            <span class="tech-corner tl"></span><span class="tech-corner br"></span>
            <div style="font-family:var(--font-mono); font-size:0.7rem; color:var(--text-sub);">
              MODULE_TELEMETRY_READOUT
            </div>

            <div class="radial-gauge">
              <svg viewBox="0 0 140 140">
                <circle class="gauge-bg" cx="70" cy="70" r="60"></circle>
                <circle class="gauge-fill" id="gauge-circle" cx="70" cy="70" r="60"></circle>
              </svg>
              <div class="gauge-center">
                <span class="gauge-pct" id="gauge-pct"><?= (int)$firstSkill['level'] ?>%</span>
                <span class="gauge-label">PROFICIENCY</span>
              </div>
            </div>

            <div>
              <h3 id="gauge-title" style="font-family:var(--font-display); font-size:1.35rem;"><?= e($firstSkill['name']) ?></h3>
              <p id="gauge-cat" style="font-family:var(--font-mono); font-size:0.76rem; color:var(--accent); margin-top:4px;">
                LAYER // <?= e(strtoupper($firstSkill['category'] ?: 'CORE STACK')) ?>
              </p>
            </div>

            <p style="font-size:0.84rem; color:var(--text-muted);">
              Klik salah satu tombol keycap 3D di sebelah kiri untuk menginspeksi metrik modul teknologi.
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       SECTION 04: ENGINEERING LOGS (BLOG DOSSIERS)
       ========================================================================== -->
  <section class="section" id="blog">
    <div class="wrap">
      <div class="section-header">
        <div class="section-kicker">// 04_ENGINEERING_DOSSIERS</div>
        <h2 class="section-title">Catatan &amp; Dokumentasi Teknis</h2>
        <p class="section-lead">Tulisan seputar arsitektur sistem, eksperimen kode, dan solusi pemrograman.</p>
      </div>

      <?php if (!$posts): ?>
        <div class="empty">Belum ada artikel teknis yang dipublikasikan.</div>
      <?php else: ?>
        <div class="blog-grid">
          <?php foreach ($posts as $idx => $post): ?>
          <article class="post-card spotlight-card tilt-card">
            <span class="tech-corner tl"></span><span class="tech-corner br"></span>
            <div class="post-image">
              <?php if (!empty($post['cover']) && file_exists(__DIR__ . '/uploads/' . basename($post['cover']))): ?>
                <img src="uploads/<?= e(basename($post['cover'])) ?>" alt="<?= e($post['title']) ?>">
              <?php else: ?>
                <div class="post-fallback">
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                  </svg>
                </div>
              <?php endif; ?>
            </div>

            <div class="post-body">
              <div class="post-date">
                DOSSIER_0<?= $idx + 1 ?> // <?= e(date('d M Y', strtotime($post['published_at'] ?: $post['created_at']))) ?>
              </div>
              <h3 class="post-title"><?= e($post['title']) ?></h3>
              <p class="post-excerpt"><?= e($post['excerpt']) ?></p>

              <a class="read-link" href="post.php?slug=<?= urlencode($post['slug']) ?>">
                OPEN_DOSSIER() ↗
              </a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       SECTION 05: ENCRYPTED TRANSMISSION CONSOLE (7-LAYER ANTI-SPAM)
       ========================================================================== -->
  <section class="section" id="contact">
    <div class="wrap">
      <div class="contact-container">
        <div class="contact-info">
          <div>
            <div class="section-kicker">// 05_TRANSMISSION_DECK</div>
            <h2>Siap membangun sistem level berikutnya?</h2>
          </div>
          <p style="color:var(--text-muted);">
            Kirimkan spesifikasi project, tawaran kolaborasi, atau diskusi teknis melalui konsol transmisi berproteksi anti-spam di samping.
          </p>

          <div class="contact-card-list">
            <div class="contact-card spotlight-card">
              <div class="contact-card-left">
                <div class="contact-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                </div>
                <div>
                  <div class="contact-card-label">DIRECT_ENDPOINT</div>
                  <div class="contact-card-val"><?= e($email) ?></div>
                </div>
              </div>
              <button type="button" class="btn-copy btn-copy-email" data-email="<?= e($email) ?>">COPY</button>
            </div>

            <div class="contact-card spotlight-card">
              <div class="contact-card-left">
                <div class="contact-card-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                </div>
                <div>
                  <div class="contact-card-label">COORDINATES</div>
                  <div class="contact-card-val"><?= e($location) ?> (UTC+7)</div>
                </div>
              </div>
              <span class="status-pill">ACTIVE</span>
            </div>
          </div>
        </div>

        <!-- Right: 7-Layer Anti-Spam Form -->
        <div class="contact-form-panel spotlight-card">
          <span class="tech-corner tl"></span><span class="tech-corner tr"></span>
          <span class="tech-corner bl"></span><span class="tech-corner br"></span>

          <div class="form-security-header">
            <span>PROTOCOL // SECURE_POST_V2</span>
            <span class="security-shield-badge">
              🛡️ 7-Layer Anti-Spam Active
            </span>
          </div>

          <?php if ($sent): ?>
            <div class="notice">
              <span>✓ Transmisi pesan berhasil diverifikasi dan disimpan ke sistem!</span>
            </div>
          <?php elseif ($formError): ?>
            <div class="notice error">
              <span>⚠️ <?= e($formError) ?></span>
            </div>
          <?php endif; ?>

          <form class="contact-form" method="post" action="#contact">
            <input type="hidden" name="send_message" value="1">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="form_loaded_at" value="<?= $formRenderTimestamp ?>">

            <!-- Honeypot Bot Trap -->
            <div class="hp-trap" aria-hidden="true">
              <label>Jangan diisi</label>
              <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off" value="">
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label class="form-label"><span>SENDER_NAME *</span></label>
                <input class="form-input" name="name" value="<?= e($oldName) ?>" placeholder="Nama lengkap Anda" required maxlength="120">
              </div>

              <div class="form-group">
                <label class="form-label"><span>RETURN_EMAIL *</span></label>
                <input class="form-input" name="email" type="email" value="<?= e($oldEmail) ?>" placeholder="nama@domain.com" required maxlength="190">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label"><span>SUBJECT_HEADER</span></label>
              <input class="form-input" name="subject" value="<?= e($oldSubject) ?>" placeholder="Kolaborasi Project / Sistem Web / Konsultasi" maxlength="200">
            </div>

            <div class="form-group">
              <label class="form-label">
                <span>MESSAGE_PAYLOAD *</span>
                <span class="form-char-count" id="message-char-count">0 chars</span>
              </label>
              <textarea class="form-textarea" name="message" placeholder="Tuliskan detail kebutuhan sistem atau pesan Anda..." required maxlength="3000"><?= e($oldMessage) ?></textarea>
            </div>

            <!-- Dynamic Human Math Verification -->
            <div class="spam-challenge-box">
              <label class="spam-challenge-label" for="spam_answer">
                <span>🔐 VERIFIKASI MANUSIA: Hitung <strong><?= $mathA ?> + <?= $mathB ?></strong> =</span>
              </label>
              <input class="form-input spam-challenge-input" id="spam_answer" name="spam_answer" type="number" placeholder="?" required>
            </div>

            <button class="btn primary magnetic-btn" type="submit" style="width:100%; padding:14px;">
              EXECUTE_TRANSMISSION() ↗
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
          <span class="brand-cube">3D</span>
          <span><?= e($name) ?><span class="dot">_OS</span></span>
        </a>
        <p><?= e($role) ?></p>
      </div>

      <nav class="footer-nav">
        <a href="#home">~/stage</a>
        <a href="#about">./blueprint</a>
        <a href="#projects">./works</a>
        <a href="#skills">./keycaps</a>
        <a href="#blog">./logs</a>
        <a href="#contact">./transmit</a>
      </nav>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e($name) ?>. <?= e($footerText) ?></span>
      <a class="footer-admin-link" href="admin/login.php">
        [ROOT@ADMIN_CONSOLE] ↗
      </a>
    </div>
  </div>
</footer>

<!-- ==========================================================================
     3D HOLOGRAPHIC DEVELOPER PROFILE MODAL (MUNCUL SAAT KLIK BUTTON PROFIL)
     ========================================================================== -->
<div class="modal-backdrop" id="profile-modal">
  <div class="modal-card profile-modal-card spotlight-card tilt-card" role="dialog" aria-modal="true" aria-label="Profil Singkat <?= e($name) ?>">
    <span class="tech-corner tl"></span><span class="tech-corner tr"></span>
    <span class="tech-corner bl"></span><span class="tech-corner br"></span>

    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span class="status-dot"></span>
        <span style="font-family:var(--font-mono); font-size:0.74rem; color:var(--accent); font-weight:700;">
          DEVELOPER_ID // <?= strtoupper(substr(md5($name), 0, 8)) ?>
        </span>
      </div>
      <button type="button" class="modal-close" id="profile-modal-close" aria-label="Tutup profil">✕</button>
    </div>

    <div class="profile-modal-body">
      <!-- Left: Photo Frame -->
      <div class="profile-photo-column">
        <div class="profile-photo-frame">
          <?php if ($profile && file_exists(__DIR__ . '/uploads/' . basename($profile))): ?>
            <img src="uploads/<?= e(basename($profile)) ?>" alt="Foto Profil <?= e($name) ?>">
          <?php else: ?>
            <div class="profile-photo-fallback">
              <span><?= e(mb_substr($name, 0, 1)) ?></span>
            </div>
          <?php endif; ?>
        </div>
        <div class="pass-barcode" style="margin-top:10px;"></div>
        <div style="font-family:var(--font-mono); font-size:0.68rem; color:var(--text-sub); text-align:center; margin-top:6px;">
          📍 <?= e($location) ?> · VERIFIED
        </div>
      </div>

      <!-- Right: Short Bio & Quick Info -->
      <div class="profile-info-column">
        <div class="status-pill" style="width:fit-content; margin-bottom:8px;">
          <span class="status-dot"></span>
          <span><?= e($availability) ?></span>
        </div>

        <h3 class="profile-modal-name"><?= e($name) ?></h3>
        <div class="profile-modal-role"><?= e($role) ?></div>

        <p class="profile-modal-bio"><?= nl2br(e($aboutText)) ?></p>

        <div class="profile-mini-stats">
          <div class="profile-stat-box">
            <strong><?= count($projects) ?>+</strong>
            <span>Projects</span>
          </div>
          <div class="profile-stat-box">
            <strong><?= count($skills) ?>+</strong>
            <span>Tech Stack</span>
          </div>
          <div class="profile-stat-box">
            <strong><?= e($location) ?></strong>
            <span>Base</span>
          </div>
        </div>

        <div class="profile-modal-actions">
          <a class="btn primary sm" href="#contact" id="profile-contact-btn">
            HUBUNGI_SAYA ↗
          </a>
          <button type="button" class="btn sm btn-copy-email" data-email="<?= e($email) ?>">
            SALIN_EMAIL [<?= e($email) ?>]
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

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
      <p id="modal-project-desc" style="color:var(--text-muted); font-size:0.95rem; line-height:1.68;"></p>
      <div style="display:flex; gap:10px; margin-top:8px; flex-wrap:wrap;">
        <a id="modal-project-demo" class="btn primary sm" href="#" target="_blank" rel="noopener">LIVE_DEMO ↗</a>
        <a id="modal-project-repo" class="btn sm" href="#" target="_blank" rel="noopener">SOURCE_CODE ↗</a>
      </div>
    </div>
  </div>
</div>

<button class="back-to-top" id="back-to-top" aria-label="Kembali ke atas">↑</button>
<div id="toast-container"></div>

<script src="assets/main.js?v=<?= $jsVer ?>"></script>
</body>
</html>
