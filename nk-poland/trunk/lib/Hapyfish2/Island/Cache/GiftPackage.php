<?php
class Hapyfish2_Island_Cache_GiftPackage
{	
	
	/**
	 * @param integer uid
	 * @add send gift log
	 */
	public static function insertPostGiftLog($uid, $GiftLog)
	{
		$key = 'i:u:gift:log:post:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getLogData($uid);
        $cache->insertGiftLog($key, $GiftLog);
	}
	
	/**
	 * @param integer uid
	 * @add get gift log
	 */
	public static function insertGetGiftLog($uid, $GiftLog)
	{
		$key = 'i:u:gift:log:get:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getLogData($uid);
        $cache->insertGiftLog($key, $GiftLog);
	}
	
	public static function getGiftLogData($uid)
	{
		$key = 'i:u:gift:log:get:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getLogData($uid);
		return $cache->get($key);
	}
	
	public static function getGiftLogCount($uid)
	{
		$key = 'i:u:gift:log:count:get:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getLogData($uid);
		$count = $cache->get($key);
		if ($count === false) {
			$count = 0;
		}

		return $count;
	}
	
	public static function postGiftLogData($uid)
	{
		$key = 'i:u:gift:log:post:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getLogData($uid);
		return $cache->get($key);
	}
	
	public static function postGiftLogCount($uid)
	{
		$key = 'i:u:gift:log:count:post:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getLogData($uid);
		$count = $cache->get($key);
		if ($count === false) {
			$count = 0;
		}

		return $count;
	}
	
}