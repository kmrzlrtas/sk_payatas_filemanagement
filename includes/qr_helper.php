<?php
function generate_qr($text, $docId) {
    $qrFolder = __DIR__ . '/../uploads/qrcodes/';
    if (!is_dir($qrFolder)) mkdir($qrFolder, 0755, true);
    $qrlib = __DIR__ . '/phpqrcode/qrlib.php';
    if (file_exists($qrlib)) {
        include_once $qrlib;
        $file = $qrFolder . 'doc_' . $docId . '.png';
        QRcode::png($text, $file, QR_ECLEVEL_L, 4);
        return 'uploads/qrcodes/' . basename($file);
    } else {
        $enc = urlencode($text);
        return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $enc . '&choe=UTF-8';
    }
}
?>