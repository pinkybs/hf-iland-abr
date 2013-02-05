<?php

/**
 * logic's Operation
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/11    xial
 */
class Bll_Island_Task_Achievement_T3028 extends Bll_Abstract implements Bll_Island_Task_Interface
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

        
        $dalTask = Dal_Island_Task::getDefaultInstance();

        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
        $userTask = $dalMongoTask->getUserTask($uid, $taskId);
        if ( $userTask ) {
            $result['content'] = 'serverWord_151';
            return $result;
        }

        //get task info
        $taskInfo = Bll_Cache_Island::getAchievementTask($taskId);

        
        $dalAchievement = Dal_Island_Achievement::getDefaultInstance();
        //get user achievement info
        $userAchievement = $dalAchievement->getUserAchievement($uid);

    	if ( $userAchievement['num_6'] < 1000 ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }

        
        $bllDock = new Bll_Island_Dock();

        
        $dalCard = Dal_Island_Card::getDefaultInstance();

        //get title info by id
        $titleInfo = Bll_Cache_Island::getTitleById($taskInfo['title']);

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
            $coinChange = $taskInfo['coin'];
            $expChange = $taskInfo['exp'];
            $cardId = $taskInfo['cid'];
            $titleId = $taskInfo['title'];
            $nowTime = time();

            
        	$dalUser = Dal_Island_User::getDefaultInstance();

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

            //insert user task info
            $newUserTask = array( 'uid' => $uid,
	                              'tid' => $taskId,
	                              'finish_time' => $nowTime,
	                              'status' => 1,
	                              'type' => 3);
            $dalMongoTask->insertUserTask($newUserTask);

            //insert user title info
            $newUserTitle = array('uid' => $uid,
	                              'status' => 0,
	                              'title' => $titleId);
            $dalTask->insertUserTitle($newUserTitle);

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

            //end of transaction
            $this->_wdb->commit();

            $result['status'] = 1;
            $result['content'] = '任务完成，奖励金币 '. $coinChange . ',经验' . $expChange . ',获得称号' . $titleInfo['title'];
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
            $result['taskChange'] = true;
            $result['title'] = $titleInfo['title'];
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