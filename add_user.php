<?php
require 'includes/config.php';

// ✅ Access control: only admin
if (!isset($_SESSION['user_id'])) { 
    header('Location: index.php'); 
    exit; 
}
if (strtolower($_SESSION['role']) !== 'admin') { 
    header('Location: official_dashboard.php'); 
    exit; 
}

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'official';

            $stmt = $mysqli->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $username, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "Official account created successfully!";
            } else {
                $error = "Failed to create account. Please try again.";
            }

            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Official | SK FileHub</title>
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
    <a href="track_update.php">🔄 Track & Update</a>
    <a href="backup_recovery.php">💾 Backup & Recovery</a>
    <a href="user_management.php" class="active">👥 User Management</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php" class="text-danger mt-3">🚪 Logout</a>
</div>

<div class="main">
    <h3 class="fw-bold mb-4">Add New Official</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
            </div>
            <div class="d-flex justify-content-between">
                <a href="user_management.php" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
