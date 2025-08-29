<?php
/* Project Delete (confirm + delete) — DB: potfolio_db, table: project */
header('X-Content-Type-Options: nosniff');

$DB_HOST='localhost'; $DB_NAME='portfolio_db'; $DB_USER='root'; $DB_PASS='';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

try{
  $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
}catch(Throwable $e){ http_response_code(500); exit("DB error: ".esc($e->getMessage())); }

function safe_delete_video($rel){
  if(!$rel) return;
  $file = __DIR__ . '/' . $rel;
  if(is_file($file)){
    $base = realpath(__DIR__ . '/upload/project_videos');
    $real = realpath($file);
    if($real && strpos($real,$base)===0) @unlink($real);
  }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id'] ?? 0);
  if($id>0){
    $st=$pdo->prepare("SELECT video_path FROM project WHERE id=?");
    $st->execute([$id]);
    if($row=$st->fetch()){
      safe_delete_video($row['video_path']);
      $pdo->prepare("DELETE FROM project WHERE id=?")->execute([$id]);
    }
  }
  header("Location: /portfolio/admin/project_render.php?manage=1"); exit;
}

$id=(int)($_GET['id'] ?? 0);
if($id<=0){ header("Location: /portfolio/admin/project_render.php?manage=1"); exit; }

$st=$pdo->prepare("SELECT * FROM project WHERE id=?"); $st->execute([$id]); $row=$st->fetch();
if(!$row){ header("Location: /portfolio/admin/project_render.php?manage=1"); exit; }

$videoAbs='/portfolio/admin/'.ltrim($row['video_path'],'/');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Delete Project</title>
<style>
  body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
  .wrap{max-width:680px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
  h1{margin:0 0 12px;font-size:24px}
  .card{display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:flex-start;margin:16px 0}
  .card video{width:100%; aspect-ratio:16/9; object-fit:cover; border:1px solid #22304a; border-radius:10px}
  .note{opacity:.85}
  .actions{display:flex;gap:12px;margin-top:16px}
  button,a.btn{background:#a11;border:1px solid #c44;color:#fff;border-radius:10px;padding:12px 14px;text-decoration:none;cursor:pointer}
  button:hover,a.btn:hover{filter:brightness(1.05)}
  a.link{color:#00bcd4}
</style></head><body>
  <div class="wrap">
    <h1>Delete Project</h1>
    <p class="note">This action cannot be undone.</p>

    <div class="card">
      <video src="<?= esc($videoAbs) ?>" controls preload="metadata" playsinline muted></video>
      <div>
        <div><strong><?= esc($row['title']) ?></strong></div>
        <?php if(!empty($row['link_url'])): ?>
          <div>Link: <a class="link" target="_blank" rel="noopener" href="<?= esc($row['link_url']) ?>"><?= esc($row['link_url']) ?></a></div>
        <?php endif; ?>
        <?php if(!empty($row['description'])): ?>
          <div><?= esc($row['description']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <form method="post" class="actions" onsubmit="return confirm('Really delete this project?');">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <button type="submit">Delete permanently</button>
      <a class="btn" href="/portfolio/admin/project_render.php?manage=1">Cancel</a>
    </form>
  </div>
</body></html>
