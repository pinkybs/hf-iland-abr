<?php

class UnittestController extends Zend_Controller_Action
{

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


	public function userAction()
	{
		$auth = Zend_Auth::getInstance();
	    if (!$auth->hasIdentity()) {
            $result = array('status' => '-1', 'content' => 'serverWord_101');
            echo Zend_Json::encode($result);
            exit;
        }
        $uid = $auth->getIdentity();
		$qzone = Qzone_Rest2::getInstance();
		$openid = $_SESSION['openid'];
		$openkey = $_SESSION['openkey'];
		$qzone->setUser($openid, $openkey);

		$user_data = $qzone->getUser();

		print_r($user_data);

		exit;
	}

	public function paybalanceAction()
	{
		$auth = Zend_Auth::getInstance();
	    if (!$auth->hasIdentity()) {
            $result = array('status' => '-1', 'content' => 'serverWord_101');
            echo Zend_Json::encode($result);
            exit;
        }
        $uid = $auth->getIdentity();
		$qzone = Qzone_Rest2::getInstance();
		$openid = $_SESSION['openid'];
		$openkey = $_SESSION['openkey'];
		$qzone->setUser($openid, $openkey);

		$balance = $qzone->getPayBalance();

		echo 'balance: ' . $balance;

		exit;
	}

	public function uidAction()
	{
		$dalUidMap = Hapyfish2_Platform_Dal_UidMap::getDefaultInstance();
		$puid = '00000000000000000000000007231CF3';
		$uid = $dalUidMap->getSequence($puid);
		echo $uid;
		exit;
	}

	public function inituserAction()
	{
        $uid = $this->_request->getParam('uid', 101);
		$user = array(
			'uid' => $uid,
            'puid' => $uid,
			'name' => 'Happyfish'.$uid,
			'figureurl' => 'http://xy.store.qq.com/2124fc82e3f1a301e6886541894d29da0575023a16e570ef0',
			'gender' => 1,
			'email' => ''
		);
		
		Hapyfish2_Platform_Bll_User::addUser($user);

		Hapyfish2_Island_Bll_User::joinUser($uid);

		echo 'OK';
		exit;
	}

	public function gaintitleAction()
	{
		$uid = 115;
		$titleId = 1;

		$userTitle = Hapyfish2_Island_HFC_User::getUserTitle($uid);

		print_r($userTitle);

		$newUserTitle = array('title' => 0, 'title_list' => '1,2,3');

		Hapyfish2_Island_HFC_User::updateUserTitle($uid, $newUserTitle);

		//Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId);
		echo 'OK';
		exit;
	}

	public function usercardAction()
	{
		$uid = 115;
		$lstCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		print_r($lstCard);
		exit;
	}

	public function changeplantAction()
	{
		$uid = 118;
		$itemId = 1;
		$userPlant = Hapyfish2_Island_HFC_Plant::getOneOnIsland($uid, $itemId);
		print_r($userPlant);
		$userPlant['start_pay_time'] -= 3600;
		$userPlant['event'] = 1;
		Hapyfish2_Island_HFC_Plant::updateOneOnIsland($uid, $itemId, $userPlant);
		echo 'OK';
		exit;
	}

	public function jsonAction()
	{
		$data = '{"a" : false}';
		$t = json_decode($data, true);
		print_r($t);
		exit;
	}

	public function clearAction()
	{
		Hapyfish2_Island_Cache_BasicInfo::loadPlantList();
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$key = 'island:plantlist';
		$localcache->delete($key);
		echo 'OK';
		exit;
	}

	public function tuserAction()
	{
		$uid = 124;
		$user = Hapyfish2_Platform_Bll_User::getUser($uid);
		print_r($user);
		//exit;

		$ok = Hapyfish2_Platform_Bll_User::updateUser($uid, $user, true);
		if ($ok) {
			echo 'yes';
		}
		exit;
	}

	public function tpuidAction()
	{
		$uid = 115;
		$fids = array("000000000000000000000000030C7337","00000000000000000000000006D831D8","00000000000000000000000006D0B55C","0000000000000000000000000335F66C","00000000000000000000000006E8391A","00000000000000000000000006E21F36","000000000000000000000000077B3382");

		$fids = Hapyfish2_Platform_Bll_User::getUids($fids);

		print_r($fids);
		exit;
	}

	public function getfeeddataAction()
	{
		$uid = $this->_request->getParam('uid');
		$data = Hapyfish2_Island_Bll_Feed::getFeedData($uid);
		print_r($data);

		$list = Hapyfish2_Island_Cache_BasicInfo::getFeedTemplate();
		print_r($list);
		exit;
	}

	public function feedAction()
	{
	    $uid = 115;
	    $now = time();
	    $template_id = 101;
	    $feedTitle = array('title' => '风水大师', 'coin' => 200);
		$feed = array(
			'uid' => $uid,
			'template_id' => $template_id,
			'actor' => $uid,
			'target' => $uid,
			'title' => $feedTitle,
			'type' => 3,
			'create_time' => $now
		);
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);

	    $template_id = 102;
	    $feedTitle = array('title' => '风水大师', 'exp' => 100);
		$feed = array(
			'uid' => $uid,
			'template_id' => $template_id,
			'actor' => $uid,
			'target' => $uid,
			'title' => $feedTitle,
			'type' => 3,
			'create_time' => $now + 10
		);
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);

	    $template_id = 103;
	    $feedTitle = array('title' => '风水大师', 'coin' => 200, 'exp' => 100);
		$feed = array(
			'uid' => $uid,
			'template_id' => $template_id,
			'actor' => $uid,
			'target' => $uid,
			'title' => $feedTitle,
			'type' => 3,
			'create_time' => $now + 20
		);
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
	    exit;
	}

	public function taskAction()
	{
	    $uid = 115;
	    $taskId = 2006;
	    $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildTaskInfo($taskId);

		$userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);

		echo $islandUser['position_count'];
		echo '<br/>' . $taskInfo['need_num'];
		exit;
	}

	public function clearhouseAction()
	{
		$uid = 127;
		Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
		Hapyfish2_Island_Cache_Plant::reloadInWareHouse($uid);
		Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid);
		Hapyfish2_Island_Cache_Building::loadAllOnIsland($uid);
		Hapyfish2_Island_Cache_Building::loadInWareHouse($uid);
		echo 'OK';
		exit;
	}

	public function t02Action()
	{
		$uid = 127;
		//$content = '你好，胡锦涛!';
		//$type = 3;
		//Hapyfish2_Island_Bll_Remind::addRemind(115, 124, $content, $type);
		//$data = Hapyfish2_Island_Cache_Remind::getRemindData($uid);
		$data = Hapyfish2_Island_HFC_AchievementDaily::getUserAchievementDaily($uid);
		print_r($data);
		echo '<br/>';

		Hapyfish2_Island_Cache_TaskDaily::clearAll($uid);
    	$key = 'i:u:alltaskdly:' . $uid;

        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        print_r($data);
		exit;
	}

	public function t03Action()
	{
		$uid = 134;
		$cid = 21532;
		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
		$newPlant = array(
			'uid' => $uid,
			'cid' => $plantInfo['cid'],
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => time(),
			'item_type' => $plantInfo['item_type']
		);

		Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
		echo 'OK';
		exit;
	}

	public function t04Action()
	{
		$uid = 115;
		$islandUser = Hapyfish2_Island_HFC_User::getUser($uid, array('exp' => 1, 'coin' => 1, 'level' => 1));
		print_r($islandUser);
		exit;
	}

	public function pwdAction()
	{
		$pwd = $this->_request->getParam('pwd');
		$secret = $this->_request->getParam('secret');
		$md5 = md5($pwd. ':' . $secret);
		echo $md5;
		exit;
	}

	public function noticeAction()
	{
		echo urlencode('[欢乐海岛]开始内测，如有问题请加玩家交流群42808828');
		exit;
	}

	public function inviteAction()
	{
		$myopenid = '00000000000000000000000007231CF3';
		$iopenid = '0000000000000000000000000335F66C';
		$itime = '1290075198';
		$appid = APP_ID;
		$appkey = APP_KEY;
		$validKey = md5($myopenid . '_' . $iopenid . '_' . $appid . '_' . $appkey . '_' . $itime);
		echo $validKey;
		exit;
	}

	public function deleteuserislandAction()
	{
		$uid = 115;
		//清除岛
		$ids = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    foreach ($ids as $id) {
        	$key = 'i:u:plt:' . $uid . ':' . $id;
        	$cache->delete($key);
    		$dalPlant->delete($uid, $id);
        }

        $dalPlant->init($uid);
        Hapyfish2_Island_Cache_Plant::reloadAllIds($uid);
        Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid);
        echo 'OK';
        exit;
	}

	public function userlvAction()
	{
		$file = CONFIG_DIR . '/levelup_user.txt';
		if (is_file($file)) {
			$content = file_get_contents($file);
			$data = explode(',', $content);
			foreach ($data as $item) {
				$tmp = explode(':', $item);
				$log_file = 'ulv_' . $tmp[1];
				info_log($tmp[0], $log_file);
			}
		}
		echo 'OK';
        exit;
	}

	public function listuidAction()
	{
		$file = CONFIG_DIR . '/list.txt';
		if (is_file($file)) {
			$content = file_get_contents($file);
			$content = trim($content);
			$data = explode(',', $content);
			$uid1 = array();
			$uid2 = array();
			$uid3 = array();
			foreach ($data as $item) {
				$tmp = explode(':', $item);
				if ($tmp[1] > 2) {
					$uid1[] = trim($tmp[0], '"');
				} else if($tmp[1] > 1)  {
					$uid2[] = trim($tmp[0], '"');
				} else {
					$uid3[] = trim($tmp[0], '"');
				}
			}
			$n1 = count($uid1);
			$n2 = count($uid2);
			$n3 = count($uid3);
			$uid1 = join(',', $uid1);
			$uid2 = join(',', $uid2);
			$uid3 = join(',', $uid3);
			echo $uid1 . '<br/>' . $n1 . '<br/>';
			echo $uid2 . '<br/>' . $n2 . '<br/>';
			echo $uid3 . '<br/>' . $n3 . '<br/>';
		}
		echo 'OK';
        exit;
	}

	public function t1215Action()
	{
		$mc = new Memcached();
		//$mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
		$mc->addServer('127.0.0.1', 11211);
		$data = array('1222', '12312', '12312312', '1212312312312');
		$mc->set('t_001', $data);
		echo $t2 - $t1;
		exit;
	}

	public function t1216Action()
	{
		$mc = new Memcached();
		//$mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
		$mc->addServer('127.0.0.1', 11211);
		//$mc->set('t_001', 't_001');
		$t1 = microtime(true);
		for($i=0;$i<200;$i++) {
			$mc->get('t_001');
		}
		$t2 = microtime(true);
		echo $t2 - $t1;
		exit;
	}

	public function t1217Action()
	{
		$uid = 115;
		$coin = 3000;
		$cid = 26341;
		$template_id = 17;
		$now = time();
		$title = array('coin' => $coin, 'dayCount' => 6);

		$feed = array(
			'uid' => $uid,
			'actor' => $uid,
			'target' => $uid,
			'type' => 3,
			'create_time' => $now
		);

		if ($cid > 0) {
			$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
			if ($cardInfo) {
				Hapyfish2_Island_HFC_Card::addUserCard($uid, $cid, 1);
				$template_id = 21;
				$title['cardName'] = $cardInfo['name'];
			}
		}

		$feed['template_id'] = $template_id;
		$feed['title'] = $title;
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
		echo 'ok';
		exit;
	}

	public function loginactiveAction()
	{
		$uid = 127;
		$loginInfo = array(
			'last_login_time' => time() - 86400,
			'active_login_count' => 5,
			'max_active_login_count' => 5,
			'today_login_count' => 0
		);
		Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo, true);
		echo 'ok';
		exit;
	}

	public function clearsomeAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:e:nye:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);
		$dal = Hapyfish2_Island_Event_Dal_NewYearEgg::getDefaultInstance();
		$dal->delete($uid);
        echo 'ok';
        exit;
	}

	public function gplantAction()
	{
		$uid = 115;
		$itemId = 136;
		$userPlant = Hapyfish2_Island_HFC_Plant::getOne($uid, $itemId, 1);
		$userPlant['start_pay_time'] = 0;
		$userPlant['start_deposit'] = 3600;
		$userPlant['deposit'] = 3600;
		Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $userPlant);
        echo 'ok';
        exit;
	}

	public function logininfoAction()
	{
		$uid = $this->_request->getParam('uid');
		$loginInfo = Hapyfish2_Island_HFC_User::getUserLoginInfo($uid);
		print_r($loginInfo);
		exit;
	}

	public function testoldusercacheAction()
	{
		$uid = $this->_request->getParam('uid');
		$data = Hapyfish2_Island_Tool_ClearOldUserCache::testOne($uid);
		print_r($data);
		exit;
	}

	public function clearoldusercacheAction()
	{
		$uid = $this->_request->getParam('uid');
		$data = Hapyfish2_Island_Tool_ClearOldUserCache::testBuilding($uid);
		echo 'ok<br/>';
		print_r($data);
		exit;
	}

	public function allplantidsAction()
	{
		$uid = $this->_request->getParam('uid');
		$ids = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
		print_r($ids);
		exit;
	}

	public function taAction()
	{
		$queryStr = $_SERVER['QUERY_STRING'];

		$fix = strpos($queryStr, '&&');
		if ($fix !== false) {
			$tmp = explode('&&', $queryStr);
			$queryStrArr = explode('&sig=', $tmp[1]);
		} else {
			$queryStrArr = explode('&sig=', $queryStr);
		}
		print_r($queryStrArr);
		print_r($_GET);
		$p = $this->_request->getParams();
		print_r($p);
		exit;
	}


    public function testgettaojinbiAction()
	{
		$info = $this->vailid();
		$puid = $info['puid'];
		$sessionKey = $info['session_key'];
        $rest = Taobao_Rest::getInstance();
        $rest->setUser($puid, $sessionKey);
        $data = $rest->jianghu_getCoinsSum();
        echo json_encode($data);
		exit;
	}

    public function testconsumetaojinbiAction()
	{
	    $count = (int)$this->_request->getParam('count');
		$info = $this->vailid();
		$puid = $info['puid'];
		$sessionKey = $info['session_key'];
        $rest = Taobao_Rest::getInstance();
        $rest->setUser($puid, $sessionKey);

        $data = $rest->jianghu_coinsConsume($count);
        echo json_encode($data);
		exit;
	}

	public function testtbproductsaleAction()
	{
	    $uid = (int)$this->_request->getParam('uid');
	    $productid = (int)$this->_request->getParam('pid');
	    $sale = (int)$this->_request->getParam('sale', 7);
	    $area = (int)$this->_request->getParam('area', 5);
	    echo Hapyfish2_Island_Event_Bll_CatchFish::addTaoBaoDiscount($uid, $productid, $sale, $area);
	    exit;
	}


    public function testtopgetuserAction()
	{
	    $info = $this->vailid();
		$puid = $info['puid'];
		$sessionKey = $info['session_key'];
        $rest = Taobao_Rest::getInstance();
        $rest->setUser($puid, $sessionKey);
        $data = $rest->top_getUser();
        echo json_encode($data);
		exit;
	}
}