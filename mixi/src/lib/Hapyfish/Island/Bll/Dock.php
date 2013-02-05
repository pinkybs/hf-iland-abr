<?php

class Hapyfish_Island_Bll_Dock
{
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
    	
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result['content'] = 'serverWord_101';
            $result = array('result' => $result);
            return $result;
        }
    	
        //check is friend
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $result['content'] = 'serverWord_120';
            $result = array('result' => $result);
            return $result;
        }

        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //get mooch info
        $moochInfo = $dalMooch->hadMoochDock($uid, $ownerUid, $positionId);
        if ( $moochInfo ) {
            $result['content'] = 'serverWord_102';
            $result = array('result' => $result);
            return $result;
        }

        //get user position info by position id
        $positionInfo = self::getUserPositionById($ownerUid, $positionId);
        if ( $positionInfo['status'] != 2 ) {
            $result['content'] = 'serverWord_156';
            return array('result' => $result);
        }
        
        //get ship info
        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($positionInfo['ship_id']);

        $now = time();
        //get visitor arrive time
        $arriveTime = $now - $positionInfo['receive_time'] - $shipInfo['wait_time'];
        $remainNum = $positionInfo['remain_visitor_num'] - $shipInfo['safe_visitor_num'];
        if ( $remainNum <= 0 ) {
            $result['content'] = 'serverWord_132';
            $result = array('result' => $result);
            return $result;
        }
        
        //check user currently visitor
        $currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($uid);
        $levelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        $islandLevelInfo = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelInfo['island_level']);
        if ( $islandLevelInfo['visitor_count'] <= $currently_visitor ) {
            $result['content'] = 'serverWord_133';
            $result = array('result' => $result);
            return $result;
        }
    
        //get insurance card time
		$insuranceCardTime = Hapyfish_Island_Cache_User::getInsuranceCardTime($ownerUid);
        
        //check owner whether have receive_card
        if ($now - $insuranceCardTime <= 6*3600){
            $result['content'] = 'serverWord_134';
            $result = array('result' => $result);
            return $result;
        }

        //mooch num,visitor feeling type
        if ( $arriveTime <= $shipInfo['safe_time_1'] ) {
            $moochNum = 1;
        }
        else if ( $arriveTime <= $shipInfo['safe_time_2'] ) {
            $moochNum = rand(1, 2);
        }
        else {
            $moochNum = rand(1, 3);
        }
        
        //help num,visitor feeling type
        if ( $arriveTime <= $shipInfo['safe_time_1'] ) {
            $helpNum = rand(1, 2);
        }
        else if ( $arriveTime <= $shipInfo['safe_time_2'] ) {
            $helpNum = rand(1, 4);
        }
        else {
            $helpNum = rand(1, 6);
        }

        if ( $moochNum > $remainNum ) {
            $moochNum = $remainNum;
            $helpNum = 0;
        }
        else if ( ($moochNum + $helpNum) > $remainNum ) {
        	$helpNum = $remainNum - $moochNum;
        }
        
        if ( $moochNum + $currently_visitor > $islandLevelInfo['visitor_count'] ) {
            $moochNum = $islandLevelInfo['visitor_count'] - $currently_visitor;
        }
        
        $allNum = $moochNum + $helpNum;
        $cardNum = rand(1, 1000);
        $sendCard = 0;
        $cardId = null;
        //$cardNum = 1;
        if ( $cardNum <= $shipInfo['getcard']) {
        	$cardArray = array(26241,26341,26441,26541,26641,26841,27141);
        	$randNum = array_rand($cardArray);
            $cardId = $cardArray[$randNum];
        	$sendCard = 1;
        }

        $dalDock = Dal_Island_Dock::getDefaultInstance();

        try {
            //get user position info by position id
            $positionInfoForUpdate = $dalDock->getUserPositionByIdForupdate($ownerUid, $positionId);
            $remainNum = $positionInfoForUpdate['remain_visitor_num'] - $shipInfo['safe_visitor_num'];
            if ( $remainNum <= 0 ) {
                $result['content'] = 'serverWord_132';
                $result = array('result' => $result);
                return $result;
            }
            
            $moochStatus = 1;
            $feedType = 1;

            $visitorsAry = self::moochVisitors($uid, $moochNum);
            $helpAry = self::moochVisitors($ownerUid, $helpNum);
            
            if ( $moochStatus == 1 ) {
                //update onwer position info
                $newPosition = array('remain_visitor_num' => $positionInfo['remain_visitor_num'] - $allNum);
                $dalDock->updateUserPosition($ownerUid, $positionId, $newPosition);
            }

            $expChange = 2;
            //update user exp
            Hapyfish_Island_Cache_User::incExp($uid, $expChange);

            if ( $sendCard == 1 ) {
            	$dalCard = Dal_Island_Card::getDefaultInstance();
                $newCard = array('uid' => $ownerUid,
                                 'cid' => $cardId,
                                 'count' => 1,
                                 'buy_time' => time(),
                                 'item_type' => 41);
                    //add user card
                $dalCard->addUserCard($newCard);
            }
            
            Hapyfish_Island_Cache_Dock::cleanUserPositionList($ownerUid);

            //insert mooch info
            $newMooch = array('uid' => (string)$uid,
                              'owner_uid' => (string)$ownerUid,
                              'pid' => (string)$positionId);
            $dalMooch->insertMoochDock($newMooch);
            
            $result['status'] = 1;
            $result['expChange'] = $expChange;
        }
        catch (Exception $e) {
            info_log('[mooch]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Dock');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today achievement,num_5
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_5', 1);
            //update user today achievement,num_7
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_7', $moochNum);
            //update user achievement,mooch_count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_5', 1);
        } catch (Exception $e) {

        }

        if ( $feedType == 1 ) {
            $minifeed = array('uid' => $ownerUid,
                              'template_id' => 20,
                              'actor' => $uid,
                              'target' => $ownerUid,
                              'title' => array('visitorNum' => $moochNum, 'helpNum' => $helpNum),
                              'type' => 2,
                              'create_time' => $now);
            Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
	        if ( $sendCard == 1 ) {
	            $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cardId);
	            $minifeedCard = array('uid' => $ownerUid,
	                              'template_id' => 19,
	                              'actor' => $uid,
	                              'target' => $ownerUid,
	                              'title' => array('cardName' => $cardInfo['name']),
	                              'type' => 3,
	                              'create_time' => time());
	            Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeedCard);
	        }
        }
    
        //check level up
        try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $result['levelUp'] = $levelUp['levelUp'];
	        $result['islandLevelUp'] = $levelUp['islandLevelUp'];
	        
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
	        
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	            $result['feed'] = $feed;
	        }
        } catch (Exception $e) {

        }


        if (empty($visitorsAry)) {
            $visitorsAry = array();
        }

        $result = array('result' => $result, 'visitors' => $visitorsAry, 'friendVisitors' => $helpAry, 'cardCid' => $cardId);
        return $result;
    }
    
    /**
     * add boat
     *
     * @param integer $uid
     * @return array ResultVo
     */
   public static function addBoat($uid)
    {
		$resultVo = array('status' => -1);
		
        $dalUser = Dal_Island_User::getDefaultInstance();
    	$userInfo = $dalUser->getUserForAddBoat($uid);

		//check position count
		if ($userInfo['position_count'] == 8) {
			return $resultVo;
		}

		//position_count +1
		$ifAry = Hapyfish_Island_Cache_Dock::getAddBoatByid($userInfo['position_count'] + 1);

    	if ($userInfo['level'] < $ifAry['level']) {
			$resultVo['content'] = 'serverWord_136';
			return $resultVo;
		}

		if ($userInfo['coin'] < $ifAry['price']) {
			$resultVo['content'] = 'serverWord_137';
			return $resultVo;
		}

		$power = Hapyfish_Island_Bll_User::getPower($uid);
		if ($power < $ifAry['power']) {
		    $resultVo['content'] = 'serverWord_138';
            return $resultVo;
		}
		
		//add exp
		$addExp = 9;

		$shipId = 1;
        //get ship info
        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($shipId);
        //get add visitor count by user praise
        $addVisitor = Hapyfish_Island_Cache_Dock::getShipAddVisitorByPraise($shipId, $userInfo['praise']);

        //get start visitor num
        $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

		try{
			//insert new boat
			$boatAry = array(
				'uid' => $uid,
				'position_id' => $userInfo['position_count'] + 1,
				'status' => 1,
				'ship_id' => $shipInfo['sid'],
				'start_visitor_num' => $startVisitorNum,
				'remain_visitor_num' => $startVisitorNum
			);
        	
        	$dalDock = Dal_Island_Dock::getDefaultInstance();
			$dalDock->insertDock($boatAry);
			
			$newUnlockShip = array('uid' => $uid,
			                       'position_id' => $boatAry['position_id'],
			                       'ship_id' => $boatAry['ship_id']);
			
			$dalDock->insertUserShip($newUnlockShip);

			//update user info
			Hapyfish_Island_Cache_User::updatePositionCount($uid);
			
			Hapyfish_Island_Cache_User::decCoin($uid, $ifAry['price']);
			Hapyfish_Island_Cache_User::incExp($uid, $addExp);

			Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = + $addExp;
            $resultVo['coinChange'] = - $ifAry['price'];
		} catch (Exception $e) {
			info_log('[addBoat]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Dock');
			$resultVo['content'] = 'serverWord_110';
            return $resultVo;
		}
		
		try {
			$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement,num_12
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_12', 1);
            //update user buy coin
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $ifAry['price']);
		} catch (Exception $e) {
		}

        //send activity
        try {
        	$dalDock = Dal_Island_Dock::getDefaultInstance();
        	$imageName = $dalDock->getUserDockImage($uid);
	        $feed = Bll_Island_Activity::send('DOCK_EXPANSION', $uid, array('expanOld'=>$userInfo['position_count'], 'expanNew'=>$userInfo['position_count']+1, 'img'=>$imageName));
	        $resultVo['feed'] = $feed;
        } catch (Exception $e) {
		}
        
		try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	                    
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['newLevel']));
	            $resultVo['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
		
		$isAppUser = Bll_User::isAppUser($uid);
		if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            return array('result' => $resultVo);
		}

		$boatInfo = self::getUserPositionById($uid, $pid);
        if ($boatInfo['remain_visitor_num'] == 0) {
            return array('result' => $resultVo);
        }

        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($boatInfo['ship_id']);
        $now = time();
        $time = $now - $boatInfo['receive_time'] - $shipInfo['wait_time'];
        //is use speed card
        if ($boatInfo['speedup']) {
            $time = $time + $boatInfo['speedup_type'];
        }
        
        if ($boatInfo['status'] != 2) {
            //check ship's status
            if ( $time < 0 ){
                $resultVo['content'] = 'serverWord_135';
                return array('result' => $resultVo);
            }
        }
    	
    	$levelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
    	//get island level info
    	$islandLevelInfo = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelInfo['island_level']);
		//island visitor
		$visitorCount = $islandLevelInfo['visitor_count'];

    	//check island have 1 minute do not go out people
    	$currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($uid);
    	if ($currently_visitor > 0) {
    		//out island
    		$outCount = Hapyfish_Island_Bll_User::outIslandPeople($uid);
    		if ($outCount) {
				$currently_visitor = Hapyfish_Island_Cache_Plant::getCurrentlyVisitor($uid);
    		}
	    	if ($currently_visitor >= $visitorCount) {
	    		$resultVo['content'] = 'serverWord_133';
				return array('result' => $resultVo);
	    	}
    	}

    	try {
	        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
	        //delete mooch table info
	        $dalMooch->deleteMoochDock($uid, $pid);
    	}catch (Exception $e) {
    		
    	}

        //receiveNum
        $receiveNum = $boatInfo['remain_visitor_num'];
        if ( $currently_visitor > 0 ) {
            $receiveNum = min($visitorCount - $currently_visitor, $receiveNum);
            if ( $receiveNum < 0 ) {
                $receiveNum = 0;
                return array();
            }
        }
        else if ($visitorCount < $receiveNum) {
            $receiveNum = $visitorCount;
        }

        $praise = Hapyfish_Island_Cache_User::getPraise($uid);
        try{
			$positionAry = array();
			$visitorNum = 0;
			if ($receiveNum == $boatInfo['remain_visitor_num']) {
                //get add visitor count by user praise
                $addVisitor = Hapyfish_Island_Cache_Dock::getShipAddVisitorByPraise($boatInfo['ship_id'], $praise);
        
                //get start visitor num
                $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

				//clear postition info
	        	$positionAry['status'] = 1;
	        	$positionAry['start_visitor_num'] = $startVisitorNum;
	        	$positionAry['remain_visitor_num'] = $startVisitorNum;
	        	$positionAry['receive_time'] = 0;
				$positionAry['speedup'] = 0;
				$positionAry['speedup_type'] = 0;
				$positionAry['is_usecard_one'] = 0;
				$positionAry['receive_time'] = $now;
			}
			elseif ($receiveNum == $visitorCount)  {
				$positionAry['remain_visitor_num'] = $boatInfo['remain_visitor_num'] - $receiveNum;
				$positionAry['status'] = 2;
				//ship residual people
				$visitorNum = $boatInfo['remain_visitor_num'] - $receiveNum;
			}
			else {
				$positionAry['remain_visitor_num'] = $boatInfo['remain_visitor_num'] - $receiveNum;
				$positionAry['status'] = 2;
				//ship residual people
				$visitorNum = $boatInfo['remain_visitor_num'] - $receiveNum;
			}
			
			$dalDock = Dal_Island_Dock::getDefaultInstance();
			$dalDock->updateUserPosition($uid, $pid, $positionAry);

			//update user boat arrive time
			$arriveTime = self::getArriveTime($uid);
			$dalUser = Dal_Island_User::getDefaultInstance();
			$dalUser->updateUser($uid, array('boat_arrive_time' => $arriveTime));
			
			$addExp = 3;
			Hapyfish_Island_Cache_User::incExp($uid, $addExp);
			
            $visitorsAry = self::visitorsInvite($uid, $receiveNum);
            
			Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);
            
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
			$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
			//update today achievement
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_1', 1);
            //upate achievement
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_10', 1);
		} catch (Exception $e) {

		}
		
		try {
			$levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
			$resultVo['levelUp'] = $levelUp['levelUp'];
			$resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			
			if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
	
			//send activity
			if ( $levelUp['levelUp'] ) {
			    $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
			    $resultVo['feed'] = $feed;
			}

		} catch (Exception $e) {

		}

		if (empty($visitorsAry)) {
			$visitorsAry = array();
		}

		return array('result' => $resultVo, 'visitors' => $visitorsAry, 'visitorNum' => $visitorNum);
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
        
        if( $shipId < 1 || $shipId > 8 ) {
            return $resultVo;
        }
    
        if( $pid < 1 || $pid > 8 ) {
            return $resultVo;
        }
        
        //get user unlock ship list
        $shipList = Hapyfish_Island_Cache_Dock::getUserUnlockShipList($uid, $pid);
        
        if ( in_array($shipId, $shipList) ) {
            $resultVo['content'] = 'serverWord_139';
            return $resultVo;
        }
        
        //get ship info
        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($shipId);
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user island info
        $islandUser = $dalUser->getUserForUnlock($uid);

        if ($islandUser['level'] < $shipInfo['level']) {
            $resultVo['content'] = 'serverWord_136';
            return $resultVo;
        }

        if ( $priceType == 1 ) {
            $price = 'coin';
            $ifPrice = 'coin';
            //$priceContent = '金币';
            $wordType = 'serverWord_137';
        }
        else {
            $price = 'gold';
            $ifPrice = 'gem';
            //$priceContent = '宝石';
            $wordType = 'serverWord_140';
        }

        if ($islandUser[$price] < $shipInfo[$ifPrice]) {
            $resultVo['content'] = $wordType;
            return $resultVo;
        }

        if ( $price == 'coin' && $shipInfo['coin'] < 1 ) {
            $resultVo['content'] = 'serverWord_141';
            return $resultVo;
        }

        try{
            //update user info
            $userAry = array('exp' => 0);
            if ( $priceType == 1 ) {
                Hapyfish_Island_Cache_User::decCoin($uid, $shipInfo['coin']);
            }
            else {
                Hapyfish_Island_Cache_User::decGold($uid, $shipInfo['gem']);
            }

            $dalDock = Dal_Island_Dock::getDefaultInstance();
            $dalDock->updateUserShip(array('uid' => $uid, 'position_id' => $pid, 'ship_id' => $shipId));
            
            
            if ( $shipInfo['gem'] > 0 && $priceType != 1 ) {
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'gold' => $shipInfo['gem'],
                                      'item_id' => $shipInfo['sid'],
                                      'name' => $pid . '号船位解锁' . $shipInfo['name'],
                                      'count' => 1,
                                      'content' => '[unlockShip]:newShip=' . $shipId . ',positionId=' . $pid,
                                      'create_time' => time());
                try {
                	$dalGold = Dal_Island_Gold::getDefaultInstance();
                	$dalGold->insertUserGoldInfo($userGoldInfo);
                }catch (Exception $e) {
                	
                }
            }

            if ( $priceType == 1 ) {
                $resultVo['coinChange'] = -$shipInfo['coin'];
            }
            else {
                $resultVo['goldChange'] = -$shipInfo['gem'];
            }
            
            $addExp = 9;
            Hapyfish_Island_Cache_User::incExp($uid, $addExp);
            $resultVo['expChange'] = + $addExp;
            
            Hapyfish_Island_Cache_Dock::cleanUserUnlockShipList($uid, $pid);
            
            $resultVo['status'] = 1;
        } catch (Exception $e) {
            info_log('[unlockShip]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return $resultVo;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement ship level
            $userAchievementShipLevel = $dalMongoAchievement->getUserAchievementByField($uid, 'num_11');
            if ( $userAchievementShipLevel < $shipInfo['sid'] + 1 ) {
                $dalMongoAchievement->updateUserAchievement($uid, array('num_11' => $shipInfo['sid'] + 1));
            }
        } catch (Exception $e) {
        }
    
        //send activity
        try {
	        $feed = Bll_Island_Activity::send('BOAT_LEVEL_UP', $uid, array('boat'=>$shipInfo['name'], 'img'=>$shipInfo['class_name']));
	        $resultVo['feed'] = $feed;
        } catch (Exception $e) {
        }
        
        try {
	        //check level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $forUpdateUser['newLevel']));
	            $resultVo['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {
        }
        
        return $resultVo;
    }


    /**
     * invite visitors
     *
     * @param integer $uid
     * @param integer $number
     * @return array
     */
    public static function visitorsInvite($uid, $number)
    {
		$visitorsAry = array();

		$plantInfo1 = Hapyfish_Island_Cache_Plant::getUserPlantList($uid);
		
		$plantInfo2 = Bll_Cache_Island_User::getListIslandPlant($uid);
		
        for ( $j=0,$jCount=count($plantInfo1); $j<$jCount; $j++ ) {
            if ( $plantInfo1[$j]['can_find'] != 1 ) {
                unset($plantInfo1[$j]);
            } else {
            	$plantInfo1[$j]['num'] = 0;
            }
        }

        foreach ($plantInfo2 as $key2 => $value2) {
        	foreach ($plantInfo1 as $value1) {
                if ( $value1['item_id'] == $value2['item_id'] && $value2['level'] < $value1['level'] ) {
                	unset($plantInfo2[$key2]);
                }
        	}
        	unset($plantInfo2[$key2]['level']);
            unset($plantInfo2[$key2]['item_id']);
        }

        $levelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        $plantInfo3 = Bll_Cache_Island::getPlantListByLevel($levelInfo['level']);
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
		}
		
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
			
			$defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($uid);
			foreach ($plantInfo1 as $key => $value) {
				$resultAry = array();
				$value_num = $value['num'];
				if ( $value_num > 0 ) {
					unset($value['num']);
					if ( $value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
						$value['start_pay_time'] = $now;
						//event 
					    if (($now - $defenseCardTime) >= 12*3600 ) {
                            $rand = rand(1, 100);
                            if ( $rand < 11 ) {
                                $value['event'] = 1;
                            }
                        }
					}

					$value['wait_visitor_num'] = $value['wait_visitor_num'] + $value_num;
                    
					//update
					Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($value['uid'], $value['id'], $value);
					
					$updateVisitor += $value_num;

					$resultAry['itemId'] = $value['itemId'];
					$resultAry['cid'] = $value['cid'];
					$resultAry['num'] = $value_num;
					$resultAry['eventId'] = $value['event'];

					$result1[] = $resultAry;
				}

			}
			//update user visitor
			if ($updateVisitor > 0) {
			    Hapyfish_Island_Cache_Plant::incCurrentlyVistor($uid, $updateVisitor);
			}
		}

		//rand distribution 15% people
    	if ($plantInfo2 && $num2) {
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
		}

		return $visitorsAry;
    }
    
    /**
     * mooch visitors
     *
     * @param integer $uid
     * @param integer $number
     * @return array
     */
    public static function moochVisitors($uid, $number)
    {
        $visitorsAry = array();
        $now = time();

        $plantInfo1  = Hapyfish_Island_Cache_Plant::getUserPlantList($uid);
        
        for ( $j=0,$jCount=count($plantInfo1); $j<$jCount; $j++ ) {
        	if ( $plantInfo1[$j]['can_find'] != 1 ) {
        		unset($plantInfo1[$j]);
        	} else {
        		$plantInfo1[$j]['num'] = 0;
        	}
        }
        
        if ( !$plantInfo1 ) {
            return $visitorsAry;
        }
        
        $defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($uid);

        $updateVisitor = 0;
        if ($plantInfo1 && $number) {
            //rand distribution
            for ($i = 0; $i < $number; $i++ )
            {
                $randNum = array_rand($plantInfo1);
                $plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
            }

            $visitorsAry = array();
            
            foreach ($plantInfo1 as $key => $value) {
                $resultAry = array();
				$value_num = $value['num'];
                if ( $value_num != 0 ) {
                	unset($value['num']);
                    if ( $value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
                        $value['start_pay_time'] = $now;
                        if (($now - $defenseCardTime) >= 12*3600 ) {
                            $rand = rand(1, 100);
                            if ( $rand < 11 ) {
                                $value['event'] = 1;
                            }
                        }
                    }

                    $value['wait_visitor_num'] = $value['wait_visitor_num'] + $value_num;

                    //
                    Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($value['uid'], $value['id'], $value);
                    
                    $updateVisitor += $value_num;

                    $resultAry['itemId'] = $value['itemId'];
                    $resultAry['cid'] = $value['cid'];
                    $resultAry['num'] = $value_num;
                    $resultAry['eventId'] = $value['event'];

                    $visitorsAry[] = $resultAry;
                }
            }
            
            //update user visitor
            if ($updateVisitor) {
                Hapyfish_Island_Cache_Plant::incCurrentlyVistor($uid, $updateVisitor);
            }
        }

        return $visitorsAry;
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
        $isAppUser = Bll_User::isAppUser($uid);
    
        //get user unlock ship list
        $shipList = Hapyfish_Island_Cache_Dock::getUserUnlockShipList($uid, $pid);
        if ( !in_array($shipId, $shipList) ) {
            $resultVo['content'] = 'serverWord_142';
            return $resultVo;
        }
        
        //get user position info
        $positionInfo = self::getUserPositionById($uid, $pid);
        if ( $positionInfo['ship_id'] == $shipId ) {
            return $resultVo;
        }
        
        
        //get ship info
        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($shipId);
        
        $userPraise = Hapyfish_Island_Cache_User::getPraise($uid);
        
        //get add visitor count by user praise
        $addVisitor = Hapyfish_Island_Cache_Dock::getShipAddVisitorByPraise($shipId, $userPraise);
            
        try{
            //get start visitor num
            $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

            //update new boat
            $newShip = array('ship_id' => $shipId,
                             'status' => 1,
                             'receive_time' => time(),
                             'is_usecard_one' => 0,
                             'speedup' => 0,
                             'speedup_type' => 0,
                             'start_visitor_num' => $startVisitorNum,
                             'remain_visitor_num' => $startVisitorNum);
            
            $dalDock = Dal_Island_Dock::getDefaultInstance();
            $dalDock->updateUserPosition($uid, $pid, $newShip);

            Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);
            Hapyfish_Island_Cache_Dock::cleanUserShipList($uid);
            
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            info_log('[changeShip]:' . $e->getMessage(), 'Hapyfish_Island_Bll_Dock');
            $resultVo['content'] = 'serverWord_110';
            return $resultVo;
        }
        
        return $resultVo;
    }
    
    /**
     * read ship list
     *
     * @param integer $uid
     * @return array
     */
    public static function readShip($uid, $pid)
    {      
        //get user unlock ship list  
        return Hapyfish_Island_Cache_Dock::getUserUnlockShipList($uid, $pid);
    }
    
    /**
     * get user position by pid
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public static function getUserPositionById($uid, $pid)
    {
    	$positionInfo = array();
        $positionList = Hapyfish_Island_Cache_Dock::getUserPositionList($uid);
        
        for ( $i = 0,$iCount = count($positionList); $i < $iCount; $i++ ) {
            if ( $positionList[$i]['position_id'] == $pid ) {
                $positionInfo = $positionList[$i];
            }
        }
        return $positionInfo;
    }
    
    /**
     * get user boat arrive time
     *
     * @param integer $uid
     * @return void
     */
    public static function getArriveTime($uid)
    {
        $dalDock = Dal_Island_Dock::getDefaultInstance();

        //get user dock by status
        $dockInfo = $dalDock->getUserDockByStatus($uid, 2);
        if ( $dockInfo ) {
            return 0;
        }

        //get user dock by status
        $dockInfo = $dalDock->getUserDockByStatus($uid, 1);
        if ( $dockInfo ) {
            $arriveTime = 10000000000;
            for ( $i=0,$iCount=count($dockInfo); $i<$iCount; $i++ ) {
                $time = $dockInfo[$i]['receive_time'] + $dockInfo[$i]['wait_time'] - $dockInfo[$i]['speedup_type'];
                $arriveTime = $time < $arriveTime ? $time : $arriveTime;
            }
            return $arriveTime;
        }

        return 10000000000;
    }
    
    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param integer $pid
     * @return array $boatItem
     */
    public static function getDockShipInfo($uid, $pid)
    {
    	$boatItem = array();
		$now = time();

		$dalDock = Dal_Island_Dock::getDefaultInstance();
    	$boatInfo = $dalDock->getUserPositionById($uid, $pid);
    	$shipInfo = Hapyfish_Island_Cache_Dock::getShip($boatInfo['ship_id']);

		//ship init time
	    $time = $now - $boatInfo['receive_time'] - $shipInfo['wait_time'];
		//is use speed card
		if ($boatInfo['speedup']) {
			$time = $time + $boatInfo['speedup_type'];
		}

	    if ( $boatInfo['status'] != 0 ) {
	    	$visitorNum = $boatInfo['remain_visitor_num'];
			//time < 0 is ship arrival ;
        	if ($time > 0 ) {
    	    	$dalDock->updateUserPosition($uid, $boatInfo['position_id'], array('status' => 2));
    	        $boatInfo['status'] = 2;
    	        Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);
        	}

	    	if ( $boatInfo['status'] == 1 ) {
				$boatItem['state'] = 'onTheRoad';
            }
            //ship arrive status
	    	else if ( $boatInfo['status'] == 2 ) {
		    	//visitor arrive time
		    	$arriveTime = $now - $boatInfo['receive_time'] - $shipInfo['wait_time'];
		        if ( $arriveTime >= $shipInfo['safe_time_1'] ) {
		            $boatItem['state'] = 'arrive_2';
		        }
		        else if ( $arriveTime >= $shipInfo['safe_time_2'] ) {
		            $boatItem['state'] = 'arrive_3';
		        }
		        else {
		        	$boatItem['state'] = 'arrive_1';
		        }
			}

			$boatItem['visitorNum'] = $visitorNum;
			$boatItem['maxVisitorNum'] = $boatInfo['start_visitor_num'];
			$boatItem['id'] = $boatInfo['position_id'];
    		$boatItem['boatId'] = $boatInfo['ship_id'];
	    }
	    else {
			$boatItem['state'] = 'empty';
	    }

		$boatItem['time'] = $time;

		return $boatItem;
    }
    
    /**
     * init dock
     *
     * @param integer $uid
     * @return array
     */
    public static function initDock($uid, $viewUid)
    {
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result = array('status' => -1, 'content' => 'serverWord_101');
            return $result;
        }
        
		$resultVo = array();
		$boatVo = array();
		
		//get user position count
		$userPositionCount = Hapyfish_Island_Cache_User::getPositionCount($uid);
		
		//get user position list
		$userPositionArray = Hapyfish_Island_Cache_Dock::getUserPositionList($uid);
		
		for ( $j = 0; $j < $userPositionCount; $j++ ) {
			$boatVo[] = self::getDockShipInfoForInitDock($uid, $userPositionArray[$j], $viewUid);
		}
		
        $resultVo['boatPositions'] = $boatVo;
        $resultVo['uid'] = $uid;
        $resultVo['positionNum'] = $userPositionCount;
		
		return $resultVo;
    }

    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param array $boatInfo
     * @return array $boatItem
     */
    public static function getDockShipInfoForInitDock($uid, $boatInfo, $viewUid)
    {
        $boatItem = array();
        $now = time();

        $shipInfo = Hapyfish_Island_Cache_Dock::getShip($boatInfo['ship_id']);
        //ship init time
        $time = $now - $boatInfo['receive_time'] - $shipInfo['wait_time'];
        //is use speed card
        if ($boatInfo['speedup']) {
            $time = $time + $boatInfo['speedup_type'];
        }

        if ( $boatInfo['status'] != 0 ) {
            $visitorNum = $boatInfo['remain_visitor_num'];
            //time < 0 is ship arrival ;
            if ($time > 0 ) {
            	$dalDock = Dal_Island_Dock::getDefaultInstance();
                $dalDock->updateUserPosition($uid, $boatInfo['position_id'], array('status' => 2));
                $boatInfo['status'] = 2;
                Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);
            }

            if ( $boatInfo['status'] == 1 ) {
                $boatItem['state'] = 'onTheRoad';
            }
            //ship arrive status
            else if ( $boatInfo['status'] == 2 ) {
                //visitor arrive time
                $arriveTime = $now - $boatInfo['receive_time'] - $shipInfo['wait_time'];
                if ( $arriveTime >= $shipInfo['safe_time_1'] ) {
                    $boatItem['state'] = 'arrive_2';
                }
                else if ( $arriveTime >= $shipInfo['safe_time_2'] ) {
                    $boatItem['state'] = 'arrive_3';
                }
                else {
                    $boatItem['state'] = 'arrive_1';
                }
            }

            $boatItem['visitorNum'] = $visitorNum;
            $boatItem['maxVisitorNum'] = $boatInfo['start_visitor_num'];
            $boatItem['id'] = $boatInfo['position_id'];
            $boatItem['boatId'] = $boatInfo['ship_id'];
            
            $boatItem['canSteal'] = true;
            if ( $uid != $viewUid ) {
		        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
		        //get mooch info
		        $moochInfo = $dalMooch->hadMoochDock($viewUid, $uid, $boatInfo['position_id']);
		        if ( $moochInfo ) {
		        	$boatItem['canSteal'] = false;
		        }
            }
        }
        else {
            $boatItem['state'] = 'empty';
        }

        $boatItem['time'] = $time;

        return $boatItem;
    }

}