<?php

class Hapyfish_Island_Cache_BasicInfo
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
		$info = null;

		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getBackgroundList();
		} catch (Exception $e) {
			
		}
		
		return $info;
	}
	
	public static function getBuildingList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getBuildingList();
		} catch (Exception $e) {
			
		}
		
		return $info;		
	}
	
	public static function getPlantList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getPlantList();
		} catch (Exception $e) {
			
		}
		
		return $info;		
	}
	
	public static function getCardList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getCardList();
		} catch (Exception $e) {
			
		}
		
		return $info;	
	}
	
	public static function getLevelList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getLevelList();
			
		    $lastCount = 0;
            for ($i = 0,$iCount = count($info); $i < $iCount; $i++) {
                $info[$i]['addVisitor'] = 0;
                if ($info[$i]['visitor_count'] > 0) {
                    if ($lastCount > 0) {
                        $info[$i]['addVisitor'] = $info[$i]['visitor_count'] - $lastCount;
                    }
                    $lastCount = $info[$i]['visitor_count'];
                }
                unset($info[$i]['visitor_count']);
            }
			
		} catch (Exception $e) {
			
		}
		
		return $info;
	}
	
	public static function getDailyTaskList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getDailyTaskList();
		} catch (Exception $e) {
			
		}
		
		return $info;	
	}
	
	public static function getBuildTaskList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getBuildTaskList();
		} catch (Exception $e) {
			
		}
		
		return $info;	
	}
	
	public static function getAchievementTaskList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getAchievementTaskList();
		} catch (Exception $e) {
			
		}
		
		return $info;	
	}
	
	public static function getTitleList()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$info = $db->getTitleList();
		} catch (Exception $e) {
			
		}
		
		return $info;	
	}
	
	public static function getBoatClass()
	{
		$info = null;
		
		try {
			//load from db
			$db = Hapyfish_Island_Dal_BasicInfo::getDefaultInstance();
			$shipList = $db->getShipList();
			$info = array();
			foreach ($shipList as $key => $value)
			{
	            $value['addVisitors'] = $db->getShipAddVisitorBySid($value['boatId']);
				$info[$key] = $value;
			}			

		} catch (Exception $e) {
			
		}
		
		return $info;
	}
	
}