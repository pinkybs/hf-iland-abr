<?php

define('ROOT_DIR', realpath('../'));

require(ROOT_DIR . '/bin/config.php');

build_logger('Island_batch_compute', true);

Zend_Registry::set('db.xml', CONFIG_DIR . '/db.xml');

// daily batch execute
require_once 'Bll/Island/BatchWork.php';

try {
	//debug_log('Batch in!');
	$bllBatchWork = new Bll_Island_BatchWork();
	$bllBatchWork->doComputeByHour(time(), 'mixi');
}
catch (Exception $e) {
	err_log($e->getMessage());
}