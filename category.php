<?php
require 'includes/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$category = $_GET['cat'] ?? 'Documents';
$result = $mysqli->query("SELECT * FROM files WHERE category='$category' ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title><?= htmlspecialchars($category) ?> | SK FileHub</title>
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
    <a href="official_dashboard.php"><i class="fa fa-home"></i> Dashboard</
