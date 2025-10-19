<?php
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// ✅ Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $fileName = basename($_FILES['file']['name']);
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $category = $_POST['category'];

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filePath = $uploadDir . time() . '_' . $fileName;

        if (move_uploaded_file($fileTmp, $filePath)) {
            $stmt = $mysqli->prepare("INSERT INTO files (filename, filepath, size, uploaded_by, uploaded_at, category) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt->bind_param("ssiss", $fileName, $filePath, $fileSize, $user_id, $category);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success text-center'>✅ File uploaded successfully! Redirecting...</div>";
                echo "<script>setTimeout(()=>{window.location='official_dashboard.php';},2000);</script>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Database error.</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>⚠️ Failed to move uploaded file.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ No file selected or upload error occurred.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload File | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background:#f9fafb; }
.sidebar {
    width: 250px; background: #fff; height: 100vh;
    position: fixed; top: 0; left: 0;
    border-right: 1px solid #e5e5e5; padding: 1.5rem 1rem;
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
.card-box { background: #fff; border-radius: 12px; padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    max-width: 650px; margin: 0 auto;
}
.form-control, .form-select { border-radius: 8px; padding: 10px; }
.btn-primary { border-radius: 8px; font-weight: 600; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>SK Council</h4>
    <p class="text-muted">File Management</p>
    <a href="official_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="my_files.php"><i class="fa fa-folder"></i> My Files</a>
    <a href="shared_files.php"><i class="fa fa-share-alt"></i> Shared Files</a>
    <a href="recent_files.php"><i class="fa fa-clock"></i> Recent</a>
    <a href="trash.php"><i class="fa fa-trash"></i> Trash</a>
    <hr>
    <p class="text-muted small mb-2">CATEGORIES</p>
    <a href="category.php?cat=Documents"><span class="text-danger">●</span> Documents</a>
    <a href="category.php?cat=Reports"><span class="text-primary">●</span> Reports</a>
    <a href="category.php?cat=Projects"><span class="text-success">●</span> Projects</a>
    <a href="logout.php" class="text-danger mt-3"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Upload File</h3>
        <a href="official_dashboard.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card-box">
        <?= $message ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-semibold">Choose File</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Select Category</label>
                <select name="category" class="form-select" required>
                    <option value="" disabled selected>Select category</option>
                    <option value="Documents">Documents</option>
                    <option value="Reports">Reports</option>
                    <option value="Projects">Projects</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa fa-upload me-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
