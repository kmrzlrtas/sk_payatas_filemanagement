<?php
require 'includes/config.php';

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// --- Ensure only admin can access ---
if (strtolower($_SESSION['role']) !== 'admin') {
    header('Location: official_dashboard.php');
    exit;
}

// --- Dashboard Stats ---
$totalFiles = $mysqli->query("SELECT COUNT(*) AS total FROM files")->fetch_assoc()['total'] ?? 0;
$totalUsers = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$storageUsed = $mysqli->query("SELECT SUM(size) AS total FROM files")->fetch_assoc()['total'] ?? 0;
$storageGB = $storageUsed ? round($storageUsed / (1024 * 1024 * 1024), 2) : 0;

// --- File Activity Overview (Last 7 Days) ---
$fileActivity = [];
$days = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
foreach ($days as $d) $fileActivity[$d] = ['uploads'=>0];

$result = $mysqli->query("
    SELECT DAYNAME(uploaded_at) AS day, COUNT(*) AS uploads
    FROM files
    WHERE uploaded_at >= NOW() - INTERVAL 7 DAY
    GROUP BY DAYNAME(uploaded_at)
");
while ($row = $result->fetch_assoc()) {
    $day = substr($row['day'],0,3);
    if (isset($fileActivity[$day])) $fileActivity[$day]['uploads'] = $row['uploads'];
}

// --- Track & Update Files ---
$trackFiles = $mysqli->query("SELECT * FROM files ORDER BY uploaded_at DESC LIMIT 5");

// --- Recent Activity ---
$recentActivity = $mysqli->query("
    SELECT a.action, a.created_at, f.filename, o.name, o.profile_img
    FROM activity_log a
    JOIN files f ON a.file_id = f.id
    JOIN officials o ON a.official_id = o.id
    ORDER BY a.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SK FileHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #fff;
            border-right: 1px solid #e5e5e5;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1.5rem 1rem;
        }
        .sidebar h4 {
            color: #007bff;
            margin-bottom: 0.2rem;
        }
        .sidebar p {
            color: gray;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .sidebar a {
            display: block;
            color: #333;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            text-decoration: none;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #007bff;
            color: #fff;
        }
        .main {
            margin-left: 270px;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .chart-container {
            height: 300px;
        }
        .recent-activity img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .btn-share {
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>SK FileHub</h4>
        <p>Council Management</p>

        <!-- ✅ Active page highlighting -->
        <a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="file_manager.php" class="<?= basename($_SERVER['PHP_SELF']) == 'file_manager.php' ? 'active' : '' ?>">📁 File Manager</a>
        <a href="track_recovery.php" class="<?= basename($_SERVER['PHP_SELF']) == 'track_recovery.php' ? 'active' : '' ?>">🔄 Track & Update</a>
        <a href="backup_recovery.php" class="<?= basename($_SERVER['PHP_SELF']) == 'backup_recovery.php' ? 'active' : '' ?>">💾 Backup & Recovery</a>
        <a href="user_management.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : '' ?>">👥 User Management</a>
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">⚙️ Settings</a>
        <a href="logout.php" class="text-danger mt-3">🚪 Logout</a>
    </div>

    <div class="main">
        <h3 class="fw-bold mb-4">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Total Files</h6>
                    <h3><?= $totalFiles ?></h3>
                    <p class="text-success small">+12.5% from last month</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Storage Used</h6>
                    <h3><?= $storageGB ?> GB</h3>
                    <p class="text-danger small">+8.2% from last month</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Active Users</h6>
                    <h3><?= $totalUsers ?></h3>
                    <p class="text-success small">+3 new users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <h6>Last Backup</h6>
                    <h3>2h ago</h3>
                    <p class="text-success small">Successful</p>
                </div>
            </div>
        </div>

        <!-- File Activity Overview -->
        <div class="card mb-4 p-4">
            <h5 class="mb-3">File Activity Overview (Last 7 Days)</h5>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <div class="row g-3">
            <!-- Track & Update Files -->
            <div class="col-md-8">
                <div class="card p-4">
                    <h5>Track & Update Files</h5>
                    <ul class="list-group mt-3">
                        <?php if ($trackFiles->num_rows > 0): ?>
                            <?php while($file = $trackFiles->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    📄 <?= htmlspecialchars($file['filename']) ?>
                                    <div>
                                        <span class="badge 
                                            <?= $file['status']=='Updated' ? 'bg-success' : ($file['status']=='Pending' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                                            <?= $file['status'] ?>
                                        </span>
                                        <a href="share_file.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-primary btn-share ms-2">
                                            <i class="fa fa-share me-1"></i> Share
                                        </a>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No files found.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-4">
                <div class="card p-4">
                    <h5>Recent Activity</h5>
                    <div class="recent-activity mt-3">
                        <?php if ($recentActivity->num_rows > 0): ?>
                            <?php while($act = $recentActivity->fetch_assoc()): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="uploads/officials/<?= htmlspecialchars($act['profile_img']) ?>" alt="Profile">
                                <div>
                                    <?= htmlspecialchars($act['name']) ?> <?= htmlspecialchars($act['action']) ?> 
                                    <b><?= htmlspecialchars($act['filename']) ?></b><br>
                                    <small><?= date("M d, h:i A", strtotime($act['created_at'])) ?></small>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent activities yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
const ctx = document.getElementById('activityChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($fileActivity)) ?>,
        datasets: [{
            label: 'Uploads',
            data: <?= json_encode(array_column($fileActivity, 'uploads')) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0,123,255,0.1)',
            fill: true,
            tension: 0.3
        }]
    }
});
</script>
</body>
</html>
