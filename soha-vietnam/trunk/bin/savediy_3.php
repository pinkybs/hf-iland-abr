<?php

define('ROOT_DIR', realpath('../'));
include_once ROOT_DIR . '/bin/config.php';

try {
	$dbId = 3;
	info_log('/****savedbAllUser - islandId = '.$dbId.' - start*****/', 'savedbAllUser_'.$dbId);
    Hapyfish2_Island_Tool_Savediy::savedbAllUser($dbId);
	info_log('/****savedbAllUser - end*******/', 'savedbAllUser_'.$dbId);
	exit;
}
catch (Exception $e) {
	err_log($e->getMessage());
}