<?php

/**
 * island pay controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/05/11    Huch
 */
class PayController extends Zend_Controller_Action
{
    protected $uid;
    
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            echo '<html><body><script type="text/javascript">window.top.location="http://mixi.jp/run_appli.pl?id=' . APP_ID . '";</script></body></html>';
            exit;
        }
        
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');

        $this->uid = $auth->getIdentity();
        
        $this->view->mixi_platform_api_url = $_COOKIE['mixi_platform_api_url'];
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        $user = $dalUser->getUser($this->uid);
        Bll_User::appendPerson($user);
        $this->view->user = $user;
    }
    
    public function topAction()
    {
        $this->view->sig = md5(APP_SECRET . $this->uid);
        $this->render();
    }
    
	public function webmoneyAction()
    {
    	$this->view->ts = time();
        $this->view->sig = md5(APP_SECRET . $this->uid . $this->view->ts);
        $this->render();
    }
    
    public function rewardplusAction()
    {
    	//$secretKey = 'island-test-island-test-island-test-island-test-island-test-isla';
        $secretKey = 'of8iiqTZmaichu0MZIZASexo5RGU2zNS9jUVd1icKd3FoYPYOX7pVy2cOZO5p9fr';
        $sig = sha1($this->uid . $secretKey);
		$url = "http://island.ppls.jp/?uid=$this->uid&crypt_str=$sig";
		$this->view->rewardUrl = $url;
    }
    
    public function consumerlogsAction()
    {
        $dalGold = Dal_Island_Gold::getDefaultInstance();
        $logs = $dalGold->getUserGoldInfo($this->uid);
        $this->view->count = count($logs);
        $this->view->logs = $logs;
        $this->render();
    }
    
    public function orderlogsAction()
    {        
        $dalPay = Dal_PayLog::getDefaultInstance();
        $logs = $dalPay->getLogs($this->uid);
        $this->view->count = count($logs);
        $this->view->logs = $logs;
        $this->render();
    }    
}