<?php

require_once 'Bll/Abstract.php';

/**
 * @see Bll_Island_Task_Interface
 */
require_once 'Bll/Island/Task/Interface.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/09    Liz
 */
class Bll_Island_Task extends Bll_Abstract
{

    /**
     * read task
     *
     * @param integer $uid
     * @return array
     */
    public function readTask($uid)
    {
        $dalMongoTask = Dal_Mongo_Task::getDefaultInstance();
        
        //get task list
        $dailyTask = Bll_Cache_Island::getDailyTaskList();
        $buildTask = Bll_Cache_Island::getBuildTaskNeedInfoList();
        $achievementTask = Bll_Cache_Island::getAchievementTaskList();

        //get user task list
        $userTaskList = $dalMongoTask->getUserTaskList($uid);
        $userTask = array();
        for ( $m=0,$mCount=count($userTaskList); $m<$mCount; $m++ ) {
            $userTask[] = $userTaskList[$m]['tid'];
        }
        
        $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
        //get user today achievement info
        $userTodayAchievement = $dalMongoAchievement->getUserTodayAchievement($uid);
        
        //get user achievement info
        $userAchievement = $dalMongoAchievement->getUserAchievement($uid);
        //get user plant list array
        $userPlantListArray = Bll_Cache_Island_User::getUserPlantList($uid);
        $userPlantList = $userPlantListArray['userPlantList'];
        $userPlantItemIdList = $userPlantListArray['userPlantItemIdList'];
        //get user ship list
        $userShipList = Bll_Cache_Island_User::getUserShipList($uid);
        
        $userDailyTask = array();
        $userBuildTask = array();
        $userAchievementTask = array();
        
        for ( $j = 0,$jCount = count($dailyTask); $j < $jCount; $j++ ) {
            $field = 'num_' . $dailyTask[$j]['needType'];     
            $dailyTask[$j]['currentGetNum'] = isset($userTodayAchievement[$field]) ? $userTodayAchievement[$field] : 0;
            
            if ( in_array($dailyTask[$j]['taskClassId'], $userTask) ) {
                $dailyTask[$j]['state'] = 2;
            }
            else {
                if ( $dailyTask[$j]['currentGetNum'] >= $dailyTask[$j]['needNum'] ) {
                    $dailyTask[$j]['state'] = 1;
                }
                else {
                    $dailyTask[$j]['state'] = 0;
                }
            }
            $userDailyTask[] = array('taskClassId' => $dailyTask[$j]['taskClassId'],
                                     'currentGetNum' => $dailyTask[$j]['currentGetNum'],
                                     'state' => $dailyTask[$j]['state']);
        }

        $unsetAry = array();
        for ( $k = 0,$kCount = count($achievementTask); $k < $kCount; $k++ ) {
        	if ( !in_array($achievementTask[$k]['taskClassId'], $unsetAry) ) {
	            $field = 'num_' . $achievementTask[$k]['needType'];
	            
	            $achievementTask[$k]['currentGetNum'] = isset($userAchievement[$field]) ? $userAchievement[$field] : 0;
	            
	            if ( in_array($achievementTask[$k]['taskClassId'], $userTask) ) {
	                $achievementTask[$k]['state'] = 2;
	                if ( $achievementTask[$k]['level'] != 3 ) {
	                	unset($achievementTask[$k]);
	                }
	            }
	            else {
	                if ( $achievementTask[$k]['currentGetNum'] >= $achievementTask[$k]['needNum'] ) {
	                    $achievementTask[$k]['state'] = 1;
	                }
	                else {
	                    $achievementTask[$k]['state'] = 0;
	                }

	                if ( $achievementTask[$k]['level'] == 1 ) {
	                	$unsetAry[] = $achievementTask[$k]['nextTaskId'];
	                	$unsetAry[] = $achievementTask[$k]['nextTwoTaskId'];
	                }
	                else if ( $achievementTask[$k]['level'] == 2 ) {
	                	$unsetAry[] = $achievementTask[$k]['nextTaskId'];
	                }
	            }
	            
	            if ( isset($achievementTask[$k]) ) {
                $userAchievementTask[] = array('taskClassId' => $achievementTask[$k]['taskClassId'],
                                               'currentGetNum' => $achievementTask[$k]['currentGetNum'],
                                               'state' => $achievementTask[$k]['state']);
	            }
        	}
        }

        for ( $l = 0,$lCount = count($buildTask); $l < $lCount; $l++ ) {
            $buildTask[$l]['currentGetNum'] = 0;

            if ( in_array($buildTask[$l]['taskClassId'], $userTask) ) {
                $buildTask[$l]['state'] = 2;
            }
            else {
                if ( $buildTask[$l]['needType'] == 9 ) {
                    if ( $buildTask[$l]['item_id'] > 0 ) {
                        //need num == 1
                        if ( $buildTask[$l]['needNum'] == 1 ) {
                            if ( isset($userPlantList[$buildTask[$l]['item_id']]) && $userPlantList[$buildTask[$l]['item_id']] >= $buildTask[$l]['item_level'] ) {
                                $buildTask[$l]['currentGetNum'] = 1;
                            }
                        }
                        else if ( $buildTask[$l]['needNum'] > 1 ) {
                            foreach ($userPlantItemIdList as $plant) {
                                if ( $plant['item_id'] == $buildTask[$l]['item_id'] && $plant['level'] >= $buildTask[$l]['item_level'] ) {
                                    $buildTask[$l]['currentGetNum'] += 1;
                                }
                            }
                        }
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                    
                }
                else if ( $buildTask[$l]['needType'] == 11 ) {
                    //get user ship count by ship id
                    if ( isset($userShipList[$buildTask[$l]['needCid']]) ) {
                        $buildTask[$l]['currentGetNum'] = $userShipList[$buildTask[$l]['needCid']];
                    }
                    
                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
                else if ( $buildTask[$l]['needType'] == 12 ) {
                    $field = 'num_' . $buildTask[$l]['needType'];
                    if ( isset($userAchievement[$field]) ) {
                        $buildTask[$l]['currentGetNum'] = $userAchievement[$field] + 3;
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
                else {
                    $field = 'num_' . $buildTask[$l]['needType'];
                    if ( isset($userAchievement[$field]) ) {
                        $buildTask[$l]['currentGetNum'] = $userAchievement[$field];
                    }

                    if ( $buildTask[$l]['currentGetNum'] >= $buildTask[$l]['needNum'] ) {
                        $buildTask[$l]['state'] = 1;
                    }
                    else {
                        $buildTask[$l]['state'] = 0;
                    }
                }
            }
            unset($buildTask[$l]['item_id']);
            unset($buildTask[$l]['item_level']);
            
            $userBuildTask[] = array('taskClassId' => $buildTask[$l]['taskClassId'],
                                     'currentGetNum' => $buildTask[$l]['currentGetNum'],
                                     'state' => $buildTask[$l]['state']);
        }
        $taskList = array_merge($userDailyTask, $userBuildTask, $userAchievementTask);

        return array('tasks' => $taskList);
    }

    /**
     * check user title list
     *
     * @param integer $uid
     * @return array
     */
    public function readTitle($uid, $ownerUid)
    {
        //get island user
        //$dalUser = Dal_Island_User::getDefaultInstance();
        //$islandUser = $dalUser->getUserLevelInfo($ownerUid);
        
        $ownerUserTitle = Bll_Cache_Island::getUserTitle($ownerUid);
        
        if ( $ownerUid != $uid ) {
            $result = array('currentTitle' => $ownerUserTitle);
        }
        else {
            $dalMongoTitle = Dal_Mongo_Title::getDefaultInstance();
            //get user title list
            $titleList = $dalMongoTitle->getUserTitleList($ownerUid);
            
            $result = array('userTitles' => $titleList, 'currentTitle' => $ownerUserTitle);
        }
        return $result;
    }

    /**
     * change user title
     *
     * @param integer $uid
     * @param integer $titleId
     * @return array
     */
    public function changeTitle($uid, $titleId)
    {
    	$result = array('status' => -1);

        $dalTitle = Dal_Mongo_Title::getDefaultInstance();
        $dalUser = Dal_Island_User::getDefaultInstance();
        
        //get user title info by title id
        $userTitle = $dalTitle->getUserTitleByTitle($uid, $titleId);
        if ( !$userTitle ) {
        	$result['content'] = 'serverWord_149';
        	return $result;
        }
        //get island user
        $islandUser = $dalUser->getUserLevelInfo($uid);
        
    	if ( $islandUser['title'] == $titleId ) {
        	$result['content'] = 'serverWord_149';
        	return $result;
        }

        //begin transaction
        $this->_wdb->beginTransaction();

        try {
        	//update user title
        	$dalUser->updateUser($uid, array('title'=>$titleId));

            //end of transaction
            $this->_wdb->commit();
            
            Bll_Cache_Island::cleanIslandShop('getUserTitle', $uid);

            $result['status'] = 1;
        }
        catch (Exception $e) {
            $this->_wdb->rollBack();
            info_log('[error_message]-[changeTitle]:'.$e->getMessage(), 'transaction');
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            $result = array('result' => $result);
            return $result;
        }
        return $result;
    }

    /**
     * check user task info
     *
     * @param integer $uid
     * @param integer $taskId
     * @return array
     */
    public function finishTask($uid, $taskId)
    {
        //check task
        $result = $this->checkTask($uid, $taskId);
        return $result;
    }

    /**
     * check user task info
     *
     * @param integer $uid
     * @param integer $taskId
     * @return array
     */
    public function checkTask($uid, $taskId)
    {
        $taskType = substr($taskId, 0, 1);

        //task type, 1->daily,2->build,3->achievement
        if ( $taskType == 1 ) {
            $typeName = 'Daily';
            $name = 'T1000';
        }
        else if ( $taskType == 2 ) {
            $typeName = 'Build';
            
            //get task info
            $taskInfo = Bll_Cache_Island::getBuildTask($taskId);
            if ( $taskInfo['need_field'] == 9 ) {
                $name = 'T2000';
            }
            else {
                $name = 'T' . $taskId;
            }
        }
        else if ( $taskType == 3 ) {
            $typeName = 'Achievement';
            $name = 'T3000';
        }

        $implFile = 'Bll/Island/Task/' . $typeName . '/' . $name . '.php';
        if (is_file(MODELS_DIR . '/' . $implFile)) {
            require_once $implFile;
            $implClassName = 'Bll_Island_Task_' . $typeName . '_' . $name;
            $impl = new $implClassName();

            $result = $impl->check($uid, $taskId);

            if ( $result['status'] == 1 ) {
                $result['feed'] = Bll_Island_Activity::send('MISSION_COMPLETE', $uid);

                $taskInfo = array();
                if ( $taskType == 1 ) {
                    return $result;
                }
                else if ( $taskType == 2 ) {
	                //get task info
	                $taskInfo = Bll_Cache_Island::getBuildTask($taskId);
                } else if ( $taskType == 3 ) {
					$taskInfo = Bll_Cache_Island::getAchievementTask($taskId);
                }

                $coinChange = $taskInfo['coin'];
	            $expChange = $taskInfo['exp'];
	            $cardId = $taskInfo['cid'];
				$cardName = '';

	            if ($cardId) {
	            	$cardInfo = Bll_Cache_Island::getCardById($cardId);
					$cardName = $cardInfo['name'];
	            }

            	$sendStr = '';
                if ( $coinChange > 0 ) {
                    $sendStr = '<font color="#FF0000">' . $coinChange . 'コイン</font> ';
                }

                if ( $expChange > 0 ) {
                    $sendStr .= '<font color="#FF0000">' . $expChange . '経験</font> ';
                }

                if ($cardName) {
                    $sendStr .= '<font color="#9F01A0">' . $cardName . '</font>';
                }

                if ( $taskType == 2 ) {
                    $minifeed = array('uid' => $uid,
                                      'template_id' => 10,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr),
                                      'type' => 5,
                                      'create_time' => time());
                    Bll_Island_Feed::insertMiniFeed($minifeed);
                }
                else if ( $taskType == 3 ) {
                    //send activity
                    $result['feed'] = Bll_Island_Activity::send('MISSION_COMPLETE', $uid, array('title' => $result['title']));
                    unset($result['title']);

                    //insert feed
                    $title = '';
                    $titleId = $taskInfo['title'];
                    if ($titleId) {
                    	$titleInfo = Bll_Cache_Island::getTitleById($titleId);
                    	
                    	$title = $titleInfo['title'];
                    }

                    $daySend = '';
	                if ( $coinChange > 0 ) {
	                    $daySend = '<font color="#FF0000">' . $coinChange . 'コイン</font> ';
	                }

	                if ( $expChange > 0 ) {
	                    $daySend .= '<font color="#FF0000">' . $expChange . '経験</font> ';
	                }

                    $minifeed = array('uid' => $uid,
                                      'template_id' => 11,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr, 'title' => $title, 'daySend' => $daySend),
                                      'type' => 5,
                                      'create_time' => time());
                    Bll_Island_Feed::insertMiniFeed($minifeed);
                }
            }

            return $result;
        }

        return array('status' => -1, 'content' => 'serverWord_110');
    }
}