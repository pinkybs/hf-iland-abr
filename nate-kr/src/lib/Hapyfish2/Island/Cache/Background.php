<?php

class Hapyfish2_Island_Cache_Background
{
	public static function getAll($uid)
    {
        $key = 'i:u:bg:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);

        if ($data === false) {
        	try {
	            $dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
	            $data = $dalBackground->get($uid);
	            if ($data) {
	            	$cache->add($key, $data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	}
        }
        
        $background = array();
        foreach ($data as $bg) {
        	$background[$bg[0]] = array('id' => $bg[0], 'bgid' => $bg[1], 'item_type' => $bg[2]);
        }
        
        return $background;
    }
    
    public static function getInWareHouse($uid)
    {
    	$all = self::getAll($uid);
    	$userIsland = Hapyfish2_Island_HFC_User::getUserIsland($uid);
		if (!$all || !$userIsland) {
    		return null;
    	}
    	
    	$usingIds = array(
    		$userIsland['bg_island_id'],$userIsland['bg_sky_id'],$userIsland['bg_sea_id'],$userIsland['bg_dock_id']
    	);

    	foreach ($usingIds as $id) {
    		unset($all[$id]);
    	}
    	
    	return $all;
    }
    
    public static function loadAll($uid)
    {
    	try {
    		$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
    		$data = $dalBackground->get($uid);
			if ($data) {
        		$key = 'i:u:bg:' . $uid;
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
    
    public static function getNewBackgroundId($uid)
    {
        try {
    		$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
    		return $dalUserSequence->get($uid, 'a', 1);
    	} catch (Exception $e) {
    	}
    	
    	return 0;
    }
    
    public static function addNewBackground($uid, $info)
    {
    	$result = false;
    	try {
    		$id = self::getNewBackgroundId($uid);
    		
    		if ($id > 0) {
	    		$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
	    		$dalBackground->insert($uid, $id, $info['bgid'], $info['item_type'], $info['buy_time']);
	    		
	    		self::loadAll($uid);
	    		$result = true;
    		}
    	} catch (Exception $e) {
    	}
    	
    	return $result;
    }
    
    public static function addNewBackgroundOnIsland($uid, $info)
    {
		$result = false;
    	try {
    		$id = self::getNewBackgroundId($uid);
    		
    		if ($id > 0) {
	    		$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
	    		$dalBackground->insert($uid, $id, $info['bgid'], $info['item_type'], $info['buy_time']);
	
	    		self::loadAll($uid);
	    		
	        	$data = array();
        		if ($info['item_type'] == 11) {
        			$data['bg_island'] = $info['bgid'];
        			$data['bg_island_id'] =  $id;
        		} else if ($info['item_type'] == 12) {
        			$data['bg_sky'] = $info['bgid'];
        			$data['bg_sky_id'] = $id;
        		} else if ($info['item_type'] == 13) {
        			$data['bg_sea'] = $info['bgid'];
        			$data['bg_sea_id'] = $id;
        		} else if ($info['item_type'] == 14) {
        			$data['bg_dock'] = $info['bgid'];
        			$data['bg_dock_id'] = $id;
        		}
        		
        		if (!empty($info)) {
        			Hapyfish2_Island_HFC_User::updateFieldUserIsland($uid, $data);
        		}
	    		
	    		$result = true;
    		}
    	} catch (Exception $e) {
    	}
    	
    	return $result;
    }
    
    public static function delBackground($uid, $id)
    {
		try {
			$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
    		$dalBackground->delete($uid, $id);
    		
    		self::loadAll($uid);
    		return true;
    	} catch (Exception $e) {
    		return false;
    	}
    }
    
}