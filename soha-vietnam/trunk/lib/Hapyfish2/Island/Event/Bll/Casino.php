<?php

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
		
		$aryGift [16] = array ('point' => 1500, 'bid' => 39132, 'count' => 1 );
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
		$aryGift [33] = array ('point' => 1000, 'bid' => 57032, 'count' => 1 );
		return $aryGift;
	}
	
	/**
	 * change casino
	 *
	 * @param $uid
	 * @return array
	 */
	public static function changeCasino($uid, $point, $itid, $changetype) 
	{
		$result = array ('status' => - 1, 'content' => '', 'itemcount' => 0, 'itemid' => $itid );
		
		//getnbbasic info
		$lstPointGift = self::getPointChagceGiftList ();
		if (! isset ( $lstPointGift [$itid] )) {
			$result ['content'] = '您选择的兑换物品有误，请重新选择。';
			return $result;
		}
		
		$myPoint = self::getUserPoint($uid);
		if ($myPoint < $point) {
			$result ['content'] = '您的积分不够兑换此物品哦~';
			return $result;
		}
		$nowDate = date ( "Y-m-d", time () );
		$now = time ();
		//先检查是否是特卖
		if ( ($itid >= 16 && $itid <= 23 ) || ($itid == 31 || $itid == 32 || $itid == 33)) {
			$pointGift = $lstPointGift [$itid] ['bid'];
			$itemType = substr ( $pointGift, - 2, 2 );
			if ($itemType == 21) {
				$b1 = array ('uid' => $uid, 'cid' => $pointGift, 'item_type' => $itemType, 'status' => 0, 'buy_time' => $now );
				Hapyfish2_Island_HFC_Building::addOne($uid, $b1);
            	$buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($pointGift);
				$name = $buildingInfo ['name'];
			} else if ($itemType == 31 || $itemType == 32) {
				$itemId = substr ( $pointGift, 0, - 2 );
				
				$plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($pointGift);
	            $p1 = array('uid' => $uid, 'cid' => $pointGift, 'status' => 0, 'item_id' => $itemId, 'level' => $plantInfo['level'], 'buy_time' => $now, 'item_type' => $itemType);
	            Hapyfish2_Island_HFC_Plant::addOne($uid, $p1);
	            
				$name = $plantInfo ['name'];
			} else if ($itemType == 41) {
				$newCard = array ('uid' => $uid, 'cid' => $pointGift, 'count' => $lstPointGift [$itid] ['count'], 'buy_time' => $now, 'item_type' => $itemType );
				//add user card
            	Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard['cid'], $newCard['count']);
	        	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($pointGift);
            	
				$name = $cardInfo ['name'] . $lstPointGift [$itid] ['count'] . '张';
			}
		} elseif ($itid >= 24 && $itid <= 30) {
			switch ($itid) {
				case "24" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 26241, 'count' => 10, 'buy_time' => $now, 'item_type' => 41 );
					$newCard2 = array ('uid' => $uid, 'cid' => 26341, 'count' => 5, 'buy_time' => $now, 'item_type' => 41 );
					$newCard3 = array ('uid' => $uid, 'cid' => 26441, 'count' => 3, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard2['cid'], $newCard2['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard3['cid'], $newCard3['count']);
					$name = '加速卡礼包';
					break;
				case "25" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 26541, 'count' => 5, 'buy_time' => $now, 'item_type' => 41 );
					$newCard2 = array ('uid' => $uid, 'cid' => 26641, 'count' => 5, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard2['cid'], $newCard2['count']);
					$name = '加时卡礼包';
					break;
				case "26" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 26841, 'count' => 10, 'buy_time' => $now, 'item_type' => 41 );
					$newCard2 = array ('uid' => $uid, 'cid' => 67541, 'count' => 5, 'buy_time' => $now, 'item_type' => 41 );
					$newCard3 = array ('uid' => $uid, 'cid' => 27141, 'count' => 5, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard2['cid'], $newCard2['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard3['cid'], $newCard3['count']);
					$name = '防御类礼包';
					break;
				case "27" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 67141, 'count' => 10, 'buy_time' => $now, 'item_type' => 41 );
					$newCard2 = array ('uid' => $uid, 'cid' => 67241, 'count' => 10, 'buy_time' => $now, 'item_type' => 41 );
					$newCard3 = array ('uid' => $uid, 'cid' => 67341, 'count' => 10, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard2['cid'], $newCard2['count']);
            		Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard3['cid'], $newCard3['count']);
					$name = '财神卡礼包';
					break;
				case "28" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 26441, 'count' => 3, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
					$name = '加速卡礼包';
					break;
				case "29" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 67241, 'count' => 3, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
					$name = '请神卡礼包';
					break;
				case "30" :
            		$newCard1 = array ('uid' => $uid, 'cid' => 67541, 'count' => 3, 'buy_time' => $now, 'item_type' => 41 );
					Hapyfish2_Island_HFC_Card::addUserCard($uid, $newCard1['cid'], $newCard1['count']);
					$name = '防御卡礼包';
					break;
			}
		}
		
		//update point
		if ($changetype == "1") {
			if ( $itid == 13 || $itid == 14 || $itid == 15 ) {
				$result['userPoint'] = $myPoint - 300;
				self::updateUserPoint($uid, -300, $result['userPoint']);
				
				$info = Hapyfish2_Platform_Bll_User::getUser($uid);
		        $userPointInfo = array('uid' => $uid,
		        					   'name' => $info['name'],
		        					   'giftName' => $name,
		        					   'create_time' => $now);
		        self::addUserPointChangeInfo($userPointInfo);
		        
		        $decPoint = 300;
			}
		} else {
			$result['userPoint'] = $myPoint - $lstPointGift[$itid]['point'];
			self::updateUserPoint($uid, -$lstPointGift[$itid]['point'], $result['userPoint']);
			
			$info = Hapyfish2_Platform_Bll_User::getUser($uid);
	        $userPointInfo = array('uid' => $uid,
	        					   'name' => $info['name'],
	        					   'giftName' => $name,
	        					   'create_time' => $now);
	        self::addUserPointChangeInfo($userPointInfo);
	        
	        $decPoint = $lstPointGift[$itid]['point'];
		}
		
		//积分兑换log
        self::addUserPointChangeLog($uid, $decPoint, $result['userPoint'], $name, $now);
		
		$name = isset ( $name2 ) ? $name2 : $name;
		$feed = '恭喜您用积分兑换了<font color="#FF0000">' . $name . '</font>！';
		$minifeed = array ('uid' => $uid, 'template_id' => 0, 'actor' => $uid, 'target' => $uid, 'title' => array ('title' => $feed ), 'type' => 6, 'create_time' => $now );
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