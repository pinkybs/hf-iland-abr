<?php
require_once(CONFIG_DIR . '/language.php');
class Hapyfish2_Island_Bll_Dock
{
    /**
     * init dock
     *
     * @param integer $ownerUid
     * @param integer $uid
     * @param integer $positionCount | option
     * @return array
     */
    public static function initDock($ownerUid, $uid, $positionCount = null)
    {
		$resultVo = array();
		$boatVo = array();
		
		//get user position list
		$dockPositionArray = Hapyfish2_Island_HFC_Dock::getUserDock($ownerUid, $positionCount);
		
		$now = time();
		if ($uid != $ownerUid ) {
			$ids = array();
			for($i = 1; $i <= $positionCount; $i++) {
				$ids[] = $i;
			}
			$moochList = Hapyfish2_Island_Cache_Mooch::getMoochShipList($ownerUid, $ids);
		} else {
			$moochList = null;
		}
		
		if ( is_array($dockPositionArray) ) {
			foreach ($dockPositionArray as $dockPosition) {
				$boatVo[] = self::getDockShipInfo($ownerUid, $dockPosition, $uid, $now, $moochList[$dockPosition['position_id']]);
			}
		}
		
        $resultVo['boatPositions'] = $boatVo;
        $resultVo['uid'] = $ownerUid;
        $resultVo['positionNum'] = $positionCount;
		
		return $resultVo;
    }
    
    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param array $boatInfo
     * @return array $boatItem
     */
    public static function getDockShipInfo($ownerUid, $dockPosition, $uid, $now, $mooch = null)
    {
        $boatItem = array();

        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($dockPosition['ship_id']);
        //ship init time
        $time = $now - $dockPosition['receive_time'] - $shipInfo['wait_time'] + $dockPosition['speedup_time'];

        $visitorNum = $dockPosition['remain_visitor_num'];
        //ship arrival when time >= 0
		if ($time >= 0 ) {
			//visitor arrive time
			$arriveTime = $now - $dockPosition['receive_time'] - $shipInfo['wait_time'];
			if ( $arriveTime >= $shipInfo['safe_time_2'] ) {
				$boatItem['state'] = 'arrive_3';
			} else if ( $arriveTime >= $shipInfo['safe_time_1'] ) {
				$boatItem['state'] = 'arrive_2';
			} else {
				$boatItem['state'] = 'arrive_1';
			}
		} else {
			$boatItem['state'] = 'onTheRoad';
		}

		$boatItem['visitorNum'] = $visitorNum;
		$boatItem['maxVisitorNum'] = $dockPosition['start_visitor_num'];
		$boatItem['id'] = $dockPosition['position_id'];
		$boatItem['boatId'] = $dockPosition['ship_id'];
            
		$boatItem['canSteal'] = true;
		if ( $uid != $ownerUid ) {
			if ($mooch != null && in_array($uid, $mooch)) {
				$boatItem['canSteal'] = false;
			}
		}

        $boatItem['time'] = $time;

        return $boatItem;
    }
    
    /**
     * read ship list
     *
     * @param integer $uid
     * @return array
     */
    public static function readShip($uid, $pid)
    {      
		$validPids = array(1,2,3,4,5,6,7,8);
    	if (!in_array($pid, $validPids)) {
    		return null;
    	}
    	//get user unlock ship list  
        return Hapyfish2_Island_Cache_Dock::getUnlockShipIds($uid, $pid);
    }
    
    /**
     * unlock ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $priceType
     * @return array
     */
    public static function unlockShip($uid, $shipId, $pid, $priceType)
    {
        $resultVo = array('status' => -1);
        
        $validIds = array(1,2,3,4,5,6,7,8);
        
        if(!in_array($shipId, $validIds)) {
            return $resultVo;
        }
    
        if(!in_array($pid, $validIds)) {
            return $resultVo;
        }
        
        //get ship info
        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($shipId);
        if (!$shipInfo) {
        	return $resultVo;
        }
        
        //get user unlock ship list
        $shipList = Hapyfish2_Island_Cache_Dock::getUnlockShipIds($uid, $pid);
        
        if (in_array($shipId, $shipList)) {
            $resultVo['content'] = 'serverWord_139';
            return $resultVo;
        }
        
		$userInfo = Hapyfish2_Island_HFC_User::getUser($uid, array('coin' => 1, 'level' => 1));
		if ($userInfo['level'] < $shipInfo['level']) {
			$resultVo['content'] = 'serverWord_136';
			return $resultVo;
        }
        
        $addExp = 9;
		$ok = false;
		$now = time();
		
        if ($priceType == 1) {
			if ($shipInfo['coin'] < 1) {
				$resultVo['content'] = 'serverWord_141';
				return $resultVo;
			}
        	
			if ($userInfo['coin'] < $shipInfo['coin']) {
				$resultVo['content'] = 'serverWord_137';
				return $resultVo;
			}
			
			$ok = Hapyfish2_Island_Cache_Dock::unlockShip($uid, $pid, $shipId, $shipList);
			if (!$ok) {
				$resultVo['content'] = 'serverWord_110';
				return $resultVo;
			}
            	
			$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $shipInfo['coin']);
			if ($ok2) {	
				$aryParam = array();
				$aryParam['num'] = $pid;
				$aryParam['name'] = $shipInfo['name'];
				//add log
				foreach ($aryParam as $k => $v) {
		            $parakeys[] = '{*' . $k . '*}';
		            $paravalues[] = $v;
		        }
				$summary = str_replace($parakeys, $paravalues, LANG_PLATFORM_EXT_TXT_102);
				//$summary = '解锁第' . $pid . '船位的' . $shipInfo['name'];
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $shipInfo['coin'], $summary, $now);			
			} else {
				info_log(json_encode($shipInfo), 'unlockship_coin_failure');
			}
			
	        try {
				Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $shipInfo['coin']);
				
				//task id 3012,task type 14
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
				if ( $checkTask['status'] == 1 ) {
					$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
				}
	        } catch (Exception $e) {
	        }
			
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);

			$resultVo['status'] = 1;
			$resultVo['expChange'] = $addExp;
			$resultVo['coinChange'] = -$shipInfo['coin'];
        } else if ($priceType == 2) {
			$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        	if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return array('resultVo' => $result);
	        }
	        
	        $gold = $balanceInfo['balance'];
			if ($shipInfo['gem'] < 1) {
				$resultVo['content'] = 'serverWord_141';
				return $resultVo;
			}
        	
			if ($gold < $shipInfo['gem']) {
				$resultVo['content'] = $wordType;
				return $resultVo;
			}
			
			$isVip = $balanceInfo['is_vip'];
			$userLevel = $userInfo['level'];
			$itemId = $pid . ($shipId < 10 ? '0' : '') . $shipId . '03';
			
			$ok = Hapyfish2_Island_Cache_Dock::unlockShip($uid, $pid, $shipId, $shipList);
			if ($ok) {
		        $aryParam = array();
				$aryParam['num'] = $pid;
				$aryParam['name'] = $shipInfo['name'];
				//add log
				foreach ($aryParam as $k => $v) {
		            $parakeys[] = '{*' . $k . '*}';
		            $paravalues[] = $v;
		        }
				$goldInfo = array(
					'uid' => $uid,
					'cost' => $shipInfo['gem'],
					//'summary' => '解锁第' . $pid . '船位的' . $shipInfo['name'],
				    'summary' => str_replace($parakeys, $paravalues, LANG_PLATFORM_EXT_TXT_102),
					'user_level' => $userLevel,
					'cid' => $itemId,
					'num' => 1
				);
		        $ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        if (!$ok2) {
		        	
		        }
				
			    //check double exp
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$doubleexpCardTime = $userCardStatus['doubleexp'];
				if ($doubleexpCardTime - $now > 0) {
					$addExp = $addExp*2;
					$result['expDouble'] = 2;
				}
		        Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
			
		        try {
					Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $shipInfo['gem']);
					
					//task id 3068,task type 19
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
					if ( $checkTask['status'] == 1 ) {
						$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
					}
		        } catch (Exception $e) {
		        }
	        
            	$resultVo['status'] = 1;
            	$resultVo['expChange'] = $addExp;
            	$resultVo['goldChange'] = -$shipInfo['gem'];
			}
        } else {
        	$resultVo['content'] = 'serverWord_110';
        	return $resultVo;
        }
            
		if ($ok) {
			//change to use the new unlock ship
			try {
            	$userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
        		//get add visitor count by user praise
        		$addVisitor = self::getShipAddVisitorByPraise($shipId, $userIslandInfo['praise']);
	            	
				//get start visitor num
	            $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];
	            $positionInfo = Hapyfish2_Island_HFC_Dock::getUserDockPosition($uid, $pid);
                if ($positionInfo && $positionInfo['ship_id'] != $shipId) {
		            $positionInfo['ship_id'] = $shipId;
		            $positionInfo['receive_time'] = $now;
		            $positionInfo['start_visitor_num'] = $startVisitorNum;
		            $positionInfo['remain_visitor_num'] = $startVisitorNum;
		            $positionInfo['speedup'] = 0;
		            $positionInfo['speedup_time'] = 0;
		            $positionInfo['is_usecard_one'] = 0;
		            
		            Hapyfish2_Island_HFC_Dock::updateUserDockPosition($uid, $pid, $positionInfo);
                }
        	} catch (Exception $e) {
        		$resultVo['status'] = 2;
        	}
        }
        
		$userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
		if ($userAchievement['num_11'] < $shipInfo['sid'] + 1) {
			$userAchievement['num_11'] = $shipInfo['sid'] + 1;
			Hapyfish2_Island_HFC_Achievement::saveUserAchievement($uid, $userAchievement);
		}
        
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            } else {
            	$resultVo['feed'] = Hapyfish2_Island_Bll_Activity::send('BOAT_LEVEL_UP', $uid);
            }
		} catch (Exception $e) {
		}
        
        return $resultVo;
    }
    
    public static function getShipAddVisitorByPraise($shipId, $praise)
    {
    	$shipPraiseInfo = Hapyfish2_Island_Cache_BasicInfo::getShipPraiseInfo($shipId);
    	if (!$shipPraiseInfo) {
    		return 0;
    	}
    	
    	$addVisitor = 0;
    	foreach ($shipPraiseInfo as $item) {
    		if ($praise >= $item[0] && $addVisitor < $item[1]) {
    			$addVisitor = $item[1];
    		}
    	}
    	
    	return $addVisitor;
    }
    
    /**
     * change ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $pid
     * @return array
     */
    public static function changeShip($uid, $shipId, $pid)
    {
        $resultVo = array('status' => -1);
        
        //get ship info
        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($shipId);
        if (!$shipInfo) {
        	return $resultVo;
        }

        //get user position info
        $positionInfo = Hapyfish2_Island_HFC_Dock::getUserDockPosition($uid, $pid);
        if (!$positionInfo || $positionInfo['ship_id'] == $shipId) {
            return $resultVo;
        }
        
        //get user unlock ship list
        $shipList = Hapyfish2_Island_Cache_Dock::getUnlockShipIds($uid, $pid);
        if (!in_array($shipId, $shipList)) {
            $resultVo['content'] = 'serverWord_142';
            return $resultVo;
        }
        
        $userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
        
        //get add visitor count by user praise
        $addVisitor = self::getShipAddVisitorByPraise($shipId, $userIslandInfo['praise']);
            
        try{
            //get start visitor num
            $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

            $positionInfo['ship_id'] = $shipId;
            $positionInfo['receive_time'] = time();
            $positionInfo['start_visitor_num'] = $startVisitorNum;
            $positionInfo['remain_visitor_num'] = $startVisitorNum;
            $positionInfo['speedup'] = 0;
            $positionInfo['speedup_time'] = 0;
            $positionInfo['is_usecard_one'] = 0;
            
            Hapyfish2_Island_HFC_Dock::updateUserDockPosition($uid, $pid, $positionInfo);
            
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            $resultVo['content'] = 'serverWord_110';
            return $resultVo;
        }
        
        return $resultVo;
    }
    
    /**
     * expand dock new position
     *
     * @param integer $uid
     * @return array ResultVo
     */
   public static function expandPosition($uid)
    {
		$resultVo = array('status' => -1);
    	
    	$userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
		//check position count
		if ($userIslandInfo['position_count'] >= 8) {
			return $resultVo;
		}
		
        $dockPositionInfo = Hapyfish2_Island_Cache_BasicInfo::getDockInfo($userIslandInfo['position_count'] + 1);
		if (!$dockPositionInfo) {
			return $resultVo;
		}
		
		$userInfo = Hapyfish2_Island_HFC_User::getUser($uid, array('coin' => 1, 'level' => 1));

    	if ($userInfo['level'] < $dockPositionInfo['level']) {
			$resultVo['content'] = 'serverWord_136';
			return $resultVo;
		}

		if ($userInfo['coin'] < $dockPositionInfo['price']) {
			$resultVo['content'] = 'serverWord_137';
			return $resultVo;
		}
		
		$friendInfo = Hapyfish2_Platform_Cache_Friend::getFriend($uid);
		if (!$friendInfo || ($friendInfo['count'] + 3 < $dockPositionInfo['power'])) {
		    $resultVo['content'] = 'serverWord_138';
            return $resultVo;
		}
		
		$shipId = 1;
        //get ship info
        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($shipId);
    	if (!$shipInfo) {
			return $resultVo;
		}

		try{
        	//get add visitor count by user praise
        	$addVisitor = self::getShipAddVisitorByPraise($shipId, $userIslandInfo['praise']);

        	//get start visitor num
        	$startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];
			
			$price = $dockPositionInfo['price'];
			$positionId = $userIslandInfo['position_count'] + 1;
			
			$ok = Hapyfish2_Island_HFC_Dock::expandPosition($uid, $positionId, $startVisitorNum);
			if (!$ok) {
				$resultVo['content'] = 'serverWord_110';
				return $resultVo;
			}
			
			$now = time();
			//add exp
			$addExp = 9;
		    //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}

			$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
			if ($ok2) {
				//add log
				//$summary = '扩展第' . $positionId . '船位';
				$summary = str_replace('{*num*}', $positionId, LANG_PLATFORM_EXT_TXT_103);
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $price, $summary, $now);
				Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
			}
			
			$resultVo['status'] = 1;
            $resultVo['expChange'] = $addExp;
            $resultVo['coinChange'] = -$price;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return $resultVo;
		}
		
		try {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByMultiField($uid, array('num_12' => 1, 'num_14' => $price));
		
			//task id 3012,task type 14
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
			if ( $checkTask['status'] == 1 ) {
				$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
			}
		} catch (Exception $e) {
		}
    
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            } else {
            	$resultVo['feed'] = Hapyfish2_Island_Bll_Activity::send('DOCK_EXPANSION', $uid);
            }
		} catch (Exception $e) {
		}
		
		return $resultVo;
    }
    
    /**
     * receive boat
     * @param $uid integer
     * @param $pid integer
     * @return $result array
     */
    public static function receiveBoat($uid, $pid)
    {
		$resultVo = array('status' => -1);

		$dockPosition = Hapyfish2_Island_HFC_Dock::getUserDockPosition($uid, $pid);
		if (!$dockPosition || $dockPosition['remain_visitor_num'] == 0) {
			return array('result' => $resultVo);
        }

        $shipInfo  = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($dockPosition['ship_id']);
        if (!$shipInfo) {
        	return array('result' => $resultVo);
        }
     
        $now = time();
        $time = $now - $dockPosition['receive_time'] - $shipInfo['wait_time'];
        //is use speed card
        if ($dockPosition['speedup']) {
            $time = $time + $dockPosition['speedup_time'];
        }

        //check boat is arrivied
		if ($time < 0) {
			$resultVo['content'] = 'serverWord_135';
			return array('result' => $resultVo);
		}
	
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid, array('level' => 1));
		if (!$userLevelInfo) {
			return array('result' => $resultVo);
		}
    		
		//get user vo,current island
		$userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userCurrentIsland = $userVo['current_island']; 
        switch ( $userCurrentIsland ) {
        	case 2 :
        		$userIslandLevel = $userLevelInfo['island_level_2'];
        		break;
        	case 3 :
        		$userIslandLevel = $userLevelInfo['island_level_3'];
        		break;
        	case 4 :
        		$userIslandLevel = $userLevelInfo['island_level_4'];
        		break;
        	default :
        		$userIslandLevel = $userLevelInfo['island_level'];
        		break;
        }
		$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($userIslandLevel);
		
		if (!$islandLevelInfo) {
			return array('result' => $resultVo);
		}

		$visitorCount = $islandLevelInfo['max_visitor'];
		
		$plantVO = Hapyfish2_Island_Bll_Plant::getAllOnIslandNoMooch($uid, $userCurrentIsland, true);
		$currently_visitor = $plantVO['visitorNum'];

		if ($currently_visitor >= $visitorCount) {
			$resultVo['content'] = 'serverWord_133';
			return array('result' => $resultVo);
		}
	    	
    	try {
    		Hapyfish2_Island_Cache_Mooch::clearMoochShip($uid, $pid);
    	}catch (Exception $e) {
    		
    	}

        //receiveNum
        $receiveNum = $dockPosition['remain_visitor_num'];
        if ($currently_visitor > 0) {
            $receiveNum = min($visitorCount - $currently_visitor, $receiveNum);
        }
        else if ($visitorCount < $receiveNum) {
            $receiveNum = $visitorCount;
        }

        $userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
        $praise = $userIslandInfo['praise'];
        
        try{
			$positionAry = array();
			$visitorNum = 0;
			if ($receiveNum == $dockPosition['remain_visitor_num']) {
                //get add visitor count by user praise
                $addVisitor = self::getShipAddVisitorByPraise($dockPosition['ship_id'], $praise);
        
                //get start visitor num
                $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

				//clear postition info
	        	$dockPosition['start_visitor_num'] = $startVisitorNum;
	        	$dockPosition['remain_visitor_num'] = $startVisitorNum;
				$dockPosition['speedup'] = 0;
				$dockPosition['speedup_time'] = 0;
				$dockPosition['is_usecard_one'] = 0;
				$dockPosition['receive_time'] = $now;
			} else {
				$dockPosition['remain_visitor_num'] -= $receiveNum;
				//ship residual people
				$visitorNum = $dockPosition['remain_visitor_num'];
			}
			
			Hapyfish2_Island_HFC_Dock::updateUserDockPosition($uid, $pid, $dockPosition);

			//update user boat arrive time
			//$oldArriveTime = Hapyfish2_Island_HFC_User::getUserBoatArriveTime($uid);
			//update
			//$arriveTime = 0;
			//Hapyfish2_Island_HFC_User::updateUserBoatArriveTime($uid, $arriveTime);
			
			
			$addExp = 3;
			//check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
            $visitorsAry = self::visitorsInvite($uid, $receiveNum, $userCurrentIsland, true);
            
			//isitor arrive pay
			$resultVo['expChange'] = $addExp;
			$resultVo['islandChange'] = true;
			$resultVo['status'] = 1;

		} catch (Exception $e) {
			info_log('[receiveBoat]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Dock');
			$resultVo['content'] = 'serverWord_110';
            return array('result' => $resultVo);
		}
		
		try {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_10', 1);
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_1', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_1', 1);
			
			//task id 3041,task type 1
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3041);
			if ( $checkTask['status'] == 1 ) {
				$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
			}
		} catch (Exception $e) {
		}
		
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

		if (empty($visitorsAry)) {
			$visitorsAry = array();
		}
		
    	try {
			//report log,统计玩家接待船只的类型
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('3002', array($uid, $shipInfo['sid']));
		} catch (Exception $e) {
		}
		
        $god = array();
		if ( $shipInfo['sid'] != 1 ) {
			$mammonTypeRand = -1;
			$mammonRand = rand(1, 100);
			if ( $mammonRand <= 5 ) {
				//$userMammonTime = Hapyfish2_Island_Cache_Counter::getUserMammonRemainTime($uid);
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$userMammonTime = $userCardStatus['mammon'];
				if ( $userMammonTime - $now < 0 ) {
					$mammonTypeRand = rand(1, 6);
					$userCardStatus['mammon'] = $now + $mammonTypeRand*3600;
					Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);
					//Hapyfish2_Island_Cache_Counter::updateUserMammonTime($uid, $mammonTypeRand*3600);
					$god['mammon'] = $mammonTypeRand;
					$templateId = 30;
					$iconType = 1;
				}
			}
			
			if ( $mammonTypeRand == -1 ) {
				$poorRand = rand(1, 500);
				if ( $poorRand == 1 ) {
					$userCardStatus  = Hapyfish2_Island_HFC_User::getCardStatus($uid);
					$defenseCardTime = $userCardStatus['defense'];
					if ($now >= $defenseCardTime ) {
						//$userPoorTime = Hapyfish2_Island_Cache_Counter::getUserPoorRemainTime($uid);
						$userPoorTime = $userCardStatus['poor'];
						if ( $userPoorTime - $now < 0 ) {
							$poorTypeRand = rand(1, 6);
							$userCardStatus['poor'] = $now + $poorTypeRand*3600;
							Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);
							//Hapyfish2_Island_Cache_Counter::updateUserPoorTime($uid, $poorTypeRand*3600);
							$god['poor'] = $poorTypeRand;
							$templateId = 31;
							$iconType = 2;
						}
					}
				}
			}
		}
		
		$result = array('visitors' => $visitorsAry, 'visitorNum' => $visitorNum);
    	if ( !empty($god) ) {
			$result['god'] = $god;
			$result['cardStates'] = Hapyfish2_Island_Bll_Card::getUserCardStates($uid);
			//insert minifeed
			$minifeed = array('uid' => $uid,
	                          'template_id' => $templateId,
	                          'actor' => $uid,
	                          'target' => $uid,
	                          'title' => '',
	                          'type' => $iconType,
	                          'create_time' => $now);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
    	
			if ( isset($god['poor']) ) {
		        //send activity
		        try {
			        $resultVo['feed'] = Hapyfish2_Island_Bll_Activity::send('GOD_POOR_CARD', $uid);
		        } catch (Exception $e) {
				}
			}
		}

		$result['result'] = $resultVo;
		return $result;
    }
    
    /**
     * invite visitors
     *
     * @param integer $uid
     * @param integer $number
     * @return array
     */
    public static function visitorsInvite($uid, $number, $userCurrentIsland, $highcache = true)
    {
		$visitorsAry = array();
		
		if ($highcache) {
			$data = Hapyfish2_Island_HFC_Plant::getAllOnIslandFromHighCache($uid, $userCurrentIsland);
		} else {
			$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, $userCurrentIsland);
		}
		
		$plantInfo1 = $data['plants'];
		if ( empty($plantInfo1) ) {
			$plantInfo1 = array();
		}
		
		$cids = array();
    	foreach ($plantInfo1 as $item) {
    		if (!isset($cids[$item['cid']])) {
    			$cids[$item['cid']] = 1;
    		}
    	}
    	
        for ($i = 0, $count = count($plantInfo1); $i < $count; $i++) {
            if ($plantInfo1[$i]['can_find'] != 1) {
                unset($plantInfo1[$i]);
            } else {
            	$plantInfo1[$i]['num'] = 0;
            }
        }
    	
		if (!$plantInfo1) {
			return $visitorsAry;
		}
        
    	/*$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
    	$plantInfo2 = Hapyfish2_Island_Cache_BasicInfo::getDiffPlantList($userLevelInfo['level'], $cids);
		
        foreach ($plantInfo2 as $key2 => $value2) {
        	foreach ($plantInfo1 as $value1) {
                if ($value1['item_id'] == $value2['item_id'] && $value2['level'] < $value1['level']) {
                	unset($plantInfo2[$key2]);
                }
        	}
        	unset($plantInfo2[$key2]['level']);
            unset($plantInfo2[$key2]['item_id']);
        }

        $plantInfo3 = Hapyfish2_Island_Cache_BasicInfo::getPlantListByLevel($userLevelInfo['level'] + 1);
        foreach ($plantInfo3 as $key3 => $value3) {
        	foreach ($plantInfo1 as $value11) {
                if ( $value11['item_id'] == $value3['item_id'] && $value3['level'] < $value11['level'] ) {
                	unset($plantInfo3[$key3]);
                }
        	}
        	unset($plantInfo3[$key3]['level']);
            unset($plantInfo3[$key3]['item_id']);
        }
        
		if (!$plantInfo1 && !$plantInfo2 && !$plantInfo3) {
			return $visitorsAry;
		}

		$flag = 0;
		if($plantInfo1) $flag += 4;
		if($plantInfo2) $flag += 2;
		if($plantInfo3) $flag += 1;

		$config_map = array(0 => array(0, 0, 0),
							1 => array(0, 0, 1),
							2 => array(0, 1, 0),
							3 => array(0, 0.97, 0.03),
							4 => array(1, 0, 0),
							5 => array(0.97, 0, 0.03),
							6 => array(0.9, 0.1, 0),
							7 => array(0.9, 0.07, 0.03));

		$numAry = $config_map[$flag];
		$num1 = round($numAry[0] * $number);
		$num2 = round($numAry[1] * $number);
		$num3 = $number - $num1 - $num2;
		
		if ( $num2 + $num3 > 2 ) {
			$num1 = $num1 + $num2 + $num3 - 2;
			if ( $num2 > 0 ) {
				if ( $num3 > 0 ) {
					$num2 = 1;
					$num3 = 1;
				}
				else {
					$num2 = 2;
					$num3 = 0;
				}
			}
			else {
				$num2 = 0;
				$num3 = 2;
			}
		}*/
		$num1 = $number;
		$num2 = 0;
		$num3 = 0;
		
		$updateVisitor = 0;
		$now = time();
		if ($plantInfo1 && $num1) {
			//rand distribution
	    	for ($i = 0; $i < $num1; $i++ )
			{
				$randNum = array_rand($plantInfo1);
				$plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
			}

			$result1 = array();
			
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$defenseCardTime = $userCardStatus['defense'];
			foreach ($plantInfo1 as $key => $value) {
				$resultAry = array();
				$value_num = $value['num'];
				if ($value_num > 0) {
					unset($value['num']);
					if ($value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
						$value['start_pay_time'] = $now;
						//event 
					    if ($now > $defenseCardTime) {
                            $rand = rand(1, 100);
                            if ( $rand < 31 ) {
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

					$result1[] = $resultAry;
				}

			}

		}

		//rand distribution 15% people
    	/*if ($plantInfo2 && $num2) {
    		for ($i = 0; $i < $num2; $i++ )
			{
				$randNum = array_rand($plantInfo2);
				$plantInfo2[$randNum]['num'] = $plantInfo2[$randNum]['num'] + 1 ;
			}
		}

		//rand distribution 5% people
    	if ($plantInfo3 && $num3) {
    		for ($i = 0; $i < $num3; $i++ )
			{
				$randNum = array_rand($plantInfo3);
				$plantInfo3[$randNum]['num'] = $plantInfo3[$randNum]['num'] + 1 ;
			}
		}
		
		if ( !empty($result1) ) {
			$visitorsAry = array_merge($result1, $plantInfo2, $plantInfo3);
		}
		else {
			$visitorsAry = array_merge($plantInfo2, $plantInfo3);
		}*/
    
		if ( !empty($result1) ) {
			$visitorsAry = $result1;
		}
		else {
			$visitorsAry = array();
		}

		return $visitorsAry;
    }
    
    
    /**
     * mooch visitor
     *
     * @param integer $uid
     * @param integer $ownerUid
     * @param integer $positionId
     * @return array
     */
    public static function mooch($uid, $ownerUid, $positionId)
    {
    	$result = array('status' => -1);
    	
        //check is friend
        $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }
        
        $moochInfo = Hapyfish2_Island_Cache_Mooch::getMoochShip($ownerUid, $positionId);
        if (!empty($moochInfo) && in_array($uid, $moochInfo)) {
            $result['content'] = 'serverWord_102';
            return array('result' => $result);
        }
        
        $positionInfo = Hapyfish2_Island_HFC_Dock::getUserDockPosition($ownerUid, $positionId);
        
        //get ship info
        $shipInfo = Hapyfish2_Island_Cache_BasicInfo::getShipInfo($positionInfo['ship_id']);

        $now = time();
        //get visitor arrive time
        $arriveTime = $now - $positionInfo['receive_time'] - $shipInfo['wait_time'] + $positionInfo['speedup_time'];
        if ($arriveTime < 0) {
            $result['content'] = 'serverWord_156';
            return array('result' => $result);
        }
        $remainNum = $positionInfo['remain_visitor_num'] - $shipInfo['safe_visitor_num'];
        if ($remainNum <= 0) {
            $result['content'] = 'serverWord_132';
            return array('result' => $result);
        }
        
    	$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		if (!$userLevelInfo) {
			return array('result' => $resultVo);
		}
    		
		//get user vo,current island
		$userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userCurrentIsland = $userVo['current_island'];
        switch ( $userCurrentIsland ) {
        	case 2 :
        		$userIslandLevel = $userLevelInfo['island_level_2'];
        		break;
        	case 3 :
        		$userIslandLevel = $userLevelInfo['island_level_3'];
        		break;
        	case 4 :
        		$userIslandLevel = $userLevelInfo['island_level_4'];
        		break;
        	default :
        		$userIslandLevel = $userLevelInfo['island_level'];
        		break;
        }
    	$islandLevelInfo = Hapyfish2_Island_Cache_BasicInfo::getIslandLevelInfo($userIslandLevel);
		if (!$islandLevelInfo) {
			return array('result' => $resultVo);
		}
        
        $plantVO = Hapyfish2_Island_Bll_Plant::getAllOnIslandNoMooch($uid, $userCurrentIsland, true);
        $currently_visitor = $plantVO['visitorNum'];
        if ($islandLevelInfo['max_visitor'] <= $currently_visitor) {
            $result['content'] = 'serverWord_133';
            return array('result' => $result);
        }
    
        //get insurance card time
		$ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
		$insuranceCardTime = $ownerCardStatus['insurance'];
        
        //check owner whether have receive_card
        if ($now < $insuranceCardTime){
            $result['content'] = 'serverWord_134';
            return array('result' => $result);
        }

        //mooch num
        //help num
        if ($arriveTime <= $shipInfo['safe_time_1']) {
            $moochNum = 1;
            $helpNum = rand(1, 2);
        }
        else if ( $arriveTime <= $shipInfo['safe_time_2'] ) {
            $moochNum = rand(1, 2);
            $helpNum = rand(1, 4);
        }
        else {
            $moochNum = rand(1, 3);
            $helpNum = rand(1, 6);
        }

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
        $cardNum = rand(1, 1000);
        $sendCard = 0;
        $cardId = null;
        if ($cardNum <= $shipInfo['getcard']) {
        	$cardArray = array(26241,26341,26441,26541,26641,26841,27141);
        	$randNum = array_rand($cardArray);
            $cardId = $cardArray[$randNum];
        	$sendCard = 1;
        }

        //
        $moochInfo[] = $uid;
        Hapyfish2_Island_Cache_Mooch::moochShip($ownerUid, $positionId, $moochInfo);
        try {
            $visitorsAry = self::moochVisitors($uid, $moochNum, true);
            $helpAry = self::moochVisitors($ownerUid, $helpNum, false);
            
			//update onwer position info
			$positionInfo['remain_visitor_num'] -= $allNum;
			Hapyfish2_Island_HFC_Dock::updateUserDockPosition($ownerUid, $positionId, $positionInfo);

            $addExp = 2;
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);

            if ($sendCard == 1) {
                Hapyfish2_Island_HFC_Card::addUserCard($ownerUid, $cardId, 1);
            }
            
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
        	
			//task id 3012,task type 7
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3012);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
        } catch (Exception $e) {

        }

        $minifeed = array(
        	'uid' => $ownerUid,
			'template_id' => 20,
			'actor' => $uid,
			'target' => $ownerUid,
			'title' => array('visitorNum' => $moochNum, 'helpNum' => $helpNum),
			'type' => 2,
			'create_time' => $now
        );
		Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		
        if ($sendCard == 1) {
        	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cardId);
            $minifeedCard = array(
            	'uid' => $ownerUid,
				'template_id' => 19,
				'actor' => $uid,
				'target' => $ownerUid,
				'title' => array('cardName' => $cardInfo['name']),
				'type' => 3,
				'create_time' => $now
            );
            Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeedCard);
        }
    
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

        if (empty($visitorsAry)) {
            $visitorsAry = array();
        }
        
    
        $resultVo = array('result' => $result, 'visitors' => $visitorsAry, 'friendVisitors' => $helpAry);
        if ( $cardId ) {
        	$resultVo['cardCid'] = $cardId;
        }
        else {
	        $god = array();
			if ( $shipInfo['sid'] != 1 ) {
			//if ( $shipInfo['sid'] ) {
				$mammonTypeRand = -1;
				$mammonRand = rand(1, 100);
				if ( $mammonRand <= 5 ) {
					//$userMammonTime = Hapyfish2_Island_Cache_Counter::getUserMammonRemainTime($ownerUid);
					$ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
					$ownerMammonTime = $ownerCardStatus['mammon'];
					if ( $ownerMammonTime - $now < 0 ) {
						$mammonTypeRand = rand(1, 6);
						//Hapyfish2_Island_Cache_Counter::updateUserMammonTime($ownerUid, $mammonTypeRand*3600);
						//$ownerCardStatus['poor'] = $now + $poorTypeRand*3600;
						$ownerCardStatus['mammon'] = $now + $mammonTypeRand*3600;
						Hapyfish2_Island_HFC_User::updateCardStatus($ownerUid, $ownerCardStatus);
						$god['mammon'] = $mammonTypeRand;
						$templateId = 28;
						$iconType = 1;
					}
				}
				
				if ( $mammonTypeRand == -1 ) {
					$poorRand = rand(1, 5000);
					if ( $poorRand == 1 ) {
					    $ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
					    $defenseCardTime = $ownerCardStatus['defense'];
						if ($now >= $defenseCardTime) {
							//$userPoorTime = Hapyfish2_Island_Cache_Counter::getUserPoorRemainTime($ownerUid);
							$ownerPoorTime = $ownerCardStatus['poor'];
							if ( $ownerPoorTime - $now < 0 ) {
								$poorTypeRand = rand(1, 6);
								//Hapyfish2_Island_Cache_Counter::updateUserPoorTime($ownerUid, $poorTypeRand*3600);
								$ownerCardStatus['poor'] = $now + $poorTypeRand*3600;
								Hapyfish2_Island_HFC_User::updateCardStatus($ownerUid, $ownerCardStatus);
								$god['poor'] = $poorTypeRand;
								$templateId = 29;
								$iconType = 2;
							}
						}
					}
				}
			}
	        if ( !empty($god) ) {
	        	$resultVo['god'] = $god;
	        	$resultVo['cardStates'] = Hapyfish2_Island_Bll_Card::getUserCardStates($ownerUid);
	        	//insert minifeed
				$minifeed = array('uid' => $ownerUid,
		                          'template_id' => $templateId,
		                          'actor' => $uid,
		                          'target' => $ownerUid,
		                          'title' => '',
		                          'type' => $iconType,
		                          'create_time' => $now);
				Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
				
				if ( isset($god['mammon']) ) {
					$achievementCardType = 'num_25';
					$checkTaskId = 3059;
				}
				else {
					$achievementCardType = 'num_32';
					$checkTaskId = 3062;
				}
	            //update achievement task
		        try {
		        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, $achievementCardType, 1);
					//task id 3059,task type 
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, $checkTaskId);
					if ( $checkTask['status'] == 1 ) {
						$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
					}
		        
		        } catch (Exception $e) {
		        }
	        }
        }
        
        return $resultVo;
    }
    
    /**
     * mooch visitors
     *
     * @param integer $uid
     * @param integer $number
     * @return array
     */
    public static function moochVisitors($uid, $number, $highcache = true)
    {
        $visitorsAry = array();
        $now = time();
    
		//get user vo,current island
		$userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userCurrentIsland = $userVo['current_island']; 
        
		if ($highcache) {
			$data = Hapyfish2_Island_HFC_Plant::getAllOnIslandFromHighCache($uid, $userCurrentIsland);
		} else {
			$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, $userCurrentIsland);
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
                            if ($rand < 31) {
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