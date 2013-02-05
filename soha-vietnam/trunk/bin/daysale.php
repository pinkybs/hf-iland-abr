<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
    //$logDate = '20110716';
    $d = array(201, 202, 203, 204);
    foreach($d as $k=> $v){
    	Hapyfish2_Island_Stat_Bll_Propsale::updateToDB($v);
    }
	
	echo "OK ";
	/*
	 *     $start = 1310659200;
    $end = 1312387200;
    while ($start <= $end) {
        $logDate = date('Ymd', $start);
        Hapyfish2_Island_Stat_Bll_DaycLoadTm::calcDayData($logDate, $dir);
        $start = $start + 60*60*24;
    }*/
}
catch (Exception $e) {
	err_log($e->getMessage());
}