<?php

/**
 * island pay controller
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/05/11    Huch
 */
class PaypalController extends Zend_Controller_Action
{
    private $_payment = array('500'=>25, '1000'=>50, '2000'=>105, '3000'=>160, '5000'=>275, '10000'=>560);
    
    public function init()
    {
        session_start(); 
    }
    
    public function indexAction()
    {
        $uid = $this->_request->getParam('uid');
                
        if (empty($uid)) {
            $this->_redirect('/paypal/error');
        }
        
        $this->view->uid = $uid;
        $this->view->staticUrl = Zend_Registry::get('static');
        $this->render();
    }
    
    public function notificationAction()
    {
        //check payment status
        if ($this->_request->getPost('payment_status') != 'Completed') {
            exit;
        }
        
        //check email
        if ($this->_request->getPost('receiver_email') != 'payment@play-w.com') {
            exit;
        }
        
        //check verify
        $nvpReq = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $nvpReq .= "&$key=$value";
        }
        
        $this->checkNotification($nvpReq);
        
        //get all params
        $id = $this->_request->getPost('custom');
        $dalPayment = Dal_Island_Payment::getDefaultInstance();
        $mixi_paypal = $dalPayment->getById($id);
        
        if (empty($mixi_paypal) || $mixi_paypal['status'] == 1) {
            exit;
        }
        
        $mixi_paypal['trade_no'] = $this->_request->getPost('txn_id');
        
        //insert into pay_log and add gold
        $bllIslandPay = new Bll_Island_Pay();
        $bllIslandPay->order($mixi_paypal);
        
        exit;
    }
    
    public function paymentAction()
	{
	    $sig = $this->_request->getParam('sig');
	    $uid = $this->_request->getParam('uid');
	    
	    if ($sig != md5(APP_SECRET . $uid)) {
	        $this->_redirect('/paypal/error');
	    }
	    
		$returnURL = Zend_Registry::get('host') . '/paypal/return';
		$cancelURL = Zend_Registry::get('host') . "/paypal/index/uid/" . $uid;		
		
		$mixi_pay_no = $this->createPayOrderId($uid);
		$mixi_amont = $this->_request->getParam('payRadio');
		$mixi_gold = $this->_payment[$mixi_amont];
		//add for test
		//$mixi_amont = 1;
		
		if (empty($mixi_gold)) {
		    $this->_redirect('/paypal/error');
		}
		
		//set nvpstr
		$nvpArray = array('RETURNURL' => $returnURL,
						  'CANCELURL' => $cancelURL,
						  'CURRENCYCODE' => 'JPY',
						  'LOCALECODE' => 'JP',
						  'AMT' => $mixi_amont,
						  'L_NAME0' => 'チャージ',
						  'L_DESC0' => 'チャージ',
						  'L_AMT0' => $mixi_amont,
						  'L_QTY0' => 1,
						  'CUSTOM' => $mixi_pay_no);
		$nvpStr = http_build_query($nvpArray);
		
		$nvpResArray = $this->callService('SetExpressCheckout', $nvpStr);
		
		$ack = strtoupper($nvpResArray["ACK"]);

		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
			//insert into payment for log
	   	    $token = urldecode($nvpResArray["TOKEN"]);
	   	    
	   	    $mixi_paypal = array('id' => $mixi_pay_no,
	   	                         'uid' => $uid,
	   	                         'amont' => $mixi_amont,
	   	                         'gold' => $mixi_gold,
	   	                         'create_time' => time(),
	   	                         'token' => $token);

	   	    $dalPaypemt = Dal_Island_Payment::getDefaultInstance();
	   	    $dalPaypemt->insert($mixi_paypal);

	   	    $_SESSION['mixi_paypal'] = $mixi_paypal;
	   	    
	   	    $payPalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=' . $token;
			$this->_redirect($payPalURL);
		}
		else {
			$this->_redirect('/paypal/error');
		}
	}
	
	public function returnAction()
	{
	    $mixi_paypal = $_SESSION['mixi_paypal'];
	    $token = urlencode($mixi_paypal['token']);
	    
		//GetExpressCheckoutDetails
		$nvpStr = "TOKEN=$token";

        $responseGet = $this->callService('GetExpressCheckoutDetails', $nvpStr);
		if("SUCCESS" == strtoupper($responseGet["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($responseGet["ACK"])) {
        	// Extract the response details.
        	$payerID = $responseGet['PAYERID'];
		}
		else {
		    $this->_redirect('/paypal/error');
		}		
		
		//DoExpressCheckoutDetails
		$payerID = urlencode($payerID);
        
		$paymentType = 'Sale';
        $paymentAmount = urlencode("payment_amount");
        $currencyID = urlencode("JPY");						// or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
        
        // Add request-specific fields to the request string.
        $nvpStr = "TOKEN=$token&PAYERID=$payerID&PAYMENTACTION=$paymentType&AMT=".$mixi_paypal['amont']."&CURRENCYCODE=$currencyID";
        
        $responseDo = $this->callService('DoExpressCheckoutPayment', $nvpStr);
        
        if("SUCCESS" == strtoupper($responseDo["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($responseDo["ACK"])) {
            $mixi_paypal['trade_no'] = $responseDo['TRANSACTIONID'];
            //pay success
            $bllIslandPay = new Bll_Island_Pay();
            $bllIslandPay->order($mixi_paypal);
            
            $this->_redirect('/paypal/success');
        }
        else {
		    $this->_redirect('/paypal/error');
		}
	}

	public function successAction()
	{
	    $this->view->staticUrl = Zend_Registry::get('static');
	    $this->view->pay = $_SESSION['mixi_paypal'];
	    $this->render();
	}
	
	public function errorAction()
	{
	    $this->view->staticUrl = Zend_Registry::get('static');
	    $this->view->hostUrl = Zend_Registry::get('host');
	    $this->render();
	}
	
	function checkNotification($nvpStr)
	{
	    $checkUrl = 'https://www.paypal.com/cgi-bin/webscr';
	    
	    $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $checkUrl);
    	curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
    	// Set the curl parameters.
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);    
    
    	// Set the request as a POST FIELD for curl.
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpStr);
    
    	// Get response from the server.
    	$httpResponse = curl_exec($ch);
	    	    
    	//check notification failed
    	if ($httpResponse != 'VERIFIED') {
    	    exit();
    	}
	}
	
	function callService($methodName, $nvpStr) 
	{    
    	// Set up your API credentials, PayPal end point, and API version.
    	$API_UserName = urlencode('payment_api1.play-w.com');
    	$API_Password = urlencode('XREFFVAPSPGFMZKL');
    	$API_Signature = urlencode('AiPC9BjkCyDFQXbSkoZcgqH3hpacAfkOZazklrILS.8gK22.N9f9ULz-');
    	$API_Endpoint = "https://api-3t.paypal.com/nvp";
    	
    	$version = urlencode('51.0');
    
    	// setting the curl parameters.
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
    	curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
    	// Set the curl parameters.
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1);
    
    	// Set the API operation, version, and API signature in the request.
    	$nvpreq = "METHOD=$methodName&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature&$nvpStr";
    
    	// Set the request as a POST FIELD for curl.
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
    
    	// Get response from the server.
    	$httpResponse = curl_exec($ch);
    
    	if(!$httpResponse) {
    		exit('$methodName_ failed: '.curl_error($ch).'('.curl_errno($ch).')');
    	}
    
    	// Extract the response details.
    	$httpResponseAr = explode("&", $httpResponse);
    
    	$httpParsedResponseAr = array();
    	foreach ($httpResponseAr as $i => $value) {
    		$tmpAr = explode("=", $value);
    		if(sizeof($tmpAr) > 1) {
    			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
    		}
    	}
    
    	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
    		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
    	}
    
    	return $httpParsedResponseAr;
    }
	
	function createPayOrderId($uid)
    {
        //seconds 10 lens
        $ticks = floor(microtime(true) * 1000);

        //server id, 1 lens 0~9
        if (defined('SERVER_ID')) {
            $serverid = SERVER_ID;
        } else {
            $serverid = '0';
        }

        //max 9 lens
        //$this->user_id
        return $ticks . '_' . $serverid . '_' . $uid;
    }
}