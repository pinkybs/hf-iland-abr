<?php

class Hapyfish2_Island_Bll_Gift
{
	/**
	 * add gift BackGround
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addBackGround($uid, $fid, $cid, $itemType)
	{
		$bgInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($cid);
		if (!$bgInfo) {
			return false;
		}
		
		$newBackground = array(
			'uid' => $fid,
			'bgid' => $cid,
			'item_type' => $itemType,
			'buy_time' => time()
		);
            
		$ok = Hapyfish2_Island_Cache_Background::addNewBackground($fid, $newBackground);
		
		if ($ok) {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_4', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_4', 1);
		}

		return $ok;
	}

	/**
	 * add gift card
	 * @param : integer uid
	 * @param : integer fid
	 * @param : integer id
	 * @param : integer $itemType
	 * @return: boolean
	 */
	public static function addCard($uid, $fid, $cid, $itemType)
	{
		$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cid);
		if (!$cardInfo) {
			return false;
		}
		
		$ok = Hapyfish2_Island_HFC_Card::addUserCard($fid, $cid, 1);
		
		if ($ok) {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_4', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_4', 1);
		}
		
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
	public static function addBuilding($uid, $fid, $cid, $itemType)
	{
		$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($cid);
		if (!$buildingInfo) {
			return false;
		}
		
		$newBuilding = array(
			'uid' => $fid,
			'cid' => $cid,
			'item_type' => $itemType,
			'status' => 0,
			'buy_time' => time()
		);
		
		$ok = Hapyfish2_Island_HFC_Building::addOne($fid, $newBuilding);
		
		if ($ok) {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_4', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_4', 1);
		}
		
		return $ok;
	}

	public static function addPlant($uid, $fid, $cid, $itemType)
	{
		$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cid);
		if (!$plantInfo) {
			return false;
		}
		
		$newPlant = array(
			'uid' => $fid,
			'cid' => $cid,
			'item_type' => $itemType,
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => time()
		);
		
		$ok = Hapyfish2_Island_HFC_Plant::addOne($fid, $newPlant);
		
		if ($ok) {
			Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_4', 1);
			Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_4', 1);
		}
		
		return $ok;
	}

	/**
	 * type add gift
	 * @param integer $actorUid
	 * @param integer $fid
	 * @param integer $gid
	 * @return boolean $result
	 */
	public static function addGift($uid, $fid, $gid)
	{
		$result = false;
		$type = substr($gid, -2);
		$itemType = substr($gid, -2, 1);

		//itemType,1x->card,2x->background,3x->plant,4x->building
		if ($itemType == 1) {
			$result = self::addBackground($uid, $fid, $gid, $type);
		} else if ($itemType == 2) {
			$result = self::addBuilding($uid, $fid, $gid, $type);
		} else if ($itemType == 3) {
			$result = self::addPlant($uid, $fid, $gid, $type);
        } else if ($itemType == 4) {
			$result = self::addCard($uid, $fid, $gid, $type);
		}

        return $result;
	}

	/**
	 * send gift
	 * @param array $g
	 * @param array $fids (friend uid)
	 * @return boolean
	 */
	public static function sendGift($gid, $uid, $fids, $countInfo)
	{
	    if (empty($fids)) {
	    	return 0;
	    }
	    
	    $time = time();
	    $count = 0;
		foreach ($fids as $fid) {
			$ok = self::addGift($uid, $fid, $gid);
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
		}
		
		if ($count > 0) {
			$countInfo['count'] -= $count;
			Hapyfish2_Island_Cache_Counter::updateSendGiftCount($uid, $countInfo);
		}
		
		return $count;
	}
}