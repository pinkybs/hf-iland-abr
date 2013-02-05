<?php

class Hapyfish2_Island_Event_Bll_Midautumn
{
	const TXT001 = '对不起，您今天已兑换该物品十次，请明天再来兑换';
	const TXT002 = '对不起，兑换物品不正确';
	const TXT003 = '通行证不足，不能兑换';
	const TXT004 = '恭喜你成功兑换：';
	const TXT005 = '兑换失败在重新尝试 ';
	const TXT006 = '送给你一个穿越通行证';
	const TXT007 = '我在快乐岛主参加穿越活动，帮忙送我一个通行证吧！';

	/**
	 * get user valentine info
	 *
	 * @param integer $uid
	 * @return array
	 */
	
	public static function getconfig()
	{
		return array(array('cid'=>116532,'price'=>40),array('cid'=>array(115721,115821,115921,116021,116121,116221,116321,116421),'price'=>40),array('cid'=>116932,'price'=>40),array('cid'=>117132,'price'=>40),array('cid'=>117032,'price'=>40));
	}
	public static function getUserMidautumn($uid)
	{
		$resultVo = array('status' => 1);
		try {
			$daluserpass = Hapyfish2_Island_Event_Dal_Midautumn::getDefaultInstance();
			$userpass = $daluserpass->getUserPass($uid);
			}
		catch (Exception $e) {
			$resultVo['status'] = '-1';
			$resultVo['content'] = 'serverWord_110';
			info_log('getUserValentine', 'Event_Midautumn_Err');
			info_log($e->getMessage(), 'Event_Midautumn_Err');
			return array('result' => $resultVo);
		}
		$userpass = $userpass?$userpass:0;
		$arr = self::getconfig();
		$data = array();
		$mkey = 'i:u:e:m-a:'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$data = $cache->get($mkey);
		foreach($arr as $k => $v){
			$info = Hapyfish2_Island_Cache_BasicInfo::getDetailInfo($v['cid']);
			$detail['itemId'] = $v['cid'];
			$detail['name'] = $info['name'];
			$detail['needPass'] = $v['price'];
			if(isset($data[$k+1])){
				if($data[$k+1] <= 10){
					$num = 10 - $data[$k+1];
				}else{
					$num = 0;
				}
			}else{
				$num = 10;
			}
			$detail['hasTimes'] = $num;
			$list[] = $detail;
		}
		$price = 1;
		$mkey = 'i:u:e:m-a:send:' . $uid;
        $arySendInfo = $cache->get($mkey);
        if($arySendInfo){
        	$left = 10 - count($arySendInfo);
        }else{
        	$left = 10;
        }
        $url = 'http://bangpai.taobao.com/group/thread/570689-265519741.htm';
		return array('result'=>$resultVo, 'passNum'=>$userpass, 'list'=>$list, 'price'=>$price,'canDonateNum'=>$left, 'adURL' => $url);
	}



	/**
	 * send rose
	 * @param : integer uid
	 * @param : string fids
	 * @return: array
	 */
	public static function sendPass($uid, $fids)
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
			$resultVo['status'] = 2;
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
		$mkey = 'i:u:e:m-a:send:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $arySendInfo = $cache->get($mkey);
        $aryAddFids = array();
        if ($arySendInfo) {
        	$sendNum = count($arySendInfo);
        	if ($sendNum >= 10) {
				$resultVo['status'] = -1;
	        	$resultVo['content'] = '赠送好友的数量达到上限';
	        	return array('result' => $resultVo);
        	}

        	foreach ($aryFid as $fid) {
        		if (!in_array($fid, $arySendInfo)) {
					$arySendInfo[] = $fid;
					$aryAddFids[] = $fid;
					if (count($arySendInfo) >= 10) {
						break;
					}
        		}
        	}
        }
        else {
        	$arySendInfo = $aryFid;
        	$aryAddFids = $aryFid;
        }

        if (!empty($aryAddFids)) {
        	//send to fid
	        $rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
	        foreach ($aryAddFids as $fid) {
				self::incPass($fid, 1, 1);//friend send
				$title = $rowUser['name'].self::TXT006;
		        $minifeed = array('uid' => $fid,
		                          'template_id' => 0,
		                          'actor' => $uid,
		                          'target' => $fid,
		                          'title' => array('title' => $title),
		                          'type' => 3,
		                          'create_time' => time());
		        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
	        }
	        //update cache
	        $today = date('Y-m-d');
	        $today .= ' 23:59:59';
	        $cache->set($mkey, $arySendInfo, strtotime($today));
	        $resultVo['status'] = 1;
	        return array('result' => $resultVo);
        }
        else {
        	$resultVo['status'] = 2;
        	$resultVo['content'] = "对不起，因为以下原因赠送通行证失败：\n1）你已经给TA送过通行证了\n2）选择的人数太多\n3）通行证已送完\n请检查，或明天再来";
        	return array('result' => $resultVo);
        }
	}

	/**
	 * beg rose
	 * @param : integer uid
	 * @param : string fids
	 * @return: array
	 */
	public static function begPass($uid, $fids)
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
		$content = self::TXT007;
		foreach ($aryFid as $fid) {
			Hapyfish2_Island_Bll_Remind::addRemind($uid, $fid, $content, 0);
		}
		$resultVo['status'] = 1;
		return array('result' => $resultVo);
	}


	/**
	 * buy rose
	 * @param : integer uid
	 * @param : integer num
	 * @return: array
	 */
	public static function buyPass($uid, $num)
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
			$resultVo['status'] = 2;
			$resultVo['content'] = '岛钻不足';
			return array('result' => $resultVo);
		}

		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$userLevel = $userLevelInfo['level'];
	    $goldInfo = array(	'uid'=>$uid,
									'cost'=>$price*$num,
									'summary'=>'购买通行证',
									'user_level'=>$userLevel,
									'cid'=>'10002',
									'num'=>$num);
		$ok = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		if ($ok) {
			//add rose by gold
			$ok2 = self::incPass($uid, $num, 1);
			if ($ok2) {
				$resultVo['status'] = 1;
				$resultVo['goldChange'] = -($price*$num);
				info_log($uid . ',' . $num, 'event_buy_pass_'.date('Ymd'));
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
	 * decrease user rose
	 *
	 * @param integer $uid
	 * @param integer $num
	 * @return array
	 */

    
    public static function Exchange($uid, $id)
    {
    	$resultVo['status'] = -1;
    	$arr = self::getconfig();
    	//判断兑换物品是否在列表中
    	if($id < 1 || $id >5){
    		$resultVo['content'] = self::TXT002;
    		return array('result' => $resultVo);
    	}
    	//检查兑换的物品是否超过10次
    	$mkey = 'i:u:e:m-a:'.$uid;
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$data = $cache->get($mkey);
    	if($data){
			if(isset($data[$id])){
				if($data[$id] >=10){
					$resultVo['content'] = self::TXT001;
    				return array('result' => $resultVo);
				}
			}
    	}
    	//判断玩家通行证是否够
    	$dal = Hapyfish2_Island_Event_Dal_Midautumn::getDefaultInstance();
    	$userpass = $dal->getUserPass($uid);
    	if($userpass < $arr[$id-1]['price']){
    		$resultVo['status'] = 2;
    		$resultVo['content'] = self::TXT003;
    		return array('result' => $resultVo);
    	}
		//发物品
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		if(is_array($arr[$id-1]['cid'])){
			foreach($arr[$id-1]['cid'] as $k =>$v){
				$compensation->setItem($v, 1);
			}
			$name ='城墙';
		}else {
			$compensation->setItem($arr[$id-1]['cid'], 1);
			$info = Hapyfish2_Island_Cache_BasicInfo::getDetailInfo($arr[$id-1]['cid']);
			$name = $info['name'];
		}
		
		$ok = $compensation->sendOne($uid, self::TXT004);
		//扣通行证和更改次数
		if($ok){
			$userpass = -$arr[$id-1]['price'];
			$ok2 = self::incPass($uid, $userpass, 1);
			if($data){
				if(isset($data[$id])){
					$data[$id] +=1;
				}else{
					$data[$id] =1;
				}
			}else{
				$data[$id] =1;
			}
			$ymd = date('Y-m-d');
    		$ymd = $ymd.' 23:59:59';
			$cache->set($mkey, $data, strtotime($ymd));
			$resultVo['status'] = 1;
		}else{
			$resultVo['content'] = self::TXT005;
		}
		$data['name'] = $name;
		$feed = '';
		return array('result' => $resultVo,'feed'=>$feed);
    }
    
    public static function incPass($uid, $userpass, $modth = 1)
    {
    	//更新db	
   		try {
    		$dal = Hapyfish2_Island_Event_Dal_Midautumn::getDefaultInstance();
    		$passnum = $dal->getUserPass($uid);
    		if($passnum){
    			$userpass += $passnum;
    		}
    		$dal->update($uid, $userpass);
    	}catch (Exception $e) {
			info_log('updateuserpass----'.$uid.'-----------'.$userpass, 'Event_Midautumn_Err');
			info_log($e->getMessage(), 'Event_Midautumn_Err');
			return false;
    	}
    	//db 更新成功 更新缓存次数
    	
    	return true;
    }
    
	public static function buyFriendPass($uid, $num, $fids)
		{
			if ($num<=0) {
				$resultVo['status'] = -1;
	        	$resultVo['content'] = 'serverWord_148';
	        	return array('result' => $resultVo);
			}
			$price = 1;
			if (strpos($fids, ',') === false) {
				$aryFid[] = $fids;
			}
			else {
				$aryFid = explode(',', $fids);
			}
	
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
			//get user gold
			$balanceInfo = Hapyfish2_Island_Bll_Gold::get($uid, true);
	        if (!$balanceInfo) {
	        	$resultVo['status'] = -1;
	        	$resultVo['content'] = 'serverWord_1002';
	        	return array('result' => $resultVo);
	        }
	
	        //is gold enough
			$userGold = $balanceInfo['balance'];
			if ($userGold < count($aryFid)*$price*$num) {
				$resultVo['status'] = -1;
				$resultVo['content'] = 'serverWord_140';
				return array('result' => $resultVo);
			}
	
			$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
			$userLevel = $userLevelInfo['level'];
		    $goldInfo = array(	'uid'=>$uid,
										'cost'=> count($aryFid)*$price*$num,
										'summary'=>'购买通行证',
										'user_level'=>$userLevel,
										'cid'=>'10002',
										'num'=>count($aryFid)*$num);
			$ok = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
			if ($ok) {
				//add rose by gold
				 $rowUser = Hapyfish2_Platform_Bll_User::getUser($uid);
				 foreach($aryFid as $k => $v){
					self::incPass($v, $num, 1);
					$title = $rowUser['name'].'送给你'.$num.'个通行证';
		        	$minifeed = array('uid' => $fid,
		                          'template_id' => 0,
		                          'actor' => $uid,
		                          'target' => $v,
		                          'title' => array('title' => $title),
		                          'type' => 3,
		                          'create_time' => time());
		        Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
				}
				$resultVo['status'] = 1;
				$resultVo['goldChange'] = -(count($aryFid)*$price*$num);
				info_log($uid . ',' . $num, 'event_buy_pass_'.date('Ymd'));
				return array('result' => $resultVo);
			} else {
				info_log(Zend_Json::encode($goldInfo), 'payorder_failure');
				$resultVo['status'] = -1;
				$resultVo['content'] = 'serverWord_148';
				return array('result' => $resultVo);
			}
		}
		
		public function addPassbyHelp($uid, $pid)
		{
			$cache = Hapyfish2_Cache_Factory::getMC($pid);
			$key = 'i:u:e:g:f:list'.$pid;
			$data = $cache->get($key);
			if($data){
				if(count($data) >= 20){
					return false;
				}
				if(in_array($uid, $data)){
					return false;
				}
			}
			$ok = self::incPass($pid, 1 ,2);
			if($ok){
				$data[] = $uid;
	    		$ymd = date('Y-m-d');
	    		$ymd = $ymd.' 23:59:59';
	    		$cache->set($key, $data, strtotime($ymd));
			}
			
		}
}