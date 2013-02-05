<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	$ok = Hapyfish2_Island_Event_Bll_Peidui::deletePlant();
	
	//$ok = Hapyfish2_Island_Event_Bll_Peidui::getPayInfo();
//	$ok = Hapyfish2_Island_Event_Bll_Peidui::getNewPayInfo();
	echo $ok;
}
catch (Exception $e) {
	info_log($e->getMessage(), 'tempPayErr');
	//err_log($e->getMessage());
}