<?php

class Hapyfish2_Island_Cache_UserHelp
{
	public static function getHelpInfo($uid)
    {
        $key = 'i:u:help:' . $uid;
        
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);

        if ($data === false) {
        	try {
	            $dalUserHelp = Hapyfish2_Island_Dal_UserHelp::getDefaultInstance();
	            $data = $dalUserHelp->get($uid);
	            if ($data !== false) {
	            	$cache->add($key, $data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		err_log($e->getMessage());
        		return null;
        	}
        }
        
        $help = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
        if ($data != '') {
			$tmp = split(',', $data);
			foreach ($tmp as $id) {
				$help[$id] = 1;
			}
		}
        
        return $help;
    }
    
    public static function updateHelp($uid, $helpInfo)
    {
		$tmp = array();
		foreach ($helpInfo as $k => $v) {
			if ($v == 1) {
				$tmp[] = $k;
			}
		}
    	$data = join(',', $tmp);
		$info = array('help' => $data);
		if ($helpInfo[6] == 1) {
			$info['help_completed'] = 1;
		}
    	try {
            $dalUserHelp = Hapyfish2_Island_Dal_UserHelp::getDefaultInstance();
            $dalUserHelp->update($uid, $info);
            
        	$key = 'i:u:help:' . $uid;
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
        	$cache->set($key, $data);
        	
        	return true;
		}catch (Exception $e) {
			return false;
		}
    }
    
    public static function clearHelp($uid)
    {
        try {
        	$data = '';
        	$info = array('help' => $data, 'help_completed' => 0);
            $dalUserHelp = Hapyfish2_Island_Dal_UserHelp::getDefaultInstance();
            $dalUserHelp->update($uid, $info);
        	$key = 'i:u:help:' . $uid;
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
        	$cache->set($key, $data);
		}catch (Exception $e) {
			
		}
    }
    
}