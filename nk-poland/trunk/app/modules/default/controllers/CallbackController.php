<?php

/**
 * application callback controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/08/07    HLJ
 */
class CallbackController extends Zend_Controller_Action
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'callback';
    	exit;
    }

    public function payAction()
    {
info_log(json_encode($_POST), 'testpay');
    	/*$ip = $this->getClientIP();
    	$allowIp = array('122.147.63.81', '122.147.63.82', '122.147.63.44', '122.147.63.224');
       	if (!in_array($ip, $allowIp)) {
			$result = array('result' => '1', 'message' => 'ip address invalid');
            echo Zend_Json::encode($result);
            exit;
        }*/

        /*
        userid : userid of the end-user
        game : the backend gamename of your game
        ts : timestamp
        coins : amount of virtual currency the user bought
        vcurrency : name of virtual currency
        price : price in real money
        currency : currency of the real money (e.g. EUR or EGB)
        tid : transaction id
        signature : hash-value to check integrity of the call (see example below)
        */
        //Check signature
        $gPostMsg = "";
        $elements = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'signature') {
                $elements[] = "$key=$value&";
            }
        }
        sort($elements);
        for ($i = 0; $i < count($elements); $i++) {
            $gPostMsg .= $elements[$i];
        }
        $gPostMsg .= APP_SECRET; //Secret Key Of Your Game (provided by Plinga)
        $sourcesig = $_POST['signature'];
        if ($sourcesig != md5($gPostMsg)) {
            die('Signature Error');
        }

        //Signature check was successful Add coins to the user in your database
        $puid = $_POST['userid'];
        $transId = $_POST['tid'];
        $gameMoney = $_POST['coins'];
        $amount = $_POST['price'];
        $currency = $_POST['currency'];

        if ($amount < 0 || $gold < 0) {
            die('invalid amount or game money');
        }

        $rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
        if (empty($rowUser)) {
            die('no such user '.$puid);
        }

        info_log(json_encode($_POST), 'FromPaymentCall_'.date('Ymd'));
		$result = Hapyfish2_Island_Bll_Payment::pay($transId, $currency, $amount, $gameMoney, $rowUser['uid']);
        if ($result == 0) {
            $log = Hapyfish2_Util_Log::getInstance();
            $aryLog = array($rowUser['uid'], $puid, $transId, $currency, $amount, $gameMoney);
            $log->report('payment', $aryLog);
            $message = "*OK*";
        }
        elseif ($result == 2){
            $message = "*OK*";
        }
        else {
            $message = 'failed';
        }

        echo($message);
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