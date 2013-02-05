<?php


class RewardplusController extends Zend_Controller_Action
{	
    public function testAction()
    {
        echo 'this is reward plus test';
        exit;    
    }
    
    public function gorewardAction()
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
        
        $uid = $this->uid;
        if (empty($uid)) {
        	echo 'error';
        	exit;
        }
        
        //$secretKey = 'island-test-island-test-island-test-island-test-island-test-isla';
        $secretKey = 'of8iiqTZmaichu0MZIZASexo5RGU2zNS9jUVd1icKd3FoYPYOX7pVy2cOZO5p9fr';
        $sig = sha1($uid . $secretKey);
		$url = "http://island.ppls.jp/?uid=$uid&crypt_str=$sig";
		
		/*
		echo '<a href="javascript:void(0);" onclick="mixi.util.requestExternalNavigateTo(' . $url . ', mixi.util.ExternalSiteType.PAYMENT);">test rewardplus</a>';	    	    	  	  
        exit;*/
		$this->view->rurl = $url;
		$this->render();
	}
	
	public function addpointAction()
	{
		$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
     
		$aryAllowIp = array('59.106.111.162', '59.106.111.164', '117.74.129.20');
//info_log($_SERVER['HTTP_X_FORWARDED_FOR'], 'reward');		
		if (!in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $aryAllowIp)) {
			echo 'error!';
			exit;	
		}
        
		$uid = $this->_request->getParam('user_id');
		$point = $this->_request->getParam('point');
		$promotion_name = $this->_request->getParam('promotion_name');
		$status = $this->_request->getParam('status');
		$transaction_id = $this->_request->getParam('transaction_id', 0);
		
		$rstCode = 0;
		if (empty($uid) || empty($transaction_id)) {
			$rstCode = 1;
		}
		
		if (empty($rstCode)) {
			$bllReward = new Bll_Island_RewardPlus();
			$rst = $bllReward->addPoint($transaction_id, $promotion_name, $uid, $status, $point);
			$rstCode = $rst ? 0 : 2;
		}
		
		//0正常終了APIへのアクセスが正常に終了した 1エラー1APIへのアクセスパラメータに不備があった  2エラー2APIのアクセス処理中にエラーが発生した
		$xmlRes = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xmlRes .= "<!DOCTYPE parameters>\n";
		$xmlRes .= "<parameters>\n";
		$xmlRes .= "<result_code>$rstCode</result_code>\n";
		$xmlRes .= "<transaction_id>$transaction_id</transaction_id>\n";
		$xmlRes .= "</parameters>";
		
		header('Content-Type: text/xml');		
		echo $xmlRes;
        exit;
	}
}