<?php
// PPP Profile Page
include_once('../include/headhtml.php');
include_once('../include/menu.php');
include_once('../include/config.php');
include_once('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');
$API = new RouterosAPI();
$API->debug = false;

$pppProfiles = [];
if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
    $pppProfiles = $API->comm("/ppp/profile/print");
    $API->disconnect();
}

// Handle add profile
$addSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $profileData = [
        "name" => $_POST['name'],
        "local-address" => $_POST['local_address'],
        "remote-address" => $_POST['remote_address'],
        "rate-limit" => $_POST['rate_limit'],
        "comment" => $_POST['comment']
    ];
    if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
        $API->comm("/ppp/profile/add", $profileData);
        $API->disconnect();
        $addSuccess = true;
        // Refresh profile list
        $pppProfiles = $API->comm("/ppp/profile/print");
    }
}
?>
<div class="main-container">
  <div class="row align-middle mb-2">
    <div class="col-6">
      <h3 class="mb-0"><i class="fa fa-id-card"></i> PPP Profiles</h3>
    </div>
    <div class="col-6 text-right">
      <button class="btn bg-success text-white" onclick="document.getElementById('addProfileForm').style.display='block';"><i class="fa fa-plus"></i> Add Profile</button>
    </div>
  </div>
  <?php if ($addSuccess): ?>
    <div class="alert bg-success text-white">Profile berhasil ditambahkan!</div>
  <?php endif; ?>
  <div id="addProfileForm" class="card box-bordered mb-3" style="display:none;">
    <div class="card-header"><strong>Tambah PPP Profile</strong></div>
    <div class="card-body">
      <form method="post" autocomplete="off">
        <div class="row">
          <div class="col-6 mb-2">
            <input type="text" name="name" class="form-control group-item" placeholder="Nama Profile" required>
          </div>
          <div class="col-6 mb-2">
            <input type="text" name="rate_limit" class="form-control group-item" placeholder="Rate Limit (misal: 512k/512k)" required>
          </div>
        </div>
        <div class="row">
          <div class="col-6 mb-2">
            <input type="text" name="local_address" class="form-control group-item" placeholder="Local Address">
          </div>
          <div class="col-6 mb-2">
            <input type="text" name="remote_address" class="form-control group-item" placeholder="Remote Address">
          </div>
        </div>
        <div class="row">
          <div class="col-12 mb-2">
            <input type="text" name="comment" class="form-control group-item" placeholder="Comment">
          </div>
        </div>
        <div class="row">
          <div class="col-12 text-right">
            <button type="submit" class="btn bg-primary text-white"><i class="fa fa-save"></i> Simpan</button>
            <button type="button" class="btn bg-secondary text-white" onclick="document.getElementById('addProfileForm').style.display='none';">Batal</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="overflow box-bordered">
        <table class="table table-bordered table-hover text-nowrap">
          <thead>
            <tr>
              <th class="align-middle text-center">Name</th>
              <th class="align-middle text-center">Rate Limit</th>
              <th class="align-middle text-center">Local Address</th>
              <th class="align-middle text-center">Remote Address</th>
              <th class="align-middle text-center">Comment</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pppProfiles as $profile): ?>
              <tr>
                <td class="align-middle text-center"><?= htmlspecialchars($profile['name'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($profile['rate-limit'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($profile['local-address'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($profile['remote-address'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($profile['comment'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
include_once('../include/footer.php');
?>
