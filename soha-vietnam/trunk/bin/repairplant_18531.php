<?php

define('ROOT_DIR', realpath('../'));
include_once ROOT_DIR . '/bin/config.php';

try {
    Hapyfish2_Island_Tool_Repair::repairUserPlant(18531, 1);
	exit;
}
catch (Exception $e) {
	err_log($e->getMessage());
}