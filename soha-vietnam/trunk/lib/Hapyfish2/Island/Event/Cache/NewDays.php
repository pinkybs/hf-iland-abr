<?php

/**
 * Event NewDays
 *
 * @package    Island/Event/Bll
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/12/23    zhangli
*/
class Hapyfish2_Island_Event_Cache_NewDays
{
	/**
	 * @获取锤子砸中物品概率
	 * @param int $eid
	 * @return Array
	 */
	public static function getEggData($eid)
	{
		//0->2,1->0,2->1,3->2,4->happy,5->new,6->year,
		//7->船只加速卡I,8->船只加速卡II,9->一键收钱卡,10->新年天空,11->船只加速卡III
		$key = 'ev:newdays:items';
		$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
		$list = $cache->get($key);

		if ($list === false) {
			try {
				$db = Hapyfish2_Island_Event_Dal_NewDays::getDefaultInstance();
				$list = $db->getListArr();
			} catch (Exception $e) {}

			if ($list) {
				$cache->set($key, $list, 3600 * 24 * 15);
			}
		}
		
		foreach ($list as $ls) {
			if ($ls['eid'] == $eid) {
				$dataCo = $ls;
				break;
			}
		}
		
		return $dataCo;
	}
	
	/**
	 * @根据ID获取物品CID
	 * @param int $gainID
	 * @return Array
	 */
	public static function getCardID($gainID)
	{
		//0->2,1->0,2->1,3->2,4->happy,5->new,6->year,
		//7->船只加速卡I,8->船只加速卡II,9->一键收钱卡,10->新年天空,11->船只加速卡III
		$cids = array('0' => '133241', '1' => '133041', '2' => '133141', '3' => '133341', '4' => '133841', '5' => '133941', '6' => '134041',
					'7' => '26241', '8' => '26341', '9' => '67441', '10' => '67712', '11' => '26441');
		
		foreach ($cids as $key => $cid) {
			if ($gainID == $key) {
				return $cid;
			}
		}
		
		return 0;
	}
	
	/**
	 * @获取当日金币锤子的次数
	 * @param int $uid
	 * @return int
	 */
	public static function getWoodenHammerNum($uid)
	{
		$key = 'ev:newdays:woodenhammer:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);

		return $data;
	}

	/**
	 * @增加当日使用金币锤子的次数
	 * @param unknown_type $uid
	 */
	public static function addWoodenHammerNum($uid)
	{
		$key = 'ev:newdays:woodenhammer:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
	
		$data += 1;

		$logDate = date('Y-m-d');
		$dtDate = $logDate . ' 23:59:59';
		$endTime = strtotime($dtDate);
		
		$cache->set($key, $data, $endTime);
	}
	
	/**
	 * @获取当前兑换礼包的信息
	 * @param int $pid
	 * @return Array
	 */
	public static function getData($pid)
	{
		//item:兑换需求,list:奖励物品
		$dataVo = array(array('pid' => 1, 'item' => array('133241' => 1, '133041' => 1, '133141' => 1, '133341' => 1), 'list' => array('1' => 5000, '74841' => 5)),
						array('pid' => 2, 'item' => array('133841' => 1, '133941' => 1, '134041' => 1), 'list' => array('65232' => 1, '74841' => 5, '1' => 10000)),
						array('pid' => 3, 'item' => array('133241' => 1, '133041' => 1, '133141' => 1, '133341' => 1, '133841' => 1, '133941' => 1, '134041' => 1), 'list' => array('65531' => 1, '74841' => 10, '26441' => 5, '1' => 30000)));
		
		foreach ($dataVo as $itemVo) {
			if ($itemVo['pid'] == $pid) {
				$data = $itemVo;
				break;
			}
		}
						
		return $data;
	}
	
}