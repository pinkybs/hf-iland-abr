<?php

class Hapyfish2_Island_Bll_GiftPackage
{

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
		
		$count = 0;
		for($i = 0; $i < $item_num; $i++) {
			$ok = Hapyfish2_Island_HFC_Building::addOne($uid, $newBuilding);
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
			'uid' => $fid,
			'cid' => $item_id,
			'item_type' => $itemType,
			'item_id' => $plantInfo['item_id'],
			'level' => $plantInfo['level'],
			'status' => 0,
			'buy_time' => $time
		);
		
		$count = 0;
		for($i = 0; $i < $item_num; $i++) {
			$ok = Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
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
		$type = substr($gid, -2);
		$itemType = substr($gid, -2, 1);
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
		$lastReadTime = time() - 10000000;
		foreach ( $giftVOLists as $giftVOList ) {
			$giftVo['type'] = $giftVOList['gift_type'];
			$giftVo['sendTime'] = $giftVOList['send_time'];
			if ($giftVOList['send_time'] > $lastReadTime) {
				$giftVo['newFlag'] = 1;
			} else {
				$giftVo['newFlag'] = 0;
			}
				
			if( $giftVOList['gift_type'] == 1 ) {
				$giftVo['id'] = $giftVOList['pid'];	
				$giftVo['sendReason'] = '升级奖励';
				$giftVo['sendUserName'] = '系统';
			}	
			else if ( $giftVOList['gift_type'] == 2 ) {
				$giftVo['id'] = $giftVOList['pid'];					
				$giftVo['sendReason'] = '称号奖励';
				$giftVo['sendUserName'] = '系统';
			}
			else if ( $giftVOList['gift_type'] == 7 ) {
				$giftVo['id'] = $giftVOList['pid'];					
				$giftVo['sendReason'] = '连续登陆奖励';
				$giftVo['sendUserName'] = '系统';
			}
			else if( $giftVOList['gift_type'] == 8 ){
				$giftVo['id'] = $giftVOList['pid'];					
				$giftVo['sendReason'] = '新手引导奖励';
				$giftVo['sendUserName'] = '系统';
			}	
			else if( $giftVOList['gift_type'] == 9 ){
				$giftVo['id'] = $giftVOList['pid'];					
				$giftVo['sendReason'] = '等级大礼包';
				$giftVo['sendUserName'] = '系统';
			}
			else if( $giftVOList['gift_type'] == 10 ){
				$giftVo['id'] = $giftVOList['pid'];					
				$giftVo['sendReason'] = '新手时间礼物';
				$giftVo['sendUserName'] = '系统';
			}
			else {				
				$giftVo['sendUserId'] = $giftVOList['from_uid'];
				$giftVo['id'] = $giftVOList['pid'];					
				
				$userInfo = Hapyfish2_Platform_Bll_User::getUser($giftVOList['from_uid']);
				$giftVo['sendUserName'] = $userInfo['name'];
				$giftVo['sendReason'] = $userInfo['name'].'赠';
			}
			
			$giftVo['itemList'] = array();
			$giftList[] = $giftVo;
		}	
		
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
			$result['content'] = '礼包不存在!';
            return $result;
		}
		
		//get gift
		try {
			$dalGiftPackage = Hapyfish2_Island_Dal_GiftPackage::getDefaultInstance();
			$gift = $dalGiftPackage->getOne($uid, $pid);
		} catch (Exception $e) {
			$result['content'] = '读取礼包数据出错!';
			return $result;
		}
		
		if (empty($gift)) {
			$result['content'] = '礼包为空!';
            return $result;
		}
		
		$giftVo = array();
		$result = array('status' => 1); 
		
		$giftVo['type'] = $gift['gift_type'];
		$giftVo['sendTime'] = $gift['send_time'];
		
		if( $gift['gift_type'] == 1 ) {
			$giftVo['id'] = $gift['pid'];	
			$giftVo['sendReason'] = '升级奖励';
			$giftVo['sendUserName'] = '系统';
		}	
		else if ( $gift['gift_type'] == 2 ) {
			$giftVo['id'] = $gift['pid'];					
			$giftVo['sendReason'] = '称号奖励';
			$giftVo['sendUserName'] = '系统';
		}
		else if ( $gift['gift_type'] == 7 ) {
			$giftVo['id'] = $gift['pid'];					
			$giftVo['sendReason'] = '连续登陆奖励';
			$giftVo['sendUserName'] = '系统';
		}
		else if( $gift['gift_type'] == 8 ){
			$giftVo['id'] = $gift['pid'];					
			$giftVo['sendReason'] = '新手引导奖励';
			$giftVo['sendUserName'] = '系统';
		}	
		else if( $gift['gift_type'] == 9 ){
			$giftVo['id'] = $gift['pid'];					
			$giftVo['sendReason'] = '等级大礼包';
			$giftVo['sendUserName'] = '系统';
		}
		else if( $gift['gift_type'] == 10 ){
			$giftVo['id'] = $gift['pid'];					
			$giftVo['sendReason'] = '新手时间礼物';
			$giftVo['sendUserName'] = '系统';
		}
		else {					
			$giftVo['sendUserId'] = $gift['from_uid'];
			$giftVo['id'] = $gift['pid'];					
				
			$userInfo = Hapyfish2_Platform_Bll_User::getUser($gift['from_uid']);
			$giftVo['sendUserName'] = $userInfo['name'];
			$giftVo['sendReason'] = $userInfo['name'].'赠';
		}
		
		$itemList = array();
		if ($gift['coin'] > 0) {
			$itemList[] = array('coin' => $gift['coin']);
			$result['coinChange'] = $gift['coin'];
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $gift['coin']);
		}
		if ($gift['exp'] > 0) {
			$itemList[] = array('exp' => $gift['exp']);
			$result['expChange'] = $gift['exp'];
			
			Hapyfish2_Island_HFC_User::incUserExp($uid, $gift['exp']);
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
}