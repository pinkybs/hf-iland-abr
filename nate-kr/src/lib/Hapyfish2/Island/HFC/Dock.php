<?php

class Hapyfish2_Island_HFC_Dock
{
	public static function getUserDock($uid, &$positionCount)
    {
        if ($positionCount == null) {
	    	$userIsland = Hapyfish2_Island_HFC_User::getUserIsland($uid);
	        if ($userIsland === null) {
	        	return null;
	        }
	        
	        $positionCount = $userIsland['position_count'];
	        if ($positionCount == 0) {
	        	return array();
	        }
        }
        
        $keys = array();
        for($i = 1; $i <= $positionCount; $i++) {
        	$keys[] = 'i:u:dock:' . $uid . ':' . $i;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return null;
        }
        
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
	            $dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
	            $result = $dalDock->get($uid);
	            if ($result) {
	            	$data = array();
	            	foreach ($result as $item) {
	            		$key = 'i:u:dock:' . $uid . ':' . $item[0];
	            		$item[4] = $item[3];
	            		$item[5] = 0;
	            		$item[6] = 0;
	            		$item[7] = 0;
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
        		$tmp = explode(':', $key);
        		$data[$key] = self::loadUserDockPosition($uid, $tmp[4]);
        	}
        }
        
        $dock = array();
        $shipInfoList = Hapyfish2_Island_Cache_BasicInfo::getShipList();
        foreach ($data as $item) {
        	$dock[$item[0]] = array(
        	    'position_id' => $item[0],
    			'ship_id' => $item[1],
    			'receive_time' => $item[2],
    			'start_visitor_num' => $item[3],
    			'remain_visitor_num' => $item[4],
    			'speedup' => $item[5],
    			'speedup_time' => $item[6],
    			'is_usecard_one' => $item[7],
        		'wait_time' => $shipInfoList[$item[1]]['wait_time']
        	);
        }
        
        return $dock;
    }
    
    public static function loadUserDockPosition($uid, $positionId)
    {
		try {
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$data = $dalDock->getPosition($uid, $positionId);
    		if ($data) {
    			$data[4] = $data[3];
    			$data[5] = 0;
    			$data[6] = 0;
    			$data[7] = 0;
    			$key = 'i:u:dock:' . $uid . ':' . $positionId;
    			$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    			$cache->add($key, $data);
    			return $data;
    		} else {
    			return null;
    		}
    	} catch (Exception $e) {
    		return null;
    	}
    }
    
    public static function getUserDockPosition($uid, $positionId)
    {
    	$key = 'i:u:dock:' . $uid . ':' . $positionId;
    	$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    	$data = $cache->get($key);
    	
    	if ($data === false) {
    		try {
	    		$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
	    		$data = $dalDock->getPosition($uid, $positionId);
	    		if ($data) {
	    			$data[4] = $data[3];
	    			$data[5] = 0;
	    			$data[6] = 0;
	    			$data[7] = 0;
	    			$cache->add($key, $data);
	    		} else {
	    			return null;
	    		}
    		} catch (Exception $e) {
    			return null;
    		}
    	}
    	
    	$shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($data[1]);
    	
    	//position_id,ship_id,receive_time,start_visitor_num,
    	//remain_visitor_num,speedup,speedup_time,is_usecard_one
    	return array(
    		'position_id' => $data[0],
    		'ship_id' => $data[1],
    		'receive_time' => $data[2],
    		'start_visitor_num' => $data[3],
    		'remain_visitor_num' => $data[4],
    		'speedup' => $data[5],
    		'speedup_time' => $data[6],
    		'is_usecard_one' => $data[7],
    		'wait_time' => $shipInfo['wait_time']
    	);
    }
    
    public static function updateUserDockPosition($uid, $positonId, $positonInfo)
    {
    	$key = 'i:u:dock:' . $uid . ':' . $positonId;
    	$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    	$data = array(
    		$positonInfo['position_id'], $positonInfo['ship_id'], $positonInfo['receive_time'], $positonInfo['start_visitor_num'],
    		$positonInfo['remain_visitor_num'], $positonInfo['speedup'], $positonInfo['speedup_time'], $positonInfo['is_usecard_one']
    	);
    	
        $savedb = $cache->canSaveToDB($key, 900);
    	
    	if ($savedb) {
    		$ok = $cache->save($key, $data);
    		if ($ok) {
	    		//save to db
	    		try {
	    			$info = array(
						'position_id' => $positonInfo['position_id'], 
	    				'ship_id' => $positonInfo['ship_id'], 
	    				'receive_time' => $positonInfo['receive_time'], 
	    				'start_visitor_num' => $positonInfo['start_visitor_num'],
						'remain_visitor_num' => $positonInfo['remain_visitor_num'], 
	    				'speedup' => $positonInfo['speedup'], 
	    				'speedup_time' => $positonInfo['speedup_time'], 
	    				'is_usecard_one' => $positonInfo['is_usecard_one']
	    			);
	    			
	    			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
	    			$dalDock->update($uid, $positonId, $info);
	    		}catch (Exception $e) {
	    			
	    		}
    		}
    	} else {
    		$ok = $cache->update($key, $data);
    	}
    	
    	return $ok;
    }
    
    public static function expandPosition($uid, $positionId, $visitNum)
    {
    	try {
    		$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
    		$ok = $dalDock->expandPosition($uid, $positionId, $visitNum);
    		
    		if (!$ok) {
    			return false;
    		}
    		
    		$key = 'i:u:dock:' . $uid . ':' . $positionId;
    		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    		$data = array($positionId, 1, time(), $visitNum, $visitNum, 0, 0, 0);
    		$cache->add($key, $data);
    		
    		//update cache info
    		Hapyfish2_Island_Cache_Dock::reloadUnlockShipCount($uid);
    		
    		//
    		Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, array('position_count' => $positionId), true);
    		return true;
    	} catch (Exception $e) {
    		return false;
    	}
    }
    
}