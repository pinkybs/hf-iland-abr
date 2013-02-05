<?php

class Hapyfish2_Island_Cache_Task
{
	public static function getIds($uid)
    {
        $key = 'i:u:alltask:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $tids = $cache->get($key);

        if ($tids === false) {
        	try {
	            $dalTask = Hapyfish2_Island_Dal_Task::getDefaultInstance();
	            $tids = $dalTask->getAll($uid);
	            if ($tids) {
	            	$cache->add($key, $tids);
	            } else {
	            	return null;
	            }
        	}catch (Exception $e) {
        		return null;
        	}
        }
        
        return $tids;
    }
    
    public static function loadIds($uid)
    {
		try {
	    	$dalTask = Hapyfish2_Island_Dal_Task::getDefaultInstance();
			$tids = $dalTask->getAll($uid);
	
			if ($tids) {
				$key = 'i:u:alltask:' . $uid;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, $tids);
			} else {
				return null;
			}
			
			return $tids;
		}catch (Exception $e) {
			return null;
		}
    }
    
    public static function insertId($uid, $tid)
    {
        $key = 'i:u:alltask:' . $uid;
        
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $tids = $cache->get($key);

        if ($tids === false) {
			return false;
        }
        
        $tids[] = $tid;
        
        return $cache->replace($key, $tids);
    }
    
    public static function isCompletedTask($uid, $tid)
    {
    	$tids = self::getIds($uid);
    	if (is_array($tids) && in_array($tid, $tids)) {
    		return true;
    	}
    	
    	return false;
    }
    
    public static function completeTask($uid, $tid, $time = null)
    {
    	if (!$time) {
    		$time = time();
    	}
    	
    	$completed = false;
    	try {
    		$dalTask = Hapyfish2_Island_Dal_Task::getDefaultInstance();
    		$dalTask->insert($uid, $tid, $time);
			self::loadIds($uid);
    		$completed = true;
    	}catch (Exception $e) {
    	}
    	
    	return $completed;
    }
    
    /**
     * get task info by title
     *
     * @return array
     */
    public static function getUserOpenTask($uid)
    {
        $key = 'i:u:openTask2:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $info = $cache->get($key);
		if ($info === false) {
			$info = array();
		}
		
		return $info;
    }

    /**
     * update user open task list
     *
     * @return array
     */
    public static function updateUserOpenTask($uid, $openTask)
    {
	    try {
	        $key = 'i:u:openTask2:' . $uid;
	        $cache = Hapyfish2_Cache_Factory::getMC($uid);
	        $data = $cache->get($key);
	        if ( $data === false ) {
	        	$data = array();
	        }
	        foreach ( $openTask as $task ) {
	        	if ( !in_array($task, $data) ) {
	        		$data[] = $task;
	        	}
	        }
	        
			$cache->set($key, $data);
		}catch (Exception $e) {
		}		
    }
    
}