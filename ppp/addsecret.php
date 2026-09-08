<?php
// Add PPP Secret Page (Enterprise UI - Final Polish)
include_once('../include/headhtml.php');
include_once('../include/menu.php');
include_once('../include/config.php');
include_once('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');
$API = new RouterosAPI();
$API->debug = false;

$addSuccess = false;
$deleteSuccess = false;
$serviceList = ['pppoe','pptp','l2tp','ovpn','any'];
$pppSecrets = [];
if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
  // Ambil daftar profile PPP
  $profiles = $API->comm('/ppp/profile/print');
  if (is_array($profiles)) {
    foreach ($profiles as $p) {
      if (isset($p['name'])) {
        $profileList[] = $p['name'];
      }
    }
  }
  // Jika submit form
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $secretData = [
      "name" => $_POST['name'],
      "password" => $_POST['password'],
      "service" => $_POST['service'],
      "profile" => $_POST['profile'],
      "local-address" => $_POST['local_address'],
      "remote-address" => $_POST['remote_address'],
      "comment" => $_POST['comment']
    ];
    $API->comm("/ppp/secret/add", $secretData);
    $addSuccess = true;
  }
  $API->disconnect();
}
?>

<style>
/* Enterprise UI Custom CSS - Compact & Harmonized */
.ent-container {
    padding: 15px;
}
.ent-card {
    background-color: #2b2b2b;
    border-radius: 6px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
    border: 1px solid #333;
    overflow: hidden;
    margin-bottom: 20px;
}
.ent-card-header {
    background: linear-gradient(to right, #252525, #2b2b2b); /* Dark Header */
    padding: 15px 25px;
    border-bottom: 1px solid #333;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.ent-card-body {
    padding: 25px;
    background-color: #2b2b2b;
    color: #fff;
}
.ent-title {
    font-size: 18px;
    font-weight: 700;
    color: #e0e0e0; /* Off-white text */
    margin: 0;
}
.ent-subtitle {
    font-size: 13px;
    color: #999;
    margin-top: 5px;
    margin-bottom: 0;
}
.ent-badge {
    background-color: #007bff;
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
    margin-bottom: 5px;
    align-self: flex-start;
}

/* Form Styles */
.section-title {
    color: #66afe9;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #333;
    padding-bottom: 10px;
}
.section-title i {
    margin-right: 10px;
    font-size: 14px;
}
.ent-form-group {
    margin-bottom: 20px;
}
.ent-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 13px;
    color: #ccc;
    letter-spacing: 0.3px;
}
.ent-label span {
    color: #ff4d4d;
}
.ent-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.ent-input-icon {
    position: absolute;
    left: 15px;
    color: #888;
    z-index: 2;
    font-size: 13px;
    transition: color 0.3s;
}
.ent-input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    background-color: #1e1e1e; /* Darker input bg */
    border: 1px solid #444;
    border-radius: 4px;
    color: #eee;
    font-size: 13px;
    transition: all 0.3s;
    height: 40px; /* Comfortable touch target */
}
.ent-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    background-color: #1a1a1a;
}
.ent-input:focus + .ent-input-icon, .ent-input-wrapper:focus-within .ent-input-icon {
    color: #007bff; /* Highlight icon on focus */
}

.ent-select {
    width: 100%;
    padding: 10px 15px 10px 40px;
    background-color: #1e1e1e;
    border: 1px solid #444;
    border-radius: 4px;
    color: #eee;
    font-size: 13px;
    height: 40px;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 14px;
    cursor: pointer;
}
.helper-text {
    font-size: 11px;
    color: #777;
    margin-top: 6px;
    margin-left: 2px;
    display: block;
}

/* Harmonized Guide Bar */
.guide-bar {
    background-color: #252525;
    border: 1px solid #333;
    border-radius: 6px;
    padding: 20px;
    margin-top: 10px;
}
.guide-header {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}
.guide-title {
    font-size: 13px;
    font-weight: 700;
    color: #aaa;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.guide-content {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
}
.guide-item {
    flex: 1;
    padding: 0 15px;
    border-right: 1px solid #333; /* Darker separator */
    min-width: 200px;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
}
.guide-item:last-child {
    border-right: none;
}
.guide-icon-box {
    width: 32px;
    height: 32px;
    background-color: #333;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #66afe9;
    font-size: 14px;
    margin-right: 12px;
    flex-shrink: 0;
}
.guide-text-group {
    display: flex;
    flex-direction: column;
}
.guide-label {
    font-size: 12px;
    font-weight: 600;
    color: #ddd;
    margin-bottom: 3px;
}
.guide-text {
    font-size: 11px;
    color: #888;
    line-height: 1.4;
    margin: 0;
}

/* Grid Layout */
.row-ent {
    display: flex;
    flex-wrap: wrap;
    margin-right: -12px;
    margin-left: -12px;
}
.col-full {
    flex: 0 0 100%;
    max-width: 100%;
    padding-right: 12px;
    padding-left: 12px;
}
.col-half {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 12px;
    padding-left: 12px;
}
.d-flex-end {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}
.btn-save {
    background-color: #007bff; /* Primary Blue */
    color: #fff;
    border: none;
    padding: 10px 30px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 13px;
    box-shadow: 0 4px 6px rgba(0,123,255,0.2);
    cursor: pointer;
    transition: all 0.2s;
}
.btn-save:hover {
    background-color: #0069d9;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0,123,255,0.3);
}
.btn-cancel {
    color: #aaa;
    background: transparent;
    border: 1px solid transparent;
    padding: 8px 20px;
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    margin-right: 10px;
    border-radius: 4px;
    transition: all 0.2s;
}
.btn-cancel:hover {
    color: #fff;
    border-color: #444;
    background-color: #333;
    text-decoration: none;
}
</style>

<div class="main-container ent-container">
    <div class="row-ent">
        
        <!-- Main Form (Full Width) -->
        <div class="col-full">
            <div class="ent-card">
                <div class="ent-card-header">
                    <span class="ent-badge">Formulir</span>
                    <h1 class="ent-title">Tambah PPP Secret Baru</h1>
                    <p class="ent-subtitle">Isi kredensial pengguna di bawah ini.</p>
                </div>
                
                <div class="ent-card-body">
                    <form method="post" autocomplete="off" id="pppSecretForm">
                        
                        <!-- Row 1: Credentials -->
                        <div class="section-title">
                            <i class="fa fa-id-card-o"></i> Kredensial & Akses
                        </div>

                        <div class="row-ent">
                            <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="name">Username <span>*</span></label>
                                    <div class="ent-input-wrapper">
                                        <input type="text" id="name" name="name" class="ent-input" placeholder="Username" required>
                                        <i class="fa fa-user ent-input-icon"></i>
                                    </div>
                                    <small class="helper-text">Identitas unik login client.</small>
                                </div>
                            </div>
                            <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="password">Password <span>*</span></label>
                                    <div class="ent-input-wrapper">
                                        <input type="password" id="password" name="password" class="ent-input" placeholder="Password" required>
                                        <i class="fa fa-lock ent-input-icon"></i>
                                    </div>
                                    <small class="helper-text">Kunci keamanan akun.</small>
                                </div>
                            </div>
                        </div>

                         <!-- Row 2: Service -->
                        <div class="row-ent">
                            <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="service">Service <span>*</span></label>
                                    <div class="ent-input-wrapper">
                                        <select id="service" name="service" class="ent-select" required>
                                            <option value="">Pilih Service...</option>
                                            <?php foreach ($serviceList as $service): ?>
                                                <option value="<?= htmlspecialchars($service) ?>"><?= htmlspecialchars(strtoupper($service)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="fa fa-wrench ent-input-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="profile">Profile <span>*</span></label>
                                    <div class="ent-input-wrapper">
                                        <select id="profile" name="profile" class="ent-select" required>
                                            <option value="">Pilih Profile...</option>
                                            <?php foreach ($profileList as $profile): ?>
                                                <option value="<?= htmlspecialchars($profile) ?>"><?= htmlspecialchars($profile) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="fa fa-tachometer ent-input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Network & Comment -->
                         <div class="section-title" style="margin-top: 15px;">
                            <i class="fa fa-globe"></i> Konfigurasi Jaringan (Opsional)
                        </div>

                        <div class="row-ent">
                             <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="local_address">Local IP Address</label>
                                    <div class="ent-input-wrapper">
                                        <input type="text" id="local_address" name="local_address" class="ent-input" placeholder="IP Gateway">
                                        <i class="fa fa-hdd-o ent-input-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-half">
                                <div class="ent-form-group">
                                    <label class="ent-label" for="remote_address">Remote IP Address</label>
                                    <div class="ent-input-wrapper">
                                        <input type="text" id="remote_address" name="remote_address" class="ent-input" placeholder="IP Client">
                                        <i class="fa fa-desktop ent-input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row-ent">
                             <div class="col-full">
                                <div class="ent-form-group mb-0">
                                    <label class="ent-label" for="comment">Catatan Tambahan</label>
                                    <div class="ent-input-wrapper">
                                        <input type="text" id="comment" name="comment" class="ent-input" placeholder="Tulis catatan di sini...">
                                        <i class="fa fa-comment-o ent-input-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex-end" style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
                            <a href="./?ppp=secrets&session=<?= $session; ?>" class="btn-cancel">Batal</a>
                            <button type="submit" id="btnSave" class="btn-save">
                                <i class="fa fa-save"></i> Simpan Data
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Horizontal Guide Below Form -->
        <div class="col-full">
            <div class="guide-bar">
                <div class="guide-header">
                     <h5 class="guide-title"><i class="fa fa-info-circle mr-2"></i> Panduan Singkat</h5>
                </div>
                <div class="guide-content">
                    <div class="guide-item">
                        <div class="guide-icon-box"><i class="fa fa-user"></i></div>
                        <div class="guide-text-group">
                            <span class="guide-label">Username & Password</span>
                            <p class="guide-text">Wajib diisi unik dan aman untuk autentikasi client.</p>
                        </div>
                    </div>
                    <div class="guide-item">
                        <div class="guide-icon-box"><i class="fa fa-wifi"></i></div>
                        <div class="guide-text-group">
                            <span class="guide-label">Service & Profile</span>
                            <p class="guide-text">Tentukan protokol (PPPoE) dan limitasi bandwidth pengguna.</p>
                        </div>
                    </div>
                    <div class="guide-item">
                        <div class="guide-icon-box"><i class="fa fa-globe"></i></div>
                        <div class="guide-text-group">
                             <span class="guide-label">IP Address</span>
                             <p class="guide-text">Opsional. Isi hanya jika pengguna membutuhkan IP statis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php
include_once('../include/footer.php');
?>
