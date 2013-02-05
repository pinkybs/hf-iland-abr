<?php

class Hapyfish2_Island_Bll_Plant
{
	public static function getAllOnIsland($ownerUid, $uid, $islandId, $highcache = false)
    {
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($ownerUid, $islandId, $highcache);
		$plants = array();
		$visitorNum  = 0;
		
		if ($data) {
			$now = time();
			$home = ($ownerUid == $uid);
			if (!$home) {
				$moochList = Hapyfish2_Island_Cache_Mooch::getMoochPlantList($ownerUid, $data['ids']);
				$nosteal = empty($moochList);
			}
			Hapyfish2_Island_Bll_PlantStatus::outIslandPeople($ownerUid, $data['plants'], $islandId);
			
			foreach ($data['plants'] as $item) {
				if (!$home) {
					if ($nosteal) {
						$hasSteal = 0;
					} else {
						if (empty($moochList[$item['id']])) {
							$hasSteal = 0;
						} else {
							$hasSteal = in_array($uid, $moochList[$item['id']]);
						}
					}
				} else {
					$hasSteal = 0;
				}
				$plant = self::handlerPlant($item, $now, $home, $hasSteal);
				$visitorNum += $plant['waitVisitorNum'];
				$plants[] = $plant;
			}
		}
		
		return array('plants' => $plants, 'visitorNum' => $visitorNum);
    }
    
    public static function getAllOnIslandNoMooch($uid, $userCurrentIsland, $savehighcache = true, $checkout = false)
    {
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, $userCurrentIsland, $savehighcache);
		$plants = array();
		$visitorNum  = 0;
		
		if ($data) {
			$now = time();
			foreach ($data['plants'] as $item) {
				$plant = self::handlerPlant($item, $now);
				$visitorNum += $plant['waitVisitorNum'];
				$plants[] = $plant;
			}
		}
		
		return array('plants' => $plants, 'visitorNum' => $visitorNum);
    }
    
    public static function handlerPlant(&$item, $now, $home = true, $hasSteal = 0)
    {
    	$plant = array(
    		'id' => $item['id'] . $item['item_type'],
    		'cid' => $item['cid'],
			'x' => $item['x'],
			'y' => $item['y'],
			'z' => $item['z'],
			'mirro' => $item['mirro'],
    		'canFind' => $item['can_find'],
    		'event' => $item['event'],
    		'hasSteal' => $hasSteal,
    		'waitVisitorNum' => $item['wait_visitor_num'],
    		'startDeposit' => $item['start_deposit'],
    		'deposit' => $item['deposit']
    	);
    	
        if ($item['wait_visitor_num'] < 1 && $item['start_deposit'] < 1) {
    		$plant['payRemainder'] = 0;
    	} else {
    		if ($item['event'] > 0) {
    			if ($now - $item['start_pay_time'] - $item['delay_time'] >= $item['pay_time'] * 0.6) {
    				$plant['payRemainder'] = $item['pay_time'] * 0.4;
    			} else {
    				$plant['payRemainder'] = $item['pay_time'] - ($now - $item['start_pay_time'] - $item['delay_time']);
    			}
    		} else {
				$payRemainder = $item['pay_time'] - ($now - $item['start_pay_time'] - $item['delay_time']);
				$plant['payRemainder'] = $payRemainder < 0 ? 0 : $payRemainder;
    		}
    	}
    	
    	return $plant;
    }
    
    /**
     * gain plant
     *
     * @param integer $uid
     * @param integer $itemId
     * @return array
     */
	public static function harvestPlant($uid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);

        if ($itemType != 3) {
            $result['content'] = 'serverWord_115';
            return $result;
        }
        
        
        //get user vo ,current island id
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userCurrentIsland = $userVO['current_island'];
        $userPlant = Hapyfish2_Island_HFC_Plant::getOne($uid, $itemId, 1, $userCurrentIsland);
        if (!$userPlant) {
            $result['content'] = 'serverWord_115';
            return $result;
        }
        
        if ( $userCurrentIsland != $userPlant['status'] ) {
            $result['content'] = 'serverWord_115';
            return $result;
        }
        
        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo) {
        	return $result;
        }
        
        $now = time();
        Hapyfish2_Island_Bll_PlantStatus::outPlantPeople($uid, $userPlant, $now);
        
        if ($userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 || $userPlant['deposit'] <= 0) {
            return $result;
        }
        
		if ($userPlant['event'] == 2) {
            $result['content'] = 'serverWord_121';
            return $result;
        }
        
        if ($userPlant['event'] == 1) {
            if ($now - $userPlant['start_pay_time'] - $userPlant['delay_time'] >= $userPlant['pay_time'] * 0.6) {
                $result['content'] = 'serverWord_121';
                return $result;
            }
        }
        
		if ($now - $userPlant['start_pay_time'] - $userPlant['pay_time'] - $userPlant['delay_time'] < 0) {
            return $result;
        }
        
        $coinChange = $userPlant['deposit'];
        $addExp = 3;
    
        //check user god card
		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$userMammonTime = $userCardStatus['mammon'];
		$userPoorTime = $userCardStatus['poor'];
		if ( $userMammonTime <= $now && $userPoorTime > $now ) {
			$coinChange = round($coinChange * 0.9);
		}
		if ( $userMammonTime > $now && $userPoorTime <= $now ) {
			$coinChange = round($coinChange * 1.1);
		}
		
        try {
	        $userPlant['start_pay_time'] = $now;
	        $userPlant['event'] = 0;
	        $userPlant['delay_time'] = 0;
	        $userPlant['deposit'] = 0;
	        $userPlant['start_deposit'] = 0;
	        
	        Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $userPlant);
            
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $addExp, $coinChange);

            $result['status'] = 1;
            $result['expChange'] = $addExp;
            $result['coinChange'] = $coinChange;
        } catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
            //delete user plant mooch info
            Hapyfish2_Island_Cache_Mooch::clearMoochPlant($uid, $itemId);
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
		
		try {
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_31', 1);
			
			//task id 3038,task type 31
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3038);
			if ( $checkTask['status'] == 1 ) {
				$result['finishTaskId'] = $checkTask['finishTaskId'];
			}
		} catch (Exception $e) {
		}
		
        return $result;
    }
    
    /**
     * mooch plant
     *
     * @param integer $uid
     * @param integer $ownerUid
     * @param integer $itemId
     * @return array
     */
    public static function moochPlant($uid, $ownerUid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $result['content'] = 'serverWord_115';
            return $result;
        }
        
        //check is friend
        $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }
        
        $moochInfo = Hapyfish2_Island_Cache_Mooch::getMoochPlant($ownerUid, $itemId);

        if (!empty($moochInfo) && in_array($uid, $moochInfo)) {
            $result['content'] = 'serverWord_144';
            return $result;
        }
        //insert plant mooch info
        $moochInfo[] = $uid;
		Hapyfish2_Island_Cache_Mooch::moochPlant($ownerUid, $itemId, $moochInfo);

        $userVO = Hapyfish2_Island_HFC_User::getUserVO($ownerUid);
	    $ownerCurrentIsland = $userVO['current_island'];
        $userPlant = Hapyfish2_Island_HFC_Plant::getOne($ownerUid, $itemId, 1, $ownerCurrentIsland);
        if (!$userPlant) {
            $result['content'] = 'serverWord_115';
            return $result;
        }
        
        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo) {
        	return $result;
        }
        
        $now = time();
        Hapyfish2_Island_Bll_PlantStatus::outPlantPeople($ownerUid, $userPlant, $now);
        
		if ($userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 || $userPlant['deposit'] <= 0) {
			$result['content'] = 'serverWord_143';
            return $result;
        }
        
		if ($userPlant['event'] == 2) {
            $result['content'] = 'serverWord_121';
            return $result;
        }
        
        if ($userPlant['event'] == 1) {
            if ($now - $userPlant['start_pay_time'] - $userPlant['delay_time'] >= $userPlant['pay_time'] * 0.6) {
                $result['content'] = 'serverWord_121';
                return $result;
            }
        }
        
		if ($now - $userPlant['start_pay_time'] - $userPlant['pay_time'] - $userPlant['delay_time'] < 0) {
            return $result;
        }
        
		//check plant deposit
		$safeCoinNum = $userPlant['start_deposit'] * $plantInfo['safe_coin_num'];
		$safeCoinNum = round($safeCoinNum);
		if ($userPlant['deposit'] <= $safeCoinNum) {
			$result['content'] = 'serverWord_145';
			return $result;
		}
        
        try {
            $moochCoin = rand(5, 100);
            $remainCoin = $userPlant['deposit'] - $moochCoin;
            $moochCoin = $remainCoin >= $safeCoinNum ? $moochCoin : $userPlant['deposit'] - $safeCoinNum;

            //
            $userPlant['deposit'] = $userPlant['deposit'] - $moochCoin;
            
            Hapyfish2_Island_HFC_Plant::updateOne($ownerUid, $itemId, $userPlant);

            $addExp = 2;
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
            
            //add user coin and exp
			Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $addExp, $moochCoin);
            
	        $result['status'] = 1;
            $result['expChange'] = $addExp;
            $result['coinChange'] = $moochCoin;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return $result;
        }
        
        try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_8', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_8', 1);
        
			//task id 3009,task type 8
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3009);
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
			if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}
        
		try {
	        $minifeed = array('uid' => $ownerUid,
	                          'template_id' => 12,
	                          'actor' => $uid,
	                          'target' => $ownerUid,
	                          'title' => array('money' => $moochCoin),
	                          'type' => 2,
	                          'create_time' => $now);
	        
	        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
	        
		} catch (Exception $e) {
            
        }
        
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
    public static function managePlant($uid, $itemId, $eventType, $ownerUid)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }

        if ($uid != $ownerUid) {
	        //check is friend
	        $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
	        if (!$isFriend) {
	            $result['content'] = 'serverWord_120';
	            return array('resultVo' => $result);
	        }
        }
        //get uservo,current island id
		$userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
		$islandId = $userVO['current_island'];
		
        $userPlant = Hapyfish2_Island_HFC_Plant::getOne($ownerUid, $itemId, 1, $islandId);
        
        if (!$userPlant) {
            $result['content'] = 'serverWord_115';
            return array('resultVo' => $result);
        }
        
        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo) {
        	return array('resultVo' => $result);
        }

        //check plant event type
        if ($userPlant['event'] != $eventType ) {
            $result['content'] = 'serverWord_146';
            return array('resultVo' => $result);
        }
        
        $now = time();
        //check is not damage card
        if ( $userPlant['event'] != 2 ) {
	        //check plant event info
	        if ( $now - $userPlant['start_pay_time'] + $userPlant['delay_time'] < $plantInfo['pay_time'] * 0.6 ) {
	        	$result['content'] = 'serverWord_146';
	            return array('resultVo' => $result);
	        }
        	$stopTime = ($now - $userPlant['start_pay_time']) - $plantInfo['pay_time'] * 0.6;
        }
        else {
        	$stopTime = ($now - $userPlant['start_pay_time']) - $plantInfo['pay_time'] * 0.6;
        }
        
        $payRemainder = $userPlant['pay_time'] - ($now - ($userPlant['start_pay_time'] + $stopTime)) + $userPlant['delay_time'];
        $payRemainder = max(0, $payRemainder);
        $addExp = 5;

        try {
	        $userPlant['event'] = 0;
	        $userPlant['start_pay_time'] += $stopTime;
	        
	        Hapyfish2_Island_HFC_Plant::updateOne($ownerUid, $itemId, $userPlant);
	        
            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);

            $result['status'] = 1;
            $result['expChange'] = $addExp;
        }
        catch (Exception $e) {
            $result['status'] = -1;
            $result['content'] = 'serverWord_110';
            return array('resultVo' => $result);
        }
        
        try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_3', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_3', 1);
			
			//task id 3024,task type 3
			$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3024);
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
			if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}
        
        if ($ownerUid != $uid) {
        	try {
	            $minifeed = array('uid' => $ownerUid,
	                              'template_id' => 13,
	                              'actor' => $uid,
	                              'target' => $ownerUid,
	                              'title' => array('manage_num' => 1),
	                              'type' => 1,
	                              'create_time' => $now);
	            Hapyfish2_Island_Bll_Feed::insertMinifeed($minifeed);
        	}catch(Exception $e) {
        		
        	}
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
    public static function upgradePlant($uid, $itemId)
    {
        $result = array('status' => -1);

        $itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $ownerCurrentIsland = $userVO['current_island'];
        $userPlant = Hapyfish2_Island_HFC_Plant::getOne($uid, $itemId, 1, $ownerCurrentIsland);
        if (!$userPlant) {
            $result['content'] = 'serverWord_115';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo || !$plantInfo['next_level_cid']) {
        	return array('resultVo' => $result);
        }
        
        $nextLevelPlantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($plantInfo['next_level_cid']);
        if (!$nextLevelPlantInfo) {
        	return array('resultVo' => $result);
        }

        $praiseChange = $nextLevelPlantInfo['add_praise'] - $plantInfo['add_praise'];
        
        $userInfo = Hapyfish2_Island_HFC_User::getUser($uid, array('coin' => 1, 'level' => 1));
        if ($userInfo === null) {
        	return array('resultVo' => $resultVo);
        }
        
        //check need level
        if ($nextLevelPlantInfo['need_level'] > $userInfo['level']) {
            $result['content'] = 'serverWord_136';
            $result = array('resultVo' => $result);
            return $result;
        }
        
        $userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
        switch ( $ownerCurrentIsland ) {
        	case 1 :
        		//$userIslandInfo['praise'] = $userIslandInfo['praise'];
        		$currentIslandPraise = $userIslandInfo['praise'];
        		break;
        	case 2 :
        		//$userIslandInfo['praise'] = $userIslandInfo['praise_2'];
        		$currentIslandPraise = $userIslandInfo['praise_2'];
        		break;
        	case 3 :
        		//$userIslandInfo['praise'] = $userIslandInfo['praise_3'];
        		$currentIslandPraise = $userIslandInfo['praise_3'];
        		break;
        	case 4 :
        		//$userIslandInfo['praise'] = $userIslandInfo['praise_4'];
        		$currentIslandPraise = $userIslandInfo['praise_4'];
        		break;
        }
		//check need praise
        if ($nextLevelPlantInfo['need_praise'] > $currentIslandPraise) {
            $result = array('resultVo' => $result);
            return $result;
        }
    
        $now = time();
        //折扣系统
        if ( $nextLevelPlantInfo['cheap_price'] > 0 && $now > $nextLevelPlantInfo['cheap_start_time'] && $now < $nextLevelPlantInfo['cheap_end_time'] ) {
        	$nextLevelPlantInfo['price'] = $nextLevelPlantInfo['cheap_price'];
        }
        
        $addExp = 5;
        $price = $nextLevelPlantInfo['price'];
        $priceType = $nextLevelPlantInfo['price_type'];
        require_once(CONFIG_DIR . '/language.php');
        //check need coin
        if ($priceType == 1) {
            if ($price > $userInfo['coin']) {
                $result['content'] = 'serverWord_137';
                $result = array('resultVo' => $result);
                return $result;
            }

			$userPlant['level'] += 1;
			$userPlant['cid'] = $nextLevelPlantInfo['cid'];
			$userPlant['pay_time'] = $nextLevelPlantInfo['pay_time'];
			$userPlant['ticket'] = $nextLevelPlantInfo['ticket'];
	        	
			$res = Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $userPlant, true);
			if (!$res) {
				$result['status'] = -1;
				$result['content'] = 'serverWord_110';
				$result = array('resultVo' => $result);
				return $result;
			}
			
			$ok = Hapyfish2_Island_HFC_User::decUserCoin($uid, $price);
			if ($ok) {				//add log
				$aryParam = array();
				$aryParam['name'] = $plantInfo['name'];
				$aryParam['num'] = $userPlant['level'];
				//add log
				foreach ($aryParam as $k => $v) {
		            $parakeys[] = '{*' . $k . '*}';
		            $paravalues[] = $v;
		        }
				$summary = str_replace($parakeys, $paravalues, LANG_PLATFORM_EXT_TXT_104);
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $nextLevelPlantInfo['price'], $summary, $now);
			} else {
				info_log(json_encode($userPlant), 'upgrade_coin_failure');
			}
	        	
			Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
	        	
			//check user current island 
            switch ( $userIslandInfo['current_island'] ) {
            	case 2 :
            		$userIslandInfo['praise_2'] += $praiseChange;
            		break;
            	case 3 :
            		$userIslandInfo['praise_3'] += $praiseChange;
            		break;
            	case 4 :
            		$userIslandInfo['praise_4'] += $praiseChange;
            		break;
            	default :
            		$userIslandInfo['praise'] += $praiseChange;
            		break;
            }
            
			Hapyfish2_Island_HFC_User::updateUserIsland($uid, $userIslandInfo);

            //check double exp
			$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
			$doubleexpCardTime = $userCardStatus['doubleexp'];
			if ($doubleexpCardTime - $now > 0) {
				$addExp = $addExp*2;
				$result['expDouble'] = 2;
			}
			Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
			
			try {
				Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $price);
				
				//task id 3012,task type 14
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
				if ( $checkTask['status'] == 1 ) {
					$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
				}
			} catch (Exception $e) {
			}
			
			$result['status'] = 1;
			$result['coinChange'] = -$price;
			$result['expChange'] = $addExp;
			$result['praiseChange'] = $praiseChange;
        }
        else if ($priceType == 2) {
        	$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        	if (!$balanceInfo) {
	        	$result['content'] = 'serverWord_1002';
	        	return array('resultVo' => $result);
	        }
	        
            $gold = $balanceInfo['balance'];
            if ($nextLevelPlantInfo['price'] > $gold) {
                $result['content'] = 'serverWord_140';
                $result = array('resultVo' => $result);
                return $result;
            }
            
			$isVip = $balanceInfo['is_vip'];
			$userLevel = $userInfo['level'];

			$userPlant['level'] += 1;
			$userPlant['cid'] = $nextLevelPlantInfo['cid'];
			$userPlant['pay_time'] = $nextLevelPlantInfo['pay_time'];
			$userPlant['ticket'] = $nextLevelPlantInfo['ticket'];
			
			$res = Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $userPlant, true);
            $aryParam = array();
			$aryParam['name'] = $plantInfo['name'];
			$aryParam['num'] = $userPlant['level'];
			//add log
			foreach ($aryParam as $k => $v) {
	            $parakeys[] = '{*' . $k . '*}';
	            $paravalues[] = $v;
	        }
			if ($res) {
				$goldInfo = array(
					'uid' => $uid,
					'cost' => $price,
					//'summary' => '升级' . $plantInfo['name'] . '到' . $userPlant['level'] . '星',
					'summary' => str_replace($parakeys, $paravalues, LANG_PLATFORM_EXT_TXT_104),
					'user_level' => $userLevel,
					'cid' => $nextLevelPlantInfo['cid'],
					'num' => 1
				);
		        $ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		        if (!$ok2) {
		        	
		        }
				
				Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);
				
				//check user current island 
	            switch ( $userIslandInfo['current_island'] ) {
	            	case 2 :
	            		$userIslandInfo['praise_2'] += $praiseChange;
	            		break;
	            	case 3 :
	            		$userIslandInfo['praise_3'] += $praiseChange;
	            		break;
	            	case 4 :
	            		$userIslandInfo['praise_4'] += $praiseChange;
	            		break;
	            	default :
	            		$userIslandInfo['praise'] += $praiseChange;
	            		break;
	            }
            
				Hapyfish2_Island_HFC_User::updateUserIsland($uid, $userIslandInfo);
				
				//check double exp
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$doubleexpCardTime = $userCardStatus['doubleexp'];
				if ($doubleexpCardTime - $now > 0) {
					$addExp = $addExp*2;
					$result['expDouble'] = 2;
				}
				Hapyfish2_Island_HFC_User::incUserExp($uid, $addExp);
			
				try {
					Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $price);
					
					//task id 3068,task type 19
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
					if ( $checkTask['status'] == 1 ) {
						$resultVo['finishTaskId'] = $checkTask['finishTaskId'];
					}
				} catch (Exception $e) {
				}
				$result['status'] = 1;
				$result['goldChange'] = -$price;
				$result['expChange'] = $addExp;
				$result['praiseChange'] = $praiseChange;
			}
        } else {
			$result['status'] = -1;
			$result['content'] = 'serverWord_110';
			$result = array('resultVo' => $result);
			return $result;
        }

    	try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            } else {
            	$result['feed'] = Hapyfish2_Island_Bll_Activity::send('BUILDING_LEVEL_UP', $uid);
            }
		} catch (Exception $e) {
		}
        
        $buildingVo = self::handlerPlant($userPlant, $now);
        
        $result = array('resultVo' => $result, 'buildingVo' => $buildingVo);

        return $result;
    }
    
	public static function harvestAllPlant($uid)
	{
		//get user vo,current island
		$userVo = Hapyfish2_Island_HFC_User::getUserVO($uid);
        $userCurrentIsland = $userVo['current_island']; 
        
		$data = Hapyfish2_Island_HFC_Plant::getOnIsland($uid, $userCurrentIsland, false);
		
		$result = array('status' => -1);
		$coinChange = 0;
		$expChange = 0;
		
		if ($data['plants']) {
			Hapyfish2_Island_Bll_PlantStatus::outIslandPeople($uid, $data['plants'], $userCurrentIsland);
			
        	$now = time();
			foreach ($data['plants'] as $userPlant) {
		        //check plant visitor
		        if ($userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 || $userPlant['deposit'] <= 0) {
		            continue;
		        }
				
		        if ($userPlant['event'] == 2) {
		            continue;
		        }
			
				if ($userPlant['event'] == 1) {
		            if ($now - $userPlant['start_pay_time'] - $userPlant['delay_time'] >= $userPlant['pay_time'] * 0.6) {
						continue;
		            }
        		}
		        
		        //check plant pat time
				if ($now - $userPlant['start_pay_time'] - $userPlant['pay_time'] - $userPlant['delay_time'] < 0) {
            		continue;
        		}
		        
		        $coinChange += $userPlant['deposit'];
		        $expChange += 3;
		        
				$userPlant['start_pay_time'] = $now;
				$userPlant['event'] = 0;
				$userPlant['delay_time'] = 0;
				$userPlant['deposit'] = 0;
				$userPlant['start_deposit'] = 0;
        
				Hapyfish2_Island_HFC_Plant::updateOne($uid, $userPlant['id'], $userPlant);
				
            	//delete user plant mooch info
            	Hapyfish2_Island_Cache_Mooch::clearMoochPlant($uid, $userPlant['id']);
			}
			
			if ($coinChange > 0) {
				//check double exp
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$doubleexpCardTime = $userCardStatus['doubleexp'];
				if ($doubleexpCardTime - $now > 0) {
					$expChange = $expChange*2;
					$result['expDouble'] = 2;
				}
				
		        //check user god card
				$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
				$userMammonTime = $userCardStatus['mammon'];
				$userPoorTime = $userCardStatus['poor'];
				if ( $userMammonTime <= $now && $userPoorTime > $now ) {
					$coinChange = round($coinChange * 0.9);
				}
				if ( $userMammonTime > $now && $userPoorTime <= $now ) {
					$coinChange = round($coinChange * 1.1);
				}
				
				Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $expChange, $coinChange);
		        
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
	            
	            $result['status'] = 1;
	            $result['expChange'] = $expChange;
	            $result['coinChange'] = $coinChange;
	            
				/*
				if (isset($harvestResult['expDouble'])) {
					$result['expDouble'] = $harvestResult['expDouble'];
				}
				$result['itemBoxChange'] = true;
				*/
			}
			else {
				$result['status'] = -1;
				$result['content'] = 'serverWord_1003';
			}
		}
		
		return array('result' => $result);
	}

}