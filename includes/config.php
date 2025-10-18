<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'sk_payatas_db';
$DB_USER = 'root';
$DB_PASS = '';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("MySQL connection failed: " . $mysqli->connect_error);
}
session_start();
?>