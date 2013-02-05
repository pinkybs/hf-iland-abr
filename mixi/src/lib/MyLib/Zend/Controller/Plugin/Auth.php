<?php

/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Implement the privilege controller.
 *
 * @package    MyLib_Controller
 * @subpackage MyLib_Controller_Plugin
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/14     Huch
 */
class MyLib_Zend_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * Track user privileges.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
		/*
    	$stop = (defined('APP_STATUS') && APP_STATUS == 0);
        if ($stop && defined('APP_STATUS_DEV') && APP_STATUS_DEV == 1) {

            $ipList = array('220.248.92.126', '117.74.129.20', '114.86.91.94', '58.39.139.246', '124.79.206.249', '114.91.74.231', '58.38.159.68', '218.82.207.61', '58.41.114.230', '58.39.171.152', '122.147.63.223', '59.147.70.199');
            $ip = false;
        	try {
	            if(!empty($_SERVER["HTTP_CLIENT_IP"])){
	                $ip = $_SERVER["HTTP_CLIENT_IP"];
	            }
	            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	                $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
	                if ($ip) {
	                    array_unshift($ips, $ip);
	                    $ip = false;
	                }
	                for ($i = 0, $n = count($ips); $i < $n; $i++) {
	                    if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
	                        $ip = $ips[$i];
	                        break;
	                    }
	                }
	            }
	            if (!$ip) {
	                $ip = $_SERVER['REMOTE_ADDR'];
	            }

	            if ($ip) {
	                if (in_array($ip, $ipList)) {
	                    $stop = false;
	                }
	            }
	        }catch (Exception $e) {

	        }
        }

        if ($stop) {
        	$controller = $request->getControllerName();
        	if ($controller != 'callback') {
				echo '<div>メンテナンス中<br/></div>';
				exit;
        	}
        }
		*/
    }
}