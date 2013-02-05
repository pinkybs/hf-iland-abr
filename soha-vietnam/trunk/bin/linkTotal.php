<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	$dtYesterday = strtotime("-1 day");
	$logDate = date('Ymd', $dtYesterday);

    $dir = '/home/admin/logs/island.hapyfish.com/stat-data/campPvStat/';
	Hapyfish2_Island_Stat_Bll_LinkTotal::addLinkTotal($logDate, $dir);
	echo "OK ";
}
catch (Exception $e) {
	err_log($e->getMessage());
}