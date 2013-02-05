<?php

class Hapyfish2_Island_Bll_Notice
{
	public static function add($info)
	{
		try {
			$dalBasicInfoManage = Hapyfish2_Island_Dal_BasicInfoManage::getDefaultInstance();
			$dalBasicInfoManage->addNotice($info);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function update($id, $info)
	{
		try {
			$dalBasicInfoManage = Hapyfish2_Island_Dal_BasicInfoManage::getDefaultInstance();
			$dalBasicInfoManage->updateNotice($id, $info);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function delete($id)
	{
		try {
			$dalBasicInfoManage = Hapyfish2_Island_Dal_BasicInfoManage::getDefaultInstance();
			$dalBasicInfoManage->deleteNotice($id);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function loadToMemcached()
	{
		return Hapyfish2_Island_Cache_BasicInfo::loadNoticeList();
	}
	
	public static function loadToAPC()
	{
		$key = 'island:pubnoticelist';
		$cache = Hapyfish2_Island_Cache_BasicInfo::getBasicMC();
		$list = $cache->get($key);
		
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$localcache->set($key, $list, false, 900);
		
		return $list;
	}
	
	public static function getAllFromDB()
	{
		try {
			$dalBasicInfoManage = Hapyfish2_Island_Dal_BasicInfoManage::getDefaultInstance();
			return $dalBasicInfoManage->getList();
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function getFromMemcached()
	{
		$key = 'island:pubnoticelist';
		$cache = Hapyfish2_Island_Cache_BasicInfo::getBasicMC();
		return $cache->get($key);
	}
	
	public static function getFromAPC()
	{
		$key = 'island:pubnoticelist';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		return $localcache->get($key);
	}

}