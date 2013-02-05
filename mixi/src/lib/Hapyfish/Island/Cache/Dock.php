<?php

class Hapyfish_Island_Cache_Dock
{
	
    /**
     * get user ship list
     *
     * @param int $uid
     */
    public static function getUserShipList($uid)
    {
		$key = 'UserShipList_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$shipList = $cache->get($key);
		if ($shipList === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$shipList = $db->getUserShipList($uid);
			if ($shipList) {
				$cache->add($key, $shipList, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $shipList;
    }
    
    public static function cleanUserShipList($uid)
    {
 		$key = 'UserShipList_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }
    
    /**
     * get user unlock ship list
     *
     * @param int $uid
     * @param int $fid
     */
    public static function getUserUnlockShipList($uid, $pid)
    {
		$key = 'UserUnlockShipList_' . $uid . '_' . $pid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$unlockshipList = $cache->get($key);
		if ($unlockshipList === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$unlockshipList = $db->getUserUnlockShipList($uid, $pid);
			if ($unlockshipList) {
				$cache->add($key, $unlockshipList, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $unlockshipList;
    }
    
    public static function cleanUserUnlockShipList($uid, $pid)
    {
 		$key = 'UserUnlockShipList_' . $uid . '_' . $pid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }
    
    /**
     * get user position list
     *
     * @param int $uid
     */
    public static function getUserPositionList($uid)
    {
		$key = 'UserPositionList_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$positionList = $cache->get($key);
		if ($positionList === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$positionList = $db->getUserPositionList($uid);
			if ($positionList) {
				$cache->add($key, $positionList, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $positionList;
    }
    
    public static function cleanUserPositionList($uid)
    {
 		$key = 'UserPositionList_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }
    
    /**
     * get ship info by ship id
     *
     * @return array
     */
    public static function getShip($id)
    {
		$key = 'Ship_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$ship = $cache->get($key);
		if ($ship === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$ship = $db->getShip($id);
			if ($ship) {
				$cache->add($key, $ship, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $ship;
    }
    
    /**
     * get dock info by position id
     *
     * @return array
     */
    public static function getAddBoatByid($id)
    {
		$key = 'AddBoatByid_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$addship = $cache->get($key);
		if ($addship === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$addship = $db->getAddBoatByid($id);
			if ($addship) {
				$cache->add($key, $addship, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $addship;
    }
    
        /**
     * get ship add visitore count by praise
     *
     * @return array
     */
    public static function getShipAddVisitorByPraise($shipId, $praise)
    {
		$key = 'ShipAddVisitorByPraise_' . $shipId . '_' . $praise;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$num = $cache->get($key);
		if ($num === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Dock::getDefaultInstance();
			$num = $db->getShipAddVisitorByPraise($shipId, $praise);
			if ($num) {
				$cache->add($key, $num, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $num;
    }
	
}