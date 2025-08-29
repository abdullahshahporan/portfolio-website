<?php

header('X-Content-Type-Options: nosniff');

$DB_HOST = 'localhost';
$DB_NAME = 'portfolio_db';
$DB_USER = 'root';
$DB_PASS = '';

// Ensure DB / table
try {
  $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
  $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
  $pdo->exec("USE `$DB_NAME`");
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS education (
      id INT AUTO_INCREMENT PRIMARY KEY,
      institution VARCHAR(150) NOT NULL,
      degree VARCHAR(150) NOT NULL,
      field VARCHAR(150) DEFAULT NULL,
      logo_path VARCHAR(255) NOT NULL,
      start_year YEAR DEFAULT NULL,
      end_year YEAR DEFAULT NULL,
      description TEXT DEFAULT NULL,
      sort_order INT DEFAULT 0,
      is_active TINYINT(1) DEFAULT 1,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  ");
} catch (Throwable $e) {
  http_response_code(500);
  exit("DB error: " . htmlspecialchars($e->getMessage()));
}

$ok = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $institution = trim($_POST['institution'] ?? '');
  $degree      = trim($_POST['degree'] ?? '');
  $field       = trim($_POST['field'] ?? '');
  $start_year  = $_POST['start_year'] !== '' ? $_POST['start_year'] : null;
  $end_year    = $_POST['end_year']   !== '' ? $_POST['end_year']   : null;
  $description = trim($_POST['description'] ?? '');
  $sort_order  = (int)($_POST['sort_order'] ?? 0);
  $is_active   = isset($_POST['is_active']) ? 1 : 1;

  if ($institution === '' || $degree === '') $err = "Institution and Degree are required.";
  if (!$err && (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK)) $err = "Logo file is required.";

  $relPath = '';
  if (!$err) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['logo']['tmp_name']); finfo_close($finfo);
    if (!isset($allowed[$mime])) $err = "Logo must be JPG/PNG/WEBP.";
  }
  if (!$err) {
    $baseDir = __DIR__ . '/upload/education_logos/';
    @mkdir($baseDir, 0777, true);
    $ext  = $allowed[$mime];
    $name = 'edu_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
    $relPath = 'upload/education_logos/' . $name;   // relative to /portfolio/admin
    $dest = __DIR__ . '/' . $relPath;
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) $err = "Failed to save logo.";
  }
  if (!$err) {
    try {
      $stmt = $pdo->prepare("INSERT INTO education
         (institution, degree, field, logo_path, start_year, end_year, description, sort_order, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$institution, $degree, $field, $relPath, $start_year, $end_year, $description, $sort_order, $is_active]);
      $ok = "Saved!";
    } catch (Throwable $e) {
      $err = "DB insert failed.";
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Education Upload</title>
  <style>
    body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
    .wrap{max-width:780px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
    h1{margin:0 0 16px;font-size:24px}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
    input,textarea,button{background:#101a2d;color:#eaf1ff;border:1px solid #22304a;border-radius:10px;padding:12px;width:100%}
    label{display:block;opacity:.9;margin-bottom:6px}
    .half{flex:1 1 300px}
    .ok{color:#6f6}
    .err{color:#ff6}
    .note{opacity:.8}
    a{color:#00bcd4}
    button{cursor:pointer}
    button:hover{border-color:#00bcd4; box-shadow:0 10px 30px rgba(0,188,212,.12)}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Education Upload</h1>
    <p class="note">Back to site: <a href="/portfolio/index.html">Potfolio</a></p>
    <p class="view">View List: <a href="/portfolio/admin/education_edit.php">Manage Education</a></p>

    <?php if($ok): ?><p class="ok"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="err"><?= htmlspecialchars($err) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="half">
          <label>Institution *</label>
          <input name="institution" required>
        </div>
        <div class="half">
          <label>Degree *</label>
          <input name="degree" required>
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Field (optional)</label>
          <input name="field" placeholder="e.g., Computer Science">
        </div>
        <div class="half">
          <label>Logo (JPG/PNG/WEBP) *</label>
          <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp" required>
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Start Year</label>
          <input type="number" name="start_year" placeholder="e.g., 2022">
        </div>
        <div class="half">
          <label>End Year (empty = Present)</label>
          <input type="number" name="end_year">
        </div>
      </div>

      <div class="row">
        <div class="half">
          <label>Sort Order (0 default)</label>
          <input type="number" name="sort_order" value="0">
        </div>
        <div class="half" style="display:flex;align-items:flex-end">
          <label style="display:flex;align-items:center;gap:.6rem"><input type="checkbox" name="is_active" checked> Active</label>
        </div>
      </div>

      <div class="row">
        <label>Description (optional)</label>
        <textarea name="description" rows="4" placeholder="Short description"></textarea>
      </div>

      <div class="row">
        <button type="submit">Save</button>
      </div>
    </form>

    
  </div>
</body>
</html>

