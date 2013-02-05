<?php
define('ADMIN_USERNAME','admin'); 	// Admin Username
define('ADMIN_PASSWORD','suijishengchangde');  	// Admin Password
class SwhController extends Zend_Controller_Action
{
	public function init()
	{
		// set_time_limit(30);

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
	public function toolAction()
	{
		$msg = $this->_request->getParam('msg');
		$this->view->msg = $msg;
		$this->render();
	}
	public function sendAction()
	{
		$uid = $this->_request->getParam('uids');
		$title = $this->_request->getParam('feed');
		$cid = $this->_request->getParam('cids');
		$star = $this->_request->getParam('star');
		$coin = $this->_request->getParam('coin');
		$gold = $this->_request->getParam('gold');
		$uid = $this->trimstring($uid);
		$title = $this->trimstring($title);
		$cid = $this->trimstring($cid);
		$star = $this->trimstring($star);
		$coin = $this->trimstring($coin);
		$gold = $this->trimstring($gold);
		$msg = '发送成功';
		if($uid){
			$uidlist = explode(',', $uid);
		} else {
			$msg = 'uid不正确';
		}
		if($cid){
			$items = explode(',', $cid);
		}
		$compensation = new Hapyfish2_Island_Bll_Compensation();

		if ($coin > 0) {
			$compensation->setCoin($coin);
		}
		if($gold > 0){
			Hapyfish2_Island_Bll_Gold::add($uid, array('gold' => $gold, 'type' => 0));
		}
		if($star > 0){
			$compensation->setStarfish($star);
		}
		if (!empty($items)) {
			foreach ($items as $data) {
				$item = explode('*', $data);
				$compensation->setItem($item[0], $item[1]);
			}
		}
		$compensation->setUids($uidlist);
		$compensation->setFeedTitle($title);
		$total = 1;
		$num = $compensation->send('');
		if($num < 1){
			$msg = '请检查发送的物品'.$num;
		} else {
			$msg = '发放成功'.$num;
		}
		$this->_redirect("swh/tool?msg=$msg");
	}
	public function trimstring($str)
	{
		$str = trim($str);
		$str = str_replace("\r", '', $str);
		$str = str_replace("\n", '', $str);
		$str = str_replace("\t", '', $str);
		$str = str_replace(" ", '', $str);
		return trim($str);
	}
	public function addrobotAction()
	{
		Hapyfish2_Island_Bll_Robot::addFriend(114);
		echo "OK";
		exit;
	}
	public function getlistAction()
	{
		$data = Hapyfish2_Island_Bll_Robot::getFriendList(113);
		print_r($data);
		exit;
	}
	public function updaterankAction()
	{
		Hapyfish2_Island_Bll_Rank::updateRankWeek();
		echo "ok";
		exit;
	}
	public function clearallrobotAction()
	{
		for($i=1;$i<=500;$i++){
			$key = 'i:u:s:r:i:s'.$i;
			$cache = Hapyfish2_Island_Bll_Robot::getBasicMC();
			$cache->delete($key);
		}
		echo"OK";exit;
	}

	public function clearuserrobotAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:robot:f:'.$uid;
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$cache->delete($key);
		$dal = Hapyfish2_Island_Dal_Robot::getDefaultInstance();
		$dal->clearrobot($uid);
		echo"OK";
		exit;
	}
	public function clearsearchfriendAction()
	{
		$key = 'i:u:searchFriend';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		echo"OK";
		exit;
	}
	
	public function clearqixiechangeAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'qixi:get:gift:'.$uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->delete($key);
		$dal = Hapyfish2_Island_Event_Dal_Qixi::getDefaultInstance();
		$dal -> delete($uid);
		echo "OK";
		exit;
	}
	public function clearuserdailyqixiAction()
	{
		$uid = $this->_request->getParam('uid');
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$mkeyUid = 'event:xmas:fair:qixi:daily:' . $uid;
		$cache->delete($mkeyUid);
		echo "ok";
		exit;
	}
	
    public function clearconfigAction()
	{
		$key = 'i:award:config';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		echo"OK";
		exit;
	}
	public function clearuserfragmentAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:Fragment:'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$cache->delete($key);
    	echo"OK";
    	exit;
	}
	
	public function awardconfigAction()
	{
	
	}
	
	public function updateuserdayAction()
	{
		$uid = $this->_request->getParam('uid');
		$time = $this->_request->getParam('time');
		$data['friend_time'] = $time;
		Hapyfish2_Island_Bll_Fragments::updateFragments($uid, $data);
		echo"OK";
		exit;
	}
	
	public function updateuserexchangeAction()
	{
		$uid = $this->_request->getParam('uid');
		$time = $this->_request->getParam('time');
		$data['polish_time'] = $time;
		Hapyfish2_Island_Bll_Fragments::updateFragments($uid, $data);
		echo"OK";
		exit;
	}
	
	public function addfragmentAction()
	{
		$uid = $this->_request->getParam('uid');
		$num = $this->_request->getParam('num');
		$t = new Hapyfish2_Island_Bll_Compensation();
		$t->setFragment($num);
		$t->sendOne($uid, '发碎片喽');
		echo"OK";
		exit;
	}
	
	public function adduserfrinedAction()
	{
		$uid = $this->_request->getParam('uid');
		$pid =  $this->_request->getParam('pid');
		Hapyfish2_Island_Bll_Invite::add($uid, $pid, '');
		echo "OK";
		exit;
	}
}
