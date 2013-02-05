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

        return $resultInitVo;
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
				'isNew' => $item['new']
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
				'isNew' => $item['new']
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
				'nextCid' => $item['next_level_cid']
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
				'isNew' => $item['new']
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
			$v = array(
				'level' => $level,
				'addGem' => 2,
				'exp' => $exp,
				'addCid' => isset($giftLevelList[$level]) ? $giftLevelList[$level]['cid'] : null,
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
	
	public static function getDailyTaskList()
	{
		$info = array();
		$data = Hapyfish2_Island_Cache_BasicInfo::getDailyTaskList();
		foreach ($data as $item) {
			$info[] = array(
				'taskClassId' => $item['id'],
				'type' => 1,
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
				'addTitle' => $item['title']
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
				'addTitle' => $item['title']
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
			$info[] = array(
				'taskClassId' => $item['id'],
				'type' => 3,
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
				'addTitle' => $titleList[$item['title']],
				'nextTaskId' => $item['next_task'],
				'nextTwoTaskId' => $item['next_two_task']
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
			$tmp[$item['title']] = array('coin' => $item['coin'], 'exp' => $item['exp']);
		}
		foreach ($data as $id => $name) {
			$info[] = array(
				'id' => $id,
				'name' => $name,
				'coin' => $tmp[$id]['coin'],
				'exp' => $tmp[$id]['exp']
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