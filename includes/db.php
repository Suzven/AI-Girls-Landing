<?php
require_once __DIR__ . '/../config.php';

/* полифилл на случай, если на хостинге нет расширения mbstring */
if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $length = null) { return $length === null ? substr($s, $start) : substr($s, $start, $length); }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($s) { return strtoupper($s); }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($s) { return strlen($s); }
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Check credentials in config.php');
        }
    }
    return $pdo;
}

function esc($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'tool-' . time();
}

/* ---------- выборки ---------- */

function getToolBySlug(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM tools WHERE slug = ?');
    $st->execute([$slug]);
    $tool = $st->fetch();
    return $tool ?: null;
}

function getToolRelations(int $toolId): array {
    $d = db();
    $rel = [];
    foreach (['badges' => 'label', 'features' => 'feature', 'pros' => 'text', 'cons' => 'text', 'best_for' => 'text'] as $table => $col) {
        $st = $d->prepare("SELECT $col AS v FROM $table WHERE tool_id = ? ORDER BY sort_order, id");
        $st->execute([$toolId]);
        $rel[$table] = array_column($st->fetchAll(), 'v');
    }
    $st = $d->prepare('SELECT plan_name, price FROM pricing_plans WHERE tool_id = ? ORDER BY sort_order, id');
    $st->execute([$toolId]);
    $rel['pricing'] = $st->fetchAll();

    $st = $d->prepare('SELECT question, answer FROM faqs WHERE tool_id = ? ORDER BY sort_order, id');
    $st->execute([$toolId]);
    $rel['faqs'] = $st->fetchAll();

    $st = $d->prepare('SELECT c.name, c.slug FROM categories c JOIN tool_categories tc ON tc.category_id = c.id WHERE tc.tool_id = ? ORDER BY c.name');
    $st->execute([$toolId]);
    $rel['categories'] = $st->fetchAll();

    $st = $d->prepare('SELECT t.* FROM tools t JOIN alternatives a ON a.alternative_tool_id = t.id WHERE a.tool_id = ? LIMIT 6');
    $st->execute([$toolId]);
    $rel['alternatives'] = $st->fetchAll();

    return $rel;
}

function getToolBadges(int $toolId, int $limit = 5): array {
    $st = db()->prepare('SELECT label FROM badges WHERE tool_id = ? ORDER BY sort_order, id LIMIT ' . (int)$limit);
    $st->execute([$toolId]);
    return array_column($st->fetchAll(), 'label');
}

function getTools(array $opts = []): array {
    $where = [];
    $params = [];
    $join = '';
    if (!empty($opts['featured']))   $where[] = 'featured = 1';
    if (!empty($opts['free_trial'])) $where[] = 'free_trial = 1';
    if (!empty($opts['category_slug'])) {
        $join = ' JOIN tool_categories tc ON tc.tool_id = t.id JOIN categories c ON c.id = tc.category_id ';
        $where[] = 'c.slug = ?';
        $params[] = $opts['category_slug'];
    }
    $sql = 'SELECT DISTINCT t.* FROM tools t' . $join;
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY t.featured DESC, t.created_at DESC';
    if (!empty($opts['limit'])) $sql .= ' LIMIT ' . (int)$opts['limit'];
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function getCategories(): array {
    return db()->query('SELECT c.*, (SELECT COUNT(*) FROM tool_categories tc WHERE tc.category_id = c.id) AS cnt FROM categories c ORDER BY c.name')->fetchAll();
}
