<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function setting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    $cache[$key] = ($value === false || $value === null) ? $default : (string)$value;
    return $cache[$key];
}
function save_setting(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}
function admin_required(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'artikel-' . time();
}
function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function default_skill_categories(): array {
    return [
        [
            'name' => 'Front End',
            'icon' => 'code',
            'desc' => 'Membangun antarmuka website yang responsif, interaktif, dan optimal di berbagai ukuran layar perangkat.'
        ],
        [
            'name' => 'Back End',
            'icon' => 'server',
            'desc' => 'Mengembangkan logic website, pengolahan data, dan komunikasi dengan database.'
        ],
        [
            'name' => 'Programming',
            'icon' => 'terminal',
            'desc' => 'Penguasaan logika pemrograman, struktur data, dan penulisan kode yang bersih serta efisien.'
        ],
        [
            'name' => 'UI Design',
            'icon' => 'design',
            'desc' => 'Merancang tata letak visual, wireframing, dan sistem desain antarmuka yang modern serta ramah pengguna.'
        ],
        [
            'name' => 'Framework',
            'icon' => 'layers',
            'desc' => 'Memanfaatkan kerangka kerja dan library modern untuk mempercepat pengembangan aplikasi yang terstruktur.'
        ],
        [
            'name' => 'Editing',
            'icon' => 'editing',
            'desc' => 'Pengolahan aset visual, optimasi media grafis, dan penyuntingan konten kreatif pendukung website.'
        ],
        [
            'name' => 'Tools',
            'icon' => 'tools',
            'desc' => 'Perangkat lunak pendukung workflow pengembangan, manajemen versi kode, dan pengujian server.'
        ],
    ];
}

function get_skill_category_icon(string $iconKey, string $catName = ''): string {
    $key = strtolower(trim($iconKey));
    $name = strtolower(trim($catName));
    if ($key === 'code' || str_contains($name, 'front')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline><line x1="14" y1="4" x2="10" y2="20"></line></svg>';
    }
    if ($key === 'server' || str_contains($name, 'back') || str_contains($name, 'data')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="3" width="20" height="7" rx="1.5"></rect><rect x="2" y="14" width="20" height="7" rx="1.5"></rect><line x1="6" y1="6.5" x2="6.01" y2="6.5"></line><line x1="6" y1="17.5" x2="6.01" y2="17.5"></line></svg>';
    }
    if ($key === 'terminal' || str_contains($name, 'program')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line></svg>';
    }
    if ($key === 'design' || str_contains($name, 'ui') || str_contains($name, 'design')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19l7-7 3 3-7 7-3-3z"></path><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path><path d="M2 2l7.586 7.586"></path></svg>';
    }
    if ($key === 'layers' || str_contains($name, 'frame')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';
    }
    if ($key === 'editing' || str_contains($name, 'edit') || str_contains($name, 'media')) {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
    }
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';
}

function ensure_skills_v2(PDO $pdo): array {
    // 1. Ensure subtitle column exists in skills table
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM skills LIKE 'subtitle'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE skills ADD COLUMN subtitle VARCHAR(150) NOT NULL DEFAULT '' AFTER category");
        }
    } catch (Throwable $e) {
        // Ignore if already exists or restricted
    }

    // 2. Seed reference 7 categories & default items once if not initialized yet
    if (setting($pdo, 'skills_v2_seeded', '') === '') {
        $defaultCats = default_skill_categories();
        save_setting($pdo, 'skills_categories_json', json_encode($defaultCats, JSON_UNESCAPED_UNICODE));
        if (setting($pdo, 'skills_subtitle', '') === '') {
            save_setting($pdo, 'skills_subtitle', 'Teknologi dan tools yang saya gunakan untuk mengembangkan website, programming, UI design, dan berbagai kebutuhan kreatif.');
        }

        // Standardize old category names if present
        try {
            $pdo->exec("UPDATE skills SET category = 'Front End' WHERE LOWER(category) IN ('frontend', 'front-end')");
            $pdo->exec("UPDATE skills SET category = 'Back End' WHERE LOWER(category) IN ('backend', 'back-end', 'database')");
        } catch (Throwable $e) {}

        $seedSkills = [
            // Front End
            ['HTML5', 'Front End', 'Semantic Markup', 90, 1],
            ['CSS3', 'Front End', 'Responsive Layout & Styling', 88, 2],
            ['JavaScript', 'Front End', 'Interactive DOM & Logic', 82, 3],
            ['Responsive UI', 'Front End', 'Mobile-First Design', 85, 4],
            // Back End (Matches reference image!)
            ['PHP', 'Back End', 'Server-side', 88, 1],
            ['MySQL', 'Back End', 'Database', 85, 2],
            ['CRUD', 'Back End', 'Data processing', 90, 3],
            ['API', 'Back End', 'Data exchange', 80, 4],
            // Programming
            ['PHP 8+', 'Programming', 'OOP & Procedural Logic', 86, 1],
            ['JavaScript ES6+', 'Programming', 'Client & Async Scripting', 82, 2],
            ['SQL Query', 'Programming', 'Relational Data Modeling', 85, 3],
            ['Algorithm & Logic', 'Programming', 'Problem Solving', 84, 4],
            // UI Design
            ['Figma', 'UI Design', 'Interface Design & Prototype', 82, 1],
            ['Wireframing', 'UI Design', 'User Flow & Structure', 85, 2],
            ['Design System', 'UI Design', 'Consistent UI Components', 80, 3],
            ['UI/UX Principles', 'UI Design', 'Usability & Interaction', 84, 4],
            // Framework
            ['Bootstrap', 'Framework', 'Responsive CSS Framework', 86, 1],
            ['Tailwind CSS', 'Framework', 'Utility-First Styling', 82, 2],
            ['Native MVC', 'Framework', 'Modular PHP Architecture', 85, 3],
            ['REST Architecture', 'Framework', 'Web Service Pattern', 80, 4],
            // Editing
            ['Canva', 'Editing', 'Graphic & Layout Design', 88, 1],
            ['Photoshop / GIMP', 'Editing', 'Photo & Asset Retouching', 78, 2],
            ['Asset Optimization', 'Editing', 'WebP & Media Compression', 85, 3],
            ['Visual Content', 'Editing', 'Presentation & Media', 82, 4],
            // Tools
            ['VS Code', 'Tools', 'Code Editor & Extensions', 92, 1],
            ['Git & GitHub', 'Tools', 'Version Control System', 85, 2],
            ['XAMPP / Laragon', 'Tools', 'Local Web Server', 90, 3],
            ['phpMyAdmin', 'Tools', 'Database Administration', 88, 4],
        ];

        try {
            $checkStmt = $pdo->prepare('SELECT id, subtitle FROM skills WHERE LOWER(name) = LOWER(?) AND LOWER(category) = LOWER(?) LIMIT 1');
            $insertStmt = $pdo->prepare('INSERT INTO skills (name, category, subtitle, level, sort_order) VALUES (?, ?, ?, ?, ?)');
            $updateSubStmt = $pdo->prepare('UPDATE skills SET subtitle = ? WHERE id = ?');

            foreach ($seedSkills as [$sName, $sCat, $sSub, $sLvl, $sOrd]) {
                $checkStmt->execute([$sName, $sCat]);
                $existing = $checkStmt->fetch();
                if (!$existing) {
                    $insertStmt->execute([$sName, $sCat, $sSub, $sLvl, $sOrd]);
                } elseif (trim((string)($existing['subtitle'] ?? '')) === '') {
                    $updateSubStmt->execute([$sSub, (int)$existing['id']]);
                }
            }

            // Fill default subtitle for any older rows without subtitle
            $pdo->exec("UPDATE skills SET subtitle = 'Core Technology' WHERE subtitle = '' OR subtitle IS NULL");
        } catch (Throwable $e) {}

        save_setting($pdo, 'skills_v2_seeded', '1');
    }

    // 3. Load configured categories & group skills
    $rawCats = setting($pdo, 'skills_categories_json', '');
    $catList = $rawCats !== '' ? json_decode($rawCats, true) : null;
    if (!is_array($catList) || empty($catList)) {
        $catList = default_skill_categories();
    }

    $allSkills = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, id ASC')->fetchAll();

    $groups = [];
    $groupMap = [];
    foreach ($catList as $c) {
        $cName = trim((string)($c['name'] ?? ''));
        if ($cName === '') continue;
        $key = strtolower($cName);
        $groupMap[$key] = count($groups);
        $groups[] = [
            'name' => $cName,
            'icon' => trim((string)($c['icon'] ?? 'tools')),
            'desc' => trim((string)($c['desc'] ?? 'Teknologi dan instrumen pendukung pada kategori ' . $cName . '.')),
            'items' => []
        ];
    }

    foreach ($allSkills as $sk) {
        $catName = trim((string)($sk['category'] ?? 'Tools')) ?: 'Tools';
        $key = strtolower($catName);
        if (!isset($groupMap[$key])) {
            $groupMap[$key] = count($groups);
            $groups[] = [
                'name' => $catName,
                'icon' => 'tools',
                'desc' => 'Teknologi dan instrumen yang digunakan pada kategori ' . $catName . '.',
                'items' => []
            ];
        }
        $groups[$groupMap[$key]]['items'][] = $sk;
    }

    return $groups;
}

