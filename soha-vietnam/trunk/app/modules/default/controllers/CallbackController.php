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
    	$ip = $this->getClientIP();
    	$allowIp = array('122.147.63.81', '122.147.63.82', '122.147.63.44', '122.147.63.224');
       	if (!in_array($ip, $allowIp)) {
			$result = array('result' => '1', 'message' => 'ip address invalid');
            echo Zend_Json::encode($result);
            exit;
        }
    	
        $puid = $this->_request->getPost('mj_sig_uid');
        $transaction_id = $this->_request->getPost('mj_sig_transaction_id');
        $currency_amount = $this->_request->getPost('mj_sig_currency_amount');
        $game_money = $this->_request->getPost('mj_sig_game_money');
        $time = $this->_request->getPost('mj_sig_time');
        $sig = $this->_request->getPost('mj_sig');

        if (md5("currency_amount=$currency_amount" . "game_money=$game_money" . "time=$time"  . "transaction_id=$transaction_id" . "uid=$puid" . APP_SECRET) != $sig) {
            $result = array('result' => '1', 'message' => 'sig error');
            echo Zend_Json::encode($result);
            exit;
        }

        if ($currency_amount < 0 || $game_money < 0) {
        	$result = array('result' => '1', 'message' => 'invalid amount or game money');
            echo Zend_Json::encode($result);
            exit;
        }

        $rowUser = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
        if (empty($rowUser)) {
        	$result = array('result' => '1', 'message' => 'no user');
            echo Zend_Json::encode($result);
            exit;
        }

        info_log(json_encode($_POST), 'FromPayment_'.date('Ymd'));
		$result = Hapyfish2_Island_Bll_Payment::pay($transaction_id, $currency_amount, $game_money, $rowUser['uid']);
        if ($result == 0) {
            $log = Hapyfish2_Util_Log::getInstance();
            $aryLog = array($rowUser['uid'], $puid, $transaction_id, $currency_amount, $game_money, $time);
            $log->report('payment', $aryLog);
            $message = 'ok';
        }
        elseif ($result == 2){
            $message = 'repeat';
        }
        else {
            $message = 'failed';
        }

        $result = array('result' => "$result", 'message'=> $message);
        echo Zend_Json::encode($result);
        exit;
    }

    public function permissionAction()
    {
    	header('Content-Type: text/html; charset=utf-8');
    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);

        $skey = $_COOKIE['hf_skey'];

    	if (!$skey) {
    		echo '0';
    		exit();
    	}

    	$tmp = explode('.', $skey);
    	if (empty($tmp)) {
    		echo '0';
    		exit();
    	}
    	$count = count($tmp);
    	if ($count != 5 && $count != 6) {
    		echo '0';
    		exit();
    	}

        // $uid = $tmp[0];
        $uid = $tmp[1];
        $skey = base64_decode($tmp[2]);

	    if( empty( $uid ) || empty( $skey ) ) {
	    	echo '0';
	    	exit();
	    }

        $facebook = Facebook_Client2::getInstance();
	    $facebook->setUser($uid, $skey);

	    $permissions = $facebook->getPermissions();
	    if (array_key_exists('email', $permissions)
	        && array_key_exists('read_stream', $permissions)
	        && array_key_exists('user_likes', $permissions)) {

	        $isfanTF = $facebook->getIsFan(); // true|false
    	    $isfanTF = ($isfanTF === true ? '1' : '0') ;
    	    echo $isfanTF;
	    }
	    else {
	        echo '-1';
	    }

        exit();
    }

    /**
     * feed Action
     *
     */
    public function feedAction()
    {
        echo '<script type="text/javascript">parent.showFeed();</script>feed finish！';
        exit;
    }
    
    /**
     * invite Action
     *
     */
    public function inviteAction()
    {
    	$skey = $_COOKIE['hf_skey'];
    	if($skey) {
    		$tmp = explode('.', $skey);
    		$uid = $tmp[0];			//uid
    		$platformId = $tmp[1];	//platform id
    	}
    	if($uid && $_REQUEST['request_ids']) {
	    	$data = array(
	    		'actor'		=>	$uid,
	    		'target'	=>	'',
	    		'status'	=>	1,
	    		'sig'		=>	$_REQUEST['request_ids'],
	    		'time'		=>	time()	
	    	);
	    	$dalInvite = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
	    	$dalInvite->addInvite($data);
	    	
    	}
    	info_log(json_encode($_REQUEST), 'InviteTest_CallBack');
        echo '<script type="text/javascript">parent.showInvite();</script>invite finish！';
        exit;
    }
    public function finshnewAction()
    {
    	$uid = $this->_request->getParam('uid');
    	$key = 'i:u:finshnew:help:' . $uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$hasGet = $cache->get($key);
    	$coin = 1000;
    	if($hasGet === false) { 
    		Hapyfish2_Island_HFC_User::incUserCoin($uid, $coin);
    		$cache->set($key , 1);
    	}
        echo '<script type="text/javascript">parent.finshNewFeed();</script>feed finish！';
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