<?php
require 'includes/config.php';
if (!isset($_GET['doc_id'])) die('Invalid document id');
$docId = intval($_GET['doc_id']);
$stmt = $mysqli->prepare('SELECT d.*, u.fullname FROM documents d LEFT JOIN users u ON d.uploaded_by=u.id WHERE d.id=?');
$stmt->bind_param('i', $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
if (!$doc) die('Document not found');

$user_id = $_SESSION['user_id'] ?? null;
$action = 'Viewed via QR';
$logStmt = $mysqli->prepare('INSERT INTO document_activity (doc_id, user_id, action) VALUES (?,?,?)');
$logStmt->bind_param('iis', $docId, $user_id, $action);
$logStmt->execute();

$logs = $mysqli->prepare('SELECT da.*, u.fullname FROM document_activity da LEFT JOIN users u ON da.user_id=u.id WHERE da.doc_id=? ORDER BY da.timestamp DESC');
$logs->bind_param('i', $docId);
$logs->execute();
$logsRes = $logs->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Track Document</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container mt-4">
  <div class="card p-3">
    <div class="d-flex justify-content-between">
      <h4>Tracking - <?=htmlspecialchars($doc['title'])?></h4>
      <?php if(!empty($doc['qr_code'])): ?><img src="<?=htmlspecialchars($doc['qr_code'])?>" width="120"><?php endif; ?>
    </div>
    <p><strong>Status:</strong> <?=htmlspecialchars($doc['status'])?></p>
    <p><strong>Uploaded by:</strong> <?=htmlspecialchars($doc['fullname'] ?? $doc['uploaded_by'])?></p>
    <hr>
    <h6>Activity Log</h6>
    <ul class="list-group">
      <?php foreach($logsRes as $l): ?>
        <li class="list-group-item"><?=htmlspecialchars($l['action'])?> by <?=htmlspecialchars($l['fullname'] ?? 'System')?> at <?=htmlspecialchars($l['timestamp'])?></li>
      <?php endforeach; ?>
    </ul>
    <div class="mt-3"><a href="dashboard.php" class="btn btn-secondary">Back</a></div>
  </div>
</div>
</body>
</html>