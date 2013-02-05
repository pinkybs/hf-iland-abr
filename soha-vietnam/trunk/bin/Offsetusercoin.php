<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	$num = Hapyfish2_Island_Bll_Offset::offsetUserCion();
//	$time = time();
//	file_put_contents(ROOT_DIR . '/bin/test.txt', $time."\n", FILE_APPEND);
	echo $num;
}
catch (Exception $e) {
	err_log($e->getMessage());
}