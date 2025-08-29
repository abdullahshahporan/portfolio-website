<?php
/* Project Edit — DB: potfolio_db, table: project (no delete button) */
header('X-Content-Type-Options: nosniff');

$DB_HOST='localhost'; $DB_NAME='portfolio_db'; $DB_USER='root'; $DB_PASS='';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

try{
  $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
}catch(Throwable $e){ http_response_code(500); exit("DB error: ".esc($e->getMessage())); }

$id=(int)($_GET['id'] ?? 0);
if($id<=0){ header('Location: /portfolio/admin/project_render.php?manage=1'); exit; }

$st=$pdo->prepare("SELECT * FROM project WHERE id=?"); $st->execute([$id]); $row=$st->fetch();
if(!$row){ header('Location: /portfolio/admin/project_render.php?manage=1'); exit; }

$ok=$err='';

function save_video_if_uploaded(&$err){
  if(!isset($_FILES['video']) || $_FILES['video']['error']===UPLOAD_ERR_NO_FILE) return null;
  if($_FILES['video']['error']!==UPLOAD_ERR_OK){ $err="Video upload error."; return null; }
  $allowed=['video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv','video/ogv'=>'ogv'];
  $finfo=finfo_open(FILEINFO_MIME_TYPE);
  $mime=finfo_file($finfo,$_FILES['video']['tmp_name']); finfo_close($finfo);
  if(!isset($allowed[$mime])){ $err="Video must be MP4/WEBM/OGG."; return null; }
  $baseDir=__DIR__.'/upload/project_videos/'; @mkdir($baseDir,0777,true);
  $name='proj_'.time().'_'.mt_rand(1000,9999).'.'.$allowed[$mime];
  $rel='upload/project_videos/'.$name; $dest=$baseDir.$name;
  if(!move_uploaded_file($_FILES['video']['tmp_name'],$dest)){ $err="Failed to save new video."; return null; }
  return $rel;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $title       = trim($_POST['title'] ?? '');
  $link_url    = trim($_POST['link_url'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $sort_order  = (int)($_POST['sort_order'] ?? 0);
  $is_active   = isset($_POST['is_active']) ? 1 : 0;

  if($title===''){ $err="Title is required."; }

  $newVideoRel=$row['video_path'];
  if(!$err){
    $maybe=save_video_if_uploaded($err);
    if($maybe) $newVideoRel=$maybe;
  }
  if(!$err){
    $pdo->prepare("UPDATE project SET title=?, description=?, link_url=?, video_path=?, sort_order=?, is_active=? WHERE id=?")
        ->execute([$title,$description,$link_url,$newVideoRel,$sort_order,$is_active,$id]);

    $st->execute([$id]); $row=$st->fetch();
    $ok="Updated!";
  }
}

$videoAbs='/portfolio/admin/'.ltrim($row['video_path'],'/');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit Project</title>
<style>
  body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
  .wrap{max-width:820px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
  h1{margin:0 0 16px;font-size:24px}
  .row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
  input,textarea,button{background:#101a2d;color:#eaf1ff;border:1px solid #22304a;border-radius:10px;padding:12px;width:100%}
  label{display:block;opacity:.9;margin-bottom:6px}
  .half{flex:1 1 320px}
  .ok{color:#6f6}.err{color:#ff6}.note{opacity:.8} a{color:#00bcd4}
  .preview{display:block}
  .preview video{width:100%; max-width:480px; border:1px solid #22304a; border-radius:10px; display:block}
  .actions{display:flex;gap:10px;flex-wrap:wrap}
  button{cursor:pointer} button:hover{border-color:#00bcd4; box-shadow:0 10px 30px rgba(0,188,212,.12)}
</style></head><body>
  <div class="wrap">
    <h1>Edit Project</h1>
    <p class="note"><a href="/portfolio/admin/project_render.php?manage=1">Back to list</a> • <a href="/portfolio/index.html">View site</a></p>

    <?php if($ok): ?><p class="ok"><?= esc($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="err"><?= esc($err) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="half"><label>Title *</label><input name="title" value="<?= esc($row['title']) ?>" required></div>
        <div class="half"><label>Project Link (URL)</label><input name="link_url" value="<?= esc($row['link_url']) ?>" placeholder="https://..."></div>
      </div>
      <div class="row"><label>Description</label><textarea name="description" rows="4"><?= esc($row['description']) ?></textarea></div>
      <div class="row">
        <div class="half"><label>Replace Video (MP4/WEBM/OGG) — optional</label><input type="file" name="video" accept="video/mp4,video/webm,video/ogg"></div>
        <div class="half preview"><label>Current Video</label><video src="<?= esc($videoAbs) ?>" controls preload="metadata" playsinline muted></video></div>
      </div>
      <div class="row">
        <div class="half"><label>Sort Order</label><input type="number" name="sort_order" value="<?= esc($row['sort_order']) ?>"></div>
        <div class="half" style="display:flex;align-items:flex-end"><label style="display:flex;align-items:center;gap:.6rem"><input type="checkbox" name="is_active" <?= $row['is_active'] ? 'checked' : '' ?>> Active</label></div>
      </div>
      <div class="row actions">
        <button type="submit">Save Changes</button>
        <a href="/portfolio/admin/project_render.php?manage=1" class="btn" style="padding:12px;border:1px solid #22304a;border-radius:10px;text-decoration:none;color:#eaf1ff;background:#101a2d">Cancel</a>
      </div>
    </form>
  </div>
</body></html>
