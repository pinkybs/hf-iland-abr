<?php

class Hapyfish2_Island_Cache_Building
{
	public static function getAllIds($uid)
    {
        $key = 'i:u:bldids:all:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	try {
	            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
	            $ids = $dalBuilding->getAllIds($uid);
	            if (!empty($ids)) {
	            	$cache->add($key, $ids);
	            } else {
	            	$cache->add($key, array());
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        }
        
        return $ids;
    }

	public static function getOnIslandIds($uid, $islandId)
    {
    	if ( $islandId > 1 ) {
        	$key = 'i:u:bldids:onisl:' . $uid . ':' . $islandId;
    	}
    	else {
        	$key = 'i:u:bldids:onisl:' . $uid;
    	}
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false || $ids === array()) {
        	try {
	            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
	            $ids = $dalBuilding->getOnIslandIds($uid, $islandId);
	            if (!empty($ids)) {
	            	$cache->add($key, $ids);
	            } else {
	            	$cache->add($key, array());
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        }
        
        return $ids;
    }
    
	public static function getInWareHouseIds($uid)
    {
        $allIds = self::getAllIds($uid);
    	if (!$allIds) {
    		return null;
    	}
    	
    	$onIslandIdsIn1 = self::getOnIslandIds($uid, 1);
    	$onIslandIdsIn2 = self::getOnIslandIds($uid, 2);
    	$onIslandIdsIn3 = self::getOnIslandIds($uid, 3);
    	$onIslandIdsIn4 = self::getOnIslandIds($uid, 4);
    	if ( !$onIslandIdsIn1 ) {
    		$onIslandIdsIn1 = array();
    	}
    	if ( !$onIslandIdsIn2 ) {
    		$onIslandIdsIn2 = array();
    	}
    	if ( !$onIslandIdsIn3 ) {
    		$onIslandIdsIn3 = array();
    	}
    	if ( !$onIslandIdsIn4 ) {
    		$onIslandIdsIn4 = array();
    	}
    	
    	$ids = array_diff($allIds, $onIslandIdsIn1, $onIslandIdsIn2, $onIslandIdsIn3, $onIslandIdsIn4);
    	
    	/*if ($onIslandIds) {
    		$ids = array_diff($allIds, $onIslandIds);
    	} else {
    		$ids = $allIds;
    	}*/
    	
        return $ids;
    }
    
    public static function reloadAllIds($uid)
    {
        try {
            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
            $ids = $dalBuilding->getAllIds($uid);
        	$key = 'i:u:bldids:all:' . $uid;
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
            if (!empty($ids)) {
            	$cache->set($key, $ids);
            } else {
            	$cache->set($key, array());
            	return null;
            }
            
            return $ids;
        } catch (Exception $e) {
        	return null;
        }
    }
    
    public static function reloadOnIslandIds($uid, $islandId)
    {
        try {
            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
            $ids = $dalBuilding->getOnIslandIds($uid, $islandId);
            
	    	if ( $islandId > 1 ) {
	        	$key = 'i:u:bldids:onisl:' . $uid . ':' . $islandId;
	    	}
	    	else {
	        	$key = 'i:u:bldids:onisl:' . $uid;
	    	}
        	
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
            if (!empty($ids)) {
            	$cache->set($key, $ids);
            } else {
            	$cache->set($key, array());
            	return null;
            }
            
            return $ids;
        } catch (Exception $e) {
        	return null;
        }
    }
    
    public static function popOneIdOnIsland($uid, $id, $islandId)
    {
    	if ( $islandId > 1 ) {
        	$key = 'i:u:bldids:onisl:' . $uid . ':' . $islandId;
    	}
    	else {
        	$key = 'i:u:bldids:onisl:' . $uid;
    	}
        
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	return null;
        } else {
        	if (empty($ids)) {
        		return null;
        	} else {
	    		$newIds = array();
	    		foreach ($ids as $v) {
	    			if ($v != $id) {
	    				$newIds[] = $v;
	    			}
	    		}
	    		$cache->set($key, $newIds);
	    		return $newIds;
        	}
        }
    }
    
    public static function pushOneIdOnIsland($uid, $id, $islandId)
    {
    	if ( $islandId > 1 ) {
        	$key = 'i:u:bldids:onisl:' . $uid . ':' . $islandId;
    	}
    	else {
        	$key = 'i:u:bldids:onisl:' . $uid;
    	}
        
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	return null;
        } else {
        	$contain = false;
        	if (empty($ids)) {
        		$ids = array($id);
        	} else {
        		foreach ($ids as $v) {
        			if ($v == $id) {
        				$contain = true;
        				break;
        			}
        		}
        		if (!$contain) {
					$ids[] = $id;
        		}
        	}
        	if(!$contain) {
				$cache->set($key, $ids);
        	}
			return $ids;
        }
    }
    
    public static function pushOneIdInAll($uid, $id)
    {
        $key = 'i:u:bldids:all:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
			return null;
        } else {
        	if (empty($ids)) {
        		$ids = array($id);
        	} else {
        		$ids[] = $id;
        	}
        	$cache->set($key, $ids);
        	return $ids;
        }
    }
    
    public static function popOneIdInAll($uid, $id)
    {
        $key = 'i:u:bldids:all:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
			return null;
        } else {
        	if (empty($ids)) {
        		return null;
        	} else {
	    		$newIds = array();
	    		foreach ($ids as $v) {
	    			if ($v != $id) {
	    				$newIds[] = $v;
	    			}
	    		}
	    		$cache->set($key, $newIds);
	    		return $newIds;
        	}
        }
    }
}