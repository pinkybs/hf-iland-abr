<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config-stat.php');

try {
	$time = strtotime("-1 day");
	$day = date("Ymd", $time);
	$day0 = date('Y-m-d', $time);
	$time0 = strtotime($day0) - 3600;
	$file = "/home/admin/data/stat-data/help/$day/all-102-$day.log";
	$result = Hapyfish2_Island_Stat_Log_Help::handle($day, $time0, $file);
	
	echo $result;
}
catch (Exception $e) {
	err_log($e->getMessage());
}
