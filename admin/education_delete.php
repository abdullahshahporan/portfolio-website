<?php


header('X-Content-Type-Options: nosniff');

$DB_HOST = 'localhost';
$DB_NAME = 'portfolio_db';
$DB_USER = 'root';
$DB_PASS = '';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---------- DB connect ----------
try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  exit("DB error: ".esc($e->getMessage()));
}

// If POST → delete and redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id > 0) {
    $stmt = $pdo->prepare("SELECT logo_path FROM education WHERE id=?");
    $stmt->execute([$id]);
    if ($row = $stmt->fetch()) {
      // delete file
      $file = __DIR__ . '/' . $row['logo_path'];
      if (is_file($file)) {
        $base = realpath(__DIR__ . '/upload/education_logos');
        $real = realpath($file);
        if ($real && strpos($real, $base) === 0) @unlink($real);
      }
      // delete db
      $pdo->prepare("DELETE FROM education WHERE id=?")->execute([$id]);
    }
  }
  header("Location: /portfolio/admin/education_render.php?manage=1");
  exit;
}

// If GET → simple confirm page
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: /portfolio/admin/education_render.php?manage=1"); exit; }

$stmt = $pdo->prepare("SELECT * FROM education WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header("Location: /portfolio/admin/education_render.php?manage=1"); exit; }

$logoAbs = '/portfolio/admin/' . ltrim($row['logo_path'],'/');
$years = ($row['start_year'] ?: '') . ' - ' . (is_null($row['end_year']) ? 'Present' : $row['end_year']);
$field = $row['field'] ? ' — ' . $row['field'] : '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Delete Education</title>
  <style>
    body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
    .wrap{max-width:680px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
    h1{margin:0 0 12px;font-size:24px}
    .card{display:flex;gap:14px;align-items:flex-start;margin:16px 0}
    .card img{width:84px;height:84px;object-fit:cover;border-radius:10px;border:1px solid #22304a;background:#101a2d}
    .note{opacity:.85}
    .actions{display:flex;gap:12px;margin-top:16px}
    button,a.btn{background:#a11;border:1px solid #c44;color:#fff;border-radius:10px;padding:12px 14px;text-decoration:none;cursor:pointer}
    button:hover,a.btn:hover{filter:brightness(1.05)}
    a.link{color:#00bcd4}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Delete Education</h1>
    <p class="note">This action cannot be undone.</p>

    <div class="card">
      <img src="<?= esc($logoAbs) ?>" alt="logo">
      <div>
        <div><strong><?= esc($row['institution']) ?></strong></div>
        <div><?= esc($row['degree']) ?><?= esc($field) ?></div>
        <div><?= esc($years) ?></div>
      </div>
    </div>

    <form method="post" class="actions" onsubmit="return confirm('Really delete this record?');">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <button type="submit">Delete permanently</button>
      <a class="btn" href="/portfolio/admin/education_render.php?manage=1">Cancel</a>
    </form>
  </div>
</body>
</html>
