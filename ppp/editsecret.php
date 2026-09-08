<?php
// Edit PPP Secret Page
include_once('../include/headhtml.php');
include_once('../include/menu.php');

// Load config and MikroTik API
include_once('../include/config.php');
include_once('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');

$API = new RouterosAPI();
$API->debug = false;

// Variabel untuk penyimpanan data PPP Secret
$secret = [];
$errorMsg = '';
$editSuccess = false;
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Cek apakah ID ada dan lakukan koneksi ke MikroTik API
if ($id && $API->connect($iphost, $userhost, decrypt($passwdhost))) {
  // Ambil data PPP Secret berdasarkan ID
  $data = $API->comm('/ppp/secret/print', [".id" => $id]);
  
  // Jika data ditemukan, ambil datanya
  if (is_array($data) && count($data) > 0) {
    $secret = $data[0];
  } else {
    $errorMsg = 'PPP Secret dengan ID tersebut tidak ditemukan.';
  }

  // Proses update data jika formulir disubmit
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [
      ".id" => $id,
      "name" => $_POST['name'],
      "password" => $_POST['password'],
      "service" => $_POST['service'],
      "profile" => $_POST['profile'],
      "local-address" => $_POST['local_address'],
      "remote-address" => $_POST['remote_address'],
      "comment" => $_POST['comment'],
    ];
    // Kirim data update ke MikroTik API
    $API->comm('/ppp/secret/set', $updateData);
    $editSuccess = true;
    
    // Ambil data terbaru setelah update
    $data = $API->comm('/ppp/secret/print', [".id" => $id]);
    if (is_array($data) && count($data) > 0) {
      $secret = $data[0];
    }
  }

  $API->disconnect();
} else {
  $errorMsg = 'Gagal koneksi ke MikroTik atau ID tidak valid.';
}
?>

<div class="main-container">
  <div class="row align-middle mb-2">
    <div class="col-6">
      <h3 class="mb-0"><i class="fa fa-pencil"></i> Edit PPP Secret</h3>
    </div>
    <div class="col-6 text-right">
      <a href="./?ppp=secrets&session=<?= $session; ?>" class="btn bg-secondary text-white"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
  </div>

  <?php if ($editSuccess): ?>
    <div class="alert bg-success text-white">PPP Secret berhasil diupdate!</div>
  <?php endif; ?>

  <?php if ($errorMsg): ?>
    <div class="alert bg-danger text-white"><?= $errorMsg ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-12">
      <div class="card box-bordered">
        <div class="card-header"><strong>Form Edit PPP Secret</strong></div>
        <div class="card-body">
          <form method="post" autocomplete="off">
            <div class="row mb-3">
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="name">Nama User</label>
                  <input type="text" id="name" name="name" class="form-control group-item" value="<?= htmlspecialchars($secret['name'] ?? '') ?>" required>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="password">Password</label>
                  <input type="password" id="password" name="password" class="form-control group-item" value="<?= htmlspecialchars($secret['password'] ?? '') ?>" required>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="service">Service</label>
                  <select id="service" name="service" class="form-control group-item" required>
                    <option value="">-- Pilih Service --</option>
                    <option value="pppoe" <?= ($secret['service'] ?? '') == 'pppoe' ? 'selected' : '' ?>>PPPoE</option>
                    <option value="pptp" <?= ($secret['service'] ?? '') == 'pptp' ? 'selected' : '' ?>>PPTP</option>
                    <option value="l2tp" <?= ($secret['service'] ?? '') == 'l2tp' ? 'selected' : '' ?>>L2TP</option>
                    <option value="ovpn" <?= ($secret['service'] ?? '') == 'ovpn' ? 'selected' : '' ?>>OpenVPN</option>
                  </select>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="profile">Profile PPP</label>
                  <input type="text" id="profile" name="profile" class="form-control group-item" value="<?= htmlspecialchars($secret['profile'] ?? '') ?>" required>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="local_address">Local Address</label>
                  <input type="text" id="local_address" name="local_address" class="form-control group-item" value="<?= htmlspecialchars($secret['local-address'] ?? '') ?>">
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="remote_address">Remote Address</label>
                  <input type="text" id="remote_address" name="remote_address" class="form-control group-item" value="<?= htmlspecialchars($secret['remote-address'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12 mb-3">
                <div class="form-group mb-3">
                  <label class="mb-1" for="comment">Comment</label>
                  <input type="text" id="comment" name="comment" class="form-control group-item" value="<?= htmlspecialchars($secret['comment'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12 text-right">
                <button type="submit" class="btn bg-warning text-white px-4 mr-2">
                  <i class="fa fa-save"></i> Update
                </button>
                <a href="./?ppp=secrets&session=<?= $session; ?>" class="btn bg-secondary text-white px-4">Batal</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once('../include/footer.php'); ?>
