<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) { echo json_encode([]); exit; }

$like = '%' . $q . '%';
$st = db()->prepare(
    'SELECT DISTINCT t.name, t.slug, t.tagline, t.logo, t.free_trial
     FROM tools t
     LEFT JOIN tool_categories tc ON tc.tool_id = t.id
     LEFT JOIN categories c ON c.id = tc.category_id
     LEFT JOIN badges b ON b.tool_id = t.id
     WHERE t.name LIKE ? OR t.tagline LIKE ? OR c.name LIKE ? OR b.label LIKE ?
     ORDER BY t.featured DESC, t.name
     LIMIT 8'
);
$st->execute([$like, $like, $like, $like]);
echo json_encode($st->fetchAll(), JSON_UNESCAPED_UNICODE);
