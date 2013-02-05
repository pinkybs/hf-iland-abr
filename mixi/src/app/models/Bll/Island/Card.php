<?php

require_once 'Bll/Abstract.php';

/**
 * logic's Operation
 *
 * @package    Bll
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/26    xial
 */
class Bll_Island_Card extends Bll_Abstract {

	/**
	 * read item by id
	 * @param integer $uid
	 * @return array $resultVo
	 */
	public function readItem($uid)
	{
		$dalCard = Dal_Island_Card::getDefaultInstance();

		$lstCard = $dalCard->getLstCardById($uid);
		$cardVo = array();
		if ($lstCard) {
			foreach ($lstCard as $var) {
				$itemVo = array($var['id'] . $var['item_type'], $var['cid'], $var['count']);
				$cardVo[] = $itemVo;
			}
		}

		$dalBuilding = Dal_Island_Building::getDefaultInstance();
		$lstBuilding = $dalBuilding->getItemBoxBuilding($uid);
		$buildingVo = array();
		if ($lstBuilding) {
			foreach ($lstBuilding as $var) {
				$itemVo = array($var['id'] , $var['cid'], 1);
				$buildingVo[] = $itemVo;
			}
		}

		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$lstPlant = $dalPlant->getItemBoxPlant($uid);
		$plantVo = array();
		if ($lstPlant) {
			foreach ($lstPlant as $var) {
				$itemVo = array($var['id'] , $var['cid'], "1", $var['level']);
				$plantVo[] = $itemVo;
			}
		}

		$lstBackground = $dalBuilding->getItemBoxBackground($uid);
		$backgroundVo = array();
		if ($lstBackground) {
			foreach ($lstBackground as $var) {
				$itemVo = array($var['id'] , $var['cid'], 1);
				$backgroundVo[] = $itemVo;
			}
		}

		$resultVo = array_merge($cardVo, $buildingVo, $plantVo, $backgroundVo);
		return $resultVo;
	}

	/**
	 * use card
	 *
	 * @param integer $uid
	 * @param integer $ownerUid
	 * @param integer $cid
	 * @param integer $itemId
	 * @return array
	 */
	public function useCard($uid, $ownerUid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		if (empty($cid)) {
			$resultVo['content'] = 'serverWord_104';
			return $resultVo;
		}

		$id = substr($cid, 0, -2);
		//
		if ($id == 271) {
			$resultVo = $this->insuranceCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} 
		else if ($id == 265 || $id == 266) {
			$resultVo = $this->delayCard($uid, $cid, $itemId);
		} 
		else if ($id == 267) {
			$resultVo = $this->damageCard($uid, $ownerUid, $cid, $itemId);
		} 
		else if ($id == 269) {
			$resultVo = $this->plunderCard($uid, $ownerUid, $cid);
		} 
		else if ($id == 268) {
			$resultVo = $this->defenseCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} 
		else if ($id == 270) {
			$resultVo = $this->checkCard($uid, $ownerUid, $cid);
			if(isset($resultVo['resultVo']['coinChange'])){
    			$resultVo['coinChange'] = $resultVo['resultVo']['coinChange'];
    			unset($resultVo['resultVo']['coinChange']);
			}
		}

		//get user item info
		$itemBox = $this->readItem($uid);
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
	public function insuranceCard($uid, $cid)
	{
		$resultVo = array('status' => -1);
		$now = time();

		$dalUser = Dal_Island_User::getDefaultInstance();
		$userInfo = $dalUser->getUserDockInfo($uid);
	    if (!$userInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check card count >1
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (empty($isHave)) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		if (($now - $userInfo['insurance_card']) < 6*3600 ) {
			$resultVo['content'] = 'serverWord_106';
			return array('resultVo' =>$resultVo);
		}

        //card info
        $cardInfo = Bll_Cache_Island::getCardById($cid);
        $bllDock = new Bll_Island_Dock();

		try {
			$this->_wdb->beginTransaction();
			//update Card count
			$dalCard->updateCardById($uid, $cid, -1);

			$newUser = array('insurance_card' => $now,
							 'exp' => $userInfo['exp'] + $cardInfo['add_exp']);
			$dalUser->updateUser($uid, $newUser);

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user use card count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array('0' => array('cid' => 27141, 'time' => 6*3600),
                                            '1' => array('cid' => 26841, 'time' => 12*3600 - ($now - $userInfo['defense_card'])));
		}catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
			$this->_wdb->rollBack();
			info_log('[error_message]-[insuranceCard]:'.$e->getMessage(), 'transaction');
            return array('resultVo' =>$resultVo);
		}

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
	    if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
	public function speedCard($uid, $pid, $cid)
	{
		$resultVo = array('status' => -1);
		$now = time();

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

		$time = $now - $dockInfo['receive_time'] - $dockInfo['wait_time'] + $dockInfo['speedup_type'];

		if ($cid == 26241 && $dockInfo['is_usecard_one'] == 1) {
			$resultVo['content'] = 'serverWord_109';
			return array('resultVo' =>$resultVo);
		}

        $dalUser = Dal_Island_User::getDefaultInstance();
        $bllDock = new Bll_Island_Dock();
        //cart info
        $cardInfo = Bll_Cache_Island::getCardById($cid);

		try {
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

			$this->_wdb->beginTransaction();

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

			//user info
			$userInfo = $dalUser->getUserLevelInfo($uid);

			//get user boat arrive time
            $arriveTime = $bllDock->getArriveTime($uid);

			//update user exp
			$dalUser->updateUser($uid, array('exp' => $userInfo['exp'] + $cardInfo['add_exp'],
											 'boat_arrive_time' => $arriveTime));
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();
			
			Bll_Cache_Island_Dock::clearCache('getUserPositionList', $uid);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user use card count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'use_speedup_card_count', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;

		}catch (Exception $e) {
			$this->_wdb->rollBack();
			info_log('[error_message]-[speedCard]:'.$e->getMessage(), 'transaction');
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

		$dockInfo = $bllDock->getDockShipInfo($uid, $pid);
		//get user item info
        $itemBox = $this->readItem($uid);

		return array('resultVo' =>$resultVo, 'boatPositionVo' => $dockInfo, 'items' => $itemBox);
	}

	/**
	 * update island user mood word
	 * @param integer $uid
	 * @param integer $cid
	 * @return array $resultVo
	 */
	public function updateMoodWord($uid, $cid)
	{
		$resultVo = array('status' => -1);

	    $dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
	    $userInfo = $dalUser->getUserDockInfo($uid);
	    if (!$userInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return $resultVo;
	    }

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return $resultVo;
		}

		$bllDock = new Bll_Island_Dock();
		$cardInfo = Bll_Cache_Island::getCardById($cid);

		try {
			$this->_wdb->beginTransaction();

			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			$dalUser->updateUser($uid, array('mood_word_count' => $userInfo['mood_word_count'] + $cardInfo['add_word']));

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user use card count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
		}
		catch (Exception $e) {
			$this->_wdb->roolback();
			$resultVo['content'] = 'serverWord_110';
            return $resultVo;
		}

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

		return $resultVo;
	}

	/**
	 * use plunder card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @param integer $ownerUid
	 * @return array $resultVo
	 */
	public function plunderCard($uid, $ownerUid, $cid)
	{
		$resultVo = array('status' => -1);
		$now = time();
	
        if ($uid == $ownerUid) {
            $resultVo['content'] = 'serverWord_111';
            return array('resultVo' =>$resultVo);
        }
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
        
	    $dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
	    $ownerInfo = $dalUser->getUserDockInfo($ownerUid);
	    if (!$ownerInfo ) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }
	    
	    //check level
	    if ($ownerInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_112';
            return array('resultVo' =>$resultVo);
	    }

		if ($now - $ownerInfo['defense_card'] < 12*3600 && $ownerInfo['defense_card']) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' =>$resultVo);
		}
		
		//get user info
        $userInfo = $dalUser->getUserLevelInfo($uid);
	
        //check level
        if ($userInfo['level'] < 10) {
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

		$cardInfo = Bll_Cache_Island::getCardById($cid);
		$updateMoney = floor($ownerInfo['coin'] * 0.01);
		$bllDock = new Bll_Island_Dock();

		try {
			$this->_wdb->beginTransaction();

			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			$dalUser->updateUser($uid, array('coin' => $userInfo['coin'] + $updateMoney,
											 'exp' => $userInfo['exp'] + $cardInfo['add_exp']));

			$dalUser->updateUser($ownerUid, array('coin' => $ownerInfo['coin'] - $updateMoney));

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['coinChange'] = $updateMoney;
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
		}
		catch (Exception $e) {
			$this->_wdb->roolback();
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		//insert minifeed
		$minifeed = array('uid' => $ownerUid,
                          'template_id' => 5,
                          'actor' => $uid,
                          'target' => $ownerUid,
                          'title' => array('plunderCoin' => $updateMoney),
                          'type' => 2,
                          'create_time' => $now);
		Bll_Island_Feed::insertMiniFeed($minifeed);

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
	public function delayCard($uid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		$now = time();

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }

	    $dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
	    $userInfo = $dalUser->getUserLevelInfo($uid);
	    if (!$userInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }

		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$isExists = $dalPlant->isPlantExistsById($uid, $itemId);
		if (!$isExists) {
			return array('resultVo' =>$resultVo);
		}

		$dalCard = Dal_Island_Card::getDefaultInstance();
		//check count > 0
		$isHave = $dalCard->isHaveCardById($uid, $cid);
		if (!$isHave) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$plantInfo = $dalPlant->getUserPlantById($itemId, $uid);
	    if ($plantInfo['uid'] != $uid) {
			$resultVo['content'] = 'serverWord_116';
			return array('resultVo' =>$resultVo);
		}

		$plantNb = Bll_Cache_Island::getPlantById($plantInfo['bid']);

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
        $cardInfo = Bll_Cache_Island::getCardById($cid);
        $bllDock = new Bll_Island_Dock();

		try {
			$this->_wdb->beginTransaction();
			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			$dalPlant->updateUserPlant($itemId, array('uid' => $plantInfo['uid'], 'delay_time' => $plantInfo['delay_time'] + $delaryTime));

			$dalUser->updateUser($uid, array('exp' => $userInfo['exp'] + $cardInfo['add_exp']));

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();
			
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $plantInfo['uid']);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $plantInfo['uid']);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
		}
		catch (Exception $e) {
			$this->_wdb->roolback();
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

        //get owner plant info
        $plantInfo = $dalPlant->getUsingPlantById($itemId, $uid);
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
	public function damageCard($uid, $ownerUid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		$now = time();

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -3);
        if ( $itemType != 3 ) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }
	
        if ($ownerUid == $uid) {
            $resultVo['content'] = 'serverWord_119';
            return array('resultVo' =>$resultVo);
        }
        
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
        
		$dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
		$ownerInfo = $dalUser->getUserDockInfo($ownerUid);
	    if (!$ownerInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }

	    if ($ownerInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_122';
            return array('resultVo' =>$resultVo);
	    }
	    
        $userInfo = $dalUser->getUserLevelInfo($uid);
        if ($userInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_123';
            return array('resultVo' =>$resultVo);
        }
        
		$dalPlant = Dal_Island_Plant::getDefaultInstance();
		$isExists = $dalPlant->isPlantExistsById($ownerUid, $itemId);
		if (!$isExists) {
			$resultVo['content'] = 'serverWord_115';
			return array('resultVo' =>$resultVo);
		}

		if ($now - $ownerInfo['defense_card'] < 12*3600 && $ownerInfo['defense_card']) {
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

		$plantInfo = $dalPlant->getUserPlantById($itemId, $ownerUid);
		//check plant visitor
        if ( $plantInfo['wait_visitor_num'] <= 0 && $plantInfo['deposit'] <= 0 ) {
            $resultVo['content'] = 'serverWord_124';
            return array('resultVo' =>$resultVo);
        }

		if ( $plantInfo['event'] == 2 ) {
			$resultVo['content'] = 'serverWord_125';
            return array('resultVo' =>$resultVo);
        }

        $plantNb = Bll_Cache_Island::getPlantById($plantInfo['bid']);
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

        $cardInfo = Bll_Cache_Island::getCardById($cid);
        $bllDock = new Bll_Island_Dock();

		try {
			$this->_wdb->beginTransaction();

			//update count -1
			$dalCard->updateCardById($uid, $cid, -1);

			$dalPlant->updateUserPlant($itemId, array('uid' => $plantInfo['uid'], 'event' => 2, 'damage_card_time' => $now));

			$dalUser->updateUser($uid, array('exp' => $userInfo['exp'] + $cardInfo['add_exp']));

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();
			
            //clear user plant cache
            Bll_Cache_Island_User::clearCache('getUsingPlant', $plantInfo['uid']);
            Bll_Cache_Island_User::clearCache('getUserPlantListAll', $plantInfo['uid']);

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
		}
		catch (Exception $e) {
			$this->_wdb->roolback();
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		$minifeed = array('uid' => $ownerUid,
                          'template_id' => 3,
                          'actor' => $uid,
                          'target' => $ownerUid,
            		  	  'title' => array('plantName' => $plantNb['name']),
                          'type' => 2,
                          'create_time' => $now);
		Bll_Island_Feed::insertMiniFeed($minifeed);
		
        $resultVo['feed'] = Bll_Island_Activity::send('BUILDING_DAMAGE', $uid, array('building' => $plantNb['name']), $ownerUid);
        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }
        
        //get owner plant info
        $plantInfo = $dalPlant->getUsingPlantById($itemId, $ownerUid);
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
	public function defenseCard($uid, $cid)
	{
		$resultVo = array('status' => -1);
		$now = time();

	    $dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
	    $userInfo = $dalUser->getUserDockInfo($uid);
	    if (!$userInfo) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }
	    
	    if ($userInfo['level'] < 10) {
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
        $cardInfo = Bll_Cache_Island::getCardById($cid);
        $bllDock = new Bll_Island_Dock();

        $newUser = array('defense_card' => $now,
                         'exp' => $userInfo['exp'] + $cardInfo['add_exp']);

		try {
			$this->_wdb->beginTransaction();
			//update Card count
			$dalCard->updateCardById($uid, $cid, -1);

			$dalUser->updateUser($uid, $newUser);

            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array('0' => array('cid' => 26841, 'time' => 12*3600),
                                            '1' => array('cid' => 27141, 'time' => 6*3600 - ($now - $userInfo['insurance_card'])));
		}catch (Exception $e) {
			$this->_wdb->rollBack();
			info_log('[error_message]-[defenseCard]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
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
	public function checkCard($uid, $ownerUid, $cid)
	{
		$resultVo = array('status' => -1);
		$now = time();
	
        if ($ownerUid == $uid ) {
            $resultVo['content'] = 'serverWord_129';
            return array('resultVo' =>$resultVo);
        }
        
        $isFriend = Bll_Friend::isFriend($uid, $ownerUid);
        if ( !$isFriend ) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' =>$resultVo);
        }
        
	    $dalUser = Dal_Island_User::getDefaultInstance();
	    //get island user info
	    $ownerInfo = $dalUser->getUserDockInfo($ownerUid);
	    if (!$ownerInfo ) {
	    	$resultVo['content'] = 'serverWord_101';
			return array('resultVo' =>$resultVo);
	    }

	    if ($ownerInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_130';
            return array('resultVo' =>$resultVo);
	    }

		if ($now - $ownerInfo['defense_card'] < 12*3600 && $ownerInfo['defense_card']) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' =>$resultVo);
		}
	
        $userInfo = $dalUser->getUserLevelInfo($uid);
        if ($userInfo['level'] < 10) {
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
        
        $money = $money > $ownerInfo['coin'] ? $ownerInfo['coin'] : $money;

        $cardInfo = Bll_Cache_Island::getCardById($cid);
		try {
			$this->_wdb->beginTransaction();

			//update count -1
            $dalCard->updateCardById($uid, $cid, -1);

			$dalUser->updateUser($uid, array('exp' => $userInfo['exp'] + $cardInfo['add_exp']));
			$dalUser->updateUser($ownerUid, array('coin' => $ownerInfo['coin'] - $money));

			$bllDock = new Bll_Island_Dock();
            //check user level up
            $levelUp = $bllDock->checkLevelUp($uid);

			$this->_wdb->commit();

            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user today use card count
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_2', 1);
            //update user send gift count
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_2', 1);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            $resultVo['itemBoxChange'] = true;
            $resultVo['coinChange'] = -$money;
		}
		catch (Exception $e) {
			$this->_wdb->roolback();
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		//insert minifeed
		$minifeed = array('uid' => $ownerUid,
                          'template_id' => 6,
                          'actor' => $uid,
                          'target' => $ownerUid,
                          'title' => array('money' => $money),
                          'type' => 2,
                          'create_time' => $now);
		Bll_Island_Feed::insertMiniFeed($minifeed);

        //send activity
        if ( $levelUp['levelUp'] ) {
            $resultVo['feed'] = Bll_Island_Activity::send('USER_LEVEL_UP', $uid, array('level' => $userInfo['level'] + 1));
        }
        if ( $levelUp['islandLevelUp'] ) {
            //get next level island info
            $nextLevelIsland = Bll_Cache_Island::getIslandLevelInfo($levelUp['newIslandLevel']);
            //update user achievement island visitor count
            $dalMongoAchievement->updateUserAchievement($uid, array('num_15' => $nextLevelIsland['visitor_count']));
        }

		return array('resultVo' =>$resultVo);
	}
}