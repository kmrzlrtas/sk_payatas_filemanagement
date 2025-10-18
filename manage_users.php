<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) header('Location: index.php');
$role = $_SESSION['role'] ?? 'official';
if ($role !== 'admin') die('Restricted');
$users = $mysqli->query('SELECT * FROM users ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container mt-4">
  <h4>Users</h4>
  <table class="table table-sm">
    <thead><tr><th>ID</th><th>Username</th><th>Fullname</th><th>Role</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
      <tr><td><?=$u['id']?></td><td><?=htmlspecialchars($u['username'])?></td><td><?=htmlspecialchars($u['fullname'])?></td><td><?=htmlspecialchars($u['role'])?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <a href="dashboard.php" class="btn btn-secondary">Back</a>
</div>
</body></html>