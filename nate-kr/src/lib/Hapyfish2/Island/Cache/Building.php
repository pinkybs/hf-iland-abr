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
	            if ($ids) {
	            	$cache->add($key, $ids);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        }
        
        return $ids;
    }

	public static function getOnIslandIds($uid)
    {
        $key = 'i:u:bldids:onisl:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	try {
	            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
	            $ids = $dalBuilding->getOnIslandIds($uid);
	            if ($ids) {
	            	$cache->add($key, $ids);
	            } else {
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
    	
    	$onIslandIds = self::getOnIslandIds($uid);
    	if ($onIslandIds) {
    		$ids = array_diff($allIds, $onIslandIds);
    	} else {
    		$ids = $allIds;
    	}
        
        return $ids;
    }
    
    public static function reloadAllIds($uid)
    {
        try {
            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
            $ids = $dalBuilding->getAllIds($uid);
            if ($ids) {
        		$key = 'i:u:bldids:all:' . $uid;
        		$cache = Hapyfish2_Cache_Factory::getMC($uid);
            	$cache->set($key, $ids);
            } else {
            	return null;
            }
            
            return $ids;
        } catch (Exception $e) {
        	return null;
        }
    }
    
    public static function reloadOnIslandIds($uid)
    {
        try {
            $dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
            $ids = $dalBuilding->getOnIslandIds($uid);
            if ($ids) {
        		$key = 'i:u:bldids:onisl:' . $uid;
        		$cache = Hapyfish2_Cache_Factory::getMC($uid);
            	$cache->set($key, $ids);
            } else {
            	return null;
            }
            
            return $ids;
        } catch (Exception $e) {
        	return null;
        }
    }
    
    public static function popOneIdOnIsland($uid, $id)
    {
        $key = 'i:u:bldids:onisl:' . $uid;
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
    
    public static function pushOneIdOnIsland($uid, $id)
    {
        $key = 'i:u:bldids:onisl:' . $uid;
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