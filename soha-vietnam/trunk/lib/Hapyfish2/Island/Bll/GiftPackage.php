<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Bll_GiftPackage
{
	/**
	 * 获取packageID
	 */
 	public static function getNewPackageId($uid)
    {
        try {
    		$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
    		return $dalUserSequence->get($uid, 'd', 1);
    	} catch (Exception $e) {
    		info_log("Exception","gift");
    	}

    	return 0;
    }
	public static function addBackGround($uid, $item_id, $item_num, $time, $itemType)
	{
		$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($item_id);
		if (!$bgInfo) {
			return false;
		}

		$newBackground = array(
			'uid' => $uid,
			'bgid' => $item_id,
			'item_type' => $itemType,
			'buy_time' => $time
		);

		$count = 0;
		for($i = 0; $i < $item_num; $i++) {
			$ok = Hapyfish2_Island_Cache_Background::addNewBackground($uid, $newBackground);
			if ($ok) {
				$count++;
			}
		}

		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function addCard($uid, $item_id, $item_num, $time, $type)
	{
		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($item_id);
		if (!$cardInfo) {
			return false;
		}

		$ok = Hapyfish2_Island_HFC_Card::addUserCard($uid, $item_id, $item_num);

		return $ok;
	}

	/**
	 * add gift Building
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addBuilding($uid, $item_id, $item_num, $time, $itemType)
	{
		$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($item_id);
		if (!$buildingInfo) {
			return false;
		}

		$newBuilding = array(
			'uid' => $uid,
			'cid' => $item_id,
			'item_type' => $itemType,
			'status' => 0,
			'buy_time' => $time
		);

        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
	    $userCurrentIsland = $userVO['current_island'];
	    
		$count = 0;
		for($i = 0; $i < $item_num; $i++) {
			$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding, $userCurrentIsland);
			if ($ok) {
				$count++;
			}
		}

		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function addPlant($uid, $item_id, $item_num, $time, $itemType)
	{
		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($item_id);
		if (!$plantInfo) {
			return false;
		}

		$newPlant = array(
			'uid' => $uid,
			'cid' => $item_id,
			'item_type' => $itemType,
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => $time
		);

        $userVO = Hapyfish2_Island_HFC_User::getUserVO($uid);
	    $userCurrentIsland = $userVO['current_island'];
		$count = 0;
		for($i = 0; $i < $item_num; $i++) {
			$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant, $userCurrentIsland);
			if ($ok) {
				$count++;
			}
		}

		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * type add gift
	 * @param integer $actorUid
	 * @param integer $fid
	 * @param integer $gid
	 * @return boolean $result
	 */
	public static function addGift($uid, $item_id, $item_num)
	{
		$result = false;
		$type = substr($item_id, -2);
		$itemType = substr($item_id, -2, 1);
		$time = time();

		//itemType,1x->background,2x->building,3x->plant,4x->card
		if ($itemType == 1) {
            $result = self::addBackground($uid, $item_id, $item_num, $time, $type);
        } else if ($itemType == 2){
            $result = self::addBuilding($uid, $item_id, $item_num, $time, $type);
        } else if ($itemType == 3) {
        	$result = self::addPlant($uid, $item_id, $item_num, $time, $type);
        } else if ($itemType == 4) {
            $result = self::addCard($uid, $item_id, $item_num, $time, $type);
        }

        return $result;
	}

	/**
	 * get giftlist
	 *
	 * @param integer uid
	 * @return array
	 */
	public static function getList($uid)
	{
		$result = array('status' => -1);

		//read giftVOLists
		$dalGiftPackage = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
		$giftVOLists = $dalGiftPackage->getList($uid);

		$giftList = array();
		$giftVo = array();

		$key = 'i:u:e:getgiftpackageList:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $lastReadTime = $cache->get($key);

        if(empty($lastReadTime))
        {
        	$lastReadTime = time() - 3600;
        }

		foreach ( $giftVOLists as $giftVOList ) {
			$giftVo['type'] = $giftVOList['gift_type'];
			$giftVo['sendTime'] = $giftVOList['send_time'];
			if ($giftVOList['send_time'] > $lastReadTime) {
				$giftVo['newFlag'] = 1;
			} else {
				$giftVo['newFlag'] = 0;
			}

			switch ($giftVOList['gift_type']) {
				case 1:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_27;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				case 2:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_28;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				case 7:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_29;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				case 8:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_30;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				case 9:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_31;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				case 10:
					$giftVo['id'] = $giftVOList['pid'];
					$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_32;
					$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
				break;
				default:
					$giftVo['sendUserId'] = $giftVOList['from_uid'];
					$giftVo['id'] = $giftVOList['pid'];

					$userInfo = Hapyfish2_Platform_Bll_User::getUser($giftVOList['from_uid']);
					$giftVo['sendUserName'] = $userInfo['name'];
					$giftVo['sendReason'] = $userInfo['name'].LANG_PLATFORM_BASE_TXT_33;
			}

			$giftVo['itemList'] = array();
			$giftList[] = $giftVo;
		}
		$cache->set($key,time());

		$result['status'] = 1;
		$resultVo['result'] = $result;
		$resultVo['giftVOList'] = $giftList;

		return $resultVo;
	}

	/**
	 * open one gift package
	 *
	 * @param int uid
	 * @param int pid
	 * @return array
     */
	public static function openOne($uid, $pid)
	{
		$result = array('status' => -1);

		if(empty($pid) ) {
			$result['content'] = LANG_PLATFORM_BASE_TXT_34;
            return $result;
		}

		//get gift
		try {
			$dalGiftPackage = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
			$gift = $dalGiftPackage->getOne($uid, $pid);
		} catch (Exception $e) {
			$result['content'] = LANG_PLATFORM_BASE_TXT_34;
			return $result;
		}

		if (empty($gift)) {
			$result['content'] = LANG_PLATFORM_BASE_TXT_34;
            return $result;
		}

		$giftVo = array();
		$result = array('status' => 1);

		$giftVo['type'] = $gift['gift_type'];
		$giftVo['sendTime'] = $gift['send_time'];

		switch ($gift['gift_type']) {
			case 1:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_27;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			case 2:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_28;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			case 7:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_29;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			case 8:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_30;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			case 9:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_31;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			case 10:
				$giftVo['id'] = $gift['pid'];
				$giftVo['sendReason'] = LANG_PLATFORM_BASE_TXT_32;
				$giftVo['sendUserName'] = LANG_PLATFORM_BASE_TXT_15;
			break;
			default:
				$giftVo['sendUserId'] = $gift['from_uid'];
				$giftVo['id'] = $gift['pid'];

				$userInfo = Hapyfish2_Platform_Bll_User::getUser($gift['from_uid']);
				$giftVo['sendUserName'] = $userInfo['name'];
				$giftVo['sendReason'] = $userInfo['name'].LANG_PLATFORM_BASE_TXT_33;
		}

		$itemList = array();
		if ($gift['gold'] > 0) {
			$itemList[] = array('gem' => $gift['gold']);
			$result['goldChange'] = $gift['gold'];
			Hapyfish2_Island_Bll_Gold::add($uid, array('gold' => $gift['gold']));
		}
		
		if ($gift['coin'] > 0) {
			$itemList[] = array('coin' => $gift['coin']);
			$result['coinChange'] = $gift['coin'];
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $gift['coin']);
		}
		
		//海星添加
		if ($gift['starfish'] > 0) {
			$itemList[] = array('starfish' => $gift['starfish']);
			Hapyfish2_Island_HFC_User::incUserStarFish($uid, $gift['starfish']);
		}
		
		if ($gift['exp'] > 0) {
			$itemList[] = array('exp' => $gift['exp']);
			$result['expChange'] = $gift['exp'];

			Hapyfish2_Island_HFC_User::incUserExp($uid, $gift['exp']);

			$levelUp = Hapyfish2_Island_Bll_User::checkLevelUp($uid);
            $result['levelUp'] = $levelUp['levelUp'];
            $result['islandLevelUp'] = $levelUp['islandLevelUp'];
        	if ($levelUp['feed']) {
            	$result['feed'] = $levelUp['feed'];
            }
		}
		
		if(!empty($gift['item_data'])) {
			$items = explode(',', $gift['item_data']);
			foreach ($items as $v) {
				$item = explode('*', $v);
				self::addGift($uid, $item[0], $item[1]);
				$itemList[] = array('itemId' => $item[0], 'itemNum' => $item[1]);
			}
		}

		//delete gift
		try {
			$dalGiftPackage->delete($uid, $pid);
		} catch (Exception $e) {

		}
		
		//统计阶段性礼物的领取信息
		if ( $gift['gift_type'] == 9 ) {
			try {
				//report log
				$logger = Hapyfish2_Util_Log::getInstance();
				$logger->report('3003', array($uid, $gift['coin']));
			} catch (Exception $e) {
			}
		}

		$giftVo['itemList'] = $itemList;

		$resultVo = array('giftVo' => $giftVo,
						  'result' => $result);

		return $resultVo;
	}

	public static function getNum($uid)
	{
		try {
			$dalGiftPackage = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
			return $dalGiftPackage->getNum($uid);
		} catch (Exception $e) {
		}

		return 0;
	}

	/**
	 * insert new user gift (time)
	 * @return : boolean
	 * */
	public static function getNewUserGift($giftInfo)
	{

		if( !empty($giftInfo['item_data']) ) {
			$gold = isset($giftInfo['gold']) ? $giftInfo['gold'] : 0;
			$pid = Hapyfish2_Island_Bll_GiftPackage::getNewPackageId($giftInfo['uid']);
	        $info = array('to_uid' => $giftInfo['uid'],
	        			  'pid'	   => $pid ,
	        			  'gold'   => $gold,
	        	  		  'gift_type' => 10,
	        	          'send_time' => time(),
	        	  		  'item_data' => $giftInfo['item_data']);


	        $dalGift = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();

	        $ret = $dalGift->insert($giftInfo['uid'], $info);

			$minifeed = array('uid' => $info['to_uid'],
	                          'template_id' => 105,
	                          'actor' => $info['to_uid'],
	                          'target' => $info['to_uid'],
	                          'title' => array('type' => $giftInfo['type']),
	                          'type' => 3,
	                          'create_time' => time());

           	 Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);

		}
        return true;
	}

	/**
	 * get Level Gift
	 *
	 * @param inter uid
	 * @return array
     */
	public static function getLevelGift($uid)
	{
		$result = array('status' => -1);

		//get user info
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);

		$resultVoList = array();

		$userLevel = $userLevelInfo['level'] % 5;

		$giftPackId = 0;
		if( $userLevel == 0 ) {
        	$result = array('status' => 1);

			//get level gift
			$levelGift = Hapyfish2_Island_Cache_BasicInfo::getStepGiftByUserLevel($userLevelInfo['level']);

			$itemId = explode(",", $levelGift['item_id']);
			$itemNum = explode(",", $levelGift['item_num']);
			$itemData = array();
			for($i = 0; $i < sizeof($itemId); $i++) {
				$itemData[] .= $itemId[$i] . "*" . $itemNum[$i];
			}
			$itemDataStr = join(",", $itemData);

			$pack = array('to_uid' => $uid,
						  'coin' => $levelGift['coin'],
						  'item_data' => $itemDataStr);

			//get gift id
			try {
				$dalGift = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
				$giftPackId = $dalGift->getGiftId($uid, $pack);
			} catch (Exception $e) {
				info_log('[error_message]-[UserStepGift]:'.$e->getMessage(), 'UserStepGift-Open');
			}
		}
		$resultVoList['result'] = $result;
		$resultVoList['giftPackId'] = $giftPackId;

		return $resultVoList;
	}

	/**
	 * send gift
	 * @param array $g
	 * @param array $fids (friend uid)
	 * @return boolean
	 */
	public static function sendGift($gid, $uid, $fids, $countInfo, $type)
	{
	    if (empty($fids)) {
			return 0;
	    }

	    if (!in_array($type, array(1, 2, 3))) {
	    	return 0;
	    }

	    $time = time();
	    $count = 0;
		foreach ($fids as $fid) {
			$itemDataStr = $gid . "*" . '1';
			$pid = Hapyfish2_Island_Bll_GiftPackage::getNewPackageId($fid);

			if($type == 1) {
				$info = array('to_uid' => $fid,
							'from_uid' => $uid,
							'gift_type' => 3,
							'pid' => $pid,
							'item_data'	=> $itemDataStr,
							'send_time' => $time);
			}
			else if($type == 2) {
				$info = array('to_uid' => $fid,
							'from_uid' => $uid,
							'gift_type' => 4,
							'pid' => $pid,
							'item_data'	=> $itemDataStr,
							'send_time' => $time);
			} else {
				$info = array('to_uid' => $fid,
							'from_uid' => $uid,
							'gift_type' => 5,
							'pid' => $pid,
							'gold'	=> $gid,
							'send_time' => $time);
			}

			//insert gift
        	$dalGift = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
            $ok = $dalGift->insert($fid, $info);

            if ($ok) {
            	$count++;
				$feed = array(
					'uid' => $fid,
					'template_id' => 9,
					'actor' => $uid,
					'target' => $fid,
					'type' => 3,
					'title' => '',
					'create_time' => $time
				);
				Hapyfish2_Island_Bll_Feed::insertMiniFeed($feed);
			}

			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_4', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_4', 1);

			//task id 3021,task type 4
			Hapyfish2_Island_Bll_Task::checkTask($uid, 3021);
		}

		if ($type == 1) {
			$countInfo['count'] -= $count;
			if($count > 0) {
				Hapyfish2_Island_Cache_Counter::updateSendGiftCount($uid, $countInfo);
			}
		}

		return $count;
	}

	/**
	 * insert send gift log
	 */
	public static function insertGiftLog($uid, $gid, $fids, $gtype)
	{
		$now = time();

		if(!$fids) {
			return ;
		}

		foreach($fids as $fid) {
			$infoPost = array('to_uid' => $fid,
								'gid' => $gid,
								'gtype' => $gtype,
								'create_time' => $now);

			Hapyfish2_Island_Cache_GiftPackage::insertPostGiftLog($uid, $infoPost);

			$infoGet = array('from_uid' => $uid,
								  'gid' => $gid,
								  'gtype' => $gtype,
								  'create_time' => $now);

			//update user gift log status
			Hapyfish2_Island_Cache_GiftPackage::insertGetGiftLog($fid, $infoGet);
		}
	}

	/**
	 * get user has gift log
	 */
	public static function getGiftLog($uid)
	{
		$gifts = Hapyfish2_Island_Cache_GiftPackage::getGiftLogData($uid);
		if (!$gifts) {
			return array();
		}

		$giftList = array();
		$giftListNew = array();
		foreach( $gifts as $gift ) {
			if($gift['gtype'] != 3) {
				$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gift['gid']);
				$gift_name = $giftInfo['name'];
			} else {
				$gift_name = $gift['gid'];
			}

			$giftList['from_uid'] = $gift['from_uid'];
			$giftList['gift_name'] = $gift_name;
			$giftList['gtype'] = $gift['gtype'];
			$giftList['create_time'] = $gift['create_time'];

			//get island name
			$userInfo = Hapyfish2_Platform_Bll_User::getUser($giftList['from_uid']);

			$giftListNew[] = array('from_name' => $userInfo['name'],
									'name' => $giftList['gift_name'],
									'gtype' => $giftList['gtype'],
									'create_time' => $giftList['create_time']);
		}

		return $giftListNew;
	}

	/**
	 * get user post gift log
	 */
	public static function postGiftLog($uid)
	{
		$gifts = Hapyfish2_Island_Cache_GiftPackage::postGiftLogData($uid);
		if (!$gifts) {
			return array();
		}

		$giftList = array();
		$giftListNew = array();
		foreach( $gifts as $gift ) {
			if($gift['gtype'] != 3) {
				$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getGiftInfo($gift['gid']);
				$gift_name = $giftInfo['name'];
				$gift_price = $giftInfo['price'];
			} else {
				$gift_name = $gift['gid'] . '宝石';
				$gift_price = $gift['gid'];
			}

			$giftList['to_uid'] = $gift['to_uid'];
			$giftList['gift_name'] = $gift_name;
			$giftList['price'] = $gift_price;
			$giftList['gtype'] = $gift['gtype'];
			$giftList['create_time'] = $gift['create_time'];

			//get name
			$userInfo = Hapyfish2_Platform_Bll_User::getUser($giftList['to_uid']);

			$giftListNew[] = array('to_name' => $userInfo['name'],
									'name' => $giftList['gift_name'],
									'price' => $giftList['price'],
									'gtype' => $giftList['gtype'],
									'create_time' => $giftList['create_time']);
		}

		return $giftListNew;
	}

}