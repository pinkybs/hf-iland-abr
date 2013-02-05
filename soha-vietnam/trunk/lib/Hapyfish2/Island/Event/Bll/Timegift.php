<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com
 * */
class Hapyfish2_Island_Event_Bll_Timegift
{
	
	public static function setup($uid) 
	{
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$key 	= 'event_timegift_' . $uid;
		$val 	= array();
		$val['state'] = 0;
		
		$val['time_at'] = time();
		
		$cache->set($key, $val, Hapyfish2_Cache_Memcached::LIFE_TIME_ONE_DAY*10);
		
		$tg = Hapyfish2_Island_Event_Dal_Timegift::getDefaultInstance();
		
		$tg->setup($uid);
	}
	
	public static function gettime($uid) 
	{
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$key = 'event_timegift_' . $uid;
		$val = $cache->get($key);
		if( $val['state'] >= 6 || empty($val) ) {
			// 任务已经领取完
			$result['state'] = '-1';
			$result['content'] = 'serverWord_201'; // #todo 错误代码未改
			return $result;
		}
		
		$result['state'] = $val['state'];
		$result['time_at'] = $val['time_at'];
		
		return $result;
		
	}
	
	public static function receive( $uid ) 
	{
		/*
		 memcache 数据结构
		 hash 	time_at		上次时间
		 		state		第几次领取物品 
		 */
		//$h = array('0' => 0,'1'=>0.1, '2'=>0.2,'3'=>0.3,'4'=>0.4,'5'=>0.5);		// 测试时间,1分钟
		$h = array('0'=>5, '1'=>10, '2'=>20, '3'=>30, '4'=>40, '5'=>60);	// 正确时间	// 测试时间,1分钟
		
		$now = time();
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$key = 'event_timegift_' . $uid;
		$val = $cache->get($key);
		
		if( $val['state'] >= 6 || empty($val) ) {
			//任务已经领取完
			$result['state'] = '-1';
			$result['content'] = 'serverWord_201'; // #todo 错误代码未改
			return $result;
		}
	
		if( $now < ($val['time_at'] + ($h[$val['state']] * 60)) ) {
			// 时间未到
			$result['state'] = '-1';
			$result['content'] = 'serverWord_200'; // #todo 错误代码未改
			return $result;
		}
	
		try {
			$bllCompensation = new Hapyfish2_Island_Bll_Compensation();
			switch( $val['state'] ) {
				case 0 :
						//$info = array('uid'=>$uid, 'gold'=>2, 'item_data'=>'56641*2,26341*2', 'type'=>'0');
						//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
						$bllCompensation->setCoin(1000);
						$bllCompensation->setGold(2);
						$bllCompensation->setItem(74841, 1);
						$bllCompensation->setItem(26341, 2);
						$bllCompensation->sendOne($uid, '');
						
                		$result['state'] = '0';
                		$result['cids'] = array('74841*1', '26341*2');
                		$result['gold'] = 2;
                		$result['coin'] = 1000;
                		$result['time_at'] = $now;
                		$result['goldChange'] = 2;
                		$result['coinChange'] = 1000;
                		$result['itemBoxChange'] = true;
						break;
				case 1 :
						//$info = array('uid'=>$uid, 'gold'=>2, 'item_data'=>'56741*1,26341*2', 'type'=>'5');
                		//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
                		$bllCompensation->setCoin(1000);
						$bllCompensation->setGold(2);
						$bllCompensation->setItem(74841, 1);
						$bllCompensation->setItem(56641, 2);
						$bllCompensation->sendOne($uid, '');
						
			            $result['state'] = '1';
                		$result['cids'] = array('74841*1', '56641*1');
                		$result['gold'] = 2;
                		$result['coin'] = 1000;
                		$result['time_at'] = $now;
                		$result['goldChange'] = 2;
                		$result['coinChange'] = 1000;
                		$result['itemBoxChange'] = true;
						break;
				case 2 :
						//$info = array('uid'=>$uid, 'gold'=>2, 'item_data'=>'56741*2,74841*1', 'type'=>'20');
                		//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
                		$bllCompensation->setCoin(4000);
						$bllCompensation->setGold(2);
						$bllCompensation->setItem(74841, 1);
						$bllCompensation->setItem(67441, 1);
						$bllCompensation->sendOne($uid, '');
						
		                $result['state'] = '2';
                		$result['cids'] = array('74841*1', '67441*1'); 
                		$result['gold'] = 2;
                		$result['coin'] = 4000;
                		$result['time_at'] = $now;
                		$result['goldChange'] = 2;
                		$result['coinChange'] = 4000;
                		$result['itemBoxChange'] = true;
						break;
				case 3 :
						//$info = array('uid'=>$uid, 'gold'=>2, 'item_data'=>'74841*1,26441*2,14431*1', 'type'=>'40');
                		//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
                		$bllCompensation->setCoin(7000);
						$bllCompensation->setGold(2);
						$bllCompensation->setItem(74841, 2);
						$bllCompensation->setItem(26441, 2);
						$bllCompensation->sendOne($uid, '');
						
			            $result['state'] = '3';
                		$result['cids'] = array('74841*2', '26441*2');
                		$result['gold'] = 2;
                		$result['coin'] = 7000;
                		$result['time_at'] = $now;
                		$result['goldChange'] = 2;
                		$result['coinChange'] = 7000;
                		$result['itemBoxChange'] = true;
						break;
				case 4 :
						//$info = array('uid'=>$uid, 'coin'=>10000, 'gold'=>2, 'item_data'=>'74841*2,79331*1', 'type'=>'60');
                		//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
                		
						$bllCompensation->setCoin(10000);
						$bllCompensation->setGold(2);
						$bllCompensation->setItem(74841, 2);
						$bllCompensation->setItem(56741, 2);
						$bllCompensation->sendOne($uid, '');
						
			            $result['state'] = '4';
                		$result['cids'] = array('74841*2', '56741*2');
                		$result['gold'] = 2;
                		$result['coin'] = 10000;
                		$result['time_at'] = $now;
                		$result['coinChange'] = 10000;
                		$result['goldChange'] = 2;
                		$result['itemBoxChange'] = true;
						break;
				case 5 :
						//$info = array('uid'=>$uid, 'gold'=>10, 'item_data'=>'54321*1,15231*1,76621*1', 'type'=>'120');
                		//Hapyfish2_Island_Bll_GiftPackage::getNewUserGift($info);
                		
						$bllCompensation->setGold(5);
						$bllCompensation->setItem(74841, 4);
						$bllCompensation->setItem(56841, 1);
						$bllCompensation->setItem(55321, 1);
						$bllCompensation->sendOne($uid, '');
						
			            $result['state'] = '5';
                		$result['cids'] = array('74841*4', '56841*1', '55321*1');
                		$result['gold'] = 5;
                		$result['time_at'] = $now;
                		$result['goldChange'] = 5;
                		$result['itemBoxChange'] = true;
						break;
				default :
			}
			
		}
		catch( Exception $e ) {
			$result['state'] = '-1';
			$result['content'] = 'serverWord_110';
			info_log($e->getMessage(), 'Event_Timegift_Err');
			info_log($e->getTraceAsString(), 'Event_Timegift_Err');
		}
		
		$result['state']++;
		
		if( $val['state'] >= 5 ) {
			$cache->delete( $key );
		} else {
			$val['state']++;
			$val['time_at'] = time();
			$cache->set($key, $val, Hapyfish2_Cache_Memcached::LIFE_TIME_ONE_DAY*10);
			$tg = new Hapyfish2_Island_Event_Dal_Timegift();

			$tg->nextsteptask($uid);
		}
		
		return $result;
	}
	
	public static function getNextTimeGift($uid)
	{
		$now = time();
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$key = 'event_timegift_' . $uid;
		$val = $cache->get($key);
		
		if( $val['state'] >= 6 || empty($val) ) {
			//任务已经领取完
			$result['state'] = '-1';
			$result['content'] = 'serverWord_201'; // #todo 错误代码未改
			return $result;
		}
		
		if ( $val['state'] == 6 ) {
			$result['state'] = '-1';
			$result['content'] = 'Đây là phần thưởng cuối cùng của bạn';
			return $result;
		}
		
		try {
			switch( $val['state'] ) {
				case 0 :
                		$result['state'] = '1';
                		$result['cidarr'] = array('56641*2', '26341*2');
                		$result['gold'] = 2;
						break;
				case 1 :
			            $result['state'] = '2';
                		$result['cidarr'] = array('56741*1', '26341*2');
                		$result['gold'] = 2;
						break;
				case 2 :
		                $result['state'] = '3';
                		$result['cidarr'] = array('56741*2', '74841*1'); 
                		$result['gold'] = 2;
						break;
				case 3 :
			            $result['state'] = '4';
                		$result['cidarr'] = array('74841*1', '26441*2', '14431*1');
                		$result['gold'] = 2;
						break;
				case 4 :
			            $result['state'] = '5';
                		$result['cidarr'] = array('74841*2', '79331*1');
                		$result['gold'] = 2;
                		$result['coin'] = 10000;
						break;
				case 5 :
			            $result['state'] = '6';
                		$result['cidarr'] = array('54321*1', '15231*1', '76621*1');
                		$result['gold'] = 10;
						break;
				default :
			}
		}
		catch( Exception $e ) {
			$result['state'] = '-1';
			$result['content'] = 'serverWord_110';
			info_log($e->getMessage(), 'Event_Timegift_Err');
			info_log($e->getTraceAsString(), 'Event_Timegift_Err');
		}
		
		return $result;
		
	}
	
}