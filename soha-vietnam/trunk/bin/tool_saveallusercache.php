<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

try {
	
        $dbId = $_SERVER["argv"][1];
        if ( !$dbId ) {
            $dbId = 0;
        }

        $tableId = $_SERVER["argv"][2];
        if ( !$tableId ) {
            $tableId = 0;
        }
        
        for ( $i=0;$i<4;$i++ ) {
        	for ( $j=0;$j<10;$j++ ) {
        		Hapyfish2_Island_Tool_Savecache::saveAllUserCacheByDB($i, $j);
        	}
        }
        
	//Hapyfish2_Island_Tool_Savecache::saveAllUserCacheByDB($dbId, $tableId);
	echo "OK ";
}
catch (Exception $e) {
    err_log($e->getMessage());
}
