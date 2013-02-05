<?php

class EventtestController extends Zend_Controller_Action
{
    protected $uid;

    protected $info;

    /**
     * initialize basic data
     * @return void
     */
	public function init()
    {
    	$info = $this->vailid();
        if (!$info) {
        	$result = array('status' => '-1', 'content' => 'serverWord_101');
			$this->echoResult($result);
        }

        $this->info = $info;
        $this->uid = $info['uid'];
        $data = array('uid' => $info['uid'], 'puid' => $info['puid'], 'session_key' => $info['session_key']);
        $context = Hapyfish2_Util_Context::getDefaultInstance();
        $context->setData($data);

    	$controller = $this->getFrontController();
        $controller->unregisterPlugin('Zend_Controller_Plugin_ErrorHandler');
        $controller->setParam('noViewRenderer', true);
    }

	protected function vailid()
    {
    	$skey = $_COOKIE['hf_skey'];
    	if (!$skey) {
    		return false;
    	}

    	$tmp = explode('.', $skey);
    	if (empty($tmp)) {
    		return false;
    	}
    	$count = count($tmp);
    	if ($count != 5 && $count != 6) {
    		return false;
    	}

        $uid = $tmp[0];
        $puid = $tmp[1];
        $session_key = base64_decode($tmp[2]);
        $t = $tmp[3];

        $rnd = -1;
        if ($count == 5) {
        	$sig = $tmp[4];
	        $vsig = md5($uid . $puid . $session_key . $t . APP_SECRET);
	        if ($sig != $vsig) {
	        	return false;
	        }
        } else if ($count == 6) {
        	$rnd = $tmp[4];
        	$sig = $tmp[5];
        	$vsig = md5($uid . $puid . $session_key . $t . $rnd . APP_SECRET);
        	if ($sig != $vsig) {
	        	return false;
	        }
        }

        //max long time one day
        if (time() > $t + 86400) {
        	return false;
        }

        return array('uid' => $uid, 'puid' => $puid, 'session_key' => $session_key,  't' => $t, 'rnd' => $rnd);
    }

    protected function checkEcode($params = array())
    {
    	if ($this->info['rnd'] > 0) {
    		$rnd = $this->info['rnd'];
    		$uid = $this->uid;
    		$ts = $this->_request->getParam('tss');
    		$authid = $this->_request->getParam('authid');
    		$ok = true;
    		if (empty($authid) || empty($ts)) {
    			$ok = false;
    		}
    		if ($ok) {
    			$ok = Hapyfish2_Island_Bll_Ecode::check($rnd, $uid, $ts, $authid, $params);
    		}
    		if (!$ok) {
    			//Hapyfish2_Island_Bll_Block::add($uid, 1, 2);
    			info_log($uid, 'ecode-err');
	        	$result = array('status' => '-1', 'content' => 'serverWord_101');
	        	setcookie('hf_skey', '' , 0, '/', '.island.qzoneapp.com');
	        	$this->echoResult($result);
    		}
    	}
    }

    protected function echoResult($data)
    {
    	header("Cache-Control: no-store, no-cache, must-revalidate");
    	echo json_encode($data);
    	exit;
    }

	public function clearactive5dayAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:e:atv5d:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);
		$dal = Hapyfish2_Island_Event_Dal_Active5Day::getDefaultInstance();
		$dal->delete($uid);
        echo 'ok';
        exit;
	}

	public function loadlotterylistAction()
	{
		Hapyfish2_Island_Cache_LotteryItemOdds::loadLotteryItemOddsList(1);
        echo 'ok';
        exit;
	}

	public function loadlocallotterylistAction()
	{
		$key = 'island:lotteryitemodds:1';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$cache = Hapyfish2_Island_Cache_LotteryItemOdds::getBasicMC();
		$list = $cache->get($key);
		$localcache->set($key, $list);
        echo SERVER_ID . 'ok';
        exit;
	}

	public function getlocallotterylistAction()
	{
		$key = 'island:lotteryitemodds:1';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$list = $localcache->get($key);
		print_r($list);
		exit;
	}

	public function sendgodAction()
	{
		exit;
		$uid = 1453;
		$send = new Hapyfish2_Island_Bll_Compensation();
		$send->setGold(778);
		$send->sendOne($uid, "论坛奖励,系统赠送的:");
		var_dump($uid); echo "<br>";
		echo "OK";exit;
	}
	public function getusercardAction()
	{
		$uid = $this->_request->getParam('uid');

		$cards = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		//for($i =0 ; $i < count($cards) ; $i++)
		//{
		//	$cards[$]
		//}
		foreach ($cards as $k => $v)
		{
			if($v['count']>100)
			{
				var_dump($v);
				var_dump($k);
				echo "<br>";
				$v['count'] = 1;
				$cards[$k]= $v;
				//$cards[$k]

			}

		}
		var_dump($cards);
		Hapyfish2_Island_HFC_Card::updateUserCard($uid, $cards,true);
		echo "OK";exit;
	}
	public function clearinviteflowAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:e:invf:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);
		echo 'ok';
		exit;
	}


    public function testalubmAction()
	{
	    require_once 'Taobao/Rest.php';
		$rest = Taobao_Rest::getInstance();
		$rest->setUser($this->uid, $this->info['session_key']);
		echo $rest->jianghu_getAlubmId();
		exit;
	}
 }