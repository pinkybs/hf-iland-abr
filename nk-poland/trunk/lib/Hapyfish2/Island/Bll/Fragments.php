<?php

class Hapyfish2_Island_Bll_Fragments
{
    public static function getBasicMC()
    {
         $key = 'mc_0';
		 return Hapyfish2_Cache_Factory::getBasicMC($key);
    }
    
    public static function getAwardConfig()
    {
    	$key = 'i:award:config';
    	$cache = self::getBasicMC();
    	$config = $cache->get($key);
    	if($config === false){
    		try {
            	$dal = Hapyfish2_Island_Dal_Fragments::getDefaultInstance();
            	$config = $dal->getAwardConfig();
	            if ($config) {
	            	$cache->set($key, $config);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	} 
    	}
    	return $config;
    }
    
    public static function  getUserFragments($uid)
    {
    	$key = 'i:u:Fragment:'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
    	if($data === false){
    		try {
            	$dal = Hapyfish2_Island_Dal_Fragments::getDefaultInstance();
            	$data = $dal->getUserFragments($uid);
	            if ($data) {
	            	$cache->set($key, $data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	} 
    	}
    	return $data;
    }
    
    public static function getFragmentsInfo($uid)
    {
		$result = array();
		$award = Hapyfish2_Island_Bll_DailyAward::getAwards($uid);
		$FragmentsInfo = self::getUserFragments($uid);
		$detail = self::getDetail($uid);
		$config = self::getAwardConfig();
		$IsPadded = true;
		$IsExchange = false;
		$num = 0;
		$Paddednum = $config['need_num'] * $config['price'];
		if($FragmentsInfo ){
			if(isset($FragmentsInfo['fragment_num'])){
				$num = $FragmentsInfo['fragment_num'];
				if( $FragmentsInfo['fragment_num'] >= $config['need_num']){
					$Paddednum = 0;
					$IsExchange = true;
					$IsPadded = false;
				} else {
					$Paddednum = ($config['need_num'] - $num) * $config['price'];
				}
			}
			if(isset($FragmentsInfo['polish_num'])){
				if( $FragmentsInfo['polish_num'] >= 3){
					$IsPadded = false;
				}		
			} 
		}
		
		$result['dailyAward_items'] = $award['items'];
		$result['dailyAward_awardNum'] = $award['awardNum'];
		$result['dailyAward_seriesDays'] = $award['seriesDays'];
		$result['isFan'] = $award['isFan'];
		$result['award_left_day'] = $detail['leftday'];
		$result['Deadline_data'] = intval($config['create_time']);
		$result['Fragment_num'] = $num;
		$result['Isfriend'] = $detail['Isfriend'];
		$result['Paddednum'] = $Paddednum;
		$result['Padded_left_times'] = $detail['polish_num'];
		$result['cid'] = $config['cid'];
		$result['friend_left_num'] = $detail['friend_left_num'];
		$result['IsExchange'] = $IsExchange;
		$result['IsPadded'] = $IsPadded;
		return $result;
    }
    
    public static function getDetail($uid)
    {
    	$detail = array();
    	$updatedata = array();
    	$time = time();
    	$keys = 'i:u:Fragments：s'.$uid;
    	$keyp = 'i:u:Fragments：p'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$datas = $cache->get($keys);
    	$datap = $cache->get($keyp);
    	$config = self::getAwardConfig();
    	$FragmentsInfo = self::getUserFragments($uid);
    	$detail['leftday'] = 0;
    	$detail['Isfriend'] = false;
    	$detail['polish_num'] = 3;
    	$detail['friend_num'] = 0;
    	$detail['friend_left_num'] = $config['friend_num'];
    	if($FragmentsInfo){
    		if(isset($FragmentsInfo['friend_time'])){
    			if($time < $FragmentsInfo['friend_time']){
		    		$detail['friend_num'] = $FragmentsInfo['friend_num'];
		    		$leftday = $FragmentsInfo['friend_time'] - $time;
		    		$detail['leftday'] = floor($leftday/86400);
		    		$detail['Isfriend'] = true;
		    		$detail['friend_left_num'] = 0;
	    		} else {
		    		if(!$datas){
		    			$updatedata['friend_num'] = 0;
		    			$detail['friend_left_num'] = $config['friend_num'];
		    			$datas = true;
		    			$cache->set($keys, $datas);
		    		} else {
		    			$detail['friend_left_num'] = $config['friend_num'] - $FragmentsInfo['friend_num'];
		    		}
	    		}
    		} 
	    	if(isset($FragmentsInfo['polish_time'])){
		    	if( $time < $FragmentsInfo['polish_time']){
					$detail['polish_num'] = 3 - $FragmentsInfo['polish_num'];	    	
		    	} else {
		    		if(!$datap){
		    			$updatedata['polish_num'] = 0;
		    			$datap = true;
		    			$cache->set($keyp, $datap);
		    		} else {
		    			$detail['polish_num'] = 3 - $FragmentsInfo['polish_num'];
		    		}
		    	}
	    	}
    	}
    	
    	if(!empty($updatedata)){
    		self::updateFragments($uid, $updatedata);
    	}
    	
    	return $detail;
    }
	
    public static function updateFragments($uid, $info)
    {
    	
    	$key = 'i:u:Fragment:'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$data = $cache->get($key);
    	if(isset($info['fragment_num'])){
    		if($info['fragment_num'] >= 15){
    			$info['fragment_num'] = 15;
    		}
    	}
    	if(is_array($info)){
			foreach($info as $k => $v){
				$data[$k] = $v;
			}
    	}	
    	$dal = Hapyfish2_Island_Dal_Fragments::getDefaultInstance();
    	try{
    		$cache->set($key, $data );
    		if($dal->getUserFragments($uid)){
    			$dal->update($uid, $info);
    		}else{
    			$info['uid'] = $uid;
    			$dal->insert($uid, $info);
    		}
    		
    	} catch (Exception $e) {
        	return null;
    	}
    	return 'ok';
    }
    
    public static function updateInviteNum($uid)
    {
    	$time = time();
    	$times = $time + 30*86400;
    	$t = date('Y-m-d', $times);
    	$ts = $t.' 23:59:59';
    	$key = 'i:u:Fragments：s'.$uid;
    	$status = array();
    	$cahce = Hapyfish2_Cache_Factory::getMC($uid);
    	$userFragment = self::getUserFragments($uid);
		$awardConfig = self::getAwardConfig();
		$friend_num = isset($userFragment['friend_num'])?$userFragment['friend_num']:0;
		$updatedata['friend_num'] = $friend_num + 1;
		if(isset($userFragment['friend_time'])){
			if($time >= $userFragment['friend_time']){
				$status = $cahce->get($key);
				if(!$status){
					$updatedata['friend_num'] = 1;
					$status = true;
					$cahce->set($key, $status);
				} else {
					if($updatedata['friend_num'] == $awardConfig['friend_num']){
						$updatedata['friend_time'] = strtotime($ts);
						$cahce->delete($key);
					}
				}
			}
		}else{
			if($updatedata['friend_num'] == $awardConfig['friend_num']){
				$updatedata['friend_time'] = strtotime($ts);
				$cahce->delete($key);
			}
		}
		
		self::updateFragments($uid, $updatedata);
    }
    
    public static function exchangeFragment($uid, $type)
    {
    	$result = array();
    	$result['status'] = 1;
    	$userFragment = self::getUserFragments($uid);
		$awardConfig = self::getAwardConfig();
		$time = time();
		$com = new Hapyfish2_Island_Bll_Compensation();
		$com->setUid($uid);
		$keyp = 'i:u:Fragments：p'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		if($type == 1){
			if($userFragment['fragment_num'] < $awardConfig['need_num']){
				$result['status'] = -1;
				$result['content'] = LANG_PLATFORM_EVENT_TXT_39;
				return array('result'=>$result);
			}
			$com->setItem($awardConfig['cid'], 1);
			$title = LANG_PLATFORM_EVENT_TXT_40;
			$ok = $com->send($title);
			$updatedata['fragment_num'] = $userFragment['fragment_num'] - $awardConfig['need_num'];
			if($ok){
				self::updateFragments($uid, $updatedata);
			}
			return array('result'=>$result);
		} else {
			if($userFragment['fragment_num'] >= $awardConfig['need_num']){
				$result['status'] = -1;
				$result['content'] = LANG_PLATFORM_EVENT_TXT_41;
				return array('result'=>$result);
			}
			$Paddednum = ($awardConfig['need_num'] - $userFragment['fragment_num']) * $awardConfig['price'];
			$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			if($userGold < $Paddednum){
				$result['status'] = -1;
				$result['content'] = LANG_PLATFORM_EVENT_TXT_42;
				return array('result'=>$result);
			}
			if($userFragment['polish_num'] >=3){
				$result['status'] = -1;
				$result['content'] = LANG_PLATFORM_EVENT_TXT_43;
				return array('result'=>$result);
			}
			$com->setItem($awardConfig['cid'], 1);
			$title = LANG_PLATFORM_EVENT_TXT_44;
			$ok = $com->send($title);
			$updatedata['fragment_num'] = 0;
			$updatedata['polish_num'] = $userFragment['polish_num'] + 1;
			if(isset($userFragment['polish_time'])){
				if($time >= $userFragment['polish_time']){
					$updatedata['polish_time'] = strtotime("+ 30 days");
					$cache->delete($keyp);
				}
			}
			
			if($ok){
				self::updateFragments($uid, $updatedata);
				$goldInfo = array(
					'uid' => $uid,
					'cost' => $Paddednum,
					'summary' => '补齐兑换花费' ,
					'cid' => $awardConfig['cid'],
					'num' => 1
				);
				$ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
			}
			$result['goldChange'] = -$Paddednum;
			return array('result'=>$result);
		}
    }
    
}
