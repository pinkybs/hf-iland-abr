<?php

class Hapyfish2_Island_Bll_BasicInfo
{
	public static function getInitVoData($v = '1.0', $compress = false)
	{
		if (!$compress) {
			return self::restore($v);
		} else {
			return self::restoreCompress($v);
		}
	}
	public static function removeDumpFile($v = '1.0', $compress = false)
	{
	    $file = TEMP_DIR . '/initvo.' . $v . '.cache';
	    if ($compress) {
	        $file .= '.zip';
	    }
	    if (is_file($file)) {
            $rst = @unlink($file);
	    }
	    return $rst;
	}

	public static function dump($v = '1.0', $compress = false)
	{
		$resultInitVo = self::getInitVo();
		$file = TEMP_DIR . '/initvo.' . $v . '.cache';
		$data = json_encode($resultInitVo);
		if ($compress) {
			$data = gzcompress($data, 9);
			$file .= '.zip';
		}

		file_put_contents($file, $data);
		return $data;
	}

	public static function restore($v = '1.0')
	{
		$file = TEMP_DIR . '/initvo.' . $v . '.cache';
		if (is_file($file)) {
			return file_get_contents($file);
		} else {
			return self::dump($v);
		}
	}

	public static function restoreCompress($v = '1.0')
	{
		$file = TEMP_DIR . '/initvo.' . $v . '.cache.zip';
		if (is_file($file)) {
			return file_get_contents($file);
		} else {
			return self::dump($v, true);
		}
	}

	public static function getInitVo()
	{
        $resultInitVo = array();

        $backgroundList = self::getBackgroundList();
        $buildingList = self::getBuildingList();
        $plantList = self::getPlantList();
        $cardList = self::getCardList();
        $levelList = self::getLevelList();
        $islandUpgradeList = self::getIslandUpgradeList();

        //get task list
        $dailyTask = self::getDailyTaskList();
        $buildTask = self::getBuildTaskList();
        $achievementTask = self::getAchievementTaskList();
        $taskList = array_merge($dailyTask, $buildTask, $achievementTask);
        $titleList = self::getTitleList();

        $resultInitVo['itemClass'] = array_merge($cardList, $backgroundList, $buildingList, $plantList);
        $resultInitVo['boatClass'] = self::getBoatClass();
        $resultInitVo['levelClass'] = $levelList;
        $resultInitVo['taskClass'] = $taskList;
        $resultInitVo['titleClass'] = $titleList;
        $resultInitVo['helpExpList'] = array(50, 100, 200, 300, 400, 500, 600);
        $resultInitVo['fourIslandStaticInfo'] = self::getNewIslandVo();
        $resultInitVo['islandUpgradeInfo'] = $islandUpgradeList;

        return $resultInitVo;
	}

	public static function getNewIslandVo()
	{
		require_once(CONFIG_DIR . '/language.php');
		$newIslandVo = array('IslandName1' => LANG_PLATFORM_INDEX_TXT_16,
							 'IslandName2' => LANG_PLATFORM_INDEX_TXT_17,
							 'IslandName3' => LANG_PLATFORM_INDEX_TXT_18,
							 'IslandName4' => LANG_PLATFORM_INDEX_TXT_19,
							 'openIsland2Coin' => 1000000,
							 'openIsland3Coin' => 3000000,
							 'openIsland4Coin' => 6000000,
							 'openIsland2Gem' => 10,
							 'openIsland3Gem' => 30,
							 'openIsland4Gem' => 50,
							 'openIsland2HZGem' => 200,
							 'openIsland3HZGem' => 500,
							 'openIsland4HZGem' => 800,
							 'openIsland2Level' => 15,
							 'openIsland3Level' => 25,
							 'openIsland4Level' => 40);
		return $newIslandVo;
	}

	public static function getBackgroundList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getBackgroundList();
		foreach ($data as $item) {
			$info[] = array(
				'cid' => $item['bgid'],
				'name' => $item['name'],
				'className' => $item['class_name'],
				'price' => $item['price'],
				'priceType' => $item['price_type'],
				'salePrice' => $item['sale_price'],
				'type' => $item['item_type'],
				'needLevel' => $item['need_level'],
				'addPraise' => $item['add_praise'],
				'isNew' => $item['new'],
				'cheapPrice' => $item['cheap_price'],
				'cheapStartTime' => $item['cheap_start_time'],
				'cheapEndTime' => $item['cheap_end_time']
			);
		}

		return $info;
	}

	public static function getBuildingList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getBuildingList();
		foreach ($data as $item) {
			$info[] = array(
				'cid' => $item['cid'],
				'name' => $item['name'],
				'className' => $item['class_name'],
				'price' => $item['price'],
				'priceType' => $item['price_type'],
				'salePrice' => $item['sale_price'],
				'nodes' => $item['nodes'],
				'type' => $item['item_type'],
				'needLevel' => $item['need_level'],
				'addPraise' => $item['add_praise'],
				'isNew' => $item['new'],
				'cheapPrice' => $item['cheap_price'],
				'cheapStartTime' => $item['cheap_start_time'],
				'cheapEndTime' => $item['cheap_end_time']
			);
		}

		return $info;
	}

	public static function getPlantList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
		foreach ($data as $item) {
			$info[] = array(
				'cid' => $item['cid'],
				'name' => $item['name'],
				'className' => $item['class_name'],
				'price' => $item['price'],
				'priceType' => $item['price_type'],
				'salePrice' => $item['sale_price'],
				'nodes' => $item['nodes'],
				'type' => $item['item_type'],
				'needLevel' => $item['need_level'],
				'addPraise' => $item['add_praise'],
				'isNew' => $item['new'],
				'level' => $item['level'],
				'ticket' => $item['ticket'],
				'payTime' => $item['pay_time'],
				'safeTime' => $item['safe_time'],
				'safeCoinNum' => $item['safe_coin_num'],
				'needPraise' => $item['need_praise'],
				'nextCid' => $item['next_level_cid'],
				'cheapPrice' => $item['cheap_price'],
				'cheapStartTime' => $item['cheap_start_time'],
				'cheapEndTime' => $item['cheap_end_time'],
				'actName' => $item['act_name']
			);
		}

		return $info;
	}

	public static function getCardList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getCardList();
		foreach ($data as $item) {
			$info[] = array(
				'cid' => $item['cid'],
				'name' => $item['name'],
				'className' => $item['class_name'],
				'content' => $item['introduce'],
				'price' => $item['price'],
				'priceType' => $item['price_type'],
				'salePrice' => $item['sale_price'],
				'type' => $item['item_type'],
				'needLevel' => $item['need_level'],
				'isNew' => $item['new'],
				'cheapPrice' => $item['cheap_price'],
				'cheapStartTime' => $item['cheap_start_time'],
				'cheapEndTime' => $item['cheap_end_time']
			);
		}

		return $info;
	}

	public static function getLevelList()
	{
		$info = array();
		$userLevelList = Hapyfish2_Island_Cache_BasicInfo::getUserLevelList();
		$islandLevelList = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelList();
		$giftLevelList = Hapyfish2_Island_Cache_BasicInfo::getGiftLevelList();

		$tmp = array();
		foreach ($islandLevelList as $item) {
			$tmp[$item['need_user_level']] = array('island_size' => $item['island_size'], 'max_visitor' => $item['max_visitor']);
		}
		$lastCount = 0;
		foreach ($userLevelList as $level => $exp) {
			$itemIdList = isset($giftLevelList[$level]['cid']) ? $giftLevelList[$level]['cid'] : '';
			$itemNumList = isset($giftLevelList[$level]['cid']) ? '1' : '';
			if ( $giftLevelList[$level]['item_id'] > 0 ) {
				$itemIdList = $itemIdList . ',' . $giftLevelList[$level]['item_id'];
				$itemNumList = '1,1';
			}
			
			$v = array(
				'level' => $level,
				'addGem' => isset($giftLevelList[$level]) ? $giftLevelList[$level]['gold'] : 0,
				'exp' => $exp,
				'itemIdList' => $itemIdList,
				'itemNumList' => $itemNumList,
				'island' => isset($tmp[$level]) ? $tmp[$level]['island_size'] : null
			);
			$addVisitor = 0;
			if (isset($tmp[$level])) {
				if ($lastCount > 0) {
					$addVisitor = $tmp[$level]['max_visitor'] - $lastCount;
				}
				$lastCount = $tmp[$level]['max_visitor'];
			}
			$v['addVisitor'] = $addVisitor;

			$info[] = $v;
		}

		return $info;
	}

	public static function getIslandUpgradeList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelList();
		foreach ($data as $item) {
			$info[] = array(
				'mustLevel' => $item['need_user_level'],
				'mustLevel2' => $item['need_user_level_2'],
				'mustLevel3' => $item['need_user_level_3'],
				'mustLevel4' => $item['need_user_level_4'],
				'coin' => $item['coin'],
				'fastGem' => $item['gold'],
				'size' => $item['island_size']
			);
		}
		
		return $info;	
	}
	
	public static function getDailyTaskList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getDailyTaskList();
		foreach ($data as $item) {
			$info[] = array(
				'taskClassId' => $item['id'],
				'type' => 1,
				'name' => $item['name'],
				'content' => $item['content'],
				'needType' => $item['need_field'],
				'needCid' => null,
				'needNum' => $item['need_num'],
				'level' => $item['level'],
				'unLockLevel' => $item['need_level'],
				'addCoin' => $item['coin'],
				'addExp' => $item['exp'],
				'addItemCid' => $item['cid'],
				'addItemNum' => 1,
				'addTitle' => $item['title'],
				'description' => $item['description']
			);
		}

		return $info;
	}

	public static function getBuildTaskList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getBuildTaskList();
		foreach ($data as $item) {
			$info[] = array(
				'taskClassId' => $item['id'],
				'type' => 2,
				'name' => $item['name'],
				'content' => $item['content'],
				'needType' => $item['need_field'],
				'needCid' => $item['need_cid'],
				'needNum' => $item['need_num'],
				'level' => $item['level'],
				'unLockLevel' => $item['need_level'],
				'addCoin' => $item['coin'],
				'addExp' => $item['exp'],
				'addItemCid' => $item['cid'],
				'addItemNum' => 1,
				'addTitle' => $item['title'],
				'description' => $item['description']
			);
		}

		return $info;
	}

	public static function getAchievementTaskList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskList();
		$titleList = Hapyfish2_Island_Cache_BasicInfo::getTitleList();
		foreach ($data as $item) {
			if(in_array($item['id'], array(3068, 3069, 3070, 3083, 3084, 3085))) {
				continue;
			}

			$info[] = array(
				'taskClassId' => $item['id'],
				'type' => 3,
				'name' => $item['name'],
				'description' => $item['content'],
				'needType' => $item['need_field'],
				'needCid' => null,
				'needNum' => $item['need_num'],
				'level' => $item['level'],
				'unLockLevel' => $item['need_level'],
				'addCoin' => $item['coin'],
				'addExp' => $item['exp'],
				'addItemCid' => $item['cid'],
				'addItemNum' => 1,
				'addTitle' => $titleList[$item['title']],
				'nextTaskId' => $item['next_task'],
				'nextTwoTaskId' => $item['next_two_task'],
				'titleId' => $item['title'],
				'honorNum' => $item['honor'],
			);
		}

		return $info;
	}

	public static function getTitleList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getTitleList();
		$achievementTaskList = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskList();
		$tmp = array();
		foreach ($achievementTaskList as $item) {
			$tmp[$item['title']] = array('coin' => $item['coin'], 'exp' => $item['exp'], 'gold' => $item['gold'], 'cid' => $item['cid']);
		}
		foreach ($data as $id => $name) {
			$info[] = array(
				'id' => $id,
				'name' => $name,
				'coin' => $tmp[$id]['coin'],
				'exp' => $tmp[$id]['exp'],
				'gemNum' => $tmp[$id]['gold'],
				'cardId' => $tmp[$id]['cid'],
				'cardNum' => 1
			);
		}

		return $info;
	}

	public static function getBoatClass()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getShipList();
		$shipPraiseList = Hapyfish2_Island_Cache_BasicInfo::getShipPraiseList();

		foreach ($data as $id => $item) {
			$t = array(
				'boatId' => $item['sid'],
				'level' => $item['sid'],
				'name' => $item['name'],
				'className' => $item['class_name'],
				'startVisitorNum' => $item['start_visitor_num'],
				'safeVisitorNum' => $item['safe_visitor_num'],
				'waitTime' => $item['wait_time'],
				'safeTime1' => $item['safe_time_1'],
				'safeTime2' => $item['safe_time_2'],
				'coin' => $item['coin'],
				'gem' => $item['gem'],
				'needLevel' => $item['level'],
			);

			$shipPraise = $shipPraiseList[$id];
			$addVisitors = array();
			foreach ($shipPraise as $v) {
				$addVisitors[] = $v[0] . ',' . $v[1];
			}
			$t['addVisitors'] = $addVisitors;

			$info[] = $t;
		}

		return $info;
	}

}