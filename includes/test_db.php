<?php
require_once 'config.php';

if ($mysqli->ping()) {
    echo "✅ Database connection successful!";
} else {
    echo "❌ Database connection failed: " . $mysqli->error;
}
?>
