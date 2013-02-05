<?php

class Hapyfish2_Island_Cache_Plant
{
	public static function getAllIds($uid)
    {
        $key = 'i:u:pltids:all:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $ids = $dalPlant->getAllIds($uid);
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
        	$key = 'i:u:pltids:onisla:' . $uid . ':' . $islandId;
    	}
    	else {
        	$key = 'i:u:pltids:onisla:' . $uid;
    	}
        
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false || $ids === array()) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $ids = $dalPlant->getOnIslandIds($uid, $islandId);
	            if (!empty($ids)) {
	            	$cache->add($key, $ids);
	            } else {
	            	$cache->add($key, array());
	            	return array();
	            }
        	} catch (Exception $e) {
        		return array();
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
            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
            $ids = $dalPlant->getAllIds($uid);
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
        	$key = 'i:u:pltids:all:' . $uid;
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
	    	$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$ids = $dalPlant->getOnIslandIds($uid, $islandId);
	            
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
	    	if ( $islandId > 1 ) {
        		$key = 'i:u:pltids:onisla:' . $uid . ':' . $islandId;
	    	}
	    	else {
				$key = 'i:u:pltids:onisla:' . $uid;
	    	}
	        
			if (!empty($ids)) {
				$cache->set($key, $ids);
			} else {
				$cache->set($key, array());
				return null;
			}
			
			return $ids;
		}catch (Exception $e) {
			return null;
		}
    }
    
    public static function getAllByItemKind($uid)
    {
		$key = 'island:userplantbyik:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		
        if ($data === false) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $data = $dalPlant->getAllByItemKind($uid);
	            
	            if ($data) {
	            	$cache->add($key, $data);
	            } else {
	            	return array();
	            }
        	} catch (Exception $e) {
        		return array();
        	}
        }
        
        $itemKind = array();
        foreach ($data as $item) {
        	$itemKind[] = array('item_id' => $item[0], 'level' => $item[1]);
        }
        
        return $itemKind;
    }
    
    public static function reloadAllByItemKind($uid)
    {
		try {
	    	$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$data = $dalPlant->getAllByItemKind($uid);
	            
			if ($data) {
				$key = 'island:userplantbyik:' . $uid;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, $data);
			} else {
				return null;
			}
			
			return $data;
		}catch (Exception $e) {
			return null;
		}
    }
    
    public static function popOneIdOnIsland($uid, $id, $islandId)
    {
    	if ( $islandId > 1 ) {
       		$key = 'i:u:pltids:onisla:' . $uid . ':' . $islandId;
    	}
    	else {
			$key = 'i:u:pltids:onisla:' . $uid;
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
       		$key = 'i:u:pltids:onisla:' . $uid . ':' . $islandId;
    	}
    	else {
			$key = 'i:u:pltids:onisla:' . $uid;
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
        $key = 'i:u:pltids:all:' . $uid;
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
        $key = 'i:u:pltids:all:' . $uid;
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