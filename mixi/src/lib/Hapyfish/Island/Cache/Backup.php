<?php

class Hapyfish_Island_Cache_Backup
{
	public static function dumpDBPlantPayInfo()
	{
		$db = Hapyfish_Island_Dal_Backup::getDefaultInstance();
		$path = TEMP_DIR . '/dbdump/plantpayinfo/';
		$time = time() - 3600*24*7;
		$size = 1000;
		for($i = 0; $i < 10; $i++) {
			$dir = $path . $i;
			mkdir($dir, 0777, true);
			$count = $db->getPlantPayInfoCount($i, $time);
			$page = ceil($count/$size);
			for($j = 1; $j <= $page; $j++) {
				$file = $dir . '/data_' . $j;
				$info = $db->getPlantPayInfo($i, $j, $size, $time);
				if(!empty($info)) {
					file_put_contents($file, json_encode($info));
				}
			}
		}
	}
	
	public static function restoreDBPlantPayInfo()
	{
		$dir = TEMP_DIR . '/dbdump/plantpayinfo/';
		for($i = 0; $i < 10; $i++) {
			$d = glob($dir . $i . '/*');
			foreach ($d as $f) {
				if (is_file($f)) {
					$data = file_get_contents($f);
					if (!empty($data)) {
						$info = json_decode($data);
						$cachedata = array();
						foreach ($info as $v) {
							$key = 'UserPlantPayInfoById_' . $v['uid'] . '_' . $v['id'];
							$cachedata[$key] = $v;
						}
						print_r($cachedata);
					}
				}
			}
		}
	}
	
	public static function dumpCachePlantPayInfo()
	{
		$db = Hapyfish_Island_Dal_Backup::getDefaultInstance();
		$dbplant = Hapyfish_Island_Dal_Plant::getDefaultInstance();
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$dir = TEMP_DIR . '/cachedump/plantpayinfo/';
		mkdir($dir, 0777, true);
		$time = time() - 3600*24*7;
		$size = 1000;
		$count = $db->getUserCount($time);
		$page = ceil($count/$size);
		for($j = 1; $j <= $page; $j++) {
			$file = $dir . '/data_' . $j;
			$userIds = $db->getUserIds($j, $size, $time);
			$keys = array();
			if(!empty($userIds)) {
				foreach ($userIds as $row) {
					$ids = $dbplant->getUserPlantIds($row['uid']);
					if (!empty($ids)) {
						foreach ($ids as $id) {
							$keys[] = 'UserPlantPayInfoById_' . $row['uid'] . '_' . $id;
						}
					}
				}
			}
			
			if (!empty($keys)) {
				$info = $cache->getMulti($keys);
				if(!empty($info)) {
					file_put_contents($file, json_encode(array_values($info)));
				}
			}
		}
	}
	
	public static function restoreCachePlantPayInfo()
	{
		$dir = TEMP_DIR . '/dbdump/plantpayinfo/';
		for($i = 0; $i < 10; $i++) {
			$d = glob($dir . $i . '/*');
			foreach ($d as $f) {
				if (is_file($f)) {
					$data = file_get_contents($f);
					if (!empty($data)) {
						$info = json_decode($data);
						$cachedata = array();
						foreach ($info as $v) {
							$key = 'UserPlantPayInfoById_' . $v['uid'] . '_' . $v['id'];
							$cachedata[$key] = $v;
						}
						print_r($cachedata);
					}
				}
			}
		}
	}


	
}