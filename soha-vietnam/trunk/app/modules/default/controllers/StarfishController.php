<?php

define('ADMIN_USERNAME','admin'); 	// Admin Username
define('ADMIN_PASSWORD','starfishadmin');  	// Admin Password

class StarfishController extends Zend_Controller_Action
{
	protected $uid;

    protected $info;
	    public function init()
    {
//        $info = $this->vailid();
//        if (!$info) {
//        	$result = array('status' => '-1', 'content' => 'serverWord_101');
//			$this->echoResult($result);
//        }

//        $this->info = $info;
//        $this->uid = $info['uid'];
//        $data = array('uid' => $info['uid'], 'openid' => $info['openid'], 'openkey' => $info['openkey']);
//        $context = Hapyfish2_Util_Context::getDefaultInstance();
//        $context->setData($data);

    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
           $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME ||$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
			Header("WWW-Authenticate: Basic realm=\"Event name\"");
			Header("HTTP/1.0 401 Unauthorized");

			echo <<<EOB
				<html><body>
				<h1>Rejected!</h1>
				<big>Wrong Username or Password!</big>
				</body></html>
EOB;
			exit;
		}
    }

    protected function vailid()
    {
    	$skey = $_COOKIE['hf_skey'];
    	if (!$skey) {
    		return false;
    	}

    	$tmp = split('_', $skey);
    	if (empty($tmp)) {
    		return false;
    	}
    	$count = count($tmp);
    	if ($count != 5 && $count != 6) {
    		return false;
    	}

        $uid = $tmp[0];
        $openid = $tmp[1];
        $openkey = base64_decode($tmp[2]);
        $t = $tmp[3];

        $rnd = -1;
        if ($count == 5) {
        	$sig = $tmp[4];
	        $vsig = md5($uid . $openid . $openkey . $t . APP_SECRET);
	        if ($sig != $vsig) {
	        	return false;
	        }
        } else if ($count == 6) {
        	$rnd = $tmp[4];
        	$sig = $tmp[5];
        	$vsig = md5($uid . $openid . $openkey . $t . $rnd . APP_SECRET);
        	if ($sig != $vsig) {
	        	return false;
	        }
        }

        //max long time one day
        if (time() > $t + 86400) {
        	return false;
        }

        return array('uid' => $uid, 'openid' => $openid, 'openkey' => $openkey,  't' => $t, 'rnd' => $rnd);
    }

	public function getstarfishsaleAction(){
	    $saleList = Hapyfish2_Island_Event_Bll_StarfishSale::getSaleList();
	    if (empty($saleList)) {
        	$this->view->saleList = false;
        } else {
        	$this->view->saleList = $saleList;
        }
    	$this->render();
	}
	public function updatesaleAction(){
		$cids = $this->_request->getParam('cids');
		$types = $this->_request->getParam('types');
		$numbers = $this->_request->getParam('numbers');
		$prices = $this->_request->getParam('prices');
		$sort = $this->_request->getParam('sort');
		$saleList = Hapyfish2_Island_Event_Bll_StarfishSale::getSaleList();
	    	if($saleList){
	     		$key = 'starfishOnSale:';
	     		$cache = Hapyfish2_Island_Event_Bll_StarfishSale::getBasicMC();
	     		$dal1 = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
	     		$cache->delete($key);
	        	foreach($saleList as $key=>$value){
	         		$key1 = 'starfishOnSale:'.$value['cid'];
	         		$cache->delete($key1);
	         		$dal1->delete($value['cid']);
	         }
	     }
		foreach($cids as $key=>$value){
		    $info = array(
		        'cid'=>$value,
		        'type'=>$types[$key],
		    	'number'=>$numbers[$key],
		    	'price'=>$prices[$key],
		        'sort'=>$sort[$key]
		    );
		    $dal = Hapyfish2_Island_Event_Dal_StarfishSale::getDefaultInstance();
		    if($value){
		    	$dal->insert($info);
		    }

		}
		$this->_redirect("starfish/getstarfishsale");
	}
	public function getconsumeinfoAction(){
		$consumeEvent = Hapyfish2_Island_Event_Bll_ConsumeExchange::getConsumeEvent();
		  if (empty($consumeEvent)) {
        	$this->view->consumeEvent = false;
        } else {
        	$this->view->start = date('Y-m-d H:i:s',$consumeEvent['start']);
        	$this->view->end = date('Y-m-d H:i:s',$consumeEvent['end']);
        	$this->view->consumeEvent = $consumeEvent['data'];
        }
    	$this->render();
	}
	public function updateconsumeAction(){
		$start = $this->_request->getParam('start');
		$end = $this->_request->getParam('end');
		$cids = $this->_request->getParam('cids');
		$tips = $this->_request->getParam('tips');
		$names = $this->_request->getParam('names');
		$golds = $this->_request->getParam('golds');
		$start = strtotime($start);
		$end = strtotime($end);
		$dal = Hapyfish2_Island_Event_Dal_ConsumeExchange::getDefaultInstance();
		foreach($cids as $key=>$value){
			$info = array(
				'cid' => $value,
				'things'=> $tips[$key],
				'name' => $names[$key],
				'gold' => $golds[$key],
				'start' => $start,
			    'end' => $end
			);
			$postfix =$key+1;
			$window = 'window'.$postfix;
			$dal->updateConsume($window,$info);
		}
		$key = 'consumeandgive:';
		$cache = Hapyfish2_Island_Event_Bll_ConsumeExchange::getBasicMC();
		$cache->delete($key);
		$this->_redirect("starfish/getconsumeinfo");

	}
}