<?php

class Hapyfish2_Island_Bll_SearchFriend
{
	
    public static function getBasicMC(){
         $key = 'mc_0';
		 return Hapyfish2_Cache_Factory::getBasicMC($key);
    }
	
	public static function addToFriendSearch($uid)
	{
		$key = 'i:u:searchFriend';
		$cache = self::getBasicMC();
		$time = time();
		$data = array();
		$m = date('i',$time);
		if($m <= 15){
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			if($userLevelInfo['level'] > 10){
				$data = $cache->get($key);
				$count = count($data);
				if(is_array($data)){
					if(!in_array($uid, $data)){
						if($count == 100){
							array_splice($data, 0, 1);
						}
						array_push($data, $uid);
					}
				}else{
					$data[] = $uid;
				}
				$cache->set($key, $data);
			}
		}
	}
    public static function getSearchFriend()
    {
    	$data = array();
    	$key = 'i:u:searchFriend';
		$cache = self::getBasicMC();
		$data = $cache->get($key);
		$friendList = array();
		if(count($data)>10){
			 $rand_keys = array_rand($data, 10);
			 foreach($rand_keys as $k => $v){
		     	$friendList[] =  $data[$v];
			}
		}else{
			$friendList = $data;
		}
		if($friendList){
			foreach($friendList as $k1=>$v1){
				$user = Hapyfish2_Platform_Bll_User::getUser($v1);
				$userVO = Hapyfish2_Island_HFC_User::getUserVO($v1);
				$puser = Hapyfish2_Platform_Cache_User::getUser($v1);
				$list[$k1]['uid'] = $v1;
				$list[$k1]['name'] = $user['name'];
				$list[$k1]['face'] = $user['figureurl'];
				$list[$k1]['islandLevel'] = $userVO['level'];
				$list[$k1]['link'] = 'http://jianghu.taobao.com/u/'.base64_encode($puser['puid']).'/front.htm';
			}
		}
		return $list;
    }
    public static function updateActivity($uid)
    {
    	$cachekey = 'i:u:week:activity_' .$uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$time = strtotime("+1 week");
		$cache->set($cachekey, 1, $time);
    }
    
    public static function getUserActivity($uid)
    {
    	$cachekey = 'i:u:week:activity_' .$uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($cachekey);
		if($data == 1){
			$Activity = 1;
		}else{
			$Activity = 0;
		}
		return $Activity;
    }
}