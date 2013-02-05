<?php

require_once(CONFIG_DIR . '/language.php');

class Hapyfish2_Island_Event_Bll_TeamBuy
{
	const TXT001 = LANG_PLATFORM_EVENT_TXT_29;
	const TXT002 = LANG_PLATFORM_EVENT_TXT_30;
	const TXT003 = LANG_PLATFORM_EVENT_TXT_29;
	const TXT004 = LANG_PLATFORM_EVENT_TXT_31;
	const TXT005 = LANG_PLATFORM_EVENT_TXT_32;

	/**
	 * 获得团购的信息
	 * @param integer $uid
	 * @param return array
	 */
	public static function teamBuy($uid)
	{
		$result = array('status' => -1);

		//获得团购信息
		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$info = $cache->get($key);

		$dalDB = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();

		if($info === false) {
			try {
				$info = $dalDB->getTeamBuyInfo();
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}

			if($info === false) {
				$result['content'] = self::TXT001;
				return $result;
			}

			$cache->set($key, $info);
		}

		//判断是否已经加入团购:没有加入attendflag=-1,已经加入attendflag=1
		$mkey = 'i:e:teambuy:buygood:' . $uid;
		$mcache = Hapyfish2_Cache_Factory::getMC($uid);
		$state = $mcache->get($mkey);

		if($state === false) {
			try {
				$state = $dalDB->getJoinTeamBuyInfo($uid);
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}
		}

		//判断用户的状态:$attendflag:-1:已加入;$buyokflag:1-已购买,-1:加入没有购买),
		if(!$state) {
			$attendflag = -1;
			$buyokflag = -1;
		} else {
			$attendflag = 1;
			$buyokflag = $state;

			$mcache->set($mkey, $state);
		}

		$nowTime = time();
		$teamBuyGift = explode('*', $info['gid']);
		$ok_time = $info['ok_time'] * 3600;
		$buy_time = $info['buy_time'] * 3600;

		$canJoinTime = $info['start_time'] + $ok_time - $nowTime;
		$canBuyTime = $info['start_time'] + $ok_time + $buy_time - $nowTime;

		//参加和购买时间已过
		if($canBuyTime <= 0 && $canJoinTime <= 0) {
			try {
				$dalDB->updateTeamBuyStatus($teamBuyGift[0]);
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}

			$cache->delete($key);

			$result['content'] =  self::TXT001;

			return $result;
		}

		$result = array('status' => 1);

		//已经参加人数
		$hasJoinNum = $dalDB->getJoinNum();
		//显示人数=加入人数+起始人数
		$joinNum = $hasJoinNum + $info['start_num'];

		//价格和类型:1-coin,2-gold
		$min_price_info = explode('*', $info['min_price']);
		$max_price_info = explode('*', $info['max_price']);

		//获得价格类型等信息
		$priceArr = self::getPriceType($joinNum, $min_price_info, $max_price_info, $info);

		//参加时间没结束
		if($canJoinTime > 0) {
			//没有加入的用户,显示参加面板,已经加入的用户,显示可以分享
			$man = array('attendflag' => $attendflag);
			$item = array(
						'cid' => (int)$teamBuyGift[0],
						'description' => $info['name'],
						'pricenow' => $priceArr['nowArr'],
						'price_jie' => $priceArr['decArr'],
						'pricebefore' => $priceArr['maxArr']
					);
			$Action = array(
						'buylen' => $canBuyTime,
						'Actlen' => $canJoinTime,
						'manattendnum' => $joinNum,
						'downmannum' => $priceArr['downNum'],
						'minnum' => (int)$info['min_num'],
						'maxnum' => (int)$info['max_num'],
						'minprice' => $priceArr['minArr']
					);
		}
		//参加时间结束,进入购买时间
		else if($canJoinTime <= 0 && $canBuyTime > 0) {
			//已经购买的人数
			$hasBuyNum = $dalDB->getHasBuyNum();

			//获得用户coin
			$userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);

			//获得用户gold
			$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);

			//参加时间已经结束
			$man = array(
						'attendflag' => $attendflag,
						'baoshinum' => (int)$userGold,
						'coinnum' => $userCoin
					);
			$item = array(
						'cid' => (int)$teamBuyGift[0],
						'description' => $info['name'],
						'pricenow' => $priceArr['nowArr'],
						'price_jie' => $priceArr['decArr'],
						'pricebefore' => $priceArr['maxArr']
					);
			$Action = array(
						'buylen' => $canBuyTime,
						'Actlen' => -1,
						'buyoknum' => (int)$hasBuyNum,
						'buyokflag' => $buyokflag
					);
		}

		$resultVo = array(
						'result' => $result,
						'item' => $item,
						'man' => $man,
						'Action' => $Action
					);

		return $resultVo;
	}

	/**
	 * 加入团购
	 * @param integer $uid
	 * @param return array
	 */
	public static function joinTeamBuy($uid)
	{
		$result = array('status' => -1);

		//获得团购信息
		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$info = $cache->get($key);

		$dalDB = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();

		if($info === false) {
			try {
				$info = $dalDB->getTeamBuyInfo();
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}

			if($info === false) {
				$result['content'] = self::TXT001;
				return $result;
			}

			$cache->set($key, $info);
		}

		//判断是否已经加入团购:没有加入attendflag=-1,已经加入attendflag=1
		$mkey = 'i:e:teambuy:buygood:' . $uid;
		$mcache = Hapyfish2_Cache_Factory::getMC($uid);
		$state = $mcache->get($mkey);

		if($state === false) {
			try {
				$state = $dalDB->getJoinTeamBuyInfo($uid);
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}
		}

		if($state) {
			$mcache->set($mkey, $state);

			$result['content'] = self::TXT002;
			return $result;
		}

		$nowTime = time();
		$teamBuyGift = explode('*', $info['gid']);
		$join_time = $info['ok_time'] * 3600;
		$buy_time = $info['buy_time'] * 3600;
		$canJoinTime = $info['start_time'] + $join_time - $nowTime;
		$canBuyTime = $info['start_time'] + $join_time + $buy_time - $nowTime;

		//参加时间已过
		if($canJoinTime <= 0) {
			if($canBuyTime <= 0) {
				try {
					$dalDB->updateTeamBuyStatus($teamBuyGift[0]);
				} catch (Exception $e) {
					info_log($e, 'teambuy_info');

					$result['content'] = 'serverWord_101';
					return $result;
				}

				$cache->delete($key);

				$result['content'] = self::TXT001;
				return $result;
			} else {
				$result['content'] = self::TXT004;

				return $result;
			}
		}

		//已经参加人数
		$hasJoinNum = $dalDB->getJoinNum();
		//页面显示人数=加入人数+起始人数
		$joinNum = $hasJoinNum + $info['start_num'];

		$teamBuyGift = explode('*', $info['gid']);
		$max_price_info = explode('*', $info['max_price']);
		$min_price_info = explode('*', $info['min_price']);

		//获得价格类型等信息
		$priceArr = self::getPriceType($joinNum, $min_price_info, $max_price_info, $info);

		//get user gold
		$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);

		//get user con
		$userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);

		$result = array('status' => 1);

		//当前价格类型coin,不足返回
		if(1 == $priceArr['nowArr']['price_type']) {
			if($priceArr['nowArr']['price'] > $userCoin) {
				$result['content'] = 'serverWord_137';

				$resultVo = array(
								'result' => $result,
								'attendflag' => -1,
								'manattendnum' => $joinNum,
								'downmannum' => $priceArr['downNum'],
								'pricenownum' => $priceArr['nowArr'],
								'pricebefore' => $priceArr['maxArr'],
								'pricejie' => $priceArr['decArr'],
							);

				return $resultVo;
			}
		}

		//当前价格类型gold,不足返回
		if(2 == $priceArr['nowArr']['price_type']) {
			if($priceArr['nowArr']['price'] > $userGold) {
				$resultVo = array(
								'result' => $result,
								'manattendnum' => $joinNum,
								'downmannum' => $priceArr['downNum'],
								'pricenownum' => $priceArr['nowArr'],
								'pricebefore' => $priceArr['maxArr'],
								'pricejie' => $priceArr['decArr'],
								'attendflag' => -1
							);

				return $resultVo;
			}
		}

		//参加团购
		try {
			$dalDB->joinTeamBuy($uid);
		} catch (Exception $e) {
			info_log($e, 'teambuy_info');

			$result['content'] = 'serverWord_101';
			return $result;
		}

		$mcache->set($mkey, -1);

		//重新计算已经加入的人数
		$joinNum += 1;

		//重新获得价格类型等信息
		$priceArr = self::getPriceType($joinNum, $min_price_info, $max_price_info, $info);

		$resultVo = array(
						'result' => $result,
						'pricenownum' => $priceArr['nowArr'],
						'pricebefore' => $priceArr['maxArr'],
						'pricejie' => $priceArr['decArr'],
						'downmannum' => $priceArr['downNum'],
						'manattendnum' => $joinNum,
						'attendflag' => 1
					);

		return $resultVo;
	}

	/**
	 * 购买物品
	 * @param integer $uid
	 * @param return array
	 */
	public static function buyGoods($uid)
	{
		$result = array('status' => -1);

		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$info = $cache->get($key);

		$dalDB = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();

		if($info === false) {
			try {
				$info = $dalDB->getTeamBuyInfo();
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}

			if($info === false) {
				$result['content'] = self::TXT001;
				return $result;
			}

			$cache->set($key, $info);
		}

		$nowTime = time();

		$teamBuyGift = explode('*', $info['gid']);
		$ok_time = $info['ok_time'] * 3600;
		$buy_time = $info['buy_time'] * 3600;
		$canBuyTime = $info['start_time'] + $ok_time + $buy_time - $nowTime;

		//购买时间已过,隐藏图标,返回
		if($canBuyTime <= 0) {
			$result['content'] = TXT001;

			return $result;
		}

		//已加入人数
		$hasJoinNum = $dalDB->getJoinNum();
		//面板显示人数=加入人数+起始人数
		$joinNum = $hasJoinNum + $info['start_num'];
		//已购买人数
		$hasBuyNum = $dalDB->getHasBuyNum();

		$max_price_info = explode('*', $info['max_price']);
		$min_price_info = explode('*', $info['min_price']);

		//判断是否已经加入团购:attendflag(没有:-1,已加入:1)
		$mkey = 'i:e:teambuy:buygood:' . $uid;
		$mcache = Hapyfish2_Cache_Factory::getMC($uid);
		$state = $mcache->get($key);

		if($state === false) {
			try {
				$state = $dalDB->getJoinTeamBuyInfo($uid);
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}
		}

		//获得价格类型等信息
		$priceArr = self::getPriceType($joinNum, $min_price_info, $max_price_info, $info);

		//没有加入($state=false)或已购买($state=1)返回
		if($state === false) {
			$resultVo = array(
							'result' => $result,
							'attendflag' => -1,
							'pricebefore' => $priceArr['maxArr'],
							'pricenownum' => $priceArr['nowArr'],
							'price_jie' => $priceArr['decArr'],
							'buyoknum' => $hasBuyNum,
							'buyokflag' => -1,
							'buylen' => -1,
							'Actlen' => -1
						);

			return $resultVo;
		} else if(1 == $state) {
			$mcache->set($mkey);

			$result['content'] = self::TXT005;
			return $result;
		}

		//获得用户gold
		$userGold = Hapyfish2_Island_HFC_User::getUserGold($uid);

		//获得用户coin
		$userCoin = Hapyfish2_Island_HFC_User::getUserCoin($uid);

		//获得物品名字
		$giftType = substr($teamBuyGift[0], -2, 1);

		if (1 == $giftType) {
			$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($teamBuyGift[0]);
		} else if (2 == $giftType) {
			$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($teamBuyGift[0]);
		} else if (3 == $giftType) {
			$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($teamBuyGift[0]);
		} else if (4 == $giftType) {
			$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($teamBuyGift[0]);
		}

		//购买价格类型gold
		if(2 == $priceArr['nowArr']['price_type']) {
			//宝石不足,返回
			if($priceArr['nowArr']['price'] > $userGold) {
				$resultVo = array(
								'result' => $result,
								'attendflag' => 1,
								'pricebefore' => $priceArr['maxArr'],
								'pricenownum' => $priceArr['nowArr'],
								'price_jie' => $priceArr['decArr'],
								'buyoknum' => $hasBuyNum,
								'buyokflag' => -1,
								'buylen' => -1,
								'Actlen' => -1
							);

				return $resultVo;
			}

			//发放物品
			$ok = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $teamBuyGift[0], $teamBuyGift[1]);

			//发放成功加log,减少gold
			if($ok) {
				//减少gold
				$goldInfo = array(
					'uid' => $uid,
					'cost' => $priceArr['nowArr']['price'],
					'summary' => LANG_PLATFORM_EVENT_TXT_33 . $giftInfo['name'],
					'cid' => $teamBuyGift[0],
					'num' => 1
				);
		        $ok2 = Hapyfish2_Island_Bll_Gold::consume($uid, $goldInfo);

		        if($ok2) {
        	        try {
						Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_19', $goldInfo['cost']);
					
						//task id 3068,task type 19
						$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3068);
						if ( $checkTask['status'] == 1 ) {
							$result['finishTaskId'] = $checkTask['finishTaskId'];
						}
			        } catch (Exception $e) {
			
			        }
		        }
		        
				$Num = $priceArr['nowArr']['price'] . LANG_PLATFORM_BASE_TXT_02;

				$result['goldChange'] = -$priceArr['nowArr']['price'];
				$result['itemBoxChange'] = true;
			}
		} else {
			//价格类型coin,数量不足返回
			if($priceArr['nowArr']['price'] > $userCoin) {
				$result['content'] = 'serverWord_137';

				$resultVo = array(
								'result' => $result,
								'attendflag' => 1,
								'pricebefore' => $priceArr['maxArr'],
								'pricenownum' => $priceArr['nowArr'],
								'price_jie' => $priceArr['decArr'],
								'buyoknum' => $hasBuyNum,
								'buyokflag' => -1,
								'buylen' => -1,
								'Actlen' => -1
							);

				return $resultVo;
			}

			//发放物品
			$ok = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $teamBuyGift[0], $teamBuyGift[1]);

			//发放成功,减少coin,加log
			if($ok) {
				Hapyfish2_Island_HFC_User::decUserCoin($uid, $priceArr['nowArr']['price']);
				//add log
				$summary = LANG_PLATFORM_EVENT_TXT_33 . $giftInfo['name'];
				Hapyfish2_Island_Bll_ConsumeLog::coin($uid, $priceArr['nowArr']['price'], $summary, $nowTime);

				//update user buy coin
		        try {
					Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_14', $priceArr['nowArr']['price']);
				
					//task id 3012,task type 14
					$checkTask = Hapyfish2_Island_Bll_Task::checkTask($uid, 3001);
					if ( $checkTask['status'] == 1 ) {
						$result['finishTaskId'] = $checkTask['finishTaskId'];
					}
		        } catch (Exception $e) {
		
		        }
				
				$Num = $priceArr['nowArr']['price'] . LANG_PLATFORM_BASE_TXT_01;

				$result['coinChange'] = -$priceArr['nowArr']['price'];
				$result['itemBoxChange'] = true;
			}
		}

		if($ok) {
			//更新DB中购买状态
			try {
				$dalDB->updateHasBuy($uid);
			} catch (Exception $e) {
				info_log($e, 'teambuy_info');

				$result['content'] = 'serverWord_101';
				return $result;
			}

			//更新购买状态
			$mcache->set($mkey, 1);

			//发送Feed
        	$minifeed = array(
							'uid' => $uid,
							'template_id' => 106,
							'actor' => $uid,
							'target' => $uid,
							'title' => array('info' => $Num, 'name' => $giftInfo['name']),
							'type' => 3,
							'create_time' => $nowTime
						);

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}

		$result['status'] = 1;

		$resultVo = array(
						'result' => $result,
						'attendflag' => 1,
						'buyokflag' => 1,
						'buylen' => -1,
						'Actlen' => -1
					);

		return $resultVo;
	}

	/**
	 *
	 * 判断团购的价格和类型
	 * @param $uids
	 */
	public static function getPriceType($joinNum, $min, $max, $info)
	{
		//金币和宝石降价比例
		$scale_gold = explode(':', $info['scale_gold']);
		$scale_coin = explode(':', $info['scale_coin']);

		//原价类型max:1-coin,2-gold
		if($max[1] == 2) {
			//最低价类型min:1-coin,2-gold
			if($min[1] == 2) {
				$price_type = 2;

				//加入人数少于最多人数
				if($joinNum < $info['max_num']) {
					$price_Now = $max[0] - (floor($joinNum/$scale_gold[1]) * $scale_gold[0]);
					$price_Dec = $max[0] - $price_Now;
					$downManNum = $scale_gold[1] - ($joinNum % $scale_gold[1]);
				} else {
					$price_Now = $min[0];
					$price_Dec = $max[0] - $min[0];
					$downManNum = 0;
				}

				$max_price = array('price_type' => $price_type, 'price' => (int)$max[0]);
				$min_price = array('price_type' => $price_type, 'price' => (int)$min[0]);
				$now_price = array('price_type' => $price_type, 'price' => $price_Now);
				$dec_price = array('price_type' => $price_type, 'price' => $price_Dec);
			} else {
				//加入人数少于变为金币类型人数
				if($joinNum < $info['bec_num']) {
					$price_type = 2;

					$price_Now = $max[0] - (floor($joinNum/$scale_gold[1]) * $scale_gold[0]);
					$price_Dec = $max[1] - $price_Now;
					$downManNum = $scale_gold[1] - ($joinNum % $scale_gold[1]);

					$max_price = array('price_type' => $price_type, 'price' => (int)$max[0]);
					$min_price = array('price_type' => $min[1], 'price' => $min[0]);
					$now_price = array('price_type' => $price_type, 'price' => $price_Now);
					$dec_price = array('price_type' => $price_type, 'price' => $price_Dec);
				} else if($joinNum >= $info['bec_num'] && $joinNum < $info['max_num']) {
					$price_Now = $info['bec_price'] - (floor(($joinNum - $info['bec_num'])/$scale_coin[1]) * $scale_coin[0]);
					$downManNum = $scale_coin[1] - (($joinNum - $info['bec_num']) % $scale_coin[1]);
					$price_Dec = $max[0] - $price_Now;

					$max_price = array('price_type' => $max[1], 'price' => (int)$max[0]);
					$min_price = array('price_type' => $min[1], 'price' => (int)$min[0]);
					$now_price = array('price_type' => $min[1], 'price' => $price_Now);
					$dec_price = array('price_type' => $max[1], 'price' => $price_Dec);
				} else {
					$downManNum = 0;
					$price_Dec = $max[0] - $min[0];

					$max_price = array('price_type' => $max[1], 'price' => (int)$max[0]);
					$min_price = array('price_type' => $min[1], 'price' => (int)$min[0]);
					$now_price = array('price_type' => $min[1], 'price' => $min[0]);
					$dec_price = array('price_type' => $max[1], 'price' => $price_Dec);
				}
			}
		} else {
			$price_type = 1;

			if($joinNum < $info['max_num']) {
				$price_type = 1;

				$price_Now = $max[0] - (floor($joinNum/$scale_coin[1]) * $scale_coin[0]);
				$price_Dec = $max[0] - $price_Now;
				$downManNum = $scale_coin[1] - ($joinNum % $scale_coin[1]);

				$now_price = array('price_type' => $price_type, 'price' => $price_Now);
				$dec_price = array('price_type' => $price_type, 'price' => $price_Dec);
			} else {
				$downManNum = 0;
				$price_Dec = $max[0] - $min[0];

				$now_price = array('price_type' => $price_type, 'price' => (int)$min[0]);
				$dec_price = array('price_type' => $price_type, 'price' => $price_Dec);
			}
			$max_price = array('price_type' => $price_type, 'price' => (int)$max[0]);
			$min_price = array('price_type' => $price_type, 'price' => (int)$min[0]);
		}

		$resultVo = array(
					'downNum' => $downManNum,
					'maxArr' => $max_price,
					'minArr' => $min_price,
					'nowArr' => $now_price,
					'decArr' => $dec_price
				);

		return $resultVo;
	}

	/**
	 * 设置试开放用户ID
	 */
	public static function setOpenUID($uids)
	{
		$key = 'i:e:teambuy:opened';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->set($key, $uids);
	}

	/**
	 * 获得试开放的ID
	 */
	public static function getOpenUID()
	{
		$uids = array();

		$key = 'i:e:teambuy:opened';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$uids = $cache->get($key);

		return $uids;
	}

	/**
	 * 删除试开放的用户==全部开放
	 */
	public static function deleteOpenUID()
	{
		$key = 'i:e:teambuy:opened';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$cache->delete($key);
	}

	/**
	 * 验证icon是否显示
	 *
	 * @return 0/1
	 */
	public static function checkIcon($uid)
	{
		$key = 'i:e:teambuy:info';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$info = $cache->get($key);

		$dalDB = Hapyfish2_Island_Event_Dal_TeamBuy::getDefaultInstance();

		if($info === false) {
			try {
				$info = $dalDB->getTeamBuyInfo();
			} catch (Exception $e) {
				return 1;
			}

			if($info === false) {
				return 1;
			}

			$cache->set($key, $info);
		}

		if($info == false) {
			$icon = 1;
		} else {
			//检查是否本期团购已结束
			if($info['start_time'] + ($info['ok_time'] + $info['buy_time']) * 3600 - time() < 0) {
				$icon = 1;
			} else {
				$icon = 0;

				$newkey = 'i:e:teambuy:buygood:' . $uid;
				$newcache = Hapyfish2_Cache_Factory::getMC($uid);
				$state = $newcache->get($newkey);

				if(!$state) {
					try {
						$state = $dalDB->getJoinTeamBuyInfo($uid);
					} catch (Exception $e) {
						return 1;
					}

					if($state) {
						$newcache->set($newkey, $state);
					}
				}

				//$state(1:已购买,-1:没购买)
				if($state == 1) {
					$icon = 1;
				} else {
					$icon = 0;
				}
			}
		}

		if(0 == $icon) {
			//判断是否属于试开放的UID
			$openUIDs = self::getOpenUID();

			if($openUIDs) {
				if(in_array($uid, $openUIDs)) {
					$icon = 0;
				} else {
					$icon = 1;
				}
			}
		}

		return $icon;
	}

}