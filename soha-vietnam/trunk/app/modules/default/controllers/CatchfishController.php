<?php

define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','tbfish12345');  	// Admin Password

class CatchfishController extends Zend_Controller_Action
{


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

	public function discountcheckAction()
	{
		$this->view->hostUrl = HOST;
		$act = $this->_request->getParam('act');
		$number = $this->_request->getParam('number');
		if($act == 'search') {
			$uid = intval($number);
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$discountInfo = $dalFish->getDiscountInfo($uid, $number);
			$discountInfo['gettime'] = date('Y-m-d H:i:s',$discountInfo['gettime']);
			$pInfo = $dalFish->getProductById($discountInfo['pid']);
			$discountInfo['pname'] = $pInfo['name'];

			$this->view->discountinfo = $discountInfo;
		}
		if($act == 'update') {
			$number = $this->_request->getParam('number');
			$uid = $this->_request->getParam('uid');
			$dalFish = Hapyfish2_Island_Event_Dal_CatchFish::getDefaultInstance();
			$dalFish->updateDiscountInfo($uid, $number);
			//清除缓存
			$key = 'i:e:u:disinfo:' . $uid;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);	
			$cache->delete($key);

			$data = $dalFish->getUserDiscount($uid);
			$cache->add($key, $data);
			
		}		
		$this->view->number = $number;
	}
}