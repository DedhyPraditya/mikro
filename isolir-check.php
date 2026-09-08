<?php
/**
 * Isolir Check Middleware for Mikhmon Klien
 * Terintegrasi dengan Sistem Billing Pusat (billing.madignet.site & billing.test)
 */

// Deteksi host saat ini
$host = $_SERVER['HTTP_X_FORWARDED_HOST']
    ?? $_SERVER['HTTP_HOST']
    ?? $_SERVER['SERVER_NAME']
    ?? 'default';

if (str_contains($host, ',')) {
    $host = trim(explode(',', $host)[0]);
}
$currentHost = strtolower(trim($host));

// Konfigurasi Billing Server (otomatis deteksi lokal vs produksi jika belum di-define)
if (!defined('BILLING_SERVER_URL')) {
    $isLocal = preg_match('/(localhost|\.test|127\.0\.0\.1)/i', $currentHost);
    define('BILLING_SERVER_URL', $isLocal ? 'http://billing.test:8080' : 'https://billing.madignet.site');
}

$statusFile = __DIR__ . '/status.json';

// 1. Cek Cache Lokal (Hanya fallback jika Billing Server mati/offline)
// Pada tahap ujicoba lokal, kita bypass pengecekan cache waktu agar perubahan status berlaku INSTAN real-time
/*
if (file_exists($statusFile)) {
    $statusData = json_decode(@file_get_contents($statusFile), true);
    if (is_array($statusData)) {
        $status = strtolower($statusData['status'] ?? '');
        $dueDate = $statusData['due_date'] ?? ($statusData['expired'] ?? date('Y-m-d'));
        $lastCheck = $statusData['last_check'] ?? 0;
        $today = date('Y-m-d');

        if ($status === 'aktif' && strtotime($dueDate) >= strtotime($today) && (time() - $lastCheck < 60)) {
            if (basename($_SERVER['PHP_SELF'] ?? '') === 'isolir.php') {
                header("Location: /admin.php?id=login");
                exit;
            }
            return;
        }
    }
}
*/

// 2. Query Status Real-time ke API Billing Server
$apiUrl = rtrim(BILLING_SERVER_URL, '/') . '/api.php?domain=' . rawurlencode($currentHost);
$response = null;

if (function_exists('curl_init')) {
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
}

if (!$response) {
    $context = stream_context_create([
        'http' => ['timeout' => 3, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $response = @file_get_contents($apiUrl, false, $context);
}

$isAktif = false;
if ($response) {
    $resData = json_decode($response, true);
    if (is_array($resData) && isset($resData['status'])) {
        $status = strtolower($resData['status']);
        $dueDate = $resData['due_date'] ?? date('Y-m-d');

        // Simpan / update status lokal
        @file_put_contents($statusFile, json_encode([
            'status' => $status,
            'domain' => $currentHost,
            'due_date' => $dueDate,
            'last_check' => time()
        ], JSON_PRETTY_PRINT));

        if ($status === 'aktif' && strtotime($dueDate) >= strtotime(date('Y-m-d'))) {
            $isAktif = true;
        }
    }
} else {
    // Fallback: Jika billing server offline, baca status lokal terakhir
    if (file_exists($statusFile)) {
        $cached = json_decode(@file_get_contents($statusFile), true);
        if (is_array($cached) && strtolower($cached['status'] ?? '') === 'aktif') {
            $isAktif = true;
        }
    }
}

// 3. Penanganan Akses
if ($isAktif) {
    // Jika pelanggan sudah aktif tapi sedang membuka isolir.php, arahkan kembali ke login/dashboard
    if (basename($_SERVER['PHP_SELF'] ?? '') === 'isolir.php') {
        header("Location: /admin.php?id=login");
        exit;
    }
    return; // Akses diizinkan ke Mikhmon
}

// Jika status isolir atau tidak ditemukan, redirect ke halaman isolir
if (basename($_SERVER['PHP_SELF'] ?? '') !== 'isolir.php') {
    header("Location: /isolir.php");
    exit;
}