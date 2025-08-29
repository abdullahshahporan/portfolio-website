<?php
/* Projects renderer — DB: potfolio_db, table: project */
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

$DB_HOST='localhost'; $DB_NAME='portfolio_db'; $DB_USER='root'; $DB_PASS='';

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

try{
  $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
}catch(Throwable $e){
  http_response_code(500);
  echo '<div class="empty">DB connection failed: '.esc($e->getMessage()).'</div>'; exit;
}

/* Manage list */
if(isset($_GET['manage'])){
  try{
    $rows=$pdo->query("SELECT id,title,description,link_url,video_path,sort_order,is_active
                       FROM project ORDER BY sort_order ASC,id ASC")->fetchAll();
  }catch(Throwable $e){ $rows=[]; }
  ?>
  <!doctype html><html><head><meta charset="utf-8"><title>Manage Projects</title>
  <style>
    body{background:#0a0f18;color:#eaf1ff;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0}
    .wrap{max-width:1000px;margin:36px auto;background:#0f1624;padding:22px;border-radius:14px;border:1px solid rgba(255,255,255,.08)}
    h1{margin:0 0 14px;font-size:24px}
    .top{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .btn-top{display:inline-block;padding:10px 14px;border-radius:10px;text-decoration:none;border:1px solid #2a7;background:#1a6;color:#fff}
    .btn-view{display:inline-block;padding:10px 14px;border-radius:10px;text-decoration:none;border:1px solid #345;background:#22314a;color:#eaf1ff}
    .list{display:grid;gap:12px}
    .card{display:grid;grid-template-columns:220px 1fr auto;gap:14px;align-items:center;background:#101a2d;border:1px solid #22304a;border-radius:12px;padding:12px}
    .thumb{width:220px; aspect-ratio:16/9; background:#0e1725; border:1px solid #22304a; border-radius:10px; overflow:hidden}
    .thumb video{width:100%; height:100%; object-fit:cover; display:block}
    .title{font-weight:800;margin-bottom:2px}
    .meta{opacity:.95}
    .desc{opacity:.9;margin-top:6px}
    .badge{display:inline-block;margin-left:8px;padding:2px 8px;border-radius:999px;background:#26344d;border:1px solid #3a4f75;color:#cfe1ff;font-size:.85rem}
    .actions{display:flex;gap:8px}
    .btn{display:inline-block;padding:10px 12px;border-radius:10px;text-decoration:none;color:#fff;border:1px solid transparent;cursor:pointer}
    .edit{background:#1a6;border-color:#2a7}
    .delete{background:#a33;border-color:#c55}
    .btn:hover{filter:brightness(1.05)}
    .empty{padding:16px;border:1px dashed rgba(255,255,255,.25);border-radius:10px;color:#cfe1ff}
    a.link{color:#00bcd4}
  </style></head><body>
    <div class="wrap">
      <h1>Manage Projects</h1>
      <div class="top">
        <a class="btn-top" href="/portfolio/admin/project_upload.php">+ Add New Entry</a>
        <a class="btn-view" href="/portfolio/index.html">View Site</a>
      </div>

      <?php if(!$rows): ?>
        <div class="empty">No projects yet. Click <strong>+ Add New Entry</strong>.</div>
      <?php else: ?>
        <div class="list">
          <?php foreach($rows as $p):
            $video = '/portfolio/admin/'.ltrim($p['video_path'],'/');
            $inactive = $p['is_active'] ? '' : '<span class="badge">inactive</span>';
          ?>
          <div class="card">
            <div class="thumb"><video src="<?= esc($video) ?>" preload="metadata" playsinline muted controls></video></div>
            <div>
              <div class="title"><?= esc($p['title']) ?> <?= $inactive ?></div>
              <?php if(!empty($p['link_url'])): ?>
                <div class="meta">Link: <a class="link" href="<?= esc($p['link_url']) ?>" target="_blank" rel="noopener"><?= esc($p['link_url']) ?></a></div>
              <?php endif; ?>
              <?php if(!empty($p['description'])): ?><div class="desc"><?= esc($p['description']) ?></div><?php endif; ?>
            </div>
            <div class="actions">
              <a class="btn edit" href="/portfolio/admin/project_edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
              <a class="btn delete" href="/portfolio/admin/project_delete.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this project?');">Delete</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </body></html>
  <?php exit; }

/* Public cards (homepage) */
try{
  $rows=$pdo->query("SELECT title,description,link_url,video_path,is_active
                     FROM project ORDER BY sort_order ASC,id ASC")->fetchAll();
}catch(Throwable $e){ $rows=[]; }

if(!$rows){ echo '<div class="empty">No projects added yet.</div>'; exit; }

foreach($rows as $p){
  if(!$p['is_active']) continue;
  $video='/portfolio/admin/'.ltrim($p['video_path'],'/');
  $desc = $p['description'] ? '<div class="project-desc">'.esc($p['description']).'</div>' : '';
  $btn  = !empty($p['link_url'])
    ? '<a class="btn primary project-btn" href="'.esc($p['link_url']).'" target="_blank" rel="noopener">Visit Project</a>'
    : '';
  echo '<article class="project-card reveal visible">
          <div class="project-media"><video src="'.esc($video).'" preload="metadata" playsinline muted controls></video></div>
          <div class="project-body">
            <div class="project-title">'.esc($p['title']).'</div>'.$desc.$btn.'
          </div>
        </article>'."\n";
}
