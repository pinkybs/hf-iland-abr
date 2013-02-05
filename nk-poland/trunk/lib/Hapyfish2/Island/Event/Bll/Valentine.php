<?php

class Hapyfish2_Island_Event_Bll_Valentine
{
	const CACHE_KEY_EXCHANGE = 'event_valentine_exchange_list';
	const CACHE_KEY_RANK = 'event_valentine_rank_list';
	const TXT001 = '獲得七夕禮物：';
	const TXT002 = '金幣';
	const TXT003 = '成功領取禮品：';
	const TXT004 = '成功兌換禮品：';
	const TXT005 = '恭喜你獲得情人節禮物 ';
	const TXT006 = '對不起，今天的禮物你已經領取過了';
	const TXT007 = '對不起，你還沒有鵲羽哦';
	const TXT008 = '鵲羽';
	const TXT009 = '七夕禮包';
	const TXT010 = '牛郎';
	const TXT011 = '織女';
	const TXT012 = '七夕天空';
	const TXT013 = '許願樹';
	const TXT015 = '雙魚座';
	const TXT016 = '對不起，您已領取過該禮物';

	/**
	 * get user valentine info
	 *
	 * @param integer $uid
	 * @return array
	 */
	public static function getUserValentine($uid)
	{
		$resultVo = array('status' => 1);
		$awardlist = array(0, 0 ,0 ,0 ,0);
		try {
			$dalValentine = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
			$rowValentine = $dalValentine->get($uid);
			$alist = $dalValentine->getUserExchange($uid);
			if($rowValentine){
				foreach($alist as $k => $v){
					$awardlist[$v['method']-1] = 1;
				}
			}
		}
		catch (Exception $e) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			info_log('getUserValentine', 'Event_Valentine_Err');
			info_log($e->getMessage(), 'Event_Valentine_Err');
			return array('result' => $resultVo);
		}

		$result = array('result' => $resultVo);
		if (empty($rowValentine)) {
			try {
				self::incRose($uid, 7, 1);
				$result['getRoseNum'] = 7;
			}
			catch (Exception $e) {
				info_log('getUserValentine', 'Event_Valentine_Err');
				info_log($e->getMessage(), 'Event_Valentine_Err');
				$result['getRoseNum'] = 0;
			}
		}
		else {
			$result['getRoseNum'] = $rowValentine['rose'];
			if($rowValentine['rose'] < 7){
				$rose = 7 ;
				self::incRose($uid, $rose, 1);
				$result['getRoseNum'] = 7 + $rowValentine['rose'];
			}
			$result['awardList'] = $awardlist;
		}
		return $result;
	}

	/**
	 * exchange roses
	 *
	 * @param integer $uid
	 * @param integer $changeType [1,2,3,4,5]
	 * @return array
	 */
	public static function exchangeRose($uid, $changeType=1)
	{
		$resultVo = array();
		$resultVo = array('status' => 1);

		if ( $changeType<1 || $changeType>5 ) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_150';
			return array('result' => $resultVo);
		}

		try {
			$dalValentine = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
			$rowValentine = $dalValentine->get($uid);
			$check = $dalValentine->checkExchange($uid, $changeType);
			if (empty($rowValentine)) {
				$resultVo['status'] = '-1';
				$resultVo['content'] = 'serverWord_150';
				return array('result' => $resultVo);
			}
			if ($check){
				$resultVo['status'] = '-1';
				$resultVo['content'] = self::TXT016;
				return array('result' => $resultVo);
			}
		}
		catch (Exception $e) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			info_log('exchangeRose', 'Event_Valentine_Err');
			info_log($e->getMessage(), 'Event_Valentine_Err');
			return array('result' => $resultVo);
		}

		/*	名称	ID	数量	玫瑰数
			经验卡金币	74841	1, coin:5000	7
			牛郎           	104532	1	37
			织女			104732	1	107
			天			105512	1	167
			许愿树		105132	1	277
		*/
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		if (1 == $changeType) {
			$needCnt = 7;
			$gainItemId = '74841';
			$compensation->setCoin(5000);
			$gainName = self::TXT009;
		}
		else if (2 == $changeType) {
			$needCnt = 37;
			$gainItemId = '104532';
			$gainName = self::TXT010;
		}
		else if (3 == $changeType) {
			$needCnt = 107;
			$gainItemId = '104732';
			$gainName = self::TXT011;
		}
		else if (4 == $changeType) {
			$needCnt = 167;
			$gainItemId = '105512';
			$gainName = self::TXT012;
		}
		else {
			$needCnt = 277;
			$gainItemId = '105132';
			$gainName = self::TXT013;
		}

		if ($rowValentine['rose'] < $needCnt) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_150';
			return array('result' => $resultVo);
		}
		//send item to user
		$compensation->setItem($gainItemId, 1);
		$ok = $compensation->sendOne($uid, self::TXT003);
	    if ($ok) {
	    	$now = time();
	    	//update rose count
	        try {
	        	//update exchange log
		        $dalExg = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
		        $dalExg->insertUserExchange($uid, $changeType);
			}
			catch (Exception $e) {
				info_log($e->getMessage(), 'Event_Valentine_Err');
			}
        	//cache feed
	        $user = Hapyfish2_Platform_Bll_User::getUser($uid);
			$mkey = self::CACHE_KEY_EXCHANGE;
			$eventFeed = Hapyfish2_Cache_Factory::getEventFeed();
			$eventFeed->insert($mkey, array($user['name'], $gainName, $now, $uid));
			$feed = Hapyfish2_Island_Bll_Activity::send('QIXI_SHRE', $uid, array('name'=>$gainName));
	    }
		else {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			return array('result' => $resultVo);
		}

		//create result vo
		return array('result' => $resultVo, 'itemId' => $gainItemId, 'feed' => isset($feed)?$feed:'');
	}

	/**
	 * send rose
	 * @param : integer uid
	 * @param : string fids
	 * @return: array
	 */
	public static function sendRose($uid, $fids)
	{
		$resultVo = array();

		if (empty($fids)) {
			$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_110';
        	return array('result' => $resultVo);
		}

		if (strpos($fids, ',') === false) {
			$aryFid[] = $fids;
		}
		else {
			$aryFid = explode(',', $fids);
		}

		if (empty($aryFid) || count($aryFid) > 10) {
			$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_110';
        	return array('result' => $resultVo);
		}

		//is friend check
		foreach ($aryFid as $fid) {
			$isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $fid);
			if (!$isFriend) {
				$resultVo['status'] = -1;
	        	$resultVo['content'] = 'serverWord_173';
	        	return array('result' => $resultVo);
			}
		}

		//get today send uid list
		$now = time();
		$today = date('Ymd');
		$mkey = 'i:u:eventsendrose:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $arySendInfo = $cache->get($mkey);
        $aryAddFids = array();
        if ( $arySendInfo && $arySendInfo['dt'] && $arySendInfo['dt']==$today && $arySendInfo['ids'] ) {
        	$sendNum = count($arySendInfo['ids']);
        	if ($sendNum >=10) {
				$resultVo['status'] = -1;
	        	$resultVo['content'] = 'serverWord_110';
	        	return array('result' => $resultVo);
        	}

        	foreach ($aryFid as $fid) {
        		if (!in_array($fid, $arySendInfo['ids'])) {
					$arySendInfo['ids'][] = $fid;
					$aryAddFids[] = $fid;
					if (count($arySendInfo['ids']) >= 10) {
						break;
					}
        		}
        	}
        }
        else {
        	$arySendInfo = array();
        	$arySendInfo['dt'] = $today;
        	$arySendInfo['ids'] = $aryFid;
        	$aryAddFids = $aryFid;
        }

        if (!empty($aryAddFids)) {
        	//send to fid
	        $rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
	        foreach ($aryAddFids as $fid) {
				self::incRose($fid, 1, 2);//friend send
				$title = $rowUser['name'].'送給你一根鵲羽';
		        $minifeed = array('uid' => $fid,
		                          'template_id' => 0,
		                          'actor' => $uid,
		                          'target' => $fid,
		                          'title' => array('title' => $title),
		                          'type' => 3,
		                          'create_time' => $now);
		        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
	        }
	        //update cache
	        $cache->set($mkey, $arySendInfo);

	        if (rand(1,1000) == 777) {
	        //if (777) {
	        	info_log($uid, 'event_send_rose_'.date('Ymd'));
	        }
	        $resultVo['status'] = 1;
	        return array('result' => $resultVo, 'sendSucceedUid' => $aryAddFids);
        }
        else {
        	$resultVo['status'] = -1;
        	$resultVo['content'] = "對不起，因為以下原因羽毛贈送失敗：\n1）你 已經給TA送過羽毛了\n2）選擇的人數過多\n3）羽毛已送完\n請檢查，或明天再來";
        	return array('result' => $resultVo);
        }
	}

	/**
	 * beg rose
	 * @param : integer uid
	 * @param : string fids
	 * @return: array
	 */
	public static function begRose($uid, $fids)
	{
		$resultVo = array();

		if (empty($fids)) {
			$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_110';
        	return array('result' => $resultVo);
		}

		$aryFid = explode(',', $fids);
		if (empty($aryFid)) {
			$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_110';
        	return array('result' => $resultVo);
		}

		//is friend check
		foreach ($aryFid as $fid) {
			$isFriend = Hapyfish2_Platform_Bll_Friend::isFriend($uid, $fid);
			if (!$isFriend) {
				$resultVo['status'] = -1;
	        	$resultVo['content'] = 'serverWord_173';
	        	return array('result' => $resultVo);
			}
		}

		//ask for
		$content = '我在參加快樂島主七夕情人節“鵲橋之戀”活動，幫忙送我一根鵲羽吧！';
		foreach ($aryFid as $fid) {
			Hapyfish2_Island_Bll_Remind::addRemind($uid, $fid, $content, 0);
		}
		$feed = Hapyfish2_Island_Bll_Activity::send('QIXI_WANT', $uid);
		$resultVo['status'] = 1;
		return array('result' => $resultVo, 'feed' => isset($feed)?$feed:'');
	}


	/**
	 * buy rose
	 * @param : integer uid
	 * @param : integer num
	 * @return: array
	 */
	public static function buyRose($uid, $num)
	{
		if ($num<=0) {
			$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_148';
        	return array('result' => $resultVo);
		}
		$price = 1;

		//get user gold
		$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
        if (!$balanceInfo) {
        	$resultVo['status'] = -1;
        	$resultVo['content'] = 'serverWord_1002';
        	return array('result' => $resultVo);
        }

        //is gold enough
		$userGold = $balanceInfo['balance'];
		if ($userGold < $price*$num) {
			$resultVo['status'] = -3;
			$resultVo['content'] = 'serverWord_140';
			return array('result' => $resultVo);
		}

		$isVip = $balanceInfo['is_vip'];
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$userLevel = $userLevelInfo['level'];
	    $goldInfo = array(	'uid'=>$uid,
									'cost'=>$price*$num,
									'summary'=>'购买鹊羽',
									'user_level'=>$userLevel,
									'cid'=>'10001',
									'num'=>$num);
		$ok = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		if ($ok) {
			//add rose by gold
			$ok2 = self::incRose($uid, $num, 3);
			if ($ok2) {
				$resultVo['status'] = 1;
				$resultVo['goldChange'] = -($price*$num);
				info_log($uid . ',' . $num, 'event_buy_rose_'.date('Ymd'));
				return array('result' => $resultVo);
			} else {
				//cancel consume
				$resultVo['status'] = -1;
				$resultVo['content'] = 'serverWord_148';
				return array('result' => $resultVo);
			}
		} else {
			info_log(Zend_Json::encode($goldInfo), 'payorder_failure');
			$resultVo['status'] = -1;
			$resultVo['content'] = 'serverWord_148';
			return array('result' => $resultVo);
		}
	}

	/**
	 * gain pisces
	 *
	 * @param integer $uid
	 * @return array
	 */
	public static function gainPisces($uid)
	{
		$resultVo = array();
		$resultVo = array('status' => 1);

		//has already gained
		if (self::hasGainPisces($uid)) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_150';
			return array('result' => $resultVo);
		}
		$gainItemId = '75532';//双鱼座
		$now = time();
		//send item to user
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		$compensation->setItem($gainItemId, 1);
		$ok = $compensation->sendOne($uid, self::TXT003);

		if ($ok) {
			try {
				$dal = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
				$dal->update($uid, array('gain_pisces'=>$now));
			}
			catch (Exception $e) {
				info_log($e->getMessage(), 'Event_Valentine_Err');
			}
			//cache feed
			$info = Hapyfish2_Platform_Bll_User::getUser($uid);
			$userName = $info['name'];
			$itemName = self::TXT015;
			$mkey = self::CACHE_KEY_EXCHANGE;
			$eventFeed = Hapyfish2_Cache_Factory::getEventFeed();
			$eventFeed->insert($mkey, array($uid, $userName, $itemName, $now));
		}
		else {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			return array('result' => $resultVo);
		}

		//create result vo
		return array('result' => $resultVo, 'itemId' => $gainItemId);
	}

	/**
	 * has gained pisces
	 *
	 * @param integer $uid
	 * @return array
	 */
	public static function hasGainPisces($uid)
	{
		try {
			$dalValentine = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
			$rowValentine = $dalValentine->get($uid);
			$gained = !empty($rowValentine['gain_pisces']);
		}
		catch (Exception $e) {
			info_log('hasGainPisces error', 'Event_Valentine_Err');
			info_log($e->getMessage(), 'Event_Valentine_Err');
			$gained = false;
		}
		return $gained;
	}

	/**
	 * increase user rose
	 *
	 * @param integer $uid
	 * @param integer $num
	 * @param integer $method [1-login daily, 2-friend gift, 3-gold buy]
	 * @return array
	 */
    public static function incRose($uid, $num=1, $method)
    {
    	if ((int)$num <= 0) {
    		return false;
    	}

    	try {
    		$dal = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
			$row = $dal->get($uid);
			$info = array();
			$info['rose'] = $num;
			if ($method>=1 && $method<=3) {
				$info['rose_tot'.$method] = $num;
			}
			if (empty($row)) {
				$roseNum = $num;
				$info['uid'] = $uid;
				$dal->insert($uid, $info);
			}
			else {
				$roseNum = $row['rose_tot1'] + $row['rose_tot2'] + $row['rose_tot3'] + $num;
				$dal->updateByMultipleField($uid, $info);
			}
    	}
		catch (Exception $e) {
			info_log('incRose error', 'Event_Valentine_Err');
			info_log($e->getMessage(), 'Event_Valentine_Err');
			return false;
		}
		self::_synRoseRank($uid, $roseNum);
		return true;
    }


	/**
	 * decrease user rose
	 *
	 * @param integer $uid
	 * @param integer $num
	 * @return array
	 */
    public static function decRose($uid, $num)
    {
    	if ((int)$num <= 0) {
    		return false;
    	}
    	try {
    		$dal = Hapyfish2_Island_Event_Dal_Valentine::getDefaultInstance();
			$row = $dal->get($uid);
			if (empty($row)) {
				return false;
			}
			if ($row['rose'] < $num) {
				return false;
			}
			$dal->updateByField($uid, 'rose', 0-$num);
    	}
		catch (Exception $e) {
			info_log('decRose error', 'Event_Valentine_Err');
			info_log($e->getMessage(), 'Event_Valentine_Err');
			return false;
		}
		return true;
    }

    private static function _synRoseRank($uid, $roseNum)
    {
		//local cache
    	$locKey = 'island:event:roserank:1';
    	$loc = Hapyfish2_Cache_LocalCache::getInstance();
    	$minLine = $loc->get($locKey);
    	//memcache
    	$mkey = self::CACHE_KEY_RANK;
		$eventRank = Hapyfish2_Cache_Factory::getEventRank();
    	if (empty($minLine)) {
    		$minLine = 100;
	    	$lstRank = $eventRank->get($mkey);
			if (!empty($lstRank)) {
				$cnt = count($lstRank);
				if ($cnt >=100) {
					$minLine = $lstRank[$cnt - 1][1];
				}
			}
			$loc->set($locKey, $minLine, 600);
    	}

	    if ($roseNum <= $minLine) {
			return;
    	}

		$rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
		$info = array($rowUser['name'], $roseNum, $uid);
    	$eventRank->insert($mkey, $info);
		return;
    }

}