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

		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
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

}