<?php
require 'includes/config.php';
require 'includes/qr_helper.php';
if (!isset($_SESSION['user_id'])) header('Location: index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $mysqli->real_escape_string($_POST['title']);
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please choose a file to upload.';
    } else {
        $f = $_FILES['file'];
        $uploadsDir = __DIR__ . '/uploads/files/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
        $safe = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($f['name']));
        $target = $uploadsDir . $safe;
        if (move_uploaded_file($f['tmp_name'], $target)) {
            $filesize = filesize($target);
            $uploaded_by = $_SESSION['user_id'];
            $stmt = $mysqli->prepare('INSERT INTO documents (title, filename, filepath, filesize, uploaded_by) VALUES (?,?,?,?,?)');
            $stmt->bind_param('sssii', $title, $safe, $target, $filesize, $uploaded_by);
            $stmt->execute();
            $docId = $stmt->insert_id;
            $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/track.php?doc_id=' . $docId;
            $qrPath = generate_qr($url, $docId);
            $mysqli->query("UPDATE documents SET qr_code='" . $mysqli->real_escape_string($qrPath) . "' WHERE id=$docId");
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Failed to move uploaded file.';
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Upload | SK Payatas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container mt-4">
  <h4>Upload Document</h4>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3"><input name="title" class="form-control" placeholder="Document Title" required></div>
    <div class="mb-3"><input name="file" type="file" class="form-control" required></div>
    <div><button class="btn btn-primary">Upload</button> <a href="dashboard.php" class="btn btn-secondary">Cancel</a></div>
  </form>
</div>
</body>
</html>