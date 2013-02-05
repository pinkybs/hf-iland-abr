<?php

class Hapyfish2_Island_Bll_Robot
{
	 public static function getBasicMC()
    {
         $key = 'mc_0';
		 return Hapyfish2_Cache_Factory::getBasicMC($key);
    }
	
	public static function addFriend($uid)
	{
		$nums = range(1,500);
		$robotkey = array_rand($nums, 3);
		foreach($robotkey as $k => $v){
			$robot[$k] = 's'.$v;
		}
		$fid = implode(',', $robot);
		$robot = self::getRobotFriend($uid);
		if(!$robot){
			$dal = Hapyfish2_Island_Dal_Robot::getDefaultInstance();
				 $dal->addRobot($uid, $fid);
		}
	}
	
	public static function getRobotFriend($uid)
	{
		$key = 'i:u:robot:f:'.$uid;
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
        $data = $cache->get($key);
        if ($data === false) {
        	try {
            	$dal = Hapyfish2_Island_Dal_Robot::getDefaultInstance();
            	$data = $dal->getRobotFriend($uid);
	            if ($data) {
	            	$cache->save($key, $data);
	            } else {
	            	return null;
	            }
        	} catch (Exception $e) {
        		return null;
        	} 
        }
        return $data;
	}
	
	public static function getFriendList($uid)
	{
		$robot = self::getRobotFriend($uid);
		$idList = explode(',', $robot);
		$info = array();
		foreach($idList as $k => $v){
			$robotinfo = self::getRobotInfo($v);
			if($robotinfo){
				$rb = json_decode($robotinfo);
				$info[$k]['uid'] = $rb->islandVo->uid;
				$info[$k]['name'] = $rb->islandVo->uname;
				$info[$k]['face'] = $rb->islandVo->face;
				$info[$k]['exp'] = $rb->islandVo->exp;
				$info[$k]['level'] = $rb->islandVo->level;
				$info[$k]['canSteal'] = 0;
			}
		}
		return $info;
	}
	
	public static function getRobotInfo($sid)
	{
		$h = date('G');
		$end = mktime($h+1,0,0,date("m"),date("d"),date("Y"));
		$cache = self::getBasicMC();
		$key = 'i:u:s:r:i:'.$sid;
		$data = $cache->get($key);
		if(empty($data)){
			$data = self::getRobotFile($sid);
			if(!empty($data)){
				$cache->set($key, $data, $end);
			}
		}
		return $data;
	}
	
	public static function getRobotFile($sid)
	{
		$data = array();
		$file = TEMP_DIR . '/robot/' . $sid . '.cache';
		if (is_file($file)) {
			$data = file_get_contents($file);
		}
		return $data;
	}
	
  public static function moochPlant($uid, $ownerUid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        //check is friend
        $isFriend = self::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }

        $moochInfo = self::getMoochPlant($ownerUid, $itemId);

        if (!empty($moochInfo) && in_array($uid, $moochInfo)) {
            $result['content'] = 'serverWord_144';
            return $result;
        }
        //insert plant mooch info
        $moochInfo[] = $uid;
		self::smoochPlant($ownerUid, $itemId, $moochInfo);

        $userPlant = self::getOnePlant($ownerUid, $itemId );
        if (empty($userPlant)) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo) {
        	return $result;
        }
        $now = time();

		//check plant deposit
		$safeCoinNum = $userPlant['startDeposit'] * $plantInfo['safe_coin_num'];
		$safeCoinNum = round($safeCoinNum);
		if ($userPlant['deposit'] <= $safeCoinNum) {
			$result['content'] = 'serverWord_145';
			return $result;
		}

        try {
            $moochCoin = rand(5, 100);
            $remainCoin = $userPlant['deposit'] - $moochCoin;
            $moochCoin = $remainCoin >= $safeCoinNum ? $moochCoin : $userPlant['deposit'] - $safeCoinNum;

            //
            $userPlant['deposit'] = $userPlant['deposit'] - $moochCoin;
            self::updateOne($ownerUid, $itemId, $userPlant);

            $addExp = 2;
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}

            //add user coin and exp
			Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $addExp, $moochCoin);

	        $result['status'] = 1;
            $result['expChange'] = $addExp;
            $result['coinChange'] = $moochCoin;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }

        try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_8', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_8', 1);
        } catch (Exception $e) {

        }

    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
    	    if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}


        return $result;
    }
    
    public static function isFriend($uid, $fid)
    {
    	$robot = self::getRobotFriend($uid);
    	$idList = explode(',', $robot);
    	if(in_array($fid, $idList)){
    		return true;
    	} else {
    		return false;
    	}
    	
    }
    
    public static function getMoochPlant($ownerUid, $itemId)
    {
    	$key = 'i:u:mooch:plt:' . $ownerUid . ':' . $itemId;
    	$uid = substr($ownerUid, 1);
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		if ($data === false) {
			return array();
		}
		return $data;
    }
    
    public static function smoochPlant($uid, $plantId, $data)
    {
    	$h = date('G');
		$end = mktime($h+1,0,0,date("m"),date("d"),date("Y"));
        $key = 'i:u:mooch:plt:' . $uid . ':' . $plantId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $data, $end);
    }
    
    public static function getOnePlant($uid, $itemid)
    {
    	$robotinfo = self::getRobotInfo($uid);
    	$info = json_decode($robotinfo);
    	$list = array();
    	if($info){
    		$plantlist = $info->islandVo->buildings;
    		foreach($plantlist as $k => $v){
    			if($itemid == substr($v->id, 0, -2)){
    				$list = (array)$v;
    			}
    		}
    	}
    	return $list;
    }
    
    public static function updateOne($uid, $id, $plant)
    {
    	$cache = self::getBasicMC();
		$key = 'i:u:s:r:i:'.$uid;
		$datas = $cache->get($key);
		$info = json_decode($datas);
		if($info){
			$plantlist = $info->islandVo->buildings;
			foreach($plantlist as $k => $v){
	    		if(substr($v->id, 0, -2) == $id){
	    			$plant['hasSteal'] = 1;
	    			$plants = new stdClass();
	    			foreach($plant as $k1 => $v1){
	    				$plants->$k1 = $v1;
	    			}
	    			$plantlist[$k] = $plants;
	    		}
			}
			$info->islandVo->buildings = $plantlist;
		}
    	$h = date('G');
		$end = mktime($h+1,0,0,date("m"),date("d"),date("Y"));
		$datas = json_encode($info);
		$cache->set($key, $datas, $end);
    }
    
    public static function moochboat($uid, $ownerUid, $positionId)
    {
    	$result = array('status' => -1);
    	
        //check is friend
     	$isFriend = self::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }
        
        $moochInfo = self::getMoochShip($ownerUid, $positionId);
        if (!empty($moochInfo) && in_array($uid, $moochInfo)) {
            $result['content'] = 'serverWord_102';
            return array('result' => $result);
        }
        
        $positionInfo = self::getUserDockPosition($ownerUid, $positionId);
        
        //get ship info 
        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($positionInfo['boatId']);

        $now = time();
        //get visitor arrive time
        $remainNum = $positionInfo['visitorNum'] - $shipInfo['safe_visitor_num'];
        if ($remainNum <= 0) {
            $result['content'] = 'serverWord_132';
            return array('result' => $result);
        }
        
    	$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		if (!$userLevelInfo) {
			return array('result' => $resultVo);
		}
		
    	$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($userLevelInfo['island_level']);
		if (!$islandLevelInfo) {
			return array('result' => $resultVo);
		}
        
        $plantVO = Hapyfish2_Island_Bll_Plant::getAllOnIslandNoMooch($uid, true);
        $currently_visitor = $plantVO['visitorNum'];
        if ($islandLevelInfo['max_visitor'] <= $currently_visitor) {
            $result['content'] = 'serverWord_133';
            return array('result' => $result);
        }

            $moochNum = 1;
            $helpNum = rand(1, 2);

        if ($moochNum > $remainNum) {
            $moochNum = $remainNum;
            $helpNum = 0;
        }
        else if (($moochNum + $helpNum) > $remainNum) {
        	$helpNum = $remainNum - $moochNum;
        }
        
        if ($moochNum + $currently_visitor > $islandLevelInfo['max_visitor']) {
            $moochNum = $islandLevelInfo['max_visitor'] - $currently_visitor;
        }
        
        $allNum = $moochNum + $helpNum;

        //
        $moochInfo[] = $uid;
        self::moochShip($ownerUid, $positionId, $moochInfo);
        try {
            $visitorsAry = self::moochVisitors($uid, $moochNum, true);
            $helpAry = array();
			//update onwer position info
			$positionInfo['visitorNum'] -= $allNum;
			self::updateUserDockPosition($ownerUid, $positionId, $positionInfo);

            $addExp = 2;
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);

            $result['status'] = 1;
            $result['expChange'] = $addExp;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }
        
        try {
        	Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByMultiField($uid, array('num_5' => 1, 'num_7' => $moochNum));
        	
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_5', 1);
        } catch (Exception $e) {

        }
		
        //check level up
        try {
	        $levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
	        $result['levelUp'] = $levelUp['levelUp'];
	        $result['islandLevelUp'] = $levelUp['islandLevelUp'];
            if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
        } catch (Exception $e) {

        }

        if (empty($visitorsAry)) {
            $visitorsAry = array();
        }
            
    
        $resultVo = array('result' => $result, 'visitors' => $visitorsAry, 'friendVisitors' => $helpAry);
        
        return $resultVo;
    }
    
    public static function getMoochShip($ownerUid, $positionId)
    {
    	$key = 'i:u:mooch:ship:' . $ownerUid . ':' . $positionId;
    	$uid = substr($ownerUid, 1);
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		if ($data === false) {
			return array();
		}
		
		return $data;
    }
    
	 public static function moochShip($uid, $postionId, $data)
    {
    	$h = date('G');
		$end = mktime($h+1,0,0,date("m"),date("d"),date("Y"));
       	$key = 'i:u:mooch:ship:' . $uid . ':' . $postionId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $data, $end);
    }
    
    public static function getUserDockPosition($ownerUid, $positionId)
    {
    	$robotinfo = self::getRobotInfo($ownerUid);
    	$info = json_decode($robotinfo);
    	$list = array();
    	if($info){
    		$boatlist = $info->dockVo->boatPositions;
    		foreach($boatlist as $k => $v){
    			if($positionId == $v->id){
    				$list = (array)$v;
    			}
    		}
    	}
    	return $list;
    }
    
    public static function updateUserDockPosition($ownerUid, $positionId, $positionInfo)
    {
    	$cache = self::getBasicMC();
		$key = 'i:u:s:r:i:'.$ownerUid;
		$datas = $cache->get($key);
		$info = json_decode($datas);
		if($info){
			$boatlist = $info->dockVo->boatPositions;
			foreach($boatlist as $k => $v){
	    		if($v->id == $positionId){
	    			$positionInfo['canSteal'] = false;
	    			$positionInfos = new stdClass();
	    			foreach($positionInfo as $k1 => $v1){
	    				$positionInfos->$k1 = $v1;
	    			}
	    			$boatlist[$k] = $positionInfos;
	    		}
			}
			$info->dockVo->boatPositions = $boatlist;
		}
    	$h = date('G');
		$end = mktime($h+1,0,0,date("m"),date("d"),date("Y"));
		$datas = json_encode($info);
		$cache->set($key, $datas, $end);
    }
    
  public static function moochVisitors($uid, $number, $highcache = true)
    {
        $visitorsAry = array();
        $now = time();

		if ($highcache) {
			$data = Hapyfish2_Island_HFC_Plant::getAllOnIslandFromHighCache($uid, 1);
		} else {
			$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, 1);
		}
		$plantInfo1 = $data['plants'];
        for($j=0, $jCount = count($plantInfo1); $j < $jCount; $j++) {
        	if ( $plantInfo1[$j]['can_find'] != 1 ) {
        		unset($plantInfo1[$j]);
        	} else {
        		$plantInfo1[$j]['num'] = 0;
        	}
        }
        if (!$plantInfo1) {
            return $visitorsAry;
        }
        
        $userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
        $defenseCardTime = $userCardStatus['defense'];

        $updateVisitor = 0;
        if ($plantInfo1 && $number) {
            //rand distribution
            for ($i = 0; $i < $number; $i++) {
                $randNum = array_rand($plantInfo1);
                $plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
            }

            $visitorsAry = array();
            
            foreach ($plantInfo1 as $key => $value) {
                $resultAry = array();
				$value_num = $value['num'];
                if ( $value_num != 0 ) {
                	unset($value['num']);
                    if ($value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
                        $value['start_pay_time'] = $now;
                        if ($now > $defenseCardTime) {
                            $rand = rand(1, 100);
                            if ($rand < 11) {
                                $value['event'] = 1;
                            }
                        }
                    }

                    $value['wait_visitor_num'] += $value_num;

                    //update
                    Hapyfish2_Island_HFC_Plant::updateOne($uid, $value['id'], $value);
                    
                    $updateVisitor += $value_num;

                    $resultAry['itemId'] = $value['id'] . $value['item_type'];
                    $resultAry['cid'] = $value['cid'];
                    $resultAry['num'] = $value_num;
                    $resultAry['eventId'] = $value['event'];

                    $visitorsAry[] = $resultAry;
                }
            }
        }

        return $visitorsAry;
    }
}