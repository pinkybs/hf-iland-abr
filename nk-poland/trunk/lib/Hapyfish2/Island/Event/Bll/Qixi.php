<?php

class Hapyfish2_Island_Event_Bll_Qixi
{
	const TXT002 = '金幣';
	const TXT003 = '寶石';
	const TXT006 = '對不去，今天的禮物你已經領取過了哦';
	const TXT007 = '對不起，您沒有該建築不能抽獎';
	const TXT012 = '恭喜你 獲得七夕鵲橋禮包';
	const TXT013 = ' 獲得七夕鵲橋禮包';

	public static function getGift($uid)
	{
		$rsp ['resultVo'] ['status'] = 1;
		$switch = self::getswitch($uid);
		if($switch){
			$rsp = array ();
			$rsp ['resultVo'] ['status'] = - 1;
			$rsp ['resultVo'] ['content'] = '您已領取過，或活動已過期！';
			return $rsp;
		}

		$bplist = self::getUserBAndP($uid);
		$list = array(104532,104732,104421);
		$i = 0;
		foreach ( $list as $k => $v ) {
			$subcid = substr ( $v, - 2 );
	    	if( $subcid == 31 || $subcid == 32 ) {
    			if( in_array( $v, $bplist['plant'] ) ) 
    			{
    				$i++;
    			}
    		}
			if ($subcid == 21) {
				if (in_array ( $v, $bplist['building'] ))
				{
					$i++;
				}
			}
		}	
    	if( $i == 3 ) {
    		$com = new Hapyfish2_Island_Bll_Compensation();
    		$com -> setItem(104832, 1);
    		$com -> sendone($uid, '恭喜你成功領取：');
    		$key = 'qixi:get:gift:'.$uid;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache -> set($key, true);
			$qixidal = Hapyfish2_Island_Event_Dal_Qixi::getDefaultInstance();
			$qixidal -> updateUserStatus($uid);
    	} else {
    		$rsp = array ();
			$rsp ['resultVo'] ['status'] = - 1;
			$rsp ['resultVo'] ['content'] = '沒有收集齊，不能領取！';
    	}
		return $rsp;
	}


public static function xmasFair($uid)
	{

		$resultVo['resultVo']= array('status' => 1);
		$now = time();

		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$mkeyUid = 'event:xmas:fair:qixi:daily:' . $uid;
		$gainDate = $cache->get($mkeyUid);

		$nowDate = date('Ymd');
		//has gained today's gift
		if ($gainDate && $gainDate == $nowDate) {
			$resultVo['resultVo']['status'] = -1;
	        $resultVo['resultVo']['content'] = self::TXT006;
	        return $resultVo;
		}
		//has this plant
		$isAllow = false;

		$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
		$plantMess = $dalPlant->checkUseing($uid, 105032);
		if(!$plantMess){
			$resultVo['resultVo']['status'] = -1;
	        $resultVo['resultVo']['content'] = self::TXT007;
	        return $resultVo;
		}

		try {
			//get rand item basic
			$mkey = 'event_xmas_qixi_fair';
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

//	        if(in_array($uid, array(4015706,4632138,4087762,71370,661074,6072762,3865555,4368651,3201211,1281764,6021274,661074))) {
//	        	$gainkeyNew = 'i:u:xmas_fair:' . $uid;
//        		$gaincache = Hapyfish2_Cache_Factory::getMC($uid);
//				$gain = $gaincache->get($gainkeyNew);
//
//				if(!$gain) {
//		        	$gainItem['item_type'] = 1;
//		        	$gainItem['item_num'] = 1000000;
//
//		        	$gaincache->set($gainkeyNew, 1);
//				}
//			}

			//report log
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('xmas', array($uid, $gainItem['item_type'], $gainItem['item_num']));
			
	        //金币item_type=1 | 宝石 item_type=2
	        if (1 == $gainItem['item_type']) {
	        	Hapyfish2_Island_HFC_User::incUserCoin($uid, $gainItem['item_num']);

	        	if($gainItem['item_num'] == 777777) {
	        		info_log($nowDate.'-----------:'.$uid, 'xmas_Million_user');
	        	}
	        	
	        	$itemType = 1;
				$feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_num'] . self::TXT002 . '</font>！';
	        }
	        else if (2 == $gainItem['item_type']) {
	        	Hapyfish2_Island_Bll_Gold::add($uid, array('gold' => $gainItem['item_num']));

	            $itemType = 2;
	        	$feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_num'] . self::TXT003 . '</font>！';
	        }
			else if (41 == $gainItem['item_type']) {
	            //add user card
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //card info
	        	$feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_name'] . 'X' . $gainItem['item_num'] . '</font>！';
	        }
	        else if (31 == $gainItem['item_type'] || 32 == $gainItem['item_type']) {
	            //add plant
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            $feed = self::TXT012 . ' <font color="#FF0000">' . $gainItem['item_name'] . '</font>！';
	        }
			else if (21 == $gainItem['item_type']) {
	            //add building
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);

	            //building info
	            $feed = self::TXT013 . ' <font color="#FF0000">' . $gainItem['item_name'] . '</font>！';
	        }
			else if ($gainItem['item_type'] >= 11 && $gainItem['item_type'] <= 14) {
	            //add background
	            Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $gainItem['item_id'], $gainItem['item_num']);


	            $itemId = $gainItem['item_id'];
	            $feed = self::TXT013 . ' <font color="#FF0000">' . $gainItem['item_name'] . '</font>！';
	        }
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
			$resultVo['resultVo']['status'] = '-1';
			$resultVo['resultVo']['content'] = 'serverWord_110';
			info_log($e->getMessage(), 'Event_Xmas_Err');
			info_log($e->getTraceAsString(), 'Event_Xmas_Err');
		}

		$aryResult = array();
		$items = array();
		$coin = 0;
		$gold = 0;
		if($gainItem['item_type'] == 1){
			$coin = $gainItem['item_num'];
		} elseif($gainItem['item_type'] == 2){
			$gold = $gainItem['item_num'];
		} else{
			$items[] = isset($gainItem['item_id'])?$gainItem['item_id']:0;
			$items[] = isset($gainItem['item_num'])?$gainItem['item_num']:0;
			$items[] = isset($gainItem['item_type'])?$gainItem['item_type']:0;
		}
		$resultVo['resultVo']['coinChange'] = $coin;
		$resultVo['resultVo']['goldChange'] = $gold;
		$aryResult = $resultVo;
		$aryResult['items'] = empty($items)?array():array($items);
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
	

	public static function getswitch($uid)
	{
		if(time() >= 1316102399){
			return true;
		}
		$statue = false;
		$key = 'qixi:get:gift:'.$uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		$statue = $data;
		if($data === false){
			try {
            	$dal = Hapyfish2_Island_Event_Dal_Qixi::getDefaultInstance();
            	$data = $dal->getUserStatue($uid);
	            if ($data) {
	            	$cache->set($key, true);
	            	return true;
	            } else {
	            	return false;
	            }
        	} catch (Exception $e) {
        		return true;
        	} 
    	}
    	return $statue;
	}

	public static function getUserBAndP($uid)
	{
		$dalb = Hapyfish2_Island_Dal_Building::getDefaultInstance();
		$dalp = Hapyfish2_Island_Dal_Plant::getDefaultInstance();

		$temp1 = $dalp->getAllCid($uid);
		$temp2 = $dalb->getAllCid($uid);

			$pids = array ();
			$bids = array ();
			foreach ( $temp1 as $k => $v ) {
				$pids [] = $v ['cid'];
			}
			foreach ( $temp2 as $k => $v ) {
				$bids [] = $v ['cid'];
			}
			return array('plant'=> $pids,'building' => $bids);
	}
	
	public static function checkToday($uid)
	{
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$mkeyUid = 'event:xmas:fair:qixi:daily:' . $uid;
		$gainDate = $cache->get($mkeyUid);

		$nowDate = date('Ymd');
		//has gained today's gift
		if ($gainDate && $gainDate == $nowDate) {
			return false;
		}else{
			return true;
		}
	}

}