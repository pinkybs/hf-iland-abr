<?php

define('ADMIN_USERNAME','admin'); 					// Admin Username
define('ADMIN_PASSWORD','yewushuang_920');  	// Admin Password

class TeambuyController extends Zend_Controller_Action
{

	protected $_btl_key = 'bottle:list';

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

	public function teambuyinfoAction()
	{
		$dalTeambuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$info = $dalTeambuy->getTeamBuyMessage();

		$sexTin = explode('*', $info['gid']);
		$sex['cid'] = $sexTin[0];
		$sex['num'] = $sexTin[1];

		if($info['start_time']) {
			$start_time = date('Y-m-d H:i:s', $info['start_time']);
		} else {
			$start_time = '';
		}

		$join_time = $info['ok_time'];
		$buy_time = $info['buy_time'];

		$min_price_info = explode('*', $info['min_price']);
		if($min_price_info[1] == 1) {
			$min_price = $min_price_info[0] . '*金币';
		} else if($min_price_info[1] == 2) {
			$min_price = $min_price_info[0] . '*宝石';
		}

		$max_price_info = explode('*', $info['max_price']);
		if($max_price_info[1] == 1) {
			$max_price = $max_price_info[0] . '*金币';
		} else if($max_price_info[1] == 2) {
			$max_price = $max_price_info[0] . '*宝石';
		}

		$this->view->sex = $sex;
		$this->view->start_time = $start_time;
		$this->view->join_time = $join_time;
		$this->view->buy_time = $buy_time;
		$this->view->min_price = $min_price;
		$this->view->max_price = $max_price;
		$this->view->info = $info;
	}

	public function teambuyupdateAction()
	{
		$teambuy = $this->_request->getParams('teambuyinfo');

		$sex = $teambuy['teambuyinfo']['gid'] . '*' . $teambuy['teambuyinfo']['num'];
		$start_time = strtotime($teambuy['teambuyinfo']['start_time']);

		$max_price_info = explode('*', $teambuy['teambuyinfo']['max_price']);
		if($max_price_info[1] == '宝石') {
			$max_price_info[1] = 2;
		} else {
			$max_price_info[1] = 1;
		}
		$max_price = $max_price_info[0] . '*' . $max_price_info[1];

		$min_price_info = explode('*', $teambuy['teambuyinfo']['min_price']);
		if($min_price_info[1] == '宝石') {
			$min_price_info[1] = 2;
		} else {
			$min_price_info[1] = 1;
		}
		$min_price = $min_price_info[0] . '*' . $min_price_info[1];

		$info = array('gid' => $sex,
						'name' => $teambuy['teambuyinfo']['name'],
						'start_time' => $start_time,
						'ok_time' => $teambuy['teambuyinfo']['ok_time'],
						'buy_time' => $teambuy['teambuyinfo']['buy_time'],
						'max_price' => $max_price,
						'min_price' => $min_price,
						'min_num' => $teambuy['teambuyinfo']['min_num'],
						'max_num' => $teambuy['teambuyinfo']['max_num'],
						'start_num' => $teambuy['teambuyinfo']['start_num'],
						'bec_num' => $teambuy['teambuyinfo']['bec_num'],
						'bec_price' => $teambuy['teambuyinfo']['bec_price'],
						'scale_gold' => $teambuy['teambuyinfo']['scale_gold'],
						'scale_coin' => $teambuy['teambuyinfo']['scale_coin']);

		$dalTeamBuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$dalTeamBuy->updateTeamBuyInfo($info);

		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);

		$this->_redirect("teambuy/teambuyinfo");
	}

	public function clearteambuycacheAction()
	{
		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);

		$dalTeamBuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
    	$users = $dalTeamBuy->getHasJoinTeamBuyUser();

    	if($users) {
	    	foreach ($users as $uids) {
	    		foreach ($uids as $uid) {
			    	$keys = 'i:e:teambuy:buygood:' . $uid;
					$caches = Hapyfish2_Cache_Factory::getMC($uid);
					$caches->delete($keys);
	    		}
	    	}
    	}

		$dalTeamBuyUser = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$dalTeamBuyUser->clearTeamBuyUser();

		$this->_redirect("teambuy/teambuyinfo");
	}

	public function teambuyswitchAction()
	{
		$teambuyMessage = $this->_request->getParams('teambuyswitch');

		$dalTeambuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$dalTeambuy->switchTeamBuy($teambuyMessage['teambuyswitch']);

		$this->_redirect("teambuy/teambuyinfo");
	}

	public function teambuyswitchoneAction()
	{
		$message = $this->_request->getParams('uids');

		$tids = array(1, 2);

		if(!in_array($message['teambuyswitchone']['tid'], $tids)) {
			return false;
		}

		if($message['teambuyswitchone']['tid'] == 1) {
			if($message['teambuyswitchone']['uids']) {
				$uids = explode(',', $message['teambuyswitchone']['uids']);

				Hapyfish2_Island_Event_Bll_TeamBuy::setOpenUID($uids);
			}
		} else {
			Hapyfish2_Island_Event_Bll_TeamBuy::deleteOpenUID();
		}

		$this->_redirect("teambuy/teambuyinfo");
	}

	/**********万圣节活动接口************/
	//清理卡牌信息
	public function clear1Action()
	{
		$key = 'ev:hall:card';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);

		echo 'OK';
		exit;
	}

	//清理兑换物品列表
	public function clear2Action()
	{
		$key = 'ev:hall:gift';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);

		echo 'OK';
		exit;
	}

	//清理用户卡牌信息缓存
	public function clear3Action()
	{
		$uid = $this->_request->getParam('uid');

		$key = 'ev:hall:card:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);

		echo 'OK';
		exit;
	}

	//清理用户倒计时
	public function clear4Action()
	{
		$uid = $this->_request->getParam('uid');

		$key = 'ev:hall:time:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);

		echo 'OK';
		exit;
	}

	//给用户增加卡片
	public function clear5Action()
	{
		$uid = $this->_request->getParam('uid');
		$cid = $this->_request->getParam('cid');
		$num = $this->_request->getParam('num');

		$key = 'ev:hall:card:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$card = $cache->get($key);

		foreach ($card as $cdkey => $cdva) {
			if ($cid == $cdkey) {
				$card[$cdkey] = $num;
				break;
			}
		}

		$cache->set($key, $card, 3600 * 24 * 15);

		foreach ($card as $cardkey => $cardva) {
			$data[] = $cardkey . '*' . $cardva;
		}

		$list = implode(',', $data);

		try {
			$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
			$db->incCard($uid, $list);
		} catch (Exception $e) {}

		echo 'OK';
		exit;
	}
	/**********万圣节活动接口************/

	/**********收集任务后台************/
	public function collectlistAction()
	{

		$key = 'collectgift';
		$jianglikey = 'jiangliid';
		$timekey = 'time';
		$xiaoxikey = 'xiaoxi';

		$val = Hapyfish2_Island_Event_Bll_Hash::getval($key);
		$jianglival = Hapyfish2_Island_Event_Bll_Hash::getval($jianglikey);
		$time = Hapyfish2_Island_Event_Bll_Hash::getval($timekey);
		$message = Hapyfish2_Island_Event_Bll_Hash::getval($xiaoxikey);

		$keyswitch = "collectcontrolswitch";
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$switch = $cache->get($keyswitch);

		if($switch['uid']) {
			$this->view->switchuid = $switch['uid'];
		}

		if( $val ) {
			$list = unserialize( $val );
			$this->view->list = $list;
		}

		if( $jianglival ) {
			$this->view->jiangli_id = $jianglival;
		}

		if($time) {
			$time = unserialize( $time );
			$time['start'] = date('Y-m-d H:i:s',$time['start']);
			$time['end'] = date('Y-m-d H:i:s',$time['end']);
		    $this->view->time = $time;
		}

		if($message) {
		    $this->view->message = unserialize( $message );
		}
	}

	public function collectlistdoAction()
	{
		$key = 'collectgift';
		$jianglikey = 'jiangliid';
		$timekey = 'time';
		$xiaoxikey = 'xiaoxi';

		$names = $this->_request->getParam('names');
		$cids = $this->_request->getParam('cids');
		$tips = $this->_request->getParam('tips');
		$time['start'] = $this->_request->getParam('start');
		$time['end'] = $this->_request->getParam('end');
		$message['tishi'] = $this->_request->getParam('tishi');
		$message['zhu'] = $this->_request->getParam('zhu');
		$jiangli_id = $this->_request->getParam('jiangli_id');

		$time['start'] = $time['start'] ? strtotime($time['start']) : strtotime('now');
		$time['end'] = strtotime($time['end']);

		$arr = array();
		foreach( $names as $k => $v ) {
			$arr[] = array('name'=>$names[$k], 'cid'=>$cids[$k], 'tip'=>$tips[$k]);
		}
		if( $arr ) {
			Hapyfish2_Island_Event_Bll_Hash::setval($key, serialize( $arr ));
		}
		if( $jiangli_id ) {
			Hapyfish2_Island_Event_Bll_Hash::setval($jianglikey, $jiangli_id );
		}
		if($time){
		   Hapyfish2_Island_Event_Bll_Hash::setval($timekey, serialize($time) );
		}
		if($message){
		    Hapyfish2_Island_Event_Bll_Hash::setval($xiaoxikey, serialize($message) );
		}

		$this->_redirect("teambuy/collectlist");
	}

	public function controlswitchAction()
	{
		$uids = $this->_request->getParam('uids');
		$type = $this->_request->getParam('type');

		$result = array();
		$result['type'] = $type;
		$result['uid'] = $uids;

		$key = "collectcontrolswitch";
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->set($key,$result);

		$this->_redirect("teambuy/collectlist");
	}

	public function clearcollectAction()
	{
		Hapyfish2_Island_Event_Bll_Hash::clearall();
		$this->_redirect("teambuy/collectlist");
	}
	/**********收集任务后台************/
}