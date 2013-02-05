<?php

/**
 * island pay controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/05/11    Huch
 */
class WebmoneyController extends Zend_Controller_Action
{
	private $_shopNo = 'Web0002010';
	private $_contractCode = 'KYKTH0002754';
    private $_items = array('GDSTH0001'=>array('id'=>'GDSTH0001', 'name'=>'ダイヤ25個', 'money'=>500, 'diamond'=>25), 
    						'GDSTH0002'=>array('id'=>'GDSTH0002', 'name'=>'ダイヤ50個', 'money'=>1000, 'diamond'=>50), 
    						'GDSTH0003'=>array('id'=>'GDSTH0003', 'name'=>'ダイヤ105個', 'money'=>2000, 'diamond'=>105), 
    						'GDSTH0004'=>array('id'=>'GDSTH0004', 'name'=>'ダイヤ160個', 'money'=>3000, 'diamond'=>160), 
    						'GDSTH0005'=>array('id'=>'GDSTH0005', 'name'=>'ダイヤ275個', 'money'=>5000, 'diamond'=>275),
    						'GDSTH0006'=>array('id'=>'GDSTH0006', 'name'=>'ダイヤ560個', 'money'=>10000, 'diamond'=>560));

    public function testAction()
    {
        echo 'this is webmoney';
        exit;
    }
    
    
	public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->view->version = Zend_Registry::get('version');
        $this->view->hostUrl = Zend_Registry::get('host');
        $this->view->mixi_platform_api_url = $_COOKIE['mixi_platform_api_url'];
    }

    public function paymentAction()
	{
	    //get param
		$sig = $this->_request->getParam('sig');	    	    
		$ts = $this->_request->getParam('ts');	    	    
	    $uid = $this->_request->getParam('uid');
	    $itemId = $this->_request->getParam('payRadio');
		if (empty($uid) || empty($itemId) || empty($this->_items[$itemId])) {
	    	$this->_redirect('/webmoney/error');
	        exit;
	    }
		if ($sig != md5(APP_SECRET . $uid . $ts)) {
	        $this->_redirect('/webmoney/error');
	        exit;
	    }
	    $orderId = $this->_createOrderId($uid);
	    
	    $dalPay = Dal_Island_WebmoneyPay::getDefaultInstance();
	    $rowPay = $dalPay->getById($orderId);
	    if (!$rowPay) {
	    	$aryInfo = array();
	    	$aryInfo['order_id'] = $orderId;
	    	$aryInfo['uid'] = $uid;
	    	$aryInfo['item_id'] = $itemId;
	    	$aryInfo['money'] = $this->_items[$itemId]['money'];
	    	$aryInfo['diamond'] = $this->_items[$itemId]['diamond'];
	    	$aryInfo['create_time'] = time();
	    	$dalPay->insert($aryInfo);
	    }
	    $SCD = $this->_contractCode;
        $GCD = $itemId;//"GDSTH0002";
        $PNM = mb_convert_encoding($this->_items[$itemId]['name'], 'SJIS-win', 'UTF-8');
        $PRC = $this->_items[$GCD]['money'];
        $NUM = "1";
        $RTU = "http://mixi.jp/run_appli.pl?id=" . APP_ID;
        $ATU = Zend_Registry::get('host') . "/webmoney/finish?wmret=####";//?wmret=$$$$
        $CTU = Zend_Registry::get('host') . '/cgi-bin/pagecon.cgi';//Zend_Registry::get('host') . "/webmoney/pagecon?orderId=$orderId";
        $shopfirst = "./shopfirst.cgi";
        $cmdline = $shopfirst. ' ' .$SCD. ' ' . $GCD. ' ' . $PNM. ' ' . $PRC. ' ' . $NUM. ' ' . $RTU. ' ' . $ATU. ' ' . $CTU . ' ' . $orderId;
        chdir(LIB_DIR . "/webmoney/");
        exec($cmdline, $rtn);
//info_log($cmdline, 'payment');
		if ($rtn[0]) {			
        	echo header($rtn[0]);
		}
        exit;
	}

	public function finishAction()
	{
	    //debug_log('finishAction get' . Zend_Json::encode($_GET));
	    //debug_log('finishAction post' . Zend_Json::encode($_POST));
	    $this->view->staticUrl = Zend_Registry::get('static');
	    $wmret = $this->_request->getParam('wmret');
	    
	    $shoplast = "./shoplast";
	    $cmdline = $shoplast . ' -o ' . $this->_shopNo . ' '. urlencode($wmret);
        chdir(LIB_DIR . "/webmoney/");
        exec($cmdline, $rtn);     
//info_log($rtn[0], 'finish'); 

		if ($rtn[0]) {
			$aryRst = explode(' ', $rtn[0]);
			if ($aryRst && count($aryRst) > 1) {
				$this->view->pay = $this->_items[$aryRst[1]];
				//complete buy if pagecon kick not exec
				$dalPay = Dal_Island_WebmoneyPay::getDefaultInstance();
	    		$rowPay = $dalPay->getById($orderId);
	    		if ($rowPay['complete'] == 0) {
		    		$bllPay = new Bll_Island_Pay();
		    		//020SCI20100707122039KS85640220100707122039901006250001000101 GDSTH0003                16197621122112313		    		
		    		$patten   =   "/[\s]+/ ";     //正则格式，匹配多个空格 
					$aryFinish   =   preg_split($patten, $rtn[0]); 
		    		//info_log(substr($aryFinish[0], 3, 25), 'finish');
		    		//info_log($aryFinish[2], 'finish');
					$rstPay = $bllPay->webmoneyPay($aryFinish[2], substr($aryFinish[0], 3, 25));
	    		}
			}
		}
	    $this->render();
	}
	
	public function pageconAction()
	{
info_log(Zend_Json::encode($_POST), 'pagecon');
		$orderId = $this->_request->getParam('orderId');
/*
		$KIF = $this->_request->getPost('KIF');
		$pagecon = "./pagecon.cgi";
	    //$cmdline = $pagecon . " $KIF";
	    $cmdline = $pagecon.' KIF=' .$KIF;
	    //$cmdline = $pagecon;
	    chdir(LIB_DIR . "/webmoney/");
        exec($cmdline, $rtn);
        //info_log(Zend_Json::encode($rtn), 'pagecon');
*/
			$KIF = $this->_request->getPost('KIF');
		    $ch = curl_init();

            $url = Zend_Registry::get('host') . '/cgi-bin/pagecon.cgi';
            $postBody = false;
			$postParam = array('KIF' => $KIF);
            $postBody = $this->create_post_string($postParam);
            $method = 'POST';
            $request = array('url' => $url, 'method' => $method, 'body' => $postBody, 'headers' => false);
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($postBody) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
            }

            // We need to set method even when we don't have a $postBody 'DELETE'
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $cURLVersion = curl_version();
            $ua = 'PHP-cURL/' . $cURLVersion['version'] . ' ' . $ua;
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($ch, CURLOPT_HEADER, true);
            //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            $data = @curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = @curl_errno($ch);
            //$error = @curl_error($ch);
            @curl_close($ch);

            if ($errno != CURLE_OK) {
                info_log("API request error, url=" . $url, "pagecon");
                return null;
            }

			/*
//info_log('resp:' . $data, "pagecon"); 
            //pagecon success done
            if (2 == $data) {
				//$orderId
				$bllPay = new Bll_Island_Pay();
				$rstPay = $bllPay->webmoneyPay($orderId);				
	    		if (!$rstPay) {
	    			$data = 0;
	    		}
            }    
			*/      
			echo $data;   
        	exit;
	}
	
	public function errorAction()
	{
	    $this->render();
	}
		
	
	private function _createOrderId($uid)
	{
		//seconds 10 lens
        $time = time();
        //2010-01-01 00:00:00 1262275200
        $ticks = $time - 1262275200;

        //server id, 1 lens 0~9
        if (defined('SERVER_ID')) {
            $serverid = SERVER_ID;
        } else {
            $serverid = '0';
        }

        //max 9 lens
        return $ticks . $serverid . $uid;	
	}
	
	private function create_post_string($params)
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key . '=' . urlencode($val);
        }
        return implode('&', $post_params);
    }
}