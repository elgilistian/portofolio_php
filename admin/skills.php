<?php
$pageTitle = 'Kelola Tools & Technologies (Skills)';
require __DIR__ . '/_header.php';

// Ensure v2 schema & categories exist
ensure_skills_v2($pdo);

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_skill') {
        $editId = (int)($_POST['edit_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $cat = trim($_POST['category_custom'] ?? '') !== ''
            ? trim($_POST['category_custom'])
            : trim($_POST['category'] ?? 'Tools');
        $level = max(0, min(100, (int)($_POST['level'] ?? 85)));
        $order = (int)($_POST['sort_order'] ?? 1);

        if ($name === '') {
            $error = 'Nama skill tidak boleh kosong.';
        } else {
            if ($subtitle === '') {
                $subtitle = 'Core Technology';
            }
            if ($editId > 0) {
                $st = $pdo->prepare('UPDATE skills SET name = ?, category = ?, subtitle = ?, level = ?, sort_order = ? WHERE id = ?');
                $st->execute([$name, $cat, $subtitle, $level, $order, $editId]);
                $notice = 'Skill "' . $name . '" berhasil diperbarui.';
            } else {
                $st = $pdo->prepare('INSERT INTO skills (name, category, subtitle, level, sort_order) VALUES (?, ?, ?, ?, ?)');
                $st->execute([$name, $cat, $subtitle, $level, $order]);
                $notice = 'Skill "' . $name . '" berhasil ditambahkan ke kategori ' . $cat . '.';
            }
        }
    } elseif ($action === 'delete_skill') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare('DELETE FROM skills WHERE id = ?');
            $st->execute([$id]);
            $notice = 'Item skill berhasil dihapus.';
        }
    } elseif ($action === 'save_categories') {
        $skillsSubtitle = trim($_POST['skills_subtitle'] ?? '');
        save_setting($pdo, 'skills_subtitle', $skillsSubtitle);

        $catNames = $_POST['cat_name'] ?? [];
        $catIcons = $_POST['cat_icon'] ?? [];
        $catDescs = $_POST['cat_desc'] ?? [];

        $newCategories = [];
        if (is_array($catNames)) {
            foreach ($catNames as $idx => $cName) {
                $cName = trim((string)$cName);
                if ($cName === '') continue;
                $newCategories[] = [
                    'name' => $cName,
                    'icon' => trim((string)($catIcons[$idx] ?? 'tools')),
                    'desc' => trim((string)($catDescs[$idx] ?? '')),
                ];
            }
        }

        if (!empty($newCategories)) {
            save_setting($pdo, 'skills_categories_json', json_encode($newCategories, JSON_UNESCAPED_UNICODE));
            $notice = 'Pengaturan kategori & deskripsi section Skills berhasil disimpan.';
        }
    }
}

// Reload grouped skills & categories
$skillGroups = ensure_skills_v2($pdo);
$rawCats = setting($pdo, 'skills_categories_json', '');
$configuredCats = $rawCats !== '' ? json_decode($rawCats, true) : null;
if (!is_array($configuredCats) || empty($configuredCats)) {
    $configuredCats = default_skill_categories();
}
$skillsSubtitle = setting($pdo, 'skills_subtitle', 'Teknologi dan tools yang saya gunakan untuk mengembangkan website, programming, UI design, dan berbagai kebutuhan kreatif.');

// Check if editing a skill
$editSkill = null;
if (!empty($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM skills WHERE id = ? LIMIT 1');
    $st->execute([(int)$_GET['edit']]);
    $editSkill = $st->fetch() ?: null;
}
?>

<?php if ($notice): ?>
  <div class="notice">✓ <?= e($notice) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="notice error">⚠️ <?= e($error) ?></div>
<?php endif; ?>

<!-- PANEL 1: TAMBAH / EDIT SKILL -->
<div class="panel">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
    <div>
      <h2 style="margin:0;"><?= $editSkill ? 'Edit Skill: ' . e($editSkill['name']) : '1. Tambah Skill / Tool Baru' ?></h2>
      <p class="muted" style="font-size:0.84rem; margin-top:4px;">
        Setiap item skill akan tampil di dalam kotak kategori yang dipilih lengkap dengan sub-label fungsinya (contoh: <strong>PHP</strong> — <em>Server-side</em>).
      </p>
    </div>
    <?php if ($editSkill): ?>
      <a href="skills.php" class="admin-btn secondary">Batal Edit</a>
    <?php endif; ?>
  </div>

  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="save_skill">
    <input type="hidden" name="edit_id" value="<?= (int)($editSkill['id'] ?? 0) ?>">

    <div class="form-grid">
      <label>
        Nama Skill / Teknologi
        <input class="admin-input" name="name" required value="<?= e($editSkill['name'] ?? '') ?>" placeholder="Contoh: PHP, MySQL, CRUD, API, Figma">
      </label>

      <label>
        Sub-label / Fungsi Singkat (Tampil di bawah nama skill)
        <input class="admin-input" name="subtitle" required value="<?= e($editSkill['subtitle'] ?? '') ?>" placeholder="Contoh: Server-side, Database, Data processing, Data exchange">
      </label>

      <label>
        Pilih Kategori
        <select class="admin-input" name="category">
          <?php
            $selectedCat = $editSkill['category'] ?? 'Back End';
            foreach ($configuredCats as $catItem):
              $cName = $catItem['name'] ?? '';
          ?>
            <option value="<?= e($cName) ?>" <?= strtolower($selectedCat) === strtolower($cName) ? 'selected' : '' ?>>
              <?= e($cName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Urutan Tampil (Angka kecil tampil lebih dulu)
        <input class="admin-input" type="number" name="sort_order" value="<?= (int)($editSkill['sort_order'] ?? 1) ?>">
      </label>
    </div>

    <div style="margin-top:6px;">
      <button class="admin-btn" type="submit">
        <?= $editSkill ? '💾 Simpan Perubahan Skill' : '+ Tambah Skill ke Kategori' ?>
      </button>
    </div>
  </form>
</div>

<!-- PANEL 2: DAFTAR SKILLS PER KATEGORI -->
<div class="panel">
  <h2>2. Daftar Skills Saat Ini (Per Kategori)</h2>
  <p class="muted" style="font-size:0.84rem; margin-bottom:18px;">
    Berikut adalah seluruh kategori beserta daftar skill di dalamnya yang tampil pada section <strong>Tools &amp; technologies Yang Saya Gunakan</strong>.
  </p>

  <div style="display:grid; gap:18px;">
    <?php foreach ($skillGroups as $gIdx => $group):
      $numStr = str_pad((string)($gIdx + 1), 2, '0', STR_PAD_LEFT);
    ?>
    <div style="background:#0d1117; border:1px solid #30363d; border-radius:12px; padding:18px;">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #21262d;">
        <div>
          <span style="font-family:monospace; font-size:0.78rem; color:#58a6ff; font-weight:700;"><?= $numStr ?> // KATEGORI</span>
          <h3 style="margin:2px 0 4px; font-size:1.1rem; color:#fff;"><?= e($group['name']) ?></h3>
          <p class="muted" style="margin:0; font-size:0.82rem;"><?= e($group['desc']) ?></p>
        </div>
        <span style="font-family:monospace; font-size:0.76rem; background:#161b22; border:1px solid #30363d; padding:4px 10px; border-radius:99px; color:#8b949e;">
          <?= count($group['items']) ?> SKILLS
        </span>
      </div>

      <?php if (empty($group['items'])): ?>
        <p class="muted" style="font-size:0.84rem; margin:8px 0 0;">Belum ada skill di kategori ini.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Nama Skill</th>
                <th>Sub-label / Fungsi Singkat</th>
                <th>Urutan</th>
                <th style="width:160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($group['items'] as $it): ?>
              <tr>
                <td><strong style="color:#f0f6fc;"><?= e($it['name']) ?></strong></td>
                <td class="muted"><?= e($it['subtitle'] ?: 'Core Technology') ?></td>
                <td><?= (int)$it['sort_order'] ?></td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <a href="skills.php?edit=<?= (int)$it['id'] ?>" class="admin-btn secondary" style="padding:5px 10px; font-size:0.76rem;">Edit</a>
                    <form method="post" onsubmit="return confirm('Hapus skill <?= e(addslashes($it['name'])) ?>?')" style="display:inline;">
                      <input type="hidden" name="action" value="delete_skill">
                      <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                      <button class="admin-btn danger" style="padding:5px 10px; font-size:0.76rem;">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- PANEL 3: PENGATURAN KATEGORI & DESKRIPSI SECTION SKILLS -->
<div class="panel">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
    <div>
      <h2 style="margin:0;">3. Pengaturan Kategori &amp; Deskripsi Fokus</h2>
      <p class="muted" style="font-size:0.84rem; margin-top:4px;">
        Atur urutan kategori di menu sebelah kiri, ikon, serta kalimat penjelasan di bawah judul kategori pada panel sebelah kanan.
      </p>
    </div>
    <button type="button" class="admin-btn secondary" id="add-cat-btn">+ Tambah Kategori Baru</button>
  </div>

  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="save_categories">

    <label class="full" style="margin-bottom:8px;">
      Deskripsi Header Section Skills (Tampil di bawah angka jumlah CATEGORIES)
      <textarea class="admin-input" name="skills_subtitle" style="min-height:70px;"><?= e($skillsSubtitle) ?></textarea>
    </label>

    <div id="cat-editor-list" style="display:grid; gap:12px;">
      <?php foreach ($configuredCats as $cIdx => $cat): ?>
      <div class="cat-editor-row" style="background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:14px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
          <strong style="font-size:0.84rem; color:#58a6ff;">Kategori #<?= str_pad((string)($cIdx + 1), 2, '0', STR_PAD_LEFT) ?></strong>
          <button type="button" class="admin-btn danger remove-cat-btn" style="padding:4px 10px; font-size:0.74rem;">Hapus Kategori</button>
        </div>
        <div class="form-grid">
          <label>
            Nama Kategori
            <input class="admin-input" name="cat_name[]" value="<?= e($cat['name'] ?? '') ?>" required placeholder="Contoh: Back End">
          </label>
          <label>
            Ikon Kategori
            <select class="admin-input" name="cat_icon[]">
              <?php
                $curIcon = $cat['icon'] ?? 'tools';
                $iconOpts = [
                  'code' => 'Code (</> Front End)',
                  'server' => 'Database / Server (Back End)',
                  'terminal' => 'Terminal (>_ Programming)',
                  'design' => 'Pen / Design (UI Design)',
                  'layers' => 'Layers (Framework)',
                  'editing' => 'Edit / Media (Editing)',
                  'tools' => 'Wrench / Tools (Tools)',
                ];
                foreach ($iconOpts as $iVal => $iLabel):
              ?>
                <option value="<?= e($iVal) ?>" <?= $curIcon === $iVal ? 'selected' : '' ?>><?= e($iLabel) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="full">
            Deskripsi Kategori (Tampil saat kategori dipilih)
            <input class="admin-input" name="cat_desc[]" value="<?= e($cat['desc'] ?? '') ?>" placeholder="Contoh: Mengembangkan logic website, pengolahan data, dan komunikasi dengan database.">
          </label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:14px;">
      <button class="admin-btn" type="submit">💾 Simpan Pengaturan Kategori →</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('cat-editor-list');
  const addBtn = document.getElementById('add-cat-btn');

  function bindRemove() {
    list.querySelectorAll('.remove-cat-btn').forEach(btn => {
      btn.onclick = () => {
        if (list.querySelectorAll('.cat-editor-row').length <= 1) {
          alert('Minimal harus ada 1 kategori.');
          return;
        }
        btn.closest('.cat-editor-row').remove();
      };
    });
  }

  bindRemove();

  if (addBtn && list) {
    addBtn.addEventListener('click', () => {
      const count = String(list.querySelectorAll('.cat-editor-row').length + 1).padStart(2, '0');
      const div = document.createElement('div');
      div.className = 'cat-editor-row';
      div.style.cssText = 'background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:14px;';
      div.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
          <strong style="font-size:0.84rem; color:#58a6ff;">Kategori #${count}</strong>
          <button type="button" class="admin-btn danger remove-cat-btn" style="padding:4px 10px; font-size:0.74rem;">Hapus Kategori</button>
        </div>
        <div class="form-grid">
          <label>Nama Kategori
            <input class="admin-input" name="cat_name[]" required placeholder="Contoh: DevOps / Cloud">
          </label>
          <label>Ikon Kategori
            <select class="admin-input" name="cat_icon[]">
              <option value="code">Code (&lt;/&gt; Front End)</option>
              <option value="server">Database / Server (Back End)</option>
              <option value="terminal">Terminal (&gt;_ Programming)</option>
              <option value="design">Pen / Design (UI Design)</option>
              <option value="layers">Layers (Framework)</option>
              <option value="editing">Edit / Media (Editing)</option>
              <option value="tools" selected>Wrench / Tools (Tools)</option>
            </select>
          </label>
          <label class="full">Deskripsi Kategori (Tampil saat kategori dipilih)
            <input class="admin-input" name="cat_desc[]" placeholder="Jelaskan fokus dari kategori ini...">
          </label>
        </div>
      `;
      list.appendChild(div);
      bindRemove();
    });
  }
});
</script>

<?php require __DIR__ . '/_footer.php'; ?>
