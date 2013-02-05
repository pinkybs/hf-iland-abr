<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com,
 * 2011-4-29
 * */
class Hapyfish2_Island_Cache_Hash
{
	
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}
	
	// 获得 val
	public static function get($key)
	{
		$memkey = 'island:hash:' . $key;
		$cache = self::getBasicMC();
		$val = $cache->get($memkey);
		
		if ($val === false) {
			$val = self::reloadVal($key);
		}
		
		return $val;
	}
	
	// 设置 key,val
	public static function set($key, $val)
	{
		$db = Hapyfish2_Island_Dal_Hash::getDefaultInstance();
		$db->set($key, $val);
		
		return self::reloadVal($key);
	}
	
	// 清除 key
	public static function clear($key)
	{
		$cache = self::getBasicMC();
		$cache->delete('island:hash:' . $key);
		
		$db = Hapyfish2_Island_Dal_Hash::getDefaultInstance();
		$db->clear($key);
	}
	
	// 重置 key, 返回hash 数组
	public static function reloadVal($key)
	{
		$db = Hapyfish2_Island_Dal_Hash::getDefaultInstance();
		$val = $db->get($key);
		$val = $val[$key];
		if ($val) {
			$key = 'island:hash:' . $key;
			$cache = self::getBasicMC();
			$cache->set($key, $val);
		}
		return $val;
	}
	
}


