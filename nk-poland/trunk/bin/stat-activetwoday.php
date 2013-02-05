<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	    $v = $_SERVER["argv"][1];
	    $day = $v;
	    if ( !$day ) {
	        $day = '1';
	    }
        $time = strtotime("-".$day." day");
        $day = date("Ymd", $time);
        
        //连续登录两天活跃用户
        $file = "/home/admin/stat/stat-data/101/$day/all-101-$day.log";
        $activeResult = Hapyfish2_Island_Stat_Log_Main::getActiveTwoDay($day, $file);
        
        $result = array('activeResult' => $activeResult);
        $data = json_encode($result);

        echo $data . "\n";
}
catch (Exception $e) {
        err_log($e->getMessage());
}