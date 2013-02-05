<?php

define('ROOT_DIR', realpath('../'));
include_once ROOT_DIR . '/bin/config.php';

$v = $_SERVER["argv"][1];

try {
    Hapyfish2_Island_Tool_Repair::repairUserPlant($v, 1);
	exit;
}
catch (Exception $e) {
	err_log($e->getMessage());
}