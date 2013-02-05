<?php

define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','suijishengchangde');  	// Admin Password

class OnegoldController extends Zend_Controller_Action
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
	
	function vaild()
	{

	}

	function check()
	{
		$uid = $this->_request->getParam('uid');
		if (empty($uid)) {
			echo 'uid can not empty';
			exit;
		}

		$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
		if (!$isAppUser) {
			echo 'uid error, not app user';
			exit;
		}

		return $uid;
	}
	
	public function boxAction()
	{
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$dataVo = $db->boxInfo();
		
		$this->view->datavo = $dataVo;
	}
	
	public function boxupdateAction()
	{
		$dataVo = $this->_request->getParam('data');
		
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$db->boxUpdate($dataVo);
		
		$this->_redirect("onegold/box");
	}
	
	public function incnewboxAction()
	{
		$data = $this->_request->getParams('');
		
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$db->incNewBox();
		
		$this->_redirect("onegold/box");
	}
	
	public function indexAction()
	{
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$dataVo = $db->AllData();
		
		$this->view->datavo = $dataVo;
	}
	
	public function updateAction()
	{
		$dataVo = $this->_request->getParam('data');
		
		$startTime = strtotime($dataVo['start_time']);
		$endTime = strtotime($dataVo['end_time']);
		
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$db->update($dataVo, $startTime, $endTime);
		
		$this->_redirect("onegold/index");
	}
	
	public function addnewAction()
	{
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
		$db->addNewOne();
		
		$this->_redirect("onegold/index");
	}
	
	public function clearallAction()
	{
		$key = 'i:e:onegold:all';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
			
		$this->_redirect("onegold/index");
	}
	
}