<?php

/**
 * Event HallWitches
 *
 * @package    Island/Event/Cache
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/10/18    zhangli
*/
class Hapyfish2_Island_Event_Cache_HallWitches
{
	/**
	 * @获取卡片信息
	 * @return array
	 */
	public static function getListArr()
	{
		$key = 'ev:hall:card';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$list = $cache->get($key);

		if ($list === false) {
			try {
				$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
				$list = $db->getListArr();
			} catch (Exception $e) {}

			if ($list) {
				foreach ($list as $lsk => $ls) {
					$list[$lsk]['cardId'] = $ls['cid'];
					$list[$lsk]['cardName'] = $ls['name'];

					unset($list[$lsk]['cid']);
					unset($list[$lsk]['name']);
				}

				$cache->set($key, $list, 3600 * 24 * 15);
			}
		}

		return $list;
	}

	/**
	 * @兑换物品重组
	 * @return array
	 */
	public static function getHallList()
	{
		$list = self::resetHallList();

		foreach ($list as $lskey => $ls) {
			unset($list[$lskey]['need_data']);
		}

		return $list;
	}

	/**
	 * @返兑换物品列表重组
	 * @return array
	 */
	public static function resetHallList()
	{
		$key = 'ev:hall:gift';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$list = $cache->get($key);

		if ($list === false) {
			try {
				$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
				$list = $db->getHallList();
			} catch (Exception $e) {}

			if ($list) {
				foreach ($list as $lskey => $ls) {
					$list[$lskey]['coin'] = $ls['coin'];
					$list[$lskey]['gem'] = $ls['gold'];
					$list[$lskey]['starfish'] = $ls['starfish'];
					$list[$lskey]['itemId'] = $ls['item_id'];
					$list[$lskey]['itemNum'] = $ls['item_num'];
					$list[$lskey]['need_data'] = $ls['need_data'];

					unset($list[$lskey]['gold']);
					unset($list[$lskey]['item_id']);
					unset($list[$lskey]['item_num']);
				}

				$cache->set($key, $list, 3600 * 24 * 15);
			}
		}

		return $list;
	}

	/**
	 * @获取用户是否可以领取宝石卡牌的状态
	 * @param int $uid
	 * @return int
	 */
	 public static function getCardChance($uid)
	 {
		$key = 'ev:hall:card:chance:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cardChance = $cache->get($key);

		if ($cardChance == false) {
			$cardChance = 0;
		}

		return $cardChance;
	 }

	/**
	 * @刷新用户可以领取宝石卡牌状态
	 * @param int $uid
	 * @return boolean
	 */
	 public static function refrushCardChance($uid, $chance)
	 {
		$key = 'ev:hall:card:chance:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $chance, 3600 * 24 * 30);
	 }

	/**
	 * @重组卡片兑换列表
	 * @param int $uid
	 *
	 * @return array
	 */
	public static function resetListArr($uid)
	{
		$list = self::getListArr();

		$card = self::getCard($uid);

		foreach ($list as $keyls => $ls) {
			foreach ($card as $keyRD => $rd) {
				if ($ls['cardId'] == $keyRD) {
					$list[$keyls]['cardCount'] = $rd;
					break;
				}
			}

			unset($list[$keyls]['odds']);
		}

		return $list;
	}

	/**
	 * @获取用户卡片信息列表
	 * @param int $uid
	 *
	 * @return array
	 */
	public static function getCard($uid)
	{
		$key = 'ev:hall:card:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$card = $cache->get($key);

		if ($card === false) {
			$list = array();

			try {
				$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
				$list = $db->getCard($uid);
			} catch (Exception $e) {}

			if ($list == false) {
				$list = '1*0,2*0,3*0,4*0,5*0,6*0,7*0,8*0';

				try {
					$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
					$db->addCard($uid, $list);
				} catch (Exception $e) {
					info_log($uid, 'Hall_addCard');
				}
			}

			$cardVo = explode(',', $list);

			foreach ($cardVo as $crd) {
				$data = array();

				$data = explode('*', $crd);
				$card[$data[0]] = $data[1];
			}

			$cache->set($key, $card, 3600 * 24 * 15);
		}

		return $card;
	}

	/**
	 * @计算用户卡片数量
	 * @param int $uid
	 * @param array $cardId
	 */
	public static function incCard($uid, $cardId)
	{
		$card = self::getCard($uid);

		foreach ($card as $cdkey => $value) {
			if ($cardId == $cdkey) {
				$card[$cardId] += 1;
				break;
			}
		}

		$key = 'ev:hall:card:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $card, 3600 * 24 * 15);

		foreach ($card as $cardkey => $cardva) {
			$data[] = $cardkey . '*' . $cardva;
		}

		$list = implode(',', $data);

		try {
			$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
			$db->incCard($uid, $list);
		} catch (Exception $e) {
			info_log($uid . ' '. $cardId, 'Hall_incCard');
		}
	}

	/**
	 * @减少用户卡片数量
	 * @param int $uid
	 * @param array $cardId
	 */
	public static function decCard($uid, $card)
	{
		$key = 'ev:hall:card:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $card, 3600 * 24 * 15);

		foreach ($card as $cardkey => $cardva) {
			$data[] = $cardkey . '*' . $cardva;
		}

		$list = implode(',', $data);

		try {
			$db = Hapyfish2_Island_Event_Dal_HallWitches::getDefaultInstance();
			$db->incCard($uid, $list);
		} catch (Exception $e) {
			info_log($uid . ' ' . $card, 'Hall_decCard');
		}
	}

	/**
	 * @获取用户领取免费卡片倒计时
	 * @param int $uid
	 * @param int $nowTime
	 *
	 * @return int
	 */
	public static function getLastTime($uid, $nowTime)
	{
		$key = 'ev:hall:time:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$countdown = $cache->get($key);

		if (($countdown == null) || ($countdown === false)) {
			$countdown = 0;
		} else {
			$countdown = $countdown - $nowTime;
		}

		return $countdown;
	}

	/**
	 * @标记用户领取免费卡片时间
	 * @param int $uid
	 * @param int $starTime
	 */
	public static function incFreeTime($uid, $starTime)
	{
		$endTime = $starTime + 3600 * 2;

		$key = 'ev:hall:time:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $endTime, 3600 * 2);
	}

	/**
	 * @获取兑换物品列表
	 * @reutrn array
	 */
	public static function exchangeList()
	{
		$list = self::resetHallList();

		if ($list === false) {
			return $list;
		}

		$cardListVo = array();

		foreach ($list as $key => $value) {
			$cardListVo[$key]['groupId'] = $key;
			$cardListVo[$key]['giftIndex'] = $key;

			$need_data = array();
			$need_data = explode(',', $value['need_data']);
			foreach ($need_data as $dakey => $data) {
				$card = explode('*', $data);

				$cardListVo[$key][$dakey]['cardid'] = $card[0];
				$cardListVo[$key][$dakey]['maxCount'] = $card[1];
			}
		}

		return $cardListVo;
	}

}