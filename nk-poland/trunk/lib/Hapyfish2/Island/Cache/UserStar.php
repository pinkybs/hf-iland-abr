<?php

class Hapyfish2_Island_Cache_UserStar
{
	public static function getStarInfo($uid)
    {
        $key = 'i:u:star:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);

        if ($data === false) {
        	try {
	            $dalUserHelp = Hapyfish2_Island_Dal_UserStar::getDefaultInstance();
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
      
        $starDb = $data['star_list'];
        //0:未开通, 1:可领取, 2:已领取
        $starList = array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1);
        $starDb = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);
        if ( $data['star_list'] != '' ) {
			$tmp = split(',', $data['star_list']);
			foreach ($tmp as $id) {
				$starList[$id] = 2;
				$starDb[$id] = 1;
			}
		}

		$starList[9] = 0;
		$starList[10] = 0;
		$starList[11] = 0;
		$starList[12] = 0;

        return array('starList' => $starList, 'starDb' => $starDb);
    }
    
    public static function updateStar($uid, $starDb)
    {
		$tmp = array();
		foreach ($starDb as $k => $v) {
			if ( $v == 1 ) {
				$tmp[] = $k;
			}
		}
		
		$info = array();
		if ( !empty($tmp) ) {
			$data = join(',', $tmp);
			$info['star_list'] = $data;
		}
		
    	try {
            $dalUserHelp = Hapyfish2_Island_Dal_UserStar::getDefaultInstance();
            $dalUserHelp->update($uid, $info);
            
        	$key = 'i:u:star:' . $uid;
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
        	$cache->set($key, $info);
        	
        	return true;
		}catch (Exception $e) {
			return false;
		}
    }
    
    public static function clearStar($uid)
    {
        try {
        	$data = '';
        	$info = array('star_list' => $data);
            $dalUserHelp = Hapyfish2_Island_Dal_UserStar::getDefaultInstance();
            $dalUserHelp->update($uid, $info);
        	$key = 'i:u:star:' . $uid;
        	$cache = Hapyfish2_Cache_Factory::getMC($uid);
        	$cache->set($key, $data);
		}catch (Exception $e) {
			
		}
    }

}