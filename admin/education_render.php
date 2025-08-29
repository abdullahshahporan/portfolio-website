<?php


header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$DB_HOST = 'localhost';
$DB_NAME = 'portfolio_db';
$DB_USER = 'root';      // adjust if needed
$DB_PASS = '';          // adjust if needed

// ---------- DB connect (simple, no create-table here) ----------
try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (Throwable $e) {
  http_response_code(500);
  echo '<div style="padding:16px;color:#fff;background:#b33">DB connection failed.</div>';
  exit;
}

// ---------- Fetch rows ----------
try {
  $rows = $pdo->query(
    "SELECT id, institution, degree, field, logo_path, start_year, end_year, description, sort_order, is_active
     FROM education
     ORDER BY sort_order ASC, id ASC"
  )->fetchAll();
} catch (Throwable $e) {
  $rows = [];
}

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Manage mode: show a page with a simple list + edit/delete

if (isset($_GET['manage'])): ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Education</title>
  <style>
    body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0}
    .wrap{max-width:980px;margin:40px auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
    h1{margin:0 0 16px;font-size:26px}
    .topbar{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px}
    .topbar a{color:#00bcd4;text-decoration:none}
    .empty{padding:18px;border:1px dashed rgba(255,255,255,.25);border-radius:10px;color:#cfe1ff}
    .list{display:grid;gap:10px;margin-top:10px}
    .row{display:grid;grid-template-columns:72px 1fr auto;gap:12px;align-items:center;background:#101a2d;border:1px solid #22304a;border-radius:12px;padding:10px}
    .row img{width:72px;height:72px;object-fit:cover;border-radius:10px;border:1px solid #22304a;background:#0e1725}
    .title{font-weight:800;margin:0 0 2px}
    .meta{opacity:.95}
    .actions{display:flex;gap:8px}
    .btn{display:inline-block;padding:10px 12px;border-radius:10px;text-decoration:none;color:#fff;border:1px solid transparent}
    .btn.edit{background:#1a6;border-color:#2a7}
    .btn.delete{background:#a33;border-color:#c55}
    .btn:hover{filter:brightness(1.05)}
    .badge{display:inline-block;margin-left:8px;padding:2px 8px;border-radius:999px;background:#26344d;border:1px solid #3a4f75;color:#cfe1ff;font-size:.85rem}
    .muted{opacity:.8}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Manage Education</h1>
    <div class="topbar">
      <a href="/portfolio/admin/education_upload.php">+ Add new</a>
      <span class="muted">•</span>
      <a href="/portfolio/index.html">View site</a>
    </div>

    <?php if (!$rows): ?>
      <div class="empty">No entries yet. Click <a href="/portfolio/admin/education_upload.php">Add new</a>.</div>
    <?php else: ?>
      <div class="list">
        <?php foreach ($rows as $e):
          $logoAbs = '/portfolio/admin/' . ltrim($e['logo_path'], '/');
          $years = ($e['start_year'] ?: '') . ' - ' . (is_null($e['end_year']) ? 'Present' : $e['end_year']);
          $field = $e['field'] ? ' — ' . $e['field'] : '';
          $active = $e['is_active'] ? '' : '<span class="badge">inactive</span>';
        ?>
          <div class="row">
            <img src="<?= esc($logoAbs) ?>" alt="<?= esc($e['institution']) ?> logo">
            <div>
              <div class="title"><?= esc($e['institution']) ?> <?= $active ?></div>
              <div class="meta"><?= esc($e['degree']) ?><?= esc($field) ?></div>
              <div class="meta"><?= esc($years) ?></div>
            </div>
            <div class="actions">
              <a class="btn edit"   href="/portfolio/admin/education_edit.php?id=<?= (int)$e['id'] ?>">Edit</a>
              <a class="btn delete" href="/portfolio/admin/education_delete.php?id=<?= (int)$e['id'] ?>"
                 onclick="return confirm('Delete this entry?');">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
<?php

exit;
endif;


// Public mode: return card HTML for the homepage to inject

if (!$rows) {
  echo '<div class="empty">No education added yet. Use <code>/portfolio/admin/education_upload.php</code> to add.</div>';
  exit;
}

foreach ($rows as $e) {
  if (!$e['is_active']) continue; // skip inactive in public view
  $logoAbs = '/portfolio/admin/' . ltrim($e['logo_path'], '/');
  $years = ($e['start_year'] ?: '') . ' - ' . (is_null($e['end_year']) ? 'Present' : $e['end_year']);
  $field = $e['field'] ? ' — '. $e['field'] : '';
  $desc  = $e['description'] ? '<div class="edu-line">'.esc($e['description']).'</div>' : '';

  echo '<article class="edu-card reveal visible">
          <img class="edu-logo" src="'.esc($logoAbs).'" alt="'.esc($e['institution']).' logo">
          <div>
            <div class="edu-title">'.esc($e['institution']).'</div>
            <div class="edu-line">'.esc($e['degree']).esc($field).'</div>
            <div class="edu-line">'.esc($years).'</div>'.
            $desc .
          '</div>
        </article>' . "\n";
}
