<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$d = db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

/* ---------- загрузка картинки ---------- */
$uploadWarnings = [];
function handleUpload(string $field, string $current): string {
    global $uploadWarnings;
    if (empty($_FILES[$field]['name'])) return $current; // файл не выбирали — ок
    $err = $_FILES[$field]['error'];
    if ($err !== UPLOAD_ERR_OK) {
        $uploadWarnings[] = match ($err) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "«{$field}»: файл больше лимита сервера (upload_max_filesize = " . ini_get('upload_max_filesize') . "). Сожми картинку или увеличь лимит в php.ini.",
            UPLOAD_ERR_PARTIAL => "«{$field}»: файл загрузился не полностью, попробуй ещё раз.",
            default => "«{$field}»: ошибка загрузки (код {$err}).",
        };
        return $current;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
        $uploadWarnings[] = "«{$field}»: формат .{$ext} не поддерживается (только jpg, png, webp, gif, svg).";
        return $current;
    }
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir) || !is_writable($dir)) {
        $uploadWarnings[] = "«{$field}»: папка uploads/ отсутствует или недоступна для записи (проверь права 755/775 на хостинге).";
        return $current;
    }
    $name = 'uploads/' . uniqid($field . '_') . '.' . $ext;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], __DIR__ . '/../' . $name)) return $name;
    $uploadWarnings[] = "«{$field}»: не удалось сохранить файл в uploads/.";
    return $current;
}

/* ---------- сохранение ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Название обязательно';
    } else {
        $slug = trim($_POST['slug'] ?? '') !== '' ? slugify($_POST['slug']) : slugify($name);

        $logo = trim($_POST['logo'] ?? '');
        $hero = trim($_POST['hero_image'] ?? '');
        $logo = handleUpload('logo_file', $logo);
        $hero = handleUpload('hero_file', $hero);

        $data = [
            $name, $slug,
            trim($_POST['tagline'] ?? ''),
            trim($_POST['description'] ?? ''),
            $logo, $hero,
            trim($_POST['website_url'] ?? ''),
            trim($_POST['affiliate_link'] ?? ''),
            ($_POST['editor_score'] === '' ? null : $_POST['editor_score']),
            isset($_POST['free_trial']) ? 1 : 0,
            isset($_POST['featured']) ? 1 : 0,
            isset($_POST['verified']) ? 1 : 0,
            isset($_POST['is_new']) ? 1 : 0,
            isset($_POST['is_popular']) ? 1 : 0,
        ];

        try {
            if ($id) {
                $st = $d->prepare('UPDATE tools SET name=?, slug=?, tagline=?, description=?, logo=?, hero_image=?, website_url=?, affiliate_link=?, editor_score=?, free_trial=?, featured=?, verified=?, is_new=?, is_popular=? WHERE id=?');
                $st->execute([...$data, $id]);
            } else {
                $st = $d->prepare('INSERT INTO tools (name, slug, tagline, description, logo, hero_image, website_url, affiliate_link, editor_score, free_trial, featured, verified, is_new, is_popular) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $st->execute($data);
                $id = (int)$d->lastInsertId();
            }

            /* категории */
            $d->prepare('DELETE FROM tool_categories WHERE tool_id=?')->execute([$id]);
            foreach (($_POST['categories'] ?? []) as $cid) {
                $d->prepare('INSERT IGNORE INTO tool_categories (tool_id, category_id) VALUES (?,?)')->execute([$id, (int)$cid]);
            }

            /* списки "одна строка = один пункт" */
            $lineTables = ['badges' => 'label', 'features' => 'feature', 'pros' => 'text', 'cons' => 'text', 'best_for' => 'text'];
            foreach ($lineTables as $table => $col) {
                $d->prepare("DELETE FROM $table WHERE tool_id=?")->execute([$id]);
                $lines = array_filter(array_map('trim', explode("\n", $_POST[$table] ?? '')));
                $i = 0;
                foreach ($lines as $line) {
                    $d->prepare("INSERT INTO $table (tool_id, $col, sort_order) VALUES (?,?,?)")->execute([$id, $line, ++$i]);
                }
            }

            /* тарифы */
            $d->prepare('DELETE FROM pricing_plans WHERE tool_id=?')->execute([$id]);
            $planNames = $_POST['plan_name'] ?? [];
            $planPrices = $_POST['plan_price'] ?? [];
            $i = 0;
            foreach ($planNames as $k => $pn) {
                $pn = trim($pn); $pp = trim($planPrices[$k] ?? '');
                if ($pn === '' && $pp === '') continue;
                $d->prepare('INSERT INTO pricing_plans (tool_id, plan_name, price, sort_order) VALUES (?,?,?,?)')->execute([$id, $pn, $pp, ++$i]);
            }

            /* FAQ */
            $d->prepare('DELETE FROM faqs WHERE tool_id=?')->execute([$id]);
            $faqQ = $_POST['faq_q'] ?? [];
            $faqA = $_POST['faq_a'] ?? [];
            $i = 0;
            foreach ($faqQ as $k => $q) {
                $q = trim($q); $a = trim($faqA[$k] ?? '');
                if ($q === '' || $a === '') continue;
                $d->prepare('INSERT INTO faqs (tool_id, question, answer, sort_order) VALUES (?,?,?,?)')->execute([$id, $q, $a, ++$i]);
            }

            /* альтернативы */
            $d->prepare('DELETE FROM alternatives WHERE tool_id=?')->execute([$id]);
            foreach (($_POST['alternatives'] ?? []) as $aid) {
                $aid = (int)$aid;
                if ($aid && $aid !== $id) {
                    $d->prepare('INSERT IGNORE INTO alternatives (tool_id, alternative_tool_id) VALUES (?,?)')->execute([$id, $aid]);
                }
            }

            if ($uploadWarnings) {
                $_SESSION['cv_upload_warn'] = $uploadWarnings;
                header('Location: tool-form.php?id=' . $id . '&msg=saved');
            } else {
                header('Location: index.php?msg=saved');
            }
            exit;
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate') ? 'Такой slug уже существует — укажи другой' : 'Ошибка сохранения: ' . $e->getMessage();
        }
    }
}

/* ---------- данные для формы ---------- */
$tool = ['name'=>'','slug'=>'','tagline'=>'','description'=>'','logo'=>'','hero_image'=>'','website_url'=>'','affiliate_link'=>'','editor_score'=>'','free_trial'=>0,'featured'=>0,'verified'=>0,'is_new'=>0,'is_popular'=>0];
$rel = ['badges'=>[],'features'=>[],'pros'=>[],'cons'=>[],'best_for'=>[],'pricing'=>[],'faqs'=>[],'categories'=>[],'alternatives'=>[]];

if ($id) {
    $st = $d->prepare('SELECT * FROM tools WHERE id=?');
    $st->execute([$id]);
    $found = $st->fetch();
    if (!$found) { header('Location: index.php'); exit; }
    $tool = $found;
    $rel = getToolRelations($id);
}

$allCats = getCategories();
$allTools = $d->query('SELECT id, name FROM tools ORDER BY name')->fetchAll();
$selectedCatSlugs = array_column($rel['categories'], 'slug');
$selectedAltIds = array_column($rel['alternatives'], 'id');
$title = $id ? 'Изменить: ' . $tool['name'] : 'Новый оффер';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title) ?> — панель управления</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<header class="admin-header">
  <div class="admin-container admin-header-inner">
    <a class="logo" href="index.php">Companion<span>Verse</span> <em>/ панель</em></a>
    <nav class="admin-nav">
      <a href="index.php">Офферы</a>
      <a href="categories.php">Категории</a>
      <a href="logout.php">Выйти</a>
    </nav>
  </div>
</header>

<main class="admin-container admin-form-page">
  <div class="admin-toolbar">
    <h1><?= esc($title) ?></h1>
    <a class="btn btn-ghost btn-sm" href="index.php">&larr; К списку</a>
  </div>

  <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= esc($error) ?></div><?php endif; ?>
  <?php if (($_GET['msg'] ?? '') === 'saved'): ?><div class="admin-alert admin-alert-ok">Оффер сохранён</div><?php endif; ?>
  <?php if (!empty($_SESSION['cv_upload_warn'])): foreach ($_SESSION['cv_upload_warn'] as $w): ?>
    <div class="admin-alert admin-alert-error">Картинка не сохранилась — <?= esc($w) ?></div>
  <?php endforeach; unset($_SESSION['cv_upload_warn']); endif; ?>

  <form method="post" enctype="multipart/form-data" class="admin-form">

    <fieldset>
      <legend>Основное</legend>
      <div class="grid-2">
        <label class="field"><span>Название *</span><input type="text" name="name" value="<?= esc($tool['name']) ?>" required></label>
        <label class="field"><span>Slug (URL, можно оставить пустым)</span><input type="text" name="slug" value="<?= esc($tool['slug']) ?>" placeholder="candy-ai"></label>
      </div>
      <label class="field"><span>Tagline — одно предложение</span><input type="text" name="tagline" value="<?= esc($tool['tagline']) ?>" placeholder="AI companion for realistic chat, images and voice conversations."></label>
      <label class="field"><span>Overview — 4–6 предложений человеческим языком</span><textarea name="description" rows="5"><?= esc($tool['description']) ?></textarea></label>
      <div class="grid-2">
        <label class="field"><span>Официальный сайт</span><input type="url" name="website_url" value="<?= esc($tool['website_url']) ?>" placeholder="https://candy.ai"></label>
        <label class="field"><span>Партнёрская ссылка (для кнопки Visit Website)</span><input type="url" name="affiliate_link" value="<?= esc($tool['affiliate_link']) ?>" placeholder="https://candy.ai/?ref=..."></label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Картинки</legend>
      <div class="grid-2">
        <div class="field">
          <span>Логотип — URL или файл</span>
          <input type="text" name="logo" value="<?= esc($tool['logo']) ?>" placeholder="https://... или uploads/...">
          <input type="file" name="logo_file" accept="image/*">
          <?php if ($tool['logo']): ?><img class="preview" src="<?= esc(imgUrl($tool['logo'])) ?>" alt=""><?php endif; ?>
        </div>
        <div class="field">
          <span>Скриншот (Hero) — URL или файл. Один хороший.</span>
          <input type="text" name="hero_image" value="<?= esc($tool['hero_image']) ?>" placeholder="https://... или uploads/...">
          <input type="file" name="hero_file" accept="image/*">
          <?php if ($tool['hero_image']): ?><img class="preview preview-wide" src="<?= esc(imgUrl($tool['hero_image'])) ?>" alt=""><?php endif; ?>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Trust-блок и метки</legend>
      <div class="grid-2">
        <label class="field"><span>Editor Score (0–10, напр. 9.2)</span><input type="number" step="0.1" min="0" max="10" name="editor_score" value="<?= esc($tool['editor_score']) ?>"></label>
        <div class="field checks">
          <span>Метки</span>
          <label><input type="checkbox" name="featured" <?= $tool['featured'] ? 'checked' : '' ?>> Featured (на главной)</label>
          <label><input type="checkbox" name="verified" <?= $tool['verified'] ? 'checked' : '' ?>> Verified</label>
          <label><input type="checkbox" name="is_popular" <?= $tool['is_popular'] ? 'checked' : '' ?>> Popular</label>
          <label><input type="checkbox" name="is_new" <?= $tool['is_new'] ? 'checked' : '' ?>> New</label>
          <label><input type="checkbox" name="free_trial" <?= $tool['free_trial'] ? 'checked' : '' ?>> Free Trial</label>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Категории</legend>
      <div class="checks checks-inline">
        <?php foreach ($allCats as $c): ?>
          <label><input type="checkbox" name="categories[]" value="<?= (int)$c['id'] ?>" <?= in_array($c['slug'], $selectedCatSlugs) ? 'checked' : '' ?>> <?= esc($c['name']) ?></label>
        <?php endforeach; ?>
      </div>
      <p class="hint">Добавить новую категорию можно на странице «Категории».</p>
    </fieldset>

    <fieldset>
      <legend>Списки — одна строка = один пункт</legend>
      <div class="grid-2">
        <label class="field"><span>Быстрые бейджи (6–10 шт)</span><textarea name="badges" rows="6" placeholder="AI Girlfriend&#10;Images&#10;Voice&#10;Roleplay&#10;Mobile&#10;Free Trial"><?= esc(implode("\n", $rel['badges'])) ?></textarea></label>
        <label class="field"><span>Key Features</span><textarea name="features" rows="6" placeholder="AI Chat&#10;Voice Calls&#10;Image Generation&#10;Memory"><?= esc(implode("\n", $rel['features'])) ?></textarea></label>
      </div>
      <div class="grid-2">
        <label class="field"><span>Плюсы</span><textarea name="pros" rows="4" placeholder="Excellent image quality&#10;Natural conversations"><?= esc(implode("\n", $rel['pros'])) ?></textarea></label>
        <label class="field"><span>Минусы</span><textarea name="cons" rows="4" placeholder="Subscription required&#10;Limited free messages"><?= esc(implode("\n", $rel['cons'])) ?></textarea></label>
      </div>
      <label class="field"><span>Best For</span><textarea name="best_for" rows="3" placeholder="Romantic conversations&#10;Roleplay&#10;Voice chat"><?= esc(implode("\n", $rel['best_for'])) ?></textarea></label>
    </fieldset>

    <fieldset>
      <legend>Тарифы (название / цена)</legend>
      <div id="pricing-rows">
        <?php $plans = $rel['pricing'] ?: [['plan_name'=>'','price'=>'']]; ?>
        <?php foreach ($plans as $p): ?>
        <div class="repeat-row">
          <input type="text" name="plan_name[]" value="<?= esc($p['plan_name']) ?>" placeholder="Premium Monthly">
          <input type="text" name="plan_price[]" value="<?= esc($p['price']) ?>" placeholder="$12.99 / mo">
          <button type="button" class="row-del" onclick="this.parentNode.remove()">&times;</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-ghost btn-sm" onclick="addPricingRow()">+ Добавить тариф</button>
    </fieldset>

    <fieldset>
      <legend>FAQ</legend>
      <div id="faq-rows">
        <?php $faqs = $rel['faqs'] ?: [['question'=>'','answer'=>'']]; ?>
        <?php foreach ($faqs as $f): ?>
        <div class="repeat-row repeat-row-faq">
          <input type="text" name="faq_q[]" value="<?= esc($f['question']) ?>" placeholder="Is it free?">
          <textarea name="faq_a[]" rows="2" placeholder="Ответ..."><?= esc($f['answer']) ?></textarea>
          <button type="button" class="row-del" onclick="this.parentNode.remove()">&times;</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-ghost btn-sm" onclick="addFaqRow()">+ Добавить вопрос</button>
    </fieldset>

    <?php if ($allTools): ?>
    <fieldset>
      <legend>Альтернативы (3–6 карточек)</legend>
      <div class="checks checks-inline">
        <?php foreach ($allTools as $t): if ((int)$t['id'] === $id) continue; ?>
          <label><input type="checkbox" name="alternatives[]" value="<?= (int)$t['id'] ?>" <?= in_array((int)$t['id'], $selectedAltIds) ? 'checked' : '' ?>> <?= esc($t['name']) ?></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <?php endif; ?>

    <div class="form-footer">
      <button class="btn btn-primary btn-lg" type="submit">Сохранить оффер</button>
      <a class="btn btn-ghost" href="index.php">Отмена</a>
    </div>
  </form>
</main>

<script>
function addPricingRow() {
  var d = document.createElement('div');
  d.className = 'repeat-row';
  d.innerHTML = '<input type="text" name="plan_name[]" placeholder="Premium Monthly">' +
    '<input type="text" name="plan_price[]" placeholder="$12.99 / mo">' +
    '<button type="button" class="row-del" onclick="this.parentNode.remove()">&times;</button>';
  document.getElementById('pricing-rows').appendChild(d);
}
function addFaqRow() {
  var d = document.createElement('div');
  d.className = 'repeat-row repeat-row-faq';
  d.innerHTML = '<input type="text" name="faq_q[]" placeholder="Is it free?">' +
    '<textarea name="faq_a[]" rows="2" placeholder="Ответ..."></textarea>' +
    '<button type="button" class="row-del" onclick="this.parentNode.remove()">&times;</button>';
  document.getElementById('faq-rows').appendChild(d);
}
</script>
</body>
</html>
