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

    public function checkstatusAction()
    {
        $status = array();
        //OK,  code = 1
        //Stop,code = 0
        $status['code'] = 1;
        if (defined('APP_STATUS')) {
        	$status['code'] = APP_STATUS;
        }

        $app_name = $this->_request->getParam('app_name', '');
        $view = $this->_request->getParam('view');

        if ($status['code'] == 0) {
            if ($view == 'canvas') {
                $status['html'] = '<div style="font-size:12px;">◆メンテナンスのお知らせ（３１日深夜０時～４時）◆ <br/><br/>いつもドリーム☆アイランドで遊んで頂き、ありがとうございます。<br/>上記の日時にてメンテナンスのため、サーバーを停止させて頂きます。<br/><br/>ご迷惑をおかけ致しますが、何卒宜しくお願い致します。<br/></div>';
            }
            else {
                $status['html'] = '<div style="font-size:12px;">◆メンテナンスのお知らせ（３１日深夜０時～４時）◆ <br/><br/>いつもドリーム☆アイランドで遊んで頂き、ありがとうございます。<br/>上記の日時にてメンテナンスのため、サーバーを停止させて頂きます。<br/><br/>ご迷惑をおかけ致しますが、何卒宜しくお願い致します。<br/></div>';
            }
        }
        else {
            $signature_valid = Bll_Application_Mixi::isValidSignature($parameters);
            if ($signature_valid == true) {
                $requestNonce = $this->_request->getParam('request_nonce');
                if ($requestNonce) {
                    require_once 'Bll/Nonce.php';
                    $nonce = Bll_Nonce::createNonce($parameters['app_id'], $parameters['owner_id'], $parameters['viewer_id'], $app_name);

                    $status['nonce'] = $nonce;
                    $status['html'] = $nonce;
                }
                else {
                    $status['html'] = 'HTTP 200, OK';
                }
                $status['parameters'] = $parameters;
            } else {
                $status['code'] = -1;
                $status['html'] = "This request was spoofed";
            }
        }

        echo Zend_Json::encode($status);
        exit;
    }

    
	function inviteAction()
    {
    	//
    	$addCoin = 0;
    	$recipientIds = $this->_request->getParam('recipientIds');
        $result = false;
        if ($recipientIds) {     	
        	$signature_valid = Bll_Application_Mixi::isValidSignature($parameters);

            if ($signature_valid == true) {    	
	            $aryFids = explode(',', $recipientIds);				
	            $bllInv = new Bll_Island_DailyInvite();          
	            foreach ($aryFids as $key=>$data) {
	            	$rst = $bllInv->addCoin($parameters['viewer_id'], $data, 100);
	            	if ($rst) {
	            		$addCoin += 100;
	            	}
	            }
            }
        }

        echo $addCoin;
        exit;    	
    }
    
    
   	//mixi album up photo
	public function getpicAction()
	{
		$uid = $this->_request->getParam('uid');
		$time = $this->_request->getParam('time');
		$sig = $this->_request->getParam('sig');
		if (md5($uid.$time.APP_SECRET) != $sig) {
			$status = array();
			$status['code'] = -1;
            $status['html'] = "This request was spoofed";
			echo Zend_Json::encode($status);
        	exit;
		}
		
		$tt = new Bll_TokyoTyrant($uid);
		$output = $tt->getObject('foruploadpicture'.$uid);

		ob_end_clean();
        ob_start();
        header("Content-type: image/jpeg");
        echo $output;
        exit(0);
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
