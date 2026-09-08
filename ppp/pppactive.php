<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
	header("Location:../admin.php?id=login");
} else {

// load session MikroTik
	$session = $_GET['session'];

// load config
	include('../include/config.php');
	include('../include/readcfg.php');
	
// lang
  include('../include/lang.php');
  include('../lang/'.$langid.'.php');

// routeros api
	include_once('../lib/routeros_api.class.php');
	include_once('../lib/formatbytesbites.php');
	$API = new RouterosAPI();
	$API->debug = false;
	$API->connect($iphost, $userhost, decrypt($passwdhost));

	$getPppActive = $API->comm("/ppp/active/print");
	$TotalReg = count($getPppActive);

}
?>
<div class="row">
<div class="col-12">
	<div class="card">
		<div class="card-header">
    		<h3><i class="fa fa-bolt"></i> <?= $_ppp_active ?> <?php
				if ($TotalReg < 2) {
					echo "$TotalReg item";
				} elseif ($TotalReg > 1) {
					echo "$TotalReg items";
				};
				?>			</h3>
        </div>
         <div class="card-body overflow">
<table id="dataTable" class="table table-bordered table-hover text-nowrap">
  <thead>
  <tr>
    <th style="width: 25px;"></th>
    <th>Name</th>
    <th>Service</th>
    <th>Caller ID</th>
    <th>Address</th>
    <th class="text-right">Uptime</th>
    <th>Encoding</th>
  </tr>
  </thead>
  <tbody>
<?php
for ($i = 0; $i < $TotalReg; $i++) {
	$pppActive = $getPppActive[$i];
	$id = $pppActive['.id'];
	$name = $pppActive['name'];
	$service = $pppActive['service'];
	$callerid = $pppActive['caller-id'];
	$address = $pppActive['address'];
	$uptime = formatDTM($pppActive['uptime']);
	$encoding = $pppActive['encoding'];
	
	$uriprocess = "'./?remove-pactive=" . $id . "&session=" . $session . "'";

	echo "<tr>";
	echo "<td style='text-align:center;'><span class='pointer'  title='Remove " . $name . "' onclick=loadpage(".$uriprocess.")><i class='fa fa-minus-square text-danger'></i></span></td>";
	echo "<td>" . $name . "</td>";
	echo "<td>" . $service . "</td>";
	echo "<td>" . $callerid . "</td>";
	echo "<td>" . $address . "</td>";
	echo "<td style='text-align:right;'>" . $uptime . "</td>";
	echo "<td>" . $encoding . "</td>";
	echo "</tr>";
}
?>
  </tbody>
</table>
</div>
</div>
</div>
</div>
