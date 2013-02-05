<?php

require_once(CONFIG_DIR . '/language.php');

/**
 * Event OneGoldShop
 *
 * @package    Island/Event/Bll
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/07/26    zhangli
*/
class Hapyfish2_Island_Event_Bll_OneGoldShop
{
	//计算相应值
	public static function reckon($uid, $nowTime)
	{
		$result = array('status' => -1);

		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		//本期物品信息
		$key = 'i:e:oneshop:gift';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$data = $cache->get($key);

		if ($data === false) {
			$keyAll = 'i:e:onegold:all';
			$allData = $cache->get($keyAll);

			if ($allData === false) {
				try {
					$allData = $db->getAllOneGoldGift();
				} catch (Exception $e) {}

				if ($allData) {
					$cache->set($keyAll, $allData);
				}
			}

			if ($allData) {
				foreach ($allData as $datagift) {
					if (($datagift['start_time'] <= $nowTime) && $nowTime < $datagift['end_time']) {
						$data = $datagift;
						$cache->set($key, $data, $data['end_time']);
						break;
					}

				}
			}
		}

		$result['status'] = 1;

		$falseTime = $data['end_time'];

		//计算结束时间倒计时
		$endTime = $falseTime - $nowTime;

		//获取用户是否可以抢购状态
		$keyNum = 'i:u:oneshop:gift:get_status:' . $uid;
		$userCache = Hapyfish2_Cache_Factory::getMC($uid);
		$state = $userCache->get($keyNum);

		if ($state == false) {
			try {
				$subData = $db->getOneGoldHasGet($uid);
				$state = $subData['has_get'];
			} catch (Exception $e) {
			}

			$userCache->set($keyNum, $state, $falseTime);
		}

		$keyCid = 'i:e:oneshop:gift:hasnum';
		$hasNum = $cache->get($keyCid);
		//计算本期物品剩余数量
		if ($hasNum === false) {
			$hasNum = $data['num'];

			$cache->set($keyCid, $hasNum, $falseTime);
		}

		//本期剩余数量为0,用户不可领取
		if ($hasNum == 0) {
			$state = 2;
		}

		//用户参加一元店次数
		$keyPayNum = 'i:e:oneshop:buynum:' . $uid;
		$pay_num = $userCache->get($keyPayNum);

		if ($pay_num == false) {
			try {
				$payNumNay = $db->getOneGoldHasGet($uid);
			} catch (Exception $e) {
			}

			if ($payNumNay['buy_num'] == false) {
				$pay_num = 0;
				$userCache->set($keyPayNum, $pay_num);
			} else {
				$pay_num =  $payNumNay['buy_num'];
				$userCache->set($keyPayNum, $pay_num);
			}
		}

		$result = array(
					'result'	=>	$result,
					'data'		=>	$data,
					'endTime'	=>	(int)$endTime,
					'timeVa'	=>	$data['start_time'],
					'hasNum'	=>	(int)$hasNum,
					'state'		=>	(int)$state,
					'payNum'	=>	(int)$pay_num
				);

		return $result;
	}

	//获取一元店信息
	public static function oneGoldShopInit($uid)
	{
		$result = array('status' => -1);

		$nowTime = time();
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		//计算相应数值
		$dataVo = self::reckon($uid, $nowTime);

		if($dataVo['result']['status'] == -1) {
			return $dataVo['result'];
		}

		$data = $dataVo['data'];

		$cid = array();
		if ($data['cid']) {
			$cid = explode(',', $data['cid']);
		}
		$result = array('status' => 1);

		$resultVo = array(
						'result' 		=> $result,
						'cid' 			=> $cid,
						'coin'			=> (int)$data['coin'],
						'gold'			=> (int)$data['gold'],
						'starfish'		=> (int)$data['starfish'],
						'leftnum' 		=> $dataVo['hasNum'],
						'Rechargenum'	=> $dataVo['payNum'],
						'lefttime' 		=> $dataVo['endTime'],
						'getaward_flag' => $dataVo['state'],
					);

		return $resultVo;
	}

	//充值前查询物品剩余数量
	public static function goPayOneGold($uid)
	{
		$result = array('status' => 1);

		$hasNum = 0;

		$key = 'i:e:oneshop:gift:hasnum';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$hasNum = $cache->get($key);

		$resultVo = array('result' => $result,'leftnum' => (int)$hasNum);

		return $resultVo;
	}

	//设置1元充值信息
	public static function setPayInfo($uid)
	{
		$nowTime = time();

		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		//本期物品信息
		$key = 'i:e:oneshop:gift';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$data = $cache->get($key);

		$keyTime = 'i:e:oneshop:gift:newtime';
		$TimeVa = $cache->get($keyTime);
		//设置开始的时间戳
		if($TimeVa == null) {
			try {
				$TimeVa = $db->getStartTime($data['id']);
			} catch (Exception $e) {
			}

			$cache->set($keyTime, $TimeVa, $TimeVa['end_time']);
		}

		$key = 'i:e:oneshop:gift:hasnum';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$hasNum = $cache->get($key);

		//获取用户领取本期物品的状态:0-不可领取,1可领取,2-已获得
		$keyUser = 'i:u:oneshop:gift:get_status:' . $uid;
		$cacheUser = Hapyfish2_Cache_Factory::getMC($uid);
		$userStatus = $cacheUser->get($keyUser);

		//缓存不存在或者是不可领取状态
		if ($hasNum > 0) {
			if ($userStatus != 2) {
				$cacheUser->set($keyUser, 1);

				$step = 1;
			} else {
				$step = 0;
			}
		} else {
			$step = 0;
		}

		try {
			$ok = $db->addOneGoldPay($uid, $step);
		} catch (Exception $e) {
			info_log($uid, 'onegoldshop');
			return ;
		}
	}

	//领取本期物品
	public static function getOneGoldGift($uid)
	{
		$result = array('status' => -1);

		$nowTime = time();

		$dataVo = self::reckon($uid, $nowTime);
		$data = $dataVo['data'];

		if ($dataVo['state'] == 0) {
			$result['content'] = '对不起，你还没有充值，不能领取';
			$resultVo = array('result' => $result);
			return $resultVo;
		}

		//已经领取过，返回
		if ($dataVo['state'] == 2) {
			$result['content'] = '对不起，你已经抢购了本期物品';
			$resultVo = array('result' => $result);
			return $resultVo;
		}

		//本期数量没了
		if ($dataVo['hasNum'] == 0) {
			$result['content'] = '对不起，本期物品已经被抢购光了，请期待下一期';
			$resultVo = array('result' => $result);
			return $resultVo;
		}

		//发物品
		$cid = array();
		$nameCid = '';
		if ($data['cid']) {
			$mag = explode(',', $data['cid']);
			foreach ($mag as $cidDatas) {
				$cidData = array();

				$cidData = explode('*', $cidDatas);
				$ok = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $cidData[0], $cidData[1]);

				$giftType = substr($cidData[0], -2, 1);

				if (1 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($cidData[0]);
				} else if (2 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($cidData[0]);
				} else if (3 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($cidData[0]);
				} else if (4 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($cidData[0]);
				}

				$nameCid .= $giftInfo['name'] . 'x' . $cidData[1] . ' ';

				$cid[] = $cidData[0] . '*' . $cidData[1];
			}
		}

		if ($data['coin'] > 0) {
			$ok = Hapyfish2_Island_HFC_User::incUserCoin($uid, $data['coin']);
			$nameCid .= $data['coin'] . LANG_PLATFORM_BASE_TXT_01 . ' ';
		}

		if ($data['gold'] > 0) {
			$goldInfo = array('gold' => $data['gold'], 'type' => 5, 'time' => $nowTime);
			$ok = Hapyfish2_Island_Bll_Gold::add($uid, $goldInfo);
			$nameCid .= $data['gold'] . LANG_PLATFORM_BASE_TXT_02 . ' ';
		}

		if ($data['starfish'] > 0) {
			$ok = Hapyfish2_Island_HFC_User::incUserStarFish($uid, $data['starfish']);
			$nameCid .= $data['starfish'] . '海星';
		}

		//改本期状态,缓存过期时间是本期物品奖励过期时间
		if($ok) {
			//标记用户已经本期物品状态
			$key = 'i:u:oneshop:gift:get_status:' . $uid;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache->set($key, 2, $dataVo['endTime']);

			try {
				$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();
				$db->refurbishHasGet($uid);
			} catch (Exception $e) {}

			//记录用户参加购买的次数
			$nvkey = 'i:e:oneshop:buynum:' . $uid;
			$buyNum = $cache->get($nvkey);

			if ($buyNum == false) {
				$buyData = $db->getOneGoldHasGet($uid);
				$cache->set($nvkey, $buyData['buy_num']);
			} else {
				$buyNum += 1;
				$cache->set($nvkey, $buyNum);
			}

			$report = array($uid, $data['id']);

			//report log
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('405', $report);

			//更新物品数量
			$keyCount = 'i:e:oneshop:gift:hasnum';
			$cacheCount = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
			$count = $cacheCount->get($keyCount);
			$count -= 1;

			$cacheCount->set($keyCount, $count, $dataVo['endTime']);

			$feed = '恭喜你获得1元特价物品<font color="#FF0000">' . $nameCid . '</font>';

			//发送Feed
        	$minifeed = array(
							'uid' => $uid,
							'template_id' => 0,
							'actor' => $uid,
							'target' => $uid,
							'title' => array('title' => $feed),
							'type' => 3,
							'create_time' => $nowTime
						);

			Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
		}

		//标注每期的用户数量
		$buyTime = date('Y-m-d H:i:s', $nowTime);
		info_log('time : ' . $buyTime . ' ID :' . $data['id'] . ' : ' . $uid, 'onegoldshopCount');

		$result = array(
					'status'		=>	1,
					'goldChange'	=> 	(int)$goldInfo['gold'],
					'coinChange'	=>	(int)$data['coin']
		);

		$resultVo = array(
						'result'	=>	$result,
						'cid'		=>	$cid,
						'gold'		=>	(int)$goldInfo['gold'],
						'coin'		=>	(int)$data['coin'],
						'starfish'	=>	(int)$data['starfish']
					);

		return $resultVo;
	}

	//获取盒子礼物信息
	public static function getBoxInfo($uid)
	{
		$result = array('status' => -1);

		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		//用户已经领取到哪一期礼包的标记
		$keyOB = 'i:e:oneshop:box:qishu:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$hasCountBox = $cache->get($keyOB);

		if ($hasCountBox == false) {
			$hasCountBox = $db->hasCountBox($uid);

			$cache->set($keyOB, $hasCountBox);
		}

		//获取礼包物品信息
		$keyBox = 'i:e:oneshop:gift:bigbox:' . $uid;
		$cacheBox = Hapyfish2_Cache_Factory::getMC($uid);
		$boxData = $cacheBox->get($keyBox);

		if ($boxData == false) {
			try {
				$dataVo = $db->getBoxInfo($hasCountBox);
			} catch (Exception $e) {
				$result['content'] = 'serverWord_101';
				$resultVo['result'] = $result;
				return $resultVo;
			}

			if ($dataVo) {
				$botDataNew = array();

				foreach ($dataVo as $data) {
					$boxId = $data['idx'];

					$boxData[$boxId]['gold'] = 0;
					if ($data['gold'] > 0) {
						$botDataNew[$boxId]['gold'] = (int)$data['gold'];
					}

					$boxData[$boxId]['coin'] = 0;
					if ($data['coin'] > 0) {
						$botDataNew[$boxId]['coin'] = (int)$data['coin'];
					}

					$boxData[$boxId]['starfish'] = 0;
					if ($data['starfish'] > 0) {
						$botDataNew[$boxId]['starfish'] = (int)$data['starfish'];
					}

					$boxData[$boxId]['cid'] = array();
					if ($data['data']) {
						$msgCid = explode(',', $data['data']);

						foreach ($msgCid as $vaCid) {
							$toCid = explode('*', $vaCid);

							$botDataNew[$boxId]['cid'][] = $toCid[0] . '*' . $toCid[1];
						}
					}

					$msy[$boxId] = 0;
				}

				$cacheBox->set($keyBox, $botDataNew);
			} else {
				$result['content'] = 'serverWord_101';
				$resultVo['result'] = $result;
				return $resultVo;
			}
		}

		$key = 'i:e:oneshop:box:has:' . $uid;
		$hasGet = $cache->get($key);
		if ($hasGet == false) {
			$hasGetsky = $db->getOneGoldBox($uid);

			if ($hasGetsky) {
				$hg = explode(',', $hasGetsky);
				foreach ($hg as $stas) {
					$sky[] = explode('*', $stas);
				}

				foreach ($sky as $sky_value) {
					$vak = 0;
					$vav = 0;
					$vak = $sky_value[0];
					$vav = $sky_value[1];
					$msy[$vak] = $vav;
				}

				$cache->set($key, $msy);
			} else {
				$cache->set($key, $msy);
			}
		}

		//获取用户参加活动次数
		$keyNum = 'i:e:oneshop:buynum:' . $uid;
		$payNum = $cache->get($keyNum);

		if($payNum == false) {
			try {
				$payData = $db->getOneGoldHasGet($uid);
				$payNum = $payData['buy_num'];
			} catch (Exception $e) {
			}

			if($payNum) {
				$cache->set($keyNum, $payNum);
			} else {
				$payNum = 0;
			}
		}

		$boxData = $cacheBox->get($keyBox);
		$hasGet = $cache->get($key);

		$newsdatas = array();
		foreach ($hasGet as $newkey => $newvalue) {
			foreach ($boxData as $datakey => $datavalue) {
				if ($newkey == $datakey) {
					$newsdata = array();

					$newsdata['num'] = $newkey;
					$newsdata['flag'] = (int)$newvalue;
					$newsdata['data'] = $datavalue;

					$newsdatas[] = $newsdata;
					continue;
				}
			}
		}

		$resultVo = array(
						'result'		=>	array('status' => 1),
						'Rechargenum'	=>	(int)$payNum,
						'boxData'		=>	$newsdatas,
						'updahasGet'	=>	$hasGet,
						'newsData'		=>	$boxData,
					);

		return $resultVo;
	}

	//领取盒子礼物
	public static function getOneGoldBox($uid, $idx)
	{
		$result = array('status' => -1);

		if ($idx == false) {
			$result['content'] = 'serverWord_101';
			$resultVo['result'] = $result;
			return $resultVo;
		}

		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		$datas = self::getBoxInfo($uid);

		$boxData = $datas['newsData'];
		$hasGet = $datas['updahasGet'];
		$payNum = $datas['Rechargenum'];

		//领取礼包
		if ($payNum >= $idx && $hasGet[$idx] == 0) {
			$dataVo = self::addOneGoldGift($uid, $boxData[$idx], $idx, $hasGet);
		} else {
			$result['content'] = 'serverWord_101';
			$resultVo['result'] = $result;
			return $resultVo;
		}

		$result = array('status' => 1,
						'coinChange' => (int)$dataVo['coin'],
						'goldChange' => (int)$dataVo['gold']
					);
		$resultVo = array('result' => $result);

		return $resultVo;
	}

	//发东西
	public static function addOneGoldGift($uid, $boxData, $idx, $getStatus)
	{
		$db = Hapyfish2_Island_Event_Dal_OneGoldShop::getDefaultInstance();

		//用户已经领取到哪一期礼包的标记
		$keyOB = 'i:e:oneshop:box:qishu:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$hasCountBox = $cache->get($keyOB);

		if ($hasCountBox === false) {
			$hasCountBox = $db->hasCountBox($uid);

			if ($hasCountBox) {
				$cache->set($keyOB, $hasCountBox);
			}
		}

		//获取用户领取礼包状态
		$key = 'i:e:oneshop:box:has:' . $uid;
		$hasGet = $cache->get($key);

		if ($hasGet === false) {
			try {
				$dataBoxVo = $db->getOneGoldBox($uid);
			} catch (Exception $e) {
				$result['content'] = 'serverWord_101';
				return $result;
			}

			if($dataBoxVo) {
				$dataBox = explode(',', $dataBoxVo);
				foreach ($dataBox as $box) {
					$msgBox = explode('*', $box);
					$boxIdx = $msgBox[0];
					$hasGet[$boxIdx] = $msgBox[1];
				}

				$cache->set($key, $hasGet);
			}
		}

		$toSendBox = '';
		if ($boxData['coin']) {
			$ok = Hapyfish2_Island_HFC_User::incUserCoin($uid, $boxData['coin']);
			$toSendBox .= $boxData['coin'] . LANG_PLATFORM_BASE_TXT_01 .' ';
		}

		if ($boxData['gold']) {
			$goldInfo = array('gold' => $boxData['gold'], 'type' => 5, 'time' => time);
			$ok = Hapyfish2_Island_Bll_Gold::add($uid, $goldInfo);
			$toSendBox .= $boxData['gold'] . LANG_PLATFORM_BASE_TXT_02 . ' ';
		}

		if ($boxData['starfish']) {
			$ok = Hapyfish2_Island_HFC_User::incUserStarFish($uid, $boxData['starfish']);
			$toSendBox .= $boxData['starfish'] . '海星 ';
		}

		$cidData = array();
		if ($boxData['cid']) {
			foreach ($boxData['cid'] as $mData) {
				$mcData = explode('*', $mData);
				$ok = Hapyfish2_Island_Bll_GiftPackage::addGift($uid, $mcData[0], $mcData[1]);

				$giftType = substr($mcData[0], -2, 1);

				if (1 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBackgoundInfo($mcData[0]);
				} else if (2 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getBuildingInfo($mcData[0]);
				} else if (3 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getPlantInfo($mcData[0]);
				} else if (4 == $giftType) {
					$giftInfo = Hapyfish2_Island_Cache_BasicInfo::getCardInfo($mcData[0]);
				}

				$toSendBox .= $giftInfo['name'] . 'x' . $mcData[1] . ' ';
				$cidData[] = $mcData[0];
			}
		}

		if ($ok) {
			$hasGet[$idx] = 1;
			$getStatus[$idx] = 1;

			$dkTotal = 0;
			foreach ($getStatus as $datKey => $DataVl) {
				$dkTotal += $DataVl;
			}

			if ($dkTotal == 3) {
				$stay = $hasCountBox + 1;

				$db->updateCountBox($uid, $stay);
				$cache->set($keyOB, $stay);

				//礼包物品信息
				$keyBox = 'i:e:oneshop:gift:bigbox:' . $uid;
				$cacheBox = Hapyfish2_Cache_Factory::getMC($uid);
				$cacheBox->delete($keyBox);

				$enData = 0;
				try {
					$ok2 = $db->updateOneGoldBox($uid, $enData);
				} catch (Exception $e) {
				}

				$cache->delete($key);
			} else {

				foreach ($hasGet as $Dakey => $Vakey) {
					$upData[] = $Dakey . '*' . $Vakey;
				}

				$enData = join(',', $upData);

				//更新礼包领取状态
				try {
					$ok2 = $db->updateOneGoldBox($uid, $enData);

					$db->refrushBoxAct($idx);
				} catch (Exception $e) {
				}

				$cache->set($key, $hasGet);
			}

			$info = array($uid, $idx);

			//report log
			$logger = Hapyfish2_Util_Log::getInstance();
			$logger->report('404', $info);

			if ($ok2) {
				$feed = '恭喜你获得1元特价大礼包<font color="#FF0000">' . $toSendBox . '</font>';

				//发送Feed
	        	$minifeed = array(
								'uid' => $uid,
								'template_id' => 0,
								'actor' => $uid,
								'target' => $uid,
								'title' => array('title' => $feed),
								'type' => 3,
								'create_time' => time()
							);

				Hapyfish2_Island_Bll_Feed::insertMiniFeed($minifeed);
			}
		}

		$result = array(
					'cid'		=>	$cidData,
					'coin'		=>	$boxData['coin'],
					'gold'		=>	$boxData['gold'],
					'starfish'	=>	$boxData['starfish']
		);

		return $result;
	}

	public static function resetBoxInfo()
	{
		$uids = array(2041191,5135087,6037294);

		foreach ($uids as $uid) {
			$newkey = 'i:e:oneshop:box:has:' . $uid;
			$keyBox = 'i:e:oneshop:gift:bigbox:' . $uid;
			$cacheBox = Hapyfish2_Cache_Factory::getMC($uid);
			$cacheBox->delete($keyBox);
			$cacheBox->delete($newkey);
		}
	}

}