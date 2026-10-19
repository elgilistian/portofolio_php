<?php
$pageTitle = 'Tampilan, Tentang Saya & Riwayat';
require __DIR__ . '/_header.php';

$keys = [
    'name',
    'role',
    'hero_title',
    'hero_description',
    'about_title',
    'about_subtitle',
    'about_text',
    'education_summary',
    'focus_summary',
    'experience_years',
    'email',
    'location',
    'github',
    'instagram',
    'linkedin',
    'accent_color',
    'availability',
    'footer_text'
];

// Default Riwayat / Timeline items if none saved yet
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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Save text settings
    foreach ($keys as $key) {
        $value = trim($_POST[$key] ?? '');
        if ($key === 'accent_color' && !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            $value = '#00f0ff';
        }
        save_setting($pdo, $key, $value);
    }

    // 2. Save Riwayat / Timeline items (JSON)
    $periods = $_POST['hist_period'] ?? [];
    $types = $_POST['hist_type'] ?? [];
    $titles = $_POST['hist_title'] ?? [];
    $orgs = $_POST['hist_org'] ?? [];
    $descs = $_POST['hist_desc'] ?? [];

    $historyItems = [];
    if (is_array($titles)) {
        foreach ($titles as $idx => $t) {
            $t = trim((string)$t);
            $p = trim((string)($periods[$idx] ?? ''));
            $ty = trim((string)($types[$idx] ?? 'Pendidikan'));
            $o = trim((string)($orgs[$idx] ?? ''));
            $d = trim((string)($descs[$idx] ?? ''));
            if ($t !== '' || $d !== '') {
                $historyItems[] = [
                    'period' => $p ?: 'Sekarang',
                    'type' => $ty ?: 'Pendidikan',
                    'title' => $t ?: 'Riwayat',
                    'org' => $o,
                    'desc' => $d
                ];
            }
        }
    }
    save_setting($pdo, 'history_json', json_encode($historyItems, JSON_UNESCAPED_UNICODE));

    // 3. Handle Profile / About Image Upload
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!is_dir(__DIR__ . '/../uploads')) {
        mkdir(__DIR__ . '/../uploads', 0755, true);
    }

    if (!empty($_FILES['profile_image']['name']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
        $f = $_FILES['profile_image'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (isset($allowed[$mime]) && $f['size'] <= 4 * 1024 * 1024) {
            $filename = 'profile_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
            if (move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $filename)) {
                save_setting($pdo, 'profile_image', $filename);
                save_setting($pdo, 'about_image', $filename);
            }
        } else {
            $error = 'Format gambar harus JPG/PNG/WEBP dan ukuran maksimal 4 MB.';
        }
    }

    if (!$error) {
        $success = 'Seluruh pengaturan, profil tentang saya, dan riwayat berhasil disimpan!';
    }
}

// Load current values
$rawHistory = setting($pdo, 'history_json', '');
$currentHistory = [];
if ($rawHistory !== '') {
    $decoded = json_decode($rawHistory, true);
    if (is_array($decoded)) {
        $currentHistory = $decoded;
    }
}
if (empty($currentHistory)) {
    $currentHistory = $defaultHistory;
}

$currentProfileImg = setting($pdo, 'about_image', '') ?: setting($pdo, 'profile_image', '');
?>

<?php if ($success): ?>
  <div class="notice">✓ <?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="notice error">⚠️ <?= e($error) ?></div>
<?php endif; ?>

<form class="admin-form" method="post" enctype="multipart/form-data">
  <!-- PANEL 1: IDENTITAS & BERANDA -->
  <div class="panel">
    <h2>1. Identitas &amp; Beranda (#home)</h2>
    <div class="form-grid">
      <label>
        Nama Lengkap / Brand
        <input class="admin-input" name="name" value="<?= e(setting($pdo, 'name', 'Elgi Listian')) ?>" required>
      </label>
      <label>
        Profesi / Role Utama (pisahkan dengan · untuk animasi ketik)
        <input class="admin-input" name="role" value="<?= e(setting($pdo, 'role', 'Informatics Engineering · Full-Stack Web Developer')) ?>" required>
      </label>
      <label>
        Status Ketersediaan
        <input class="admin-input" name="availability" value="<?= e(setting($pdo, 'availability', 'ONLINE // OPEN FOR COLLABORATION')) ?>" required>
      </label>
      <label>
        Warna Aksen Utama (Hex, contoh: #00f0ff)
        <input class="admin-input" name="accent_color" value="<?= e(setting($pdo, 'accent_color', '#00f0ff')) ?>" pattern="^#[0-9a-fA-F]{6}$" required>
      </label>
      <label class="full">
        Judul Utama Beranda (Hero Title)
        <input class="admin-input" name="hero_title" value="<?= e(setting($pdo, 'hero_title', 'Architecting spatial, resilient & high-velocity web systems.')) ?>" required>
      </label>
      <label class="full">
        Deskripsi Singkat Beranda
        <textarea class="admin-input" name="hero_description"><?= e(setting($pdo, 'hero_description', 'Membangun aplikasi web generasi baru dengan perpaduan rekayasa backend yang presisi, struktur database optimal, dan interaksi antarmuka semi-3D yang imersif.')) ?></textarea>
      </label>
    </div>
  </div>

  <!-- PANEL 2: TENTANG DIRI SAYA & FOTO (#about) -->
  <div class="panel">
    <h2>2. Tentang Diri Saya &amp; Foto Profil (#about)</h2>
    <p class="muted" style="margin-bottom:16px; font-size:0.86rem;">
      Atur foto profil serta penjelasan lengkap mengenai diri Anda pada bagian Tentang Saya.
    </p>

    <div class="form-grid">
      <label>
        Judul Seksi Tentang Saya
        <input class="admin-input" name="about_title" value="<?= e(setting($pdo, 'about_title', 'Tentang Diri Saya & Perjalanan Teknis')) ?>" required>
      </label>
      <label>
        Subjudul / Ringkasan Pendek
        <input class="admin-input" name="about_subtitle" value="<?= e(setting($pdo, 'about_subtitle', 'Mengenal lebih dekat latar belakang, filosofi kerja, dan riwayat perjalanan saya di dunia teknologi.')) ?>">
      </label>

      <label class="full">
        Penjelasan Lengkap Tentang Diri Saya (Paragraf Bio)
        <textarea class="admin-input" name="about_text" style="min-height:130px;"><?= e(setting($pdo, 'about_text', "Halo! Saya adalah seorang pengembang web dan mahasiswa Teknik Informatika yang memiliki ketertarikan mendalam pada arsitektur perangkat lunak, perancangan database, dan antarmuka web interaktif.\n\nSaya senang mengubah permasalahan kompleks menjadi solusi digital yang fungsional, cepat, dan mudah digunakan. Dalam setiap proyek, saya selalu mengutamakan kebersihan kode (clean code), keamanan sistem, serta pengalaman pengguna yang modern.")) ?></textarea>
      </label>

      <label>
        Highlight Pendidikan (Singkat)
        <input class="admin-input" name="education_summary" value="<?= e(setting($pdo, 'education_summary', 'S1 Teknik Informatika')) ?>" placeholder="Contoh: S1 Teknik Informatika">
      </label>
      <label>
        Fokus &amp; Spesialisasi Utama
        <input class="admin-input" name="focus_summary" value="<?= e(setting($pdo, 'focus_summary', 'Full-Stack Web & Database Engineering')) ?>" placeholder="Contoh: Full-Stack Web Development">
      </label>
      <label class="full">
        Pengalaman / Jam Terbang Singkat
        <input class="admin-input" name="experience_years" value="<?= e(setting($pdo, 'experience_years', 'Aktif membangun proyek web & sistem informasi')) ?>" placeholder="Contoh: 3+ Tahun Eksplorasi & Pembangunan Web">
      </label>

      <!-- Upload Foto Tunggal (Digunakan untuk Tentang Saya & Modal Profil) -->
      <div class="full" style="background:#0d1117; border:1px solid #30363d; padding:16px; border-radius:10px;">
        <strong style="display:block; margin-bottom:8px; font-size:0.88rem;">Foto Profil &amp; Tentang Saya</strong>
        <?php if ($currentProfileImg && file_exists(__DIR__ . '/../uploads/' . basename($currentProfileImg))): ?>
          <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
            <img src="../uploads/<?= e(basename($currentProfileImg)) ?>" alt="Profile" style="width:68px; height:68px; object-fit:cover; border-radius:8px; border:1px solid #58a6ff;">
            <span class="muted" style="font-size:0.8rem;">Foto aktif: <?= e(basename($currentProfileImg)) ?></span>
          </div>
        <?php endif; ?>
        <input class="admin-input" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
        <span class="muted" style="font-size:0.75rem; display:block; margin-top:4px;">Format JPG, PNG, atau WEBP · Maks. 4 MB (Digunakan pada bagian Tentang Saya dan tombol Profil di Beranda)</span>
      </div>
    </div>
  </div>

  <!-- PANEL 3: RIWAYAT PENDIDIKAN, PENGALAMAN & PERJALANAN SAYA -->
  <div class="panel">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
      <div>
        <h2 style="margin:0;">3. Riwayat Pendidikan &amp; Pengalaman (Timeline #about)</h2>
        <p class="muted" style="font-size:0.85rem; margin-top:4px;">Daftar riwayat perjalanan akademik, organisasi, magang, atau pengalaman proyek Anda.</p>
      </div>
      <button type="button" class="admin-btn secondary" id="add-history-btn">+ Tambah Item Riwayat</button>
    </div>

    <div id="history-list-container" style="display:grid; gap:14px;">
      <?php foreach ($currentHistory as $idx => $item): ?>
      <div class="history-item-editor" style="background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <strong style="font-size:0.86rem; color:#58a6ff;">Item Riwayat #<?= $idx + 1 ?></strong>
          <button type="button" class="admin-btn danger remove-history-btn" style="padding:4px 10px; font-size:0.76rem;">Hapus</button>
        </div>
        <div class="form-grid">
          <label>
            Periode / Tahun
            <input class="admin-input" name="hist_period[]" value="<?= e($item['period'] ?? '') ?>" placeholder="Contoh: 2022 — Sekarang" required>
          </label>
          <label>
            Kategori Riwayat
            <select class="admin-input" name="hist_type[]">
              <?php
                $curType = $item['type'] ?? 'Pendidikan';
                foreach (['Pendidikan', 'Pengalaman', 'Organisasi', 'Pencapaian'] as $opt):
              ?>
                <option value="<?= e($opt) ?>" <?= $curType === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            Judul / Posisi / Gelar
            <input class="admin-input" name="hist_title[]" value="<?= e($item['title'] ?? '') ?>" placeholder="Contoh: Mahasiswa S1 Teknik Informatika" required>
          </label>
          <label>
            Institusi / Kampus / Perusahaan
            <input class="admin-input" name="hist_org[]" value="<?= e($item['org'] ?? '') ?>" placeholder="Contoh: Universitas / Freelance">
          </label>
          <label class="full">
            Deskripsi Singkat Riwayat
            <textarea class="admin-input" name="hist_desc[]" style="min-height:70px;" placeholder="Jelaskan aktivitas, fokus studi, atau tanggung jawab Anda..."><?= e($item['desc'] ?? '') ?></textarea>
          </label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- PANEL 4: KONTAK & SOSIAL MEDIA -->
  <div class="panel">
    <h2>4. Kontak, Lokasi &amp; Tautan Sosial</h2>
    <div class="form-grid">
      <label>
        Email Kontak
        <input class="admin-input" type="email" name="email" value="<?= e(setting($pdo, 'email', 'hello@example.com')) ?>" required>
      </label>
      <label>
        Lokasi / Kota
        <input class="admin-input" name="location" value="<?= e(setting($pdo, 'location', 'Indonesia')) ?>" required>
      </label>
      <label>
        URL GitHub
        <input class="admin-input" name="github" value="<?= e(setting($pdo, 'github', 'https://github.com/')) ?>">
      </label>
      <label>
        URL LinkedIn
        <input class="admin-input" name="linkedin" value="<?= e(setting($pdo, 'linkedin', 'https://linkedin.com/')) ?>">
      </label>
      <label>
        URL Instagram
        <input class="admin-input" name="instagram" value="<?= e(setting($pdo, 'instagram', 'https://instagram.com/')) ?>">
      </label>
      <label>
        Teks Footer
        <input class="admin-input" name="footer_text" value="<?= e(setting($pdo, 'footer_text', 'Crafted with spatial 3D precision & clean code.')) ?>">
      </label>
    </div>

    <div style="margin-top:20px;">
      <button class="admin-btn" type="submit" style="padding:12px 24px; font-size:0.95rem;">💾 Simpan Seluruh Perubahan →</button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('history-list-container');
  const addBtn = document.getElementById('add-history-btn');

  function bindRemoveButtons() {
    container.querySelectorAll('.remove-history-btn').forEach(btn => {
      btn.onclick = () => {
        if (container.querySelectorAll('.history-item-editor').length <= 1) {
          alert('Minimal harus ada 1 item riwayat.');
          return;
        }
        btn.closest('.history-item-editor').remove();
      };
    });
  }

  bindRemoveButtons();

  if (addBtn && container) {
    addBtn.addEventListener('click', () => {
      const count = container.querySelectorAll('.history-item-editor').length + 1;
      const wrapper = document.createElement('div');
      wrapper.className = 'history-item-editor';
      wrapper.style.cssText = 'background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:16px;';
      wrapper.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <strong style="font-size:0.86rem; color:#58a6ff;">Item Riwayat #${count}</strong>
          <button type="button" class="admin-btn danger remove-history-btn" style="padding:4px 10px; font-size:0.76rem;">Hapus</button>
        </div>
        <div class="form-grid">
          <label>Periode / Tahun
            <input class="admin-input" name="hist_period[]" placeholder="Contoh: 2024 — Sekarang" required>
          </label>
          <label>Kategori Riwayat
            <select class="admin-input" name="hist_type[]">
              <option value="Pendidikan">Pendidikan</option>
              <option value="Pengalaman">Pengalaman</option>
              <option value="Organisasi">Organisasi</option>
              <option value="Pencapaian">Pencapaian</option>
            </select>
          </label>
          <label>Judul / Posisi / Gelar
            <input class="admin-input" name="hist_title[]" placeholder="Contoh: Web Developer Intern" required>
          </label>
          <label>Institusi / Kampus / Perusahaan
            <input class="admin-input" name="hist_org[]" placeholder="Contoh: Nama Instansi / Kampus">
          </label>
          <label class="full">Deskripsi Singkat Riwayat
            <textarea class="admin-input" name="hist_desc[]" style="min-height:70px;" placeholder="Jelaskan aktivitas atau pencapaian Anda..."></textarea>
          </label>
        </div>
      `;
      container.appendChild(wrapper);
      bindRemoveButtons();
    });
  }
});
</script>

<?php require __DIR__ . '/_footer.php'; ?>


