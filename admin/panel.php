<?php
date_default_timezone_set('Asia/Makassar');
$data = json_decode(file_get_contents('../status.json'), true);
$status = htmlspecialchars($data['status'] ?? 'blokir', ENT_QUOTES, 'UTF-8');
$expired = htmlspecialchars($data['expired'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8');
$updated = htmlspecialchars($data['updated_at'] ?? 'Belum ada', ENT_QUOTES, 'UTF-8');
$success = isset($_GET['update']) && $_GET['update'] === 'success';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Panel Status Akses</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Panel Status Akses</h2>

    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
        ✅ Status berhasil diperbarui!
      </div>
    <?php endif; ?>

    <div class="mb-4">
      <p class="text-sm text-gray-600">🕒 Terakhir diperbarui: <strong><?= $updated ?></strong></p>
    </div>

    <form action="update-status.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Status Akses:</label>
        <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="blokir" <?= $status === 'blokir' ? 'selected' : '' ?>>Blokir</option>
        </select>
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Tanggal Expired:</label>
        <input type="date" name="expired" value="<?= $expired ?>" class="w-full border border-gray-300 rounded px-3 py-2">
      </div>

      <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</body>
</html>
