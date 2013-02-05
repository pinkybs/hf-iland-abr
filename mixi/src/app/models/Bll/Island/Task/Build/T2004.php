<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/10    xial
 */
class Bll_Island_Task_Build_T2004 extends Bll_Abstract implements Bll_Island_Task_Interface
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

        $dalUser = Dal_Island_User::getDefaultInstance();
        //get user info
        $islandUser = $dalUser->getUserLevelInfo($uid);

        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
        $userTask = $dalMongoTask->getUserTask($uid, $taskId);
        if ( $userTask ) {
            $result['content'] = 'serverWord_151';
            return $result;
        }

        //get task info
        $taskInfo = Bll_Cache_Island::getBuildTask($taskId);
        if ( $islandUser['level'] < $taskInfo['need_level'] ) {
            $result['content'] = 'serverWord_153';
            return $result;
        }

        $dalDock = Dal_Island_Dock::getDefaultInstance();
        //get user plant by id
        $cnt = $dalDock->getCntShipIdById($uid, $taskInfo['need_cid']);
        if ( $cnt < 1 ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }

        $nowTime = time();
        $newUserTask = array('uid' => $uid,
                          'tid' => $taskId,
                          'finish_time' => $nowTime,
                          'status' => 1,
                          'type' => 2);
        $finishComplete = $dalMongoTask->insertUserTask($newUserTask);
    
        if ( !$finishComplete ) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_152';
            return $result;
        }
        
        $bllDock = new Bll_Island_Dock();
        $dalCard = Dal_Island_Card::getDefaultInstance();
    
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
            
        //begin transaction
        $this->_wdb->beginTransaction();

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
            $this->_wdb->commit();

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
            $result['taskChange'] = true;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        //send activity
        if ( $levelUp['levelUp'] ) {
            Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $islandUser['level'] + 1));
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
