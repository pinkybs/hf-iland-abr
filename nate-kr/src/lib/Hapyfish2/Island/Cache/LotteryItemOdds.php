<?php

class Hapyfish2_Island_Cache_LotteryItemOdds
{
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}

	public static function getLotteryItemOddsList($categoryId)
	{
		$key = 'island:lotteryitemodds:' . $categoryId;
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$list = $localcache->get($key);
		if (!$list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadLotteryItemOddsList($categoryId);
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}
		return $list;
	}

	public static function loadLotteryItemOddsList($categoryId)
	{
		$db = Hapyfish2_Island_Dal_LotteryItemOdds::getDefaultInstance();
		$tpl = $db->lstItemOddsByCategory($categoryId);
		if ($tpl) {
			$key = 'island:lotteryitemodds:'. $categoryId;
			$cache = self::getBasicMC();
			$cache->set($key, $tpl);
		}
		return $tpl;
	}

}