<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	Hapyfish2_Island_Bll_Rank::updateRankWeek();
//	$time = time();
//	file_put_contents(ROOT_DIR . '/bin/test.txt', $time."\n", FILE_APPEND);
	echo "OK ";
}
catch (Exception $e) {
	err_log($e->getMessage());
}