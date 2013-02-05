<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Bll_Island_Dock extends Bll_Abstract {

    /**
     * mooch visitor
     *
     * @param integer $uid
     * @param integer $ownerUid
     * @param integer $positionId
     * @return array
     */
    public function mooch($uid, $ownerUid, $positionId)
    {
        $result = array('status' => -1);
        $now = time();
    
        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //get mooch info
        $moochInfo = $dalMooch->hadMoochDock($uid, $ownerUid, $positionId);
        if ( $moochInfo ) {
            $result['content'] = 'serverWord_102';
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
        
        //get user position info by position id
        $positionInfo = $this->getUserPositionById($ownerUid, $positionId);
        if ( $positionInfo['status'] != 2 ) {
            $result['content'] = 'serverWord_156';
            return array('result' => $result);
        }
        
        //get ship info
        $shipInfo = Bll_Cache_Island::getShip($positionInfo['ship_id']);

        //get visitor arrive time
        $arriveTime = $now - $positionInfo['receive_time'] - $shipInfo['wait_time'];
        $remainNum = $positionInfo['remain_visitor_num'] - $shipInfo['safe_visitor_num'];
        if ( $remainNum <= 0 ) {
            $result['content'] = 'serverWord_132';
            $result = array('result' => $result);
            return $result;
        }
        
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result['content'] = 'serverWord_101';
            $result = array('result' => $result);
            return $result;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserForMooch($uid);
        
        //check user currently visitor
        $islandLevelInfo = Bll_Cache_Island::getIslandLevelInfo($islandUser['island_level']);
        if ( $islandLevelInfo['visitor_count'] <= $islandUser['currently_visitor'] ) {
            $result['content'] = 'serverWord_133';
            $result = array('result' => $result);
            return $result;
        }
    
        //get owner info
        $islandOwner = $dalUser->getUserForMoochOwner($ownerUid);
        if ( !$islandOwner ) {
            return array('result' => $result);
        }
        
        //check owner whether have receive_card
        if ($now - $islandOwner['insurance_card'] <= 6*3600){
            $result['content'] = 'serverWord_134';
            $result = array('result' => $result);
            return $result;
        }

        //mooch num,visitor feeling type
        if ( $arriveTime <= $shipInfo['safe_time_1'] ) {
            $moochNum = 1;
        }
        else if ( $arriveTime <= $shipInfo['safe_time_2'] ) {
            $moochNum = 1;
        }
        else {
            $moochNum = rand(1, 2);
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
        
        if ( $moochNum + $islandUser['currently_visitor'] > $islandLevelInfo['visitor_count'] ) {
            $moochNum = $islandLevelInfo['visitor_count'] - $islandUser['currently_visitor'];
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
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user position info by position id
            $positionInfoForUpdate = $dalDock->getUserPositionByIdForupdate($ownerUid, $positionId);
            $remainNum = $positionInfoForUpdate['remain_visitor_num'] - $shipInfo['safe_visitor_num'];
            if ( $remainNum <= 0 ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_132';
                $result = array('result' => $result);
                return $result;
            }
            
            $moochStatus = 1;
            $feedType = 1;

            //check insurance card
            if ( $now - $islandOwner['insurance_card'] <= 6*3600 ) {
                $result['content'] = 'serverWord_134';
                $moochStatus = -1;
                $feedType = 2;
            }

            $visitorsAry = $this->moochVisitors($uid, $moochNum);
            $helpAry = $this->moochVisitors($ownerUid, $helpNum);
            
            $newUser = array();
            if ( $moochStatus == 1 ) {
                //update onwer position info
                $newPosition = array('remain_visitor_num' => $positionInfo['remain_visitor_num'] - $allNum);
                $dalDock->updateUserPosition($ownerUid, $positionId, $newPosition);

                //update user visitor num
                $newUser['queue_visitor'] = $islandUser['queue_visitor'] + $moochNum;
            }

            $expChange = 2;
            //update user
            $newUser['exp'] = $islandUser['exp'] + $expChange;
            $dalUser->updateUser($uid, $newUser);

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
            
            //end of transaction
            $this->_wdb->commit();

            //check level up
            $levelUp = $this->checkLevelUp($uid);
            
            Bll_Cache_Island_Dock::clearCache('getUserPositionList', $ownerUid);
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today achievement,num_5
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_5', 1);
            //update user today achievement,num_7
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_7', $moochNum);
            //update user achievement,mooch_count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_5', 1);

            //insert mooch info
            $newMooch = array('uid' => (string)$uid,
                              'owner_uid' => (string)$ownerUid,
                              'pid' => (string)$positionId);
            $dalMooch->insertMoochDock($newMooch);
            
            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[mooch]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }

        if ( $feedType == 1 ) {
            $minifeed = array('uid' => $ownerUid,
                              'template_id' => 20,
                              'actor' => $uid,
                              'target' => $ownerUid,
                              'title' => array('visitorNum' => $moochNum, 'helpNum' => $helpNum),
                              'type' => 2,
                              'create_time' => $now);
            Bll_Island_Feed::insertMiniFeed($minifeed);
	        if ( $sendCard == 1 ) {
	            $cardInfo = Bll_Cache_Island::getCardById($cardId);
	            $minifeedCard = array('uid' => $ownerUid,
	                              'template_id' => 19,
	                              'actor' => $uid,
	                              'target' => $ownerUid,
	                              'title' => array('cardName' => $cardInfo['name']),
	                              'type' => 3,
	                              'create_time' => time());
	            Bll_Island_Feed::insertMiniFeed($minifeedCard);
	        }
        }
    
        //send activity
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
            $result['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

        if (empty($visitorsAry)) {
            $visitorsAry = array();
        }

        $result = array('result' => $result, 'visitors' => $visitorsAry, 'friendVisitors' => $helpAry, 'cardCid' => $cardId);
        return $result;
    }
	
    /**
     * mooch visitor
     *
     * @param integer $uid
     * @param integer $ownerUid
     * @param integer $positionId
     * @return array
     */
    public function moochOld($uid, $ownerUid, $positionId)
    {
        $result = array('status' => -1);
        $now = time();
    
        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //get mooch info
        $moochInfo = $dalMooch->hadMoochDock($uid, $ownerUid, $positionId);
        if ( $moochInfo ) {
            $result['content'] = 'serverWord_102';
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
        
        //get user position info by position id
        $positionInfo = $this->getUserPositionById($ownerUid, $positionId);
        if ( $positionInfo['status'] != 2 ) {
        	$result['content'] = 'serverWord_156';
            return array('result' => $result);
        }
        
        //get ship info
        $shipInfo = Bll_Cache_Island::getShip($positionInfo['ship_id']);

        //get visitor arrive time
        $arriveTime = $now - $positionInfo['receive_time'] - $shipInfo['wait_time'];
        $remainNum = $positionInfo['remain_visitor_num'] - round($shipInfo['start_visitor_num']/2);
        if ( $remainNum <= 0 ) {
            $result['content'] = 'serverWord_132';
            $result = array('result' => $result);
            return $result;
        }
        
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result['content'] = 'serverWord_101';
            $result = array('result' => $result);
            return $result;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserForMooch($uid);
        
        //check user currently visitor
        $islandLevelInfo = Bll_Cache_Island::getIslandLevelInfo($islandUser['island_level']);
        if ( $islandLevelInfo['visitor_count'] <= $islandUser['currently_visitor'] ) {
            $result['content'] = 'serverWord_133';
            $result = array('result' => $result);
            return $result;
        }
    
        //get owner info
        $islandOwner = $dalUser->getUserForMoochOwner($ownerUid);
        if ( !$islandOwner ) {
            return array('result' => $result);
        }
        
        //check owner whether have receive_card
        if ($now - $islandOwner['insurance_card'] <= 6*3600){
			$result['content'] = 'serverWord_134';
			$result = array('result' => $result);
            return $result;
        }

        //mooch num,rand 1
        $moochNum = 1;
        //visitor feeling type
        if ( $arriveTime <= $shipInfo['safe_time_2'] ) {
            $moochNum = 2;
        }
        else {
            $moochNum = 3;
        }

        if ( $moochNum > $remainNum ) {
            $moochNum = $remainNum;
        }
        
        if ( $moochNum + $islandUser['currently_visitor'] > $islandLevelInfo['visitor_count'] ) {
        	$moochNum = $islandLevelInfo['visitor_count'] - $islandUser['currently_visitor'];
        }

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
        	//get user position info by position id
            $positionInfoForUpdate = $dalDock->getUserPositionByIdForupdate($ownerUid, $positionId);
	        $remainNum = $positionInfoForUpdate['remain_visitor_num'] - round($shipInfo['start_visitor_num']/2);
	        if ( $remainNum <= 0 ) {
	        	$this->_wdb->rollBack();
	            $result['content'] = 'serverWord_132';
	            $result = array('result' => $result);
	            return $result;
	        }
	        
            $moochStatus = 1;
            $feedType = 1;

            //check insurance card
            if ( $now - $islandOwner['insurance_card'] <= 6*3600 ) {
                $result['content'] = 'serverWord_134';
                $moochStatus = -1;
                $feedType = 2;
            }

            $newUser = array();
            if ( $moochStatus == 1 ) {
                //update onwer position info
                $newPosition = array('remain_visitor_num' => $positionInfo['remain_visitor_num'] - $moochNum);
                $dalDock->updateUserPosition($ownerUid, $positionId, $newPosition);

                //update user visitor num
                $newUser['queue_visitor'] = $islandUser['queue_visitor'] + $moochNum;
            }

            $expChange = 2;
            //update user
            $newUser['exp'] = $islandUser['exp'] + $expChange;
            $dalUser->updateUser($uid, $newUser);

            $visitorsAry = $this->moochVisitors($uid, $moochNum);
            
            //end of transaction
            $this->_wdb->commit();

            //check level up
            $levelUp = $this->checkLevelUp($uid);
            
            Bll_Cache_Island_Dock::clearCache('getUserPositionList', $ownerUid);
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today achievement,num_5
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_5', 1);
            //update user today achievement,num_7
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_7', $moochNum);
            //update user achievement,mooch_count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_5', 1);

            //insert mooch info
            $newMooch = array('uid' => (string)$uid,
                              'owner_uid' => (string)$ownerUid,
                              'pid' => (string)$positionId);
            $dalMooch->insertMoochDock($newMooch);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[mooch]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }

        if ( $feedType == 1 ) {
            $minifeed = array('uid' => $ownerUid,
                              'template_id' => 12,
                              'actor' => $uid,
                              'target' => $ownerUid,
                              'title' => array('visitor_num' => $moochNum),
                              'type' => 2,
                              'create_time' => $now);
            Bll_Island_Feed::insertIslandMinifeed($minifeed);
        }

        //send activity
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
            $result['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

		if (empty($visitorsAry)) {
			$visitorsAry = array();
		}

        $result = array('result' => $result, 'visitors' => $visitorsAry);
        return $result;
    }

    /**
     * check level up
     *
     * @param integer $uid
     * @return boolean
     */
    public function checkLevelUp($uid)
    {
        $levelUp = false;
        $giftName = '';
        $islandLevelUp = false;

        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserLevelInfo($uid);

        if ( $islandUser['exp'] >= $islandUser['next_level_exp'] ) {
            $levelUp = true;
            //get next level info
            $nextLevel = Bll_Cache_Island::getUserLevelInfo($islandUser['level'] + 2);
            //send gift
            $levelGift = Bll_Cache_Island::getLevelGift($islandUser['level'] + 1);
            
            $sendGold = $levelGift[0]['gold'] > 0 ? $levelGift[0]['gold'] : 2;
            
            //update user info, level up atfer gold + 2
            $newUser = array('level' => $islandUser['level'] + 1,
                             'next_level_exp' => $nextLevel['exp'],
                             'gold' => $islandUser['gold'] + $sendGold);

            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            $newIslandLevel = Bll_Cache_Island::getIslandLevelInfoByUserLevel($islandUser['level'] + 1);
            
            //check island level up
            if ( $newIslandLevel['level'] != $islandUser['island_level'] ) {
                $islandLevelUp = true;
                $newUser['island_level'] = $newIslandLevel['level'];

                //upgrade user building info
                $dalBuilding->upgradeUserBuilding($uid);
            }
            $dalUser->updateUser($uid, $newUser);
            
            //add user gold info
            $dalPayLog = Dal_PayLog::getDefaultInstance();
            $dalPayLog->addLog($uid, $sendGold);
            
            $dalCard = Dal_Island_Card::getDefaultInstance();
        	$dalPlant = Dal_Island_Plant::getDefaultInstance();

            $now = time();
            for ( $i=0,$iCount=count($levelGift); $i<$iCount; $i++ ) {
                $itemType = substr($levelGift[$i]['cid'], -2);
                $type = substr($levelGift[$i]['cid'], -2, 1);

                if ( $i == 0 ) {
                    $giftName = $levelGift[$i]['name'];
                }
                else {
                    $giftName = $giftName . ',' . $levelGift[$i]['name'];
                }

                if ( $type == 1 ) {
                    $newBuilding = array('uid' => $uid,
                                         'bgid' => $levelGift[$i]['cid'],
                                         'status'=>0,
                                         'buy_time'=> $now,
                                         'item_type' => $itemType);
                    //add user Building
                    $dalBuilding->insertUserBackground($newBuilding);
                }
                else if ( $type == 2 ) {
                    $newBuilding = array('uid' => $uid,
                                         'bid' => $levelGift[$i]['cid'],
                                         'status'=> 0,
                                         'buy_time'=> $now,
                                         'item_type' => $itemType);
                    //add user Building
                    $dalBuilding->addUserBuilding($newBuilding);
                }
                else if ( $type == 3 ) {
                	$plantInfo = Bll_Cache_Island::getPlantById($levelGift[$i]['cid']);
                	
                    $newPlant = array('uid' => $uid,
                                      'bid' => $levelGift[$i]['cid'],
                                      'status'=> 0,
                                      'item_id' => $plantInfo['item_id'],
                                      'buy_time'=> $now,
                                      'item_type' => $itemType);
                    //add user plant
                    $dalPlant->insertUserPlant($newPlant);
                }
                else if ( $type == 4 ) {
                    $newCard = array('uid' => $uid,
                                     'cid' => $levelGift[$i]['cid'],
                                     'count' => 1,
                                     'buy_time' => $now,
                                     'item_type' => $itemType);
                    //add user card
                    $dalCard->addUserCard($newCard);
                }
            }
        }

        $result = array('levelUp' => $levelUp,
                        'islandLevelUp' => $islandLevelUp,
                        'giftName' => $giftName);
        if (isset($newUser['island_level'])) {
            $result['newIslandLevel'] = $newUser['island_level'];
        }
        
        return $result;
    }

    /**
     * get power count
     *
     * @param integer $uid
     * @return integer
     */
    public function getPower($uid)
    {
        $friendIds = Bll_Friend::getFriends($uid);

        if ( $friendIds ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get app friend count
            $friendCount = $dalUser->getAppFriendCount($friendIds);
        }
        else {
            $friendCount = 0;
        }

        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        //update user achievement friend count
        $userAchievementFriendCount = $dalMongoAchievement->getUserAchievementByField($uid, 'num_16');
        if ( $userAchievementFriendCount < $friendCount ) {
            $dalMongoAchievement->updateUserAchievement($uid, array('num_16' => $friendCount));
        }

        return $friendCount;
    }

/*****************************************************************************************/

    /**
     * init dock
     *
     * @param integer $uid
     * @return array
     */
    public function initDock($uid, $viewUid)
    {
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result = array('status' => -1, 'content' => 'serverWord_101');
            return $result;
        }
        
		$resultVo = array();
		$boatVo = array();
		
		//get user position count
        $userPositionCount = Bll_Cache_Island_Dock::getUserPositionCount($uid);
		
		//get user position list
		$userPositionArray = Bll_Cache_Island_Dock::getUserPositionList($uid);
		
		for ( $j = 0; $j < $userPositionCount; $j++ ) {
			$boatVo[] = $this->getDockShipInfoForInitDock($uid, $userPositionArray[$j], $viewUid);
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
    public function getDockShipInfoForInitDock($uid, $boatInfo, $viewUid)
    {
        $boatItem = array();
        $now = time();

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        $shipInfo = Bll_Cache_Island::getShip($boatInfo['ship_id']);

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
                Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
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
    
    /**
     * get dock ship info
     *
     * @param integer $uid
     * @param integer $pid
     * @return array $boatItem
     */
    public function getDockShipInfo($uid, $pid)
    {
    	$boatItem = array();
		$now = time();

		$dalDock = Dal_Island_Dock::getDefaultInstance();
    	$boatInfo = $dalDock->getUserPositionById($uid, $pid);
    	$shipInfo = Bll_Cache_Island::getShip($boatInfo['ship_id']);

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
    	        Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
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
     * receive boat
     * @param $uid integer
     * @param $pid integer
     * @return $result array
     */
    public function receiveBoat($uid, $pid)
    {
		$resultVo = array('status' => -1);
		$now = time();
		
		$isAppUser = Bll_User::isAppUser($uid);
		if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            return array('result' => $resultVo);
		}

		$boatInfo = $this->getUserPositionById($uid, $pid);
        if ($boatInfo['remain_visitor_num'] == 0) {
            return array('result' => $resultVo);
        }
    
        $shipInfo = Bll_Cache_Island::getShip($boatInfo['ship_id']);
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

    	$dalUser = Dal_Island_User::getDefaultInstance();
    	$userInfo = $dalUser->getUserForReceive($uid);

    	//get island level info
    	$levelInfo = Bll_Cache_Island::getIslandLevelInfo($userInfo['island_level']);
		//island visitor
		$visitorCount = $levelInfo['visitor_count'];

        $bllUser = new Bll_Island_User();
    	//check island have 1 minute do not go out people
    	if ($userInfo['currently_visitor']) {
    		//out island
			$bllUser->outIslandPeople($uid);

			$userInfo = $dalUser->getUserForReceive($uid);
	    	if ($userInfo['currently_visitor'] == $visitorCount) {
	    		$resultVo['content'] = 'serverWord_133';
				return array('result' => $resultVo);
	    	}
    	}

        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //delete mooch table info
        $dalMooch->deleteMoochDock($uid, $pid);

        //receiveNum
        $receiveNum = $boatInfo['remain_visitor_num'];
        if ( $userInfo['currently_visitor'] > 0 ) {
            $receiveNum = min($visitorCount - $userInfo['currently_visitor'], $receiveNum);
            if ( $receiveNum < 0 ) {
                info_log('uid:'.$userInfo['uid'].'-receiveNum:'.$receiveNum, 'dock_add_visitor');
                $receiveNum = 0;
                return array();
            }
        }
        else if ($visitorCount < $receiveNum) {
            $receiveNum = $visitorCount;
        }

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        try{
        	//begin transaction
        	$this->_wdb->beginTransaction();

			$positionAry = array();
			$visitorNum = 0;
			if ($receiveNum == $boatInfo['remain_visitor_num']) {
                //get add visitor count by user praise
                $addVisitor = Bll_Cache_Island::getShipAddVisitorByPraise($boatInfo['ship_id'], $userInfo['praise']);
        
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
			
			$dalDock->updateUserPosition($uid, $pid, $positionAry);

			//add data
			$addExp = 3;
			//update user boat arrive time
			$arriveTime = $this->getArriveTime($uid);
			$dalUser->updateUser($uid, array('boat_arrive_time' => $arriveTime,
											 'exp' => $userInfo['exp'] + $addExp));
			
            $visitorsAry = $this->visitorsInvite($uid, $receiveNum);
            
			$this->_wdb->commit();
			
			$levelUp = $this->checkLevelUp($uid);

			Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            
			$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
			//update today achievement
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_1', 1);
            //upate achievement
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_10', 1);

			//isitor arrive pay
			$resultVo['levelUp'] = $levelUp['levelUp'];
			$resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			$resultVo['expChange'] = $addExp;
			$resultVo['islandChange'] = true;
			$resultVo['status'] = 1;

		}catch (Exception $e) {
			$this->_wdb->rollBack();
			info_log('[error_message]-[receiveBoat]:'.$e->getMessage(), 'transaction');
			$resultVo['content'] = 'serverWord_110';
            return array('result' => $resultVo);
		}

		//send activity
		if ( $levelUp['levelUp'] ) {
		    $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
		    $resultVo['feed'] = $feed;
		}
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

		if (empty($visitorsAry)) {
			$visitorsAry = array();
		}

		return array('result' => $resultVo, 'visitors' => $visitorsAry, 'visitorNum' => $visitorNum);
    }

    /**
     * add boat
     *
     * @param integer $uid
     * @return array ResultVo
     */
   public function addBoat($uid)
    {
		$resultVo = array('status' => -1);

		$isAppUser = Bll_User::isAppUser($uid);
		if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            return $resultVo;
		}
		
        $dalUser = Dal_Island_User::getDefaultInstance();
    	$userInfo = $dalUser->getUserForAddBoat($uid);

		//check position count
		if ($userInfo['position_count'] == 8) {
			return $resultVo;
		}

		//position_count +1
		$ifAry = Bll_Cache_Island::getAddBoatByid($userInfo['position_count'] + 1);
		//add exp
		$addExp = 9;

    	if ($userInfo['level'] < $ifAry['level']) {
			$resultVo['content'] = 'serverWord_136';
			return $resultVo;
		}

		if ($userInfo['coin'] < $ifAry['price']) {
			$resultVo['content'] = 'serverWord_137';
			return $resultVo;
		}

		if ($this->getPower($uid) < $ifAry['power']) {
		    $resultVo['content'] = 'serverWord_138';
            return $resultVo;
		}

		$shipId = 1;
        //get ship info
        $shipInfo = Bll_Cache_Island::getShip($shipId);
        //get add visitor count by user praise
        $addVisitor = Bll_Cache_Island::getShipAddVisitorByPraise($shipId, $userInfo['praise']);
        
        //get start visitor num
        $startVisitorNum = $addVisitor + $shipInfo['start_visitor_num'];

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        //begin transaction
        $this->_wdb->beginTransaction();
		try{
		    //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ( $forUpdateUser['coin'] < $ifAry['price'] ) {
                $this->_wdb->rollBack();
                $resultVo['content'] = 'serverWord_137';
                return $resultVo;
            }

			//insert new boat
        	$boatAry['uid'] = $uid;
        	$boatAry['position_id'] = $userInfo['position_count'] + 1;
        	$boatAry['status'] = 1;
        	$boatAry['ship_id'] = $shipInfo['sid'];
        	$boatAry['start_visitor_num'] = $startVisitorNum;
        	$boatAry['remain_visitor_num'] = $startVisitorNum;
			$dalDock->insertDock($boatAry);

			//update user info
			$userAry['position_count'] = $userInfo['position_count'] + 1;
			$userAry['coin'] = $userInfo['coin'] - $ifAry['price'];
			$userAry['exp'] = $userInfo['exp'] + $addExp;
			$dalUser->updateUser($uid, $userAry);

			$newUnlockShip = array('uid' => $uid,
			                       'position_id' => $boatAry['position_id'],
			                       'ship_id' => $boatAry['ship_id']);
			$dalDock->insertUserShip($newUnlockShip);
			
			$this->_wdb->commit();
			
            $levelUp = $this->checkLevelUp($uid);
			
			Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
			Bll_Cache_Island_Dock::clearCache('getUserPositionCount', $uid);

			$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement,num_12
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_12', 1);
            //update user buy coin
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $ifAry['price']);

            $resultVo['expChange'] = + $addExp;
            $resultVo['coinChange'] = - $ifAry['price'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['status'] = 1;
		}
		catch (Exception $e) {
			$this->_wdb->rollBack();
			info_log('[error_message]-[addBoat]:'.$e->getMessage(), 'transaction');
			$resultVo['content'] = 'serverWord_110';
            return $resultVo;
		}

        //send activity
        $imageName = $dalDock->getUserDockImage($uid);
        $feed = Bll_Island_Activity::send('DOCK_EXPANSION', $uid, array('expanOld'=>$userInfo['position_count'], 'expanNew'=>$userInfo['position_count']+1, 'img'=>$imageName));
        $resultVo['feed'] = $feed;
        
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
            $resultVo['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
    public function visitorsInvite($uid, $number)
    {
		$visitorsAry = array();
		$now = time();

        $dalUser = Dal_Island_User::getDefaultInstance();
    	$userInfo = $dalUser->getUserForVisitor($uid);

		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$plantInfo1 = Bll_Cache_Island_User::getUserPlantListAll($uid);
		$plantInfo2 = Bll_Cache_Island_User::getListIslandPlant($uid);
		
        for ( $j=0,$jCount=count($plantInfo1); $j<$jCount; $j++ ) {
            if ( $plantInfo1[$j]['can_find'] != 1 ) {
                unset($plantInfo1[$j]);
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

        $plantInfo3 = Bll_Cache_Island::getPlantListByLevel($userInfo['level']);
    
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
		if ($plantInfo1 && $num1) {
			//rand distribution
	    	for ($i = 0; $i < $num1; $i++ )
			{
				$randNum = array_rand($plantInfo1);
				$plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
			}

			$result1 = array();
			//update db
			foreach ($plantInfo1 as $key => $value) {
				$resultAry = array();
				if ( $plantInfo1[$key]['num'] != 0 ) {
					$updatePlant = array();
					if ( $value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
						$updatePlant['start_pay_time'] = $now;
						//event 
					    if (($now - $userInfo['defense_card']) >= 12*3600 ) {
                            $rand = rand(1, 100);
                            if ( $rand < 11 ) {
                                $updatePlant['event'] = 1;
                                $value['eventId'] = 1;
                            }
                        }
					}

                    $updatePlant['uid'] = $value['uid'];
					$updatePlant['wait_visitor_num'] = $value['wait_visitor_num'] + $value['num'];
                    
					//update db
					$dalPlant->updateUserPlant($value['id'], $updatePlant);
					$updateVisitor = $updateVisitor + $value['num'];

					$resultAry['itemId'] = $value['itemId'];
					$resultAry['cid'] = $value['cid'];
					$resultAry['num'] = $value['num'];
					$resultAry['eventId'] = $value['eventId'];

					$result1[] = $resultAry;
				}
				//delete 'id' property
				array_shift($plantInfo1[$key]);
			}
			//update user table visitor
			if ($updateVisitor) {
			    $dalUser->updateUser($uid, array('currently_visitor' => $userInfo['currently_visitor'] + $updateVisitor));
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
    public function moochVisitors($uid, $number)
    {
        $visitorsAry = array();
        $now = time();

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //$plantInfo1 = $dalPlant->getListUserPlantAllById($uid);
        $plantInfo1 = Bll_Cache_Island_User::getUserPlantListAll($uid);
        
        for ( $j=0,$jCount=count($plantInfo1); $j<$jCount; $j++ ) {
        	if ( $plantInfo1[$j]['can_find'] != 1 ) {
        		unset($plantInfo1[$j]);
        	}
        }
        
        if ( !$plantInfo1 ) {
            return $visitorsAry;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        $userInfo = $dalUser->getUserForVisitor($uid);

        $updateVisitor = 0;
        if ($plantInfo1 && $number) {
            //rand distribution
            for ($i = 0; $i < $number; $i++ )
            {
                $randNum = array_rand($plantInfo1);
                $plantInfo1[$randNum]['num'] = $plantInfo1[$randNum]['num'] + 1 ;
            }

            $visitorsAry = array();
            //update db
            foreach ($plantInfo1 as $key => $value) {
                $resultAry = array();

                if ( $plantInfo1[$key]['num'] != 0 ) {
                    $updatePlant = array();
                    if ( $value['wait_visitor_num'] == 0 && $value['start_deposit'] == 0) {
                        $updatePlant['start_pay_time'] = $now;
                        if (($now - $userInfo['defense_card']) >= 12*3600 ) {
                            $rand = rand(1, 100);
                            if ( $rand < 11 ) {
                                $updatePlant['event'] = 1;
                                $value['eventId'] = 1;
                            }
                        }
                    }

                    $updatePlant['uid'] = $value['uid'];
                    $updatePlant['wait_visitor_num'] = $value['wait_visitor_num'] + $value['num'];

                    //update db
                    $dalPlant->updateUserPlant($value['id'], $updatePlant);
                    $updateVisitor = $updateVisitor + $value['num'];

                    $resultAry['itemId'] = $value['itemId'];
                    $resultAry['cid'] = $value['cid'];
                    $resultAry['num'] = $value['num'];
                    $resultAry['eventId'] = $value['eventId'];

                    $visitorsAry[] = $resultAry;
                }
                //delete 'id' property
                array_shift($plantInfo1[$key]);
            }
            
            //update user table visitor
            if ($updateVisitor) {
                $dalUser->updateUser($uid, array('currently_visitor' => $userInfo['currently_visitor'] + $updateVisitor));
            }
        }

        return $visitorsAry;
    }
    
    /**
     * read ship list
     *
     * @param integer $uid
     * @return array
     */
    public function readShip($uid, $pid)
    {      
        //get user unlock ship list  
        $shipList = Bll_Cache_Island_Dock::getUserUnlockShipList($uid, $pid);
        return $shipList;
    }
    
    /**
     * unlock ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $priceType
     * @return array
     */
    public function unlockShip($uid, $shipId, $pid, $priceType)
    {
        $resultVo = array('status' => -1);
        
        if( $shipId<1 || $shipId > 8 ) {
            return $resultVo;
        }
    
        if( $pid<1 || $pid > 8 ) {
            return $resultVo;
        }
        
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            return $resultVo;
        }
        
        //get user unlock ship list
        $shipList = Bll_Cache_Island_Dock::getUserUnlockShipList($uid, $pid);
        if ( in_array($shipId, $shipList) ) {
            $resultVo['content'] = 'serverWord_139';
            return $resultVo;
        }
        
        //get db ship info
        $shipInfo = Bll_Cache_Island::getShip($shipId);
        
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

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        //begin transaction
        $this->_wdb->beginTransaction();
        try{
            //get user for update
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ($forUpdateUser[$price] < $shipInfo[$ifPrice]) {
                $resultVo['content'] = $wordType;
                return $resultVo;
            }

            //update user info
            $userAry = array('exp' => 0);
            if ( $priceType == 1 ) {
                $userAry['coin'] = $forUpdateUser['coin'] - $shipInfo['coin'];
            }
            else {
                $userAry['gold'] = $forUpdateUser['gold'] - $shipInfo['gem'];
            }

            $addExp = 9;
            $userAry['exp'] = $forUpdateUser['exp'] + $addExp;

            $dalUser->updateUser($uid, $userAry);

            $dalDock->updateUserShip(array('uid' => $uid, 'position_id' => $pid, 'ship_id' => $shipId));
            
            $bllDock = new Bll_Island_Dock();
            
            $this->_wdb->commit();
            
            if ( $shipInfo['gem'] > 0 && $priceType != 1 ) {
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'gold' => $shipInfo['gem'],
                                      'remain_gold' => $forUpdateUser['gold'] - $shipInfo['gem'],
                                      'item_id' => $shipInfo['sid'],
                                      'name' => $pid.'号船位解锁'.$shipInfo['name'],
                                      'count' => 1,
                                      'content' => '[unlockShip]:newShip='.$shipId.',positionId='.$pid,
                                      'create_time' => time());
                $dalGold = Dal_Island_Gold::getDefaultInstance();
                $dalGold->insertUserGoldInfo($userGoldInfo);
            }
            
            //check level up
            $levelUp = $bllDock->checkLevelUp($uid);
            
            Bll_Cache_Island_Dock::clearUnlockShipList($uid, $pid);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement ship level
            $userAchievementShipLevel = $dalMongoAchievement->getUserAchievementByField($uid, 'num_11');
            if ( $userAchievementShipLevel < $shipInfo['sid'] + 1 ) {
                $dalMongoAchievement->updateUserAchievement($uid, array('num_11' => $shipInfo['sid'] + 1));
            }
            
            if ( $priceType == 1 ) {
	            //update user buy coin
	            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_14', $shipInfo['coin']);
            }

            $resultVo['expChange'] = + $addExp;
            if ( $priceType == 1 ) {
                $resultVo['coinChange'] = -$shipInfo['coin'];
            }
            else {
                $resultVo['goldChange'] = -$shipInfo['gem'];
            }
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[unlockShip]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return $resultVo;
        }
    
        //send activity
        $feed = Bll_Island_Activity::send('BOAT_LEVEL_UP', $uid, array('boat'=>$shipInfo['name'], 'img'=>$shipInfo['class_name']));
        $resultVo['feed'] = $feed;
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $forUpdateUser['level'] + 1));
            $resultVo['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }
        
        return $resultVo;
    }

    /**
     * change ship 
     *
     * @param integer $uid
     * @param integer $shipId
     * @param integer $pid
     * @return array
     */
    public function changeShip($uid, $shipId, $pid)
    {
        $resultVo = array('status' => -1);
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $resultVo['content'] = 'serverWord_101';
            return $resultVo;
        }
    
        //get user unlock ship list
        $shipList = Bll_Cache_Island_Dock::getUserUnlockShipList($uid, $pid);
        if ( !in_array($shipId, $shipList) ) {
            $resultVo['content'] = 'serverWord_142';
            return $resultVo;
        }
        
        //get user position info
        $positionInfo = $this->getUserPositionById($uid, $pid);
        if ( $positionInfo['ship_id'] == $shipId ) {
            return $resultVo;
        }
        
        $dalDock = Dal_Island_Dock::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get ship info
        $shipInfo = Bll_Cache_Island::getShip($shipId);
    
        $userPraise = $dalUser->getUserPraise($uid);
        
        //get add visitor count by user praise
        $addVisitor = Bll_Cache_Island::getShipAddVisitorByPraise($shipId, $userPraise);
            
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
            $dalDock->updateUserPosition($uid, $pid, $newShip);

            Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);
            Bll_Cache_Island_User::clearCache('getUserShipList', $uid);
            
            $resultVo['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[changeShip]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return $resultVo;
        }
        
        return $resultVo;
    }
    
    /**
     * get user position by pid
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function getUserPositionById($uid, $pid)
    {
        $positionList = Bll_Cache_Island_Dock::getUserPositionList($uid);
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
    public function getArriveTime($uid)
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
}