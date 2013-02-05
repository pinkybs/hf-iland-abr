<?php

class ToolsController extends Zend_Controller_Action
{
	/*
	function vaild()
	{

	}
	*/
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
	function check()
	{
		$uid = $this->_request->getParam('uid');
		if (empty($uid)) {
			echo 'uid can not empty';
			exit;
		}

		$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($uid);
		if (!$isAppUser) {
			echo 'uid error, not app user';
			exit;
		}

		return $uid;
	}

    public function checkistest()
    {
        if ( STATIC_HOST == 'http://tbstatic.hapyfish.com' ) {
            echo 'false';
            exit;
        }
    }
    
	public function addcoinAction()
	{
        $this->checkistest();
		$uid = $this->check();
		$coin = $this->_request->getParam('coin');
		if (empty($coin) || $coin <= 0) {
			echo 'add coin error, must > 1';
			exit;
		}

		Hapyfish2_Island_HFC_User::incUserCoin($uid, $coin);

		echo 'OK';
		exit;
	}

	public function addgoldAction()
	{
        $this->checkistest();
		$uid = $this->check();
		$gold = $this->_request->getParam('gold');
		if (empty($gold) || $gold <= 0) {
			echo 'add gold error, must > 1';
			exit;
		}

		$goldInfo = array(
			'uid' => $uid,
			'gold' => $gold,
			'type' => 0
		);
		Hapyfish2_Island_Bll_Gold::add($uid, $goldInfo);

		echo 'OK';
		exit;
	}
	
    public function decgoldAction()
    {
        $this->checkistest();
        $uid = $this->_request->getParam('uid');
        $decGold = $this->_request->getParam('gold');
        
        $dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
        $ok = $dalUser->decGold($uid, $decGold);
        
        Hapyfish2_Island_HFC_User::reloadUserGold($uid);
        
        $result = $ok ? 'OK' : 'not';
        echo $result;
        exit;
    }
    
	public function addstarfishAction()
	{
        $this->checkistest();
		$uid = $this->check();
		$starfish = $this->_request->getParam('starfish');
		if (empty($starfish) || $starfish <= 0) {
			echo 'add starfish error, must > 1';
			exit;
		}

		Hapyfish2_Island_HFC_User::incUserStarFish($uid, $starfish);

		echo 'OK';
		exit;
	}

	public function addexpAction()
	{
        $this->checkistest();
		$uid = $this->check();
		$exp = $this->_request->getParam('exp');
		if (empty($exp) || $exp <= 0) {
			echo 'add exp error, must > 1';
			exit;
		}

		Hapyfish2_Island_HFC_User::incUserExp($uid, $exp);

		echo 'OK';
		exit;
	}

	public function addcardAction()
	{
        $this->checkistest();
		$uid = $this->check();
		$cid = $this->_request->getParam('cid');
		if (empty($cid)) {
			echo 'card id[cid] can not empty';
			exit;
		}

		$count = $this->_request->getParam('count');
		if (empty($count) || $count <= 0) {
			echo 'add card number[count] error, must > 1';
			exit;
		}

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			echo 'card id[cid] error, not exists';
			exit;
		}

		Hapyfish2_Island_HFC_Card::addUserCard($uid, $cid, $count);

		echo 'OK';
		exit;
	}

	public function addachievementAction()
	{
		$uid = $this->check();
		$num = $this->_request->getParam('num');
		if (empty($num)) {
			echo 'num can not empty';
			exit;
		}

		if ($num <=0 || $num > 17) {
			echo 'num error, must > 0 and < 18';
			exit;
		}

		$count = $this->_request->getParam('count');
		if (empty($count) || $count <= 0) {
			echo 'add count error, must > 1';
			exit;
		}

		$field = 'num_' . $num;
		Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, $field, $count);

		echo 'OK';
		exit;
	}

	public function adddailyachievementAction()
	{
		$uid = $this->check();
		$num = $this->_request->getParam('num');
		if (empty($num)) {
			echo 'num can not empty';
			exit;
		}

		if ($num <=0 || $num > 17) {
			echo 'num error, must > 0 and < 18';
			exit;
		}

		$count = $this->_request->getParam('count');
		if (empty($count) || $count <= 0) {
			echo 'add count error, must > 1';
			exit;
		}

		$field = 'num_' . $num;
		Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, $field, $count);

		echo 'OK';
		exit;
	}

	public function cleardailytaskAction()
	{
		$uid = $this->check();
		Hapyfish2_Island_Cache_TaskDaily::clearAll($uid);

		echo 'OK';
		exit;
	}

	public function changelevelAction()
	{
		$uid = $this->check();
		$level = $this->_request->getParam('level');
		if (empty($level)) {
			echo 'level can not empty';
			exit;
		}

		if ($level <=0 || $level > 200) {
			echo 'level error, level > 0 and < 200';
			exit;
		}

		$levelInfo = array('level' => $level);
		$islandLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$curIslandLevel = $islandLevelInfo['island_level'];

		$levelInfo['island_level'] = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($level, 1);
		$levelInfo['island_level_2'] = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($level, 2);
		$levelInfo['island_level_3'] = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($level, 3);
		$levelInfo['island_level_4'] = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfoByUserLevel($level, 4);

		Hapyfish2_Island_HFC_User::updateUserLevel($uid, $levelInfo);
		$exp = Hapyfish2_Island_Cache_BasicInfo::getUserLevelExp($level);
		Hapyfish2_Island_HFC_User::updateUserExp($uid, $exp + 1, true);

		$step = $levelInfo['island_level'] - $curIslandLevel;

		Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid, $step);
		Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid, $step);
		echo 'OK';
		exit;
	}

	public function changelevelnoislandAction()
	{
		$uid = $this->check();
		$level = $this->_request->getParam('level');
		if (empty($level)) {
			echo 'level can not empty';
			exit;
		}

		if ($level <=0 || $level > 200) {
			echo 'level error, level > 0 and < 200';
			exit;
		}

		$levelInfo = array('level' => $level);
		$islandLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$curIslandLevel = $islandLevelInfo['island_level'];

		$levelInfo['island_level'] = $islandLevelInfo['island_level'];
		$levelInfo['island_level_2'] = $islandLevelInfo['island_level_2'];
		$levelInfo['island_level_3'] = $islandLevelInfo['island_level_3'];
		$levelInfo['island_level_4'] = $islandLevelInfo['island_level_4'];

		Hapyfish2_Island_HFC_User::updateUserLevel($uid, $levelInfo);
		$exp = Hapyfish2_Island_Cache_BasicInfo::getUserLevelExp($level);
		Hapyfish2_Island_HFC_User::updateUserExp($uid, $exp + 1, true);

		$step = $levelInfo['island_level'] - $curIslandLevel;

		Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid, $step);
		Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid, $step);
		echo 'OK';
		exit;
	}

	public function clearhelpAction()
	{
		$uid = $this->check();
		Hapyfish2_Island_Cache_UserHelp::clearHelp($uid);
		echo 'OK';
		exit;
	}
	public function inituserhelpAction()
	{
		$uid = $this->check();
		$info = array('help' => '' ,'help_gift' => '');
		$dalUserHelp = Hapyfish2_Island_Dal_UserHelp::getDefaultInstance();
        $dalUserHelp->update($uid, $info);

		echo 'OK';
		exit;
	}

	public function loadnoticeAction()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::loadNoticeList();
		print_r($list);
		exit;
	}

	public function loadlocalnoticeAction()
	{
		$key = 'island:pubnoticelist';
		$cache = Hapyfish2_Island_Cache_BasicInfo::getBasicMC();
		$list = $cache->get($key);

		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false, 900);
		print_r($list);
		exit;
	}

	public function updatenoticeAction()
	{
		$id = $this->_request->getParam('id');
		$title = $this->_request->getParam('title');
		$link = $this->_request->getParam('link');
		$time = time();

		$info = array('title' => $title, 'link' => $link, 'create_time' => $time);
		try {
			$dalBasic = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
			$dalBasic->updateNoticeList($id, $info);
		} catch (Exception $e) {
			echo 'false';
			exit;
		}

		echo 'OK';
		print_r($info);
		exit;
	}

	public function loadfeedtemplateAction()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::loadFeedTemplate();
		$key = 'island:feedtemplate';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);
		echo 'OK';
		exit;
	}

	public function loadallAction()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::loadFeedTemplate();
		$key = 'island:feedtemplate';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadShipList();
		$key = 'island:shiplist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadBuildingList();
		$key = 'island:buildinglist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadPlantList();
		$key = 'island:plantlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadBackgroundList();
		$key = 'island:backgroundlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadCardList();
		$key = 'island:cardlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadDockList();
		$key = 'island:docklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadUserLevelList();
		$key = 'island:userlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadIslandLevelList();
		$key = 'island:islandlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadGiftLevelList();
		$key = 'island:giftlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadAchievementTaskList();
		$key = 'island:achievementtasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadBuildTaskList();
		$key = 'island:buildtasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadDailyTaskList();
		$key = 'island:dailytasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadShipPraiseList();
		$key = 'island:shippraiselist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadTitleList();
		$key = 'island:titlelist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadNoticeList();
		$key = 'island:pubnoticelist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		$list = Hapyfish2_Island_Cache_BasicInfo::loadGiftList();
		$key = 'island:giftlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		echo 'ok';
		exit;
	}

	public function loadgiftAction()
	{
		$list = Hapyfish2_Island_Cache_BasicInfo::loadGiftList();
		$key = 'island:giftlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list);
		echo 'OK';
		exit;
	}

	public function fixAction()
	{
		$uid = $this->check();
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid);
		$plants = $data['plants'];
		$praise = 0;
		$plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
		foreach ($plants as $plant) {
			$praise += $plantInfoList[$plant['cid']]['add_praise'];
		}

		$buildings = Hapyfish2_Island_HFC_Building::getOnIsland($uid);
		$buildingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
		foreach ($buildings as $building) {
			$praise += $buildingInfoList[$building['cid']]['add_praise'];
		}

		echo '<br/>cal: ' . $praise;
		$useIsland = Hapyfish2_Island_HFC_User::getUserIsland($uid);
		$curPraise = $useIsland['praise'];

		echo '<br/>current: ' . $curPraise;

		if ($curPraise != $praise) {
			$useIsland['praise'] = $praise;
			Hapyfish2_Island_HFC_User::updateUserIsland($uid, $useIsland, true);
			echo '<br/>save praise';
		}

		$achi = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
		echo '<br/>achi: ' . $achi['num_13'];
		if ($achi['num_13'] != $praise) {
			$achi['num_13'] = $praise;
			Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achi);
		}

		echo '<br/>num_15: ' . $achi['num_15'];
		$user = Hapyfish2_Island_HFC_User::getUser($uid, array('exp' => 1, 'level' => 1));
		$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($user['island_level']);
		if ($achi['num_15'] != $islandLevelInfo['max_visitor']) {
			$achi['num_15'] = $islandLevelInfo['max_visitor'];
			Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $achi);
		}

		$realDockPositionCount = Hapyfish2_Island_Cache_Dock::getPositionCount($uid);
		echo '<br/>position_count: ' . $useIsland['position_count'];
		if ($realDockPositionCount && $useIsland['position_count'] != $realDockPositionCount) {
			$useIsland['position_count'] = $realDockPositionCount;
			Hapyfish2_Island_HFC_User::updateUserIsland($uid, $useIsland, true);
		}

		echo '<br/>num_11: ' . $achi['num_11'];

		$userUnlockShipCount = Hapyfish2_Island_Cache_Dock::getUnlockShipCount($uid);
		print_r($userUnlockShipCount);

		Hapyfish2_Island_Cache_Dock::reloadUnlockShipCount($uid);

		exit;
	}

	public function fix2Action()
	{
		$uid = $this->check();
		$buildings = Hapyfish2_Island_HFC_Building::getOnIsland($uid);
		$fixed = false;
		if ($buildings) {
			$builingInfoList = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
			foreach ($buildings as $building) {
				$item_type = $builingInfoList[$building['cid']]['item_type'];
				if ($item_type != $building['item_type']) {
					$fixed = true;
					$building['item_type'] = $item_type;
					$building['mirro'] = 0;
					Hapyfish2_Island_HFC_Building::updateOne($uid, $building['id'], $building, true);
				}
			}
		}

		echo $fixed ? 'true' : 'false';
		exit;
	}

	public function addgiftsendcountAction()
	{
		$uid = $this->check();
		$count = $this->_request->getParam('count');
		if (empty($count)) {
			echo 'count can not empty';
			exit;
		}

		if ($count <=0 || $count > 100) {
			echo 'count error, count > 0 and < 100';
			exit;
		}

		$giftSendCountInfo = Hapyfish2_Island_Cache_Counter::getSendGiftCount($uid);
		$giftSendCountInfo['count'] += $count;
		Hapyfish2_Island_Cache_Counter::updateSendGiftCount($uid, $giftSendCountInfo);
		echo 'OK';
		exit;
	}

	public function watchuserAction()
	{
		$uid = $this->check();
		$t = time();
		$sig = md5($uid . $t . APP_KEY);

		$this->_redirect('http://main.island.qzoneapp.com/watch?uid=' . $uid . '&t=' . $t . '&sig=' . $sig);
		exit;
	}

	public function userinfoAction()
	{
		$uid = $this->check();
		$platformUser = Hapyfish2_Platform_Bll_User::getUser($uid);
		$islandUser = Hapyfish2_Island_HFC_User::getUser($uid, array('exp', 'coin', 'level'));
		$data = array(
			'face' => $platformUser['figureurl'],
			'uid' => $uid,
			'nickname' => $platformUser['nickname'],
			'gender' => $platformUser['gender'],
			'level' => $islandUser['level'],
			'exp' => $islandUser['exp'],
			'coin' => $islandUser['coin']
		);

		echo json_encode($data);
		exit;
	}

	public function coinlogAction()
	{
		$uid = $this->check();
		$time = time();
		$year = $this->_request->getParam('year');
		if (!$year) {
			$year = date('Y');
		}
		$month = $this->_request->getParam('month');
		if (!$month) {
			$month = date('n');
		}
		$limit = $this->_request->getParam('limit');
		if (!$limit) {
			$limit = 100;
		}

		$logs = Hapyfish2_Island_Bll_ConsumeLog::getCoin($uid, $year, $month, $limit);
		if (!$logs) {
			$logs = array();
		}
		echo json_encode($logs);
		exit;
	}

	public function upgradecoordinateAction()
	{
		$uid = $this->check();
		//Hapyfish2_Island_HFC_Plant::upgradeCoordinate($uid);
		Hapyfish2_Island_HFC_Building::upgradeCoordinate($uid);
		echo 'ok';
		exit;
	}

	public function p2Action()
	{
		$uid = $this->check();
		$data = Hapyfish2_Island_Cache_Background::getAll($uid);
		foreach ($data as $item) {
			if ($item['id'] > 1000) {
				Hapyfish2_Island_Cache_Background::delBackground($uid, $item['id']);
			}
		}
		print_r($data);

		exit;
	}

	public function p3Action()
	{
		$uid = $this->check();
		$fieldInfo = array();

		//25411, 1, 23212, 2, 22213, 3, 25914, 4
            //island
		$fieldInfo['bg_island'] = 25411;
		$fieldInfo['bg_island_id'] = 1;

            //sky
		$fieldInfo['bg_sky'] = 23212;
		$fieldInfo['bg_sky_id'] = 2;

            //sea
		$fieldInfo['bg_sea'] = 22213;
		$fieldInfo['bg_sea_id'] = 3;

            //dock
		$fieldInfo['bg_dock'] = 25914;
		$fieldInfo['bg_dock_id'] = 4;

		$ok = Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $fieldInfo);

		echo $ok ? 'OK' : 'Flase';
		$d = Hapyfish2_Island_HFC_User::getUserIsland($uid);
		print_r($d);
		exit;
	}

	public function clearremindAction()
	{
		$uid = $this->check();
		Hapyfish2_Island_Cache_Remind::flush($uid);
		echo 'OK';
		exit;
	}

	public function addinviteAction()
	{
		$uid = $this->check();
		$fid = $this->_request->getParam('fid');
		if (empty($fid)) {
			echo 'fid can not empty';
			exit;
		}

		$isAppUser = Hapyfish2_Island_Cache_User::isAppUser($fid);
		if (!$isAppUser) {
			echo 'fid error, not app user';
			exit;
		}
		Hapyfish2_Island_Bll_InviteLog::add($uid, $fid);
		echo 'OK';
		exit;
	}

	public function loginactiveAction()
	{
		$uid = $this->check();
		$starDays = (int)$this->_request->getParam('starDays', 1);
		$days = (int)$this->_request->getParam('days', 1);
		$loginCount = (int)$this->_request->getParam('loginCount', 1);
		$loginInfo = array(
			'last_login_time' => time() - 86400,
			'active_login_count' => $days,
			'max_active_login_count' => 5,
			'today_login_count' => 0,
			'all_login_count' => $loginCount,
			'star_login_count' => $starDays
		);
		Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo, true);

		$key = 'i:u:ezinecount:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        $data[2] = 0;
        $cache->set($key, $data, 864000);

		echo 'OK';
		exit;
	}

	public function clearezAction()
	{
		$uid = $this->check();
		$key = 'i:u:ezinecount:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);
		echo 'OK';
		exit;
	}

	public function cleardlyawardAction()
	{
		var_dump(1);
		$uid = $this->check();
		$mckey = Hapyfish2_Island_Bll_DailyAward::$_mcKeyPrex . $uid;
	    $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($mckey);
		echo 'OK';
		exit;
	}

	public function updatetaskAction()
	{
		$uid = $this->_request->getParam('uid');
		//get user achievement info
        $userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
        echo json_encode($userAchievement);
		$taskType = $this->_request->getParam('taskType');
		$num = $this->_request->getParam('num');
		$taskType = 'num_' . $taskType;
		Hapyfish2_Island_HFC_Achievement::updateUserAchievementByFieldData($uid, $taskType, $num);

        $dalTask = Hapyfish2_Island_Dal_Task::getDefaultInstance();
        $dalTask->clear($uid);

	    $cache = Hapyfish2_Cache_Factory::getMC($uid);
	    $key = 'i:u:alltask:' . $uid;
		$cache->delete($key);

		$titleInfo = array('title' => 0, 'title_list' => '');
        Hapyfish2_Island_HFC_User::updateUserTitle($uid, $titleInfo);

        Hapyfish2_Island_Cache_Task::updateUserOpenTask(uid, array());
        $keyOpen = 'i:u:openTask2:' . $uid;
        $cache->delete($keyOpen);

		echo 'OK';
		exit;
	}

	public function testcardAction()
	{

		/*$result = Hapyfish2_Island_Bll_Card::useCard(1016, 1016, 26841, 1);

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField(1016, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField(1016, 'num_2', 1);
		} catch (Exception $e) {

		}
		//task id 3004,task type 2
		$checkTask = Hapyfish2_Island_Bll_Task::checkTask(1016, 3004);
		if ( $checkTask['status'] == 1 ) {
			$result['finishTaskId'] = $checkTask['finishTaskId'];
		}
		echo json_encode($result);*/
	}

	public function loginactivenewsAction()
	{
		$uid = $this->check();
		$starDays = (int)$this->_request->getParam('starDays', 1);
		$days = (int)$this->_request->getParam('days', 1);
		$loginCount = (int)$this->_request->getParam('loginCount', 1);
		$loginInfo = array(
			'last_login_time' => time() - 86400,
			'active_login_count' => $days,
			'max_active_login_count' => 5,
			'today_login_count' => 0,
			'all_login_count' => $loginCount,
			'star_login_count' => $starDays
		);
		Hapyfish2_Island_HFC_User::updateUserLoginInfo($uid, $loginInfo, true);
		echo 'OK';
		exit;
	}

	public function clearchangelistAction()
	{
		$key = 'event:pointchalist';
		$EventFeed = Hapyfish2_Cache_Factory::getEventFeed();
		$EventFeed->delete($key);
		echo 'OK';
		exit;
	}

	public function addpointAction()
	{
		$uid = $this->_request->getParam('uid');
		$point = $this->_request->getParam('point');

		$dalCasino = Hapyfish2_Island_Event_Dal_Casino::getDefaultInstance();
		$dalCasino->updateUserPoint($uid, $point);

		$data = $dalCasino->getUserPoint($uid);

		$key = 'i:u:casinop:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $data);

		$feed = '因系统漏洞，您兑换的物品没有到账，现已补发您消耗掉的积分' . $point;

        $minifeed = array('uid' => $uid,
                          'template_id' => 0,
                          'actor' => $uid,
                          'target' => $uid,
                          'title' => array('title' => $feed),
                          'type' => 6,
                          'create_time' => time());
        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);

		$total = $data;

		echo $uid . '  '. $total;
		exit;
	}

	public function gaintitleAction()
	{
		$uid = $this->_request->getParam('uid');
		$titleId = $this->_request->getParam('tid');

		try {
        	Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
		} catch (Exception $e) {
			echo 'false';
			exit;
		}
		echo 'ok';
		exit;
	}

    public function loadshiplistAction()
    {
    	$list = Hapyfish2_Island_Cache_BasicInfo::loadShipList();
		$key = 'island:shiplist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false);

		echo 'OK';
		exit;
    }

	function clearnewislandAction()
    {
    	$uid = $this->_request->getParam('uid');
    	$islandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
    	$islandInfo['unlock_island'] = '1';
    	$islandInfo['current_island'] = 1;
    	Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $islandInfo);

    	$userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userLevelInfo = array('level' => $userVo['level'],
							   'island_level' => $userVo['island_level'],
							   'island_level_2' => 0,
							   'island_level_3' => 0,
							   'island_level_4' => 0);
        Hapyfish2_Island_HFC_User::updateUserLevel($uid, $userLevelInfo);

        //$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
        //$dalBackground->clear($uid);
        $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
        $dalBuilding->clearNewIsland($uid);
        $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
        $dalPlant->clearNewIsland($uid);

		$cache = Hapyfish2_Cache_Factory::getMC($uid);

        $key1 = 'island:allplantonisland:' . $uid . ':' . '2';
		$cache->delete($key1);
        $key2 = 'island:allplantonisland:' . $uid . ':' . '3';
		$cache->delete($key2);
        $key3 = 'island:allplantonisland:' . $uid . ':' . '4';
		$cache->delete($key3);

		$key4 = 'i:u:bldids:onisl:' . $uid . ':' . '2';
		$cache->delete($key4);
		$key5 = 'i:u:bldids:onisl:' . $uid . ':' . '3';
		$cache->delete($key5);
		$key6 = 'i:u:bldids:onisl:' . $uid . ':' . '4';
		$cache->delete($key6);

		$key7 = 'i:u:pltids:onisla:' . $uid . ':' . '2';
		$cache->delete($key7);
		$key8 = 'i:u:pltids:onisla:' . $uid . ':' . '3';
		$cache->delete($key8);
		$key9 = 'i:u:pltids:onisla:' . $uid . ':' . '4';
		$cache->delete($key9);

		$key10 = 'i:u:isfstin:' . $uid . ':' . '2';
		$cache->delete($key10);
		$key11 = 'i:u:isfstin:' . $uid . ':' . '3';
		$cache->delete($key11);
		$key12 = 'i:u:isfstin:' . $uid . ':' . '4';
		$cache->delete($key12);

		echo 'OK';
		exit;
    }

    function savediyAction()
    {
    	$dbId = $this->_request->getParam('dbid');
    	Hapyfish2_Island_Tool_Savediy::savedbAllUser($dbId);

		echo 'OK';
		exit;
    }

    function repairplantAction()
    {
    	$cid = $this->_request->getParam('cid');
    	$uid = $this->_request->getParam('uid', 1);
    	Hapyfish2_Island_Tool_Repair::repairUserPlant($cid, $uid);

		echo 'OK';
		exit;
    }

    function repairuserAction()
    {
    	$uid = $this->_request->getParam('uid');
    	Hapyfish2_Island_Tool_Repair::repairUserInfo($uid);

		echo 'OK';
		exit;
    }

    function sendboxgiftAction()
    {
    	$uid = $this->_request->getParam('uid');
    	$taskId = $this->_request->getParam('taskId');

		Hapyfish2_Island_Bll_Task::checkTask($uid, $taskId);

		echo 'OK';
    	exit;
    }

    function cleartitlecacheAction()
    {
    	$uid = $this->_request->getParam('uid');

    	$key = 'i:u:ach:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $cache->delete($key);

        echo 'OK';
        exit;
    }

    function clearuserxmasAction()
    {
    	$uid = $this->_request->getParam('uid');

    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$mkeyUid = 'event_xmas_fair_daily_' . $uid;
		$cache->set($mkeyUid, false);

		echo 'OK';
		exit;
    }

    function clearxmasinfoAction()
    {
    	$mkey = 'event_xmas_fair';
		$cacheInfo = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cacheInfo->delete($mkey);

		echo 'OK';
		exit;
    }
    
    public function testcatchAction()
    {
        $uid = $this->_request->getParam('uid');
        $productid = (int)$this->_request->getParam('id');
        $type = (int)$this->_request->getParam('type');
        
        $result = Hapyfish2_Island_Bll_Test::catchFish($uid, $productid, $type);
        echo json_encode($result);
        
        exit;
    }
    
    
	public function addboatAction()
	{
		$uid = $this->_request->getParam('uid');
		$positionId = $this->_request->getParam('pid');

		if ( !in_array($positionId, array(4,5,6,7,8)) ) {
			echo 'False';
			exit;
		}

		Hapyfish2_Island_HFC_Dock::expandPosition($uid, $positionId, 10);

		echo 'OK';
		exit;
	}

	public function updatetimegiftAction()
	{
		$uid = $this->_request->getParam('uid');
		$step = $this->_request->getParam('step');

		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$key = 'event_timegift_' . $uid;
		$val = $cache->get($key);
		$val['state'] = $step;
		$val['time_at'] = time();
		$cache->set($key, $val, 100000);

		echo 'OK';
		exit;
	}
	public function clearusercardAction()
	{
		$uid = $this->_request->getParam('uid');
		$cid = $this->_request->getParam('cid');
		$num = $this->_request->getParam('num');
		Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, $num);
		echo "OK";
		exit;
	}

	public function repairpraiseAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$keys = array(
			'i:u:exp:' . $uid,
			'i:u:coin:' . $uid,
			'i:u:gold:' . $uid,
			'i:u:level:' . $uid,
			'i:u:island:' . $uid,
			'i:u:title:' . $uid,
			'i:u:cardstatus:' . $uid
		);

		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		
		$dalUserIsland = Hapyfish2_Island_Dal_UserIsland::getDefaultInstance();
		$userIsland = $dalUserIsland->get($uid);
		
		$cache->save($keys[4], $userIsland);
		
		echo 'OK';
		exit;
	}
	
	public function updatebiggiftlevelAction()
	{
		$uid = $this->_request->getParam('uid');
		
		Hapyfish2_Island_Cache_User::updateUserNextBigGiftLevel($uid, 5);
		echo "OK";
		exit;
	}
	
	public function getusercacheAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$mckey = Hapyfish2_Island_Bll_DailyAward::$_mcKeyPrex . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $dailyReward = $cache->get($mckey);
		
		print_r('<pre>');print_r($dailyReward);print_r('</pre>');
		
		echo 'OK';
		exit;
	}
	
	public function clearleveltaskAction()
	{
		$uid = $this->_request->getParam('uid');
		$level = $this->_request->getParam('level');
		
		//update achievement task,22
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByFieldData($uid, 'num_22', $level);

			//task id 3050,task type 22
			Hapyfish2_Island_Bll_Task::checkTask($uid, 3050);
        } catch (Exception $e) {
        }
        
        echo 'OK';
        exit;
	}
	
	public function getuserplantinfoAction()
	{
		$ownerUid = $this->_request->getParam('uid');
		$itemId = $this->_request->getParam('itemId');
		$islandId = $this->_request->getParam('islandId');
		
		$userPlant = Hapyfish2_Island_HFC_Plant::getOne($ownerUid, $itemId, 1, $islandId);
		
		print_r('<pre>');print_r($userPlant);print_r('</pre>');
		exit;
	}
	
	public function updateachievementAction()
	{
		$uid = $this->_request->getParam('uid');
		$num = $this->_request->getParam('num');
		$value = $this->_request->getParam('count');
		
		$mas = 'num_' . ($num - 1);
		
		//$data = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
		$key = 'i:u:ach:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$cache->delete($key);
        
        echo 'OK';
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
	
	//七夕活动奖励
	public function tosendqixiAction()
	{
		$tid = $this->_request->getParam('tid');
		
		//$uids[1] = array(1867426,4430671,1286145,628203,5661836,4650956,4944183,3548719,332941,4841666,534104,3697838,5680094,5671166,61008,88563,2508387,2570794,2664740,6396469,5768628,5852962,39985,6365721,1566934,1199312,5269582,4285737,5806552,2556762,5588854,1286067,3940705,182997,6425972,5453253,2771695,4099086,36605,5915088,1334969,5330057,3744562,4683351,767623,1388636,1535611,4836531,6279852,4141556,786646,5423755,3711843,4065349,4799517,2073334,5198511,6687372,987125,5132411,3465190,3474516,4659619,6132182,5588793,4490782,3048498,5603757,5751730,1209601,6673691,4831488,3244085,6557152,86995,6573341,3114124,3050555,3750233,5845388,2654340,14654,4838412,5930768,6672342,2156872,4727231,4180902,49828,3014944,1377156,1613802,4373593,152094,3788820,595736,5330957,4477648,105845,1541342,5207164,2492803,5498967,5433622,2607745,47177,2054662,4231495,3892624,3502456,31841,6083172,1412951,52252,3122246,6029423,4269513,3345438,4927456,1242133,2644505,1701941,6028045,372144,303700,3237219,3085210,5157026,1582721,2356320,4432654,4922,5310832,5397728,6431238,4057581,67920,4954034,5716489,4553632,937412,939855,4272834,2601204,1370711,738509,3071264,4265593,36209,1933039,1630506,122165,802610,2226813,578904,4288607,4868339,2513249,574228,5135536,351699,2073652,2631579,6529858,5361161,22631,514277,567790,2404495,4709230,4130526,2442474,1281802,2846477,4296112,1318281,3612853,2887623,1389165,5524247,3105425,3307627,2377989,3645036,4033635,2368292,5362851,6370494,1689748,6604249,4829475,1320079,3840243,6663850,4120344,34773,7332,4522355,98237,853631,25631,3656302,3498906,18339,2446758,4614872,4611275,148808,3666746,5204951,26463,2409927,4358757,1097746,5396678,3906916,4789969,5689148,3831537,2771876,3650915,189901,5195034,1860572,3238135,3263569,3870631,6384045,2946822,3108075,4411535,1504,4339556,708102,2380494,5568940,4078102,1212692,2116318,2565387,4535375,1300588,5765960,2567024,903587,1755417,6383063,3060720,66215,66043,4107640,3562465,4626154,2997583,1896644,4060687,3985691,1306381,1509856,563002,599401,6481941);
		//$uids[2] = array(3642229,3814211,696801,3002727,4483484,5403869,5878848,1850142,1329705,2548088,926343,5351814,5605412,1617952,3446837,351202,425066,541755,5789606,6406160,6469883,1499278,2160937,1803120,882728,1019946,1568944,1610124,2054382,2996849,2533379,1950519,3701883,196913,2524304,4970861,2042044,1466293,5888062,1023303,4890054,6081910,6624725,1363420,1084417,1242172,990126,3009426,2843260,10946,3949760,3271090,5857445,59754,3950730,2053393,1842224,2923636,6437380,3201211,5410843,2102627,882436,886130,3790035,5566794,1898957,970701,2047393,111814,4870378,2158278,4848091,5367467,3758408,1286307,1226274,4959047,3903994,2312655,5588447,5827025,3185479,3063337,2635113,2274758,1910741,554394,5223917,2944497,4620928,3091761,1004207,6633732,1184172,4368417,2361608,230441,319635,127023,586488,4083100,5316501,26992,6625462,5378229,981800,3750579,2230253,3370701,5745828,1425041,6119268,4806851,1977241,1211901,6665000,6561763,5863379,3594222,3857016,3047332,5344433,34258,4602094,5724880,532530,5625195,6548913,336103,504181,5271276,5278167,5231598,4179352,6083787,521504,5692700,2427973,1796876,2501114,6602919,1230849,730402,6092745,4299004,3349960,69616,1585747,4049432,1757174,748726,3010496,2323529,2658795,3523869,2382437,3513119,6448701,507535,5382376,4052393,4709534,4087712,402894,4242919,5023920,986676,4845197,3484461,5603731,1606268,900495,314446,4047608,4078924,553407,866133,911527,710674,4958290,1579053,4700862,1637269,5228811,1805958,2288406,2360226,4959513,410404,2906513,6647194,3383139,4140772,946440,6407871,549822,3964431,3893858,4108293,4517805,6525412,4646619,1265662,4729811,6482917,3681853,1411227,1785966,4287848,1964320,6447203,5316311,5502838,4484956,1563248,5826053,5744838,805141,561391,619861,5935335,3883781,1956075,2897843,4367690,2452573,3055390,2498821,4648369,4484,5682458,4428518,5436140,5511922,534675,4624693,6548278,130867,3774631,5703571,3163721,1236609,2818055,1184073,1304088,2448880,825463,4627599,2993954,4943130,1277998,2852961,2948265,5134310,2719950,3319449,6081537,6358828,1786869,5205647,905284,234648,5670067,269234,5681767,2083,2578412,3999535,3161902,3924663,1223635,516456,3843704,2409223,127926,2726890,1132255,442886,2077306,6075563,718463,1953417,5607700,1251280,2196487,5731512,578661,6561986,1565657,2021629,5600933,687687,579438);
		//$uids[3] = array(1184136,4807090,4795351,5133186,5035167,5038698,5327318,5379591,6002032,350592,6112046,455880,6068705,670820,3595086,4710146,1072545,1136694,112228,720727,2179719,2050320,2104463,2296891,2152810,2115811,2155470,2230040,2227710,2223744,2241979,2253544,5884985,2223243,2297326,2247868,2334805,2314421,2303735,2336407,2356340,2393922,2385148,2383349,2407546,2439230,2682426,2733773,2944063,3068912,3420504,3488430,6456717,4788278,3942835,6234284,1949265,4073588,4084059,4605974,6257317,134456,286718,5360874,6056570,1227820,2766396,5972282,5961552,6022852,6055106,6047967,6032340,6077447,6081073,6062098,3458848,6105850,6077628,6120880,6154083,6154538,6375570,135057,6446803,3827864,6450911,6443555,6451823,1208131,6464291,554612,6519506,6507613,4350666,6528925,6485169,6538210,6551302,720252,6562021,6549799,6577132,6600622,6594244,6598534,6620342,6627412,6639551,6633620,5237906,34331,6663612,6666922,6691397,6676546,6687031,6630068,6693611,3753389,4226184,158034,4652518,998170,2674409,631963,3715907,6619388,5786253,501560,1693073,6449148,6693086,75521,3196010,5994247,47061,3590795,5909471,4361395,3120131,5277329,1428521,75447,2394663,6100742,1269105,53667,31861,3239271,3316542,4831184,4528962,4124531,2407334,6174609,743936,6547740,1347208,4230798,3960148,4627702,880830,1013592,2309949,4917924,4347138,6055629,1512299,4740244,1741228,4120087,3598509,5748337,1208063,5608890,4510324,5617060,5323957,1218751,4580580,1179507,6276738,5208375,728021,3400650,4411711,5152510,1238606,6509255,267513,4880885,2628037,4255109,1762534,1300162,3241935,3780915,5229847,2046655,3831777,3921,4334415,1814154,4114701,2122933,1510121,4863275,2033461,4548931,519622,161955,5221056,257474,568320,2650838,5052219,2519955,2225540,1785376,459937,4204624,3280256,1810540,5107329,1814176,804173,327060,400610,4998701,5011624,1740360,3737929,2088364,2344816,2415255,1862245,1874503,63903,1214161,739313,529912,5949923,1975842,5435653,5471302,4717787,2134172,2445333,3446404,1242372,2213515,5351277,1723561,3589935,162985,412199,1272489,3187097,502145,4966895,3705234,1700227,4715448,758172,5143103,6645417,4345459,5467420,1383010,3951352,5468924,5896053,4438407,3536010,6416560,5195194,3701329,3445986,428507,6598052,3973176,4147915,1150765,3863979,597301,114526,6569299,1632916,4322876,4921414,638724,844872,6660147,4847971,4022021,6059704);
		//$uids[4] = array(4497846,3860551,873948,875383,5599728,65067,5840617,46165,416296,1287870,4348244,6150327,3028607,5854456,6168230,1247008,2632279,1052172,3924358,537425,667160,2087100,5580555,5144968,3480507,522247,3301568,3480172,749386,5261785,484870,5143731,18384,5065250,24590,4034088,5960462,518441,6512664,4139939,3416549,1686553,1461149,1437657,3163560,3037086,6450392,1757473,4213411,1300545,746361,2326015,811493,3936572,1221562,64206,6242492,2884398,3821903,1797280,1429378,247035,1605335,6554103,49064,141766,129717,2968867,6900,1996911,61012,738456,1453661,1621295,2472793,4581432,5551172,992749,4658550,1268937,3050495,3820428,6530000,6056680,4302151,4942253,1176379,5062910,4877419,4340993,4633493,2010181,4840691,5485263,4813428,4567101,459516,1842571,4358787,2746746,3385977,6146201,3067745,265099,4015706,914746,2923942,1299117,4237674,733917,4024809,5776497,6378354,2098,44594,6416,2300060,2458267,1855540,4595589,6067085,4410506,847575,4416120,4928684,4975254,2423834,4228526,1673599,4140165,5600761,4162157,1205915,1675931,2581106,6671225,3433936,1283236,1286063,4923346,547331,2648064,2846198,711184,3671663,4341215,3754470,3034308,2975697,5744149,3819356,3719936,4378863,1832839,4664541,3955120,1636501,2484872,6073929,4129367,2228906,5159431,2548700,2796666,4992770,3605677,4714044,1300133,3590661,3189373,28282,77482,6064461,4778938,806258,2743109,3933557,6533787,2667086,306443,1246965,1830895,5002557,1851702,4743869,2821621,2403306,4269812,1827092,1532837,4995765,4531249,2073334,1832110,4088851,3108450,46686,2107966,4204509,107132,3757729,6398321,5822530,5434303,5880075,4322736,958713,1206956,1363391,3599254,96510,3325373,587238,268976,1184709,1573095,552166,4243603,5485147,5209783,2425985,5564812,3819274,809247,3171374,588284,3595852,4143981,4808303,3223465,705861,6132182,4274778,1162659,4947505,2082339,4924798,5665120,1445320,5164129,5933914,1890875,1790938,35105,2933834,56683,6617095,4832669,2372297,4095250,3414199,5105429,141874,141198,5496306,60997,178199,3298506,620759,3882451,95560,4039618,3419927,3244085,4329945,3527724,5573095,1297681,6514384,420191,2095226,5853707,2376920,3664516,1837479,2504435,2972498,4918348,3536804,6387032,1703408,570508,4583447,3231210,4164341,617961,3685118,4161658,4161658,6032132,2366370,1226753,223112,4428644,5572931,5565539,4983237,4416717,506943,6475848,1870371,4816771,2961663,1294085,5430100,4288005,385014,6013738,1859010,4322350,1358269,5462384,101606,2956979,1525303,1734645,1120316,2672644,5071723,1696471,499424,5942035,4153455,5687064,4382857,6579075,6593533,4861570,2191443,4506932,1259226,62756,3065331,4913972,3295498,5140589,5148882,6545136,2888029,3284353,2465933,2437988,4631986,539058,5544958,4295204,2068561,1086409,3114443,1778112,4187577,4334745,4711806,1183864,5112441,157316,493403,2645116,819640,23176,3167861,3637079,277635,3414706,2411116);
		//$uids[5] = array(78551,1845466,1783651,700734,3750490,1467750,39089,4204048,6049149,711407,847191,555831,1415541,5787943,2686738,5035179,4410997,5578771,38001,3668607,1587036,2607745,5588565,2516339,1778154,7313,3497914,2807705,3698393,4352355,2345082,169229,989000,255790,39798,1877935,1661741,3284709,1301089,5683762,1350016,6083172,576228,1229283,4328727,5590228,4636215,3086564,6464832,6195881,1367707,2029364,3210950,2321254,5595944,3808466,2285686,4324113,6081994,875602,1638265,499648,134338,6507387,377401,3495959,4568683,3122246,5702016,2515079,4546491,3701918,1805233,4627428,40018,12039,789318,5088712,3276990,2109,5263806,6020325,231249,5280783,5104012,3647169,50507,4353902,5926882,1451879,4448669,2666055,505212,5693745,2410957,574598,5640907,1701941,2217033,5707957,3060425,2355667,5049568,6138257,2373867,3312531,2105558,5964929,5995828,2201023,124134,252454,2065273,5560812,3110190,5940882,3022889,5683754,649645,1186441,1798428,17224,15055,1035459,4525576,220487,676650,3312812,930162,5576483,6380153,3035794,1598785,283752,1304252,6696536,5204866,1790350,101831,537008,5040678,1901341,581891,1201925,6626537,3669971,3453044,1458199,6085402,3634847,4799363,2122590,883715,2583211,1919,5129719,3960949,3629258,5533167,3722211,2810327,4044657,2393702,970622,6230414,1808652,4964123,2059901,5190294,3869779,3718096,1245293,757281,3640363,5647313,295257,5607493,2851279,6431238,5085338,3121605,5615118,3602163,6037294,259480,5622213,2518512,4517449,5397509,1791663,3738660,4974024,562669,4834801,2907195,3315574,5428984,4229179,5133651,1232300,5997479,470194,5485042,2436791,1576311,1506222,3031111,6589730,2692086,25224,6580561,3712121,5145430,3901856,2450093,191876,2453215,3538082,2608730,1298560,1219437,2014760,5853663,3169241,6030729,2830769,1248037,3068798,4567403,1371802,4910310,1480902,3401929,5040199,5749228,36845,1229592,66063,3459564,315808,2922503,1514757,410550,1306906,1821346,664408,1390453,2341051,3895829,784441,2286933,1366538,5766368,3522625,5573439,4374009,5100204,913285,531165,6547416,4603802,853095,1813524,2100793,4343365,2947981,5394928,3268235,3785719,3061951,5331620,4118800,6085965,1314330,6176369,2937735,5190718,5890296,5584432,2981112,2971240,3448781,5415664,576221,1837402,1149181,943537,5726656,3092787,5557300,4528672,5377513,5210666,1268517,2037999,4868339,5157981,4875212,3780706,4256221,2034290,4110164,6661215,1248028,4558609,6501305,3471534,5446652,3760619,32798,1359801,1737533,5537563,6094434,5099198,4179210,896303,4538954,4160262,735218,2353392,344459,3062672,4885494,3251017,3787399,938751,67567,3261521,38223,5498889,1309288,5361161,1277843,2941219,4497605,2962527,3667174,3946152,5260168,791063);
		//$uids[6] = array(2833887,3942468,2583825,3784029,41789,4456286,6414352,3577519,3578679,567790,2707021,5360449,3059344,1082941,1039287,2184444,5598657,451517,4463494,1424784,5374167,4383033,1801846,24724,3722527,6159730,3001951,823505,15927,4054615,4798249,1135658,2070716,1798757,2385313,3212008,2258315,5282751,3682474,5622342,1305951,4881076,5460916,722214,5433921,5851768,5108332,1824572,1288093,1255033,4159614,632930,4118301,1261703,4590743,3755907,3373860,3706955,5348485,3102825,2719636,3152437,4296112,6653993,5449513,6291618,3747926,3638688,4577976,3930186,1392710,6296763,5131680,1956263,1263498,39328,1484442,19548,1501387,2863568,5912659,5516854,3546012,2659113,4075617,2699975,91051,1574004,5135087,5179214,4954115,18743,3324110,4982862,754598,978583,3476503,5127306,5160440,2394459,6079531,3344622,695110,1092636,1557556,461395,3291970,5030253,4128988,1775366,4332866,826601,4033635,5372026,5349465,5530112,1799,3191172,3545144,589901,2640748,2366070,4180206,4925176,6325112,3574399,507532,2154709,3819348,5165059,3600721,1309964,2275121,1752267,3957593,1820532,845348,2185772,1880504,1357086,3860442,2639838,5664005,5362993,4735318,6586302,1323511,3636400,1548598,4929214,5543368,3672941,5773038,4235031,4645651,597615,4962065,2572,1215531,1385356,3978836,5528776,3038668,6597972,664902,6332158,579576,588578,586239,4810629,1777292,1773749,591663,1478135,3208098,1981350,3453903,5515589,5475620,6660247,4005857,6398801,7358,3971473,2093488,1507455,3379180,2520710,792000,6419685,5673940,3660064,4576975,762414,1775797,3107125,5290571,1278836,1376104,1072184,6004324,243233,3768597,5429220,248348,239507,3736419,5769740,621849,738989,3493071,840745,1853783,34433,5557125,6224884,2548403,1265908,3238240,2052061,4355231,4625388,111493,5764167,589205,707631,3095660,6444898,2161719,2167780,608039,5345565,5268749,3714073,146398,146398,4609865,4507082,5104271,4572938,4965143,4607214,4807614,3344538,2937990,4825037,1420513,4678121,2123763,1275997,699927,6404951,1307027,5422747,983302,990871,5427945,4437953,5760637,2338156,2573334,3625739,5182696,84414,6533852,4317251,2704532,3546192,3176961,1608416,4152988,520462,1081083,3740007,1400765,1720674,4994504,4994504,5464519,6575843,3156313,3292691,2410377,71947,558136,82019,5455656,1609485,919565,4707436,2820119,3770669,749906,8528,3876111,2976,31611,3064548,5614986,2602162,1195504,3832596,3775041,2159771,1869925,6613,573777,28075,2338710,3650371,2875171,3998937,5367021,4953904,3946233,802606,3396008,73365,4532678,1838693,2371563,30662,3566897,6473325,3056323,15302,5307947,1310395,2799610,254597,319338,504657,2628107,2717140,1862836,5732882,3369803,5450483,3812816,5350271,2065108,1135594,5430913,2017619,5954616,2041918,2145483,3319177,1995473,657001,5419998,6016078,1283939,6429069,4135748,6032188,129366,3723256,5771508,745032,4305570,6371195,5274540,2191514,6664132);
		//$uids[7] = array(4288555,4402196,5257751,2346803,3347532,1911091,4232097,111141,41523,4909661,6496680,3778692,2165517,5898283,6455613,2760744,3748864,4376957,1289122,2106050,5725717,6102143,613493,13692,5826078,802480,5651538,5031088,115022,1306271,1571729,35665,290293,125407,4129154,1307614,3108075,89983,198832,1967236,5487535,3807931,759312,4822904,4573672,4555421,164424,4494173,3195277,2095854,2480977,5419994,1222639,2912444,5380351,124848,298282,1330004,3071001,799722,3122302,5792422,2991810,1315484,4529234,5349089,6382470,2680106,5356652,2134766,470486,6476533,38972,3271502,5210868,348327,186615,444957,2463518,2327506,1165787,1234707,4313067,2352254,2760569,3061925,5161244,533212,4340925,5951386,858116,4127696,3610039,4770621,4475129,4131987,3778669,119108,1372708,3854958,3134270,5205256,5126418,3891314,574876,3930210,4058276,3637475,4309059,4356646,2420229,1946414,3571364,1346420,3802655,3318187,6107120,2481889,4479946,3378396,4337003,5031250,4857158,313927,1770527,1297825,1313948,5884378,4846358,656750,4077939,4853891,4322331,4049213,1313582,3151486,3621075,1179310,2359744,514196,765175,208048,9196,56952,5605567,4221619,515539,3345293,4448004,5516735,41401,4942993,3359646,6623004,1272953,6570223,2359379,1945064,1164135,4532593,2371099,2150465,3014752,3059223,4241611,2117070,4203108,3264017,4327564,3070845,4771189,5279249,4898121,2506154,4991475,4906806,6026868,4096453,32253,1265412,4284092,1306381,3091453,5505226,4268592,4428178,901404,4796399,3639182,1294267,2750770,4220149,2392321,3679253,2097717,4231300,158306,6444813,3088280,2507751,1994980,6612205,858016,2721844,3715274,5411228,5042291,4966980,5460546,4895210,5695678,2725401,2698838,4243610,228080,4943126,4677647,1660722,2999706,919989,3170842,5594407,2831111,841484,557397,3635438,3702583,5267006,3750449,3809765,3843616,3881823,4000074,3991290,1995062,121553,4179224,4527847,4872693,2008059,4987368,5117588,2785107,5670329,1170898,2440279,5568595,5583236,337899,5558754,4393600,6351662,28157,5796867,1995256,118766,3150044,1573720,2678299,2299021,1186344,974965,1225048,1093673,5115306,1297801,1697542,1673440,1062944,1195341,2693678,1850142,1004914,4592575,365584,2881352,1952957,4263509,386424,1432034,3240783,3339009,3441510,41638,4909405,3927012,3442861,4238297,1407952,3311294,6559654,3559005,4929069,4861018,5226068,5453713,4435374,3961806,1969723,1676776,1911745,2209996,1393178,1407284,1558605,1679502,3324345,3889513,4408265,4713127,865903,5782873,5105180,2593792,586602,1586003,4460877,2250163,201254,5625334,5727460,6337613,5841224,5870377,153865,5772760,383594,266524,3862136,324203,434402,6083115,1685044);
		//$uids[8] = array(3898919,896537,25806,4081754,1266168,3775668,3072233,1057588,1437462,1435254,4019721,6047228,513872,3423326,2013590,1912182,6439342,2074910,3183190,2814384,3917963,3479910,3682600,2666380,5841661,2271467,2278204,5169392,2493015,2178221,2559356,2600197,3665085,3363189,3449984,3104658,3144886,5563360,31109,3156904,3164132,5391098,1141594,3254913,1765602,5310707,3370330,450595,55585,5451093,3436675,2402831,4039543,3701883,4969018,3752098,5427761,5628538,3981829,4031116,4442541,378677,2406627,5186962,4317004,4299928,1657138,6491223,4613024,4649157,160942,762252,2527764,4750437,4751711,4763763,4762690,4286154,1588230,5145560,1858626,3584862,3563546,243705,5321579,5495323,5705104,5545386,456087,235166,5759589,5811445,5840295,5875100,5905154,4485753,3736832,5967220,2049144,2971076,4146763,4091387,3149157,654472,271716,1537963,597779,6127487,352830,147271,399803,5271619,6062856,732413,1378540,747784,808130,956111,2324561,4285143,4931612,5905341,4615410,3240799,1305872,1454972,1410642,6297454,571652,3511192,1323813,1203159,6428295,4901884,793888,4893738,3867330,1927954,3270810,2311877,420824,4880745,2131685,5801223,2925697,2233716,2157071,1383176,4917841,5455087,5063408,3591426,505882,6372942,267871,2538937,2113303,2746707,2603621,1084417,272644,2026432,5015382,6313359,4478071,3949884,5493980,1258235,1175430,2886159,3025377,4326914,4814961,5585972,4036988,4451030,3271090,2567403,989983,1276724,6428973,1183439,520895,705083,3439245,5204684,22635,2638488,4361407,3446998,894289,3513982,1146607,1016443,1188171,3789849,2980356,689359,5514468,4117927,1699922,1101072,5913298,4312163,394821,5064712,5960884,707712,6199018,5487238,5587538,20377,2053393,226901,3950021,5813639,685406,4956047,3926753,6626492,6588916,5446276,63889,1472512,4952459,3435360,2917799,747588,5646299,544930,766585,952351,1043206,1098370,1757553,3429868,4082242,5248554,6540818,849369,5221928,5002423,1519948,1235201,2755177,3683627,2002406,1994775,2010467,821514,3208342,47885,519867,6600390,2354585,2584971,468399,6668993,1229490,680365,6536018,1078839,692983,3466696,4469398,3482795,4359967,3174341,2016985,3703354,2995256,1213774,1392350,4069095,4268526,4611791,6600408,4593380,5148427,1405808,1286307,1396262,4808509,3738031,1587697,855217,3770773,6347581,3860225,4081609,3058272,3891866,3903994,1887706,5589064,3258056,85737,6411195,4164491,4170381,4179192,4040444,802927,4852419,4153367,1278358,4043878,2520460,4370719,1809629,4988687,6475975,4486859,4502838,4476897,4582415,2242817,636630,2910148,2635225,1232234,3642050,1575194,687253,6679936,1184689,1642080,2708103,2308395,507417,5117441,4923646,791180,4845821,2558635,5143178,5917929,5228233,937406,1038742,5744139,5307343,5311097,3962250,5371359,2471148,1240194,923572,6382229,6171299,3146223,6114574,161784,1560596,4383913);
		//$uids[9] = array(5351083,4848601,3855055,945179,1892718,6152497,6037974,84075,3216426,1385993,878600,451874,697503,409889,996917,3875126,61600,2912228,6067211,4865702,5543079,858800,4441154,584094,3718483,2825958,5793782,3765902,304873,2626126,403426,537686,3575429,6366862,2165797,6336890,759435,4810591,3821772,5703485,5640444,4568587,3840332,5198305,128906,2667177,1481678,1953076,5102820,5472688,3213944,4760802,3387761,2736241,4984573,3702880,544316,4655756,568484,5898579,3654485,4949626,1223548,5123129,4360610,5682557,612105,2551061,1960643,2726482,96760,449637,5710358,4946173,541753,3909339,3270264,45555,6304842,622003,2652655,4087670,530449,2154502,51342,3341898,673835,6217155,4901619,2426693,6083289,3737511,1253196,1328626,1457706,3933104,4158478,2801452,2887195,2943323,1345410,4029427,2974312,4539740,5574404,5170704,6386991,2360890,1127224,2212485,5207956,1337284,1827062,5594158,1255606,3383967,3403125,3379799,6421384,3231114,3510927,895223,3575309,3590803,6338992,1845662,3788847,1973271,3850895,6618230,2780154,4080544,4123956,584699,2681613,4669452,4447100,4485308,4537256,3203856,5060668,3590814,5237326,4696638,910996,5256598,37601,1119740,4645386,5945415,5018020,5012495,5347603,5034535,4215912,5152651,5714965,4845772,5264133,1629178,6634193,5168373,5626097,39945,3593970,917880,5748700,406421,4443922,3777621,1441900,2635325,1796876,1775821,84798,5987587,2182419,5523716,5503023,345978,2720882,4016442,502444,636621,345860,3054467,32894,927422,953123,6496921,1260633,2463223,1155078,3524488,5379068,5360570,40483,1604980,1707400,1832225,2033371,2122089,5895802,6089037,2242351,898643,2335249,2617706,2626086,591157,1534057,2942205,4743916,4008422,1505728,4648225,3307914,3954508,3909331,3051302,5625639,5441574,3455848,4903506,4223883,5382376,3537340,5891008,3819020,3596435,4625746,11354,1234405,2201443,4513955,4643128,2389149,5910179,4571980,4838146,4733108,4741006,5368299,4972808,5030176,325444,5082272,4122155,5946651,5309321,6510299,5339248,6331658,4495921,1170804,2753230,5427236,5603731,5603112,1029866,3334543,3399783,301226,5242403,1116356,2987506,558907,1012351,5309382,645432,6588447,2973208,5838938,962981,5048751,1868017,1480731,3023986,1091308,185383,6546440,3864853,2086310,249564,2175107,4449280,2401441,1565849,2121619,1657264,849971,1013906,1013906,5723343,1817623,1098839,1763696,2421317,2158851,1377291,2033674,3291150,2597078,2360226,5878974,2363364,3440686,6041859,2391773,2628867,2437053,47001,2619703,2723851,2722346,2756824,2695898,2788971,2802153,2959060,6389242,493713,2935556,2941307,3010797,2955574,6413680,2982839,3015910,3294653,3329432,3400976,5960315,3508876,3907600,3552894,4124328,3582410,3580950,6407871,4453905,977314,6092531,545667,3870736,2333352,595170,5240551,4645903,5227259,4677248,4471833,6026901,6628095,4823575,4637788,1198421,6360241,4801142,4805842,5260269,5037958,6371307,5147284,5141625,6115121,368254,5212940,5281057,5339531,5242088,4706528,6031250,5378913,5376927,5378665,5370070,5377894,485922,5853668,5939672,5952646,5986774,6579990,6387005,703538,391456,367738,484570,518665,619195,1573678);
		//$uids[10] = array(6419676,6416450,5355402,6414843,5567394,6379839,2360222,6445493,6498686,6429502,6443007,6358559,4915277,386569,6436071,6379318,6371049,6372069,6392619,1581194,6395248,6453523,6449323,6472177,6473507,6461483,754253,976064,6400689,6471733,6520620,6099393,6485446,6494317,5249674,6486101,3241924,511018,6587120,6517886,3887748,6505932,162057,6512861,6453848,118991,3428733,734249,6455629,6533450,6450959,6522322,6511870,6515184,6532896,6519160,6522010,6547007,6461489,6549586,5456377,6557306,6556500,6548042,6537894,6633616,959626,6489309,6562921,6553010,6558882,6563292,6572036,6564452,6506988,6505529,6593907,6577132,6507689,6584386,6517128,6511439,6582761,6584242,6583862,6575165,4853530,6579645,6589383,6599631,6595500,6602442,6600112,6599350,6555999,6595000,6591464,6603872,6598534,6597864,6549538,6623862,6600840,6624042,6541449,6627521,6556208,4582755,6627412,6625361,6607285,6637796,812123,6564028,6614244,6629601,6559159,6620363,6634196,6567178,6557779,6567948,6559849,6639516,6609565,6559909,6625310,6562279,6637712,6629194,6634262,6636241,6637342,6617824,6623964,6640911,6655357,6632860,6633280,6629124,6640662,6641252,6647756,3601587,6635233,3715674,6626745,6658497,6656797,6655726,6585008,6644952,6632745,6647681,6646462,6653861,6664887,6640560,6639774,6653871,6645413,6580139,6586069,6640715,6673277,6592569,6659331,6598738,1927086,6663122,6595159,6659782,6664492,6665632,6651645,6686537,6677346,6669832,3441169,6691397,6611708,6682706,6619338,6689507,6619078,6675422,6667000,6681272,6681466,6612779,6687991,6693476,6675714,6679130,6621199,6684430,4085910,124469,4608039,3753389,1180296,12658,2927414,974166,4394447,4263155,158034,2961079,5875845,883754,2094185,4601804,1607188,715970,3643746,4255548,835803,5421674,3757007,2075885,1480912,4887465,1822429,3838119,2742796,3136648,3590072,4717381,5846632,781267,5683065,5847195,107411,4606093,3951832,2585760,22998,4518835,514032,4485431,2105850,2843124,6564450,152147,41085,6057221,2766468,716116,2436691,1322218,3118457,1952886,4351022,3939502,3936173,5605425,4477635,4570464,3609083,4238108,4332973,2937312,3869890,6640961,3196010,3042765,169218,16324,2807164,14051,4120669,5440913,3620537,5366899,42344,4784666,215595,2941781,4222038,2947816,761844,5288411,2674596,857835,4963703,4212935,2346239,2333688);
		//$uids[11] = array(652296,721695,6076473,1203171,940893,3770874,5849578,4178769,1154852,1155822,1113082,1304356,1135399,3936758,1211116,1240868,2691921,70126,198702,746714,1646994,1660898,2341452,851502,2042537,1743461,6520857,1856151,2042177,2030612,2012669,2105806,2101482,6161149,2179719,543467,2062842,2078900,2065332,2070762,2067148,2170693,2096312,2070365,2214426,2085318,2088959,2297801,4654950,2271199,2191291,2131750,2190965,2236989,2262469,2245330,2258551,6624610,2269116,2234252,2288301,2288921,2272102,2297624,5719490,2375897,2277163,2361031,2372656,2372053,2426197,463059,2378892,2682426,4981596,2658380,2700741,2709923,2697224,2679979,2692992,2739526,2990492,6047805,2885986,2905441,2824389,2922517,4313717,2889659,2939883,3558148,6518879,4882047,3389617,3377419,3486568,3416463,3430866,4064324,6510404,3459602,3454023,3472058,4826765,6385904,3541040,6061924,3559633,3579441,3546258,3926039,3572330,3614166,5423112,5764336,4490860,5980139,3936420,6531147,5977551,4098056,3738810,6644532,6358462,4139001,4705925,6593577,4176511,570670,6455919,4260952,4273711,4795785,4623649,4668659,4738509,2248491,4819903,5061711,5247704,6152861,2035740,5351599,5788694,5410760,2743302,4539294,6148206,5768525,6494910,1210709,6153311,5869090,4797858,5933507,4787778,6104958,2963276,6021756,6428936,3258631,6166128,6086383,3926174,1575111,3131305,3054994,1244406,6016189,6046324,6042984,6162033,6550095,6079559,5432261,5102442,6066720,6064845,1915682,2115441,6095521,6079205,6071549,6040529,6635504,6122902,6109610,1891464,6458091,2207017,4502165,6139871,6151056,6135884,6570349,6166907,6085149,6501486,6665972,6241821,6302942,6285846,6400866,6380265,6565800,6333939,6420074,6406531,5975110,6396094,6408433,6354468,6446347,6371738,6620343,4454908,1721030,4581998,1925793,3102354,1925813,6089417,1725388,2004966,4633039,5576059,4941561,5773830,2370852,4948122,1753082,310786,1619960,4222326,523597,6011586,869896,1486982,4368988,1250645,1376560,1802002,3062730,4027943,40895,3226854,1337678,4465509,1854440,4840222,77113,3727483,164092,4468105,1503807,269324,2848231,31469,4842890,1159728,4442206,4942578,2549318,2999749,5820512,5118462,3959023,3752177,3174498,1712019,4372633,2659390,5178053,3914979,3028219,1349369,3258365,1770387,5430304,1614768,2278538,567896,5937871,1512299,4628656,5858751,2241535,856907,5247609,4860291,2357581,635608,818668,1494550,5526928,2873408,1886810,1082991,3457767,2047579,1729598,5015924,417756,4399694,1925006,375708,1199686,2085893,1890030,1232854,3913907,3111771,2719457,1036379,5805378,4017462,1520987,723248,6222202,826805,4358670,712321,5123882,2605071,3851553,3601338,1787008,3134615,6458742,445088,2023427,4447854,3606773);
		$uids[11] = array(1014,1024);
		
		$nowTime = time();
		
		foreach ($uids[$tid] as $uid) {
			Hapyfish2_Island_Bll_Gold::add($uid, array('gold' => 20, 'type' => 0));
			Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 26441, 7);				
			Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 86241, 7);
			
			$feed = '恭喜你获得七夕论坛奖励<font color="#379636">20宝石 船只加速卡IIIx7 宝箱钥匙x7</font>';
			
        	$minifeed = array(
						'uid' => $uid,
						'template_id' => 0,
						'actor' => $uid,
						'target' => $uid,
						'title' => array('title' => $feed),
						'type' => 3,
						'create_time' => $nowTime
					);

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}
		
		echo count($uids[$tid]);
		exit;
	}
	
	//七夕称号
	public function addqixititleAction()
	{
		$uids = array();
		$titleId = 99;
		
		$nowTime = time();
		
		$feed = '恭喜你获得七夕称号<font color="#FF0000"> 鹊桥之恋</font>';
		
		foreach ($uids as $uid) {
			Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
			
        	$minifeed = array(
						'uid' => $uid,
						'template_id' => 0,
						'actor' => $uid,
						'target' => $uid,
						'title' => array('title' => $feed),
						'type' => 3,
						'create_time' => $nowTime
					);

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}
		
		echo 'OK';
		exit;
	}
	
	//发论坛管理员工资
	public function tosendadminAction()
	{
		$tid = $this->_request->getParam('tid');
		
		$puids[40] = array(109674535,25808524,326023787,415215591,352917621,45557840,83556541,423576495,536711767,28066357,244005521,134422853,199931634,389352572,653702160,284983684,298398067,135686994,64009543,654220320,42646754,22727255,36561193,354629598,595090917,291100511,353087401,307102827,162268171,170595900,506788399,75463588,50186572,203947267,92771857,353087401,307102827,162268171,170595900,506788399,75463588,50186572,203947267,92771857);
		$puids[60] = array(164280451,414512054,427297578,42344046);
		$puids[80] = array();
		$puids[120] = array();
		$puids[160] = array();
		
		$nowTime = time();
		$feed = '恭喜你获得论坛管理员工资<font color="#379636">' . $tid . '</font>宝石';
	
		foreach ($puids[$tid] as $puid) {
			$puidMS = Hapyfish2_Platform_Bll_UidMap::getUser($puid);
		
			$goldInfo = array('gold' => $tid, 'type' => 0, 'time' => $nowTime);
			
			$ok = Hapyfish2_Island_Bll_Gold::add($puidMS['uid'], $goldInfo);
			
			if($ok) {
	        	$minifeed = array(
							'uid' => $puidMS['uid'],
							'template_id' => 0,
							'actor' => $puidMS['uid'],
							'target' => $puidMS['uid'],
							'title' => array('title' => $feed),
							'type' => 3,
							'create_time' => $nowTime
						);

				Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
				
				echo 'puid : ' . $puid . ' ***  uid : ' . $puidMS['uid'];
			} else {
				echo 'not puid : ' . $puid;
			}
		}
		
		exit;
	}
	
	//获得单人团购信息
	public function getoneteambuyAction()
	{
		$uid = $this->_request->getParam('uid');
		
    	$keys = 'i:e:teambuy:buygood:' . $uid;
		$caches = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $caches->get($keys);
		
		if($data) {
			print_r('<pre>');print_r($data);print_r('</pre>');
		} else {
			echo 'NULL';
		}
		
		$dalTeamBuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$dats = $dalTeamBuy->getOneUser($uid);
		
		if($dats) {
			print_r('<pre>');print_r($dats);print_r('</pre>');
		} else {
			echo 'DONT';
		}
		
		exit;
	}
	
	public function updateteambuyAction()
	{
		$uid = $this->_request->getParam('uid');
		
    	$keys = 'i:e:teambuy:buygood:' . $uid;
		$caches = Hapyfish2_Cache_Factory::getMC($uid);
		$caches->delete($keys);
		
		echo $uid;
		exit;
	}
	
	//充值送测试
	public function testpayAction()
	{
		$uid = $this->_request->getParam('uid');
		$amount = $this->_request->getParam('amount');
		
		$ok = Hapyfish2_Island_Bll_Payment::chargeGift($uid, $amount);
		
		echo $ok ? 'OK' : 'NOT';
		exit;
	}

	//一元店充值信息
	public function testpayoneAction()
	{
		$uid = $this->_request->getParam('uid');
		
		Hapyfish2_Island_Event_Bll_OneGoldShop::setPayInfo($uid);
		
		echo 'OK';
		exit;
	}
	
	//一元店清理用户本期领取奖励记录
	public function clearuseroneAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$key = 'i:u:oneshop:gift:get_status:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->delete($key);
		
		echo 'OK';
		exit;
	}

/**一元店物品显示不出时需清理的缓存*/
	public function clearallAction()
	{
		$key = 'i:e:onegold:all';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
		echo "ok";
		exit;
	}
	
	//一元店清理本期礼物缓存
	public function clearonegoldAction()
	{
		$key = 'i:e:oneshop:gift';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
		echo 'OK';
		exit;
	}
	
	public function clearonegolduserAction()
	{
		$key = 'i:e:oneshop:gift:newtime';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->set($key, null);
		
		echo 'OK';
		exit;
	}
	
	public function clearnumAction()
	{
		$keyCid = 'i:e:oneshop:gift:hasnum';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($keyCid);
		
		echo 'OK';
		exit;
	}
/**一元店物品显示不出时需清理的缓存*/	
	
	//团购玩家不显示icon
	public function clearoneteambuyAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$dalTeamBuy = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
		$dalTeamBuy->clearOneUser($uid);
		
    	$keys = 'i:e:teambuy:buygood:' . $uid;
		$caches = Hapyfish2_Cache_Factory::getMC($uid);
		$caches->delete($keys);
		
		echo $uid;
		exit;
	}
	
	public function checknewpayAction()
	{
		$tid = $this->_request->getParam('tid');
		
		$db = Hapyfish2_Island_Dal_PaymentLog::getDefaultInstance();
		
		$array[1] = array();
		$array[2] = array();
		$array[3] = array();
		$array[4] = array();
		$array[5] = array();
		$array[6] = array();
		$array[7] = array();
		$array[8] = array();
		$array[9] = array();
		$array[10] = array();
	
		$newPay = array();
		foreach ($array[$tid] as $uid) {
			$result = 0;
			$result = $db->checkNewPayUser($uid);
			
			if ($result == 0) {
				$newPay[] = $uid;
			}
		}
		
		print_r('<pre>');print_r($newPay);print_r('</pre>');
		exit;
	
	}
	
	public function checknewpaylevelAction()
	{
		$itd = $this->_request->getParam('tid');
		
		$uids[1] = array();
		$uids[2] = array();
		$uids[3] = array();
		$uids[4] = array();
		
		$new = array();
		foreach ($uids[$itd] as $uid) {
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$new[$uid] = $userLevelInfo['level'];
		}
		
		print_r('<pre>');print_r($new);print_r('</pre>');
		exit;
	}
	//捕鱼 清除鱼信息列表
	public function clearfishAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$key = 'i:e:l:p:flist:' . 1 ;
		$cache->delete($key);
		$key = 'i:e:l:p:flist:' . 2 ;
		$cache->delete($key);
		$key = 'i:e:l:p:flist:' . 3 ;
		$cache->delete($key);
		$key = 'i:e:l:p:flist:' . 4 ;
		$cache->delete($key);
		$key = 'i:e:l:p:flist:' . 5 ;
		$cache->delete($key);							
		echo 'ok';
		exit;		
	}
	
	public function clearfishallAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$key = 'i:e:flistall';
		$cache->delete($key);							
		echo 'ok';
		exit;		
	}	
	//捕鱼 清除鱼信息详细
	public function clearfishinfoAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		for($i=1;$i<=40;$i++) {
		$key = 'i:e:l:p:finfo:' . $i ;
		$cache->delete($key);	
		}					
		echo 'ok';
		exit;		
	}
	//捕鱼 清除商品信息
	public function clearproductAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$key = 'i:e:tb:pd';
		$cache->delete($key);						
		echo 'ok';
		exit;		
	}
	//捕鱼 清除领域信息
	public function cleardomainAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$key = 'i:e:l:p:fdomain';
		$cache->delete($key);						
		echo 'ok';
		exit;		
	}
	public function clearproductproAction()
	{
		$productid = $this->_request->getParam('pid');
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		for($i=1;$i<=5;$i++) {
			$key = 'i:e:tb:pd:prob:l:pid:' . $i . ':' . $productid;
			$cache->delete($key);
		}
		echo 'ok';
		exit;	
	}
	//捕鱼达人称号测试
	public function updateusertitleAction()
	{
		$flag = $this->_request->getParam('flag');
		$uids = array(6497758);
		$titleId = 100;
		
		$nowTime = time();
		if($flag==1) {
			$feed = '恭喜你获得称号<font color="#FF0000"> 捕鱼达人</font>';
		}elseif($flag==2) {
			$feed = '取消称号<font color="#FF0000"> 捕鱼达人</font>';
		}
		foreach ($uids as $uid) {
			if($flag==1) {
				Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
			}elseif($flag==2) {
				Hapyfish2_Island_HFC_User::delTitle($uid, $titleId, true);
			}
        	$minifeed = array(
						'uid' => $uid,
						'template_id' => 0,
						'actor' => $uid,
						'target' => $uid,
						'title' => array('title' => $feed),
						'type' => 3,
						'create_time' => $nowTime
					);

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}
		
		echo 'OK';
		exit;
	}				
	public function checkpriseAction()
	{
		$uid = $this->_request->getParam('uid');
		
        $key = 'i:u:island:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $cache->delete($key);
        
        echo 'OK';
        exit;
	}
	
	public function getnextgiftAction()
	{		
		$key = 'island:stepgiftlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$list = $localcache->get($key);
        
        print_r('<pre>');print_r($list);print_r('</pre>');
        
        exit;
	}
	
	public function resetboxinfoAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$keyBox = 'i:e:oneshop:gift:bigbox:' . $uid;
		$cacheBox = Hapyfish2_Cache_Factory::getMC($uid);
		$cacheBox->delete($keyBox);
		
		echo 'OK';
		exit;
	}
	
	public function testamoyAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$key = 'i:e:amoygold:gift' . $uid;
		$cacheAmoy = Hapyfish2_Cache_Factory::getMC($uid);
		$cacheAmoy->delete($key);
		
		echo 'OK';
		exit;
	}
	
	public function clearamoynumAction()
	{
		$uid = $this->_request->getParam('uid');
		
		$keyNum = 'i:e:amoygold:num:' . $uid;
		$cacheAmoy = Hapyfish2_Cache_Factory::getMC($uid);
		$cacheAmoy->delete($keyNum);
		
		echo 'OK';
		exit;
	}
	
	public function cleartcoinAction()
	{
		$uid = $this->_request->getParam('uid');
		$num = $this->_request->getParam('num');
		
		$keyAmoy = 'i:e:amoygold' . $uid;
		$cacheAmoy = Hapyfish2_Cache_Factory::getMC($uid);
		$cacheAmoy->set($keyAmoy, $num);
		
		echo 'OK';
		exit;
	}
	
	public function teambuytestAction()
	{
		$uid = $this->_request->getParam('uid');
        $state = $this->_request->getParam('state');
        $new = $this->_request->getParam('new', 0);
		
        if ( $new == 1 ) {
		    try {
		   	   $dalDB = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();
	           $stateTest = $dalDB->getJoinTeamBuyInfo($uid);
	           if ( $stateTest != $state ) {
	                $tempUser = $dalDB->selectTeamBuyUserTemp($uid);
	                if ( !$tempUser ) {
	                    $dalDB->insertTeamBuyUserTemp($uid, $state);
	                }
	           }
	        } catch (Exception $e) {
	        }
        }
                
        $result = Hapyfish2_Island_Event_Bll_TeamBuy::buyGoodsTest($uid);

        header("Cache-Control: no-store, no-cache, must-revalidate");
        echo json_encode($result);
        exit;
	}
	
	public function decusergoldAction()
	{
		$uid = $this->_request->getParam('uid');
		$decGold = $this->_request->getParam('gold');
		
		$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
		$ok = $dalUser->decGold($uid, $decGold);
		
		Hapyfish2_Island_HFC_User::reloadUserGold($uid);
		
		$result = $ok ? 'OK' : 'not';
		echo $result;
		exit;
	}
	
	public function resetboxonegoldAction()
	{
		Hapyfish2_Island_Event_Bll_OneGoldShop::resetBoxInfo();
		
		echo 'OK';
		exit;
	}
	
	public function clearconfigAction()
	{
		$key = 'i:award:config';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
		echo "OK";
		exit;
	}
	public function clearrobotAction()
	{
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		for($i=1;$i<=503;$i++) {
			$key = 'i:u:s:r:i:'.$i;	
			$cache->delete($key);
		}
		echo 'OK';
		exit;
	}
	public function clearuserrobotAction()
	{
		for($i=0;$i<=5000;$i++) {
			$key = 'i:u:robot:f:'.$i;
			$cache = Hapyfish2_Cache_Factory::getHFC($i);
	        $cache->delete($key);
		}
		echo 'OK';
		exit;
	}
	public function clearuseronerobotAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:robot:f:'.$uid;
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
	    $cache->delete($key);
		echo 'OK';
		exit;
	}
	public function getuserstarfishAction()
	{
		$uid = $this->_request->getParam('uid');
        $key = 'i:u:starfish:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$starfish = $cache->get($key);
        print_r($starfish);
        exit;		
	}
	public function getfriendsAction()
	{
		$uid = $this->_request->getParam('uid');
		$data = Hapyfish2_Platform_Bll_Friend::getFriend($uid);
		print_r($data);
		echo 'OK';
		exit;
	}
	public function getplatformfriendsAction()
	{
		$uid = $this->_request->getParam('uid');
		$info = $this->vailid();
		print_r($info);
		$session_key = $info['session_key'];
		$rest = Ming_Rest::getInstance($session_key);
		$rest->setUser($uid);
		$fids = $rest->ming_getAppFriendIds();
		print_r($fids);
		echo 'OK';
		exit;
	}

	//清除元旦概率表缓存
	public function clearnewdaysitemsAction()
	{
		$key = 'ev:newdays:items';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
		echo 'OK';
		exit;
	}
	public function getislandtipAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:isltp:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		print_r($data);
		echo 'OK';
		exit;
	}	
	public function clearinvfAction()
	{
		$uid = $this->_request->getParam('uid');
		$key = 'i:u:e:invf_gold2:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->delete($key);
		echo 'OK';
		exit;
	}

	public function addinvitelogAction()
	{
		$inviteUid= $this->_request->getParam('uida');
		$uid= $this->_request->getParam('uidb');
		Hapyfish2_Island_Bll_Invite::add($inviteUid, $uid);
		info_log('inviter:'.$inviteUid.',uid:'.$uid, 'invitejoin');
		echo 'OK';
		exit;
	}
	public function addplantAction()
	{
		$uid = $this->_request->getParam('uid');
		$itemId = $this->_request->getParam('cid');
		$num = $this->_request->getParam('num');
		$bllCompensation = new Hapyfish2_Island_Bll_Compensation();
		$bllCompensation->setItem($itemId, $num);
		$bllCompensation->sendOne($uid, $itemId);	
		echo 'OK';
		exit;	
	}
	public function updaterankAction()
	{
		$uid = $this->_request->getParam('uid');
		Hapyfish2_Island_Bll_Rank::updateRankWeek();	
		echo 'OK';
		exit;	
	}		
}