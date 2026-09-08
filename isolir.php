<?php
require_once __DIR__ . '/isolir-check.php';

/**
 * Halaman Isolir Pelanggan - Mikhmon Client
 * Terintegrasi dengan Pusat Billing (billing.madignet.site)
 */

// Konfigurasi Billing Server Pusat
if (!defined('BILLING_SERVER_URL')) {
    define('BILLING_SERVER_URL', 'http://billing.test:8080/');
}

// Identitas Klien / Router Subdomain (Otomatis deteksi dari HTTP_HOST)
$subdomain = $_SERVER['HTTP_HOST'] ?? 'klien.madignet.site';
$client_id = explode('.', $subdomain)[0] ?? 'default';

// Parameter Tagihan Default (Akan diupdate via API billing.madignet.site)
$tagihan_amount = 125000;
$nama_pelanggan = "Pelanggan";
$nomor_layanan  = "INET-702929";
$nomor_wa_admin = "6281234567890"; // Ganti dengan nomor WhatsApp Admin

// Percobaan Ambil Data Real-time dari Server Billing (Jika API aktif)
/*
$api_url = BILLING_SERVER_URL . "/api/get_invoice.php?subdomain=" . urlencode($subdomain);
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    if (!empty($data['success'])) {
        $tagihan_amount = $data['amount'] ?? $tagihan_amount;
        $nama_pelanggan = $data['customer_name'] ?? $nama_pelanggan;
        $nomor_layanan  = $data['service_id'] ?? $nomor_layanan;
    }
}
*/
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Akses Diblokir - Pembayaran Layanan Internet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="Mid-client-k7dtFuQEsqJfNGyc"></script>
  <style>
    :root {
      --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
      --card-bg: rgba(30, 41, 59, 0.7);
      --card-border: rgba(255, 255, 255, 0.1);
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --primary: #6366f1;
      --primary-hover: #4f46e5;
      --danger: #ef4444;
      --danger-glow: rgba(239, 68, 68, 0.35);
      --warning: #f59e0b;
      --success: #10b981;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
      min-height: 100vh;
      background: var(--bg-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.25rem;
      color: var(--text-main);
    }

    .container {
      width: 100%;
      max-width: 480px;
      background: var(--card-bg);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--card-border);
      border-radius: 24px;
      padding: 2.25rem 1.75rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      animation: fadeIn 0.5s ease-out;
      text-align: center;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .icon-wrapper {
      width: 80px;
      height: 80px;
      background: rgba(239, 68, 68, 0.12);
      border: 2px solid rgba(239, 68, 68, 0.3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      box-shadow: 0 0 30px var(--danger-glow);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 0 20px var(--danger-glow); }
      50% { transform: scale(1.05); box-shadow: 0 0 35px var(--danger-glow); }
    }

    .icon-wrapper svg {
      width: 40px;
      height: 40px;
      color: var(--danger);
    }

    h1 {
      font-size: 1.6rem;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 0.5rem;
      letter-spacing: -0.02em;
    }

    .subtitle {
      color: var(--text-muted);
      font-size: 0.95rem;
      margin-bottom: 1.75rem;
      line-height: 1.5;
    }

    .info-card {
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 16px;
      padding: 1.25rem;
      margin-bottom: 1.75rem;
      text-align: left;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
      border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
      font-size: 0.9rem;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: var(--text-muted);
    }

    .info-value {
      font-weight: 600;
      color: #ffffff;
    }

    .amount-box {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(245, 158, 11, 0.15) 100%);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 16px;
      padding: 1rem;
      margin-bottom: 1.75rem;
    }

    .amount-label {
      font-size: 0.825rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #fca5a5;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }

    .amount-value {
      font-size: 1.85rem;
      font-weight: 800;
      color: #ffffff;
    }

    .btn-group {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
    }

    .btn {
      width: 100%;
      padding: 0.95rem 1.25rem;
      border-radius: 14px;
      font-size: 0.975rem;
      font-weight: 700;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      text-decoration: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
      color: #ffffff;
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(99, 102, 241, 0.45);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.07);
      color: var(--text-main);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.12);
      transform: translateY(-1px);
    }

    .btn-wa {
      background: rgba(16, 185, 129, 0.15);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .btn-wa:hover {
      background: rgba(16, 185, 129, 0.25);
    }

    /* Modal Styling */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 1.25rem;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: #1e293b;
      border: 1px solid var(--card-border);
      border-radius: 20px;
      max-width: 440px;
      width: 100%;
      padding: 1.75rem;
      text-align: left;
      position: relative;
      animation: modalIn 0.3s ease-out;
    }

    @keyframes modalIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
    }

    .modal-title {
      font-size: 1.2rem;
      font-weight: 700;
    }

    .modal-close {
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 1.5rem;
      cursor: pointer;
    }

    .guide-list {
      list-style: none;
      counter-reset: guide-step;
    }

    .guide-item {
      counter-increment: guide-step;
      margin-bottom: 1rem;
      display: flex;
      gap: 0.85rem;
      font-size: 0.9rem;
      line-height: 1.5;
      color: #cbd5e1;
    }

    .guide-item::before {
      content: counter(guide-step);
      background: var(--primary);
      color: white;
      font-weight: 700;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.8rem;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="icon-wrapper">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>

    <h1>Akses Diblokir</h1>
    <p class="subtitle">Layanan internet Anda sementara dinonaktifkan karena adanya tagihan yang belum terselesaikan.</p>

    <div class="info-card">
      <div class="info-row">
        <span class="info-label">ID Pelanggan</span>
        <span class="info-value"><?= htmlspecialchars($nomor_layanan) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Nama Domain</span>
        <span class="info-value"><?= htmlspecialchars($subdomain) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Status Layanan</span>
        <span class="info-value" style="color: var(--danger);">Terisolir</span>
      </div>
    </div>

    <div class="amount-box">
      <div class="amount-label">Total Tagihan</div>
      <div class="amount-value">Rp <?= number_format($tagihan_amount, 0, ',', '.') ?></div>
    </div>

    <div class="btn-group">
      <button class="btn btn-primary" id="pay-button">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Bayar Sekarang
      </button>
      
      <button class="btn btn-secondary" id="guide-button">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Tata Cara Pembayaran
      </button>

      <a href="https://wa.me/<?= $nomor_wa_admin ?>?text=Halo%20Admin,%20saya%20ingin%20konfirmasi%20pembayaran%20untuk%20ID:%20<?= urlencode($nomor_layanan) ?>" target="_blank" class="btn btn-wa">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
        Hubungi Admin (WhatsApp)
      </a>
    </div>
  </div>

  <!-- Modal Tata Cara Pembayaran -->
  <div class="modal" id="guide-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Tata Cara Pembayaran</h2>
        <button class="modal-close" id="modal-close">&times;</button>
      </div>
      <ul class="guide-list">
        <li class="guide-item">
          Klik tombol <strong>Bayar Sekarang</strong> untuk membuka gerbang pembayaran Midtrans/Billing Pusat.
        </li>
        <li class="guide-item">
          Pilih metode pembayaran favorit Anda (QRIS, GoPay, OVO, Transfer Bank, Alfamart/Indomaret).
        </li>
        <li class="guide-item">
          Selesaikan pembayaran sesuai dengan instruksi dan jumlah tagihan yang tertera.
        </li>
        <li class="guide-item">
          Setelah pembayaran sukses, sistem billing akan secara otomatis memulihkan koneksi internet Anda dalam waktu 1-3 menit.
        </li>
        <li class="guide-item">
          Jika koneksi belum pulih, silakan klik <strong>Hubungi Admin (WhatsApp)</strong> dengan menyertakan bukti transfer.
        </li>
      </ul>
    </div>
  </div>

  <script>
    // Modal Controller
    const guideBtn = document.getElementById('guide-button');
    const guideModal = document.getElementById('guide-modal');
    const modalClose = document.getElementById('modal-close');

    guideBtn.addEventListener('click', () => guideModal.classList.add('active'));
    modalClose.addEventListener('click', () => guideModal.classList.remove('active'));
    guideModal.addEventListener('click', (e) => {
      if (e.target === guideModal) guideModal.classList.remove('active');
    });

    // Handle Payment Button
    document.getElementById('pay-button').onclick = function () {
      // Panggil payment.php local atau langsung redirect ke Billing Pusat billing.madignet.site
      fetch('payment.php')
        .then(response => response.json())
        .then(data => {
          if (data.token) {
            snap.pay(data.token, {
              onSuccess: function (result) {
                Swal.fire({
                  icon: 'success',
                  title: 'Pembayaran Berhasil!',
                  text: 'Koneksi internet Anda sedang dipulihkan secara otomatis.',
                  confirmButtonColor: '#4f46e5'
                }).then(() => {
                  window.location.href = '/';
                });
              },
              onPending: function (result) {
                Swal.fire({
                  icon: 'info',
                  title: 'Pembayaran Menunggu',
                  text: 'Silakan selesaikan pembayaran sesuai instruksi.',
                  confirmButtonColor: '#4f46e5'
                });
              },
              onError: function (result) {
                Swal.fire({
                  icon: 'error',
                  title: 'Pembayaran Gagal',
                  text: 'Silakan coba beberapa saat lagi atau hubungi admin.',
                  confirmButtonColor: '#ef4444'
                });
              }
            });
          } else if (data.redirect_url) {
            window.location.href = data.redirect_url;
          } else {
            // Fallback jika payment API belum dikonfigurasi
            window.location.href = "<?= BILLING_SERVER_URL ?>/pay?subdomain=<?= urlencode($subdomain) ?>";
          }
        })
        .catch(error => {
          // Fallback redirect ke billing pusat
          window.location.href = "<?= BILLING_SERVER_URL ?>/pay?subdomain=<?= urlencode($subdomain) ?>";
        });
    };
  </script>
</body>

</html>
