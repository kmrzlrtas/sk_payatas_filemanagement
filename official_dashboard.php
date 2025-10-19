<?php
require 'includes/config.php';

// ✅ Restrict access
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (strtolower($_SESSION['role']) !== 'official') {
    header('Location: admin_dashboard.php');
    exit;
}

// --- Get total files ---
$totalFilesQuery = $mysqli->query("SELECT COUNT(*) AS total FROM files");
$totalFiles = $totalFilesQuery->fetch_assoc()['total'] ?? 0;

// --- Get total size (in bytes) ---
$totalSizeQuery = $mysqli->query("SELECT SUM(size) AS total_size FROM files");
$totalSize = $totalSizeQuery->fetch_assoc()['total_size'] ?? 0;
$usedGB = round($totalSize / (1024 * 1024 * 1024), 2); // bytes → GB
$availableGB = max(0, 50 - $usedGB);
$usedPercent = min(100, ($usedGB / 50) * 100);

// --- Recent Files ---
$recentFiles = $mysqli->query("SELECT id, filename, filepath, size, uploaded_at FROM files ORDER BY uploaded_at DESC LIMIT 5");

// --- Recent Activity ---
$recentActivity = $mysqli->query("SELECT COUNT(*) AS total FROM files WHERE DATE(uploaded_at)=CURDATE()");
$activityCount = $recentActivity->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Official Dashboard | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background:#f9fafb; }
.sidebar {
    width: 250px;
    background: #fff;
    height: 100vh;
    position: fixed;
    top: 0; left: 0;
    border-right: 1px solid #e5e5e5;
    padding: 1.5rem 1rem;
}
.sidebar h4 { font-weight: 600; margin-bottom: 1rem; }
.sidebar a {
    display: flex; align-items: center;
    color: #333; text-decoration: none;
    padding: 10px 15px; border-radius: 8px;
    margin-bottom: 5px; font-weight: 500;
}
.sidebar a i { margin-right: 10px; }
.sidebar a.active, .sidebar a:hover { background: #007bff; color: #fff; }

.main { margin-left: 270px; padding: 2rem; }
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}
.topbar .search-bar {
    flex: 1;
    display: flex;
    gap: 10px;
}
.topbar input {
    flex: 1;
    border-radius: 8px;
    padding: 0.6rem 1rem;
}
.stat-card {
    background: #fff; padding: 20px; border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    text-align: center;
}
.stat-card h2 { font-weight: 700; margin: 0; }

.card-box {
    background: #fff; border-radius: 12px; padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.file-list .file-item {
    background: #fff; padding: 15px; border-radius: 10px;
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.quick-actions button, .quick-actions form { width: 100%; margin-bottom: 10px; }
.storage-bar {
    background: #e9ecef; height: 10px; border-radius: 5px;
    overflow: hidden;
}
.storage-fill {
    height: 10px; background: #007bff;
    transition: width 0.3s ease;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>SK Council</h4>
    <p class="text-muted">File Management</p>
    <a href="official_dashboard.php" class="active"><i class="fa fa-home"></i> Dashboard</a>
    <a href="my_files.php"><i class="fa fa-folder"></i> My Files</a>
    <a href="shared_files.php"><i class="fa fa-share-alt"></i> Shared Files</a>
    <a href="recent_files.php"><i class="fa fa-clock"></i> Recent</a>
    <a href="trash.php"><i class="fa fa-trash"></i> Trash</a>
    <hr>
    <p class="text-muted small mb-2">CATEGORIES</p>
    <a href="#"><span class="text-danger">●</span> Documents</a>
    <a href="#"><span class="text-primary">●</span> Reports</a>
    <a href="#"><span class="text-success">●</span> Projects</a>
    <a href="logout.php" class="text-danger mt-3"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div>
            <h3 class="fw-bold mb-1">Dashboard</h3>
            <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" class="form-control" placeholder="Search files...">
            <a href="upload_file.php" class="btn btn-primary"><i class="fa fa-upload me-1"></i> Upload File</a>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h6>Total Files</h6>
                <h2><?= $totalFiles ?></h2>
                <small class="text-success">+12% from last month</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h6>Storage Used</h6>
                <h2><?= $usedGB ?> GB</h2>
                <small class="text-muted">of 50 GB available</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h6>Recent Activity</h6>
                <h2><?= $activityCount ?></h2>
                <small class="text-muted">Files modified today</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h6>Shared Files</h6>
                <h2>342</h2>
                <small class="text-muted">Active collaborations</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Recent Files -->
        <div class="col-lg-8">
            <div class="card-box">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Recent Files</h5>
                    <a href="recent_files.php" class="text-decoration-none">View All</a>
                </div>
                <div class="file-list" id="fileList">
                    <?php if ($recentFiles->num_rows > 0): ?>
                        <?php while ($file = $recentFiles->fetch_assoc()): ?>
                            <div class="file-item">
                                <div>
                                    <i class="fa fa-file me-2 text-primary"></i>
                                    <?= htmlspecialchars($file['filename']) ?>
                                    <div class="text-muted small">
                                        Modified <?= date("M d, Y h:i A", strtotime($file['uploaded_at'])) ?> • 
                                        <?= round($file['size'] / (1024*1024), 2) ?> MB
                                    </div>
                                </div>
                                <div>
                                    <a href="<?= htmlspecialchars($file['filepath']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                    <a href="share_file.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-info ms-2">Share</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent files found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card-box mb-4">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="quick-actions">
                    <button onclick="createFolder()" class="btn btn-outline-primary"><i class="fa fa-folder-plus me-1"></i> Create New Folder</button>
                    <button onclick="window.location='upload_file.php'" class="btn btn-outline-success"><i class="fa fa-upload me-1"></i> Upload Files</button>

                    <!-- Working Share File Dropdown -->
                    <form action="share_file.php" method="get" class="mt-2">
                        <div class="input-group">
                            <select name="id" class="form-select" required>
                                <option value="" disabled selected>Select file to share</option>
                                <?php
                                $files = $mysqli->query("SELECT id, filename FROM files ORDER BY uploaded_at DESC");
                                while ($f = $files->fetch_assoc()):
                                ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['filename']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-info">
                                <i class="fa fa-share me-1"></i> Share File
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Storage Usage -->
            <div class="card-box">
                <h6 class="mb-2">Storage Usage</h6>
                <div class="storage-bar mb-2">
                    <div class="storage-fill" style="width: <?= $usedPercent ?>%;"></div>
                </div>
                <p class="small text-muted mb-1">Used: <?= $usedGB ?> GB of 50 GB</p>
            </div>
        </div>
    </div>
</div>

<script>
function createFolder() {
    const name = prompt("Enter folder name:");
    if (!name) return;
    fetch('create_folder.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'folder_name=' + encodeURIComponent(name)
    })
    .then(r => r.text())
    .then(alert)
    .catch(() => alert("Error creating folder"));
}
</script>
</body>
</html>
