<?php
// Add PPP Secret Page (root)
include_once('include/headhtml.php');
include_once('include/menu.php');
include_once('include/config.php');
include_once('include/readcfg.php');
include_once('lib/routeros_api.class.php');
$API = new RouterosAPI();
$API->debug = false;

$addSuccess = false;
// Get server and profile list
$serverList = ['all'];
$profileList = [];
if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
    $profileList = $API->comm("/ppp/profile/print");
    $API->disconnect();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $secretData = [
        "name" => $_POST['name'],
        "password" => $_POST['password'],
        "service" => $_POST['service'],
        "profile" => $_POST['profile'],
        "local-address" => $_POST['local_address'],
        "remote-address" => $_POST['remote_address'],
        "comment" => $_POST['comment'],
        // Optional fields
        "limit-uptime" => $_POST['time_limit'],
        "limit-bytes-in" => $_POST['data_limit'] ? ($_POST['data_unit'] == 'MB' ? $_POST['data_limit']*1024*1024 : $_POST['data_limit']*1024*1024*1024) : '',
    ];
    if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
        $API->comm("/ppp/secret/add", $secretData);
        $API->disconnect();
        $addSuccess = true;
    }
}
?>
<div class="main-container">
  <div class="row" style="margin-top:32px;">
    <div class="col-md-7">
      <div class="card box-bordered mb-4" style="background:#2f353a;color:#f3f4f5;">
        <div class="card-header" style="background:#23272b;color:#f3f4f5;"><i class="fa fa-user-plus"></i> Add PPP Secret</div>
        <div class="card-body">
          <?php if ($addSuccess): ?>
            <div class="alert bg-success text-white mb-3">PPP Secret berhasil ditambahkan!</div>
          <?php endif; ?>
          <form method="post" autocomplete="off">
            <div class="form-group row mb-3">
              <label for="server" class="col-md-4 col-form-label text-bold">Server</label>
              <div class="col-md-8">
                <select name="server" class="form-control group-item">
                  <option value="all">all</option>
                  <!-- ...existing code for server list... -->
                </select>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="name" class="col-md-4 col-form-label text-bold">Name</label>
              <div class="col-md-8">
                <input type="text" name="name" class="form-control group-item" required placeholder="Masukkan nama user PPP">
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="password" class="col-md-4 col-form-label text-bold">Password</label>
              <div class="col-md-8">
                <input type="password" name="password" class="form-control group-item" required placeholder="Masukkan password PPP">
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="profile" class="col-md-4 col-form-label text-bold">Profile</label>
              <div class="col-md-8">
                <select name="profile" class="form-control group-item">
                  <?php foreach ($profileList as $profile): ?>
                    <option value="<?= htmlspecialchars($profile['name']) ?>"><?= htmlspecialchars($profile['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="service" class="col-md-4 col-form-label text-bold">Service</label>
              <div class="col-md-8">
                <select name="service" class="form-control group-item">
                  <option value="pppoe">PPPoE</option>
                  <option value="pptp">PPTP</option>
                  <option value="l2tp">L2TP</option>
                  <option value="ovpn">OVPN</option>
                  <option value="any">Any</option>
                </select>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="time_limit" class="col-md-4 col-form-label text-bold">Time Limit</label>
              <div class="col-md-8">
                <input type="text" name="time_limit" class="form-control group-item" placeholder="30d, 12h, 4w3d">
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="data_limit" class="col-md-4 col-form-label text-bold">Data Limit</label>
              <div class="col-md-5">
                <input type="number" name="data_limit" class="form-control group-item" min="0" placeholder="Data Limit">
              </div>
              <div class="col-md-3">
                <select name="data_unit" class="form-control group-item">
                  <option value="MB">MB</option>
                  <option value="GB">GB</option>
                </select>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="comment" class="col-md-4 col-form-label text-bold">Comment</label>
              <div class="col-md-8">
                <input type="text" name="comment" class="form-control group-item" placeholder="Catatan tambahan (opsional)">
              </div>
            </div>
            <hr class="spa" style="margin-bottom:18px;">
            <div class="row">
              <div class="col-6 text-left">
                <a href="pppsecrets.php?session=<?= $session; ?>" class="btn bg-warning text-dark" style="height:32px;padding:4px 18px;font-size:15px;"><i class="fa fa-times"></i> Close</a>
              </div>
              <div class="col-6 text-right">
                <button type="submit" class="btn bg-info text-white" style="height:32px;padding:4px 18px;font-size:15px;"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card box-bordered mb-4" style="background:#2f353a;color:#f3f4f5;">
        <div class="card-header" style="background:#23272b;color:#f3f4f5;"><i class="fa fa-info-circle"></i> Read Me</div>
        <div class="card-body">
          <div>Format Time Limit.<br>[wdhm] Example : 30d = 30days, 12h = 12hours, 4w3d = 31days.<br><br>Add User with Time Limit.<br>Should Time Limit &lt; Validity.</div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
include_once('include/footer.php');
?>
