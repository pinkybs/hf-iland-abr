<?php

class Hapyfish2_Island_Cache_Dock
{
	public static function getUnlockShipCount($uid)
	{
		$key = 'i:u:unlockshcnt:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$countList = $cache->get($key);
		
        if ($countList === false) {
            $dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();

            $data = $dalDock->getUnlockShipCount($uid);
            
            if ($data) {
            	$countList = array();
            	foreach ($data as $item) {
            		if (!empty($item)) {
            			$tmp = split(',', $item);
            			foreach ($tmp as $id) {
            				if (!isset($countList[$id])) {
            					$countList[$id] = 1;
            				} else {
            					$countList[$id] += 1;
            				}
            			}
            			
            		}
            	}
            	$cache->add($key, $countList);
            } else {
            	return false;
            }
        }
        
        return $countList;
	}
	
	public static function reloadUnlockShipCount($uid)
	{
		$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
		$data = $dalDock->getUnlockShipCount($uid);

		if ($data) {
			$countList = array();
			foreach ($data as $item) {
				if (!empty($item)) {
					$tmp = split(',', $item);
					foreach ($tmp as $id) {
						if (!isset($countList[$id])) {
							$countList[$id] = 1;
						} else {
							$countList[$id] += 1;
						}
					}
				}
			}
			$key = 'i:u:unlockshcnt:' . $uid;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache->set($key, $countList);
		} else {
			return false;
		}
		
		return $countList;
	}
	
	public static function getUnlockShipIds($uid, $positionId)
	{
		$key = 'i:u:unlockshids:' . $uid . ':' . $positionId;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		
		$shipIds = $cache->get($key);
		if ($shipIds === false) {
			try {
				$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
				$shipIds = $dalDock->getUnlockShipIds($uid, $positionId);
				if ($shipIds) {
					$cache->add($key, $shipIds);
				} else {
					return null;
				}
			} catch (Exception $e) {
				return null;
			}
		}
		
		return split(',', $shipIds);
	}
	
	public static function unlockShip($uid, $positionId, $shipId, $shipList = null)
	{
		$result = false;

		try {
			if (!$shipList) {
				$shipList = self::getUnlockShipIds($uid, $positionId);
			}
			$shipList[] = $shipId;
			$ids = join(',', $shipList);
			
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$dalDock->unlockShip($uid, $positionId, $ids);
			
			$key = 'i:u:unlockshids:' . $uid . ':' . $positionId;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache->set($key, $ids);
			
			self::reloadUnlockShipCount($uid);
			
			$result = true;
		} catch (Exception $e) {
			err_log($e->getMessage());
		}
		
		return $result;
	}
	
	public static function getPositionCount($uid)
	{
		try {
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$count = $dalDock->getPositionCount($uid);
			return $count;
		} catch (Exception $e) {
			return null;
		}
	}
    
}