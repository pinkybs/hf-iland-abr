<?php

define('ROOT_DIR', realpath('../'));
define('APP_DIR', ROOT_DIR . '/app');
define('CONFIG_DIR', APP_DIR . '/config');
define('MODELS_DIR', APP_DIR . '/models');
define('LIB_DIR', ROOT_DIR . '/lib');
define('TMP_DIR', ROOT_DIR . '/tmp');
define('LOG_DIR', ROOT_DIR . '/logs');
//define('ERR_LOG', false);
//define('ENABLE_DEBUG', false);
//define('DEBUG_LOG', false);
ini_set('display_errors', false);

//date_default_timezone_set('Asia/Shanghai');
date_default_timezone_set('Asia/Seoul');

set_include_path(LIB_DIR . PATH_SEPARATOR . MODELS_DIR . PATH_SEPARATOR . get_include_path());

include 'Zend/Loader.php';
Zend_Loader::registerAutoload();

function build_logger($prefix = '')
{
	require_once 'Zend/Log.php';

	require_once 'Zend/Log/Writer/Stream.php';
	$writer1 = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-error.log');
	$writer2 = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-debug.log');

	Zend_Registry::set('error_logger', new Zend_Log($writer1));
	Zend_Registry::set('debug_logger', new Zend_Log($writer2));
}

function err_log($msg)
{
	try {
		$log_name = 'error_logger';
	    if (!Zend_Registry::isRegistered($log_name)) {
	        //$writer = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
	        $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
	        $logger = new Zend_Log($writer);
	        Zend_Registry::set($log_name, $logger);
	    }
	    else {
	        $logger = Zend_Registry::get($log_name);
	    }

        $logger->log($msg, Zend_Log::ERR);
    }
    catch (Exception $e) {

    }
}

function debug_log($msg)
{
	$logger = Zend_Registry::get('debug_logger');

	try {
		$logger->log($msg, Zend_Log::DEBUG);
	}
	catch (Exception $e) {

	}
}

function info_log($msg, $prefix = 'default')
{
    require_once 'Zend/Log.php';
    $log_name = $prefix . '_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
        require_once 'Zend/Log/Writer/Stream.php';
        $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set($log_name, $logger);
    }
    else {
        $logger = Zend_Registry::get($log_name);
    }

    try {
        $logger->log($msg, Zend_Log::INFO);
    }
    catch (Exception $e) {

    }
}

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
	Hapyfish2_Island_Stat_Bll_DailyPayment::saveDailyPayToDbByDate('20110426');
	exit;
}
catch (Exception $e) {
	info_log($e->getMessage(), 'stat_daily_pay_Err');
}