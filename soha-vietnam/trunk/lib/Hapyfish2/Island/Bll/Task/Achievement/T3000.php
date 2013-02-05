<?php

class Hapyfish2_Island_Bll_Task_Achievement_T3000 implements Hapyfish2_Island_Bll_Task_Interface
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
        $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskId);
        if (!$taskInfo) {
            return $result;
        }
        
        $isCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskId);
        if ($isCompleted) {
            $result['content'] = 'serverWord_151';
            return $result;
        }

		//get user achievement info
        $userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);

        $fieldName = 'num_' . $taskInfo['need_field'];
    	if ($userAchievement[$fieldName] < $taskInfo['need_num'] ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }
    
        $nowTime = time();
        $finishComplete = Hapyfish2_Island_Cache_Task::completeTask($uid, $taskId, $nowTime);

        if (!$finishComplete) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_152';
            return $result;
        }
        
		$coinChange = $taskInfo['coin'];
		$expChange = $taskInfo['exp'];
		$cardId = $taskInfo['cid'];
		$titleId = $taskInfo['title'];
        
        Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId);
        
        try {
        	$titleName = Hapyfish2_Island_Cache_BasicInfo::getTitleName($titleId);
        	
            if ($coinChange > 0) {
				Hapyfish2_Island_HFC_User::incUserCoin($uid, $coinChange);
            }
            if ($expChange > 0) {
				Hapyfish2_Island_HFC_User::incUserExp($uid, $expChange);
            }

            if ($cardId) {
                Hapyfish2_Island_HFC_Card::addUserCard($uid, $cardId, 1);
            }

            $result['status'] = 1;
            $result['expChange'] = $expChange;
            $result['coinChange'] = $coinChange;
            $result['taskChange'] = true;
            $result['title'] = $titleName;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        //update achievement task,22
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_23', 1);
        } catch (Exception $e) {
        }
        
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}
        
        return $result;
    }
}
