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
        $key = 'i:u:pltids:onisl:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $ids = $cache->get($key);

        if ($ids === false) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $ids = $dalPlant->getOnIslandIds($uid);
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
            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
            $ids = $dalPlant->getAllIds($uid);
            if ($ids) {
        		$key = 'i:u:pltids:all:' . $uid;
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
	    	$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$ids = $dalPlant->getOnIslandIds($uid);
	            
			if ($ids) {
				$key = 'i:u:pltids:onisl:' . $uid;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, $ids);
			} else {
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
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
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
    
    public static function popOneIdOnIsland($uid, $id)
    {
        $key = 'i:u:pltids:onisl:' . $uid;
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
        $key = 'i:u:pltids:onisl:' . $uid;
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