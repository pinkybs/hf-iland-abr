<?php

class Hapyfish_Island_Bll_Card
{
	/**
	 * use card
	 *
	 * @param integer $uid
	 * @param integer $ownerUid
	 * @param integer $cid
	 * @param integer $itemId
	 * @return array
	 */
	public static function useCard($uid, $ownerUid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		if (empty($cid)) {
			$resultVo['content'] = 'serverWord_104';
			return $resultVo;
		}

		$id = substr($cid, 0, -2);
		//
		if ($id == 271) {
			$resultVo = self::insuranceCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} 
		else if ($id == 265 || $id == 266) {
			$resultVo = self::delayCard($uid, $cid, $itemId);
		} 
		else if ($id == 267) {
			return;
			$resultVo = self::damageCard($uid, $ownerUid, $cid, $itemId);
		} 
		else if ($id == 269) {
			$resultVo = self::plunderCard($uid, $ownerUid, $cid);
		} 
		else if ($id == 268) {
			$resultVo = self::defenseCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} 
		else if ($id == 270) {
			$resultVo = self::checkCard($uid, $ownerUid, $cid);
			if(isset($resultVo['resultVo']['coinChange'])){
    			$resultVo['coinChange'] = $resultVo['resultVo']['coinChange'];
    			unset($resultVo['resultVo']['coinChange']);
			}
		}

		//get user item info
		$itemBox = Hapyfish_Island_Bll_Warehouse::loadItems($uid);
		$resultVo['items'] = $itemBox;

		return $resultVo;
	}

	/**
	 * insurance Card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @return array
	 */
	public static function insuranceCard($uid, $cid)
	{
		$resultVo = array('status' => -1);
		
		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check card count >1
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (empty($isHave)) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}
		
		$insuranceCardTime = Hapyfish_Island_Cache_User::getInsuranceCardTime($uid);
		$now = time();
		if (($now - $insuranceCardTime) < 6*3600 ) {
			$resultVo['content'] = 'serverWord_106';
			return array('resultVo' =>$resultVo);
		}

		$defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($uid);
        //card info
        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);

		try {
			//update Card count
			$dalCard->updateCardById($uid, $cid, -1);

			Hapyfish_Island_Cache_User::updateInsuranceCardTime($uid, $now);
			
			Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array('0' => array('cid' => 27141, 'time' => 6*3600),
                                            '1' => array('cid' => 26841, 'time' => 12*3600 - ($now - $defenseCardTime)));
		}catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
			info_log('[insuranceCard]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Card');
            return array('resultVo' =>$resultVo);
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user use card count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}
		
		try {
	        //check user level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
		    if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
		} catch (Exception $e) {

		}

        return array('resultVo' =>$resultVo);
	}

	/**
	 * speed card
	 *
	 * @param integer $uid
	 * @param integer $pid
	 * @param integer $cid
	 * @return array
	 */
	public static function speedCard($uid, $pid, $cid)
	{
		$resultVo = array('status' => -1);

		if (empty($pid)) {
			$resultVo['content'] = 'serverWord_107';
			return array('resultVo' =>$resultVo);
		}

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (empty($isHave))
		{
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$dalDock = Dal_Island_Dock::getDefaultInstance();
		$dockInfo = $dalDock->getUserPositionById($uid, $pid);
	    if ($dockInfo['uid'] != $uid) {
			$resultVo['content'] = 'serverWord_108';
			return array('resultVo' =>$resultVo);
		}
		
		$now = time();
		$time = $now - $dockInfo['receive_time'] - $dockInfo['wait_time'] + $dockInfo['speedup_type'];

		if ($cid == 26241 && $dockInfo['is_usecard_one'] == 1) {
			$resultVo['content'] = 'serverWord_109';
			return array('resultVo' =>$resultVo);
		}

        //cart info
        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);
        
		$id = substr($cid, 0, -2);

		$isUseCardOne = 0;
		//is_usecard_one = 1 : used card 1
		if ($dockInfo['is_usecard_one'] == 1) {
			$isUseCardOne = 1;
		}

		if ($id == 263) {
			//card 2 speed 50 minute
			$speedTime = 3000;
		}
		elseif ($id == 264) {
			//card 2 speed 2 hour 2.5
			$speedTime = 9000;
		}
		else {//card 1 speed 10 minute
			$speedTime = 600;
			$isUseCardOne = 1;
		}

		try {
			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			$newDock = array();
			$newDock['speedup'] = 1;
			$newDock['is_usecard_one'] = $isUseCardOne;
			$newDock['speedup_type'] = $dockInfo['speedup_type'] + $speedTime;

			//check ship is have arrive?
			if ($time + $speedTime > $dockInfo['wait_time']) {
				$newDock['status'] = 2;
			}
			$dalDock->updateUserPosition($uid, $pid, $newDock);

            Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);
			
			Hapyfish_Island_Cache_Dock::cleanUserPositionList($uid);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;

		} catch (Exception $e) {
			info_log('[speedCard]:'.$e->getMessage(), 'Hapyfish_Island_Bll_Card');
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
			//get user boat arrive time
            $arriveTime = Hapyfish_Island_Bll_Dock::getArriveTime($uid);
            $dalUser = Dal_Island_User::getDefaultInstance();
			//update user exp
			$dalUser->updateUser($uid, array('boat_arrive_time' => $arriveTime));
		} catch (Exception $e) {

		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user use card count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'use_speedup_card_count', 1);
		} catch (Exception $e) {

		}
		
		try {
		    //check user level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
		} catch (Exception $e) {

		}

		$dockInfo = Hapyfish_Island_Bll_Dock::getDockShipInfo($uid, $pid);
		//get user item info
        $itemBox = Hapyfish_Island_Bll_Warehouse::loadItems($uid);

		return array('resultVo' =>$resultVo, 'boatPositionVo' => $dockInfo, 'items' => $itemBox);
	}

	/**
	 * use plunder card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @param integer $ownerUid
	 * @return array $resultVo
	 */
	public static function plunderCard($uid, $ownerUid, $cid)
	{
		$resultVo = array('status' => -1);
	
        if ($uid == $ownerUid) {
            $resultVo['content'] = 'serverWord_111';
            return array('resultVo' =>$resultVo);
        }
        
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
	    
	    $ownerLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($ownerUid);
	    
	    //check level
	    if ($ownerLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_112';
            return array('resultVo' =>$resultVo);
	    }

	    $now = time();
	    $defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($ownerUid);
		if ($now - $defenseCardTime < 12*3600) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' =>$resultVo);
		}
		
        $userLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        //check level
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_114';
            return array('resultVo' =>$resultVo);
        }
        
		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);
		
		$ownerCoin = Hapyfish_Island_Cache_User::getCoin($ownerUid);
		$updateMoney = floor($ownerCoin * 0.01);

		try {
			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);
			
			Hapyfish_Island_Cache_User::incCoinAndExp($uid, $updateMoney, $cardInfo['add_exp']);

			Hapyfish_Island_Cache_User::decCoin($ownerUid, $updateMoney);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['coinChange'] = $updateMoney;
            $resultVo['itemBoxChange'] = true;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {
		}

		try {
			//insert minifeed
			$minifeed = array('uid' => $ownerUid,
	                          'template_id' => 5,
	                          'actor' => $uid,
	                          'target' => $ownerUid,
	                          'title' => array('plunderCoin' => $updateMoney),
	                          'type' => 2,
	                          'create_time' => $now);
			Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
			
		} catch (Exception $e) {
		}

		try {
	        //check user level up
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	        
			if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
	        
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }

		} catch (Exception $e) {
		}

		return array('resultVo' =>$resultVo);
	}

	/**
	 * use delay time card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @param integer $itemId
	 * @return array $resultVo
	 */
	public static function delayCard($uid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		$now = time();

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }
	    
		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$plantInfo = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($uid, $itemId);

		if (!$plantInfo) {
			return array('resultVo' =>$resultVo);
		}

	    if ($plantInfo['uid'] != $uid) {
			$resultVo['content'] = 'serverWord_116';
			return array('resultVo' =>$resultVo);
		}

		$plantNb = Hapyfish_Island_Cache_Shop::getPlantById($plantInfo['cid']);

		if ( $plantInfo['wait_visitor_num'] <= 0 && $plantInfo['start_deposit'] <= 0 ) {
            $resultVo['content'] = 'serverWord_117';
            return array('resultVo' =>$resultVo);
        }

		if ( $now - $plantInfo['start_pay_time'] - $plantNb['pay_time'] - $plantInfo['delay_time'] > 0 ) {
            $resultVo['content'] = 'serverWord_118';
            return array('resultVo' =>$resultVo);
        }

		if ( $plantInfo['event'] != 0 ) {
            if ($plantInfo['event'] == 2) {
                $resultVo['content'] = 'serverWord_121';
                return array('resultVo' =>$resultVo);
            }
            
			if ( $now - $plantInfo['start_pay_time'] >= $plantNb['pay_time'] * 0.6 ) {
				$resultVo['content'] = 'serverWord_121';
				return array('resultVo' =>$resultVo);
	        }
        }

        $delaryTime = $cid == 26541 ? 10800 : 21600;
        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);
		$plantInfo['delay_time'] = $plantInfo['delay_time'] + $delaryTime;
		try {
			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			//updateUserPlantPayInfoById
			Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($uid, $itemId, $plantInfo);
			
			Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);
			

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {
		}

		//check user level up
		try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
		} catch (Exception $e) {
		}

        //get owner plant info
        $plantInfo = Hapyfish_Island_Cache_Plant::getUsingPlantById($uid, $itemId);
        
        if ( $plantInfo ) {
        	$stopTime = ($now - $plantInfo['start_pay_time']) - $plantInfo['pay_time'] * 0.6;
	        $payRemainder = $plantInfo['pay_time'] - ($now - ($plantInfo['start_pay_time'] + $stopTime)) + $plantInfo['delay_time'];
	        $payRemainder = max(0, $payRemainder);

	        $plantInfo['payRemainder'] = $payRemainder;
        	$plantInfo['hasSteal'] = 0;

        	unset($plantInfo['pay_time']);
        	unset($plantInfo['delay_time']);
        	unset($plantInfo['start_pay_time']);
        }

		return array('resultVo' => $resultVo, 'buildingVo' => $plantInfo);
	}

	/**
	 * use damage card
	 *
	 * @param integer $uid
	 * @param integer $ownerUid
	 * @param integer $cid
	 * @param integer $itemId
	 * @return array
	 */
	public static function damageCard($uid, $ownerUid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		
	    if ($ownerUid == $uid) {
            $resultVo['content'] = 'serverWord_119';
            return array('resultVo' =>$resultVo);
        }

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }

        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
	    
	    $ownerLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($ownerUid);

	    if ($ownerLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_122';
            return array('resultVo' =>$resultVo);
	    }
	    
        $userLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_123';
            return array('resultVo' =>$resultVo);
        }

		$now = time();
		$defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($ownerUid);
		if ($now - $defenseCardTime < 12*3600) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' =>$resultVo);
		}

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$plantInfo = Hapyfish_Island_Cache_Plant::getUserPlantPayInfoById($ownerUid, $itemId);
		
		//check plant visitor
        if ( $plantInfo['wait_visitor_num'] <= 0 && $plantInfo['deposit'] <= 0 ) {
            $resultVo['content'] = 'serverWord_124';
            return array('resultVo' =>$resultVo);
        }

		if ( $plantInfo['event'] == 2 ) {
			$resultVo['content'] = 'serverWord_125';
            return array('resultVo' =>$resultVo);
        }

        $plantNb = Hapyfish_Island_Cache_Shop::getPlantById($plantInfo['cid']);
		if ( $now - $plantInfo['start_pay_time'] - $plantNb['pay_time'] - $plantInfo['delay_time'] > 0 ) {
            $resultVo['content'] = 'serverWord_126';
            return array('resultVo' =>$resultVo);
        }

		if ( $plantInfo['event'] != 0 ) {
            if ( $now - $plantInfo['start_pay_time'] - $plantInfo['delay_time'] >= $plantNb['pay_time'] * 0.6 ) {
                $resultVo['content'] = 'serverWord_127';
                return array('resultVo' =>$resultVo);
            }
        }

        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);

		try {
			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

        	$plantInfo['event'] = 2;
        	$plantInfo['damage_card_time'] = $now;
			Hapyfish_Island_Cache_Plant::updateUserPlantPayInfoById($ownerUid, $itemId, $plantInfo);

			Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;
		}
		catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			$minifeed = array('uid' => $ownerUid,
	                          'template_id' => 3,
	                          'actor' => $uid,
	                          'target' => $ownerUid,
	            		  	  'title' => array('plantName' => $plantNb['name']),
	                          'type' => 2,
	                          'create_time' => $now);
			Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
			
	        $resultVo['feed'] = Bll_Island_Activity::send('BUILDING_DAMAGE', $uid, array('building' => $plantNb['name']), $ownerUid);
		}catch (Exception $e) {

		}
        
        //check user level up
        try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {

		}
        
        //get owner plant info
        $plantInfo = Hapyfish_Island_Cache_Plant::getUsingPlantById($ownerUid, $itemId);
        if ( $plantInfo ) {
        	$stopTime = ($now - $plantInfo['start_pay_time']) - $plantInfo['pay_time'] * 0.6;
	        $payRemainder = $plantInfo['pay_time'] - ($now - ($plantInfo['start_pay_time'] + $stopTime)) + $plantInfo['delay_time'];
	        $payRemainder = max(0, $payRemainder);

	        $plantInfo['payRemainder'] = $payRemainder;
	        $plantInfo['hasSteal'] = 0;

	        unset($plantInfo['pay_time']);
	        unset($plantInfo['delay_time']);
	        unset($plantInfo['start_pay_time']);
        }

		return array('resultVo' => $resultVo, 'buildingVo' => $plantInfo);
	}

	/**
	 * use defense card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @return array $resultVo
	 */
	public static function defenseCard($uid, $cid)
	{
		$resultVo = array('status' => -1);
	    
        $userLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
	    if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_128';
            return array('resultVo' =>$resultVo);
	    }

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check card count >1
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (empty($isHave))
		{
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

        //card info
        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);

       	$now = time();

       	$insuranceCardTime = Hapyfish_Island_Cache_User::getInsuranceCardTime($uid);
		try {
			//update Card count
			$dalCard->updateCardById($uid, $cid, -1);
			
			Hapyfish_Island_Cache_User::updateDefenseCardTime($uid, $now);
			
			Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array('0' => array('cid' => 26841, 'time' => 12*3600),
                                            '1' => array('cid' => 27141, 'time' => 6*3600 - ($now - $insuranceCardTime)));
		}catch (Exception $e) {
			info_log('[error_message]-[defenseCard]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

        //check user level up
        try {
            $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {	
        }

        return array('resultVo' => $resultVo);
	}

	/**
	 * use check card
	 *
	 * @param integer $uid
	 * @param integer $ownerUid
	 * @param integer $cid
	 * @return array $resultVo
	 */
	public static function checkCard($uid, $ownerUid, $cid)
	{
		$resultVo = array('status' => -1);
	
        if ($ownerUid == $uid ) {
            $resultVo['content'] = 'serverWord_129';
            return array('resultVo' =>$resultVo);
        }
        
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
	    
	    $ownerLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($ownerUid);	    
	    if (!$ownerLevelInfo ) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }

	    if ($ownerLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_130';
            return array('resultVo' =>$resultVo);
	    }
	    
		$now = time();
		$defenseCardTime = Hapyfish_Island_Cache_User::getDefenseCardTime($ownerUid);
		if ($now - $defenseCardTime < 12*3600) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' =>$resultVo);
		}

        $userLevelInfo = Hapyfish_Island_Cache_User::getLevelInfo($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_131';
            return array('resultVo' =>$resultVo);
        }

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

        $randerNum = rand(1, 3);
        $money = 50;
        if ($randerNum == 2) {
            $money = 100;
        } elseif ($randerNum == 3) {
            $money = 500;
        }
        
        $OwnerCoin = Hapyfish_Island_Cache_User::getCoin($ownerUid);
        
        $money = $money > $OwnerCoin ? $OwnerCoin : $money;

        $cardInfo = Hapyfish_Island_Cache_Shop::getCardById($cid);
        
		try {
			//update count -1
            $dalCard->updateCardById($uid, $cid, -1);
			
			Hapyfish_Island_Cache_User::incExp($uid, $cardInfo['add_exp']);
			
			Hapyfish_Island_Cache_User::decCoin($ownerUid, $money);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['coinChange'] = -$money;
		}
		catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}
		
		try {
			$dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
			//update user today use card count
			$dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
			//update user send gift count
			$dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			//insert minifeed
			$minifeed = array('uid' => $ownerUid,
	                          'template_id' => 6,
	                          'actor' => $uid,
	                          'target' => $ownerUid,
	                          'title' => array('money' => $money),
	                          'type' => 2,
	                          'create_time' => $now);
			Hapyfish_Island_Bll_Feed::insertMiniFeed($minifeed);
		} catch (Exception $e) {

		}
		
        //check user level up
        try {
	        $levelUp = Hapyfish_Island_Bll_User::checkLevelUp($uid);
	        $resultVo['levelUp'] = $levelUp['levelUp'];
	        $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
	
	        //send activity
	        if ( $levelUp['levelUp'] ) {
	            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $levelUp['newLevel']));
	        }
	        if ( $levelUp['islandLevelUp'] ) {
	            //get next level island info
	            $nextLevelIsland = Hapyfish_Island_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
	            //update user achievement island visitor count
	            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
	        }
        } catch (Exception $e) {

		}

		return array('resultVo' =>$resultVo);
	}
}