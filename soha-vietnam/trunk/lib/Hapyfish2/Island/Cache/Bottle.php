<?php
class Hapyfish2_Island_Cache_Bottle
{
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}
	
	// 获得一季里所有物品
	public static function getAllByBottleId($btl_id)
	{
		$key = 'island:bottle:' . $btl_id;
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$list = $localcache->get($key);
		
		if (! $list) {
			$cache = self::getBasicMC();
			$list = $cache->get($key);
			if (!$list) {
				$list = self::loadAllByBottleId($btl_id);
			}
			if ($list) {
				$localcache->set($key, $list);
			}
		}
		
		return $list;
	}
	
	// 从db 加载，一季的物品
	public static function loadAllByBottleId($btl_id)
	{
		$db = Hapyfish2_Island_Dal_Bottle::getDefaultInstance();
		$list = $db->getAllByBottleId($btl_id);
		$interval = array();
		$a = $b = 0;
			 
		foreach ($list as $key => $val) {
			$b = $b + $val['odds'];
			$interval[] = array('a'=>$a, 'b'=>$b, 'id'=>$key);
			$a = $b;
		}
		
		if ($list && $interval) {
			$key = 'island:bottle:' . $btl_id;
			$list = array('list'=>$list, 'interval'=>$interval);
			$cache = self::getBasicMC();
			$cache->set($key, $list);
		}
		
		return $list;
	}
	
	// 重置
	public static function reloadAllByBottleId($btl_id)
	{
		$key = 'island:bottle:' . $btl_id;
		$list = self::loadAllByBottleId($btl_id);
		$tf = false;
		
		if ($list) {
			$localcache = Hapyfish2_Cache_LocalCache::getInstance();
			$tf = $localcache->set($key, $list);
		}
	}
	
	// 创建新季物品时，用来初始化bottle 表数据
	public static function initRows($btl_id, $num)
	{
		$db = Hapyfish2_Island_Dal_Bottle::getDefaultInstance();
		$db->initRows($btl_id, $num);
	}
	
	// 删除单行数据
	public static function delete($id, $btl_id)
	{
		$db = Hapyfish2_Island_Dal_Bottle::getDefaultInstance();
		$db->delete($id);
		
		self::reloadAllByBottleId($btl_id);
	}
	
	// 更新单行数据
	public static function update($id, $btl_id, $info)
	{
		$db = Hapyfish2_Island_Dal_Bottle::getDefaultInstance();
		$db->update($id, $info);
		
		self::reloadAllByBottleId($btl_id);
	}
	
	
}
