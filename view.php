<?php
require 'includes/config.php';
if (!isset($_GET['id'])) die('ID required');
$id = intval($_GET['id']);
$stmt = $mysqli->prepare('SELECT * FROM documents WHERE id=?');
$stmt->bind_param('i',$id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();
if (!$d) die('Not found');
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $act = $mysqli->prepare('INSERT INTO document_activity (doc_id, user_id, action) VALUES (?,?,?)');
    $a = 'Viewed in system';
    $act->bind_param('iis',$id,$uid,$a);
    $act->execute();
}
$path = $d['filepath'];
if (!file_exists($path)) die('File missing');
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($d['filename']).'"');
readfile($path);
exit;
?>