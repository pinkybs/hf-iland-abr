<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/10    xial
 */
class Bll_Island_Task_Build_T2043 extends Bll_Abstract implements Bll_Island_Task_Interface
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
        $islandUser = $dalUser->getUser($uid);

        
        $dalTask = Dal_Island_Task::getDefaultInstance();

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

    	
        $dalPlant = Dal_Island_Plant::getDefaultInstance();
        //get user plant by id
        $userPlant = $dalPlant->getUserPlantByBid($uid, $taskInfo['need_cid']);
    	if ( !$userPlant ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }

        
        $bllDock = new Bll_Island_Dock();

        
        $dalCard = Dal_Island_Card::getDefaultInstance();

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            $coinChange = $taskInfo['coin'];
            $expChange = $taskInfo['exp'];
            $cardId = $taskInfo['cid'];
            $nowTime = time();

            if ( $coinChange ) {
                //update user coin
                $dalUser->updateUserByField($uid, 'coin', $coinChange);
            }

            if ( $expChange ) {
                //update user exp
                $dalUser->updateUserByField($uid, 'exp', $expChange);
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

            $newUserTask = array('uid' => $uid,
                                 'tid' => $taskId,
                                 'finish_time' => $nowTime,
                                 'status' => 1,
                                 'type' => 2);
            $dalMongoTask->insertUserTask($newUserTask);

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
            $result = array('result' => $result);
            return $result;
        }

        return $result;
    }

}
