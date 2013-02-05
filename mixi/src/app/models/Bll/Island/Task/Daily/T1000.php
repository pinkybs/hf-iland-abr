<?php

/**
 * logic's Operation
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/10    xial
 */
class Bll_Island_Task_Daily_T1000 implements Bll_Island_Task_Interface
{
    /**
     * check user task
     *
     * @param int $uid
     * @param int $taskId
     */
    public function check($uid, $taskId)
    {
        $result = array('status' => -1);
    
        //get task info
        $taskInfo = Bll_Cache_Island::getDailyTask($taskId);
        if ( !$taskInfo ) {
            return $result;
        }
        
        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
        $userTask = $dalMongoTask->getUserTask($uid, $taskId);
        if ( $userTask ) {
            $result['content'] = 'serverWord_151';
            return $result;
        }
        
        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        //get user achievement info
        $userTodayAchievement = $dalMongoAchievement->getUserTodayAchievement($uid);
    
        $fieldName = 'num_' . $taskInfo['need_field'];
        if ( $userTodayAchievement[$fieldName] < $taskInfo['need_num'] ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }
        
        $nowTime = time();
        //insert user task info
        $newUserTask = array('uid' => $uid,
                             'tid' => $taskId,
                             'finish_time' => $nowTime,
                             'status' => 1,
                             'type' => 1);
        $finishComplete = $dalMongoTask->insertUserTask($newUserTask);
    
        if ( !$finishComplete ) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_152';
            return $result;
        }
        
        $bllDock = new Bll_Island_Dock();
        $dalCard = Dal_Island_Card::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        
        $coinChange = $taskInfo['coin'];
        $expChange = $taskInfo['exp'];
        $cardId = $taskInfo['cid'];
        
        $array = array();
        if ( $coinChange > 0 ) {
            $array['coin'] = $coinChange;
        }
        if ( $expChange > 0 ) {
            $array['exp'] = $expChange;
        }
        
        $db = $dalUser->getWriter();
        //begin transaction
        $db->beginTransaction();

        try {
            if ( !empty($array) ) {
                //update user exp
                $dalUser->updateUserByMultipleField($uid, $array);
            }

            if ( $cardId ) {
                $newCard = array('uid' => $uid,
                                 'cid' => $cardId,
                                 'count' => 1,
                                 'buy_time' => $nowTime,
                                 'item_type' => 41);
                //add user card
                $dalCard->addUserCard($newCard);
            }

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

            //end of transaction
            $db->commit();
            
            $result['status'] = 1;
            
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
            $result['taskChange'] = true;
        }
        catch (Exception $e) {
            $db->rollBack();
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
    
        $islandUser = $dalUser->getUserLevelInfo($uid);
        //send activity
        if ( $levelUp['levelUp'] ) {
            $result['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
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
}
