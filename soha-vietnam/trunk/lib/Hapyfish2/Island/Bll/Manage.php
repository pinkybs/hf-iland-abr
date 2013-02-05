<?php

class Hapyfish2_Island_Bll_Manage
{
	public static function clearUser($uid)
	{
		$time = time();
		$today = date('Ymd', $time);
		$yearmonth = date('Ym', $time);
		$step = 0;
		
		//init db
		//
		try {
			$dalUser = Hapyfish2_Island_Dal_User::getDefaultInstance();
			$dalUserSequence = Hapyfish2_Island_Dal_UserSequence::getDefaultInstance();
			$dalBackground = Hapyfish2_Island_Dal_Background::getDefaultInstance();
			$dalBuilding = Hapyfish2_Island_Dal_Building::getDefaultInstance();
			$dalPlant = Hapyfish2_Island_Dal_Plant::getDefaultInstance();
			$dalDock = Hapyfish2_Island_Dal_Dock::getDefaultInstance();
			$dalCard = Hapyfish2_Island_Dal_Card::getDefaultInstance();
			$dalCardStatus = Hapyfish2_Island_Dal_CardStatus::getDefaultInstance();
			$dalUserIsland = Hapyfish2_Island_Dal_UserIsland::getDefaultInstance();
			$dalAchievement = Hapyfish2_Island_Dal_Achievement::getDefaultInstance();
			$dalAchievementDaily = Hapyfish2_Island_Dal_AchievementDaily::getDefaultInstance();
			$dalTask = Hapyfish2_Island_Dal_Task::getDefaultInstance();
			$dalTaskDaily = Hapyfish2_Island_Dal_TaskDaily::getDefaultInstance();
			
			//$dalConsumeLog = Hapyfish2_Island_Dal_ConsumeLog::getDefaultInstance();
			$dalInviteLog = Hapyfish2_Island_Dal_InviteLog::getDefaultInstance();
			$dalLevelUpLog = Hapyfish2_Island_Dal_LevelUpLog::getDefaultInstance();
			
			/*$userinfo = array(
				'coin' => 40000,
				'exp' => 0,
				'level' => 1,
				'island_level' => 1,
				'last_login_time' => $time,
				'today_login_count' => 1,
				'active_login_count' => 0,
				'max_active_login_count' => 0
			);
			$dalUser->update($uid, $userinfo);*/
			$dalUser->delete($uid);
			$dalUser->init($uid);
			$step++;
			
			$dalBackground->clear($uid);
			$dalBackground->init($uid);
			$step++;
			
			$dalBuilding->clear($uid);
			$dalBuilding->init($uid);
			$step++;
			
			$dalPlant->clear($uid);
			$dalPlant->init($uid);
			$step++;
			
			$dalDock->clear($uid);
			$dalDock->init($uid);
			$step++;

			/*$userislandinfo = array(
				'praise' => 14,
				'position_count' => 3,
				'bg_island' => 25411,
				'bg_island_id' => 1,
				'bg_sky' => 23212,
				'bg_sky_id' => 2,
				'bg_sea' => 22213,
				'bg_sea_id' => 3,
				'bg_dock' => 25914,
				'bg_dock_id' => 4,
				'title' => 0,
				'title_list' => '',
				'help' => '',
				'help_completed' => 0
			);
			$dalUserIsland->update($uid, $userislandinfo);*/
			$dalUserIsland->delete($uid);
			$dalUserIsland->init($uid);
			$step++;
			
			$dalCard->clear($uid);
			$dalCard->init($uid);
			$step++;
			
			$cardStatusInfo = array(
				'card_0' => 0,
				'card_1' => 0,
				'card_2' => 0,
				'card_3' => 0,
				'card_4' => 0
			);
			$dalCardStatus->update($uid, $cardStatusInfo);
			$step++;
			
			$achievement = array(
				'num_1' => 0, 'num_2' => 0, 'num_3' => 0, 'num_4' => 0, 'num_5' => 0,
				'num_6' => 0, 'num_7' => 0, 'num_8' => 0, 'num_9' => 0, 'num_10' => 0,
				'num_11' => 0, 'num_12' => 0, 'num_13' => 0, 'num_14' => 0, 'num_15' => 0,
				'num_16' => 0, 'num_17' => 0, 'num_18' => 0, 'num_19' => 0, 'num_20' => 0
			);
			$dalAchievement->update($uid, $achievement);
			$step++;
			
			$achievementDaily = array(
				'today' => $today,
				'num_1' => 0, 'num_2' => 0, 'num_3' => 0, 'num_4' => 0, 'num_5' => 0,
				'num_6' => 0, 'num_7' => 0, 'num_8' => 0, 'num_9' => 0, 'num_10' => 0
			);
			$dalAchievementDaily->update($uid, $achievementDaily);
			$step++;
			
			$dalTask->clear($uid);
			$step++;
			$dailyTaskInfo = array(
				'today' => $today,
				'tids' => ''
			);
			$dalTaskDaily->update($uid, $dailyTaskInfo);
			$step++;
			
			//$dalConsumeLog->clear($uid, $yearmonth);
			//$step++;
			
			$dalInviteLog->clear($uid);
			$step++;
			
			$dalLevelUpLog->clear($uid);
			$step++;
			
		}
		catch (Exception $e) {
			info_log('[' . $step . ']' . $e->getMessage(), 'manage.inituser');
            return false;
		}
		
		//clear cache
		$cache = Hapyfish2_Cache_Factory::getHFC($uid);
		$cache2 = Hapyfish2_Cache_Factory::getMC($uid);
		
		$keys = array(
			'i:u:exp:' . $uid,
			'i:u:coin:' . $uid,
			'i:u:level:' . $uid,
			'i:u:island:' . $uid,
			'i:u:title:' . $uid,
			'i:u:cardstatus:' . $uid,
			'i:u:login:' . $uid
		);
		foreach ($keys as $key) {
			$cache->delete($key);
		}
		
		$builingIds = Hapyfish2_Island_Cache_Building::getAllIds($uid);
		if ($builingIds) {
	        foreach ($builingIds as $id) {
	        	$key = 'i:u:bld:' . $uid . ':' . $id;
	        	$cache->delete($key);
	        }
		}
		
		$plantIds = Hapyfish2_Island_Cache_Plant::getAllIds($uid);
		if ($plantIds) {
	        foreach ($plantIds as $id) {
	        	$key = 'i:u:plt:' . $uid . ':' . $id;
	        	$cache->delete($key);
	        	$keys2 = 'i:u:mooch:plt:' . $uid . ':' . $id;
	        	$cache2->delete($keys2);
	        	$key3 = 'i:tm:opplant:' . $uid . ':' . $id;
	        	$cache2->delete($key3);
	        }
		}
		
		$key = 'i:u:card:' . $uid;
		$cache->delete($key);
		
	    for($i = 1; $i <= 8; $i++) {
        	$key = 'i:u:dock:' . $uid . ':' . $i;
        	$cache->delete($key);
        	$key2 = 'i:u:mooch:ship:' . $uid . ':' . $i;
        	$cache2->delete($key2);
        	$key3 = 'i:u:unlockshids:' . $uid . ':' . $i;
        	$cache2->delete($key3);
        }
        
        $key = 'i:u:ach:' . $uid;
        $cache->delete($key);
        
        $key = 'i:u:achdly:' . $uid;
        $cache->delete($key);
        
        
        $key = 'i:u:bg:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:bldids:all:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:bldids:onisl:' . $uid;
        $key = 'i:u:bldids:onisl:' . $uid . ':1';
        $key = 'i:u:bldids:onisl:' . $uid . ':2';
        $key = 'i:u:bldids:onisl:' . $uid . ':3';
        $key = 'i:u:bldids:onisl:' . $uid . ':4';
        $cache2->delete($key);
        
        $key = 'i:u:pltids:all:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:pltids:onisl:' . $uid;
        $key = 'i:u:pltids:onisl:' . $uid . '1';
        $key = 'i:u:pltids:onisl:' . $uid . '2';
        $key = 'i:u:pltids:onisl:' . $uid . '3';
        $key = 'i:u:pltids:onisl:' . $uid . '4';
        $cache2->delete($key);
        
        $key = 'island:userplantbyik:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:tm:opisland:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:giftcntdly:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:unlockshcnt:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:alltask:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:alltaskdly:' . $uid;
        $cache2->delete($key);
        
        $key = 'i:u:help:' . $uid;
        $cache2->delete($key);
        
        $cache3 = Hapyfish2_Cache_Factory::getFeed($uid);
        $key = 'i:u:feed:' . $uid;
        $cache3->delete($key);
        
        $key = 'i:u:feed:count:' . $uid;
        $cache3->delete($key);
        
        $cache4 = Hapyfish2_Cache_Factory::getRemind($uid);
        $key = 'i:u:remind:' . $uid;
        $cache4->delete($key);
        
        $key = 'i:u:remind:count:' . $uid;
        $cache4->delete($key);
        
        //阶段性礼物
        Hapyfish2_Island_Cache_User::updateUserNextBigGiftLevel($uid, 5);
        
        return true;
	}

}