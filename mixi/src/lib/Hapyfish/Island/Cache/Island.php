<?php

class Hapyfish_Island_Cache_Island
{
	
    /**
     * get island level info
     *
     * @return array
     */
    public static function getIslandLevelInfo($id)
	{
		$key = 'IslandLevelInfo_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Island::getDefaultInstance();
			$info = $db->getIslandLevelInfo($id);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
	}
	
	public static function getIslandLevelInfoByUserLevel($level)
	{
		$key = 'IslandLevelInfoByUserLevel_' . $level;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Island::getDefaultInstance();
			$info = $db->getIslandLevelInfoByUserLevel($level);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
	}


}