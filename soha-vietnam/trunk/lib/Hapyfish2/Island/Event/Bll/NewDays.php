<?php

/**
 * Event NewDays
 *
 * @package    Island/Event/Bll
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/12/23    zhangli
*/
class Hapyfish2_Island_Event_Bll_NewDays
{
	/**const TXT001 = '石頭錘子每天只能使用10次';
	const TXT002 = '元旦砸蛋';
	const TXT003 = '恭喜你獲得：';
	const TXT004 = '卡片不足,不能兌換!需要集齊"';
	const TXT005 = '2012"卡片!';
	const TXT006 = '元旦快樂"卡片!';
	const TXT007 = '2012元旦快樂"卡片!';*/
	const TXT001 = 'Chỉ được dùng búa đá 10 lần/ngày';
	const TXT002 = 'Đập trứng Năm mới';
	const TXT003 = 'Bạn nhận được：';
	const TXT004 = 'Thẻ không đủ, không thể đổi! Cần"';
	const TXT005 = 'Thẻ "2012"!';
	const TXT006 = 'Thẻ "Happy New Year"';
	const TXT007 = 'Thẻ "2012 Happy New Year"';
	
	/**
	 * @元旦初始化
	 * @param int $uid
	 * @return Array
	 */
	public static function newDaysInit($uid)
	{
		$cardCounts = array();
		$cids = array(133241, 133041, 133141, 133341, 133841, 133941, 134041);
		
		//获取用户拥有的字(卡片)
		$lstCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);

		$cardVo = array();
		if ($lstCard) {
			foreach ($lstCard as $cid => $item) {
				$cardVo[$cid] = $item['count'];
			}
		}

		foreach ($cids as $cid) {
			if (!$cardVo[$cid] || (!isset($cardVo[$cid]))) {
				$cardCounts[] = 0;
			} else {
				foreach ($cardVo as $keyCid => $valCid) {
					if ($cid == $keyCid) {
						$cardCounts[] = $valCid;
					}
				}
			}
		}
		
		$result = array('status' => 1);
		$resultVo = array('result' => $result, 'cardCounts' => $cardCounts);
		
		return $resultVo;
	}
	
	/**
	 * @砸蛋
	 * @param int $uid
	 * @param int $eid
	 * @return Array
	 */
	public static function newDaysDropEgg($uid, $eid)
	{
		$result = array('status' => -1);

		$eid += 1;
		
		//锤子的ID只有三个
		if (!in_array($eid, array(1, 2, 3))) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);
			return $resultVo;
		}
		
		//获得当前锤子砸中物品的信息
		$dataCo = Hapyfish2_Island_Event_Cache_NewDays::getEggData($eid);
		if (!$dataCo) {
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);
			return $resultVo;
		}
		
		$dataArr = json_decode($dataCo['item_odds']);
		
		foreach ($dataArr as $data) {
			$aryRandOdds[$data[0]] = $data[1];
		}

		$needCoin = 0;
		$needGold = 0;
		$gainKeyOr = 'false';
		if (($eid == 1) || ($eid == 2)) {
			if ($eid == 1) {
				$needCoin = 500;
			} else {
				$needCoin = 3000;
			}
			//获取木锤子使用次数
			//$woodenHammerNum = Hapyfish2_Island_Event_Cache_NewDays::getWoodenHammerNum($uid);	

			//木锤子每天只能使用20次
//			if ($woodenHammerNum >= 20) {
//				$result['content'] = self::TXT001;
//				$resultVo = array('result' => $result);
//				return $resultVo;
//			}

			//获得用户coin
			$userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);
			
			//金币不足
			if($userCoin < $needCoin) {
				$result['content'] = 'serverWord_137';
	    		$resultVo = array('result' => $result);
	    		return $resultVo;
			}
		} else {
	    	//获得用户gold
			$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);
			
//			if ($eid == 2) {
//				$needGold = 1;
//			} else {
				$needGold = 3;
//			}
			
			//宝石不足
			if ($userGold < $needGold) {
	    		$result['content'] = 'serverWord_140';
	    		$resultVo = array('result' => $result);
	    		return $resultVo;
			}
		}
	
		$nowTime = time();
	
		//随机取得获得的字样
		$gainKeyOr = self::randomKeyForOdds($aryRandOdds);

		//没有随机出卡片
		if ($gainKeyOr === 'false') {
    		$result['content'] = 'serverWord_101';
    		$resultVo = array('result' => $result);
    		return $resultVo;
		}
				
		$gainNum = 0;
		$gainCid = $gainKeyOr;
		
		//读取CID
		$cardID = Hapyfish2_Island_Event_Cache_NewDays::getCardID($gainCid);
		if (!$cardID) {
    		$result['content'] = 'serverWord_101';
    		$resultVo = array('result' => $result);
    		return $resultVo;
		}
		
		foreach ($dataArr as $dv) {
			if ($gainKeyOr == $dv[0]) {
				$gainData = $dv;
				break;
			}
		}
		
		$gainNum = $gainData[2];
		$gainCid = $cardID;
		
		//发放奖励
		$compensation = new Hapyfish2_Island_Bll_Compensation();
	
		$compensation->setItem($gainCid, $gainNum);
		$ok = $compensation->sendOne($uid, self::TXT003);

		if ($ok) {
			//统计每日获得卡片数量
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('newDaysDropEggOK', array($uid, 'card-' . $gainKeyOr));
			
			if (($eid == 1) || ($eid == 2)) {
				//标记用户使用过金币锤子
				//Hapyfish2_Island_Event_Cache_NewDays::addWoodenHammerNum($uid);
				
				$ok2 = Hapyfish2_Island_HFC_User::decUserCoin($uid, $needCoin);
		
				if ($ok2) {
					//add log
					$summary = self::TXT002;
					Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $needCoin, $summary, $nowTime);
			        try {
						Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $needCoin);

						//task id 3012,task type 14
						$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
						if ( $checkTask['status'] == 1 ) {
							$result['finishTaskId'] = $checkTask['finishTaskId'];
						}
			        } catch (Exception $e) {}
				}
			} else {
				//获取用户等级
				$userLevelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
				$userLevel = $userLevelInfo['level'];
				
				//扣除宝石
				$goldInfo = array('uid' => $uid,
								'cost' => $needGold,
								'summary' => self::TXT002,
								'user_level' => $userLevel,
								'create_time' => $nowTime,
								'cid' => '',
								'num' => '');
		
		        $ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);
		
				if ($ok2) {
					try {
						Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $goldInfo['cost']);
		
						//task id 3068,task type 19
						$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
						if ( $checkTask['status'] == 1 ) {
							$result['finishTaskId'] = $checkTask['finishTaskId'];
						}
			        } catch (Exception $e) {}
				}
			}
		}
		
		//统计使用的锤子数量
		$logger = Hapyfish2_Util_Log::getInstance();
		$logger->report('newDaysHammer', array($uid, 'hammer-' . $eid));
		
		$result['status'] = 1;
		$result['coinChange'] = -$needCoin;
		$result['goldChange'] = -$needGold;
		$result['itemBoxChange'] = true;
    	$resultVo = array('result' => $result, 'cardId' => $gainCid, 'count' => $gainNum);
	    		
    	return $resultVo;
	}
	
	
	/**
	 * @随机取ID
	 * @param Array $aryKeys
	 * @return int
	 */
	private static function randomKeyForOdds($aryKeys)
	{
		$tot = 0;
		$aryTmp = array();
		foreach ($aryKeys as $aryKey => $odd) {
			$tot += $odd;
			$aryTmp[$aryKey] = $tot;
		}

		$rnd = mt_rand(1, $tot);

		foreach ($aryTmp as $key => $value) {
			if ($rnd <= $value) {
				return $key;
			}
		}
	}
	
	/**
	 * @兑换礼包
	 * @param int $uid
	 * @param int $pid
	 * @return Array
	 */
	public static function newDaysToConvert($uid, $pid)
	{
		$result = array('status' => -1);
		
		$pid += 1;
		
		//礼包ID只有3个
		if (!in_array($pid, array(1, 2, 3))) {
    		$result['content'] = 'serverWord_101';
    		$resultVo = array('result' => $result);
    		return $resultVo;
		}
		
		//获取用户拥有的字(卡片)
		$lstCard = Hapyfish2_Island_HFC_Card::getUserCard($uid);

		$cardVo = array();
		if ($lstCard) {
			foreach ($lstCard as $cid => $item) {
				$cardVo[$cid] = $item['count'];
			}
		}
		
		//获取兑换数据
		$dataVo = Hapyfish2_Island_Event_Cache_NewDays::getData($pid);
		if (!$dataVo) {
    		$result['content'] = 'serverWord_101';
    		$resultVo = array('result' => $result);
    		return $resultVo;
		}
	
		//判断用户卡片是否足够
		foreach ($dataVo['item'] as $key => $data) {
			if (!$cardVo[$key] || (!isset($cardVo[$key]))) {
				if ($pid == 1) {
					$content = self::TXT005;
				} else if ($pid == 2){
					$content = self::TXT006;
				} else {
					$content = self::TXT007;
				}
				
	    		$result['content'] = self::TXT004 . $content;
				$resultVo = array('result' => $result);
				return $resultVo;
			}
			
			foreach ($cardVo as $cidKey => $cidVal) {
				if ($key == $cidKey) {
					if ($cidVal < $data) {
						if ($pid == 1) {
							$content = self::TXT005;
						} else if ($pid == 2){
							$content = self::TXT006;
						} else {
							$content = self::TXT007;
						}
						
			    		$result['content'] = self::TXT004 . $content;
    					$resultVo = array('result' => $result);
    					return $resultVo;
					}
				}
			}
		}
		
		//发放奖励
		$compensation = new Hapyfish2_Island_Bll_Compensation();
		
		$coin = 0;
		foreach ($dataVo['list'] as $listKey => $listVal) {
			//ID等于1的是金币
			if ($listKey == 1) {
				$coin += $listVal;
				$compensation->setCoin($listVal);
			} else {
				$compensation->setItem($listKey, $listVal);
			}
		}
		
		$ok = $compensation->sendOne($uid, self::TXT003);

		if ($ok) {
			//减少用户卡片
			foreach ($dataVo['item'] as $needCid => $needNum) {
				$userOK = Hapyfish2_Island_HFC_Card::useUserCard($uid, $needCid, $needNum);
				if (!$userOK) {
					$resultVo['content'] = 'serverWord_110';
					return array('resultVo' => $resultVo);
				}	
			}

			//记录每日各种礼包兑换次数
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('newDaysToConvertOK-' . $pid, array($uid));
		} else {
			info_log($uid, 'newDaysToConvertErr-' . $pid);
			
			$result['content'] = 'serverWord_101';
			$resultVo = array('result' => $result);
			return $resultVo;
		}
		
		$result['status'] = 1;
		$result['coinChange'] = $coin;
		$result['itemBoxChange'] = true;
		$resultVo['result'] = $result;
		
		return $resultVo;
	}

}