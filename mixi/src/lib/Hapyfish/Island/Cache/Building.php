<?php

class Hapyfish_Island_Cache_Building
{
	
    /**
     * get user using building
     *
     * @param int $uid
     */
    public static function getUsingBuilding($uid)
    {
		$key = 'UsingBuilding_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$buildings = $cache->get($key);
		if ($buildings === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Building::getDefaultInstance();
			$buildings = $db->getUsingBuilding($uid);
			if ($buildings) {
				$cache->add($key, $buildings, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $buildings;
    }
    
    public static function cleanUsingBuilding($uid)
    {
 		$key = 'UsingBuilding_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }
	
}