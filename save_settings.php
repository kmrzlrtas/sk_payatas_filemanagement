<?php
include('include/config.php');

// Ensure uploads directory exists
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Collect form data
$site_name = $_POST['site_name'] ?? '';
$theme_color = $_POST['theme_color'] ?? '#007bff';
$site_logo = '';

// Handle logo upload
if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
    $site_logo = basename($_FILES['site_logo']['name']);
    move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_dir . $site_logo);
}

// Check if record exists
$result = $mysqli->query("SELECT id FROM settings LIMIT 1");

if ($result->num_rows > 0) {
    $update_sql = "UPDATE settings SET 
        site_name='$site_name',
        theme_color='$theme_color'";

    if ($site_logo != '') {
        $update_sql .= ", site_logo='$site_logo'";
    }

    $update_sql .= " WHERE id=1";
    $mysqli->query($update_sql);
} else {
    $mysqli->query("INSERT INTO settings (site_name, theme_color, site_logo) 
                    VALUES ('$site_name', '$theme_color', '$site_logo')");
}

header("Location: settings.php?saved=1");
exit;
?>
