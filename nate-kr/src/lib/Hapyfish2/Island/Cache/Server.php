<?php

class Hapyfish2_Island_Cache_Server
{
	public static function getBasicMC()
	{
		$key = 'mc_0';
		return Hapyfish2_Cache_Factory::getBasicMC($key);
	}
	
	public static function getServerList()
	{
		$key = 'island:serverlist';
		$cache = self::getBasicMC();
		$tpl = $cache->get($key);
		if (!$tpl) {
			$tpl = self::loadServerList();
		}
		
		return $tpl;
	}
	
	public static function loadServerList()
	{
		$db = Hapyfish2_Island_Dal_BasicInfo::getDefaultInstance();
		$tpl = $db->getServerList();
		if ($tpl) {
			$key = 'island:serverlist';
			$cache = self::getBasicMC();
			$cache->set($key, $tpl);
		}
		
		return $tpl;
	}
	
	public static function getWebServerList()
	{
		$list = self::getServerList();
		if ($list) {
			$webList = array();
			foreach ($list as $id => $item) {
				if ($item['type'] == 1) {
					$webList[$id] = $item;
				}
			}
			return $webList;
		}
		
		return null;
	}
	
	public static function getCacheServerList()
	{
		$list = self::getServerList();
		if ($list) {
			$cacheList = array();
			foreach ($list as $id => $item) {
				if ($item['type'] == 2) {
					$cacheList[$id] = $item;
				}
			}
			return $cacheList;
		}
		
		return null;
	}
	
	public static function getDBServerList()
	{
		$list = self::getServerList();
		if ($list) {
			$dbList = array();
			foreach ($list as $id => $item) {
				if ($item['type'] == 3) {
					$dbList[$id] = $item;
				}
			}
			return $dbList;
		}
		
		return null;
	}
	
}