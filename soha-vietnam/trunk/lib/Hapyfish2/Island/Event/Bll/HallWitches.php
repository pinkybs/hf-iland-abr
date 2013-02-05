<?php

/**
 * Event HallWitches
 *
 * @package    Island/Event/Bll
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/10/18    zhangli
*/
class Hapyfish2_Island_Event_Bll_HallWitches
{
	const TXT001 = '获取万圣节卡牌';
	const TXT002 = '对不起，您的卡片不足，不能兑换此物品~';
	const TXT003 = '恭喜你获得：';
	const TXT004 = '对不起，倒计时没结束，不能领取免费卡牌！';
	const TXT005 = '卡片足够，不需要补齐！';
	const TXT006 = '恭喜你通过补齐兑换获得：';
	const TXT007 = '补齐万圣节卡牌';

	/**
	 * @获取初始化信息
	 * @param int $uid
	 * @return array
	 */
	public static function halloWeenInit($uid)
	{
		$result = array('status' => -1);

		$nowTime = time();

		$url = 'http://bangpai.taobao.com/group/thread/570689-266905597.htm';

		//计算用户卡牌数量,加入cardArr
		$cardArr = Hapyfish2_Island_Event_Cache_HallWitches::resetListArr($uid);

		//兑换列表
		$giftList = Hapyfish2_Island_Event_Cache_HallWitches::getHallList();

		//下一次领取倒计时
		$lastTime = Hapyfish2_Island_Event_Cache_HallWitches::getLastTime($uid, $nowTime);

		//获取用户是否可以领取状态
		$cardChance = Hapyfish2_Island_Event_Cache_HallWitches::getCardChance($uid);

		$result['status'] = 1;

		$resultVo = array('result' => $result,
							'URL' => $url,
							'countdown' => $lastTime,
							'cardChance' => $cardChance,
							'giftList' => $giftList,
							'cardList' => $cardArr);

		return $resultVo;
	}

	/**
	 * @花费宝石刷新一次领取卡牌的机会
	 * @param int $uid
	 * @return bool
	 */
	 public static function refrushCardChance($uid)
	 {
		$result = array('status' => -1);

		$nowTime = time();

//		//判断是否为免费卡片
//		$lastTime = Hapyfish2_Island_Event_Cache_HallWitches::getLastTime($uid, $nowTime);
//
//		//可以领取免费卡牌时不可以花费宝石
//		if ($lastTime == 0) {
//			$result['content'] = 'serverWord_101';
//			$resultVo = array('result' => $result);
//
//			return $resultVo;
//		}

		//获取用户是否可以领取状态
		$cardChance = Hapyfish2_Island_Event_Cache_HallWitches::getCardChance($uid);

		//当前已经可以领取,不可以刷新状态
		if ($cardChance == 1) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//刷新领取状态需要宝石数
		$needGold = 3;

		//获得用户gold
		$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);

		//宝石不足
		if ($userGold < $needGold) {
			$result['status'] = 2;
			$result['content'] = 'serverWord_140';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//刷新领取机会
		Hapyfish2_Island_Event_Cache_HallWitches::refrushCardChance($uid, 1);

		//获取用户等级
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$userLevel = $userLevelInfo['level'];

		//扣除宝石
		$goldInfo = array('uid' => $uid,
						'cost' => $needGold,
						'summary' => self::TXT001,
						'user_level' => $userLevel,
						'create_time' => $nowTime,
						'cid' => '11111111',
						'num' => 1);

        $ok = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);

		if ($ok) {
			try {
				Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $goldInfo['cost']);

				//task id 3068,task type 19
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
				if ( $checkTask['status'] == 1 ) {
					$result['finishTaskId'] = $checkTask['finishTaskId'];
				}
	        } catch (Exception $e) {}
		}

		$result['status'] = 1;
		$result['goldChange'] = -$needGold;
		$resultVo = array('result' => $result);

		return $resultVo;
	 }

	/**
	 * @选择卡片
	 * @param int $uid
	 * @return array
	 */
	public static function hallChooseCard($uid)
	{
		$result = array('status' => -1);

		$nowTime = time();

		$cardChance = 0;

		//判断是否为免费卡片
		$lastTime = Hapyfish2_Island_Event_Cache_HallWitches::getLastTime($uid, $nowTime);

		if ($lastTime > 0) {
			//获取用户是否使用宝石刷新状态--0没有,1刷新了
			$cardChance = Hapyfish2_Island_Event_Cache_HallWitches::getCardChance($uid);
			if ($cardChance == 0) {
				$result['content'] = self::TXT004;
				$resultVo = array('result' => $result);

				return $resultVo;
			}
		}

		//获取卡片ID
		$cardID = self::randomKeyForOdds();

		if ($cardID === false) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//卡片写入用户缓存
		Hapyfish2_Island_Event_Cache_HallWitches::incCard($uid, $cardID);

		if ($cardChance == 1) {
			//清除用户领取宝石卡牌状态
			Hapyfish2_Island_Event_Cache_HallWitches::refrushCardChance($uid, 0);
		} else {
			//记录免费牌时间
			Hapyfish2_Island_Event_Cache_HallWitches::incFreeTime($uid, $nowTime);
		}

		$report = array($uid, $cardID, $cardChance);

		//report log
		$logger = Hapyfish2_Util_Log::getInstance();
		$logger->report('409', $report);

		$result['status'] = 1;
		$resultVo = array('result' => $result, 'cardId' => $cardID);

		return $resultVo;
	}

	/**
	 * @获取兑换物品列表
	 * @param int $uid
	 * @return array
	 */
	public static function exchangeList($uid)
	{
		$result = array('status' => -1);

		$list = Hapyfish2_Island_Event_Cache_HallWitches::exchangeList();
		//没有兑换列表和兑换数值
		if ($list === false) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		$result['status'] = 1;
		$resultVo = array('result' => $result, 'list' => $list);

		return $resultVo;
	}

	/**
	 * @兑换物品
	 * @param int $uid
	 * @param int $groupId
	 * @return array
	 */
	public static function toExchange($uid, $groupId)
	{
		$result = array('status' => -1);

		//兑换ID不在兑换范围内
		if (!in_array($groupId, array(0, 1, 2, 3, 4, 5, 6, 7, 8))) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//获取兑换信息
		$list = Hapyfish2_Island_Event_Cache_HallWitches::resetHallList();

		//没有兑换列表和兑换数值
		if ($list === false) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//获取要换换的物品数据
		foreach ($list as $key => $data) {
			if ($key == $groupId) {
				$statistics = $data;
				break;
			}
		}

		//获取用户拥有的卡牌数量
		$card = Hapyfish2_Island_Event_Cache_HallWitches::getCard($uid);

		$need_data = array();
		$need_data = explode(',', $statistics['need_data']);
		foreach ($need_data as $dakey => $davalue) {
			$daNeed = explode('*', $davalue);

			$statistics['needData'][$dakey]['cardid'] = $daNeed[0];
			$statistics['needData'][$dakey]['maxCount'] = $daNeed[1];
		}

		//判断卡片数量数否足够兑换
		foreach ($statistics['needData'] as $needData) {
			foreach ($card as $cardID => $cardNum) {
				if ($needData['cardid'] == $cardID) {
					//判断卡牌数量是否足够
					if ($needData['maxCount'] > $cardNum) {
						$result['content'] = self::TXT002;
						$resultVo = array('result' => $result);

						return $resultVo;
					}
					break;
				}
			}
		}

		//发东西
		$compensation = new Hapyfish2_Island_Bll_Compensation();

		if ($statistics['coin'] > 0) {
			$compensation->setCoin($statistics['coin']);
		}
		if($statistics['gem'] > 0){
			$compensation->setGold($statistics['gem']);
		}
		if($statistics['starfish'] > 0){
			$compensation->setStarfish($statistics['starfish']);
		}

		if ($statistics['itemId'] && $statistics['itemNum']) {
			$compensation->setItem($statistics['itemId'], $statistics['itemNum']);
		}

		$compensation->sendOne($uid, self::TXT003);

		//扣除玩家卡牌
		foreach ($statistics['needData'] as $needData) {
			foreach ($card as $cardID => $cardNum) {
				if ($needData['cardid'] == $cardID) {
					$card[$cardID] = $cardNum - $needData['maxCount'];
					break;
				}
			}
		}

		//写入缓存
		Hapyfish2_Island_Event_Cache_HallWitches::decCard($uid, $card);

		$report = array($uid, $groupId, 0);

		//report log
		$logger = Hapyfish2_Util_Log::getInstance();
		$logger->report('406', $report);

        //send feed
        $feed = '';
//        try {
//        	//$message = array('sendname' => $saleName);
//	        $feed = Hapyfish2_Island_Bll_Activity::send('HALL_EXCHANGE', $uid);
//        } catch (Exception $e) {}

        $result['status'] = 1;
        $result['coinChange'] = $statistics['coin'];
        $result['goldChange'] = $statistics['gem'];
        $result['itemBoxChange'] = true;
        $result['feed'] = $feed;

		$resultVo = array('result' => $result);

		return $resultVo;
	}

	/**
	 * @通过补齐卡片兑换
	 * @param int $uid
	 * @return array
	 */
	 public static function replenishCard($uid, $groupId)
	 {
		$result = array('status' => -1);

		//兑换ID不在兑换范围内
		if (!in_array($groupId, array(0, 1, 2, 3, 4, 5, 6, 7, 8))) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//获取兑换信息
		$list = Hapyfish2_Island_Event_Cache_HallWitches::resetHallList();

		//没有兑换列表和兑换数值
		if ($list === false) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//获取要换换的物品数据
		foreach ($list as $key => $data) {
			if ($key == $groupId) {
				$statistics = $data;
				break;
			}
		}

		//获取用户拥有的卡牌数量
		$card = Hapyfish2_Island_Event_Cache_HallWitches::getCard($uid);

		$need_data = array();
		$need_data = explode(',', $statistics['need_data']);
		foreach ($need_data as $dakey => $davalue) {
			$daNeed = explode('*', $davalue);

			$statistics['needData'][$dakey]['cardid'] = $daNeed[0];
			$statistics['needData'][$dakey]['maxCount'] = $daNeed[1];
		}

		//判断卡片数量数否足够兑换
		$lackCard = array();
		$lackCardNum = 0;

		foreach ($statistics['needData'] as $needData) {
			foreach ($card as $cardID => $cardNum) {
				if ($needData['cardid'] == $cardID) {
					$lackNum = 0;
					//判断卡牌数量是否足够,计算缺少几张卡牌
					if ($needData['maxCount'] > $cardNum) {
						$lackNum = $needData['maxCount'] - $cardNum;
						$lackCard[$needData['cardid']] = $lackNum;
						$lackCardNum += $lackNum;
					}
					break;
				}
			}
		}

		//缺少卡牌数量大于0,扣除宝石,加入卡牌,卡牌足够时返回
		$needGold = 0;
		if ($lackCardNum > 0) {
			//补齐单个卡片需要花费6宝石
			$needGold = $lackCardNum * 6;
		} else {
			$result['content'] = self::TXT005;
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//获得用户gold
		$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);

		//宝石不足
		if ($userGold < $needGold) {
			$result['status'] = 2;
			$result['content'] = 'serverWord_140';
			$resultVo = array('result' => $result);

			return $resultVo;
		}

		//发东西
		$compensation = new Hapyfish2_Island_Bll_Compensation();

		if ($statistics['coin'] > 0) {
			$compensation->setCoin($statistics['coin']);
		}
		if($statistics['gem'] > 0){
			$compensation->setGold($statistics['gem']);
		}
		if($statistics['starfish'] > 0){
			$compensation->setStarfish($statistics['starfish']);
		}

		if ($statistics['itemId'] && $statistics['itemNum']) {
			$compensation->setItem($statistics['itemId'], $statistics['itemNum']);
		}

		$compensation->sendOne($uid, self::TXT006);

		//为玩家补齐卡牌
		foreach ($lackCard as $lackKey => $lackVa) {
			foreach ($card as $cardID => $cardNum) {
				if ($cardID == $lackKey) {
					$card[$cardID] = $cardNum + $lackVa;
					break;
				}
			}
		}

		//写入缓存
		Hapyfish2_Island_Event_Cache_HallWitches::decCard($uid, $card);

		//扣除玩家卡牌
		foreach ($statistics['needData'] as $needData) {
			foreach ($card as $cardID => $cardNum) {
				if ($needData['cardid'] == $cardID) {
					$card[$cardID] = $cardNum - $needData['maxCount'];
					break;
				}
			}
		}

		//写入缓存
		Hapyfish2_Island_Event_Cache_HallWitches::decCard($uid, $card);

		//获取用户等级
		$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
		$userLevel = $userLevelInfo['level'];

		$nowTime = time();

		//扣除宝石
		$goldInfo = array('uid' => $uid,
						'cost' => $needGold,
						'summary' => self::TXT007,
						'user_level' => $userLevel,
						'create_time' => $nowTime,
						'cid' => '11111112',
						'num' => 1);

        $ok = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);

		if ($ok) {
			try {
				Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $goldInfo['cost']);

				//task id 3068,task type 19
				$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
				if ( $checkTask['status'] == 1 ) {
					$result['finishTaskId'] = $checkTask['finishTaskId'];
				}
	        } catch (Exception $e) {}
		}

		$report = array($uid, $groupId, $needGold);

		//report log
		$logger = Hapyfish2_Util_Log::getInstance();
		$logger->report('406', $report);

        //send feed
        $feed = '';
//        try {
//        	//$message = array('sendname' => $saleName);
//	        $feed = Hapyfish2_Island_Bll_Activity::send('HALL_EXCHANGE', $uid);
//        } catch (Exception $e) {}

        $result['status'] = 1;
        $result['coinChange'] = $statistics['coin'];
        $result['goldChange'] = $statistics['gem'] - $needGold;
        $result['itemBoxChange'] = true;
        $result['feed'] = $feed;

		$resultVo = array('result' => $result);

		return $resultVo;
	 }

	/**
	 * @计算获得的卡片ID
	 *
	 * @return int
	 */
	public static function randomKeyForOdds()
	{
		$listArr = Hapyfish2_Island_Event_Cache_HallWitches::getListArr();

		foreach ($listArr as $list) {
			$aryKeys[$list['cardId']] = $list['odds'];
		}

		$tot = 0;
		$aryTmp = array();
		foreach ($aryKeys as $key => $odd) {
			$tot += $odd;
			$aryTmp[$key] = $tot;
		}

		$rnd = mt_rand(1,$tot);

		foreach ($aryTmp as $key => $value) {
			if ($rnd <= $value) {
				return $key;
			}
		}

		return false;
	}

}