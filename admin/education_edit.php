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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /portfolio/admin/education_render.php?manage=1'); exit; }

$stmt = $pdo->prepare("SELECT * FROM education WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: /portfolio/admin/education_render.php?manage=1'); exit; }

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $institution = trim($_POST['institution'] ?? '');
  $degree      = trim($_POST['degree'] ?? '');
  $field       = trim($_POST['field'] ?? '');
  $start_year  = ($_POST['start_year'] ?? '') !== '' ? $_POST['start_year'] : null;
  $end_year    = ($_POST['end_year'] ?? '')   !== '' ? $_POST['end_year']   : null;
  $description = trim($_POST['description'] ?? '');
  $sort_order  = (int)($_POST['sort_order'] ?? 0);
  $is_active   = isset($_POST['is_active']) ? 1 : 0;

  if ($institution === '' || $degree === '') { $err = "Institution and Degree are required."; }

  // optional new logo
  $newLogoRel = $row['logo_path'];
  if (!$err && isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
      $err = "Logo upload error.";
    } else {
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $_FILES['logo']['tmp_name']); finfo_close($finfo);
      if (!isset($allowed[$mime])) {
        $err = "Logo must be JPG/PNG/WEBP.";
      } else {
        $baseDir = __DIR__ . '/upload/education_logos/';
        @mkdir($baseDir, 0777, true);
        $ext  = $allowed[$mime];
        $name = 'edu_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
        $rel  = 'upload/education_logos/' . $name;
        $dest = __DIR__ . '/' . $rel;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
          $err = "Failed to save new logo.";
        } else {
          // delete old file safely
          $old = __DIR__ . '/' . $row['logo_path'];
          if (is_file($old)) {
            $base = realpath(__DIR__ . '/upload/education_logos');
            $real = realpath($old);
            if ($real && strpos($real, $base) === 0) @unlink($real);
          }
          $newLogoRel = $rel;
        }
      }
    }
  }

  if (!$err) {
    $stmtU = $pdo->prepare("UPDATE education
      SET institution=?, degree=?, field=?, logo_path=?, start_year=?, end_year=?, description=?, sort_order=?, is_active=?
      WHERE id=?");
    $stmtU->execute([$institution, $degree, $field, $newLogoRel, $start_year, $end_year, $description, $sort_order, $is_active, $id]);

    // reload row
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $ok = "Updated!";
  }
}

$logoAbs = '/portfolio/admin/' . ltrim($row['logo_path'],'/');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Education</title>
  <style>
    body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
    .wrap{max-width:820px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
    h1{margin:0 0 16px;font-size:24px}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
    input,textarea,button{background:#101a2d;color:#eaf1ff;border:1px solid #22304a;border-radius:10px;padding:12px;width:100%}
    label{display:block;opacity:.9;margin-bottom:6px}
    .half{flex:1 1 320px}
    .ok{color:#6f6}
    .err{color:#ff6}
    .note{opacity:.8}
    a{color:#00bcd4}
    .preview{display:flex;align-items:center;gap:12px}
    .preview img{width:84px;height:84px;object-fit:cover;border-radius:10px;border:1px solid #22304a;background:#101a2d}
    .actions{display:flex;gap:10px;flex-wrap:wrap}
    button{cursor:pointer}
    button:hover{border-color:#00bcd4; box-shadow:0 10px 30px rgba(0,188,212,.12)}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Edit Education</h1>
    <p class="note">
      <a href="/portfolio/admin/education_render.php?manage=1">Back to list</a> •
      <a href="/portfolio/index.html">View site</a>
    </p>

    <?php if($ok): ?><p class="ok"><?= esc($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="err"><?= esc($err) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="half">
          <label>Institution *</label>
          <input name="institution" value="<?= esc($row['institution']) ?>" required>
        </div>
        <div class="half">
          <label>Degree *</label>
          <input name="degree" value="<?= esc($row['degree']) ?>" required>
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Field (optional)</label>
          <input name="field" value="<?= esc($row['field']) ?>">
        </div>
        <div class="half">
          <label>Replace Logo (JPG/PNG/WEBP) — optional</label>
          <div class="preview">
            <img src="<?= esc($logoAbs) ?>" alt="current logo">
            <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Start Year</label>
          <input type="number" name="start_year" value="<?= esc($row['start_year']) ?>">
        </div>
        <div class="half">
          <label>End Year (empty = Present)</label>
          <input type="number" name="end_year" value="<?= esc($row['end_year']) ?>">
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Sort Order</label>
          <input type="number" name="sort_order" value="<?= esc($row['sort_order']) ?>">
        </div>
        <div class="half" style="display:flex;align-items:flex-end">
          <label style="display:flex;align-items:center;gap:.6rem">
            <input type="checkbox" name="is_active" <?= $row['is_active'] ? 'checked' : '' ?>> Active
          </label>
        </div>
      </div>

      <div class="row">
        <label>Description</label>
        <textarea name="description" rows="4"><?= esc($row['description']) ?></textarea>
      </div>

      <div class="row actions">
        <button type="submit">Save Changes</button>
        <a href="/portfolio/admin/education_delete.php?id=<?= (int)$id ?>" style="padding:12px;border:1px solid #532; border-radius:10px; background:#251; color:#fff; text-decoration:none"
           onclick="return confirm('Delete this row permanently?');">Delete…</a>
      </div>
    </form>
  </div>
</body>
</html>
