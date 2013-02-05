<?php

include_once ROOT_DIR . '/app/config/define.php';

ini_set('display_errors', false);

date_default_timezone_set('Asia/Shanghai');

set_include_path(LIB_DIR . PATH_SEPARATOR . get_include_path());

include 'Zend/Loader.php';
Zend_Loader::registerAutoload();

function err_log($msg)
{
	$logfile = LOG_DIR . '/err.bin.log';
	
	$time = date('Y-m-d h:i:s');
	
	file_put_contents($logfile, $time . "\t" . $msg . "\r\n", FILE_APPEND);
}

function debug_log($msg)
{
	$logfile = LOG_DIR . '/debug.bin.log';
	
	$time = date('Y-m-d h:i:s');
	
	file_put_contents($logfile, $time . "\t" . $msg . "\r\n", FILE_APPEND);
}

function info_log($msg, $prefix = 'default')
{
	$logfile = LOG_DIR . '/info.' . $prefix . '.bin.log';
	
	$time = date('Y-m-d h:i:s');
	
	file_put_contents($logfile, $time . "\t" . $msg . "\r\n", FILE_APPEND);
}
