<?php

class Hapyfish2_Island_Cache_BasicInfo
{
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}

	public static function getFeedTemplate()
	{
		$key = 'island:feedtemplate';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$tpl = $localcache->get($key);
		if (!$tpl) {
			$cache = self::getBasicMC();
			$tpl = $cache->get($key);
			if (!$tpl) {
				$tpl = self::loadFeedTemplate();
			}
			if ($tpl) {
				$localcache->set($key, $tpl);
			}
		}

		return $tpl;
	}

	public static function getFeedTemplateTitle($template_id)
	{
		$tpl = self::getFeedTemplate();
		if ($tpl && isset($tpl[$template_id])) {
			return $tpl[$template_id];
		}

		return null;
	}

	public static function loadFeedTemplate()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$tpl = $db->getFeedTemplate();
		if ($tpl) {
			$key = 'island:feedtemplate';
			$cache = self::getBasicMC();
			$cache->set($key, $tpl);
		}

		return $tpl;
	}

	public static function getShipList()
	{
		$key = 'island:shiplist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadShipList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getShipInfo($id)
	{
		$shipList = self::getShipList();
		if (isset($shipList[$id])) {
			return $shipList[$id];
		}

		return null;
	}

	public static function loadShipList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getShipList();
		if ($list) {
			$key = 'island:shiplist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getBuildingList()
	{
		$key = 'island:buildinglist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadBuildingList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getBuildingInfo($id)
	{
		$buildingList = self::getBuildingList();
		if (isset($buildingList[$id])) {
			return $buildingList[$id];
		}

		return null;
	}

	public static function loadBuildingList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getBuildingList();
		if ($list) {
			$key = 'island:buildinglist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getPlantList()
	{
		$key = 'island:plantlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadPlantList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getPlantInfo($id)
	{
		$plantList = self::getPlantList();
		if (isset($plantList[$id])) {
			return $plantList[$id];
		}

		return null;
	}

	public static function getDiffPlantList($level, $cids)
	{
		if (empty($cids)) {
			$all = true;
		} else {
			$all = false;
		}
		$list = array();
		$plantList = self::getPlantList();
		foreach ($plantList as $id => $item) {
			if ($item['can_buy'] == 1 && $item['price'] > 0 && $item['need_level'] < $level) {
				if (!$all) {
					if (isset($cids[$id])) {
						continue;
					}
				}
				$list[$id] = array(
					'item_id' => $item['item_id'],
					'level' => $item['level'],
					'cid' => $item['cid'],
					'itemId' => 0,
					'num' => 0,
					'eventId' => 0
				);
			}
		}

		return $list;
	}

	public static function getPlantListByLevel($level)
	{
		$plantList = self::getPlantList();
		$list = array();
		foreach ($plantList as $id => $item) {
			if ($item['can_buy'] == 1 && $item['price'] > 0 && $item['need_level'] == $level) {
				$list[$id] = array(
					'item_id' => $item['item_id'],
					'level' => $item['level'],
					'cid' => $item['cid'],
					'itemId' => 0,
					'num' => 0,
					'eventId' => 0
				);
			}
		}

		return $list;
	}

	public static function loadPlantList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getPlantList();
		if ($list) {
			$key = 'island:plantlist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getBackgroundList()
	{
		$key = 'island:backgroundlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadBackgroundList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getBackgoundInfo($id)
	{
		$bgList = self::getBackgroundList();
		if (isset($bgList[$id])) {
			return $bgList[$id];
		}

		return null;
	}

	public static function loadBackgroundList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getBackgroundList();
		if ($list) {
			$key = 'island:backgroundlist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getCardList()
	{
		$key = 'island:cardlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadCardList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getCardInfo($id)
	{
		$cardList = self::getCardList();
		if (isset($cardList[$id])) {
			return $cardList[$id];
		}

		return null;
	}

	public static function loadCardList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getCardList();
		if ($list) {
			$key = 'island:cardlist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getDockList()
	{
		$key = 'island:docklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadDockList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getDockInfo($id)
	{
		$dockList = self::getDockList();
		if (isset($dockList[$id])) {
			return $dockList[$id];
		}

		return null;
	}

	public static function loadDockList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getDockList();
		if ($list) {
			$key = 'island:docklist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getUserLevelList()
	{
		$key = 'island:userlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadUserLevelList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getUserLevelExp($id)
	{
		$userLevelList = self::getUserLevelList();
		if (isset($userLevelList[$id])) {
			return $userLevelList[$id];
		}

		return 999999999;
	}

	public static function loadUserLevelList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getUserLevelList();
		if ($list) {
			$key = 'island:userlevellist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getIslandLevelList()
	{
		$key = 'island:islandlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadIslandLevelList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getIslandLevelInfo($id)
	{
		$islandLevelList = self::getIslandLevelList();
		if (isset($islandLevelList[$id])) {
			return $islandLevelList[$id];
		}

		return null;
	}

	public static function getIslandLevelInfoByUserLevel($userLevel, $islandId = 1)
	{
		$islandLevelList = self::getIslandLevelList();
		$islandLevel = 0;
		switch ( $islandId ) {
			case 1 :
				$filed = 'need_user_level';
				break;
			case 2 :
				$filed = 'need_user_level_2';
				break;
			case 3 :
				$filed = 'need_user_level_3';
				break;
			case 4 :
				$filed = 'need_user_level_4';
				break;
		}
		foreach ($islandLevelList as $item) {
			if ($item[$filed] <= $userLevel) {
				$islandLevel = $item['level'];
			} else {
				break;
			}
		}
		
		return $islandLevel;
	}
		
	public static function loadIslandLevelList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getIslandLevelList();
		if ($list) {
			$key = 'island:islandlevellist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getGiftLevelList()
	{
		$key = 'island:giftlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadGiftLevelList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getGiftByUserLevel($level)
	{
		$list = self::getGiftLevelList();
		if (isset($list[$level])) {
			return $list[$level];
		}

		return null;
	}

	public static function loadGiftLevelList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getGiftLevelList();
		if ($list) {
			$key = 'island:giftlevellist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getAchievementTaskList()
	{
		$key = 'island:achievementtasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadAchievementTaskList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getAchievementTaskInfo($id)
	{
		$taskInfo = self::getAchievementTaskList();
		if (isset($taskInfo[$id])) {
			return $taskInfo[$id];
		}

		return null;
	}

	public static function getAchievementTaskInfoByTitle($title)
	{
		$key = 'island:achievementtasktitle:' . $title;
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$data = $localcache->get($key);
		if (!$data) {
			$cache = self::getBasicMC();
			$data = $cache->get($key);
			if (!$data) {
				$data = self::loadAchievementTaskByTitle($title);
			}
			if ($data) {
				$localcache->set($key, $data);
			}
		}

		return $data;
	}

	public static function loadAchievementTaskByTitle($title)
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getAchievementTaskByTitle($title);
		if ($list) {
			$key = 'island:achievementtasktitle:' . $title;
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function loadAchievementTaskList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getAchievementTaskList();
		if ($list) {
			$key = 'island:achievementtasklist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getBuildTaskList()
	{
		$key = 'island:buildtasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadBuildTaskList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getBuildTaskInfo($id)
	{
		$taskInfo = self::getBuildTaskList();
		if (isset($taskInfo[$id])) {
			return $taskInfo[$id];
		}

		return null;
	}

	public static function loadBuildTaskList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getBuildTaskList();
		if ($list) {
			$key = 'island:buildtasklist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getDailyTaskList()
	{
		$key = 'island:dailytasklist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadDailyTaskList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getDailyTaskInfo($id)
	{
		$taskInfo = self::getDailyTaskList();
		if (isset($taskInfo[$id])) {
			return $taskInfo[$id];
		}

		return null;
	}

	public static function loadDailyTaskList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getDailyTaskList();
		if ($list) {
			$key = 'island:dailytasklist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getShipPraiseList()
	{
		$key = 'island:shippraiselist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadShipPraiseList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getShipPraiseInfo($id)
	{
		$shipPraiseList = self::getShipPraiseList();
		if (isset($shipPraiseList[$id])) {
			return $shipPraiseList[$id];
		}

		return null;
	}

	public static function loadShipPraiseList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getShipPraiseList();
		if ($list) {
			$key = 'island:shippraiselist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getTitleList()
	{
		$key = 'island:titlelist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadTitleList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getTitleName($id)
	{
		$list = self::getTitleList();
		if ($list && isset($list[$id])) {
			return $list[$id];
		}

		return '';
	}

	public static function loadTitleList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getTitleList();
		if ($list) {
			$key = 'island:titlelist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getNoticeList()
	{
		$key = 'island:pubnoticelist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = array();
			}
			$localcache->set($key, $list, false, 900);
		}

		return $list;
	}

	public static function loadNoticeList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getNoticeList();
		if ($list) {
			$main = array();
			$sub = array();
			$pic = array();
			foreach ($list as $item){
				if ($item['position'] == 1) {
					$main[] = $item;
				} else if($value['position'] == 2){
					$sub[] = $item;
            	} else if($value['position'] == 3){
					$pic[] = $item;
				}
			}
            $info = array('main' => $main, 'sub' => $sub, 'pic' => $pic);

			$key = 'island:pubnoticelist';
			$cache = self::getBasicMC();
			$cache->set($key, $info);
		} else {
			$info = array();
		}

		return $info;
	}

	public static function getGiftList()
	{
		$key = 'island:giftlist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadGiftList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getGiftInfo($gid)
	{
		$giftList = self::getGiftList();
		if ($giftList) {
			foreach ($giftList as $gift) {
				if ($gift['gid'] == $gid) {
					return $gift;
				}
			}
		}
		return null;
	}

	public static function loadGiftList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getGiftList();
		if ($list) {
			$key = 'island:giftlist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}

	public static function getActLoginInterval()
	{
		$key = 'island:actlogininterval';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$interval = $localcache->get($key);
		if (!$interval) {
			$cache = self::getBasicMC();
			$interval = $cache->get($key);
			if (!$interval) {
				//60 seconds
				$interval = 60;
			}
			$localcache->set($key, $interval, false);
		}

		return $interval;
	}

	public static function getEZineStatus()
	{
		$key = 'island:ezinestatus';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$status = $localcache->get($key);
		if (!$status) {
			$cache = self::getBasicMC();
			$status = $cache->get($key);
			if (!$status) {
				$status = array('show' => 0, 'ver' => date('Ymd'));
			}
			$localcache->set($key, $status, false);
		}

		return $status;
	}

	public static function getStepGiftLevelList()
	{
		$key = 'island:stepgiftlevellist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();

		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadStepGiftLevelList();
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}

		return $list;
	}

	public static function getStepGiftByUserLevel($level)
	{
		$list = self::getStepGiftLevelList();
		if (isset($list[$level])) {
			return $list[$level];
		}

		return null;
	}

	public static function loadStepGiftLevelList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$list = $db->getStepGiftLevelList();
		if ($list) {
			$key = 'island:stepgiftlevellist';
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}

		return $list;
	}
    public static function getDetailInfo($cid)
    {
		$itemType = substr($cid, -2, 1);
		$info = array();
		if($itemType == 1){
			$info = self::getBackgoundInfo($cid);
		}
		if($itemType == 2){
		    $info = self::getBuildingInfo($cid);
		}
		if($itemType == 3){
			$info = self::getPlantInfo($cid);
		}
		if($itemType == 4){
			$info = self::getCardInfo($cid);
		}
		return $info;
    }
}