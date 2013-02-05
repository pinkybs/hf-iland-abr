<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config-stat.php');
$v = $_SERVER["argv"][1];
try {
	for ( $i = 0; $i < 45; $i++ ) {
		//$dayAgo = $v;
		//if ( !$dayAgo ) {
			$dayAgo = $i;
		//}
		$time = strtotime("-$dayAgo day");
		$day = date("Ymd", $time);
		$day0 = date('Y-m-d', $time);
		$time0 = strtotime($day0) - 3600;
		$file = "/home/admin/data/stat-data/203/$day/all-203-$day.log";
		$result = Hapyfish2_Island_Stat_Log_Shop::handlePlantTemp($day, $time0, $file);
	}
	$data = json_encode($result);
	echo $data;
}
catch (Exception $e) {
	err_log($e->getMessage());
}
