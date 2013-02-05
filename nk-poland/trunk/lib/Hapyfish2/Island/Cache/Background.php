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
    		$userIsland['bg_island_id'],$userIsland['bg_sky_id'],$userIsland['bg_sea_id'],$userIsland['bg_dock_id'],
    		$userIsland['bg_island_id_2'],$userIsland['bg_sky_id_2'],$userIsland['bg_sea_id_2'],$userIsland['bg_dock_id_2'],
    		$userIsland['bg_island_id_3'],$userIsland['bg_sky_id_3'],$userIsland['bg_sea_id_3'],$userIsland['bg_dock_id_3'],
    		$userIsland['bg_island_id_4'],$userIsland['bg_sky_id_4'],$userIsland['bg_sea_id_4'],$userIsland['bg_dock_id_4']
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
    
    public static function addNewBackgroundOnIsland($uid, $info, $userCurrentIsland)
    {
		$result = false;
    	try {
    		$id = self::getNewBackgroundId($uid);
    		
    		if ($id > 0) {
	    		$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
	    		$dalBackground->insert($uid, $id, $info['bgid'], $info['item_type'], $info['buy_time']);
	
	    		self::loadAll($uid);
	    		
	            switch ( $userCurrentIsland ) {
	            	case 2 :
	            		$island = 'bg_island_2';
	            		$islandId = 'bg_island_id_2';
	            		$sky = 'bg_sky_2';
	            		$skyId = 'bg_sky_id_2';
	            		$sea = 'bg_sea_2';
	            		$seaId = 'bg_sea_id_2';
	            		$dock = 'bg_dock';
	            		$dockId = 'bg_dock_id';
	            		break;
	            	case 3 :
	            		$island = 'bg_island_3';
	            		$islandId = 'bg_island_id_3';
	            		$sky = 'bg_sky_3';
	            		$skyId = 'bg_sky_id_3';
	            		$sea = 'bg_sea_3';
	            		$seaId = 'bg_sea_id_3';
	            		$dock = 'bg_dock';
	            		$dockId = 'bg_dock_id';
	            		break;
	            	case 4 :
	            		$island = 'bg_island_4';
	            		$islandId = 'bg_island_id_4';
	            		$sky = 'bg_sky_4';
	            		$skyId = 'bg_sky_id_4';
	            		$sea = 'bg_sea_4';
	            		$seaId = 'bg_sea_id_4';
	            		$dock = 'bg_dock';
	            		$dockId = 'bg_dock_id';
	            		break;
	            	default :
	            		$island = 'bg_island';
	            		$islandId = 'bg_island_id';
	            		$sky = 'bg_sky';
	            		$skyId = 'bg_sky_id';
	            		$sea = 'bg_sea';
	            		$seaId = 'bg_sea_id';
	            		$dock = 'bg_dock';
	            		$dockId = 'bg_dock_id';
	            		break;
	            }
            
	    		$data = array();
        		if ($info['item_type'] == 11) {
        			$data[$island] = $info['bgid'];
        			$data[$islandId] =  $id;
        		} else if ($info['item_type'] == 12) {
        			$data[$sky] = $info['bgid'];
        			$data[$skyId] = $id;
        		} else if ($info['item_type'] == 13) {
        			$data[$sea] = $info['bgid'];
        			$data[$seaId] = $id;
        		} else if ($info['item_type'] == 14) {
        			$data[$dock] = $info['bgid'];
        			$data[$dockId] = $id;
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