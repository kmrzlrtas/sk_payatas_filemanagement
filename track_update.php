<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
if (strtolower($_SESSION['role']) !== 'admin') { header('Location: official_dashboard.php'); exit; }

// Files that can be tracked/updated
$files = $mysqli->query("SELECT * FROM files ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track & Update | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f5f7fa;font-family:'Poppins',sans-serif;}
.sidebar{width:250px;height:100vh;background:#fff;border-right:1px solid #e5e5e5;position:fixed;top:0;left:0;padding:1.5rem 1rem;}
.sidebar a{display:block;color:#333;padding:10px 15px;border-radius:8px;margin-bottom:5px;text-decoration:none;font-weight:500;}
.sidebar a:hover,.sidebar a.active{background:#007bff;color:#fff;}
.main{margin-left:270px;padding:2rem;}
.card{border:none;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);}
</style>
</head>
<body>
<div class="sidebar">
    <h4>SK FileHub</h4>
    <p>Council Management</p>
    <a href="admin_dashboard.php">📊 Dashboard</a>
    <a href="file_manager.php">📁 File Manager</a>
    <a href="track_update.php" class="active">🔄 Track & Update</a>
    <a href="backup_recovery.php">💾 Backup & Recovery</a>
    <a href="user_management.php">👥 User Management</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="text-danger mt-3">🚪 Logout</a>
</div>

<div class="main">
    <h3 class="fw-bold mb-4">Track & Update Files</h3>
    <div class="card p-4">
        <h5>Monitor and update file statuses</h5>
        <table class="table mt-3">
            <thead><tr><th>File</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php while($file = $files->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($file['filename']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($file['status']) ?></span></td>
                    <td>
                        <form action="update_status.php" method="POST" class="d-flex">
                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                            <select name="status" class="form-select form-select-sm me-2">
                                <option>Pending</option>
                                <option>Reviewed</option>
                                <option>Approved</option>
                                <option>Archived</option>
                            </select>
                            <button class="btn btn-sm btn-primary" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
