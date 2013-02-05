<?php
class Hapyfish2_Island_Cache_BottleQueue
{
	protected static $_key = 'bottle:queue';
	
	protected static $_count = 20;
	
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}
	
	public static function unshift($val)
	{
		
		$cache = self::getBasicMC();
		$queue = $cache->get(self::$_key);
		$queue = array_slice($queue, 0, self::$_count-1);
		if ($queue) {
			array_unshift($queue, $val);
		} else {
			$queue = array($val);
		}
		return $cache->set(self::$_key, $queue, 0);
		
		
	}
	
	public static function getall()
	{
		
		$cache = self::getBasicMC();
		$val = $cache->get(self::$_key);
		return $val;
	
	}
	
	public static function clear()
	{
		try {
			$cache = self::getBasicMC();
			$cache->delete(self::$_key);
		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}
	
}