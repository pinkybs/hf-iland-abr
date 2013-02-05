<?php

class Hapyfish2_Island_HFC_Plant
{
	public static function getOnIsland($uid, $islandId, $savehighcache = false)
    {
        $ids = Hapyfish2_Island_Cache_Plant::getOnIslandIds($uid, $islandId); 
        if (!$ids) {
        	return null;
        }
        
        $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:plt:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return null;
        }
        
        //check all in memory
        $nocacheKeys = array();
        $empty = true;
        foreach ($data as $k => $item) {
        	if ($item == null) {
        		$nocacheKeys[] = $k;
        	} else {
        		$empty = false;
        	}
        }

        if ($empty) {
        	try {
        		Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid, $islandId);
        		
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $result = $dalPlant->getOnIsland($uid, $islandId);
	            if ($result) {
	            	$data = array();
	            	foreach ($result as $item) {
	            		$key = 'i:u:plt:' . $uid . ':' . $item[0];
	            		$data[$key] = $item;
	            	}
	            	$cache->addMulti($data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        } else if (!empty($nocacheKeys)) {
        	foreach ($nocacheKeys as $key) {
        		$tmp = split(':', $key);
        		$data[$key] = self::loadOne($uid, $tmp[4]);
        	}
        }
        
        $plants = array();
        $plantInfoList = Hapyfish2_Island_Cache_BasicInfo::getPlantList();
        $vaildIds = array();
        foreach ($data as $item) {
        	if ($item) {
        		$id = $item[0];
        		$vaildIds[] = $id;
	        	$plants[] = array(
		        	'uid' => $uid,
		        	'id' => $id,
		        	'cid' => $item[1],
		        	'level' => $item[2],
		        	'item_id' => $item[3],
		        	'item_type' => $item[4],
		        	'x' => $item[5],
		        	'y' => $item[6],
		        	'z' => $item[7],
		        	'mirro' => $item[8],
		        	'can_find' => $item[9],
	        	
		        	'pay_time' => $plantInfoList[$item[1]]['pay_time'],
		        	'ticket' => $plantInfoList[$item[1]]['ticket'],
		        	
		        	'start_pay_time' => $item[10],
		        	'wait_visitor_num' => $item[11],
		        	'delay_time' => $item[12],
		        	'event' => $item[13],
		        	'start_deposit' => $item[14],
		        	'deposit' => $item[15],
	        		'status' => $item[16]
	        	);
        	}
        }
        
        $data = array('ids' => $vaildIds, 'plants' => $plants);
        
        if ($savehighcache) {
	    	if ( $islandId > 1 ) {
        		$key = 'island:allplantonisland:' . $uid . ':' . $islandId;
	    	}
	    	else {
        		$key = 'island:allplantonisland:' . $uid;
	    	}
        	//$key = 'island:allplantonisland:' . $uid . ':' . $islandId;
			$hc = Hapyfish2_Cache_HighCache::getInstance();
			$hc->set($key, $data);
		}
		
		return $data;
    }
    
	public static function getInWareHouse($uid)
    {
        $ids = Hapyfish2_Island_Cache_Plant::getInWareHouseIds($uid);
        
        if (!$ids) {
        	return null;
        }
        
        $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:plt:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return null;
        }
        
        //check all in memory
        $nocacheKeys = array();
        $empty = true;
        foreach ($data as $k => $item) {
        	if ($item == null) {
        		$nocacheKeys[] = $k;
        	} else {
        		$empty = false;
        	}
        }

        if ($empty) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $result = $dalPlant->getInWareHouse($uid);
	            if ($result) {
	            	$data = array();
	            	foreach ($result as $item) {
	            		$key = 'i:u:plt:' . $uid . ':' . $item[0];
	            		$data[$key] = $item;
	            	}
	            	$cache->addMulti($data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        } else if (!empty($nocacheKeys)) {
        	foreach ($nocacheKeys as $key) {
        		$tmp = split(':', $key);
        		$data[$key] = self::loadOne($uid, $tmp[4]);
        	}
        }
        
        $plants = array();
        foreach ($data as $item) {
        	if ($item) {
	        	$plants[$item[0]] = array(
		        	'id' => $item[0],
		        	'cid' => $item[1],
		        	'level' => $item[2],
		        	'item_id' => $item[3],
		        	'item_type' => $item[4],
	        		'status' => $item[16]
	        	);
        	}
        }
		
		return $plants;
    }
    
	public static function getAll($uid)
    {
        $ids = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
        
        if (!$ids) {
        	return null;
        }
        
        $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:plt:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return null;
        }
        
        //check all in memory
        $nocacheKeys = array();
        $empty = true;
        foreach ($data as $k => $item) {
        	if ($item == null) {
        		$nocacheKeys[] = $k;
        	} else {
        		$empty = false;
        	}
        }

        if ($empty) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $result = $dalPlant->getAll($uid);
	            if ($result) {
	            	$data = array();
	            	foreach ($result as $item) {
	            		$key = 'i:u:plt:' . $uid . ':' . $item[0];
	            		$data[$key] = $item;
	            	}
	            	$cache->addMulti($data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        } else if (!empty($nocacheKeys)) {
        	foreach ($nocacheKeys as $key) {
        		$tmp = split(':', $key);
        		$data[$key] = self::loadOne($uid, $tmp[4]);
        	}
        }
        
        $plants = array();
        foreach ($data as $item) {
        	if ($item) {
	        	$plants[$item[0]] = array(
		        	'id' => $item[0],
		        	'cid' => $item[1],
		        	'level' => $item[2],
		        	'item_id' => $item[3],
		        	'item_type' => $item[4],
	        		'status' => $item[16]
	        	);
        	}
        }
		
		return $plants;
    }
    
    public static function getAllOnIslandFromHighCache($uid, $islandId)
    {
    	if ( $islandId > 1 ) {
    		$key = 'island:allplantonisland:' . $uid . ':' . $islandId;
    	}
    	else {
    		$key = 'island:allplantonisland:' . $uid;
    	}
    	$hc = Hapyfish2_Cache_HighCache::getInstance();
    	return $hc->get($key);
    }
    
    public static function getOne($uid, $id, $status = 1, $userCurrentIsland)
    {
    	$key = 'i:u:plt:' . $uid . ':' . $id;
    	$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    	$item = $cache->get($key);
    	
    	if ($item === false) {
    		try {
	    		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    		$item = $dalPlant->getOneOnIsland($uid, $id, $userCurrentIsland);
	    		if ($item) {
	    			$cache->add($key, $item);
	    		} else {
	    			return null;
	    		}
    		}catch (Exception $e) {
    			return null;
    		}
    	}
    	
    	if ($status == 1) {
	    	$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($item[1]);
	    	
	    	return array(
	        	'id' => $item[0],
	        	'cid' => $item[1],
	        	'level' => $item[2],
	        	'item_id' => $item[3],
	        	'item_type' => $item[4],
	        	'x' => $item[5],
	        	'y' => $item[6],
	        	'z' => $item[7],
	        	'mirro' => $item[8],
	        	'can_find' => $item[9],
	    	
	        	'pay_time' => $plantInfo['pay_time'],
	        	'ticket' => $plantInfo['ticket'],
	        	
	        	'start_pay_time' => $item[10],
	        	'wait_visitor_num' => $item[11],
	        	'delay_time' => $item[12],
	        	'event' => $item[13],
	        	'start_deposit' => $item[14],
	        	'deposit' => $item[15],
	    		'status' => $item[16]
	        );
    	} else {
	    	return array(
	        	'id' => $item[0],
	        	'cid' => $item[1],
	        	'level' => $item[2],
	        	'item_id' => $item[3],
	        	'item_type' => $item[4],
	    		'status' => $item[16]
	        );
    	}
    }
    
    public static function loadOne($uid, $id)
    {
		try {
	    	$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    	$item = $dalPlant->getOne($uid, $id);
	    	if ($item) {
	    		$key = 'i:u:plt:' . $uid . ':' . $id;
	    		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
	    		$cache->save($key, $item);
	    	} else {
	    		return null;
	    	}
	    	
	    	return $item;
		}catch (Exception $e) {
			err_log($e->getMessage());
			return null;
		}
    }
    
    public static function loadMultiOnIsland($uid, $ids)
    {
    	$items = array();
    	foreach ($ids as $id) {
    		$items[$id] = self::loadOne($uid, $id);
    	}
    	
    	return $items;
    }
    
    public static function updateFieldOfPlant($uid, $id, $fieldInfo)
    {
    	$plant = self::getOneOnIsland($uid, $id);
    	if ($plant) {
    		foreach ($fieldInfo as $k => $v) {
    			if(isset($plant[$k])) {
    				$plant[$k] = $v;
    			}
    		}
			return self::updateOne($uid, $id, $plant);
    	}
    	
    	return false;
    }
    
    public static function saveOne($uid, $id, $plant)
    {
		try {
    		$info = array(
				'cid' => $plant['cid'], 
    			'level' => $plant['level'], 
    			'item_id' => $plant['item_id'], 
    			'item_type' => $plant['item_type'],
				'x' => $plant['x'], 
    			'y' => $plant['y'], 
    			'z' => $plant['z'], 
    			'mirro' => $plant['mirro'], 
    			'can_find' => $plant['can_find'],
				'start_pay_time' => $plant['start_pay_time'], 
    			'wait_visitor_num' => $plant['wait_visitor_num'], 
    			'delay_time' => $plant['delay_time'], 
    			'event' => $plant['event'], 
				'start_deposit' => $plant['start_deposit'], 
    			'deposit' => $plant['deposit'],
    			'status' => $plant['status']
    		);
	    			
    		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
    		$dalPlant->update($uid, $id, $info);
    	} catch (Exception $e) {
    		err_log($e->getMessage());
    	}
    }
    
    public static function updateOne($uid, $id, $plant, $savedb = false)
    {
    	$key = 'i:u:plt:' . $uid . ':' . $id;
    	$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    	$data = array(
    		$plant['id'], $plant['cid'], $plant['level'], $plant['item_id'], $plant['item_type'],
			$plant['x'], $plant['y'], $plant['z'], $plant['mirro'], $plant['can_find'],
			$plant['start_pay_time'], $plant['wait_visitor_num'], $plant['delay_time'], $plant['event'], 
			$plant['start_deposit'], $plant['deposit'], $plant['status']
    	);
    	
    	if (!$savedb) {
    		$savedb = $cache->canSaveToDB($key, 900);
    	}
    	
    	if ($savedb) {
    		$ok = $cache->save($key, $data);
    		if ($ok) {
	    		//save to db
	    		try {
	    			$info = array(
						'cid' => $plant['cid'], 
	    				'level' => $plant['level'], 
	    				'item_id' => $plant['item_id'], 
	    				'item_type' => $plant['item_type'],
						'x' => $plant['x'], 
	    				'y' => $plant['y'], 
	    				'z' => $plant['z'], 
	    				'mirro' => $plant['mirro'], 
	    				'can_find' => $plant['can_find'],
						'start_pay_time' => $plant['start_pay_time'], 
	    				'wait_visitor_num' => $plant['wait_visitor_num'], 
	    				'delay_time' => $plant['delay_time'], 
	    				'event' => $plant['event'], 
						'start_deposit' => $plant['start_deposit'], 
	    				'deposit' => $plant['deposit'],
	    				'status' => $plant['status']
	    			);
	    			
	    			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    			$dalPlant->update($uid, $id, $info);
	    		} catch (Exception $e) {
	    			err_log($e->getMessage());
	    		}
    		}
    	} else {
    		$ok = $cache->update($key, $data);
    	}
    	
    	return $ok;
    }
    
    public static function removeOne($uid, $id)
    {
		$key = 'i:u:plt:' . $uid . ':' . $id;
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		return $cache->delete($key);
    }
    
    public static function getNewPlantId($uid)
    {
        try {
    		$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
    		return $dalUserSequence->get($uid, 'c', 1);
    	} catch (Exception $e) {
    	}
    	
    	return 0;
    }

    public static function addOne($uid, $plant, $islandId = null)
    {
    	if ( !$islandId ) {
	        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
	        $islandId = $userVO['current_island'];
    	}
    	$result = false;
    	try {
    		$id = self::getNewPlantId($uid);
    		if ($id > 0) {
    			$plant['id'] = $id;
	    		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    		$dalPlant->insert($uid, $plant);
	    		
	    		self::loadOne($uid, $id);
				//Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid);
				Hapyfish2_Island_Cache_Plant::pushOneIdInAll($uid, $id);
				
				Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
						
				if ($plant['status'] > 0 ) {
					Hapyfish2_Island_Cache_Plant::pushOneIdOnIsland($uid, $id, $islandId);
				}
	    		
	    		$result = true;
    		}
    	} catch (Exception $e) {
    		
    	}
    	
    	return $result;
    }
    
    public static function delOne($uid, $id, $status, $islandId)
    {
    	$result = false;
    	try {
    		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
    		$dalPlant->delete($uid, $id);
    		
    		self::removeOne($uid, $id);
    		//Hapyfish2_Island_Cache_Plant::reloadOnIslandIds($uid);
    		Hapyfish2_Island_Cache_Plant::popOneIdInAll($uid, $id);
    		
    		Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
    		
    		if ($status > 0) {
    			Hapyfish2_Island_Cache_Plant::pushOneIdOnIsland($uid, $id, $islandId);
    		}
    		
    		$result = true;
    	} catch (Exception $e) {

    	}
    	
    	return $result;
    }
    
    public static function upgradeCoordinate($uid, $islandId, $step = 1)
    {
		$ids = Hapyfish2_Island_Cache_Plant::getOnIslandIds($uid, $islandId);
	    if (!$ids) {
        	return false;
        }
        
	    $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:plt:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return false;
        }
        
        //check all in memory
        $cacheKeys = array();
        $empty = true;
        foreach ($data as $k => $item) {
        	if ($item !== null) {
        		$empty = false;
        		$cacheKeys[] = $k;
        	}
        }

        if (!$empty) {
        	try {
	            $dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	            $dalPlant->upgradeCoordinate($uid, $islandId, $step);
	            
        	    if (!empty($cacheKeys)) {
		        	foreach ($cacheKeys as $key) {
		        		$tmp = $data[$key];
		        		//x+1
		        		$tmp[5] += $step;
		        		//y+1
		        		$tmp[6] += $step;
		        		$cache->update($key, $tmp);
		        	}
        		}
        	} catch (Exception $e) {
        		err_log($e->getMessage());
        		return false;
        	}
        }
        
        return true;
    }
    
}