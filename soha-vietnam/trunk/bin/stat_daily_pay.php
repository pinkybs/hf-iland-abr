<?php

define('ROOT_DIR', realpath('../'));
include_once ROOT_DIR . '/bin/config.php';

function buildStatDbAdapter()
{
    require_once CONFIG_DIR.'/database-stat.php';

	//$params = array('host' => '121.78.69.36', 'username' => 'worker', 'password' => 'pqnx4HVFDh', 'dbname' => 'islandv2_log_stat');
	$params = $DATABASE_STAT;
    $params['driver_options'] = array(
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 4
    );

    $dbAdapter =  Zend_Db::factory('PDO_MYSQL', $params);
    $dbAdapter->query("SET NAMES utf8");
    return $dbAdapter;
}

try {
	Hapyfish2_Island_Stat_Bll_DailyPayment::saveDailyPayToDb();
	exit;
}
catch (Exception $e) {
	info_log($e->getMessage(), 'stat_daily_pay_Err');
}