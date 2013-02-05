<?php

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2011/04/01    zx
 */
class CallbackController extends Zend_Controller_Action
{
    /**
     * index Action
     *
     */
    public function indexAction()
    {

    	$strDate = date('Ymd');
    	$endDate = strtotime($strDate);
		$startDate = $endDate - 60*60*24;

		echo '<br/>';
		echo $strDate;
		echo '<br/>';
		echo date('Ymd', $startDate);
		echo '<br/>';
		echo date('Ymd', $endDate);
		exit;
/*
    	//echo 'callback';
    	$key = 'cad8c2fd-c3ed-c1f5-bcff-c6e1c2cafbf9';
    	$data = array();
    	$data['callback/payment'] = '';
    	$data['payment_key'] = 'b413d6c4e232aad6e394d868e41cb41c';
    	$data['user_id'] = '21263792';
    	$data['apps_no'] = '1645';
    	$data['item_id'] = '1';
    	$data['item_type'] = 'ITEM';
    	$data['item_name'] = '100diamond';
    	$data['item_dotori'] = '1';
    	$data['status'] = 'ready';
    	$data['passthrough'] = '1300966484533_1_1_1012-381e2e8b6214b8d71bb227123d870b3e';
    	$data['mac'] = 'f2797cec857114116b280b46fa8d1218';
    	unset($data['callback/payment']);
    	unset($data['mac']);
    	echo hash_hmac('md5', http_build_query($data), $key);
		*/
    	exit;
    }

	public function paymentAction()
    {
    	//info_log(json_encode($_REQUEST), 'paycallback');

    	$secretKey = 'epqmelrmfkdnsem2010';
    	$payment_key = $_REQUEST['payment_key'];
		$user_id = $_REQUEST['user_id'];
		$apps_no = $_REQUEST['apps_no'];
		$item_id = $_REQUEST['item_id'];
		$item_type = $_REQUEST['item_type'];
		$item_name = $_REQUEST['item_name'];
		$item_dotori = $_REQUEST['item_dotori'];
		$status = $_REQUEST['status'];
		$passThrough = $_REQUEST['passthrough'];
		$mac = $_REQUEST['mac'];
		unset($_REQUEST['callback/payment']);
    	unset($_REQUEST['mac']);
    	$sig = hash_hmac('md5', http_build_query($_REQUEST), $secretKey);

    	header("HTTP/1.0 401 Invalid");
        if ($sig != $mac) {
    		echo 'mac failed';
    		exit;
    	}

    	if ($item_dotori<10 || $item_dotori>100) {
    		echo 'invalid dotori';
    		exit;
    	}

    	//get orderId from passthrough
    	$aryPass = explode('-', $passThrough);
    	$orderId = $aryPass[0];
    	$innerSig = $aryPass[1];
    	if ($innerSig != md5($orderId.APP_SECRET)) {
			echo 'invalid req';
    		exit;
    	}

    	//file log
    	$log = Hapyfish2_Util_Log::getInstance();
		$log->report('pay', $_REQUEST);

		$aryOrder = explode('_', $orderId);
    	$uid = $aryOrder[3];
    	if ('ready' == $status) {
    		$rowOrder = Hapyfish2_Island_Bll_Payment::getOrder($uid, $orderId);
    		if (empty($rowOrder)) {
    			$rowOrder = Hapyfish2_Island_Bll_Payment::createOrder($uid, $orderId, $item_id);
    		}
    		if (empty($rowOrder)) {
    			echo 'order ready failed';
    			exit;
    		}
            header("HTTP/1.0 200 OK");
    		exit;
    	}

    	if ('accepted' == $status) {
    		$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			$result = Hapyfish2_Island_Bll_Payment::confirm($uid, $orderId, array('pid' => $payment_key, 'pay_before_gold' => $userGold));
		    if ($result == 0) {
				$log->report('paydone', array($orderId, $item_dotori, $payment_key, $uid));
				header("HTTP/1.0 200 OK");
			} else if ($result == 3) {
			    header("HTTP/1.0 200 OK");
			} else {
				echo 'order accept failed';
			}
			exit;
	    }

    	header("HTTP/1.0 405 Invalid");
    	echo 'order failed';
    	exit;
    }

	protected function getClientIP()
    {
    	$ip = false;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
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
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
    }

    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }
 }
