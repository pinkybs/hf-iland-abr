<?php

define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','zhangli');  	// Admin Password

class UnlockshipController extends Zend_Controller_Action
{

	protected $_btl_key = 'bottle:list';

	public function init()
	{
		// http 401 验证
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
           $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME ||$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
			Header("WWW-Authenticate: Basic realm=\"Who is god of wealth, Login\"");
			Header("HTTP/1.0 401 Unauthorized");

			echo <<<EOB
				<html><body>
				<h1>Rejected!</h1>
				<big>Wrong Username or Password!</big>
				</body></html>
EOB;
			exit;
		}

		$this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = STATIC_HOST;
        $this->view->hostUrl = HOST;
        $this->view->appId = APP_ID;
        $this->view->appKey = APP_KEY;
	}
	
	public function getsomeAction()
	{
		$sex = '';
		$this->view->sex = $sex;
	}
	
	public function unlockshipAction()
	{
		$unlockship = $this->_request->getParams('uid');

		if(!$unlockship['uid']) {
			echo '没有uid';
			exit;
		}
		
		$uid = $unlockship['uid'];
		
		for($i=5; $i<=6; $i++) {
			if ($i < 10) {
				$month = '0' . $i;
			}
			$year = 2011;
			$yearmonth = $year . $month;

			$dalLog = Hapyfish2_Island_Dal_ConsumeLog::getDefaultInstance();
			$hasLog[$i] = $dalLog->getFXUnlockshipGold($uid, $yearmonth);
		}
		
		$allLog = array_merge($hasLog[5], $hasLog[6]);
		$gold = 0;
		
		foreach ($allLog as $value) {
			$id = substr($value['cid'], 2, 1);
			
			$addGold = 0;
			
			if($id == 1) {
				$addGold = $value['cost'] - 1;
			} else if($id == 2) {
				$addGold = $value['cost'] - 2;
			} else if($id == 3) {
				$addGold = $value['cost'] - 8;
			} else if($id == 4) {
				$addGold = $value['cost'] - 15;
			} else if($id == 5) {
				$addGold = $value['cost'] - 24;
			} else if($id == 6) {
				$addGold = $value['cost'] - 36;
			} else if($id == 7) {
				$addGold = $value['cost'] - 53;
			} else if($id == 8) {
				$addGold = $value['cost'] - 75;
			}
			
			$gold += $addGold;
		}

		$toSendGold = array('gold' => $gold, 'type' => 0, 'time' => time());
		
		try {
			$ok = Hapyfish2_Island_Bll_Gold::add($uid, $toSendGold);
			info_log('uid:'.$uid.' gold:'.$gold, 'unlockShipToSendGold');
		}
		catch (Exception $e) {
			info_log($uid . ' ' . $e, 'unlockShipError');
		}
		
		if($ok) {
			$title = '系统给您<font color="#FF0000">'.$gold.'</font>宝石,修复了船只升级价格问题,我们根据您的额外支出给予补偿';

        	$minifeed = array('uid' => $uid,
                              'template_id' => 0,
                              'actor' => $uid,
                              'target' => $uid,
                              'title' => array('title' => $title),
                              'type' => 3,
                              'create_time' => time());

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}
		
		$this->_redirect("unlockship/getsome");
	}
}