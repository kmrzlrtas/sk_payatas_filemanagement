<?php
$mysqli = new mysqli("127.0.0.1", "root", "", "sk_payatas_db");

if ($mysqli->connect_errno) {
    die("❌ Database connection failed: " . $mysqli->connect_error);
}

echo "✅ Database connected successfully!<br>";

// Try reading the users table
$result = $mysqli->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
    $data = $result->fetch_assoc();
    echo "👤 Users table found! Total users: " . $data['total'];
} else {
    echo "⚠️ Couldn't query users table: " . $mysqli->error;
}
?>
