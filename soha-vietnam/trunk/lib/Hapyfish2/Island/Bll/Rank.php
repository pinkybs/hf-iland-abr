<?php

class Hapyfish2_Island_Bll_Rank
{
    public static function getBasicMC()
    {
         $key = 'mc_0';
		 return Hapyfish2_Cache_Factory::getBasicMC($key);
    }
    
	public static function getAllRank($type)
	{
		$data = array();
		$key = 'i:u:all:rank_'.$type;
        $cache = self::getBasicMC();
        $data = $cache->get($key);
		return $data;
	}
	
	public static function getTotalRank()
	{
		$key = 'i:u:new:rank';
		$cache = self::getBasicMC();
        $rank = $cache->get($key);
        return $rank;
		
	}
	
	public static function getRankChange($uid, $type)
	{
		$data = array();
		$totalRank = self::getAllRank($type);
		foreach($totalRank as $key => $value){
			if($value['uid'] == $uid ){
					$data['change'] = $value['change'];
					$data['rank'] = $value['rank'];
				}
			}
			if(!isset($data['change'])){
				$data['change'] = '';
				$data['rank'] = '1000名以后';
			}
		return $data;
	}
	
	public static function getuserRank($uid)
	{
		$data = array();
		$day = date('d');
		$day += 1;
		$end = mktime(0,30,0,date("m"), $day, date("Y"));
		$cachekey = 'i:u:user:rank:' .$uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($cachekey);
		if(empty($data)){
			try{
				/*
				$userRankdal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
				$userRankInfo = $userRankdal->getUserRankInfo($uid);
				*/
//				$usercoin = $userRankdal->getUserRankCoin($uid, $date);
//				$userchange = self::getRankChange($uid, 2);
//				$data['rank']['goldRank']['value'] = isset($usercoin[0]['num'])?$usercoin[0]['num']:0;	
//				$data['rank']['goldRank']['rank'] = $userchange['rank'];	
//				$data['rank']['goldRank']['lift'] = $userchange['change'];	
				/*
				if($userRankInfo){
					foreach($userRankInfo as $key=>$value){
						$change = self::getRankChange($uid,$value['type']);
						switch($value['type']){
							case 1:
								$data['rank']['gemRank']['value'] = $value['num'];	
								$data['rank']['gemRank']['rank'] = $change['rank'];	
								$data['rank']['gemRank']['lift'] = $change['change'];	
							break;
							case 3:
								$data['rank']['friendRand']['value'] = $value['num'];	
								$data['rank']['friendRand']['rank'] = $change['rank'];	
								$data['rank']['friendRand']['lift'] = $change['change'];	
							break;
							case 4:
								$data['rank']['activityRand']['value'] = $value['num'];	
								$data['rank']['activityRand']['rank'] = $change['rank'];	
								$data['rank']['activityRand']['lift'] = $change['change'];	
							break;	
						}
					}
				}
				*/
				$change = self::getRankChange($uid,5);
				$userinfo = Hapyfish2_Island_Bll_User::getUserInit($uid);
				$data['rank']['levelRank']['value'] = $userinfo['level'];	
				$data['rank']['levelRank']['rank'] = $change['rank'];	
				$data['rank']['levelRank']['lift'] = $change['change'];
									
				$cache->set($cachekey, $data, $end);
			} catch (Exception $e) {
				return $data;
			}
		}
		return $data;
	}
	
	public static function getRank($uid)
	{
		$rank = array();
		$userRank = self::getuserRank($uid);
		$totalRank = self::getTotalRank();
//		foreach($totalRank as $k => $v){
//			if($v){
//				foreach($v as $k1 => $v1){
//					if(in_array($v1['userID'], array('13231'))){
//						$totalRank[$k][$k1]['name'] = '***(非公开)';
//						$totalRank[$k][$k1]['head'] = 'http://xy.store.qq.com/01ca4cb4615550879958494fcd2726b3f82eab143c9897eb0';
//						$totalRank[$k][$k1]['homepage'] = '';
//					}
//				}
//			}
//		}
		$default =  array('value' => 0,'rank' => '1000名以后','lift' => '');
		//$rank['gemRank']['rankList'] = $totalRank['gemRank'] ? $totalRank['gemRank'] : array();
		//$rank['gemRank']['myRank'] = isset($userRank['rank']['gemRank']) ? $userRank['rank']['gemRank'] : $default;
		$rank['levelRank']['rankList'] = $totalRank['levelRank'] ? $totalRank['levelRank'] : array();
		$rank['levelRank']['myRank'] = isset($userRank['rank']['levelRank']) ? $userRank['rank']['levelRank'] : $default;
		return $rank;
	}
	
	
	public static function getTopTenInfo($type)
	{
		$data = array();
		$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
		$datalist = $dal->getBasicRank($type, 2, 10);
		if($datalist){
			foreach($datalist as $key => $value){
				$userinfo = Hapyfish2_Island_Bll_User::getUserInit($value['uid']);
				$puser = Hapyfish2_Platform_Cache_User::getUser($value['uid']);
				$data[$key]['rank'] = $value['rank'];
				$data[$key]['lift'] = $value['change'];
				$data[$key]['value'] = $value['num'];
				$data[$key]['userID'] = $userinfo['uid'];
				$data[$key]['name'] = $userinfo['name'];
				$data[$key]['level'] = $userinfo['level'];
				$data[$key]['head'] = $userinfo['face'];
				$data[$key]['homepage'] = 'http://my.soha.vn/'.$userinfo['name'];
			}
		}
		return $data;
	}
	
	public static function setNewRank()
	{
		$data = array();
		$key = 'i:u:new:rank';
		$cache = self::getBasicMC(); 
		/* 
		$data['gemRank'] = self::getTopTenInfo(1);
		$data['goldRank'] = self::getTopTenInfo(2);
		$data['friendRand'] = self::getTopTenInfo(3);
		$data['activityRand'] = self::getTopTenInfo(4);
		*/
		$data['levelRank'] = self::getTopTenInfo(5);
		$cache->set($key, $data);
	}
	
	public static function getGoldLog($id ,$start, $end)
	{
		$startym = date('Ym',$start);
    	$endym = date('Ym',$end);
    	$list = array();
    	$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
    	if($endym - $startym !=0){
    		$startlist = array();
    		$endlist = array();
    		$startlist = $dal->getGoldLog($id, $start, $end, $start);
    		$endlist = $dal->getGoldLog($id, $start, $end, $end);
    		$totallist = array_merge($startlist,$endlist);
    		if($totallist){
    			$r = array();
    			foreach($totallist as $key => $value){
    				if(isset($r[$value['uid']])) {
    					$r[$value['uid']]['num'] += $value['num'];
    				} else {
    					$r[$value['uid']] = $value;
    				}
    			}
    			$list = array_values($r);
    		}
    	}else{
    		$list = $dal->getGoldLog($id, $start, $end, $start);
    	}
    	return $list;
	}
	
	public static function getTopThousand($arr,  $limit)
	{
		$data = array();
		if ( !empty($arr) ){
			foreach($arr as $k => $v){
    				$volume[$k] = $v['num'];
    			}
    			array_multisort($volume, SORT_DESC, $arr);
    			$arr_new = array_chunk($arr, $limit);
    			$data = $arr_new[0];
		}
		return $data;
	}
	
	public static function updateUserRankForType($type, $start, $end)
	{
		for($i=0;$i<DATABASE_NODE_NUM;$i++){
			for($j=0;$j<=9;$j++){
				$db[$i][]= DATABASE_NODE_NUM*$j + $i;
			}
		}
		$arr = array();
		$data = array();
		$rankdal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
		
		foreach($db as $k => $v){
			foreach($v as $k1 => $v1){
				$rankdal->clearUserRank($v1 ,$type);
				switch($type){
					case 1:
						$data = self::getGoldLog($v1 ,$start, $end);
					break;
//					case 2:
//						$rankdal->clearUserCoin($v1, $date);
//					break;
//					case 3:
//						$data = $rankdal->getInviteLog($v1 ,$start, $end);
//					break;
//					case 4:
//		
//					break;	
				}
				if($data){
					foreach($data as $k2 => $v2){
						$info = array(
							'uid' => $v2['uid'],
							'type' => $type,
							'num' => $v2['num']
						);
					$rankdal -> insertUserRank($v1, $info);
					}
				}
			}
		}
	}
	
	public static function updateRankForType($type)
	{
		for($i=0;$i<DATABASE_NODE_NUM;$i++){
			for($j=0;$j<=9;$j++){
				$db[$i][]= DATABASE_NODE_NUM*$j + $i;
			}
		}
		$arr = array();
		$date = strtotime("Monday");
		$rankdal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
		foreach($db as $k => $v){
			$arr1 = array();
			foreach($v as $k1 => $v1){
				$newarr = $rankdal->getUserRankLimit($v1, $type, 1000, $date);
				$arr1 = array_merge($newarr, $arr1);
			}
			$arr1 = self::getTopThousand($arr1, 1000);
			$arr = array_merge($arr1, $arr);
		}
		$list = self::getTopThousand($arr, 1000);
		
		foreach($list as $k2 => $v2){
			if($k <= 9){
					if($k2 == 0)
					{
						$list[$k2]['rank'] = 1;
					} else {
						if($v2['num'] == $list[$k2-1]['num']){
							$list[$k2]['rank'] = $list[$k2-1]['rank'];
						} else {
							$list[$k2]['rank'] = $k2+1;
						}
					}
			}else{
				$list[$k2]['rank'] = $k2 + 1;
			}
		}
		$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
		$old = $dal->getAllRank(1, $type);
		if($old){
			$dal->deleteRankDate(1, $type);
		}
		$new = $dal->getAllRank(2, $type);
		if($new){
			$dal->updateRankNewToOld($type);
			foreach($new as $k5 => $v5){
				$oldlist[$v5['uid']] = $v5;
			}
		}
		foreach($list as $k4 => $v4){
			if(isset($oldlist[$v4['uid']])){
				$changenum =  $v4['rank'] - $oldlist[$v4['uid']]['rank'];
				if($changenum < 0){
					$change = '1/'.abs($changenum);
				} else if ($changenum > 0){
					$change = '0/'.abs($changenum);
				} else {
					$change = '0';
				}
			}else{
				$change = '1';
			}
			$list[$k4]['change'] = $change;
		}
		foreach($list as $k3 => $v3){
			$info = array(
				'uid' => $v3['uid'],
				'type'=> $type,
				'num' => $v3['num'],
				'date' => 2,
				'rank' => $v3['rank'],
				'change' => $v3['change']
			);
			$dal->insertBasicRank($info);
		}
		
		$key = 'i:u:all:rank_'.$type;
        $cache = self::getBasicMC();
        $cache->set($key, $list);
	}
	
 	public static function updateUserCoinLog($uid, $coinChange, $savedb = false)
    {
   	 	$time = time();
   	 	$endtime = strtotime("next Monday");
   	 	$data = array();
    	$key = 'i:u:week:coin:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->get($key);
        if(empty($data) || $endtime > $data['endtime']){
        	$data['num'] = 0;
        	$data['endtime'] = $endtime;
        	$cache->update($key, $data);
        }
        
        $data['num'] += $coinChange;
        if (!$savedb) {
        	$savedb = $cache->canSaveToDB($key, 900);
        }
        if ($savedb) {
        	$ok = $cache->save($key, $data);
        	if ($ok) {
        		try {
        			$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
        			$dal->updateUserCoin($uid, $endtime, $data['num']);
        		} catch (Exception $e) {
        		}
        	}
        	return $ok;
        } else {
        	return $cache->update($key, $data);
        }
    }
    
    public static function isTopTen($uid)
    {
    	$medalArray = array(0, 0, 0, 0, 0);
    	$topten = self::getTotalRank();
    	if($topten){
    		foreach($topten as $k => $v){
    			foreach($v as $k1 => $v1){
	    			switch($k){
	    				case 'levelRank':
	    					if($v1['userID'] == $uid){
		    					if($v1['rank'] <=3){
		    						$medalArray[0] = intval($v1['rank']);
		    					} else {
		    						$medalArray[0] = 4;
		    					}
	    					}
	    				break;	    				
	    				case 'gemRank':
	    					if($v1['userID'] == $uid){
		    					if($v1['rank'] <=3){
		    						$medalArray[0] = intval($v1['rank']);
		    					} else {
		    						$medalArray[0] = 4;
		    					}
	    					}
	    				break;
	    				case 'goldRank':
	    					if($v1['userID'] == $uid){
		    					if($v1['rank'] <=3){
		    						$medalArray[1] = intval($v1['rank']);
		    					} else {
		    						$medalArray[1] = 4;
		    					}
	    					}
	    				break;
	    				case 'friendRand':
	    					if($v1['userID'] == $uid){
		    					if($v1['rank'] <=3){
		    						$medalArray[2] = intval($v1['rank']);
		    					} else {
		    						$medalArray[2] = 4;
		    					}
	    					}
	    				break;
	    				case 'activityRand':
	    					if($v1['userID'] == $uid){
		    					if($v1['rank'] <=3){
		    						$medalArray[3] = intval($v1['rank']);
		    					} else {
		    						$medalArray[3] = 4;
		    					}
	    					}
	    				break;
	    			}
    			}
    		}
    	}
    	return $medalArray;
    }
    public static function updateRankWeek()
    {
    	$start = 1307980800;
    	$end = 1308758399;
    	$end1 = date('d');
    	if($end1 <= 22){
    		$end = mktime(23,59,59,date("m"), $end1-1, date("Y"));
    	}
    	//self::updateUserRankForType(1, $start, $end);
    	//self::updateRankForType(1);
    	self::updateRankForType(5);
    	self::setNewRank();
    }
    public static function setNewRankFor()
    {
    	$data = array();
		$key = 'i:u:new:rank';
		$cache = self::getBasicMC();  
		$data['gemRank'] = self::getTopTenInfo1(1);
		$data['goldRank'] = self::getTopTenInfo1(2);
		$data['friendRand'] = self::getTopTenInfo1(3);
		$data['activityRand'] = self::getTopTenInfo1(4);
		$cache->set($key,$data);
    }
    public static function getTopTenInfo1($type)
    {
    	$data = array();
		$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
		$datalist = $dal->getBasicRank($type, 2, 10);
		
		if($datalist){
			foreach($datalist as $key => $value){
				$userinfo = Hapyfish2_Island_Bll_User::getUserInit($value['uid']);
				if($type == 2 ){
					if($value['rank'] !=1){
						$data[$key-1]['rank'] = $value['rank']-1;
						$data[$key-1]['lift'] = $value['change'];
						$data[$key-1]['value'] = $value['num'];
						$data[$key-1]['userID'] = $userinfo['uid'];
						$data[$key-1]['name'] = $userinfo['name'];
						$data[$key-1]['level'] = $userinfo['level'];
						$data[$key-1]['head'] = $userinfo['face'];
						$data[$key-1]['homepage'] = $userinfo['sitLink'];
					}
				}else{
					$data[$key]['rank'] = $value['rank'];
					$data[$key]['lift'] = $value['change'];
					$data[$key]['value'] = $value['num'];
					$data[$key]['userID'] = $userinfo['uid'];
					$data[$key]['name'] = $userinfo['name'];
					$data[$key]['level'] = $userinfo['level'];
					$data[$key]['head'] = $userinfo['face'];
					$data[$key]['homepage'] = $userinfo['sitLink'];
				}
				
			}
		}
		return $data;
    }
    public static function changerank()
    {
    	for($i=1;$i<2;$i++){
	    	$dal = Hapyfish2_Island_Dal_Rank::getDefaultInstance();
			$old = $dal->getAllRank(1, $i);
			foreach($old as $k => $v){
				$oldlist[$i][$v['uid']] =  $v;
			}
			$list[$i] = $dal->getAllRank(2, $i);
	    	foreach($list as $k3 => $v3){
	    		foreach($v3 as $k4 => $v4){
	    			if(isset($oldlist[$i][$v4['uid']])){
					$changenum =  $v4['rank'] - $oldlist[$i][$v4['uid']]['rank'];
					if($changenum < 0){
						$change = '1/'.abs($changenum);
					} else if ($changenum > 0){
						$change = '0/'.abs($changenum);
					} else {
						$change = '0';
					}
				}else{
					$change = '1';
				}
				$list[$i][$k4]['change'] = $change;
	    		}
			}
			$key = 'i:u:all:rank_'.$i;
	        $cache = self::getBasicMC();
	        $cache->set($key, $list[$i]);
    	}
    	
    	foreach($list as $k5 => $v5){
    		if($v5){
	    		foreach($v5 as $k6 => $v6){
	    			if(!empty($v6)){
	    				$dal->updateRank($v6['uid'], 2, $k5, $v6['change']);
	    			}
	    			
	    		}
    		}
    	}
		return $list;
	}
}
