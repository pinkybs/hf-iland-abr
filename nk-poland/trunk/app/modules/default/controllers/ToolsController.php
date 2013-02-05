<?php

class ToolsController extends Zend_Controller_Action
{
	function vaild()
	{

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

	public function addcoinAction()
	{
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

	public function addstarfishAction()
	{
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

	public function clearcasinoawardAction()
	{
		$key = 'island:caisnoawardtype';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);

		echo 'OK';
		exit;
	}

	//修复等级成就
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

	//清除fans奖励
	public function clearfansAction()
	{
		$uid = $this->_request->getParam('uid');

		$key = 'permission:gift:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, false);

		$db = Hapyfish2_Island_Dal_Permissiongift::getDefaultInstance();
		$db->deleteHas($uid);

		echo $uid;
		exit;
	}

	//登陆翻牌缓存读取
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

	public function teambuyAction()
	{
		$uid = $this->_request->getParam('uid');

		$mkey = 'i:e:teambuy:buygood:' . $uid;
		$mcache = Hapyfish2_Cache_Factory::getMC($uid);
		$mcache->delete($mkey);

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


	public function addmanyuserAction()
	{
	    $startid = $this->_request->getParam('start', 10000001);
	    $num = $this->_request->getParam('end', 10000010);
	    $uids = array();
	    for($i = $startid; $i <= $num; $i++) {
			$id = $i;
			$puid = $id;
			try {
				$uidInfo = Hapyfish2_Platform_Cache_UidMap::newUser($puid);
				$uid = $uidInfo['uid'];
				if (!$uid) {
					continue;
				}
				$user = array(
					'uid' => $uid,
					'puid' => $puid,
					'name' => 'pl_test_' . $id,
					'gender' => rand(0,1),
					'figureurl' => 'http://tp1.sinaimg.cn/1/50/0/0',
				    'create_time' => time()
				);

				Hapyfish2_Platform_Bll_User::addUser($user);
                $fids = array(1016);
				Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids);
				Hapyfish2_Island_Bll_User::joinUser($uid);
				$uids[] = $uid;
                echo 'test acount created:'.$uid;
                echo '<br/>';
			} catch (Exception $e) {
				err_log($e->getMessage());
			}
		}

		Hapyfish2_Platform_Bll_Friend::addFriend(1016, $uids);
		exit;
	}


    public function addfriendAction()
	{
	    $uid = $this->_request->getParam('uid');
	    $fids = $this->_request->getParam('fids');

	    $fids = explode(',', $fids);
		Hapyfish2_Platform_Bll_Friend::addFriend($uid, $fids, true);
		//Hapyfish2_Platform_Bll_Friend::addFriend($fid, array($uid), true);
		echo 'ok';
		exit;
	}
	
	//清除连续登陆缓存
	public function clearconfigAction()
	{
		$key = 'i:award:config';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
		
		echo "OK";
		exit;
	}	
}