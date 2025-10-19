<?php
require 'includes/config.php';

// ✅ Access control
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (strtolower($_SESSION['role']) !== 'admin') {
    header('Location: official_dashboard.php');
    exit;
}

// --- DELETE USER ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $_SESSION['user_id']) { // prevent admin from deleting own account
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: user_management.php");
    exit();
}

// --- RESET PASSWORD ---
if (isset($_POST['reset'])) {
    $id = intval($_POST['user_id']);
    $new_pass = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pass, $id);
    $stmt->execute();
    $reset_message = "Password for user ID #$id has been reset to '123456'.";
}

// --- FETCH USERS ---
$users = $mysqli->query("SELECT id, fullname, username, role, created_at FROM users ORDER BY role ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management | SK FileHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f5f7fa;
    font-family: 'Poppins', sans-serif;
}
.sidebar {
    width: 250px;
    height: 100vh;
    background: #fff;
    border-right: 1px solid #e5e5e5;
    position: fixed;
    top: 0;
    left: 0;
    padding: 1.5rem 1rem;
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
.sidebar a:hover,
.sidebar a.active {
    background: #007bff;
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
.btn-sm {
    padding: 4px 8px;
    font-size: 0.85rem;
}
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
    <h3 class="fw-bold mb-4">User Management</h3>

    <?php if (!empty($reset_message)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($reset_message) ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Manage Accounts</h5>
            <a href="add_user.php" class="btn btn-primary btn-sm">+ Add Official</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['role'] === 'admin' ? 'primary' : 'success' ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <?php if ($row['role'] !== 'admin'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button name="reset" class="btn btn-warning btn-sm"
                                        onclick="return confirm('Reset password to 123456 for this user?')">
                                        Reset Password
                                    </button>
                                </form>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this account?')">
                                    Delete
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Admin</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2">Default password for reset is <b>123456</b>.</p>
    </div>
</div>
</body>
</html>
