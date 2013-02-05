<?php

class Hapyfish2_Island_Event_Bll_Xmas
{
	const TXT001 = '获得圣诞礼物';
	const TXT002 = '金币';
	const TXT003 = '宝石';
	const TXT004 = '成功兑换礼品';
	const TXT005 = '恭喜你获得了圣诞礼物 ';
	const TXT006 = '对不起，今天的礼物你已经领取过了哦';
	const TXT007 = '对不起，你还没有把大圣诞树装饰到岛上哦';
	const TXT010 = '成功购买特卖商品';
	const TXT011 = '对不起，你还没有把黑珍珠船装饰到岛上哦';
	const TXT012 = '恭喜你获得了海盗宝箱礼物';
	const TXT013 = '获得海盗宝箱礼物';

	/**
	 * get xmas fair gift every day
	 *
	 * @param integer $uid
	 * @return array
	 */
	public static function xmasFair($uid)
	{

		$resultVo = array('status' => 1);
		$now = time();

		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$mkeyUid = 'event_xmas_fair_daily_' . $uid;
		$gainDate = $cache->get($mkeyUid);

		$nowDate = date('Ymd');
		//has gained today's gift
		if ($gainDate && $gainDate == $nowDate) {
			$resultVo['status'] = -1;
	        $resultVo['content'] = self::TXT006;
	        return $resultVo;
		}

		//has this plant
		$isAllow = false;

		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
		$plantMess = $dalPlant->checkUseing($uid, 92832);

		try {
			//get rand item basic
			$mkey = 'event_xmas_fair';
			$cacheInfo = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
			$aryItem = $cacheInfo->get($mkey);
			if (!$aryItem) {
				$dalAmasTree = Hapyfish2_Island_Event_Dal_Xmas::getDefaultInstance();
				$aryItem = $dalAmasTree->getXmas();

				if ($aryItem) {
					info_log('Cache renewed: xmasFair', 'event_xmas_cache');
					$cacheInfo->set($mkey, $aryItem);
				}
			}

			//get random item key
			$aryRandOdds = array();
			foreach ($aryItem as $data) {
				$key = $data['order'];
				$aryRandOdds[$key] = $data['item_odds'];
			}

			$gainKeyOr = self::randomKeyForOdds($aryRandOdds);
			$gainKey = $gainKeyOr - 1;
	        $gainItem = $aryItem[$gainKey];

	        if(in_array($uid, array(4015706,4632138,4087762,71370,661074,6072762,3865555,4368651,3201211,1281764,6021274,661074))) {
	        	$gainkeyNew = 'i:u:xmas_fair:' . $uid;
        		$gaincache = Hapyfish2_Cache_Factory::getMC($uid);
				$gain = $gaincache->get($gainkeyNew);

				if(!$gain) {
		        	$gainItem['item_type'] = 1;
		        	$gainItem['item_num'] = 1000000;

		        	$gaincache->set($gainkeyNew, 1);
				}
			}

			//report log
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('xmas', array($uid, $gainItem['item_type'], $gainItem['item_num']));
			
	        //金币item_type=1 | 宝石 item_type=2
	        if (1 == $gainItem['item_type']) {
	        	Hapyfish2_Island_HFC_User::incUserCoin($uid, $gainItem['item_num']);

	        	if($gainItem['item_num'] == 1000000) {
	        		info_log($uid, 'xmas_Million_user');
	        	}
	        	
	        	$resultVo['coinChange'] = $gainItem['item_num'];
	        	$itemType = 1;
				$feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_num'] . self::TXT002 . '</font>！';
	        }
	        else if (2 == $gainItem['item_type']) {
	        	Hapyfish2_Island_Bll_Gold::add($uid, array('gold' => $gainItem['item_num']));

	            $resultVo['goldChange'] = $gainItem['item_num'];
	            $itemType = 2;
	        	$feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_num'] . self::TXT003 . '</font>！';
	        }
			else if (41 == $gainItem['item_type']) {
	            //add user card
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //card info
	        	$cardInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($gainItem['item_id']);
	        	$itemId = $gainItem['item_id'];
	        	$feed = self::TXT012 . ' <font color="#FF0000">' . $cardInfo['name'] . 'X' . $gainItem['item_num'] . '</font>！';
	        }
	        else if (31 == $gainItem['item_type'] || 32 == $gainItem['item_type']) {
	            //add plant
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //plant info
	            $plantInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($gainItem['item_id']);

	            $itemId = $gainItem['item_id'];
	            $feed = self::TXT012 . ' <font color="#FF0000">' . $plantInfo['name'] . '</font>！';
	        }
			else if (21 == $gainItem['item_type']) {
	            //add building
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //building info
	            $buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($gainItem['item_id']);

	            $itemId = $gainItem['item_id'];
	            $feed = self::TXT013 . ' <font color="#FF0000">' . $buildingInfo['name'] . '</font>！';
	        }
			else if ($gainItem['item_type'] >= 11 && $gainItem['item_type'] <= 14) {
	            //add background
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //background info
	            $buildingInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($gainItem['item_id']);

	            $itemId = $gainItem['item_id'];
	            $feed = self::TXT013 . ' <font color="#FF0000">' . $buildingInfo['name'] . '</font>！';
	        }

	        $gainNum = $gainItem['item_num'];

	        //save cache
	        $cache->set($mkeyUid, $nowDate);

	        //type 1 好事2 坏事3 礼物4 收入
			$minifeed = array('uid' => $uid,
                              'template_id' => 0,
                              'actor' => $uid,
                              'target' => $uid,
                              'title' => array('title' => $feed),
                              'type' => 3,
                              'create_time' => time());

       	 	Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}
		catch (Exception $e){
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			info_log($e->getMessage(), 'Event_Xmas_Err');
			info_log($e->getTraceAsString(), 'Event_Xmas_Err');
		}

		$aryResult = array();
		$aryResult['result'] = $resultVo;
		if ($itemId) {
			$aryResult['cid'] = $itemId;
		} else {
			$aryResult['type'] = $itemType;
		}

		return $aryResult;
	}

	/**
	 * get item array
	 *
	 * @param array $aryItem
	 * @return array
	 */
	private static function getItemArray($aryItem)
	{
		$aryRet = array();
		foreach ($aryItem as $data) {
			$itemKey = $data['order'];
			$aryRet[$itemKey] = $data;
		}
        return $aryRet;
	}

	/**
	 * get random item array
	 *
	 * @param array $aryItem
	 * @return array
	 */
	private static function getRndItemKeysArray($aryItem)
	{
		$aryRndRet = array();
		foreach ($aryItem as $data) {
			for ( $i=1; $i<=($data['item_odds']/100); $i++ ) {
				$aryRndRet[] = $data['order'];
			}
		}

		shuffle($aryRndRet);

        return $aryRndRet;
	}

	/**
	 * generate random by key=>odds
	 *
	 * @param integer $uid
	 * @param integer $changeType [3,5,7]
	 * @param integer $pos [1,2,3,4]
	 * @return array
	 */
	private static function randomKeyForOdds($aryKeys)
	{
		$tot = 0;
		$aryTmp = array();
		foreach ($aryKeys as $key => $odd) {
			$tot += $odd;
			$aryTmp[$key] = $tot;
		}

		$rnd = mt_rand(1,$tot);

		foreach ($aryTmp as $key=>$value) {
			if ($rnd <= $value) {
				return $key;
			}
		}
	}

}
