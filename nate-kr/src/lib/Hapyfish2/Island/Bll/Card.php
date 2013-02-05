<?php
require_once(CONFIG_DIR . '/language.php');
class Hapyfish2_Island_Bll_Card
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
		} else if ($id == 265 || $id == 266) {
			$resultVo = self::delayCard($uid, $cid, $itemId);
		} else if ($id == 267) {
			$resultVo = self::damageCard($uid, $ownerUid, $cid, $itemId);
		} else if ($id == 269) {
			$resultVo = self::plunderCard($uid, $ownerUid, $cid);
		} else if ($id == 268) {
			$resultVo = self::defenseCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} else if ($id == 270) {
			$resultVo = self::checkCard($uid, $ownerUid, $cid);
			if(isset($resultVo['resultVo']['coinChange'])){
    			$resultVo['coinChange'] = $resultVo['resultVo']['coinChange'];
    			unset($resultVo['resultVo']['coinChange']);
			}
		} else if ($id == 566 || $id == 567 || $id == 568 || $id == 569) {
			//plant upgrade card
			$resultVo = self::upgradeCard($uid, $cid, $itemId);
		} else if ($id == 674) {
			$resultVo = self::onekeyCard($uid, $ownerUid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} else if ($id == 675) {
			$resultVo = self::superDefenseCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		} else if ($id == 748) {
			$resultVo = self::doubleExpCard($uid, $cid);
			if(isset($resultVo['resultVo']['cardStates'])){
                $resultVo['cardStates'] = $resultVo['resultVo']['cardStates'];
                unset($resultVo['resultVo']['cardStates']);
			}
		}

		//get user item info
		$itemBox = Hapyfish2_Island_Bll_Warehouse::loadItems($uid);
		$resultVo['items'] = $itemBox;

		return $resultVo;
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
			return array('resultVo' => $resultVo);
		}

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$dockPositionInfo = Hapyfish2_Island_HFC_Dock::getUserDockPosition($uid, $pid);
		if (!$dockPositionInfo) {
			$resultVo['content'] = 'serverWord_108';
			return array('resultVo' =>$resultVo);
		}

		if ($cid == 26241 && $dockPositionInfo['is_usecard_one'] == 1) {
			$resultVo['content'] = 'serverWord_109';
			return array('resultVo' =>$resultVo);
		}

		$now = time();
		$time = $now - $dockPositionInfo['receive_time'] - $dockPositionInfo['wait_time'] + $dockPositionInfo['speedup_time'];

		$isUseCardOne = 0;
		//is_usecard_one = 1 : used card 1
		if ($dockPositionInfo['is_usecard_one'] == 1) {
			$isUseCardOne = 1;
		}

		$id = substr($cid, 0, -2);

		if ($id == 263) {
			//card 2 speed 50 minute
			$speedTime = 3000;
		} elseif ($id == 264) {
			//card 2 speed 2 hour 2.5
			$speedTime = 9000;
		} else {//card 1 speed 10 minute
			$speedTime = 600;
			$isUseCardOne = 1;
		}

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		try {
			$dockPositionInfo['speedup'] = 1;
			$dockPositionInfo['is_usecard_one'] = $isUseCardOne;
			$dockPositionInfo['speedup_time'] += $speedTime;

			Hapyfish2_Island_HFC_Dock::updateUserDockPosition($uid, $pid, $dockPositionInfo);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;

		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		/*
		try {
			//get user boat arrive time
            $arriveTime = Hapyfish_Island_Bll_Dock::getArriveTime($uid);
            $dalUser = Dal_Island_User::getDefaultInstance();
			//update user exp
			$dalUser->updateUser($uid, array('boat_arrive_time' => $arriveTime));
		} catch (Exception $e) {

		}*/

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);

		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
            if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {

		}

		$dockInfo = Hapyfish2_Island_Bll_Dock::getDockShipInfo($uid, $pid, $uid, $now);

		//get user item info
        $itemBox = Hapyfish2_Island_Bll_Warehouse::loadItems($uid);

		return array('resultVo' =>$resultVo, 'boatPositionVo' => $dockInfo, 'items' => $itemBox);
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

		if (empty($cid)) {
			return array('resultVo' => $resultVo);
		}

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' =>$resultVo);
		}

		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$insuranceCardTime = $userCardStatus['insurance'];
		$defenseCardTime = $userCardStatus['defense'];
		$doubleexpCardTime = $userCardStatus['doubleexp'];
		$onekeyCardTime = $userCardStatus['onekey'];

		$now = time();
		if ($now < $insuranceCardTime) {
			$resultVo['content'] = 'serverWord_106';
			return array('resultVo' => $resultVo);
		}

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		try {
			$userCardStatus['insurance'] = $now + 6*3600;
			Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array(
            	'0' => array('cid' => 27141, 'time' => 6*3600),
				'1' => array('cid' => 26841, 'time' => ($defenseCardTime - $now)),
            	'2' => array('cid' => 74841, 'time' => ($doubleexpCardTime - $now)),
            	'3' => array('cid' => 67441, 'time' => ($onekeyCardTime - $now))
            );
		}catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
		    if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {

		}

        return array('resultVo' =>$resultVo);
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
            return array('resultVo' => $resultVo);
        }

        $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		//check owner level
	    $ownerInfo = Hapyfish2_Island_HFC_User::getUser($ownerUid, array('coin' => 1 , 'level' => 1));
	    if ($ownerInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_112';
            return array('resultVo' => $resultVo);
	    }

	    $now = time();
	    $ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
	    $defenseCardTime = $ownerCardStatus['defense'];
		if ($now < $defenseCardTime) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' => $resultVo);
		}

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_114';
            return array('resultVo' => $resultVo);
        }

        //allow 3 times
        $plunderCountInfo = Hapyfish2_Island_Cache_Counter::getPlunderCount($ownerUid);
        if ($plunderCountInfo['count'] <= 0) {
        	$resultVo['content'] = 'serverWord_1001';
        	return array('resultVo' => $resultVo);
        }

		$ownerCoin = $ownerInfo['coin'];
		$updateMoney = floor($ownerCoin * 0.01);

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		$plunderCountInfo['count'] = $plunderCountInfo['count'] - 1;
		Hapyfish2_Island_Cache_Counter::updatePlunderCount($ownerUid, $plunderCountInfo);

		try {
			Hapyfish2_Island_HFC_User::incUserExpAndCoin($uid, $cardInfo['add_exp'], $updateMoney);

			$ok = Hapyfish2_Island_HFC_User::decUserCoin($ownerUid, $updateMoney);
			if ($ok) {
				//add log
				$summary = str_replace('{*name*}', $cardInfo['name'], LANG_PLATFORM_EXT_TXT_101);
				Hapyfish2_Island_Bll_ConsumeLog::coin($ownerUid, $updateMoney, $summary, $now);
			}

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['coinChange'] = $updateMoney;
            $resultVo['itemBoxChange'] = true;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
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

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		} catch (Exception $e) {
		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

		return array('resultVo' => $resultVo);
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
        $itemId = substr($itemId, 0, -2);

        if ($itemType != 3) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		$plantInfo = Hapyfish2_Island_HFC_Plant::getOne($uid, $itemId, 1);

		if (!$plantInfo) {
			return array('resultVo' => $resultVo);
		}

		if ($plantInfo['wait_visitor_num'] <= 0 && $plantInfo['start_deposit'] <= 0 || $plantInfo['deposit'] <= 0) {
            $resultVo['content'] = 'serverWord_117';
            return array('resultVo' => $resultVo);
        }

		if ($now - $plantInfo['start_pay_time'] - $plantInfo['pay_time'] - $plantInfo['delay_time'] > 0) {
            $resultVo['content'] = 'serverWord_118';
            return array('resultVo' =>$resultVo);
        }

		if ($plantInfo['event'] != 0) {
            if ($plantInfo['event'] == 2) {
                $resultVo['content'] = 'serverWord_121';
                return array('resultVo' =>$resultVo);
            }

			if ($now - $plantInfo['start_pay_time'] >= $plantInfo['pay_time'] * 0.6) {
				$resultVo['content'] = 'serverWord_121';
				return array('resultVo' => $resultVo);
	        }
        }

        $delaryTime = ($cid == 26541) ? 10800 : 21600;
		$plantInfo['delay_time'] = $plantInfo['delay_time'] + $delaryTime;

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		try {
			Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $plantInfo);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {
		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

        $clientPlantInfo = Hapyfish2_Island_Bll_Plant::handlerPlant($plantInfo, $now);

		return array('resultVo' => $resultVo, 'buildingVo' => $clientPlantInfo);
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

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_128';
            return array('resultVo' => $resultVo);
        }

       	$now = time();

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$insuranceCardTime = $userCardStatus['insurance'];
		$doubleexpCardTime = $userCardStatus['doubleexp'];
		$onekeyCardTime = $userCardStatus['onekey'];
		try {
			$userCardStatus['defense'] = $now + 12*3600;
			Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array(
            	'0' => array('cid' => 26841, 'time' => 12*3600),
				'1' => array('cid' => 27141, 'time' => ($insuranceCardTime - $now)),
            	'2' => array('cid' => 74841, 'time' => ($doubleexpCardTime - $now)),
            	'3' => array('cid' => 67441, 'time' => ($onekeyCardTime - $now))
            );
		}catch (Exception $e) {
			info_log('[error_message]-[defenseCard]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

        return array('resultVo' => $resultVo);
	}

	/**
	 * use super defense card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @return array $resultVo
	 */
	public static function superDefenseCard($uid, $cid)
	{
		$resultVo = array('status' => -1);

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_128';
            return array('resultVo' => $resultVo);
        }

       	$now = time();

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$insuranceCardTime = $userCardStatus['insurance'];
		$doubleexpCardTime = $userCardStatus['doubleexp'];
		$onekeyCardTime = $userCardStatus['onekey'];

		try {
			$userCardStatus['defense'] = $now + 30*3600;
			Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array(
            	'0' => array('cid' => 26841, 'time' => 30*3600),
				'1' => array('cid' => 27141, 'time' => ($insuranceCardTime - $now)),
            	'2' => array('cid' => 74841, 'time' => ($doubleexpCardTime - $now)),
            	'3' => array('cid' => 67441, 'time' => ($onekeyCardTime - $now))
            );
		}catch (Exception $e) {
			info_log('[error_message]-[defenseCard]:'.$e->getMessage(), 'transaction');
            $resultVo['content'] = 'serverWord_110';
            return array('resultVo' =>$resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
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
            return array('resultVo' => $resultVo);
        }

	    $isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		//check owner level
	    $ownerInfo = Hapyfish2_Island_HFC_User::getUser($ownerUid, array('coin' => 1 , 'level' => 1));
	    if ($ownerInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_130';
            return array('resultVo' => $resultVo);
	    }

		$now = time();
	    $ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
	    $defenseCardTime = $ownerCardStatus['defense'];
		if ($now < $defenseCardTime) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' => $resultVo);
		}

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_131';
            return array('resultVo' => $resultVo);
        }

        $randerNum = rand(1, 3);
        $money = 50;
        if ($randerNum == 2) {
            $money = 100;
        } elseif ($randerNum == 3) {
            $money = 500;
        }

        $OwnerCoin = $ownerInfo['coin'];
        $money = $money > $OwnerCoin ? $OwnerCoin : $money;

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		try {
			$ok = Hapyfish2_Island_HFC_User::decUserCoin($ownerUid, $money);
			if ($ok) {
				//add log
				$summary = str_replace('{*name*}', $cardInfo['name'], LANG_PLATFORM_EXT_TXT_101);
				Hapyfish2_Island_Bll_ConsumeLog::coin($ownerUid, $money, $summary, $now);
			}

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['coinChange'] = -$money;
		}
		catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
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

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

		return array('resultVo' => $resultVo);
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
            return array('resultVo' => $resultVo);
        }

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }

		$isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $ownerUid);
        if (!$isFriend) {
            $resultVo['content'] = 'serverWord_120';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

	    //allow 10 times
        $damageCountInfo = Hapyfish2_Island_Cache_Counter::getDamageCount($uid);
        if ($damageCountInfo['count'] <= 0) {
        	$resultVo['content'] = 'serverWord_192';
        	return array('resultVo' => $resultVo);
        }

		//check owner level
	    $ownerLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($ownerUid);
	    if ($ownerLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_122';
            return array('resultVo' => $resultVo);
	    }

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 10) {
            $resultVo['content'] = 'serverWord_123';
            return array('resultVo' => $resultVo);
        }

		$now = time();
	    $ownerCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($ownerUid);
	    $defenseCardTime = $ownerCardStatus['defense'];
		if ($now < $defenseCardTime) {
			$resultVo['content'] = 'serverWord_113';
			return array('resultVo' => $resultVo);
		}

		$userPlant = Hapyfish2_Island_HFC_Plant::getOne($ownerUid, $itemId, 1);

		//check plant visitor
        if ($userPlant['wait_visitor_num'] <= 0 && $userPlant['start_deposit'] <= 0 || $userPlant['deposit'] <= 0) {
            $resultVo['content'] = 'serverWord_124';
            return array('resultVo' => $resultVo);
        }

		if ($userPlant['event'] == 2) {
			$resultVo['content'] = 'serverWord_125';
            return array('resultVo' => $resultVo);
        }

		if ($now - $userPlant['start_pay_time'] - $userPlant['pay_time'] - $userPlant['delay_time'] > 0) {
            $resultVo['content'] = 'serverWord_126';
            return array('resultVo' => $resultVo);
        }

		if ($userPlant['event'] != 0) {
            if ($now - $userPlant['start_pay_time'] - $userPlant['delay_time'] >= $userPlant['pay_time']*0.6) {
                $resultVo['content'] = 'serverWord_127';
                return array('resultVo' => $resultVo);
            }
        }

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' => $resultVo);
		}

		try {
        	$userPlant['event'] = 2;
        	Hapyfish2_Island_HFC_Plant::updateOne($ownerUid, $itemId, $userPlant);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];
            $resultVo['itemBoxChange'] = true;

			$damageCountInfo['count'] = $damageCountInfo['count'] - 1;
			Hapyfish2_Island_Cache_Counter::updateDamageCount($uid, $damageCountInfo);
		}
		catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
			$minifeed = array('uid' => $ownerUid,
	                          'template_id' => 3,
	                          'actor' => $uid,
	                          'target' => $ownerUid,
	            		  	  'title' => array('plantName' => $plantInfo['name']),
	                          'type' => 2,
	                          'create_time' => $now);
			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);

		}catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

		$clientPlantInfo = Hapyfish2_Island_Bll_Plant::handlerPlant($userPlant, $now);

		return array('resultVo' => $resultVo, 'buildingVo' => $clientPlantInfo);
	}

	/**
	 * use plant upgrade card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @param integer $itemId
	 * @return array $resultVo
	 */
	public static function upgradeCard($uid, $cid, $itemId)
	{
		$resultVo = array('status' => -1);
		$now = time();

		$itemType = substr($itemId, -2, 1);
        $itemId = substr($itemId, 0, -2);
        if ($itemType != 3) {
            $resultVo['content'] = 'serverWord_115';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_172';
			return array('resultVo' => $resultVo);
		}

		$userPlant = Hapyfish2_Island_HFC_Plant::getOne($uid, $itemId, 1);
		if (!$userPlant) {
			return array('resultVo' => $resultVo);
		}

		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($userPlant['cid']);
        if (!$plantInfo['next_level_cid']) {
        	$resultVo['content'] = 'serverWord_171';
            return array('resultVo' => $resultVo);
        }

        if ($cardInfo['plant_level'] != $plantInfo['level']) {
            $resultVo['content'] = 'serverWord_168';
            return array('resultVo' => $resultVo);
        }

        //get next level plant info
        $nextLevelPlantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($plantInfo['next_level_cid']);
        if (!$nextLevelPlantInfo) {
        	$resultVo['content'] = 'serverWord_171';
            return array('resultVo' => $resultVo);
        }

        //get praise change
        $praiseChange = $nextLevelPlantInfo['add_praise'] - $plantInfo['add_praise'];

        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
	    if ($userLevelInfo === null) {
        	return array('resultVo' => $resultVo);
        }

	    //check need level
        if ($nextLevelPlantInfo['need_level'] > $userLevelInfo['level'] ) {
            $resultVo['content'] = 'serverWord_136';
            $result = array('resultVo' => $resultVo);
            return $result;
        }

	    $userIslandInfo = Hapyfish2_Island_HFC_User::getUserIsland($uid);
	    if ($userIslandInfo === null) {
	    	return array('resultVo' => $resultVo);
	    }

        //check need praise
        if ($nextLevelPlantInfo['need_praise'] > $userIslandInfo['praise']) {
            $resultVo['content'] = 'serverWord_169';
            return array('resultVo' => $resultVo);
        }

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' => $resultVo);
		}

		try {
        	$userPlant['level'] += 1;
        	$userPlant['cid'] = $nextLevelPlantInfo['cid'];
        	$ok = Hapyfish2_Island_HFC_Plant::updateOne($uid, $itemId, $userPlant, true);
        	if (!$ok) {
				$resultVo['content'] = 'serverWord_110';
            	return array('resultVo' => $resultVo);
        	}

        	Hapyfish2_Island_Cache_Plant::reloadAllByItemKind($uid);

        	$userIslandInfo['praise'] += $praiseChange;
        	Hapyfish2_Island_HFC_User::updateUserIsland($uid, $userIslandInfo);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = 0;
            $resultVo['praiseChange'] = $praiseChange;
            $resultVo['itemBoxChange'] = true;
		} catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		$clientPlantInfo = Hapyfish2_Island_Bll_Plant::handlerPlant($userPlant, $now);

		return array('resultVo' => $resultVo, 'buildingVo' => $clientPlantInfo);
	}

	public static function onekeyCard($uid, $ownerUid, $cid)
	{
		$resultVo = array('status' => -1);

        if ($ownerUid != $uid ) {
            $resultVo['content'] = 'serverWord_174';
            return array('resultVo' =>$resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

       	$now = time();

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' =>$resultVo);
		}

		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$insuranceCardTime = $userCardStatus['insurance'];
		$defenseCardTime = $userCardStatus['defense'];
		$doubleexpCardTime = $userCardStatus['doubleexp'];
		$onekeyCardTime = $userCardStatus['onekey'];
		if ($onekeyCardTime < $now) {
			$onekeyCardTime = $now + 24*3600;
		} else {
			$onekeyCardTime = $onekeyCardTime + 24*3600;
		}

		try {
			$userCardStatus['onekey'] = $onekeyCardTime;
			Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array(
            	'0' => array('cid' => 26841, 'time' => ($defenseCardTime - $now)),
				'1' => array('cid' => 27141, 'time' => ($insuranceCardTime - $now)),
            	'2' => array('cid' => 74841, 'time' => ($doubleexpCardTime - $now)),
            	'3' => array('cid' => 67441, 'time' => ($onekeyCardTime - $now))
            );
		} catch (Exception $e) {
            $resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		try {
			//check user level up
        	$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $resultVo['levelUp'] = $levelUp['levelUp'];
            $resultVo['islandLevelUp'] = $levelUp['islandLevelUp'];
			if ($levelUp['feed']) {
            	$resultVo['feed'] = $levelUp['feed'];
            }
		} catch (Exception $e) {
		}

        return array('resultVo' => $resultVo);
	}

	/**
	 * use double exp card
	 *
	 * @param integer $uid
	 * @param integer $cid
	 * @return array $resultVo
	 */
	public static function doubleExpCard($uid, $cid)
	{
		$resultVo = array('status' => -1);

		//check user level
        $userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
        if ($userLevelInfo['level'] < 5) {
            $resultVo['content'] = 'serverWord_179';
            return array('resultVo' => $resultVo);
        }

		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return array('resultVo' => $resultVo);
		}

		$userCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);
		if (!isset($userCard[$cid]) || $userCard[$cid]['count'] < 1) {
			$resultVo['content'] = 'serverWord_105';
			return array('resultVo' => $resultVo);
		}

		//check user double exp card time
		$userCardStatus = Hapyfish2_Island_HFC_User::getCardStatus($uid);
		$doubleexpCardTime = $userCardStatus['doubleexp'];

		$now = time();
		if ($doubleexpCardTime - $now > 0) {
			$resultVo['content'] = 'serverWord_180';
			return array('resultVo' => $resultVo);
		}

		$result = Hapyfish2_Island_HFC_Card::useUserCard($uid, $cid, 1, $userCard);
		if (!$result) {
			$resultVo['content'] = 'serverWord_110';
			return array('resultVo' => $resultVo);
		}

		$insuranceCardTime = $userCardStatus['insurance'];
		$defenseCardTime = $userCardStatus['defense'];
		$onekeyCardTime = $userCardStatus['onekey'];

		try {
			$userCardStatus['doubleexp'] = $now + 2*3600;
			Hapyfish2_Island_HFC_User::updateCardStatus($uid, $userCardStatus);

			Hapyfish2_Island_HFC_User::incUserExp($uid, $cardInfo['add_exp']);

			$resultVo['status'] = 1;
            $resultVo['expChange'] = $cardInfo['add_exp'];

            $resultVo['itemBoxChange'] = true;
            $resultVo['cardStates'] = array(
            	'0' => array('cid' => 26841, 'time' => ($defenseCardTime - $now)),
				'1' => array('cid' => 27141, 'time' => ($insuranceCardTime - $now)),
            	'2' => array('cid' => 74841, 'time' => ($doubleexpCardTime - $now)),
            	'3' => array('cid' => 67441, 'time' => ($onekeyCardTime - $now))
            );
		}
		catch (Exception $e) {
			$resultVo['content'] = 'serverWord_110';
            return array('resultVo' => $resultVo);
		}

		try {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_2', 1);

			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_2', 1);
		} catch (Exception $e) {

		}

		return array('resultVo' => $resultVo);
	}

}