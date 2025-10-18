<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) header('Location: index.php');

$totalFiles = $mysqli->query('SELECT COUNT(*) AS c FROM documents')->fetch_assoc()['c'] ?? 0;
$pending = $mysqli->query("SELECT COUNT(*) AS c FROM documents WHERE status='Pending'")->fetch_assoc()['c'] ?? 0;
$approved = $mysqli->query("SELECT COUNT(*) AS c FROM documents WHERE status='Approved'")->fetch_assoc()['c'] ?? 0;
$storageBytes = 0;
$rows = $mysqli->query('SELECT filepath, filesize FROM documents')->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) { $storageBytes += (int)$r['filesize']; }
$totalLimit = 10 * 1024 * 1024 * 1024;
$usedGB = round($storageBytes / (1024*1024*1024), 2);
$perc = round(($storageBytes / max(1,$totalLimit)) * 100, 1);
$recent = $mysqli->query('SELECT d.*, u.fullname FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id ORDER BY d.created_at DESC LIMIT 6')->fetch_all(MYSQLI_ASSOC);
$types = [];
$files = $mysqli->query('SELECT filename, filesize FROM documents')->fetch_all(MYSQLI_ASSOC);
foreach($files as $f){
    $ext = strtolower(pathinfo($f['filename'], PATHINFO_EXTENSION));
    if ($ext=='') $ext='other';
    if (!isset($types[$ext])) $types[$ext] = ['count'=>0,'size'=>0];
    $types[$ext]['count'] += 1;
    $types[$ext]['size'] += (int)$f['filesize'];
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard | SK Payatas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold">SK Payatas File Management</a>
    <div>
      <span class="me-3">Welcome, <?=htmlspecialchars($_SESSION['username'])?></span>
      <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
  </div>
</nav>
<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-md-2">
      <div class="list-group">
        <a class="list-group-item active">Dashboard</a>
        <a class="list-group-item" href="upload.php">Upload</a>
        <a class="list-group-item" href="manage_users.php">Users</a>
        <a class="list-group-item" href="track.php?doc_id=0">Tracking</a>
      </div>
    </div>
    <div class="col-md-10">
      <div class="row g-3">
        <div class="col-md-2">
          <div class="card p-3 text-center">
            <small class="text-muted">Total Files</small>
            <h4><?=number_format($totalFiles)?></h4>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card p-3 text-center">
            <small class="text-muted">Pending</small>
            <h4><?=number_format($pending)?></h4>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card p-3 text-center">
            <small class="text-muted">Approved</small>
            <h4><?=number_format($approved)?></h4>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3">
            <small class="text-muted">Storage Usage (<?=$usedGB?> GB / 10 GB)</small>
            <div class="progress mt-2" style="height:18px;">
              <div class="progress-bar" role="progressbar" style="width: <?=$perc?>%"><?=$perc?>%</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-8">
          <div class="card p-3">
            <h6>Recent Files</h6>
            <ul class="list-group">
              <?php foreach($recent as $r): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?=htmlspecialchars($r['title'])?></strong><br>
                    <small class="text-muted">Uploaded by <?=htmlspecialchars($r['fullname'] ?? $r['uploaded_by'])?> · <?=htmlspecialchars($r['created_at'])?></small>
                  </div>
                  <div>
                    <?php if(!empty($r['qr_code'])): ?>
                      <img src="<?=htmlspecialchars($r['qr_code'])?>" width="90" class="me-2"><br>
                    <?php endif; ?>
                    <a href="track.php?doc_id=<?=urlencode($r['id'])?>" class="btn btn-sm btn-outline-primary">Track</a>
                    <a href="view.php?id=<?=urlencode($r['id'])?>" class="btn btn-sm btn-primary">Open</a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card p-3">
            <h6>Storage Monitoring</h6>
            <p class="small text-muted">Total used: <strong><?=$usedGB?> GB</strong> of 10 GB</p>
            <div class="mt-2 mb-3">
              <div class="progress" style="height:14px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?=$perc?>%"></div>
              </div>
            </div>
            <h6 class="mt-2">Storage Breakdown</h6>
            <table class="table table-sm">
              <thead><tr><th>Type</th><th>Count</th><th>Size (MB)</th></tr></thead>
              <tbody>
                <?php foreach($types as $ext=>$data): ?>
                  <tr>
                    <td><?=htmlspecialchars($ext)?></td>
                    <td><?=$data['count']?></td>
                    <td><?=round($data['size']/1024/1024,2)?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>