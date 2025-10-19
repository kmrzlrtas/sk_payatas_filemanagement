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

// --- Fetch file to share ---
$file_id = $_GET['id'] ?? null;
$file = null;
if ($file_id) {
    $stmt = $mysqli->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $file = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// --- Handle share form ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'];
    $share_to = $_POST['share_to'];
    $shared_by = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("INSERT INTO shared_files (file_id, shared_by, shared_to, shared_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $file_id, $shared_by, $share_to);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ File shared successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Failed to share file.</div>";
    }
    $stmt->close();
}

// --- Get all officials for dropdown ---
$users = $mysqli->query("SELECT id, fullname, role FROM users WHERE role='official' AND id != " . $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Share File | SK FileHub</title>
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

.main {
    margin-left: 270px;
    padding: 2rem;
}
.card-box {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    max-width: 650px;
    margin: 0 auto;
}
.form-control, .form-select {
    border-radius: 8px;
    padding: 10px;
}
.btn-primary {
    border-radius: 8px;
    font-weight: 600;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>SK Council</h4>
    <p class="text-muted">File Management</p>
    <a href="official_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="my_files.php"><i class="fa fa-folder"></i> My Files</a>
    <a href="shared_files.php" class="active"><i class="fa fa-share-alt"></i> Shared Files</a>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Share File</h3>
        <a href="official_dashboard.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card-box">
        <?= $message ?>

        <?php if ($file): ?>
            <div class="mb-4">
                <h5><i class="fa fa-file text-primary me-2"></i><?= htmlspecialchars($file['filename']) ?></h5>
                <p class="text-muted small mb-0">
                    Uploaded on <?= date("M d, Y h:i A", strtotime($file['uploaded_at'])) ?> |
                    <?= round($file['size'] / (1024*1024), 2) ?> MB
                </p>
                <hr>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <input type="hidden" name="file_id" value="<?= $file_id ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Select Official to Share With</label>
                <select name="share_to" class="form-select" required>
                    <option value="" disabled selected>Select an official</option>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa fa-share me-1"></i> Share File
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
