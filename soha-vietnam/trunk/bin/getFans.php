<?php

define('ROOT_DIR', realpath('../'));
include_once ROOT_DIR . '/bin/config.php';

try {
	Hapyfish2_Island_Event_Bll_Fans::getAll();
}
catch (Exception $e) {
	err_log($e->getMessage());
}