<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$result = $mysqli->query("SELECT * FROM files WHERE uploaded_at >= NOW() - INTERVAL 7 DAY ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Recent Files | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f9fafb;}
.sidebar{width:250px;background:#fff;height:100vh;position:fixed;top:0;left:0;border-right:1px solid #e5e5e5;padding:1.5rem 1rem;}
.sidebar a{display:flex;align-items:center;color:#333;text-decoration:none;padding:10px 15px;border-radius:8px;margin-bottom:5px;font-weight:500;}
.sidebar a.active,.sidebar a:hover{background:#007bff;color:#fff;}
.main{margin-left:270px;padding:2rem;}
.card-box{background:#fff;border-radius:12px;padding:25px;box-shadow:0 4px 12px rgba(0,0,0,0.05);}
.file-item{display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid #eee;}
</style>
</head>
<body>
<div class="sidebar">
    <h4>SK Council</h4><p class="text-muted">File Management</p>
    <a href="official_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="my_files.php"><i class="fa fa-folder"></i> My Files</a>
    <a href="shared_files.php"><i class="fa fa-share-alt"></i> Shared Files</a>
    <a href="recent_files.php" class="active"><i class="fa fa-clock"></i> Recent</a>
    <a href="trash.php"><i class="fa fa-trash"></i> Trash</a>
    <hr><p class="text-muted small mb-2">CATEGORIES</p>
    <a href="category.php?cat=Documents"><span class="text-danger">●</span> Documents</a>
    <a href="category.php?cat=Reports"><span class="text-primary">●</span> Reports</a>
    <a href="category.php?cat=Projects"><span class="text-success">●</span> Projects</a>
    <a href="logout.php" class="text-danger mt-3"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
<div class="main">
    <h3 class="fw-bold mb-3">Recent Files (Last 7 Days)</h3>
    <div class="card-box">
        <?php if ($result->num_rows > 0): while($file = $result->fetch_assoc()): ?>
            <div class="file-item">
                <div>
                    <i class="fa fa-clock text-warning me-2"></i><?= htmlspecialchars($file['filename']) ?>
                    <div class="text-muted small"><?= round($file['size'] / (1024*1024), 2) ?> MB • <?= date("M d, Y h:i A", strtotime($file['uploaded_at'])) ?></div>
                </div>
                <a href="<?= htmlspecialchars($file['filepath']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
            </div>
        <?php endwhile; else: ?>
            <p class="text-muted text-center">No recent files.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
