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
        //$time = strtotime('2011-07-17 01:00:00');
        $day = date("Ymd", $time);
        $day0 = date('Y-m-d', $time);
        $time0 = strtotime($day0) - 3600;
        $file = "/data/stat/stat-data/tutorial/$day/all-tutorial-$day.log";
        $result = Hapyfish2_Island_Stat_Log_Tutorial::handle($day, $time0, $file);

        $data = json_encode($result);

        echo $data . "\n";
}
catch (Exception $e) {
        err_log($e->getMessage());
}