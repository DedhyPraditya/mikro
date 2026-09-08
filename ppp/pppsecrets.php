<?php
// PPP Secrets Page
include_once('../include/headhtml.php');
include_once('../include/menu.php');

// Load config and MikroTik API
include_once('../include/config.php');
include_once('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');
$API = new RouterosAPI();
$API->debug = false;

$pppSecrets = [];
$pppActive = [];
$deleteSuccess = false;
if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
  // Proses hapus PPP Secret
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $API->comm('/ppp/secret/remove', [ ".id" => $_POST['delete_id'] ]);
    $deleteSuccess = true;
  }
  $pppSecrets = $API->comm("/ppp/secret/print");
  $pppActive = $API->comm("/ppp/active/print");
  $API->disconnect();
}

// Count summary
// Hitung summary
$totalSecrets = count($pppSecrets);
$totalOnline = 0;
$totalOffline = 0;
// Buat array user yang online
$onlineUsers = [];
if (is_array($pppActive)) {
  foreach ($pppActive as $active) {
    if (isset($active['name'])) {
      $onlineUsers[] = $active['name'];
    }
  }
}
foreach ($pppSecrets as $secret) {
  if (($secret['disabled'] ?? 'false') == 'true') continue;
  if (in_array($secret['name'] ?? '', $onlineUsers)) {
    $totalOnline++;
  } else {
    $totalOffline++;
  }
}
?>
<div class="main-container">
  <div class="row align-middle mb-2">
    <div class="col-6">
      <h3 class="mb-0"><i class="fa fa-key"></i> PPP Secrets</h3>
    </div>
    <div class="col-6 text-right">
      <a href="./?ppp=addsecret&session=<?= $session; ?>" class="btn bg-success text-white"><i class="fa fa-plus"></i> Add</a>
    </div>
  </div>
  <?php if ($deleteSuccess): ?>
    <div class="alert bg-success text-white">PPP Secret berhasil dihapus!</div>
  <?php endif; ?>
  <div class="row mb-3">
    <div class="col-4">
      <div class="box box-bordered text-center">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-key"></i></div>
          <div class="box-group-area">
            <span>Total Secrets</span><br>
            <span class="h3 mb-0"><?= $totalSecrets ?></span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="box box-bordered text-center">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-link"></i></div>
          <div class="box-group-area">
            <span>Total Online</span><br>
            <span class="h3 mb-0"><?= $totalOnline ?></span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="box box-bordered text-center">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-chain-broken"></i></div>
          <div class="box-group-area">
            <span>Total Offline</span><br>
            <span class="h3 mb-0"><?= $totalOffline ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mb-2">
    <div class="col-12">
      <input type="text" class="form-control group-item" id="pppSecretFilter" placeholder="Filter">
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="overflow box-bordered">
        <table class="table table-bordered table-hover text-nowrap" id="pppSecretTable">
          <thead>
            <tr>
              <th class="align-middle text-center"></th>
              <th class="align-middle text-center" style="width:60px;"></th>
              <th class="align-middle text-center">Name</th>
              <th class="align-middle text-center">Status</th>
              <th class="align-middle text-center">Service</th>
              <th class="align-middle text-center">Caller ID</th>
              <th class="align-middle text-center">Profile</th>
              <th class="align-middle text-center">Local Address</th>
              <th class="align-middle text-center">Remote Address</th>
              <th class="align-middle text-center">Last Logged Out</th>
              <th class="align-middle text-center">Comment</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pppSecrets as $secret): ?>
              <tr>
                <td class="align-middle text-center"><i class="fa fa-user-secret"></i></td>
                <td class="align-middle text-center">
                  <a href="/ppp/editsecret.php?id=<?= urlencode($secret['.id'] ?? '') ?>&session=<?= $session; ?>" 
                    class="btn btn-sm bg-warning text-white" 
                    title="Edit" 
                    style="font-size:12px; padding:2px 6px;">
                    <i class="fa fa-pencil" style="font-size:9px;"></i>
                    </a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus PPP Secret ini?');">
                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($secret['.id'] ?? '') ?>">
                    <button type="submit" class="btn btn-sm bg-danger text-white" title="Hapus" style="font-size:10px; padding:2px 6px;">
                    <i class="fa-solid fa-minus" style="font-size:9px;"></i>
                    </button>
                  </form>
                </td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['name'] ?? '-') ?></td>
                <td class="align-middle text-center" style="padding:10px 0; font-size:16px;">
                  <?php if (($secret['disabled'] ?? 'false') == 'true'): ?>
                    <span class="badge bg-secondary text-white px-3 py-2" style="font-size:15px; letter-spacing:1px; border-radius:12px;">disabled</span>
                  <?php elseif (in_array($secret['name'] ?? '', $onlineUsers)): ?>
                    <span class="badge bg-success text-white px-4 py-3" style="font-size:15px; letter-spacing:1px; border-radius:12px;">online</span>
                  <?php else: ?>
                    <span class="badge bg-danger text-white px-4 py-3" style="font-size:15px; letter-spacing:1px; border-radius:12px;">offline</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['service'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['caller-id'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['profile'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['local-address'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['remote-address'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['last-logged-out'] ?? '-') ?></td>
                <td class="align-middle text-center"><?= htmlspecialchars($secret['comment'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('pppSecretFilter').addEventListener('input', function() {
  var filter = this.value.toLowerCase();
  var rows = document.querySelectorAll('#pppSecretTable tbody tr');
  rows.forEach(function(row) {
    var text = row.textContent.toLowerCase();
    row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
  });
});
</script>
<?php
include_once('../include/footer.php');
?>
