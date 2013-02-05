<?php

class Hapyfish2_Island_Event_Bll_AmoyGold
{
	public static function amoyGoldInit($uid)
	{
		$result = array('status' => -1);

		$URL = 'http://bangpai.taobao.com/group/thread/570689-265305341.htm';
		$id = 1; //第一期

		//获取淘金币数量
//		$keyAmoy = 'i:e:amoygold' . $uid;
		$cacheAmoy = Hapyfish2_Cache_Factory::getMC($uid);
//		$amoyGold = $cacheAmoy->get($keyAmoy);

//		if ($amoyGold === false) {
			$context = Hapyfish2_Util_Context::getDefaultInstance();
        	$puid = $context->get('puid');
        	$session_key = $context->get('session_key');
			$taobao = Taobao_Rest::getInstance();
			$taobao->setUser($puid, $session_key);
        	$amoyGold = $taobao->jianghu_getCoinsSum();

			if (!$amoyGold) {
				$amoyGold = 0;
			}

//			if ($amoyGold) {
//				$cacheAmoy->set($keyAmoy, $amoyGold, 3600 * 24 * 10);
//			} else {
//				$amoyGold = 0;
//			}
//		}

		//获取物品列表
		//1:coin,2:gold,3:starfish,4:cid
		$key = 'i:e:amoygold:gift' . $uid;
		$list = $cacheAmoy->get($key);

		if ($list === false) {
			$db = Hapyfish2_Island_Event_Dal_AmoyGold::getDefaultInstance();
			try {
				$dataVo = $db->getGiftInit($id);
			} catch (Exception $e) {
			}

			if (!$dataVo) {
				$result['content'] = 'serverWord_101';
				$resultVo['result'] = $result;

				return $resultVo;
			}

			//每次只返回一个东西
			foreach ($dataVo as $data) {
				$listArr = array();

				$listArr['needTCoin'] = $data['item_need'];
				$listArr['hasCount'] = $data['item_limit'];
				$listArr['name'] = $data['item_name'];

				if (1 == $data['item_type']) {
					$listArr['coin'] = $data['item_num'];
				} else if (2 == $data['item_type']) {
					$listArr['gem'] = $data['item_num'];
				} else if (3 == $data['item_type']) {
					$listArr['starfish'] = $data['item_num'];
				} else {
					$listArr['itemId'] = $data['item_id'];
					$listArr['itemNum'] = $data['item_num'];
				}

				$list[] = $listArr;
			}

			$cacheAmoy->set($key, $list);
		}

		//换购数量限制缓存时间,每天的23:59:59清空
		$logDate = date('Y-m-d');
		$dtDate = $logDate . ' 23:59:59';
		$endTime = strtotime($dtDate);

		//获取用户可以兑换次数,每天的0点清空前一天的领取次数
		$keyNum = 'i:e:amoygold:num:' . $uid;
		$itemNum = $cacheAmoy->get($keyNum);

		if ($itemNum === false) {
			foreach ($list as $listVa) {
				$itemNum[] = $listVa['hasCount'];
			}

			$cacheAmoy->set($keyNum, $itemNum, $endTime);
		} else {
			foreach ($list as $keyConnt => $value) {
				$list[$keyConnt]['hasCount'] = $itemNum[$keyConnt];
			}
		}

		$resultVo = array('result' => array('status' => 1),
						  'tCoin' => $amoyGold,
						  'url' => $URL,
						  'list' => $list);

		return $resultVo;
	}

	public static function getAmoyGoldGift($uid, $idx)
	{
		$result = array('status' => -1);

		if (!$idx) {
			$result['content'] = 'serverWord_101';
			$resultVo['result'] = $result;

			return $resultVo;
		}

		//获取淘金币数量
//		$keyAmoy = 'i:e:amoygold' . $uid;
		$cacheAmoy = Hapyfish2_Cache_Factory::getMC($uid);
//		$amoyGold = $cacheAmoy->get($keyAmoy);
//
//		if ($amoyGold === false) {
//			$context = Hapyfish2_Util_Context::getDefaultInstance();
//        	$puid = $context->get('puid');
//        	$session_key = $context->get('session_key');
//			$taobao = Taobao_Rest::getInstance();
//			$taobao->setUser($puid, $session_key);
//        	$amoyGold = $taobao->jianghu_getCoinsSum();
//
//			if (!$amoyGold) {
//				$amoyGold = 0;
//			}

//			if ($amoyGold) {
//				$cacheAmoy->set($keyAmoy, $amoyGold, 3600 * 24 * 10);
//			} else {
//				$amoyGold = 0;
//			}
//		}

		$trueIdx = $idx - 1;

		//获取物品列表
		//1:coin,2:gold,3:starfish,4:cid
		$key = 'i:e:amoygold:gift' . $uid;
		$list = $cacheAmoy->get($key);

		if ($list === false) {
			$db = Hapyfish2_Island_Event_Dal_AmoyGold::getDefaultInstance();
			try {
				$dataVo = $db->getGiftInit($trueIdx);
			} catch (Exception $e) {
			}

			if (!$dataVo) {
				$result['content'] = 'serverWord_101';
				$resultVo['result'] = $result;

				return $resultVo;
			}

			//每次只返回一个东西
			foreach ($dataVo as $data) {
				$listArr = array();

				$listArr['needTCoin'] = $data['item_need'];
				$listArr['hasCount'] = $data['item_limit'];
				$listArr['name'] = $data['item_name'];

				if (1 == $data['item_type']) {
					$listArr['coin'] = $data['item_num'];
				} else if (2 == $data['item_type']) {
					$listArr['gem'] = $data['item_num'];
				} else if (3 == $data['item_type']) {
					$listArr['starfish'] = $data['item_num'];
				} else {
					$listArr['itemId'] = $data['item_id'];
					$listArr['itemNum'] = $data['item_num'];
				}

				$list[] = $listArr;
			}

			$cacheAmoy->set($key, $list);
		}

		//换购数量限制缓存时间,每天的23:59:59清空
		$logDate = date('Y-m-d');
		$dtDate = $logDate . ' 23:59:59';
		$endTime = strtotime($dtDate);

		//获取用户可以兑换次数,每天的0点清空前一天的领取次数
		$keyNum = 'i:e:amoygold:num:' . $uid;
		$itemNum = $cacheAmoy->get($keyNum);

		if ($itemNum === false) {
			foreach ($list as $listVa) {
				$itemNum[] = $listVa['hasCount'];
			}

			$cacheAmoy->set($keyNum, $itemNum, $endTime);
		} else {
			foreach ($list as $keyConnt => $value) {
				$list[$keyConnt]['hasCount'] = $itemNum[$keyConnt];
			}
		}

		$trueData = $list[$trueIdx];

		//兑换次数为0
		if ($trueData['hasCount'] <= 0) {
			$result['content'] = '对不起，您今天对该物品的兑换次数已经用完，请兑换其他物品';
			$resultVo['result'] = $result;

			return $resultVo;
		}

//		//淘金币不足
//		if ($trueData['needTCoin'] > $amoyGold) {
//			$result['content'] = '对不起，您的淘金币不足，不能兑换此物品';
//			$resultVo['result'] = $result;
//
//			return $resultVo;
//		}

		$result = array('status' => 1);
		$compensation = new Hapyfish2_Island_Bll_Compensation();

		//扣除玩家淘金币
		$context = Hapyfish2_Util_Context::getDefaultInstance();
        $puid = $context->get('puid');
        $session_key = $context->get('session_key');
		$taobao = Taobao_Rest::getInstance();
		$taobao->setUser($puid, $session_key);
        $ok = $taobao->jianghu_coinsConsume((int)$trueData['needTCoin']);

        if ($ok == 'true') {
        	if (isset($trueData['coin'])) {
				$compensation->setCoin($trueData['coin']);
				$result['coinChange'] = $trueData['coin'];
			}

			if(isset($trueData['gem'])) {
				$compensation->setGold($trueData['gem']);
				$result['goldChange'] = $trueData['gem'];
			}
			if(isset($trueData['starfish'])) {
				$compensation->setStarfish($trueData['starfish']);
				$result['starfishChange'] = $trueData['starfish'];
			}

			if (isset($trueData['itemId'])) {
				$compensation->setItem($trueData['itemId'], $trueData['itemNum']);
			}

			$compensation->sendOne($uid, '恭喜你用' . $trueData['needTCoin'] . '淘金币兑换了：');

			//重新计算可兑换次数
			foreach ($itemNum as $numKey => $numVa) {
				if ($trueIdx == $numKey) {
					$numVa--;
					$itemNum[$numKey] = $numVa;
				}

				$cacheAmoy->set($keyNum, $itemNum, $endTime);
			}

			info_log('uid:' . $uid . ',name:' . $trueData['name'] . ',decTCoin:'. $trueData['needTCoin'], 'Tcoin');
        } else {
        	info_log('uid:' . $uid, 'TcoinFail');

			$result['status'] = -1;
        	$result['content'] = '不好意思 您今天兑换的淘金币额度已经达到400上限 请明天再来吧！';
			$resultVo['result'] = $result;

			return $resultVo;

        	info_log('uid:' . $uid . ',name:' . $trueData['name'] . ',decTCoin:'. $trueData['needTCoin'], 'TcoinFail');
        }

//        $newAmoyGold = $amoyGold - $trueData['needTCoin'];
//        $cacheAmoy->set($keyAmoy, $newAmoyGold, 3600 * 24 * 10);

		$resultVo = array('result' => $result);
		return $resultVo;
	}
}