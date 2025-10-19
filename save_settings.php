<?php
require 'includes/config.php';

// ✅ Access control
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $system_name = trim($_POST['system_name']);
    $notifications = $_POST['notifications'] === 'Enabled' ? 'enabled' : 'disabled';
    $theme = $_POST['theme'] === 'Dark' ? '#000000' : '#ffffff';

    // Update the settings in DB
    $stmt = $mysqli->prepare("UPDATE settings SET site_name=?, site_logo=?, theme_color=? WHERE id=1");
    $stmt->bind_param("sss", $system_name, $notifications, $theme);
    $stmt->execute();

    header("Location: settings.php?saved=1");
    exit;
}
?>
