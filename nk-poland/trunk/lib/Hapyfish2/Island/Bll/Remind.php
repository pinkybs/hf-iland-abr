<?php

class Hapyfish2_Island_Bll_Remind
{
	public static function getRemindData($uid)
	{
		$data = Hapyfish2_Island_Cache_Remind::getRemindData($uid);
		if ($data === false) {
			return array();
		}
		
		$result = array();
		foreach ($data as $feed) {
			$result[] = array(
				'actor' => $feed[0],
				'target' => $feed[1],
				'content' => $feed[2],
				'type' => $feed[3],
				'create_time' => $feed[4]
			);
		}
		
		return $result;
	}
	
	public static function flushRemindData($uid)
	{
		Hapyfish2_Island_Cache_Remind::flush($uid);
	}
	
	public static function addRemind($actor, $target, $content, $type)
	{
    	$result = array('status' => -1);
    	try {
    		$content = self::filterContent($content);
			$nowTime = time();

	    	$newRemind = array($actor, $target, $content, $type, $nowTime);
	    	
	    	Hapyfish2_Island_Cache_Remind::insertRemind($target, $newRemind);
	
			//update user feed status
	        Hapyfish2_Island_Cache_Remind::incNewRemindCount($target);
	        
	        $result['status'] = 1;
    	}catch (Exception $e) {
    		
    	}
    	
    	if ( $type == 2 ) {
	        //update achievement task,30
	        try {
	        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($actor, 'num_30', 1);
	        
				//task id 3080,task type 30
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($actor, 3080);
				if ( $checkTask['status'] == 1 ) {
					$result['finishTaskId'] = $checkTask['finishTaskId'];
				}
	        } catch (Exception $e) {
	        }
    	}
    	
        //update achievement task,26
        try {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($actor, 'num_26', 1);
			//task id 3065,task type 26
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($actor, 3065);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
        } catch (Exception $e) {
        }
    	
    	return $result;
	}
	
	public static function getRemind($uid, $pageIndex = 1, $pageSize = 50)
    {
		//get user remind list
		$remindList = self::getRemindData($uid);

		if (empty($remindList)) {
			return array();
		}

		Hapyfish2_Island_Cache_Remind::clearNewRemindCount($uid);

		return self::buildRemind($remindList);
    }
    
    protected static function buildRemind(&$remindList)
    {
		$result = array();
		foreach ($remindList as $remind) {
			$actor = Hapyfish2_Platform_Bll_User::getUser($remind['actor']);
			$result[] = array(
				'fromUid' => $remind['actor'],
				'fromUserName' => $actor['name'],
				'fromUserFace' => $actor['figureurl'],
				'sendDate' => $remind['create_time'],
				'content' => $remind['content']
			);
		}

		return $result;
    }
    
	public static function getRemindStatus($actor, $target)
	{
		$remindList = self::getRemindData($target);
		$remindTime1 = 0;
		$remindTime2 = 0;
		$remindTime3 = 0;
		$remindTime4 = 0;
		$nowTime = time();
        
		foreach ($remindList as $key => $remind) {
			if ($remind['type'] == 1 && $remindTime1 < 1) {
				$remindTime1 = $remind['create_time'];
			}
			if ($remind['type'] == 2 && $remindTime2 < 1) {
				$remindTime2 = $remind['create_time'];
			}
			if ($remind['type'] == 3 && $remind['actor'] == $actor && $remindTime3 < 1) {
				$remindTime3 = $remind['create_time'];
			}
			if ($remind['type'] == 4 && $remind['actor'] == $actor && $remindTime4 < 1) {
				$remindTime4 = $remind['create_time'];
			}
		}

		$canSend1 = 1;
		$canSend2 = 1;
		$canSend3 = 1;
		$canSend4 = 1;

		if (($nowTime - $remindTime1) <= 6*3600) {
			$canSend1 = 0;
        }
		if (($nowTime - $remindTime2) <= 6*3600) {
			$canSend2 = 0;
        }        
		if (($nowTime - $remindTime3) <= 3600) {
			$canSend3 = 0;
        }    
		if (($nowTime - $remindTime4) <= 3600) {
			$canSend4 = 0;
		}
        
		$remindStatus = array('1' => $canSend1, '2' => $canSend2, '3' => $canSend3, '4' => $canSend4);

		return $remindStatus;
	}
	
	public static function getFilterWords()
	{
		$key = 'island:filterworlds';
		$localcache = Hapyfish2_Cache_LocalCache::getInstance();
		$words = $localcache->get($key);
		if (!$words) {
			$file = CONFIG_DIR . '/filterworlds.txt';
			$data = file_get_contents($file);
			if ($data) {
				$words = explode(',', $data);
				$localcache->set($key, $words);
			} else {
				$words = array();
			}
		}
		
		return $words;
	}
	
    public static function filterContent($content)
    {
    	$filterWords = self::getFilterWords();
    	return str_ireplace($filterWords, '***', $content);
    }

}