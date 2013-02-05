<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	Hapyfish2_Island_Event_Bll_OneGoldShop::resetBoxInfo();
	echo "OK ";
}
catch (Exception $e) {
	err_log($e->getMessage());
}