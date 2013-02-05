<?php

require_once(CONFIG_DIR . '/language.php');

/**
 * Event Casino
 *
 * @package    Island/Event/Bll
 * @copyright  Copyright (c) 2008 Happyfish Inc.
 * @create     2011/05/24    Nick
*/
class Hapyfish2_Island_Event_Bll_Casino
{
    /**
     * get user point
     *
     */
	public static function getUserPoint($uid)
	{
		$key = 'i:u:casinop:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);

		$data = $cache->get($key);
		if ($data === false) {
			try {
	        	$dalCasino = Hapyfish2_Island_Event_Dal_Casino::getDefaultInstance();
				$data = $dalCasino->getUserPoint($uid);
			
				if ( empty($data) ) {
					$data = 0;
				}
				
	            if ( $data != null ) {
	                $cache->add($key, $data);
	            }
			} catch (Exception $e) {
				return 0;
			}
		}
		return $data;
	}

	public static function updateUserPoint($uid, $point, $nowPoint)
	{
		try {
			$db = Hapyfish2_Island_Event_Dal_Casino::getDefaultInstance();
			$db->updateUserPoint($uid, $point);
			
			$key = 'i:u:casinop:' . $uid;
	        $cache = Hapyfish2_Cache_Factory::getMC($uid);
	        $cache->set($key, $nowPoint);
		} catch(Exception $e) {
			
		}
	}
	
    /**
     * get point change list
     *
     */
	public static function getPointChangeList()
	{
        $key = 'event:pointchalist';
		$EventFeed = Hapyfish2_Cache_Factory::getEventFeed();
		$pointChangeList = $EventFeed->get($key);
		return $pointChangeList;
	}
	
	public static function addUserPointChangeInfo($changeInfo)
	{
        $key = 'event:pointchalist';
		$EventFeed = Hapyfish2_Cache_Factory::getEventFeed();
		$info = array($changeInfo['name'], $changeInfo['create_time'], $changeInfo['giftName']);
    	$EventFeed->insert($key, $info, 0, 8);
	}
	
	public static function getPointChagceGiftList() {
		$aryGift = array ();
		
		$aryGift[0] = array('point' => 2500,  'cid' => '69031', 'count' => 1);
		$aryGift[1] = array('point' => 1500,  'cid' => '50932', 'count' => 1);
		$aryGift[2] = array('point' => 1500,  'cid' => '71832', 'count' => 1);
		$aryGift[3] = array('point' => 1000,  'cid' => '44631', 'count' => 1);
		$aryGift[4] = array('point' => 800,  'cid' => '40932', 'count' => 1);
		$aryGift[5] = array('point' => 800,  'cid' => '41232', 'count' => 1);
		$aryGift[6] = array('point' => 3000,  'cid' => '72731', 'count' => 1);
		$aryGift[7] = array('point' => 3000,  'cid' => '78631', 'count' => 1);
		$aryGift[8] = array('point' => 150,  'cid' => '74841*67541', 'count' => 1);
		$aryGift[9] = array('point' => 80,  'cid' => '74841*67541', 'count' => 1);
		$aryGift[10] = array('point' => 50,  'cid' => '26441*27141', 'count' => 1);
		$aryGift[11] = array('point' => 20,  'cid' => '26441*67141', 'count' => 1);
		
		/*$aryGift [1] = array ('point' => 100, 'bid' => 1, 'count' => 1, 'name' => '简约牛皮卡包' );
		$aryGift [2] = array ('point' => 150, 'bid' => 2, 'count' => 1, 'name' => '金色浴缸收纳盒' );
		$aryGift [3] = array ('point' => 200, 'bid' => 3, 'count' => 1, 'name' => '盒装铁观音70克' );
		$aryGift [4] = array ('point' => 200, 'bid' => 4, 'count' => 1, 'name' => '迪龙USB双震动游戏手柄' );
		$aryGift [5] = array ('point' => 250, 'bid' => 5, 'count' => 1, 'name' => '美容瘦身血珊瑚草' );
		$aryGift [6] = array ('point' => 300, 'bid' => 6, 'count' => 1, 'name' => '缤纷春夏 果冻单鞋' );
		$aryGift [7] = array ('point' => 300, 'bid' => 7, 'count' => 1, 'name' => '子羽串起的幸福项链' );
		$aryGift [8] = array ('point' => 500, 'bid' => 8, 'count' => 1, 'name' => '花瓣面膜' );
		$aryGift [9] = array ('point' => 500, 'bid' => 9, 'count' => 1, 'name' => '户外清凉多功能健步涉溪鞋' );
		$aryGift [10] = array ('point' => 600, 'bid' => 10, 'count' => 1, 'name' => '天丝棉免烫男士休闲长裤' );
		$aryGift [11] = array ('point' => 600, 'bid' => 11, 'count' => 1, 'name' => '凯胜初学超轻羽拍' );
		$aryGift [12] = array ('point' => 800, 'bid' => 12, 'count' => 1, 'name' => '凯胜专业羽毛球鞋' );
		
		$aryGift [13] = array ('point' => 3000, 'bid' => 13, 'count' => 1, 'name' => '性感挂脖聚拢' );
		$aryGift [14] = array ('point' => 2500, 'bid' => 14, 'count' => 1, 'name' => '基本款三色背心' );
		$aryGift [15] = array ('point' => 2000, 'bid' => 15, 'count' => 1, 'name' => '纯棉可爱家居睡衣套' );*/
		
		/*$aryGift [16] = array ('point' => 1500, 'bid' => 39132, 'count' => 1 );
		$aryGift [17] = array ('point' => 1500, 'bid' => 40432, 'count' => 1 );
		$aryGift [18] = array ('point' => 1500, 'bid' => 41232, 'count' => 1 );
		$aryGift [19] = array ('point' => 1500, 'bid' => 41332, 'count' => 1 );
		$aryGift [20] = array ('point' => 700, 'bid' => 60021, 'count' => 1 );
		$aryGift [21] = array ('point' => 700, 'bid' => 41521, 'count' => 1 );
		$aryGift [22] = array ('point' => 700, 'bid' => 41621, 'count' => 1 );
		$aryGift [23] = array ('point' => 700, 'bid' => 41721, 'count' => 1 );
		$aryGift [24] = array ('point' => 100, 'bid' => 24, 'count' => 1 );
		$aryGift [25] = array ('point' => 100, 'bid' => 25, 'count' => 1 );
		$aryGift [26] = array ('point' => 100, 'bid' => 26, 'count' => 1 );
		$aryGift [27] = array ('point' => 100, 'bid' => 27, 'count' => 1 );
		$aryGift [28] = array ('point' => 30, 'bid' => 28, 'count' => 1 );
		$aryGift [29] = array ('point' => 30, 'bid' => 29, 'count' => 1 );
		$aryGift [30] = array ('point' => 30, 'bid' => 30, 'count' => 1 );
		$aryGift [31] = array ('point' => 300, 'bid' => 59821, 'count' => 1 );
		$aryGift [32] = array ('point' => 500, 'bid' => 59921, 'count' => 1 );
		$aryGift [33] = array ('point' => 1000, 'bid' => 57032, 'count' => 1 );*/
		
		return $aryGift;
	}
	
	/**
	 * change casino
	 *
	 * @param $uid
	 * @return array
	 */
	public static function changeCasino($uid, $pointID) 
	{
		$result = array ('status' => - 1, 'content' => '');
		
		//getnbbasic info
		$lstPointGift = self::getPointChagceGiftList();
		if (!isset($lstPointGift[$pointID])) {
			$result ['content'] = LANG_PLATFORM_EVENT_TXT_04;
			return $result;
		}
		
		$point = $lstPointGift[$pointID]['point'];
		$giftID = $lstPointGift[$pointID]['cid'];

		$myPoint = self::getUserPoint($uid);
		if ($myPoint < $point) {
			$result ['content'] = LANG_PLATFORM_EVENT_TXT_05;
			return $result;
		}
		
		$nowDate = date("Y-m-d", time());
		$now = time();
		
		if (in_array($pointID, array(0, 1, 2, 3, 4, 5, 6, 7))) {			
			$ok = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $giftID, 1);

			if($ok) {
	            $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($giftID);
	            $feed = LANG_PLATFORM_EVENT_TXT_13 . '<font color="#FF0000">' . $plantInfo['name'] . '</font>！';
	            $name = $plantInfo['name'];
			}
		} else if (8 == $pointID) {
			$okCid[0] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 74841, 3);
			$okCid[1] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 67541, 10);
			
			if($okCid[0] == true && $okCid[1] == true) {
				$cardInfo1 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(74841);
				$cardInfo2 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(67541);
			
				$feed = LANG_PLATFORM_EVENT_TXT_13 . '<font color="#FF0000">' . $cardInfo1['name'] . '*3、'. $cardInfo2['name'] .'*10</font>！';
				$name = LANG_PLATFORM_EVENT_TXT_07;
			}
		} else if (9 == $pointID) {
			$okCid[0] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 74841, 1);
			$okCid[1] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 67541, 5);
			
			if($okCid[0] == true && $okCid[1] == true) {
				$cardInfo1 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(74841);
				$cardInfo2 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(67541);
			
				$feed = LANG_PLATFORM_EVENT_TXT_13 . '<font color="#FF0000">' . $cardInfo1['name'] . '*1、'. $cardInfo2['name'] .'*5</font>！';
				$name = LANG_PLATFORM_EVENT_TXT_08;
			}
		} else if (10 == $pointID) {
			$okCid[0] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 26441, 3);
			$okCid[1] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 27141, 1);
			
			if($okCid[0] == true && $okCid[1] == true) {
				$cardInfo1 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(26441);
				$cardInfo2 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(27141);
			
				$feed = LANG_PLATFORM_EVENT_TXT_13 . '<font color="#FF0000">' . $cardInfo1['name'] . '*3、'. $cardInfo2['name'] .'*1</font>！';
				$name = LANG_PLATFORM_EVENT_TXT_09;
			}			
		} else if (11 == $pointID) {
			$okCid[0] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 26441, 1);
			$okCid[1] = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, 67141, 1);
			
			if($okCid[0] == true && $okCid[1] == true) {
				$cardInfo1 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(26441);
				$cardInfo2 = Hapyfish2_Island_Cache_BasicInfo::getCardInfo(67141);
			
				$feed = LANG_PLATFORM_EVENT_TXT_13 . '<font color="#FF0000">' . $cardInfo1['name'] . '*1、'. $cardInfo2['name'] .'*1</font>！';
				$name = LANG_PLATFORM_EVENT_TXT_10;
			}
		} 
		
		//update point
		$result['userPoint'] = $myPoint - $point;
		self::updateUserPoint($uid, -$point, $result['userPoint']);
		
		$info = Hapyfish2_Platform_Bll_User::getUser($uid);
        $userPointInfo = array('uid' => $uid,
        					   'name' => $info['name'],
        					   'giftName' => $name,
        					   'create_time' => $now);
        self::addUserPointChangeInfo($userPointInfo);
		
		//积分兑换log
        self::addUserPointChangeLog($uid, $point, $result['userPoint'], $name, $now);

		$minifeed = array (
						'uid' => $uid,
						'template_id' => 0,
						'actor' => $uid,
						'target' => $uid,
						'title' => array('title' => $feed),
						'type' => 6,
						'create_time' => $now
					);
        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		
		$result ['status'] = 1;
		return $result;
	}
	
	/**
	 * user point change log
	 * 
	 * @param $uid
	 * @param $decPoint
	 * @param $userPoint
	 * @param $name
	 * @param $now
	 */
	public static function addUserPointChangeLog($uid, $decPoint, $userPoint, $name, $now)
	{
		$info = array(
				'uid'			=>	$uid,
				'decpoint'		=>	$decPoint,
				'userpoint'		=>	$userPoint,
				'summary'		=>	$name,
				'create_time'	=>	$now
		);
		
		try {			
			$db = Hapyfish2_Island_Event_Dal_Casino::getDefaultInstance();
			$db->addUserPointChangeLog($uid, $info);
		}  catch(Exception $e) {
			info_log($e, 'userPointLogErr');
			info_log('uid:'.$uid.',DecPoint:'.$decPoint.',summary:'.$name.',Time:'.$now, 'userPointChangeLogError');
		}
	}
	
}