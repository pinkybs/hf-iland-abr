<?php

class Hapyfish2_Island_Cache_TenLevelAward
{
    public static function isGained($uid)
    {
		$key = 'i:u:tenlvl:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        
        $data = $cache->get($key);
		if ($data === false) {
			try {
				$dal = Hapyfish2_Island_Dal_TenLevelAward::getDefaultInstance();
				$data = $dal->get($uid);
				if ($data) {
					$cache->set($key, $data);
					return true;
				}
				return false;
			} catch (Exception $e) {
				return true;
			}
		} else {
			return true;
		}
    }
    
    public static function gain($uid)
    {
    	//大圣诞树
    	$cid = 67631;
    	
		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
		if (!$plantInfo) {
			return false;
		}

		$time = time();
		$newPlant = array(
			'uid' => $uid,
			'cid' => $cid,
			'item_type' => 31,
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => $time
		);
		
		$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
    	
		if ($ok) {
			try {
				$key = 'i:u:tenlvl:' . $uid;
        		$cache = Hapyfish2_Cache_Factory::getMC($uid);
        		$cache->set($key, $time);
        		
				$dal = Hapyfish2_Island_Dal_TenLevelAward::getDefaultInstance();
				$info = array('uid' => $uid, 'create_time' => $time);
				$dal->insert($uid, $info);
			} catch (Exception $e) {
			}
			
			$feed = array(
				'uid' => $uid,
				'template_id' => 0,
				'actor' => $uid,
				'target' => $uid,
				'type' => 3,
				'title' => array('title' => '获得10级大礼包1个。'),
				'create_time' => $time
			);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
		}
		
		return $ok;
    }
}