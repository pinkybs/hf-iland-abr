<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	Hapyfish2_Island_Event_Bll_Peidui::deleteOnePlant();
	echo "OK ";
}
catch (Exception $e) {
	err_log($e->getMessage());
}