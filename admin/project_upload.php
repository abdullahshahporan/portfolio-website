<?php
/* Project Upload (Create) — DB: potfolio_db, table: project */
header('X-Content-Type-Options: nosniff');

$DB_HOST='localhost'; $DB_NAME='portfolio_db'; $DB_USER='root'; $DB_PASS='';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

try{
  $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
}catch(Throwable $e){
  http_response_code(500);
  exit("DB error: ".esc($e->getMessage()));
}

$ok=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $title       = trim($_POST['title'] ?? '');
  $link_url    = trim($_POST['link_url'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $sort_order  = (int)($_POST['sort_order'] ?? 0);
  $is_active   = isset($_POST['is_active']) ? 1 : 1;

  if($title==='') $err='Title is required.';
  if(!$err && (!isset($_FILES['video']) || $_FILES['video']['error']!==UPLOAD_ERR_OK)) $err='Video is required.';

  $relPath='';
  if(!$err){
    $allowed=['video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv','video/ogv'=>'ogv'];
    $finfo=finfo_open(FILEINFO_MIME_TYPE);
    $mime=finfo_file($finfo,$_FILES['video']['tmp_name']); finfo_close($finfo);
    if(!isset($allowed[$mime])) $err='Video must be MP4/WEBM/OGG.';
  }
  if(!$err){
    $baseDir=__DIR__.'/upload/project_videos/'; @mkdir($baseDir,0777,true);
    $name='proj_'.time().'_'.mt_rand(1000,9999).'.'.$allowed[$mime];
    $relPath='upload/project_videos/'.$name; $dest=$baseDir.$name;
    if(!move_uploaded_file($_FILES['video']['tmp_name'],$dest)) $err='Failed to save video.';
  }
  if(!$err){
    try{
      $pdo->prepare("INSERT INTO project (title,description,link_url,video_path,sort_order,is_active)
                     VALUES (?,?,?,?,?,?)")
          ->execute([$title,$description,$link_url,$relPath,$sort_order,$is_active]);
      $ok='Saved!';
    }catch(Throwable $e){
      $err='DB insert failed: '.esc($e->getMessage());
    }
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Project Upload</title>
<style>
  body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:40px}
  .wrap{max-width:780px;margin:0 auto;background:#0f1624;padding:24px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
  h1{margin:0 0 16px;font-size:24px}
  .row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
  input,textarea,button{background:#101a2d;color:#eaf1ff;border:1px solid #22304a;border-radius:10px;padding:12px;width:100%}
  label{display:block;opacity:.9;margin-bottom:6px}
  .half{flex:1 1 300px}
  .ok{color:#6f6}.err{color:#ff6}.note{opacity:.8} a{color:#00bcd4}
  button{cursor:pointer} button:hover{border-color:#00bcd4; box-shadow:0 10px 30px rgba(0,188,212,.12)}
</style></head><body>
  <div class="wrap">
    <h1>Project Upload</h1>
    <p class="note">
      Manage: <a href="/portfolio/admin/project_render.php?manage=1">List</a> •
      View site: <a href="/portfolio/index.html">Portfolio</a>
    </p>
    <?php if($ok): ?><p class="ok"><?= esc($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="err"><?= esc($err) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="half"><label>Title *</label><input name="title" required></div>
        <div class="half"><label>Project Link (URL)</label><input name="link_url" placeholder="https://..."></div>
      </div>
      <div class="row"><label>Description</label><textarea name="description" rows="4"></textarea></div>
      <div class="row">
        <div class="half"><label>Video (MP4/WEBM/OGG) *</label><input type="file" name="video" accept="video/mp4,video/webm,video/ogg" required></div>
        <div class="half"><label>Sort Order (0 default)</label><input type="number" name="sort_order" value="0"></div>
      </div>
      <div class="row"><label style="display:flex;align-items:center;gap:.6rem"><input type="checkbox" name="is_active" checked> Active</label></div>
      <div class="row"><button type="submit">Save</button></div>
    </form>
  </div>
</body></html>

