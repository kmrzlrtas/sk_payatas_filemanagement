<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$userId = $_SESSION['user_id'];
$shared = $mysqli->query("
    SELECT f.filename, f.filepath, u.fullname AS shared_by, s.shared_at
    FROM shared_files s
    JOIN files f ON f.id = s.file_id
    JOIN users u ON u.id = s.shared_by
    WHERE s.shared_with = $userId
    ORDER BY s.shared_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shared Files | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <h3 class="mb-4">Shared Files</h3>
    <div class="card p-3 shadow-sm">
        <?php if ($shared->num_rows > 0): ?>
            <?php while($f = $shared->fetch_assoc()): ?>
                <div class="border-bottom py-2">
                    <strong><?= htmlspecialchars($f['filename']) ?></strong><br>
                    Shared by: <?= htmlspecialchars($f['shared_by']) ?><br>
                    On: <?= $f['shared_at'] ?><br>
                    <a href="<?= htmlspecialchars($f['filepath']) ?>" class="btn btn-sm btn-outline-primary mt-2">Open</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No files shared with you yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
