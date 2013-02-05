<?php

class Hapyfish_Island_Bll_Task_Daily_T1000 implements Hapyfish_Island_Bll_Task_Interface
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
        $taskInfo = Hapyfish_Island_Cache_Task::getDailyTask($taskId);
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
        
        $coinChange = $taskInfo['coin'];
        $expChange = $taskInfo['exp'];
        $cardId = $taskInfo['cid'];

        try {
            if ( $coinChange > 0 ) {
            	Hapyfish_Island_Cache_User::incCoin($uid, $coinChange);
            }
            
            if ( $expChange > 0 ) {
                $array['exp'] = $expChange;
                Hapyfish_Island_Cache_User::incExp($uid, $expChange);
            }
        	
            if ( $cardId ) {
                $newCard = array('uid' => $uid,
                                 'cid' => $cardId,
                                 'count' => 1,
                                 'buy_time' => $nowTime,
                                 'item_type' => 41);
                //add user card
                $dalCard = Dal_Island_Card::getDefaultInstance();
                $dalCard->addUserCard($newCard);
            }
            
            $result['status'] = 1;
            
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['taskChange'] = true;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
        	$levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
            
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $result['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {
        }
        
        return $result;
    }
}
