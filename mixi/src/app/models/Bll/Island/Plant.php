<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/01    Liz
 */
class Bll_Island_Plant extends Bll_Abstract
{
    /**
     * gain plant
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
	public function harvestPlant($uid, $itemId)
    {
        $isAppUser = Bll_User::isAppUser($uid);
        if ( !$isAppUser ) {
            $result['content'] = 'serverWord_101';
            return $result;
        }
        
        $result = array('status' => -1);
        $nowTime = time();

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            return $result;
        }

        //out plant people
        $bllUser = new Bll_Island_User();
        $bllUser->outPlantPeople($uid, $itemId);
        
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant info by item id
        $userPlant = $dalPlant->getUserPlantById($itemId, $uid);

        if ( !$userPlant || $userPlant['uid'] != $uid ) {
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
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);
        
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

        $bllDock = new Bll_Island_Dock();
        $dalMooch = Dal_Mongo_Mooch::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserForHarvest($uid);
        
        $newDeposit = $userPlant['wait_visitor_num'] * $plantInfo['ticket'];
        $coinChange = $userPlant['deposit'];
        $expChange = 3;

        //update user plant
        $newPlant = array( 'uid' => $userPlant['uid'],
                           'start_pay_time' => $nowTime,
                           'deposit' => $newDeposit,
                           'start_deposit' => $newDeposit,
                           'delay_time' => 0,
                           'event_manage_time' => 0);
        
        //update user info
        $newUser = array('coin' => $islandUser['coin'] + $coinChange,
                         'exp' => $islandUser['exp'] + $expChange);
        
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            $dalPlant->updateUserPlant($itemId, $newPlant);

            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();
            
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);
            
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            
            //delete user plant mooch info
            $dalMooch->deletePlantMooch($userPlant['id']);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[harvestPlant]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }

        //send activity
        if ( $levelUp['levelUp'] ) {
            $feed = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
            $result['feed'] = $feed;
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
    public function moochPlant($uid, $fid, $itemId)
    {
        $result = array('status' => -1);
        $nowTime = time();

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
        $bllUser = new Bll_Island_User();
        $bllUser->outPlantPeople($uid, $itemId);
        
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant info by item id
        $userPlant = $dalPlant->getUserPlantById($itemId, $fid);
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
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);
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

        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserForHarvest($uid);
        if ( !$islandUser ) {
            $result['content'] = 'serverWord_101';
            return $result;
        }
        $bllDock = new Bll_Island_Dock();
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user plant by id for update
            $userPlantForUpdate = $dalPlant->getUserPlantByIdForupdate($itemId, $fid);
            //check plant deposit
            $safeCoinNum = $userPlantForUpdate['start_deposit'] * 0.9;
            $safeCoinNum = round($safeCoinNum);
            if ( $userPlantForUpdate['deposit'] <= $safeCoinNum ) {
                $this->_wdb->rollBack();
                $result['content'] = 'serverWord_145';
                return $result;
            }

            $moochCoin = rand(1, 3);
            $remainCoin = $userPlantForUpdate['deposit'] - $moochCoin;
            $moochCoin = $remainCoin >= $safeCoinNum ? $moochCoin : $userPlantForUpdate['deposit'] - $safeCoinNum;

            //update friend plant
            $newPlant = array('uid' => $userPlantForUpdate['uid'] , 'deposit' => $userPlantForUpdate['deposit'] - $moochCoin);
            $dalPlant->updateUserPlant($itemId, $newPlant);

            $expChange = 2;
            //update user info
            $newUser = array('coin' => $islandUser['coin'] + $moochCoin,
                             'exp' => $islandUser['exp'] + $expChange);
            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();
            
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);
            
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $fid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $fid);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today send gift count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_8', 1);
            //update user achievement,num_8
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_8', 1);

            //insert plant mooch info
            $newPlantMooch = array('uid' => $uid,
                                   'id' => $itemId);
            $dalMooch->insertPlantMooch($newPlantMooch);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $moochCoin;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[moochPlant]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
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

        $minifeed = array('uid' => $fid,
                          'template_id' => 12,
                          'actor' => $uid,
                          'target' => $fid,
                          'title' => array('money' => $moochCoin),
                          'type' => 2,
                          'create_time' => $nowTime);
        Bll_Island_Feed::insertIslandMinifeed($minifeed);
        
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
    public function managePlant($uid, $itemId, $eventType, $ownerUid)
    {
        $result = array('status' => -1);
        $nowTime = time();

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
        
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant info by item id
        $userPlant = $dalPlant->getUserPlantById($itemId, $ownerUid);
        
        if ( !$userPlant ) {
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
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);
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
    
        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserLevelInfo($uid);
        if ( !$islandUser ) {
            $result['content'] = 'serverWord_101';
            $result = array('resultVo' => $result);
            return $result;
        }
        $bllDock = new Bll_Island_Dock();
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //update user plant
            $newPlant = array('uid' => $userPlant['uid'],
                              'event' => 0,
                              'start_pay_time' => $userPlant['start_pay_time'] + $stopTime,
                              'event_manage_time' => $nowTime);
            $dalPlant->updateUserPlant($itemId, $newPlant);

            //update user info
            $newUser = array('exp' => $islandUser['exp'] + $expChange);
            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();
            
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);
            
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $ownerUid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $ownerUid);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update today achievement
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_3', 1);
            //update user achievement,num_3
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_3', 1);

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[managePlant]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('resultVo' => $result);
            return $result;
        }

        if ( $userPlant['uid'] != $uid ) {
            $minifeed = array('uid' => $userPlant['uid'],
                              'template_id' => 13,
                              'actor' => $uid,
                              'target' => $userPlant['uid'],
                              'title' => array('manage_num' => 1),
                              'type' => 1,
                              'create_time' => $nowTime);
            Bll_Island_Feed::insertPlantManageMinifeed($minifeed);
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
    public function upgradePlant($uid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }

        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant info by item id
        $userPlant = $dalPlant->getUserPlantById($itemId, $uid);
        
        if ( !$userPlant || $userPlant['uid'] != $uid ) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        //get plant info by bid
        $plantInfo = Bll_Cache_Island::getPlantById($userPlant['bid']);
        if ( !$plantInfo['next_level_bid'] ) {
            $result = array('resultVo' => $result);
            return $result;
        }

        //get next level plant info
        $nextLevelPlant = Bll_Cache_Island::getPlantById($plantInfo['next_level_bid']);
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

        $bllDock = new Bll_Island_Dock();
        //begin transaction
        $this->_wdb->beginTransaction();
        try {
            //get user for update,check coin
            $forUpdateUser = $dalUser->getUserForUpdate($uid);
            if ( $forUpdateUser[$priceType] < $nextLevelPlant['price'] ) {
                $this->_wdb->rollBack();
                $result['content'] = $priceContent;
                $result = array('resultVo' => $result);
                return $result;
            }

            //update user plant
            $newPlant = array('uid' => $userPlant['uid'] , 'bid' => $plantInfo['next_level_bid'], 'level' => $plantInfo['level'] + 1);
            $dalPlant->updateUserPlant($itemId, $newPlant);

            $expChange = 5;
            //update user info
            $newUser = array($priceType => $forUpdateUser[$priceType] - $nextLevelPlant['price'],
                             'exp' => $forUpdateUser['exp'] + $expChange,
                             'praise' => $forUpdateUser['praise'] + $praiseChange);
            $dalUser->updateUser($uid, $newUser);

            //end of transaction
            $this->_wdb->commit();
        
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);
            
            if ( $priceType == 'gold' && $nextLevelPlant['price'] > 0 ) {
                //add user gold info
                $userGoldInfo = array('uid' => $uid,
                                      'gold' => $nextLevelPlant['price'],
                                      'remain_gold' => $forUpdateUser[$priceType] - $nextLevelPlant['price'],
                                      'item_id' => $plantInfo['next_level_bid'],
                                      'name' => $nextLevelPlant['level'] . '星' .$nextLevelPlant['name'],
                                      'count' => 1,
                                      'content' => '[upgradePlant]:newPlant='.$plantInfo['next_level_bid'],
                                      'create_time' => time());
                $dalGold = Dal_Island_Gold::getDefaultInstance();
                $dalGold->insertUserGoldInfo($userGoldInfo);
            }
            
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $uid);
            Bll_Cache_Island_User::clearCache('getUserPlantList', $uid);
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user buy count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'buy_count', 1);
            //update user buy money
            $dalMongoAchievement->updateUserAchievementByField($uid, $buyType, $nextLevelPlant['price']);

            $result['status'] = 1;
            $result[$changeType] = -$nextLevelPlant['price'];
            $result['expChange'] = $expChange;
            //$result['praiseChange'] = $praiseChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[upgradePlant]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('resultVo' => $result);
            return $result;
        }

        //send activity
        $img = explode('.', $nextLevelPlant['class_name']);
        $img = $img[count($img) - 1];
        $img = substr($img, 0, strlen($img) - 1);
        $feed = Bll_Island_Activity::send('BUILDING_LEVEL_UP', $uid, array('building'=>$nextLevelPlant['name'], 'level'=>$nextLevelPlant['level'], 'img' => $img));
        $result['feed'] = $feed;
        
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

        $n = $userPlant['uid'] % 10;
        $id = $itemId . $n . $userPlant['item_type'];
        $buildingVo = array('id' => $id,
        					'cid' => $plantInfo['next_level_bid'],
        					'x' => $userPlant['x'],
        					'y' => $userPlant['y'],
        					'z' => $userPlant['z'],
        					'mirro' => $userPlant['mirro'],
        					'event' => $userPlant['event'],
        					'waitVisitorNum' => $userPlant['wait_visitor_num'],
        					'payRemainder' => max(0, $nextLevelPlant['pay_time'] - (time() - $userPlant['start_pay_time'])),
        					'deposit' => $userPlant['deposit'],
        					'startDeposit' => $userPlant['start_deposit'],
        					'canFind' => $userPlant['can_find'],
        					'hasSteal' => 0);
        $result = array('resultVo' => $result, 'buildingVo' => $buildingVo);

        return $result;
    }
}