<?php

class Hapyfish_Island_Cache_Plant
{
	public static function canOutIslandPeople($uid)
	{
		$key = 'OutIslandPeople_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->lock($key, 120);
	}
	
	public static function doneOutIslandPeople($uid)
	{
		$key = 'OutIslandPeople_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->unlock($key);
	}
	
	public static function canOutPlantPeopleOfItem($uid, $itemId)
	{
		$key = 'canoutPlantPeopleOfItem_' . $itemId;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->lock($key, 120);		
	}
	
	public static function doneOutPlantPeopleOfItem($uid, $itemId)
	{
		$key = 'canoutPlantPeopleOfItem_' . $itemId;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->unlock($key);
	}
	
	public static function getLastOutIslandPeopleTime($uid)
	{
        $key = 'LastOutIslandPeopleTime_' . $uid;
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $time = $cache->get($key);
        
        if ($time === false) {
        	$time = Hapyfish_Island_Cache_Login::getLastLoginTime($uid);
        	$cache->add($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
        }
        
        return $time;
	}
	
	public static function updateLastOutIslandPeopleTime($uid, $time)
	{
        $key = 'LastOutIslandPeopleTime_' . $uid;
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $cache->replace($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function getLastOutPlantPeopleTime($uid, $itemId)
	{
        $key = 'LastOutPlantPeopleTime_' . $uid . '_' . $itemId;
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $time = $cache->get($key);
        
        if ($time === false) {
        	$time = self::getLastOutIslandPeopleTime($uid);
        	$cache->add($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
        }
        
        return $time;
	}
	
	public static function updateLastOutPlantPeopleTime($uid, $itemId, $time)
	{
        $key = 'LastOutPlantPeopleTime_' . $uid . '_' . $itemId;
        $cache = Hapyfish_Cache_Memcached::getInstance();
        $cache->replace($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function getUserPlantIds($uid)
	{
		$key = 'UserPlantIds_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$ids = $cache->get($key);
		if (!$ids) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$ids = $db->getUserPlantIds($uid);
			if ($ids) {
				$cache->add($key, $ids, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $ids;
	}
	
	public static function getPlantPayTimeList()
	{
		$key = 'PlantPayTimeList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$list = $db->getPlantPayTimeList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;		
	}
	
	public static function refreshPlantPayTimeList()
	{
		$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
		$list = $db->getPlantPayTimeList();
		if ($list) {
			$key = 'PlantPayTimeList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;
	}
	
	public static function cleanPlantPayTimeList()
	{
		$key = 'PlantPayTimeList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->delete($key);	
	}
	
	public static function getPlantPayTimeAndTicketList()
	{
		$key = 'PlantPayTimeAndTicketList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$list = $cache->get($key);
		if ($list === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$list = $db->getPlantPayTimeAndTicketList();
			if ($list) {
				$cache->add($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $list;		
	}
	
	public static function refreshPlantPayTicketList()
	{
		$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
		$list = $db->getPlantPayTimeAndTicketList();
		if ($list) {
			$key = 'PlantPayTimeAndTicketList';
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $list, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $list;
	}
	
	public static function cleanPlantPayTicketList()
	{
		$key = 'PlantPayTimeAndTicketList';
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->delete($key);	
	}
	
	public static function cleanUserPlantIds($uid)
	{
		$key = 'UserPlantIds_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);		
	}
	
	public static function getUserPlantList($uid)
	{
		$basicInfo = self::getUserUsingPlantBasicInfo($uid);
		$plants = array();
		if ($basicInfo) {
			$plant = null;
			$keys = array();
			$tmp = array();
			foreach ($basicInfo as $basicPlant) {
				$key = 'UserPlantPayInfoById_' . $uid . '_' . $basicPlant['id'];
				$keys[] = $key;
				$tmp[$key] = $basicPlant;
			}
			
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$datas = $cache->getMulti($keys);
			$payTimeList = self::getPlantPayTimeList();
			$nocache = array();
			foreach ($datas as $key => $value) {
				$default = $tmp[$key];
				if (!$value) {
					$plant = array(
						'id' => $default['id'],
						'itemId' => $default['itemId'],
						'uid' => $uid,
						'cid' => $default['cid'],
						'can_find' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'event' => 0,
						'wait_visitor_num' => 0,
						'start_pay_time' => 0,
						'safecard_time' => 0,
						'deposit' => 0,
						'start_deposit' => 0,
						'delay_time' => 0,
						'event_manage_time' => 0
					);
					$nocache[$key] = array(
						'id' => $default['id'],
						'itemId' => $default['itemId'],
						'uid' => $uid,
						'cid' => $default['cid'],
						'can_find' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'event' => 0,
						'wait_visitor_num' => 0,
						'start_pay_time' => 0,
						'safecard_time' => 0,
						'deposit' => 0,
						'start_deposit' => 0,
						'delay_time' => 0,
						'event_manage_time' => 0
					);
				} else {
					$plant = $value;
					//repair pay_time null
					if (empty($plant['pay_time'])) {
						$plant['pay_time'] = $payTimeList[$default['cid']];
						self::updateUserPlantPayInfoById($uid, $plant['id'], $plant);
					}
				}
				
				$plant['item_id'] = $default['item_id'];
				$plant['level'] = $default['level'];
				$plants[] = $plant;
			}
			
			if (!empty($nocache)) {
				$cache->setMulti($nocache, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $plants;
	}
	
	public static function getUserUsingPlantBasicInfo($uid)
	{
		$key = 'UserUsingPlantBasicInfo_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if (!$info) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$info = $db->getUsingPlantInfo($uid);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
	}
	
	public static function cleanUserUsingPlantBasicInfo($uid)
	{
		$key = 'UserUsingPlantBasicInfo_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->delete($key);		
	}
	
	public static function getUserUsingPlant($uid)
	{
		$basicInfo = self::getUserUsingPlantBasicInfo($uid);
		$plants = array();
		if ($basicInfo) {
			$plant = null;
			
			$keys = array();
			$tmp = array();
			foreach ($basicInfo as $basicPlant) {
				$key = 'UserPlantPayInfoById_' . $uid . '_' . $basicPlant['id'];
				$keys[] = $key;
				$tmp[$key] = $basicPlant;
			}
			
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$datas = $cache->getMulti($keys);
			$nocache = array();
			$payTimeList = self::getPlantPayTimeList();
			foreach ($datas as $key => $value) {
				$default = $tmp[$key];
				if (!$value) {
					$plant = array(
						'id' => $default['itemId'],
						'cid' => $default['cid'],
						'x' => $default['x'],
						'y' => $default['y'],
						'z' => $default['z'],
						'mirro' => $default['mirro'],
						'event' => 0,
						'waitVisitorNum' => 0,
						'start_pay_time' => 0,
						'deposit' => 0,
						'startDeposit' => 0,
						'canFind' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'delay_time' => 0
					);
					
					$nocache[$key] = array(
						'id' => $default['id'],
						'itemId' => $default['itemId'],
						'uid' => $uid,
						'cid' => $default['cid'],
						'can_find' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'event' => 0,
						'wait_visitor_num' => 0,
						'start_pay_time' => 0,
						'safecard_time' => 0,
						'deposit' => 0,
						'start_deposit' => 0,
						'delay_time' => 0,
						'event_manage_time' => 0
					);
				} else {
					$plant = array(
						'id' => $default['itemId'],
						'cid' => $default['cid'],
						'x' => $default['x'],
						'y' => $default['y'],
						'z' => $default['z'],
						'mirro' => $default['mirro'],
						'event' => $value['event'],
						'waitVisitorNum' => $value['wait_visitor_num'],
						'start_pay_time' => $value['start_pay_time'],
						'deposit' => $value['deposit'],
						'startDeposit' => $value['start_deposit'],
						'canFind' => $value['can_find'],
						'pay_time' => $value['pay_time'],
						'delay_time' => $value['delay_time']
					);
				}
				$plants[] = $plant;
			}
			
			if (!empty($nocache)) {
				$cache->setMulti($nocache, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
						
		}
		
		return $plants;
	}
	
	public static function removePlantById($uid, $id)
	{
		try {
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$db->removePlantById($uid, $id);
			
			$key = 'UserPlantPayInfoById_' . $uid . '_' .$id;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->delete($key);
		} catch(Exception $e) {
			
		}
	}
	
	public static function getPlantInfoById($uid, $id)
	{
		$key = 'PlantInfoById_' . $uid . '_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if (!$info) {
			//load from database
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$info = $db->getPlantInfoById($uid, $id);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;		
	}
	
	public static function upgradePlantById($uid, $id, $bid, $level)
	{
		$upgradeInfo = array(
			'uid' => $uid,
			'bid' => $bid,
			'level' => $level
		);
		
		try {
			$db = Hapyfish_Island_Dal_Plant::getDefaultInstance();
			$db->updateUserPlant($id, $upgradeInfo);
			
			self::cleanUserUsingPlantBasicInfo($uid);
			
			$plantPayInfo =  self::getUserPlantPayInfoById($uid, $id);
			if ($plantPayInfo) {
				$plantPayInfo['cid'] = $bid;
				$key = 'UserPlantPayInfoById_' . $uid . '_' . $id;
				$cache = Hapyfish_Cache_Memcached::getInstance();
				$cache->replace($key, $plantPayInfo, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		} catch(Exception $e) {
			
		}
	}
	
	public static function getUsingPlantById($uid, $id)
	{
		$payInfo = self::getUserPlantPayInfoById($uid, $id);
		$plant = null;
		if ($payInfo) {
			$basicInfo = self::getPlantInfoById($uid, $id);
			$plant = array(
				'id' => $payInfo['itemId'],
				'cid' => $payInfo['cid'],
				'level' => $payInfo['cid'],
				'x' => $basicInfo['x'],
				'y' => $basicInfo['y'],
				'z' => $basicInfo['z'],
				'mirro' => $basicInfo['mirro'],
				'event' => $payInfo['event'],
				'waitVisitorNum' => $payInfo['wait_visitor_num'],
				'start_pay_time' => $payInfo['start_pay_time'],
				'deposit' => $payInfo['deposit'],
				'startDeposit' => $payInfo['start_deposit'],
				'canFind' => $payInfo['can_find'],
				'pay_time' => $payInfo['pay_time'],
				'delay_time' => $payInfo['delay_time']
			);
		}
		
		return $plant;
	}

    //id
    //itemId
    //uid
    //can_find			: 游客是否可以走到此建筑
    //event    			: 设施事件,0:没有,1:故障,2:使用破坏卡直接故障
    //wait_visitor_num  : 当前排队人数
    //start_pay_time    : 开始收费时间
    //safecard_time     : 保护卡使用时间
    //deposit           : 目前金币数
    //start_deposit     : 起始金币数
    //delay_time        : 延迟卡使用时间:如 10800秒
    //event_manage_time : 事件处理时间
	public static function getUserPlantPayInfoById($uid, $id)
	{
		$key = 'UserPlantPayInfoById_' . $uid . '_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if (!$info) {
			$basicInfo = self::getPlantInfoById($uid, $id);
			if ($basicInfo) {
				$info = array(
					'id' => $id,
					'itemId' => $basicInfo['itemId'],
					'uid' => $uid,
					'cid' => $basicInfo['cid'],
					'can_find' => $basicInfo['can_find'],
					'pay_time' => $basicInfo['pay_time'],
					'event' => 0,
					'wait_visitor_num' => 0,
					'start_pay_time' => 0,
					'safecard_time' => 0,
					'deposit' => 0,
					'start_deposit' => 0,
					'delay_time' => 0,
					'event_manage_time' => 0
				);
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
	}
	
	public static function getNewUserUsingPlant($uid)
	{
		$basicInfo = self::getUserUsingPlantBasicInfo($uid);
		$plants = array();
		if ($basicInfo) {
			$plant = null;
			
			$keys = array();
			$tmp = array();
			foreach ($basicInfo as $basicPlant) {
				$key = 'UserPlantPayInfoById_' . $uid . '_' . $basicPlant['id'];
				$keys[] = $key;
				$tmp[$key] = $basicPlant;
			}
			
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$datas = $cache->getMulti($keys);
			$nocache = array();
			$payTimeList = self::getPlantPayTimeList();
			foreach ($datas as $key => $value) {
				$default = $tmp[$key];
				if (!$value) {
					if ($default['cid'] == '632') {
						$deposit = 300;
						$start_deposit = 300;
					} else {
						$deposit = 0;
						$start_deposit = 0;				
					}
					$plant = array(
						'id' => $default['itemId'],
						'cid' => $default['cid'],
						'x' => $default['x'],
						'y' => $default['y'],
						'z' => $default['z'],
						'mirro' => $default['mirro'],
						'event' => 0,
						'waitVisitorNum' => 0,
						'start_pay_time' => 0,
						'deposit' => $deposit,
						'startDeposit' => $start_deposit,
						'canFind' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'delay_time' => 0
					);
					
					$nocache[$key] = array(
						'id' => $default['id'],
						'itemId' => $default['itemId'],
						'uid' => $uid,
						'cid' => $default['cid'],
						'can_find' => $default['can_find'],
						'pay_time' => $payTimeList[$default['cid']],
						'event' => 0,
						'wait_visitor_num' => 0,
						'start_pay_time' => 0,
						'safecard_time' => 0,
						'deposit' => $deposit,
						'start_deposit' => $start_deposit,
						'delay_time' => 0,
						'event_manage_time' => 0
					);
				} else {
					$plant = array(
						'id' => $default['itemId'],
						'cid' => $default['cid'],
						'x' => $default['x'],
						'y' => $default['y'],
						'z' => $default['z'],
						'mirro' => $default['mirro'],
						'event' => $value['event'],
						'waitVisitorNum' => $value['wait_visitor_num'],
						'start_pay_time' => $value['start_pay_time'],
						'deposit' => $value['deposit'],
						'startDeposit' => $value['start_deposit'],
						'canFind' => $value['can_find'],
						'pay_time' => $value['pay_time'],
						'delay_time' => $value['delay_time']
					);
				}
				$plants[] = $plant;
			}
			
			if (!empty($nocache)) {
				$cache->setMulti($nocache, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
						
		}
		
		return $plants;
	}
	
	public static function updateUserPlantPayInfoById($uid, $id, $data)
	{
		$key = 'UserPlantPayInfoById_' . $uid . '_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->replace($key, $data, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function casUpdateUserPlantPayInfoById($uid, $id, $fields)
	{
		$key = 'UserPlantPayInfoById_' . $uid . '_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		
		return $cache->cas($key, $fields, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function cleanUserPlantPayInfoById($uid, $id)
	{
		$key = 'UserPlantPayInfoById_' . $uid . '_' . $id;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);	
	}

	
	public static function getCurrentlyVisitor($uid)
	{
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$key = 'CurrentlyVisitor_' . $uid;
		$number = $cache->get($key);
		if (!$number) {
			$number = 0;
			$cache->add($key, $number, 0);
			return $number;
		}
		
		if ($number < 0) {
			$number = 0;
			$cache->replace($key, $number, 0);
		}
		
		return $number;
	}
	
	public static function incCurrentlyVistor($uid, $inc_number)
	{
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$key = 'CurrentlyVisitor_' . $uid;
		return $cache->increment($key, $inc_number);
	}
		
	public static function decCurrentlyVistor($uid, $dec_number)
	{
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$key = 'CurrentlyVisitor_' . $uid;
		return $cache->decrement($key, $dec_number);
	}
	
	public static function updateCurrentlyVistor($uid, $number)
	{
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$key = 'CurrentlyVisitor_' . $uid;
		return $cache->replace($key, $number);		
	}
	
	public static function cleanCurrentlyVistor($uid)
	{
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$key = 'CurrentlyVisitor_' . $uid;
		return $cache->delete($key);
	}	
	
	
}