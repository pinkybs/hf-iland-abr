<?php

class Hapyfish_Island_Bll_Plant
{
    /**
     * gain plant
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
	public static function harvestPlant($uid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        
        //out plant people
        Hapyfish_Island_Bll_User::outPlantPeople($uid, $itemId);
		
        $userPlant = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($uid, $itemId);

        if ( !$userPlant || $userPlant['uid'] != $uid ) {
        	info_log(json_encode($userPlant), 'aaaaa');
            $result['content'] = 'serverWord_115';
            return $result;
        }

        //check plant visitor
        if ( $userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 ) {
            return $result;
        }
    
        if ( $userPlant['event'] == 2 ) {
            $result['content'] = 'serverWord_121';
            return $result;
        }
    
        //check plant deposit
        if ( $userPlant['deposit'] <= 0 ) {
            return $result;
        }
        
        //get plant info by bid
        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['cid']);
        $nowTime = time();
        //check plant event info
        if ( $userPlant['event'] == 1 ) {
            if ( $nowTime - $userPlant['start_pay_time'] - $userPlant['delay_time'] >= $plantInfo['pay_time'] * 0.6 ) {
                $result['content'] = 'serverWord_121';
                return $result;
            }
        }

        //check plant pat time
        if ( $nowTime - $userPlant['start_pay_time'] - $plantInfo['pay_time'] - $userPlant['delay_time'] < 0 ) {
            return $result;
        }
        
        $newDeposit = $userPlant['wait_visitor_num'] * $plantInfo['ticket'];
        $coinChange = $userPlant['deposit'];
        $expChange = 3;
        
        try {
	        $userPlant['start_pay_time'] = $nowTime;
	        $userPlant['deposit'] = $newDeposit;
	        $userPlant['start_deposit'] = $newDeposit;
	        $userPlant['delay_time'] = 0;
	        $userPlant['event_manage_time'] = 0;
	        //update plant pay info
            Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($uid, $itemId, $userPlant);
            
            //add user coin and exp
            Hapyfish_Island_Cache_User::incCoinAndExp($uid ,$coinChange, $expChange);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
        } catch (Exception $e) {
            info_log('[harvestPlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
            //delete user plant mooch info
            $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
            $dalMooch->deletePlantMooch($userPlant['id']);
        } catch (Exception $e) {
            info_log('[deletePlantMooch]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }
        
        try {
	        //check user level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
	
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	            $result['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {
            info_log('[checkLevelUp]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }

        return $result;
    }

    /**
     * mooch plant
     *
     * @param integer $uid
     * @param integer $fid
     * @param integer $itemId
     * @return array
     */
    public static function moochPlant($uid, $fid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        //check is friend
        $isFriend = Bll_Friend::isFriend($uid, $fid);
        if ( !$isFriend ) {
            $result['content'] = 'serverWord_120';
            return $result;
        }

        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        //get mooch info
        $moochInfo = $dalMooch->hasMoochPlant($uid, $itemId);
        if ( $moochInfo ) {
            $result['content'] = 'serverWord_144';
            return $result;
        }
        
        //out plant people
        Hapyfish_Island_Bll_User::outPlantPeople($uid, $itemId);
        
        $userPlant = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($fid, $itemId);
        
        if ( !$userPlant || $userPlant['uid'] != $fid ) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        //check plant visitor
        if ( $userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 ) {
            $result['content'] = 'serverWord_143';
            return $result;
        }
    
        if ( $userPlant['event'] == 2 ) {
            $result['content'] = 'serverWord_121';
            return $result;
        }
    
        //check plant deposit
        if ( $userPlant['deposit'] <= 0 ) {
            return $result;
        }
        
        //get plant info by bid
        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['cid']);
        $nowTime = time();
        //check plant event info
        if ( $userPlant['event'] != 0 ) {
            if ( $nowTime - $userPlant['start_pay_time'] >= $plantInfo['pay_time'] * 0.6 ) {
                $result['content'] = 'serverWord_121';
                return $result;
            }
        }
        
        //check plant pay time
        if ( $nowTime - $userPlant['start_pay_time'] - $plantInfo['pay_time'] - $userPlant['delay_time'] < 0 ) {
            return $result;
        }
        
        try {
            //check plant deposit
            $safeCoinNum = $userPlant['start_deposit'] * $plantInfo['safe_coin_num'];
            $safeCoinNum = round($safeCoinNum);
            if ( $userPlant['deposit'] <= $safeCoinNum ) {
                $result['content'] = 'serverWord_145';
                return $result;
            }

            //$moochCoin = rand(5, 100);
            //mixi 特殊
            $moochCoin = rand(1, 3);
            $remainCoin = $userPlant['deposit'] - $moochCoin;
            $moochCoin = $remainCoin >= $safeCoinNum ? $moochCoin : $userPlant['deposit'] - $safeCoinNum;

            //
            $userPlant['deposit'] = $userPlant['deposit'] - $moochCoin;
            
            //update user plant pay info
            Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($userPlant['uid'], $itemId, $userPlant);

            $expChange = 2;
            //add user coin and exp
            Hapyfish_Island_Cache_User::incCoinAndExp($uid, $moochCoin, $expChange);
            
	        $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $moochCoin;
        }
        catch (Exception $e) {
            info_log('[moochPlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_8', 1);
            //update user achievement,num_8
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_8', 1);
            //insert plant mooch info
            $newPlantMooch = array('uid' => $uid, 'id' => $itemId);
            $dalMooch->insertPlantMooch($newPlantMooch);
        } catch (Exception $e) {
            info_log('[mongo]-[moochPlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }
        
        try {
        	//check user level up
            $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	            $result['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {
            info_log('[levelUp]-[moochPlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }
        
		try {
	        $minifeed = array('uid' => $fid,
	                          'template_id' => 12,
	                          'actor' => $uid,
	                          'target' => $fid,
	                          'title' => array('money' => $moochCoin),
	                          'type' => 2,
	                          'create_time' => $nowTime);
	        
	        Hapyfish_Island_Bll_Feed::insertIslandMinifeed($minifeed);
	        
	        Bll_Island_Message::send('moochPlant', $uid, $fid);
		} catch (Exception $e) {
            
        }
        
        return $result;
    }

    /**
     * manage plant
     *
     * @param integer $uid
     * @param integer $itemId
     * @param integer $eventType
     * @return array
     */
    public static function managePlant($uid, $itemId, $eventType, $ownerUid)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }
    
        //check is friend
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend && $uid != $ownerUid ) {
            $result['content'] = 'serverWord_120';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $userPlant = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($ownerUid, $itemId);
        
        if ( !$userPlant || $userPlant['uid'] != $ownerUid ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }

        //check plant event type
        if ( $userPlant['event'] != $eventType || $eventType < 1 ) {
            $result['content'] = 'serverWord_146';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        //get plant info by bid
        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['cid']);
        $nowTime = time();
        //check plant event info
        if ( $nowTime - $userPlant['start_pay_time'] + $userPlant['delay_time'] < $plantInfo['pay_time'] * 0.6 ) {
        	$result['content'] = 'serverWord_146';
            $result = array('resultVo' => $result);
            return $result;
        }

        $stopTime = ($nowTime - $userPlant['start_pay_time']) - $plantInfo['pay_time'] * 0.6;
        $payRemainder = $plantInfo['pay_time'] - ($nowTime - ($userPlant['start_pay_time'] + $stopTime)) + $userPlant['delay_time'];
        $payRemainder = max(0, $payRemainder);
        $expChange = 5;

        try {
	        $userPlant['event'] = 0;
	        $userPlant['start_pay_time'] = $userPlant['start_pay_time'] + $stopTime;
	        $userPlant['event_manage_time'] = $nowTime;
            Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($userPlant['uid'], $itemId, $userPlant);
        	
            //add user  exp
            Hapyfish_Island_Cache_User::incExp($uid, $expChange);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
        }
        catch (Exception $e) {
            info_log('[managePlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        try {
        	$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update today achievement
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_3', 1);
            //update user achievement,num_3
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_3', 1);
        } catch (Exception $e) {
            info_log('[mongo]-[managePlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }
        
        try {
	        //check user level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];

	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
	            $result['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch(Exception $e) {
        	info_log('[levelUp]-[managePlant]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Plant');
        }
        
        if ( $userPlant['uid'] != $uid ) {
        	try {
	            $minifeed = array('uid' => $userPlant['uid'],
	                              'template_id' => 13,
	                              'actor' => $uid,
	                              'target' => $userPlant['uid'],
	                              'title' => array('manage_num' => 1),
	                              'type' => 1,
	                              'create_time' => $nowTime);
	            Hapyfish_Island_Bll_Feed::insertPlantManageMinifeed($minifeed);
        	}catch(Exception $e) {
        		
        	}
        }
        
        $result = array('resultVo' => $result, 'payRemainder' => $payRemainder);
        return $result;
    }

    /**
     * upgrade plant
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
    public static function upgradePlant($uid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }

        $userPlant = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($uid, $itemId);
        
        if ( !$userPlant || $userPlant['uid'] != $uid ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        //get plant info by bid
        $plantInfo = Hapyfish_Island_Cache_Shop::getPlantById($userPlant['cid']);
        if ( !$plantInfo['next_level_bid'] ) {
            $result = array('resultVo' => $result);
            return $result;
        }

        //get next level plant info
        $nextLevelPlant = Hapyfish_Island_Cache_Shop::getPlantById($plantInfo['next_level_bid']);
        $praiseChange = $nextLevelPlant['add_praise'] - $plantInfo['add_praise'];
        
        if ( !$nextLevelPlant ) {
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserLevelInfo($uid);
        if ( !$islandUser ) {
            $result['content'] = 'serverWord_101';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        //check need coin
        if ( $nextLevelPlant['price_type'] == 1 ) {
            if ( $nextLevelPlant['price'] > $islandUser['coin'] ) {
                $result['content'] = 'serverWord_137';
                $result = array('resultVo' => $result);
                return $result;
            }

            $priceType = 'coin';
            $changeType = 'coinChange';
            $buyType = 'num_14';
            $priceContent = 'serverWord_137';
        }
        else {
            if ( $nextLevelPlant['price'] > $islandUser['gold'] ) {
                $result['content'] = 'serverWord_140';
                $result = array('resultVo' => $result);
                return $result;
            }

            $priceType = 'gold';
            $changeType = 'goldChange';
            $buyType = 'buy_gold';
            $priceContent = 'serverWord_140';
        }

        //check need level
        if ( $nextLevelPlant['need_level'] > $islandUser['level'] ) {
            $result['content'] = 'serverWord_136';
            $result = array('resultVo' => $result);
            return $result;
        }
        //check need praise
        if ( $nextLevelPlant['need_praise'] > $islandUser['praise'] ) {
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $now = time();

        try {
            //get user for update,check coin
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ( $forUpdateUser[$priceType] < $nextLevelPlant['price'] ) {
                $result['content'] = $priceContent;
                $result = array('resultVo' => $result);
                return $result;
            }

            Hapyfish_Island_Cache_Plant::upgradePlantById($uid, $itemId, $plantInfo['next_level_bid'], $plantInfo['level'] + 1);

            $expChange = 5;
            //update user info
            /*
            $newUser = array($priceType => $forUpdateUser[$priceType] - $nextLevelPlant['price'],
                             'exp' => $forUpdateUser['exp'] + $expChange,
                             'praise' => $forUpdateUser['praise'] + $praiseChange);
            $dalUser->updateUser($uid, $newUser);
			*/
            
            if ($priceType == 'coin') {
            	Hapyfish_Island_Cache_User::decCoin($uid, $nextLevelPlant['price']);
            	Hapyfish_Island_Cache_User::incExp($uid, $expChange);
            	Hapyfish_Island_Cache_User::updatePraise($uid, $praiseChange);
            	
            } else if ( $priceType == 'gold') {
            	Hapyfish_Island_Cache_User::decGold($uid, $nextLevelPlant['price']);
            	
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'gold' => $nextLevelPlant['price'],
                                      'remain_gold' => $forUpdateUser[$priceType] - $nextLevelPlant['price'],
                                      'item_id' => $plantInfo['next_level_bid'],
                                      'name' => $nextLevelPlant['level'] . '星' .$nextLevelPlant['name'],
                                      'count' => 1,
                                      'content' => '[upgradePlant]:newPlant='.$plantInfo['next_level_bid'],
                                      'create_time' => $now);
                $dalGold = Dal_Island_Gold::getDefaultInstance();
                $dalGold->insertUserGoldInfo($userGoldInfo);
            }
            
            Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);

            $result['status'] = 1;
            $result[$changeType] = -$nextLevelPlant['price'];
            $result['expChange'] = $expChange;
            $result['praiseChange'] = $praiseChange;

        }
        catch (Exception $e) {
            info_log('[error_message]-[upgradePlant]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user buy count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);
            //update user buy money
            $dalMongoAchievement->updateUserAchievementByField($uid, $buyType, $nextLevelPlant['price']);
        } catch(Exception $e) {
        	
        }

        //send activity
        $img = explode('.', $nextLevelPlant['class_name']);
        $img = $img[count($img) - 1];
        $img = substr($img, 0, strlen($img) - 1);
        $feed = Bll_Island_Activity::send('BUILDING_LEVEL_UP', $uid, array('building'=>$nextLevelPlant['name'], 'level'=>$nextLevelPlant['level'], 'img' => $img));
        $result['feed'] = $feed;
        
        //check user level up
        try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $result['levelUp'] = $levelUp['levelUp'];
	        $result['islandLevelUp'] = $levelUp['islandLevelUp'];
	        if ( $levelUp['levelUp'] ) {
	            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	            $result['feed'] = $feed;
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        }
        catch(Exception $e) {
        	
        }
        
        $basicInfo = Hapyfish_Island_Cache_Plant::getPlantInfoById($uid, $itemId);
        
        $buildingVo = array('id' => $userPlant['itemId'],
        					'cid' => $plantInfo['next_level_bid'],
        					'x' => $basicInfo['x'],
        					'y' => $basicInfo['y'],
        					'z' => $basicInfo['z'],
        					'mirro' => $basicInfo['mirro'],
        					'event' => $userPlant['event'],
        					'waitVisitorNum' => $userPlant['wait_visitor_num'],
        					'payRemainder' => max(0, $nextLevelPlant['pay_time'] - ($now - $userPlant['start_pay_time'])),
        					'deposit' => $userPlant['deposit'],
        					'startDeposit' => $userPlant['start_deposit'],
        					'canFind' => $userPlant['can_find'],
        					'hasSteal' => 0);
        
        $result = array('resultVo' => $result, 'buildingVo' => $buildingVo);

        return $result;
    }
}