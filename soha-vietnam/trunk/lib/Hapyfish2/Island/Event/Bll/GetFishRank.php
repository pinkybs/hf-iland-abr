<?php

class Hapyfish2_Island_Event_Bll_GetFishRank
{
	public static function getData()
	{
			$titleId = 100;
			$nowTime = time();		
				
			$key = 'i:e:fishnumrank';
			$cache = Hapyfish2_Cache_Factory::getBasicMC('mc_0');
			$data = $cache->get($key);
			/*
			$data = array(
				0=>array('uid'=>'6497758','fishnum'=>1,'time'=>20111018)
			);
			*/
			if($data) {
				
				//先清除上一期用户的称号
				$dalFish = Hapyfish2_Island_Event_Dal_GetFishRank::getDefaultInstance();
				$lastUids = $dalFish->getLastDateUser();
				$feed = '取消了称号<font color="#FF0000"> 捕鱼达人</font>';
				if($lastUids) {
					foreach ($lastUids as $k=>$v) {
						$uid = $v['uid'];
						Hapyfish2_Island_HFC_User::delTitle($uid, $titleId, true);
					
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
				}
				//发送本期排行用户的称号
				$feed = '恭喜你获得称号<font color="#FF0000"> 捕鱼达人</font>';
				foreach($data as $k=>$v) {
					$uid = $v['uid'];
					Hapyfish2_Island_HFC_User::gainTitle($uid, $titleId, true);
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
				
				self::savelocaldb($data);			
				//清缓存
				$cache->delete($key);			
			}

	}
	public static function savelocaldb($data)
	{
		try {
			$dalFish = Hapyfish2_Island_Event_Dal_GetFishRank::getDefaultInstance();
			foreach($data as $k=>$v) {
				$info = array();
				$info['uid'] = $v['uid'];
				$info['rank'] = $k+1;
				$info['date'] = date('Ymd');
				$info['num'] = $v['fishnum'];
				$dalFish->FishRank($info);
			}
		}
		catch (Exception $e) {
			info_log($e->getMessage(), 'fishrannk_ins_error');
		}
	}
}