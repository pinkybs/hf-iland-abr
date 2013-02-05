<?php
require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Bll_Task
{
	public static function openTask($uid, $openTask)
	{
		$result = array('status' => -1);
		if ( !array($openTask) || empty($openTask) ) {
			return $result;
		}
		Hapyfish2_Island_Cache_Task::updateUserOpenTask($uid, $openTask);
		
		$result['status'] = 1;
		return $result;
	}
	
	/**
     * read task
     * 
     * @param integer $uid
     * @return array
     */
    public static function readTask($uid)
    {
        $dailyTask = Hapyfish2_Island_Cache_BasicInfo::getDailyTaskList();
    	$buildTask = Hapyfish2_Island_Cache_BasicInfo::getBuildTaskList();
    	$achievementTask = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskList();
   	
    	$userOpenTaskList = Hapyfish2_Island_Cache_Task::getUserOpenTask($uid);
    	$userTaskIds = Hapyfish2_Island_Cache_Task::getIds($uid);
    	$noTask = empty($userTaskIds);
    	$userDailyTaskIds = Hapyfish2_Island_Cache_TaskDaily::getIds($uid);
    	$noDailyTask = empty($userDailyTaskIds);
    	
    	$userDailyAchievement = Hapyfish2_Island_HFC_AchievementDaily::getUserAchievementDaily($uid);
    	$userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
  	
    	$userDailyTask = array();
    	foreach ($dailyTask as $id => $task) {
    		$field = 'num_' . $task['need_field']; 
    		$currentGetNum = isset($userDailyAchievement[$field]) ? $userDailyAchievement[$field] : 0;
    		if ($noDailyTask) {
    			if ($currentGetNum >= $task['need_num']) {
					$state = 1;
				}
				else {
					$state = 0;
				}
    		} else {
				if (in_array($id, $userDailyTaskIds)) {
					$state = 2;
				} else {
					if ($currentGetNum >= $task['need_num']) {
						$state = 1;
					}
					else {
						$state = 0;
					}
				}
    		}
    	
    		//check is open task 
            if ( in_array($id, $userOpenTaskList)) {
            	$lockOpen = 1;
            }
            else {
            	$lockOpen = 0;
            }
            $userDailyTask[] = array(
				'taskClassId' => $id,
				'currentGetNum' => $currentGetNum,
				'state' => $state,
				'lockOpen' => $lockOpen
			);
    	}
    	
    	//$unsetAry = array();
    	$userAchievementTask = array();
    	foreach ($achievementTask as $id => $task) {
    		if(in_array($id, array(3068, 3069, 3070, 3083, 3084, 3085, 3086, 3087))) {
				continue;
			}
			//if (!in_array($id, $unsetAry)) {
				$field = 'num_' . $task['need_field'];
				$currentGetNum = isset($userAchievement[$field]) ? $userAchievement[$field] : 0;
				if (!$noTask && in_array($id, $userTaskIds)) {
					/*if ($task['level'] != 3) {
						continue;
	                }*/
					$state = 2;
	            } else {
	            	if ($currentGetNum >= $task['need_num']) {
	            		$state = 1;
	            	} else {
	            		$state = 0;
	            	}
	            	
	            	/*if ($task['level'] == 1) {
	            		$unsetAry[] = $task['next_task'];
	            		$unsetAry[] = $task['next_two_task'];
	            	} else if($task['level'] == 2) {
	            		$unsetAry[] = $task['next_task'];
	            	}*/
	            }
			
	    		//check is open task 
	            if ( in_array($id, $userOpenTaskList)) {
	            	$lockOpen = 1;
	            }
	            else {
	            	$lockOpen = 0;
	            }
				$userAchievementTask[] = array(
					'taskClassId' => $id,
					'currentGetNum' => $currentGetNum,
					'state' => $state,
					'lockOpen' => $lockOpen
				);
			//}
    	}
    	
    	$userBuildTask = array();
    	$userUnlockShipCount = Hapyfish2_Island_Cache_Dock::getUnlockShipCount($uid);
    	$userPlantListByItemKind = Hapyfish2_Island_Cache_Plant::getAllByItemKind($uid);
    	foreach ($buildTask as $id => $task) {
    		$currentGetNum = 0;
			if (!$noTask && in_array($id, $userTaskIds)) {
                $state = 2;
            } else {
				if ($task['need_field'] == 9) {
					if ($task['item_id'] > 0 ) {
						//need num == 1
						if ($task['need_num'] == 1) {
							foreach ($userPlantListByItemKind as $item) {
								if ($item['item_id'] == $task['item_id'] && $item['level'] >= $task['item_level']) {
									$currentGetNum = 1;
									break;
								}
							}
						}
						else if ($task['need_num'] > 1) {
							foreach ($userPlantListByItemKind as $item) {
								if ($item['item_id'] == $task['item_id'] && $item['level'] >= $task['item_level'] ) {
									$currentGetNum += 1;
								}
							}
						}
					}

					if ($currentGetNum >= $task['need_num']) {
						$state = 1;
                    } else {
						$state = 0;
					}
				} else if ($task['need_field'] == 11 ) {
					//get user ship count by ship id
					if (isset($userUnlockShipCount[$task['need_cid']])) {
						$currentGetNum = $userUnlockShipCount[$task['need_cid']];
					}

					if ($currentGetNum >= $task['need_num'] ) {
						$state = 1;
					} else {
						$state = 0;
					}
				} else if ($task['need_field'] == 12) {
					$field = 'num_' . $task['need_field'];
					if (isset($userAchievement[$field])) {
						$currentGetNum = $userAchievement[$field] + 3;
					}

					if ($currentGetNum >= $task['need_num']) {
						$state = 1;
					} else {
						$state = 0;
					}
				} else {
					$field = 'num_' . $task['need_field'];
					if (isset($userAchievement[$field])) {
						$currentGetNum = $userAchievement[$field];
					}

					if ($currentGetNum >= $task['need_num']) {
						$state = 1;
					} else {
						$state = 0;
					}
				}
            }
    	
    		//check is open task 
            if ( in_array($id, $userOpenTaskList)) {
            	$lockOpen = 1;
            }
            else {
            	$lockOpen = 0;
            }
			$userBuildTask[] = array(
				'taskClassId' => $id,
				'currentGetNum' => $currentGetNum,
				'state' => $state,
				'lockOpen' => $lockOpen
			);
		}

        $taskList = array_merge($userDailyTask, $userBuildTask, $userAchievementTask);

        return array('tasks' => $taskList);
    }
    
    /**
     * check user task info
     *
     * @param integer $uid
     * @param integer $taskId
     * @return array
     */
    public static function finishTask($uid, $taskId)
    {
        $taskType = substr($taskId, 0, 1);

        //task type, 
        //1->daily,2->build,3->achievement
        if ($taskType == 1) {
            $typeName = 'Daily';
            $name = 'T1000';
        } else if ($taskType == 2) {
            $typeName = 'Build';

            //get task info
            $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildTaskInfo($taskId);
            if ($taskInfo['need_field'] == 9) {
                $name = 'T2000';
            } else {
                $name = 'T' . $taskId;
            }
        } else if ($taskType == 3) {
            $typeName = 'Achievement';
            $name = 'T3000';
        }
        require_once(CONFIG_DIR . '/language.php');
        $implFile = 'Hapyfish2/Island/Bll/Task/' . $typeName . '/' . $name . '.php';
        if (is_file(LIB_DIR . '/' . $implFile)) {
            require_once $implFile;
            $implClassName = 'Hapyfish2_Island_Bll_Task_' . $typeName . '_' . $name;
            $impl = new $implClassName();

            $result = $impl->check($uid, $taskId);

            if ($result['status'] == 1) {
                //update achievement task,20
                if ( $taskType < 3 ) {
			        try {
			        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_20', 1);
			        
						//task id 3044,task type 20
						$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3044);
						if ( $checkTask['status'] == 1 ) {
							$result['finishTaskId'] = $checkTask['finishTaskId'];
						}
			        } catch (Exception $e) {
			        }
                }
                
                $taskInfo = array();
                if ($taskType == 1) {
                    return $result;
                } else if ($taskType == 2) {
	                //get task info
	                $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildTaskInfo($taskId);
                } else if ($taskType == 3) {
					$taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskId);
                }

                $coinChange = $taskInfo['coin'];
	            $expChange = $taskInfo['exp'];
	            $cardId = $taskInfo['cid'];
				$cardName = '';

	            if ($cardId) {
	            	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cardId);
					$cardName = $cardInfo['name'];
	            }

            	$sendStr = '';
                if ($coinChange > 0) {
                	$sendStr = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01. '</font> ';
                    //$sendStr = '<font color="#FF0000">' . $coinChange . '金币</font> ';
                }

                if ($expChange > 0) {
                	$sendStr .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_04. '</font> ';
                    //$sendStr .= '<font color="#FF0000">' . $expChange . '经验</font> ';
                }

                if ($cardName) {
                    $sendStr .= '<font color="#9F01A0">' . $cardName . '</font>';
                }

                if ($taskType == 2) {
                    $minifeed = array('uid' => $uid,
                                      'template_id' => 10,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr),
                                      'type' => 5,
                                      'create_time' => time());
                    
                    Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
                } else if ($taskType == 3) {
                    unset($result['title']);

                    //insert feed
                    $title = '';
                    $titleId = $taskInfo['title'];
                    if ($titleId) {
                    	$title = Hapyfish2_Island_Cache_BasicInfo::getTitleName($titleId);
                    }

                    $daySend = '';
	                if ( $coinChange > 0 ) {
	                	$daySend = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01.'</font> ';
	                    //$daySend = '<font color="#FF0000">' . $coinChange . '金币</font> ';
	                }

	                if ( $expChange > 0 ) {
	                	$daySend .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_04.'</font> ';
	                    //$daySend .= '<font color="#FF0000">' . $expChange . '经验</font> ';
	                }

                    $minifeed = array('uid' => $uid,
                                      'template_id' => 11,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr, 'title' => $title, 'daySend' => $daySend),
                                      'type' => 5,
                                      'create_time' => time());
                    
                    Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
                }
            }

            return $result;
        }

        return array('status' => -1, 'content' => 'serverWord_110');
    }
    
    /**
     * check user task
     *
     * @param int $uid
     * @param int $taskId
     */
    public static function checkTask($uid, $taskId)
    {
        $result = array('status' => -1);
        
        //get task info
        $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskId);
        if (!$taskInfo) {
            return $result;
        }
        
		//get user achievement info
        $userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
        
        $fieldName = 'num_' . $taskInfo['need_field'];
    	if ($userAchievement[$fieldName] < $taskInfo['need_num'] ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }
        else {
	        $isCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskId);
	        if ( $isCompleted ) {
	        	$nextTaskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskInfo['next_task']);
	        	if ( $userAchievement[$fieldName] < $nextTaskInfo['need_num'] ) {
		            $result['content'] = 'serverWord_150';
		            return $result;
	        	}
	        	else {
	        		    $isCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskId);
	        		$isNextCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskInfo['next_task']);
	        		if ( $isNextCompleted ) {
	        			$nextTwoTaskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskInfo['next_two_task']);
	        			
	        			if ( $userAchievement[$fieldName] < $nextTwoTaskInfo['need_num'] ) {
				            $result['content'] = 'serverWord_150';
				            return $result;
	        			}
	        			else {
	        				$isNextTwoCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskInfo['next_two_task']);
		        			
	        				if ( $isNextTwoCompleted ) {
					            $result['content'] = 'serverWord_151';
					            return $result;
		        			}
	        				$finishTaskId = $taskInfo['next_two_task'];
	        				$finishTaskInfo = $nextTwoTaskInfo;
	        			}
	        		}
	        		else {
	        			$finishTaskId = $taskInfo['next_task'];
        				$finishTaskInfo = $nextTaskInfo;
	        		}
	        	}
	        }
	        else {
	        	$finishTaskId = $taskId;
        		$finishTaskInfo = $taskInfo;
	        }
        }
    
        $nowTime = time();
        $finishComplete = Hapyfish2_Island_Cache_Task::completeTask($uid, $finishTaskId, $nowTime);

        if (!$finishComplete) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_152';
            return $result;
        }
        
		$coinChange = $finishTaskInfo['coin'];
		$expChange = $finishTaskInfo['exp'];
		$cardId = $finishTaskInfo['cid'];
		$titleId = $finishTaskInfo['title'];
        
        Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
        
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
            $result['finishTaskId'] = $finishTaskId;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        //update achievement task,23
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_23', 1);
        
			//task id 3053,task type 23
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3053);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
        } catch (Exception $e) {
        }
        
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
		} catch (Exception $e) {
		}
				$cardName = '';
	            if ($cardId) {
	            	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cardId);
					$cardName = $cardInfo['name'];
	            }
            	$sendStr = '';
                if ($coinChange > 0) {
                    $sendStr = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01 . '</font> ';
                }
                if ($expChange > 0) {
                    $sendStr .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_18 . '</font> ';
                }
                if ($cardName) {
                    $sendStr .= '<font color="#9F01A0">' . $cardName . '</font>';
                }

                //insert feed
                $daySend = '';
                if ( $coinChange > 0 ) {
                    $daySend = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01 . '</font> ';
                }
                if ( $expChange > 0 ) {
                    $daySend .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_18 . '</font> ';
                }

                $minifeed = array('uid' => $uid,
                                      'template_id' => 11,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr, 'title' => $titleName, 'daySend' => $daySend),
                                      'type' => 5,
                                      'create_time' => time());
                Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
                    
        return $result;
    }
    
    /**
     * refresh user task
     *
     * @param int $uid
     * @param int $taskId
     */
    public static function refreshTask($uid, $taskId)
    {
    	$result = array('status' => -1);
        
        //get task info
        $taskInfo = Hapyfish2_Island_Cache_BasicInfo::getAchievementTaskInfo($taskId);
        if (!$taskInfo) {
            return $result;
        }
        
		//get user achievement info
        $userAchievement = Hapyfish2_Island_HFC_Achievement::getUserAchievement($uid);
        
        $fieldName = 'num_' . $taskInfo['need_field'];
    	if ($userAchievement[$fieldName] < $taskInfo['need_num'] ) {
            $result['content'] = 'serverWord_150';
            return $result;
        }
        else {
	        $isCompleted = Hapyfish2_Island_Cache_Task::isCompletedTask($uid, $taskId);
	        if ( $isCompleted ) {
	            $result['content'] = 'serverWord_151';
	            return $result;
	        }
	        else {
	        	$finishTaskId = $taskId;
        		$finishTaskInfo = $taskInfo;
	        }
        }

        $nowTime = time();
        $finishComplete = Hapyfish2_Island_Cache_Task::completeTask($uid, $finishTaskId, $nowTime);

        if (!$finishComplete) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_152';
            return $result;
        }
        
		$coinChange = $finishTaskInfo['coin'];
		$expChange = $finishTaskInfo['exp'];
		$cardId = $finishTaskInfo['cid'];
		$titleId = $finishTaskInfo['title'];
        
        Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
        
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
            $result['finishTaskId'] = $finishTaskId;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        //update achievement task,23
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_23', 1);
        
			//task id 3053,task type 23
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3053);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
        } catch (Exception $e) {
        }
        
    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
		} catch (Exception $e) {
		}
				$cardName = '';
	            if ($cardId) {
	            	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cardId);
					$cardName = $cardInfo['name'];
	            }
            	$sendStr = '';
                if ($coinChange > 0) {
                    $sendStr = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01 . '</font> ';
                }
                if ($expChange > 0) {
                    $sendStr .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_18 . '</font> ';
                }
                if ($cardName) {
                    $sendStr .= '<font color="#9F01A0">' . $cardName . '</font>';
                }

                //insert feed
                $daySend = '';
                if ( $coinChange > 0 ) {
                    $daySend = '<font color="#FF0000">' . $coinChange . LANG_PLATFORM_BASE_TXT_01 . '</font> ';
                }
                if ( $expChange > 0 ) {
                    $daySend .= '<font color="#FF0000">' . $expChange . LANG_PLATFORM_BASE_TXT_18 . '</font> ';
                }

                $minifeed = array('uid' => $uid,
                                      'template_id' => 11,
                                      'actor' => $uid,
                                      'target' => $uid,
                                      'title' => array('sendStr' => $sendStr, 'title' => $titleName, 'daySend' => $daySend),
                                      'type' => 5,
                                      'create_time' => time());
                Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
                    
        return $result;
    }
}