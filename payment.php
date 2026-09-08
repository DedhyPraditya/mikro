<?php
// Menampilkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/midtrans-php/Midtrans/Config.php';
require_once __DIR__ . '/midtrans-php/Midtrans/Snap.php';
require_once __DIR__ . '/midtrans-php/Midtrans/Transaction.php';
require_once __DIR__ . '/midtrans-php/Midtrans/Notification.php';

// Konfigurasi SDK Midtrans
\Midtrans\Config::$serverKey = 'YOUR_MIDTRANS_SERVER_KEY'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false;  // Ubah menjadi true untuk mode produksi
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Data transaksi
$transaction_details = array(
    'order_id' => 'ORDERID' . rand(),
    'gross_amount' => 15000,  // Total yang harus dibayar
);

// Detail pelanggan
$customer_details = array(
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe@mail.com',
    'phone' => '08123456789',
);

try {
    // Mendapatkan token pembayaran
    $payment_token = \Midtrans\Snap::getSnapToken(array(
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details
    ));

    // Mengirimkan data dalam format JSON
    echo json_encode([
        'token' => $payment_token  // Token pembayaran
    ]);
} catch (Exception $e) {
    // Jika terjadi kesalahan, tampilkan pesan error dalam format JSON
    echo json_encode([
        'error' => 'Terjadi kesalahan dalam mendapatkan token pembayaran: ' . $e->getMessage()
    ]);
}
?>
