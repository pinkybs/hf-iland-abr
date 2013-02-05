<?php
class Hapyfish2_Island_Cache_CasinoAwardType
{
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}
	
	public static function getAllType()
	{
		$key = 'island:caisnoawardtype';
		
		$cache = self::getBasicMC();
		$list = $cache->get($key);
		
		if ($list === false) {
			$list = self::loadAllType();
			if ($list ) {
				$cache->set($key, $list);
				return $list;
			}
			return false;
		}
		
		return $list;
	}
	
	public static function loadAllType()
	{
		$db = Hapyfish2_Island_Dal_CasinoAwardType::getDefaultInstance();
		$list = $db->getAll();
		$interval = array();
		$a = $b = 0;
		
		foreach ($list as $key => $val) {
			$b = $b + $val['odds'];
			$interval[] = array('a'=>$a, 'b'=>$b, 'id'=>$key);
			$a = $b;
		}
		
		if ($list && $interval) {
			return array('list'=>$list, 'interval'=>$interval);
		}
		
		return false;
	}
	
	public static function reloadAllType()
	{
		$key = 'island:caisnoawardtype';
		
		$cache = self::getBasicMC();
		
		$list = self::loadAllType();
		
		return $cache->set($key, $list);
	}
	
}