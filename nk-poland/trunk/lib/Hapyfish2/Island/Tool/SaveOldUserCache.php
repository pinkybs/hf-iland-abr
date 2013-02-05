<?php

class Hapyfish2_Island_Tool_SaveOldUserCache
{
    //设施缓存
	public static function savePlant($uid)
    {
    	$ids = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
    	
        if (!$ids) {
			return;
        }
        
        $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:plt:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return;
        }
        
        foreach ($data as $k => $item) {
        	if ($item != null) {
        		$savedb = $cache->canSaveToDB($k, 1);
        		try {
        			$id = $item[0];
	    			$info = array(
						'cid' => $item[1], 
	    				'level' => $item[2], 
	    				'item_id' => $item[3], 
	    				'item_type' => $item[4],
						'x' => $item[5], 
	    				'y' => $item[6], 
	    				'z' => $item[7], 
	    				'mirro' => $item[8], 
	    				'can_find' => $item[9],
						'start_pay_time' => $item[10], 
	    				'wait_visitor_num' => $item[11], 
	    				'delay_time' => $item[12], 
	    				'event' => $item[13], 
						'start_deposit' => $item[14], 
	    				'deposit' => $item[15],
	    				'status' => $item[16]
	    			);
	    			
	    			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
	    			$dalPlant->update($uid, $id, $info);
	    		} catch (Exception $e) {
	    			err_log($e->getMessage());
	    		}
        	}
        }
        
        unset($ids);
        unset($data);
    }
    
    //装饰缓存
    public static function saveBuilding($uid)
    {
    	$ids = Hapyfish2_Island_Cache_Building::getAllIds($uid);
        
        if (!$ids) {
        	return;
        }
        
        $keys = array();
        foreach ($ids as $id) {
        	$keys[] = 'i:u:bld:' . $uid . ':' . $id;
        }
        
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->getMulti($keys);
        
        if ($data === false) {
        	return;
        }
        
        foreach ($data as $k => $item) {
        	if ($item != null) {
        		$savedb = $cache->canSaveToDB($k, 1);
        		if ($savedb) {
	        		try {
	        			$id = $item[0];
		    			$info = array(
							'id' => $item[0], 
		    				'cid' => $item[1], 
							'x' => $item[2], 
		    				'y' => $item[3], 
		    				'z' => $item[4], 
		    				'mirro' => $item[5], 
		    				'item_type' => $item[6],
		    				'status' => $item[7]
		    			);
		    			
		    			$dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
		    			$dalBuilding->update($uid, $id, $info);
		    		} catch (Exception $e) {
		    			err_log($e->getMessage());
		    		}
        		}
        	}
        }
        
        unset($ids);
        unset($data);
    }
    
    //成就缓存
    public static function saveAchievement($uid)
    {
    	$key = 'i:u:ach:' . $uid;
    	$cache = Hapyfish2_Cache_Factory::getHFC($uid);
    	$data = $cache->get($key);
    	if ($data === false) {
    		return false;
    	}
    	
        if (!isset($data[17])) {
        	$data[17] = 0;
        }
		if (!isset($data[18])) {
        	$data[18] = 0;
        }
		if (!isset($data[19])) {
        	$data[19] = 0;
        }
    	
    	$savedb = $cache->canSaveToDB($key, 1);
    	if ($savedb) {
    		try {
				$info = array(
    				'num_1' => $data[0], 'num_2' => $data[1], 'num_3' => $data[2],
					'num_4' => $data[3], 'num_5' => $data[4], 'num_6' => $data[5],
    				'num_7' => $data[6], 'num_8' => $data[7], 'num_9' => $data[8],
    				'num_10' => $data[9], 'num_11' => $data[10], 'num_12' => $data[11],
    				'num_13' => $data[12], 'num_14' => $data[13], 'num_15' => $data[14],
    				'num_16' => $data[15], 'num_17' => $data[16], 'num_18' => $data[17],
					'num_19' => $data[18], 'num_20' => $data[19]
				);
            	$dalAchievement = Hapyfish2_Island_Dal_Achievement::getDefaultInstance();
            	$dalAchievement->update($uid, $info);
            	
            	unset($info);
        	} catch (Exception $e) {
        	}
    	}
    	
    	unset($data);
    }
    
    //卡片缓存
    public static function saveCard($uid)
    {
        $key = 'i:u:card:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$data = $cache->get($key);
		
		if ($data === false) {
			return false;
		}
		
		$savedb = $cache->canSaveToDB($key, 1);
		if ($savedb) {
			try {
	        	$dalCard = Hapyfish2_Island_Dal_Card::getDefaultInstance();
        		foreach ($data as $cid => $item) {
        			if ($item[1]) {
        				$dalCard->update($uid, $cid, $item[0]);
        			}
        		}
        	} catch (Exception $e) {
        		
        	}
		}
		
		unset($data);
    }
    
    //码头缓存
    public static function saveDock($uid)
    {
		$dock = Hapyfish2_Island_HFC_Dock::getUserDock($uid, $positionCount);
		if (!$dock) {
			return;
		}
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		foreach ($dock as $positonId => $positonInfo) {
			$key = 'i:u:dock:' . $uid . ':' . $positonId;
			$savedb = $cache->canSaveToDB($key, 1);
		    if ($savedb) {
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
	    			unset($info);
	    		} catch (Exception $e) {
	    			
	    		}
    		}
		}
		
		unset($dock);
    }
    
    public static function saveCoin($uid)
    {
    	$key = 'i:u:coin:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $userCoin = $cache->get($key);
        if ($userCoin === false || $userCoin == 0) {
        	return;
        }
        $savedb = $cache->canSaveToDB($key, 1);
        if ($savedb) {
        	try {
        		$info = array('coin' => $userCoin);
        		$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
        		$dalUser->update($uid, $info);
        	} catch (Exception $e) {
        	}
        }
    }
    
    public static function saveExp($uid)
    {
    	$key = 'i:u:exp:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $userExp = $cache->get($key);
        if ($userExp === false || $userExp == 0) {
        	return;
        }
        $savedb = $cache->canSaveToDB($key, 1);
        if ($savedb) {
        	try {
        		$info = array('exp' => $userExp);
        		$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
        		$dalUser->update($uid, $info);
        	} catch (Exception $e) {
        	}
        }
    }
    
    public static function saveUserIsland($uid)
    {
        $key = 'i:u:island:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$data = $cache->get($key);
        if ($data === false) {
			return;
        }
        
        $savedb = $cache->canSaveToDB($key, 1);
        if ($savedb) {
        	try {
        		$info = array(
        			'praise' => $data[0], 'position_count' => $data[1], 
        			'bg_island' => $data[2], 'bg_island_id' => $data[3],
					'bg_sky' => $data[4], 'bg_sky_id' => $data[5], 
        			'bg_sea' => $data[6], 'bg_sea_id' => $data[7], 
					'bg_dock' => $data[8], 'bg_dock_id' => $data[9]
        		);
        		$dalUserIsland = Hapyfish2_Island_Dal_UserIsland::getDefaultInstance();
        		$dalUserIsland->update($uid, $info);
        		unset($info);
        	} catch (Exception $e) {
        	}
        }
        
        unset($data);
    }
    
    public static function saveUserTitle($uid)
    {
		$key = 'i:u:title:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$data = $cache->get($key);
        if ($data === false) {
			return;
        }
        
        $savedb = $cache->canSaveToDB($key, 1);
        if ($savedb) {
            try {
        		$info = array(
        			'title' => $data[0], 'title_list' => $data[1]
        		);
        		$dalUserTitle = Hapyfish2_Island_Dal_UserTitle::getDefaultInstance();
        		$dalUserTitle->update($uid, $info);
        	} catch (Exception $e) {
        	}
        }
    }
	
	public static function saveOne($uid)
    {
		self::savePlant($uid);
		self::saveBuilding($uid);
		self::saveDock($uid);
		
		self::saveAchievement($uid);
		self::saveCard($uid);
		
		self::saveCoin($uid);
		self::saveExp($uid);
		self::saveUserIsland($uid);
		self::saveUserTitle($uid);
    }
    
    public static function doSaveRange($dbId, $tbId, $begin, $end)
    {
        try {
    		$dalToolUser = Hapyfish2_Island_Tool_User::getDefaultInstance();
    		$data = $dalToolUser->getRange($dbId, $tbId, $begin, $end);
    		if ($data) {
    			$ids = array();
    			foreach ($data as $row) {
    				$uid = $row['uid'];
    				$ids[] = $uid;
    				self::saveOne($uid);
    			}
    			$logname = 'saveusercache-' . $begin . '-' . $end . '.log';
    			self::log($logname, join("\n", $ids));
    		}
    	} catch (Exception $e) {
    		
    	}
    }
    
    public static function log($filename, $msg)
    {
    	$file = LOG_DIR . '/' . $filename;
    	file_put_contents($file, $msg, FILE_APPEND);
    }

}