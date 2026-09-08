<?php
date_default_timezone_set('Asia/Makassar');
$start = microtime(true); // ⏱️ mulai timer

$status = $_POST['status'] ?? 'blokir';
$expired = $_POST['expired'] ?? date('Y-m-d');
$updated_at = date('Y-m-d H:i:s');

$data = [
    'status' => $status,
    'expired' => $expired,
    'updated_at' => $updated_at,
];

// Simpan ke file JSON
file_put_contents('../status.json', json_encode($data, JSON_PRETTY_PRINT));

// Hitung waktu eksekusi
$elapsed = round(microtime(true) - $start, 4); // dalam detik, 4 digit desimal
error_log("[UPDATE-STATUS] Selesai dalam {$elapsed}s");

// Redirect
header('Location: panel.php?update=success');
exit;
